<?php
class User {
    private $db;

    public function __construct() {
        if (class_exists('Database')) {
            $this->db = new Database();
        } else {
            error_log("UserModel FATAL ERROR: Database class not found.");
            throw new Exception("Fatal Error: Database class not found in User model.");
        }
    }

    /**
     * Register user with a specific role.
     * Assumes password is ALREADY HASHED by the controller.
     * Assumes all other data validation (including password confirmation) is done by the controller.
     * @param array $data User data including role and role-specific fields.
     * @return int|string User ID on success, error message string on failure.
     */
    public function register($data) {
        // Password is now expected to be pre-hashed by the controller.
        // NO password validation (like length or confirm_password) or password_hash() call should happen here.
        
        $role = $data['role'] ?? 'customer';
        $status = isset($data['status']) ? (int)$data['status'] : 1; 

        $affiliate_code = null;
        if ($role === 'affiliate') {
            $affiliate_code = method_exists($this, 'generateUniqueAffiliateCode') ? $this->generateUniqueAffiliateCode() : ('AFF' . uniqid());
        }
        
        // Ensure all columns match your `users` table structure
        // Added created_at and updated_at to be set by NOW()
        // Added shop_name, vendor_payment_details, affiliate_payment_details
        $sql = 'INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, affiliate_code, shop_name, vendor_payment_details, affiliate_payment_details, created_at, updated_at) 
                VALUES (:username, :email, :password, :first_name, :last_name, :phone, :role, :status, :affiliate_code, :shop_name, :vendor_payment_details, :affiliate_payment_details, NOW(), NOW())';
        
        $this->db->query($sql);
        
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']); // Expecting HASHED password
        $this->db->bind(':first_name', $data['first_name'] ?? null);
        $this->db->bind(':last_name', $data['last_name'] ?? null);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':role', $role);
        $this->db->bind(':status', $status);
        $this->db->bind(':affiliate_code', $affiliate_code);
        
        $this->db->bind(':shop_name', ($role === 'vendor' && isset($data['shop_name'])) ? $data['shop_name'] : null);
        $this->db->bind(':vendor_payment_details', ($role === 'vendor' && isset($data['vendor_payment_details'])) ? $data['vendor_payment_details'] : null);
        $this->db->bind(':affiliate_payment_details', ($role === 'affiliate' && isset($data['affiliate_payment_details'])) ? $data['affiliate_payment_details'] : null);

        try {
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                $errorInfo = $this->db->getErrorInfo();
                if (isset($errorInfo[1]) && $errorInfo[1] == 1062) { 
                    if (strpos(strtolower($errorInfo[2]), 'username') !== false) return 'نام کاربری قبلاً استفاده شده است.';
                    if (strpos(strtolower($errorInfo[2]), 'email') !== false) return 'ایمیل قبلاً استفاده شده است.';
                    if (strpos(strtolower($errorInfo[2]), 'affiliate_code') !== false && $role === 'affiliate') {
                        return 'خطا در تولید کد همکاری، لطفاً دوباره تلاش کنید.';
                    }
                }
                error_log("UserModel::register - DB Error: " . print_r($errorInfo, true));
                return 'خطا در ثبت کاربر در پایگاه داده.';
            }
        } catch (PDOException $e) {
            error_log("UserModel::register - PDOException: " . $e->getMessage());
            if ($e->getCode() == '23000') { 
                 if (strpos(strtolower($e->getMessage()), 'username') !== false) return 'نام کاربری قبلاً استفاده شده است.';
                 if (strpos(strtolower($e->getMessage()), 'email') !== false) return 'ایمیل قبلاً استفاده شده است.';
            }
            return 'خطای پایگاه داده هنگام ثبت کاربر.';
        }
    }

// فایل: app/models/User.php
// داخل متد: public function login($usernameOrEmail, $password)

public function login($usernameOrEmail, $password) {
    // می‌توانید کدهای var_dump یا echo مربوط به اشکال‌زدایی را حذف کنید یا کامنت کنید.

    // استفاده از کوئری با نام‌های پارامتر مجزا
    $this->db->query('SELECT * FROM users WHERE (username = :u_name OR email = :u_email) AND status = 1');
    $this->db->bind(':u_name', $usernameOrEmail);
    $this->db->bind(':u_email', $usernameOrEmail); // هر دو با مقدار ورودی bind می‌شوند
    $row = $this->db->single();

    if ($row) {
        $hashed_password_from_db = $row['password'];
        if (password_verify($password, $hashed_password_from_db)) {
            return $row; // ورود موفق
        }
    }
    return false; // کاربر پیدا نشد یا رمز عبور اشتباه است
}
    

    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    public function findUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', (int)$id);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }
    
    public function getUsersByRole($role) {
        $this->db->query('SELECT id, username, first_name, last_name, email, role, status FROM users WHERE role = :role ORDER BY username');
        $this->db->bind(':role', $role);
        return $this->db->resultSet() ?: [];
    }

    public function generateUniqueAffiliateCode($length = 8) {
        $max_tries = 5; 
        $try_count = 0;
        do {
            $randomString = '';
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            $this->db->query("SELECT id FROM users WHERE affiliate_code = :code");
            $this->db->bind(':code', $randomString);
            $this->db->execute();
            $try_count++;
            if ($try_count > $max_tries) {
                return 'AFF' . time() . rand(100,999); 
            }
        } while ($this->db->rowCount() > 0);
        return $randomString;
    }
    
    public function findUserByAffiliateCode($affiliate_code) {
        $this->db->query('SELECT * FROM users WHERE affiliate_code = :affiliate_code AND role = "affiliate" AND status = 1');
        $this->db->bind(':affiliate_code', $affiliate_code);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    public function getAffiliateWithdrawableBalance($affiliate_id) {
        $user = $this->findUserById((int)$affiliate_id);
        if ($user && isset($user['affiliate_balance'])) {
            return (float)$user['affiliate_balance'];
        }
        error_log("UserModel::getAffiliateWithdrawableBalance - User or affiliate_balance not found for ID: {$affiliate_id}");
        return 0.00; 
    }

    public function updateAffiliateBalance($affiliate_id, $amount_change) {
        $affiliate_id = (int)$affiliate_id;
        $amount_change = (float)$amount_change;

        $this->db->query("UPDATE users SET affiliate_balance = affiliate_balance + :amount_change, updated_at = NOW() 
                          WHERE id = :affiliate_id");
        $this->db->bind(':amount_change', $amount_change);
        $this->db->bind(':affiliate_id', $affiliate_id);

        if ($this->db->execute()) {
            if ($this->db->rowCount() > 0) {
                error_log("UserModel::updateAffiliateBalance - Balance updated for affiliate ID {$affiliate_id} by {$amount_change}.");
                return true;
            } else {
                error_log("UserModel::updateAffiliateBalance - No rows affected. Affiliate ID {$affiliate_id} not found or balance unchanged.");
                return false; 
            }
        } else {
            $db_error_info = $this->db->getErrorInfo();
            error_log("UserModel::updateAffiliateBalance - DB Error for affiliate ID {$affiliate_id}: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'Unknown DB error')));
            return false;
        }
    }
    // در فایل app/models/User.php
public function updateVendorBalance($vendor_id, $amount_change) {
    $vendor_id = (int)$vendor_id;
    $amount_change = (float)$amount_change;

    $this->db->query("UPDATE users SET vendor_balance = vendor_balance + :amount_change, updated_at = NOW() 
                      WHERE id = :vendor_id AND role = 'vendor'"); // اطمینان از اینکه فقط فروشنده آپدیت می‌شود
    $this->db->bind(':amount_change', $amount_change);
    $this->db->bind(':vendor_id', $vendor_id);

    if ($this->db->execute()) {
        if ($this->db->rowCount() > 0) {
            error_log("UserModel::updateVendorBalance - Balance updated for vendor ID {$vendor_id} by {$amount_change}.");
            return true;
        } else {
            error_log("UserModel::updateVendorBalance - No rows affected. Vendor ID {$vendor_id} not found or balance unchanged.");
            return false; 
        }
    } else {
        // ... (لاگ خطا مشابه updateAffiliateBalance) ...
        return false;
    }
}
}
?>
