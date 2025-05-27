<?php
// app/models/User.php

class User {
    private $db;

    public function __construct() {
        if (class_exists('Database')) {
            $this->db = new Database();
        } else {
            die("Fatal Error: Database class not found in User model.");
        }
    }

    public function register($data) {
        // 1. ابتدا کد همکاری را ایجاد کنید
        $affiliate_code_to_set = $this->generateUniqueAffiliateCode();
        if ($affiliate_code_to_set === false) { 
            error_log("UserModel::register - Could not generate unique affiliate code for username: " . ($data['username'] ?? 'N/A'));
            return false;
        }

        // 2. سپس کوئری INSERT را آماده کنید
        $sql = 'INSERT INTO users (username, email, password, first_name, last_name, role, affiliate_code) 
                VALUES (:username, :email, :password, :first_name, :last_name, :role, :affiliate_code)';
        
        try {
            $this->db->query($sql);
            
            // 3. تمام پارامترهای INSERT را بایند کنید
            $this->db->bind(':username', $data['username']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':password', $data['password']); 
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':role', isset($data['role']) ? $data['role'] : 'customer');
            $this->db->bind(':affiliate_code', $affiliate_code_to_set); // استفاده از کد ایجاد شده
            
            // 4. کوئری INSERT را اجرا کنید
            if ($this->db->execute()) {
                return true;
            } else {
                error_log("UserModel::register - Execute failed for username: " . ($data['username'] ?? 'N/A') . ". DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("UserModel::register - PDOException for username: " . ($data['username'] ?? 'N/A') . ". Message: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        } catch (Exception $e) {
             error_log("UserModel::register - General Exception for username: " . ($data['username'] ?? 'N/A') . ". Message: " . $e->getMessage());
            return false;
        }
    }

    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        return $this->db->single() ?: false;
    }

    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        return $this->db->single() ?: false;
    }

    public function findUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', (int)$id);
        return $this->db->single() ?: false;
    }

    public function login($usernameOrEmail, $password) {
        $row = $this->findUserByUsername($usernameOrEmail);
        if (!$row) {
            $row = $this->findUserByEmail($usernameOrEmail);
        }
        if ($row && password_verify($password, $row['password'])) {
            return $row;
        }
        return false;
    }

    public function getUsersByRole($role) {
        $this->db->query('SELECT id, username, first_name, last_name, email, role FROM users WHERE role = :role ORDER BY username ASC');
        $this->db->bind(':role', $role);
        return $this->db->resultSet() ?: [];
    }

    public function generateUniqueAffiliateCode($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $retry_limit = 10; 
        $count = 0;
        $generated_code = '';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[rand(0, $charactersLength - 1)];
            }
            $generated_code = $code; 
            $sql = "SELECT id FROM users WHERE affiliate_code = :unique_aff_code_check";
            
            try {
                $this->db->query($sql);
                $this->db->bind(':unique_aff_code_check', $code);
                $row = $this->db->single(); 

                if ($row === false && $this->db->getErrorInfo()[0] !== '00000' && $this->db->getErrorInfo()[1] !== null) {
                    error_log("UserModel::generateUniqueAffiliateCode - DB error during SELECT. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                    return false; 
                }
                if (!$row) { 
                    error_log("UserModel::generateUniqueAffiliateCode - Unique code found: " . $code);
                    return $generated_code; 
                } else {
                    error_log("UserModel::generateUniqueAffiliateCode - Code {$code} already exists. Retrying...");
                }
            } catch (Exception $e) {
                 error_log("UserModel::generateUniqueAffiliateCode - Exception during DB operation: " . $e->getMessage());
                 return false; 
            }
            $count++;
        } while ($count < $retry_limit);

        error_log("UserModel::generateUniqueAffiliateCode - Failed to generate a unique code after {$retry_limit} retries. Returning fallback.");
        return 'AFF' . uniqid(); 
    }

    public function findUserByAffiliateCode($code) {
        if(empty($code)) return false;
        $this->db->query('SELECT * FROM users WHERE affiliate_code = :affiliate_code');
        $this->db->bind(':affiliate_code', $code);
        return $this->db->single() ?: false;
    }

    public function updateAffiliateBalance($user_id, $amount) {
        $this->db->query('UPDATE users SET affiliate_balance = affiliate_balance + :amount WHERE id = :id');
        $this->db->bind(':amount', (float)$amount);
        $this->db->bind(':id', (int)$user_id);
        if ($this->db->execute()) { return true; }
        error_log("User::updateAffiliateBalance failed for user_id: {$user_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        return false;
    }

    public function updateUserRole($user_id, $new_role) {
        $allowed_roles = ['customer', 'vendor', 'affiliate', 'admin'];
        if (!in_array($new_role, $allowed_roles)) {
            error_log("User::updateUserRole - Invalid role '{$new_role}' for user_id: {$user_id}.");
            return false;
        }
        $this->db->query('UPDATE users SET role = :role WHERE id = :id');
        $this->db->bind(':role', $new_role);
        $this->db->bind(':id', (int)$user_id);
        return $this->db->execute();
    }

    public function updateUserProfile($data) {
        // این متد باید بر اساس نیاز شما تکمیل شود
        // مثال:
        // $this->db->query('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id');
        // $this->db->bind(':first_name', $data['first_name']);
        // $this->db->bind(':last_name', $data['last_name']);
        // $this->db->bind(':email', $data['email']);
        // $this->db->bind(':id', (int)$data['id']);
        // return $this->db->execute();
        return true; // Placeholder
    }
}
// تگ پایانی PHP را حذف کنید
