<?php
// app/models/Order.php

class Order {
    private $db;
    private $productModel; 
    private $userModel; 

    public function __construct() {
        if (class_exists('Database')) { 
            $this->db = new Database(); 
        } else { 
            error_log("OrderModel FATAL ERROR: Database class not found.");
            throw new Exception("Fatal Error: Database class not found in Order model.");
        }
        if (class_exists('Product')) { 
            $this->productModel = new Product(); 
        } else { 
            error_log("OrderModel CONSTRUCT WARNING: Product class was NOT found. Product-related operations in Order model might fail."); 
            $this->productModel = null; 
        }
        if (class_exists('User')) { 
            $this->userModel = new User(); 
        } else { 
            error_log("OrderModel CONSTRUCT WARNING: User class was NOT found. User-related operations in Order model might fail."); 
            $this->userModel = null; 
        }
    }

    public function createOrder($data, $affiliate_placing_order_id = null) {
        if (!$this->productModel && class_exists('Product')) { 
            $this->productModel = new Product(); 
        }
        if (!$this->userModel && class_exists('User')) { 
            $this->userModel = new User(); 
        }

        if (!$this->productModel || !$this->userModel) { 
             error_log("OrderModel::createOrder - ProductModel or UserModel is STILL not available. Cannot proceed.");
             return false; 
        }
        
        $platform_rate_defined = defined('PLATFORM_COMMISSION_RATE') ? (float)PLATFORM_COMMISSION_RATE : 0.0;
        // error_log("OrderModel::createOrder - Platform Commission Rate being used: " . $platform_rate_defined); // Keep for debugging if needed
        
        if (method_exists($this->db, 'beginTransaction')) {
            $this->db->beginTransaction();
        }

        try {
            $this->db->query('INSERT INTO orders (user_id, first_name, last_name, email, phone, address, city, postal_code, total_amount, payment_method, notes, order_status, payment_status, placed_by_affiliate_id, created_at, updated_at)
                              VALUES (:user_id, :first_name, :last_name, :email, :phone, :address, :city, :postal_code, :total_amount, :payment_method, :notes, :order_status, :payment_status, :placed_by_affiliate_id, NOW(), NOW())');
            
            if (!isset($data['user_id']) || empty($data['user_id'])) {
                error_log("OrderModel::createOrder - CRITICAL: 'user_id' is missing or empty in data array for orders table. Data: " . print_r($data, true));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false; 
            }
            $this->db->bind(':user_id', (int)$data['user_id']); 
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':address', $data['address']);
            $this->db->bind(':city', $data['city']);
            $this->db->bind(':postal_code', $data['postal_code']);
            if (!array_key_exists('total_price', $data) || $data['total_price'] === null) {
                error_log("OrderModel::createOrder - CRITICAL: 'total_price' is missing or null in data array. Data: " . print_r($data, true));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false; 
            }
            $this->db->bind(':total_amount', (float)$data['total_price']); 
            $this->db->bind(':payment_method', $data['payment_method']);
            $this->db->bind(':notes', $data['notes']);
            $this->db->bind(':order_status', $data['order_status'] ?? 'pending_confirmation'); 
            $this->db->bind(':payment_status', $data['payment_status'] ?? ($data['payment_method'] == 'cod' ? 'pending_on_delivery' : 'pending'));
            $this->db->bind(':placed_by_affiliate_id', $affiliate_placing_order_id ? (int)$affiliate_placing_order_id : null);

            if (!$this->db->execute()) { 
                $db_error_info = $this->db->getErrorInfo();
                error_log("OrderModel::createOrder - Failed to insert into orders table. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false; 
            }
            $order_id = $this->db->lastInsertId();
            if (!$order_id) { 
                error_log("OrderModel::createOrder - lastInsertId() returned invalid ID after inserting into orders table.");
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false; 
            }
            // error_log("OrderModel::createOrder - Order #{$order_id} created. Processing items...");

            $affiliate_id_for_this_order = null;
            if ($affiliate_placing_order_id !== null) { 
                $affiliate_id_for_this_order = (int)$affiliate_placing_order_id;
            } elseif (isset($_SESSION['referred_by_affiliate_code']) && !empty($_SESSION['referred_by_affiliate_code'])) { 
                $affiliateUser = $this->userModel->findUserByAffiliateCode($_SESSION['referred_by_affiliate_code']);
                if ($affiliateUser && isset($affiliateUser['id'])) {
                    $affiliate_id_for_this_order = (int)$affiliateUser['id'];
                } else {
                    error_log("OrderModel::createOrder - Order #{$order_id}: Affiliate code {$_SESSION['referred_by_affiliate_code']} in session, but no user found with this code.");
                }
            }

            foreach ($data['cart_items'] as $item_cart_id => $cart_item) {
                if (!isset($cart_item['product_id'], $cart_item['name'], $cart_item['quantity'], $cart_item['price']) || 
                    !is_numeric($cart_item['product_id']) || !is_numeric($cart_item['quantity']) || !is_numeric($cart_item['price'])) { 
                    error_log("OrderModel::createOrder - Invalid cart item structure for order #{$order_id}. Item: " . print_r($cart_item, true));
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack(); 
                    return false; 
                }
                
                $product_details = $this->productModel->getProductById((int)$cart_item['product_id']);
                
                if (!$product_details) { 
                    error_log("OrderModel::createOrder - CRITICAL for order #{$order_id}: Product details not found for product_id: " . $cart_item['product_id']);
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return false; 
                }

                $vendor_id_for_item = (isset($product_details['vendor_id']) && !empty($product_details['vendor_id'])) ? (int)$product_details['vendor_id'] : null;
                $item_sub_total = (float)$cart_item['price'] * (int)$cart_item['quantity'];
                
                $platform_commission_rate_to_store = ($platform_rate_defined > 0) ? $platform_rate_defined : null; 
                $platform_commission_amount_for_item = 0.00; 
                $vendor_earning_for_item = 0.00; 

                if ($vendor_id_for_item !== null) { 
                    if ($platform_rate_defined > 0) {
                        $platform_commission_amount_for_item = round($item_sub_total * $platform_rate_defined, 2); 
                        $vendor_earning_for_item = $item_sub_total - $platform_commission_amount_for_item;
                    } else { 
                        $vendor_earning_for_item = $item_sub_total; 
                    }
                }

                $this->db->query('INSERT INTO order_items 
                                    (order_id, product_id, variation_id, product_name, quantity, price_at_purchase, sub_total, 
                                     vendor_id, platform_commission_rate, platform_commission_amount, vendor_earning, payout_status)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                
                $params_order_item = [
                    $order_id, (int)$cart_item['product_id'],
                    (isset($cart_item['variation_id']) && !empty($cart_item['variation_id'])) ? (int)$cart_item['variation_id'] : null,
                    $cart_item['name'], (int)$cart_item['quantity'], (float)$cart_item['price'], 
                    $item_sub_total, $vendor_id_for_item,
                    $platform_commission_rate_to_store, $platform_commission_amount_for_item, 
                    $vendor_earning_for_item, 'unpaid' 
                ];

                if (!$this->db->execute($params_order_item)) { 
                    $db_error_info = $this->db->getErrorInfo();
                    error_log("OrderModel::createOrder - Failed to insert order_item for order #{$order_id}. DBError:" . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return false; 
                }
                $order_item_id = $this->db->lastInsertId();
                // error_log("OrderModel::createOrder - OrderItem #{$order_item_id} for Order #{$order_id} created.");

                if ($affiliate_id_for_this_order && $product_details && 
                    isset($product_details['affiliate_commission_type']) && $product_details['affiliate_commission_type'] !== 'none' &&
                    isset($product_details['affiliate_commission_value']) && (float)$product_details['affiliate_commission_value'] > 0) {
                    
                    $this->recordAffiliateCommission(
                        $affiliate_id_for_this_order, $order_id, $order_item_id,
                        (int)$cart_item['product_id'], $item_sub_total, (int)$cart_item['quantity'], 
                        $product_details['affiliate_commission_type'], (float)$product_details['affiliate_commission_value']
                    );
                }
            } 

            if (method_exists($this->db, 'commit')) $this->db->commit();
            
            if ($affiliate_id_for_this_order && !$affiliate_placing_order_id) { 
                 unset($_SESSION['referred_by_affiliate_code']);
            }
            return $order_id; 
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in OrderModel::createOrder: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            return false;
        }
    }

    public function recordAffiliateCommission($affiliate_id, $order_id, $order_item_id, $product_id, $item_sub_total, $item_quantity, $product_commission_type, $product_commission_value) {
        if (empty($affiliate_id) || empty($product_commission_type) || $product_commission_type === 'none' || $product_commission_value <= 0) {
            return false; 
        }
        $commission_earned = 0.0; 
        $commission_type_at_sale = $product_commission_type; 
        $commission_value_at_sale = $product_commission_value; 

        if ($product_commission_type === 'percentage') {
            $commission_earned = round($item_sub_total * ($product_commission_value / 100), 2);
        } elseif ($product_commission_type === 'fixed_amount') { 
            $commission_earned = round((float)$product_commission_value * (int)$item_quantity, 2);
        } else { return false; }

        if ($commission_earned > 0) {
            $this->db->query("INSERT INTO affiliate_commissions 
                                (affiliate_id, order_id, order_item_id, product_id, 
                                 sale_amount, commission_type_at_sale, commission_value_at_sale, commission_earned, 
                                 status, created_at, updated_at)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $params = [
                (int)$affiliate_id, (int)$order_id, (int)$order_item_id, (int)$product_id,
                (float)$item_sub_total, $commission_type_at_sale, (float)$commission_value_at_sale, 
                (float)$commission_earned, 'pending' 
            ];
            if (!$this->db->execute($params)) {
                $db_error_info = $this->db->getErrorInfo();
                error_log("OrderModel::recordAffiliateCommission - FAILED to insert. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                return false;
            }
            return true;
        }
        return false;
    }

    public function getOrdersByUserId($user_id) {
        $this->db->query("SELECT o.*, u.username as customer_username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.user_id = :user_id ORDER BY o.created_at DESC");
        $this->db->bind(':user_id', (int)$user_id);
        return $this->db->resultSet() ?: [];
    }

    public function getOrderDetailsById($order_id, $user_id = null, $vendor_id = null) {
        $sql = "SELECT * FROM orders WHERE id = :order_id";
        if ($user_id !== null) { $sql .= " AND user_id = :user_id"; }
        $this->db->query($sql);
        $this->db->bind(':order_id', (int)$order_id);
        if ($user_id !== null) { $this->db->bind(':user_id', (int)$user_id); }
        $order = $this->db->single();

        if ($order) {
            $items_sql = "SELECT oi.id as order_item_id, oi.order_id, oi.product_id, oi.variation_id, 
                                 oi.product_name, oi.quantity, oi.price_at_purchase, oi.sub_total, 
                                 oi.vendor_id, oi.platform_commission_rate, oi.platform_commission_amount, 
                                 oi.vendor_earning, oi.payout_status, oi.payout_id,
                                 p.image_url as product_image_url, 
                                 pv.image_url as variation_image_url, pv.sku as variation_sku 
                          FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id
                          LEFT JOIN product_variations pv ON oi.variation_id = pv.id 
                          WHERE oi.order_id = :order_id";
            $this->db->query($items_sql); 
            $this->db->bind(':order_id', (int)$order_id);
            $order_items = $this->db->resultSet();
            $filtered_items = [];
            if ($order_items) {
                foreach($order_items as $item) {
                    $item['display_image_url'] = !empty($item['variation_image_url']) ? $item['variation_image_url'] : (!empty($item['product_image_url']) ? $item['product_image_url'] : null);
                    if ($vendor_id !== null) { 
                        if (isset($item['vendor_id']) && (int)$item['vendor_id'] == (int)$vendor_id) { $filtered_items[] = $item; }
                    } else { $filtered_items[] = $item; }
                }
            }
            $order['items'] = $filtered_items;
            if ($vendor_id !== null && empty($order['items'])) { return false; }
            return $order;
        }
        return false; 
    }
    
    public function getAllOrders() {
        $this->db->query("SELECT o.*, u.username as customer_username, u.email as customer_email,
                                 CONCAT(u.first_name, ' ', u.last_name) as customer_full_name
                          FROM orders o
                          LEFT JOIN users u ON o.user_id = u.id 
                          ORDER BY o.created_at DESC");
        return $this->db->resultSet() ?: [];
    }

    public function updateOrderStatus($order_id, $order_status, $payment_status = null) {
        $sql = "UPDATE orders SET order_status = :order_status";
        if ($payment_status !== null) { $sql .= ", payment_status = :payment_status"; }
        $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE id = :order_id"; 
        $this->db->query($sql);
        $this->db->bind(':order_status', $order_status);
        if ($payment_status !== null) { $this->db->bind(':payment_status', $payment_status); }
        $this->db->bind(':order_id', (int)$order_id);
        return $this->db->execute();
    }

    public function getOrdersForVendor($vendor_id) {
        $this->db->query("SELECT o.id as order_id, o.user_id, o.total_amount, o.order_status, o.payment_status, o.created_at as order_date,
                                 u.username as customer_username, u.email as customer_email, 
                                 CONCAT(u.first_name, ' ', u.last_name) as customer_full_name
                          FROM orders o
                          JOIN users u ON o.user_id = u.id
                          WHERE EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.vendor_id = :vendor_id)
                          ORDER BY o.created_at DESC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        return $this->db->resultSet() ?: [];
    }

    public function getVendorWithdrawableBalance($vendor_id) {
        $valid_order_statuses = ['delivered', 'shipped', 'completed']; 
        $paid_payment_status = 'paid';
        $unpaid_payout_status = 'unpaid';

        $quoted_statuses = [];
        foreach ($valid_order_statuses as $status) {
            if (preg_match('/^[a-zA-Z0-9_]+$/', $status)) {
                $quoted_statuses[] = "'" . $status . "'";
            }
        }
        if (empty($quoted_statuses)) {
            error_log("OrderModel::getVendorWithdrawableBalance - No valid statuses provided for IN clause.");
            return 0.00;
        }
        $status_in_clause = implode(',', $quoted_statuses);

        $this->db->query("SELECT SUM(oi.vendor_earning) as total_withdrawable
                          FROM order_items oi
                          JOIN orders o ON oi.order_id = o.id
                          WHERE oi.vendor_id = :vendor_id
                          AND oi.payout_status = :payout_status_unpaid
                          AND o.order_status IN ({$status_in_clause}) 
                          AND o.payment_status = :payment_status_paid"); 

        $this->db->bind(':vendor_id', (int)$vendor_id);
        $this->db->bind(':payout_status_unpaid', $unpaid_payout_status);
        $this->db->bind(':payment_status_paid', $paid_payment_status);   

        $result = $this->db->single();
        return $result && isset($result['total_withdrawable']) && $result['total_withdrawable'] !== null ? (float)$result['total_withdrawable'] : 0.00;
    }

    public function getUnpaidOrderItemsForVendor($vendor_id) {
        $valid_order_statuses = ['delivered', 'shipped', 'completed'];
        $paid_payment_status = 'paid';
        $unpaid_payout_status = 'unpaid';

        $quoted_statuses = [];
        foreach ($valid_order_statuses as $status) {
             if (preg_match('/^[a-zA-Z0-9_]+$/', $status)) {
                $quoted_statuses[] = "'" . $status . "'";
            }
        }
        if (empty($quoted_statuses)) {
            error_log("OrderModel::getUnpaidOrderItemsForVendor - No valid statuses provided for IN clause.");
            return []; 
        }
        $status_in_clause = implode(',', $quoted_statuses);

        $this->db->query("SELECT oi.id as order_item_id, oi.order_id, oi.product_name, oi.quantity, oi.vendor_earning, o.created_at as order_date
                          FROM order_items oi
                          JOIN orders o ON oi.order_id = o.id
                          WHERE oi.vendor_id = :vendor_id
                          AND oi.payout_status = :payout_status_unpaid
                          AND o.order_status IN ({$status_in_clause})
                          AND o.payment_status = :payment_status_paid
                          ORDER BY o.created_at ASC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        $this->db->bind(':payout_status_unpaid', $unpaid_payout_status);
        $this->db->bind(':payment_status_paid', $paid_payment_status); // Ensure this is bound
        
        return $this->db->resultSet() ?: [];
    }

  public function requestVendorPayout($vendor_id, $requested_amount, $order_item_ids, $payout_method = 'bank_transfer', $payment_details = null) {
        if (method_exists($this->db, 'beginTransaction')) $this->db->beginTransaction();
        try {
            // Assuming vendor_payouts table has 'requested_at' but not 'updated_at' to be set on initial insert.
            // 'processed_at' will be NULL initially.
            $this->db->query("INSERT INTO vendor_payouts (vendor_id, requested_amount, payout_method, payment_details, status, requested_at) 
                              VALUES (?, ?, ?, ?, ?, NOW())"); // Removed updated_at
            $params_payout = [
                (int)$vendor_id, 
                (float)$requested_amount, 
                $payout_method, 
                $payment_details, 
                'requested'
            ];
            if (!$this->db->execute($params_payout)) {
                $db_error_info = $this->db->getErrorInfo();
                error_log("OrderModel::requestVendorPayout - Failed to insert into vendor_payouts. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'db_error_payout_insert';
            }
            $payout_id = $this->db->lastInsertId();
            if (!$payout_id) {
                error_log("OrderModel::requestVendorPayout - Failed to get lastInsertId for vendor_payouts.");
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'db_error_payout_id';
            }

            if (!empty($order_item_ids) && is_array($order_item_ids)) {
                $sanitized_item_ids = array_map('intval', $order_item_ids);
                if (!empty($sanitized_item_ids)) { // Ensure there are items after sanitization
                    $placeholders = rtrim(str_repeat('?,', count($sanitized_item_ids)), ',');
                    
                    // Assuming order_items has an updated_at column that auto-updates or is set by other triggers.
                    // If not, and you want to track this change, add "updated_at = NOW()" here too.
                    $sql_update_items = "UPDATE order_items 
                                         SET payout_status = ?, payout_id = ?, updated_at = NOW() 
                                         WHERE vendor_id = ? AND payout_status = ? AND id IN ({$placeholders})";
                    $this->db->query($sql_update_items);
                    $params_update_items = ['requested', $payout_id, (int)$vendor_id, 'unpaid'];
                    foreach ($sanitized_item_ids as $item_id) { $params_update_items[] = $item_id; }

                    if (!$this->db->execute($params_update_items)) {
                        $db_error_info = $this->db->getErrorInfo();
                        error_log("OrderModel::requestVendorPayout - Failed to update order_items for payout_id: {$payout_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                        if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                        // Attempt to delete the payout request if items could not be updated
                        $this->db->query("DELETE FROM vendor_payouts WHERE id = :p_id_del_on_fail"); 
                        $this->db->bind(':p_id_del_on_fail', $payout_id); 
                        $this->db->execute();
                        return 'db_error_item_update';
                    }
                    if ($this->db->rowCount() != count($sanitized_item_ids)) {
                        error_log("OrderModel::requestVendorPayout - Mismatch in updated order_items count for payout_id: {$payout_id}. Expected: " . count($sanitized_item_ids) . ", Affected: " . $this->db->rowCount());
                        // This might not be a fatal error, but worth logging.
                    }
                } else { // No valid item IDs after sanitization
                     error_log("OrderModel::requestVendorPayout - No valid order_item_ids after sanitization for payout_id: {$payout_id}.");
                    // Delete the payout request
                    $this->db->query("DELETE FROM vendor_payouts WHERE id = :p_id_del_no_items"); 
                    $this->db->bind(':p_id_del_no_items', $payout_id); 
                    $this->db->execute();
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return 'no_valid_items_for_payout';
                }
            } else { 
                error_log("OrderModel::requestVendorPayout - No order_item_ids provided for payout_id: {$payout_id} while requested_amount was {$requested_amount}.");
                // Delete the payout request as it's invalid without items
                $this->db->query("DELETE FROM vendor_payouts WHERE id = :p_id_del_no_items"); 
                $this->db->bind(':p_id_del_no_items', $payout_id); 
                $this->db->execute();
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'no_items_for_payout';
            }
            if (method_exists($this->db, 'commit')) $this->db->commit();
            return (int)$payout_id;
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in OrderModel::requestVendorPayout: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            return 'general_exception';
        }
    }

   public function getPayoutRequestsByVendorId($vendor_id) {
        $this->db->query("SELECT * FROM vendor_payouts WHERE vendor_id = :vendor_id ORDER BY requested_at DESC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        return $this->db->resultSet() ?: [];
    }

    public function getAllPayoutRequests($startDate = null, $endDate = null, $status = null) {
        $sql = "SELECT vp.*, u.username as vendor_username, u.email as vendor_email, 
                       CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                FROM vendor_payouts vp
                JOIN users u ON vp.vendor_id = u.id";
        
        $conditions = [];
        $params = []; 

        if ($startDate) {
            $conditions[] = "DATE(vp.requested_at) >= :start_date"; 
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "DATE(vp.requested_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        if ($status && !empty($status)) {
            $conditions[] = "vp.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY vp.requested_at DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach($params as $key => $value){
                $this->db->bind($key, $value);
            }
        }
        return $this->db->resultSet() ?: [];
    }

    public function getPayoutRequestById($payout_id) {
        $this->db->query("SELECT vp.*, u.username as vendor_username, u.email as vendor_email,
                                 CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                          FROM vendor_payouts vp
                          JOIN users u ON vp.vendor_id = u.id
                          WHERE vp.id = :payout_id");
        $this->db->bind(':payout_id', (int)$payout_id);
        return $this->db->single() ?: false;
    }
    
    public function getOrderItemsByPayoutId($payout_id) {
        $this->db->query("SELECT oi.id as order_item_id, oi.order_id, oi.product_id, oi.variation_id, 
                                 oi.product_name, oi.quantity, oi.price_at_purchase, /* oi.price_per_unit, */ oi.sub_total,
                                 oi.vendor_id, oi.platform_commission_rate, oi.platform_commission_amount, 
                                 oi.vendor_earning, oi.payout_status, oi.payout_id,
                                 p.name as parent_product_name, 
                                 pv.sku as variation_sku 
                          FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id
                          LEFT JOIN product_variations pv ON oi.variation_id = pv.id
                          WHERE oi.payout_id = :payout_id");
        $this->db->bind(':payout_id', (int)$payout_id);
        return $this->db->resultSet() ?: [];
    }

    public function processVendorPayout($payout_id, $new_status, $admin_user_id, $payout_amount_paid = null, $admin_notes = null, $payment_details_admin = null) {
        // ... (userModel check as before) ...
        if (!$this->userModel && class_exists('User')) { 
            $this->userModel = new User();
        }
        if (!$this->userModel) {
            error_log("OrderModel::processVendorPayout - UserModel is not available. Cannot update affiliate balance.");
            return 'user_model_unavailable'; 
        }

        if (method_exists($this->db, 'beginTransaction')) $this->db->beginTransaction();
        try {
            $payoutRequest = $this->getPayoutRequestById($payout_id); // Using existing method (ensure it fetches from vendor_payouts)
            if (!$payoutRequest) {
                error_log("OrderModel::processVendorPayout - Payout request ID {$payout_id} not found.");
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'payout_not_found';
            }
            $vendor_id = (int)$payoutRequest['vendor_id'];

            // Assuming vendor_payouts table has 'processed_at' and NO 'updated_at' that we manage here.
            // If 'updated_at' exists and is auto-updating by DB, no need to set it.
            // If 'updated_at' exists and is NOT auto-updating, add "updated_at = CURRENT_TIMESTAMP"
            $this->db->query("UPDATE vendor_payouts 
                              SET status = :status, 
                                  payout_amount = :payout_amount, 
                                  notes = :notes, 
                                  payment_details = :payment_details_admin,
                                  processed_at = CURRENT_TIMESTAMP, 
                                  processed_by_admin_id = :processed_by_admin_id
                                  -- Removed explicit updated_at = CURRENT_TIMESTAMP, assuming it auto-updates or doesn't exist
                              WHERE id = :payout_id");
            $this->db->bind(':status', $new_status);
            $this->db->bind(':payout_amount', ($payout_amount_paid !== null) ? (float)$payout_amount_paid : null);
            $this->db->bind(':notes', $admin_notes);
            $this->db->bind(':payment_details_admin', $payment_details_admin);
            $this->db->bind(':processed_by_admin_id', (int)$admin_user_id); 
            $this->db->bind(':payout_id', (int)$payout_id);

            if (!$this->db->execute()) {
                $db_error_info = $this->db->getErrorInfo();
                error_log("OrderModel::processVendorPayout - Failed to update vendor_payouts for payout_id: {$payout_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'db_error_payout_update';
            }
            // error_log("OrderModel::processVendorPayout - vendor_payouts table updated for ID {$payout_id} to status {$new_status}.");

            if ($new_status === 'completed') {
                // This part is for vendors, affiliate balance is not directly relevant here unless vendors are also affiliates.
                // If there's a vendor balance to update in users table, that logic would be similar to affiliate_balance.
                // For now, focusing on marking order_items as paid.

                $this->db->query("UPDATE order_items 
                                  SET payout_status = :payout_status_paid, updated_at = NOW()
                                  WHERE payout_id = :payout_id AND payout_status = :payout_status_requested"); 
                $this->db->bind(':payout_status_paid', 'paid');
                $this->db->bind(':payout_id', (int)$payout_id);
                $this->db->bind(':payout_status_requested', 'requested'); 
                
                if (!$this->db->execute()) {
                     $db_error_info = $this->db->getErrorInfo();
                     error_log("OrderModel::processVendorPayout - Failed to update order_items payout_status to 'paid' for payout_id: {$payout_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                     if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                     return 'commission_status_update_failed'; // Re-using, but this is for order_items
                }
            } 
            elseif (in_array($new_status, ['rejected', 'cancelled'])) { 
                 $this->db->query("UPDATE order_items 
                                  SET payout_status = :payout_status_unpaid, payout_id = NULL, updated_at = NOW()
                                  WHERE payout_id = :payout_id AND payout_status = :payout_status_requested");
                $this->db->bind(':payout_status_unpaid', 'unpaid');
                $this->db->bind(':payout_id', (int)$payout_id);
                $this->db->bind(':payout_status_requested', 'requested');
                if (!$this->db->execute()) {
                    // Log error but don't necessarily roll back the payout status change itself
                     error_log("OrderModel::processVendorPayout - Failed to revert order_items status for payout_id: {$payout_id}.");
                }
            }

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true; 
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in OrderModel::processVendorPayout: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            return 'general_exception';
        }
    }

    public function getOrdersWithPlatformCommission($startDate = null, $endDate = null) {
        $sql = "SELECT o.id as order_id, o.created_at as order_date, o.total_amount as order_total,
                       u.username as customer_username, 
                       u.email as customer_email, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_full_name,
                       (SELECT SUM(oi.platform_commission_amount) 
                        FROM order_items oi 
                        WHERE oi.order_id = o.id) as total_order_platform_commission
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id";
        
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = "DATE(o.created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "DATE(o.created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY o.created_at DESC";
        
        $this->db->query($sql);
        if (!empty($params)) {
            foreach($params as $key => $value){
                $this->db->bind($key, $value);
            }
        }
        return $this->db->resultSet() ?: [];
    }

    public function getTotalPlatformCommission($startDate = null, $endDate = null) {
        $sql = "SELECT SUM(oi.platform_commission_amount) as grand_total_commission 
                FROM order_items oi";
        $conditions = [];
        $params = [];

        if ($startDate || $endDate) {
            $sql .= " JOIN orders o ON oi.order_id = o.id";
            if ($startDate) {
                $conditions[] = "DATE(o.created_at) >= :start_date";
                $params[':start_date'] = $startDate;
            }
            if ($endDate) {
                $conditions[] = "DATE(o.created_at) <= :end_date";
                $params[':end_date'] = $endDate;
            }
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $this->db->query($sql);
        if (!empty($params)) {
            foreach($params as $key => $value){
                $this->db->bind($key, $value);
            }
        }
        $result = $this->db->single();
        return $result && isset($result['grand_total_commission']) ? (float)$result['grand_total_commission'] : 0.00;
    }

    public function getDetailedOrderItemsForExport($startDate = null, $endDate = null, $orderStatus = null) {
        $sql = "SELECT 
                    o.id as order_id, 
                    o.created_at as order_date,
                    o.total_amount as order_total_amount,
                    o.order_status, 
                    o.payment_status, 
                    o.payment_method,
                    o.first_name as customer_first_name, 
                    o.last_name as customer_last_name, 
                    o.email as customer_email, 
                    o.phone as customer_phone,
                    o.address as shipping_address,
                    o.city as shipping_city,
                    o.postal_code as shipping_postal_code,
                    oi.id as order_item_id, 
                    oi.product_name as item_product_name, 
                    oi.quantity as item_quantity, 
                    oi.price_at_purchase as item_price_at_purchase, 
                    oi.sub_total as item_sub_total,
                    p.id as product_id,
                    pv.id as variation_id,
                    pv.sku as variation_sku,
                    p.vendor_id as item_vendor_id, 
                    CONCAT(u_vendor.first_name, ' ', u_vendor.last_name) as vendor_full_name,
                    u_vendor.username as vendor_username,
                    oi.platform_commission_rate,
                    oi.platform_commission_amount,
                    oi.vendor_earning,
                    oi.payout_status
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_variations pv ON oi.variation_id = pv.id
                LEFT JOIN users u_vendor ON p.vendor_id = u_vendor.id 
                ";
        
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = "DATE(o.created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "DATE(o.created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        if ($orderStatus && !empty($orderStatus)) {
            $conditions[] = "o.order_status = :order_status";
            $params[':order_status'] = $orderStatus;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY o.created_at DESC, oi.id ASC";
        
        $this->db->query($sql);
        if (!empty($params)) {
            foreach($params as $key => $value){
                $this->db->bind($key, $value);
            }
        }
        
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    public function getCommissionsByAffiliateId($affiliate_id, $limit = null) { 
        $sql = "SELECT ac.*, p.name as product_name, o.created_at as order_date
                          FROM affiliate_commissions ac
                          LEFT JOIN products p ON ac.product_id = p.id
                          LEFT JOIN orders o ON ac.order_id = o.id
                          WHERE ac.affiliate_id = :affiliate_id
                          ORDER BY ac.created_at DESC";
        if ($limit && is_numeric($limit) && $limit > 0) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $this->db->query($sql);
        $this->db->bind(':affiliate_id', (int)$affiliate_id);
        return $this->db->resultSet() ?: [];
    }

    public function getPayoutsByAffiliateId($affiliate_id) {
        $this->db->query("SELECT * FROM affiliate_payouts 
                          WHERE affiliate_id = :affiliate_id 
                          ORDER BY requested_at DESC");
        $this->db->bind(':affiliate_id', (int)$affiliate_id);
        return $this->db->resultSet() ?: [];
    }
    
    public function createAffiliatePayoutRequest($affiliate_id, $requested_amount, $payment_details, $payout_method = 'bank_transfer') {
        try {
            $this->db->query("INSERT INTO affiliate_payouts (affiliate_id, requested_amount, payout_method, payment_details, status, requested_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $params = [
                (int)$affiliate_id,
                (float)$requested_amount,
                $payout_method,
                $payment_details,
                'requested' 
            ];
            if ($this->db->execute($params)) {
                return $this->db->lastInsertId();
            } else {
                $db_error_info = $this->db->getErrorInfo();
                error_log("OrderModel::createAffiliatePayoutRequest - Failed to insert into affiliate_payouts. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                return false;
            }
        } catch (Exception $e) {
            error_log("Error in OrderModel::createAffiliatePayoutRequest: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            return false;
        }
    }
    
    public function getAllAffiliateCommissionsWithDetails($filter_status = null) {
        $sql = "SELECT ac.*, u.username as affiliate_username, CONCAT(u.first_name, ' ', u.last_name) as affiliate_full_name,
                       p.name as product_name, o.created_at as order_date
                FROM affiliate_commissions ac
                JOIN users u ON ac.affiliate_id = u.id
                LEFT JOIN products p ON ac.product_id = p.id
                LEFT JOIN orders o ON ac.order_id = o.id";
        
        $params = [];
        if ($filter_status && !empty($filter_status)) {
            $sql .= " WHERE ac.status = :status";
            $params[':status'] = $filter_status;
        }
        $sql .= " ORDER BY ac.created_at DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach($params as $key => $value){
                $this->db->bind($key, $value);
            }
        }
        return $this->db->resultSet() ?: [];
    }

    public function getAffiliateCommissionById($commission_id) {
        $this->db->query("SELECT * FROM affiliate_commissions WHERE id = :commission_id");
        $this->db->bind(':commission_id', (int)$commission_id);
        return $this->db->single() ?: false;
    }

    public function updateAffiliateCommissionStatus($commission_id, $new_status) {
        $allowed_statuses = ['pending', 'approved', 'rejected', 'paid', 'cancelled', 'payout_requested']; 
        if (!in_array($new_status, $allowed_statuses)) {
            error_log("OrderModel::updateAffiliateCommissionStatus - Invalid new status: {$new_status}");
            return false;
        }

        $set_clauses = ["status = :status", "updated_at = CURRENT_TIMESTAMP"];
        if ($new_status === 'approved') {
            $set_clauses[] = "approved_at = CURRENT_TIMESTAMP";
        }

        $this->db->query("UPDATE affiliate_commissions SET " . implode(', ', $set_clauses) . " WHERE id = :commission_id");
        $this->db->bind(':status', $new_status);
        $this->db->bind(':commission_id', (int)$commission_id);
        
        if ($this->db->execute()) {
            // Logic to update affiliate balance if commission is approved or un-approved
            if ($new_status === 'approved') {
                $commission = $this->getAffiliateCommissionById($commission_id);
                if ($commission && $this->userModel) { // Ensure userModel is loaded
                    $this->userModel->updateAffiliateBalance($commission['affiliate_id'], (float)$commission['commission_earned']);
                }
            } // Add logic for un-approving if necessary (e.g. status changes from approved to pending/rejected)
            return $this->db->rowCount() > 0;
        }
        $db_error_info = $this->db->getErrorInfo();
        error_log("OrderModel::updateAffiliateCommissionStatus - Failed for ID {$commission_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
        return false;
    }


    public function getAllAffiliatePayoutRequests($startDate = null, $endDate = null, $status = null) {
        $sql = "SELECT ap.*, u.username as affiliate_username, u.email as affiliate_email, 
                       CONCAT(u.first_name, ' ', u.last_name) as affiliate_full_name
                FROM affiliate_payouts ap
                JOIN users u ON ap.affiliate_id = u.id";
        
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = "DATE(ap.requested_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "DATE(ap.requested_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        if ($status && !empty($status)) {
            $conditions[] = "ap.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY ap.requested_at DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach($params as $key => $value){
                $this->db->bind($key, $value);
            }
        }
        return $this->db->resultSet() ?: [];
    }

    public function getAffiliatePayoutRequestById($payout_id) {
        $this->db->query("SELECT ap.*, u.username as affiliate_username, u.email as affiliate_email,
                                 CONCAT(u.first_name, ' ', u.last_name) as affiliate_full_name
                          FROM affiliate_payouts ap
                          JOIN users u ON ap.affiliate_id = u.id
                          WHERE ap.id = :payout_id");
        $this->db->bind(':payout_id', (int)$payout_id);
        return $this->db->single() ?: false;
    }
    
    public function getAffiliateCommissionsByPayoutId($payout_id) {
        $this->db->query("SELECT ac.*, p.name as product_name, o.created_at as order_date
                          FROM affiliate_commissions ac
                          LEFT JOIN products p ON ac.product_id = p.id
                          LEFT JOIN orders o ON ac.order_id = o.id
                          WHERE ac.payout_id = :payout_id"); 
        $this->db->bind(':payout_id', (int)$payout_id);
        return $this->db->resultSet() ?: [];
    }
    

    public function processAffiliatePayout($payout_id, $new_status, $admin_user_id, $payout_amount_paid = null, $admin_notes = null, $payment_details_admin = null) {
        if (!$this->userModel && class_exists('User')) { 
            $this->userModel = new User();
        }
        if (!$this->userModel) {
            error_log("OrderModel::processAffiliatePayout - UserModel is not available. Cannot update affiliate balance.");
            return 'user_model_unavailable'; 
        }

        if (method_exists($this->db, 'beginTransaction')) $this->db->beginTransaction();
        try {
            $payoutRequest = $this->getAffiliatePayoutRequestById($payout_id);
            if (!$payoutRequest) {
                error_log("OrderModel::processAffiliatePayout - Payout request ID {$payout_id} not found.");
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'payout_not_found';
            }
            $affiliate_id = (int)$payoutRequest['affiliate_id'];

            $this->db->query("UPDATE affiliate_payouts 
                              SET status = :status, 
                                  payout_amount = :payout_amount, 
                                  notes = :notes, 
                                  payment_details = :payment_details_admin,
                                  processed_at = CURRENT_TIMESTAMP,
                                  processed_by_admin_id = :processed_by_admin_id,
                                  updated_at = CURRENT_TIMESTAMP
                              WHERE id = :payout_id");
            $this->db->bind(':status', $new_status);
            $this->db->bind(':payout_amount', ($payout_amount_paid !== null) ? (float)$payout_amount_paid : null);
            $this->db->bind(':notes', $admin_notes);
            $this->db->bind(':payment_details_admin', $payment_details_admin);
            $this->db->bind(':processed_by_admin_id', (int)$admin_user_id); 
            $this->db->bind(':payout_id', (int)$payout_id);

            if (!$this->db->execute()) {
                $db_error_info = $this->db->getErrorInfo();
                error_log("OrderModel::processAffiliatePayout - Failed to update affiliate_payouts for payout_id: {$payout_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'db_error_payout_update';
            }
            error_log("OrderModel::processAffiliatePayout - affiliate_payouts table updated for ID {$payout_id} to status {$new_status}.");


            if ($new_status === 'completed') {
                if ($payout_amount_paid === null || (float)$payout_amount_paid <= 0) {
                    error_log("OrderModel::processAffiliatePayout - Payout amount is not valid for completed status. Payout ID: {$payout_id}");
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return 'invalid_payout_amount_for_completed';
                }

                if (method_exists($this->userModel, 'updateAffiliateBalance')) {
                    if (!$this->userModel->updateAffiliateBalance($affiliate_id, -(float)$payout_amount_paid)) { 
                        error_log("OrderModel::processAffiliatePayout - Failed to DECREMENT affiliate balance for user_id: {$affiliate_id}, amount: " . (-(float)$payout_amount_paid));
                        // Not rolling back transaction for this, as payout itself is processed. Balance update failure should be handled.
                    } else {
                        error_log("OrderModel::processAffiliatePayout - Affiliate balance DECREMENTED for user_id: {$affiliate_id} by " . $payout_amount_paid);
                    }
                } else {
                    error_log("OrderModel::processAffiliatePayout - updateAffiliateBalance method not found in UserModel.");
                }

                // Update status of commissions linked to this payout
                $this->db->query("UPDATE affiliate_commissions 
                                  SET status = :status_paid, paid_at = NOW(), updated_at = NOW()
                                  WHERE payout_id = :payout_id AND status = :status_payout_requested"); 
                $this->db->bind(':status_paid', 'paid');
                $this->db->bind(':payout_id', (int)$payout_id);
                $this->db->bind(':status_payout_requested', 'payout_requested'); 
                
                if (!$this->db->execute()) {
                     $db_error_info = $this->db->getErrorInfo();
                     error_log("OrderModel::processAffiliatePayout - Failed to update affiliate_commissions status to 'paid' for payout_id: {$payout_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                     // Consider if this failure should roll back the payout. For now, logging.
                } else {
                    error_log("OrderModel::processAffiliatePayout - affiliate_commissions status updated to 'paid' for payout_id: {$payout_id}. Rows affected: " . $this->db->rowCount());
                }
            } 
            elseif (in_array($new_status, ['rejected', 'cancelled'])) { 
                 // Revert commission status from 'payout_requested' to 'approved' and clear payout_id
                 $this->db->query("UPDATE affiliate_commissions 
                                  SET status = :status_reverted, payout_id = NULL, updated_at = NOW()
                                  WHERE payout_id = :payout_id AND status = :status_payout_requested");
                $this->db->bind(':status_reverted', 'approved'); 
                $this->db->bind(':payout_id', (int)$payout_id);
                $this->db->bind(':status_payout_requested', 'payout_requested');
                if ($this->db->execute()) {
                    error_log("OrderModel::processAffiliatePayout - affiliate_commissions status reverted for payout_id: {$payout_id}. Rows affected: " . $this->db->rowCount());
                } else {
                    $db_error_info = $this->db->getErrorInfo();
                    error_log("OrderModel::processAffiliatePayout - Failed to revert affiliate_commissions status for payout_id: {$payout_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'No specific error message')));
                }
            }

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true; 
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in OrderModel::processAffiliatePayout: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            return 'general_exception';
        }
    }
}
// It's a common practice in PHP to omit the closing ?> tag 
// if the file contains only PHP code. This can prevent accidental whitespace output.
