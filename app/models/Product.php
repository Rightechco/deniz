<?php
// app/models/Product.php

class Product {
    private $db;

    public function __construct() { 
        if (class_exists('Database')) {
            $this->db = new Database();
        } else {
            die("Fatal Error: Database class not found in Product model.");
        }
    }

    public function getAllProducts() { 
        $this->db->query("SELECT p.*, 
                                 c.name as category_name,
                                 u.username as vendor_username, 
                                 CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          LEFT JOIN users u ON p.vendor_id = u.id
                          ORDER BY p.created_at DESC");
        return $this->db->resultSet() ?: [];
    }

    public function getProductById($id) { 
        // اطمینان از انتخاب تمام ستون‌های لازم از جمله vendor_id, affiliate_commission_type, affiliate_commission_value
        $this->db->query("SELECT p.*, 
                                 c.name as category_name,
                                 u.username as vendor_username,
                                 CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          LEFT JOIN users u ON p.vendor_id = u.id
                          WHERE p.id = :id");
        $this->db->bind(':id', (int)$id);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    public function getProductsByCategoryId($category_id) { 
        $this->db->query("SELECT p.*, c.name as category_name,
                                 u.username as vendor_username, 
                                 CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          LEFT JOIN users u ON p.vendor_id = u.id
                          WHERE p.category_id = :category_id
                          ORDER BY p.created_at DESC");
        $this->db->bind(':category_id', (int)$category_id);
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }
    
    public function getProductsByVendorId($vendor_id) {
        $this->db->query("SELECT p.*, c.name as category_name 
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          WHERE p.vendor_id = :vendor_id
                          ORDER BY p.created_at DESC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    public function decreaseStock($product_id, $quantity_to_decrease) {
        $product_id = (int)$product_id;
        $quantity_to_decrease = (int)$quantity_to_decrease;

        error_log("ProductModel::decreaseStock for SIMPLE product_id: {$product_id}, quantity: {$quantity_to_decrease}");
        
        $this->db->query("SELECT stock_quantity FROM products WHERE id = :id");
        $this->db->bind(':id', $product_id);
        $product_stock_data = $this->db->single();

        if ($product_stock_data && (int)$product_stock_data['stock_quantity'] >= $quantity_to_decrease) {
            $this->db->query("UPDATE products SET stock_quantity = stock_quantity - :quantity 
                              WHERE id = :id AND stock_quantity >= :quantity_check");
            $this->db->bind(':quantity', $quantity_to_decrease);
            $this->db->bind(':id', $product_id);
            $this->db->bind(':quantity_check', $quantity_to_decrease); 
            if ($this->db->execute()) {
                error_log("ProductModel::decreaseStock - UPDATE executed. Rows affected: " . $this->db->rowCount() . " for product_id: {$product_id}");
                return $this->db->rowCount() > 0;
            } else {
                 error_log("ProductModel::decreaseStock - UPDATE failed for product_id: {$product_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
            }
        } else {
            if (!$product_stock_data) {
                error_log("ProductModel::decreaseStock - Product not found for id: {$product_id}");
            } else {
                error_log("ProductModel::decreaseStock - Not enough stock for simple product_id: {$product_id}. Current: {$product_stock_data['stock_quantity']}, Requested: {$quantity_to_decrease}");
            }
        }
        return false;
    }

    public function addProduct($data) {
        $this->db->query('INSERT INTO products (name, description, price, image_url, stock_quantity, initial_stock_quantity, category_id, product_type, vendor_id, affiliate_commission_type, affiliate_commission_value)
                          VALUES (:name, :description, :price, :image_url, :stock_quantity, :initial_stock_quantity, :category_id, :product_type, :vendor_id, :affiliate_commission_type, :affiliate_commission_value)');
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':price', ($data['product_type'] == 'variable' || $data['price'] === '' || $data['price'] === null) ? null : (float)$data['price']);
        $this->db->bind(':image_url', $data['image_url']);
        
        $stock_qty = ($data['product_type'] == 'variable' && !isset($data['stock_quantity_explicit'])) ? 0 : (isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0);
        $this->db->bind(':stock_quantity', $stock_qty);
        $this->db->bind(':initial_stock_quantity', isset($data['initial_stock_quantity']) ? (int)$data['initial_stock_quantity'] : $stock_qty); 
        
        $this->db->bind(':category_id', (empty($data['category_id'])) ? null : (int)$data['category_id']);
        $this->db->bind(':product_type', $data['product_type']);
        $this->db->bind(':vendor_id', isset($data['vendor_id']) ? (int)$data['vendor_id'] : null);
        $this->db->bind(':affiliate_commission_type', $data['affiliate_commission_type']);
        $this->db->bind(':affiliate_commission_value', ($data['affiliate_commission_type'] == 'none' || empty($data['affiliate_commission_value'])) ? null : (float)$data['affiliate_commission_value']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        error_log("ProductModel::addProduct failed. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        return false;
    }

    public function updateProduct($data) {
        $sql = 'UPDATE products SET
                    name = :name, description = :description, price = :price, image_url = :image_url,
                    stock_quantity = :stock_quantity, category_id = :category_id, product_type = :product_type,
                    vendor_id = :vendor_id,
                    affiliate_commission_type = :affiliate_commission_type, affiliate_commission_value = :affiliate_commission_value';
        
        // Only update initial_stock_quantity if it's explicitly provided for edit
        if (isset($data['initial_stock_quantity'])) { 
             $sql .= ", initial_stock_quantity = :initial_stock_quantity";
        }
        
        $sql .= ' WHERE id = :id';
        $this->db->query($sql);
        
        $this->db->bind(':id', (int)$data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':price', ($data['product_type'] == 'variable' || $data['price'] === '' || $data['price'] === null) ? null : (float)$data['price']);
        $this->db->bind(':image_url', $data['image_url']);
        
        if ($data['product_type'] == 'variable') {
            $this->db->bind(':stock_quantity', (isset($data['stock_quantity']) && $data['stock_quantity'] !== '') ? (int)$data['stock_quantity'] : 0);
        } else { 
            $this->db->bind(':stock_quantity', (int)$data['stock_quantity']);
        }
        
        if (isset($data['initial_stock_quantity'])) {
           $this->db->bind(':initial_stock_quantity', (int)$data['initial_stock_quantity']);
        }

        $this->db->bind(':category_id', (empty($data['category_id'])) ? null : (int)$data['category_id']);
        $this->db->bind(':product_type', $data['product_type']);
        $this->db->bind(':vendor_id', isset($data['vendor_id']) ? (int)$data['vendor_id'] : null);
        $this->db->bind(':affiliate_commission_type', $data['affiliate_commission_type']);
        $this->db->bind(':affiliate_commission_value', ($data['affiliate_commission_type'] == 'none' || empty($data['affiliate_commission_value'])) ? null : (float)$data['affiliate_commission_value']);

        if($this->db->execute()){
            return true;
        }
        error_log("ProductModel::updateProduct failed for ID {$data['id']}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        return false;
    }

    public function deleteProduct($id) {
        $this->db->query('DELETE FROM products WHERE id = :id');
        $this->db->bind(':id', (int)$id);
        if ($this->db->execute()) { return $this->db->rowCount() > 0; }
        error_log("ProductModel::deleteProduct failed for ID {$id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        return false;
    }

    public function getSalesCount($product_id) {
        $this->db->query("SELECT SUM(oi.quantity) as total_sold 
                          FROM order_items oi
                          JOIN orders o ON oi.order_id = o.id
                          WHERE oi.product_id = :product_id 
                          AND oi.variation_id IS NULL 
                          AND o.order_status NOT IN ('cancelled', 'refunded', 'pending_confirmation')");
        $this->db->bind(':product_id', (int)$product_id);
        $result = $this->db->single();
        return $result && isset($result['total_sold']) ? (int)$result['total_sold'] : 0;
    }

    /**
     * Get products for export, optionally filtered by date range (based on created_at).
     * @param string|null $startDate YYYY-MM-DD HH:MM:SS
     * @param string|null $endDate YYYY-MM-DD HH:MM:SS
     * @return array
     */
    public function getProductsForExport($startDate = null, $endDate = null) {
        $sql = "SELECT p.*, 
                       c.name as category_name,
                       u.username as vendor_username, 
                       CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.vendor_id = u.id";
        
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = "p.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "p.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $this->db->query($sql);
        // Bind parameters if any
        if (!empty($params)) {
            foreach($params as $key => $value){
                $this->db->bind($key, $value);
            }
        }
        
        $results = $this->db->resultSet(); // Pass params to execute if your resultSet doesn't do it
        return $results ? $results : [];
    }
}
// تگ پایانی PHP را حذف کنید
