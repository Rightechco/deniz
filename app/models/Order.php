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
            die("Fatal Error: Database class not found in Order model."); 
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

    /**
     * Create a new order and its items in the database
     * @param array $data Order data including user info, cart items, etc.
     * @param int|null $affiliate_placing_order_id ID of the affiliate placing the order for a customer, if any.
     * @return int|false The ID of the created order on success, false on failure
     */
    public function createOrder($data, $affiliate_placing_order_id = null) {
        // Ensure models are available, especially if not loaded in constructor due to load order
        if (!$this->productModel && class_exists('Product')) { 
            $this->productModel = new Product(); 
        }
        if (!$this->userModel && class_exists('User')) { 
            $this->userModel = new User(); 
        }

        if (!$this->productModel) { 
             error_log("OrderModel::createOrder - ProductModel is STILL not available. Cannot proceed.");
             return false; 
        }
        
        $platform_rate_defined = defined('PLATFORM_COMMISSION_RATE') ? (float)PLATFORM_COMMISSION_RATE : 0;
        error_log("OrderModel::createOrder - Platform Commission Rate being used: " . $platform_rate_defined);
        
        if (method_exists($this->db, 'beginTransaction')) {
            $this->db->beginTransaction();
        }

        try {
            // 1. Insert order
            $this->db->query('INSERT INTO orders (user_id, first_name, last_name, email, phone, address, city, postal_code, total_amount, payment_method, notes, order_status, payment_status, placed_by_affiliate_id)
                              VALUES (:user_id, :first_name, :last_name, :email, :phone, :address, :city, :postal_code, :total_amount, :payment_method, :notes, :order_status, :payment_status, :placed_by_affiliate_id)');
            
            $this->db->bind(':user_id', isset($data['customer_user_id']) ? (int)$data['customer_user_id'] : null); 
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':address', $data['address']);
            $this->db->bind(':city', $data['city']);
            $this->db->bind(':postal_code', $data['postal_code']);
            $this->db->bind(':total_amount', (float)$data['total_price']); 
            $this->db->bind(':payment_method', $data['payment_method']);
            $this->db->bind(':notes', $data['notes']);
            $this->db->bind(':order_status', $data['order_status'] ?? 'pending_confirmation'); 
            $this->db->bind(':payment_status', $data['payment_status'] ?? ($data['payment_method'] == 'cod' ? 'pending_on_delivery' : 'pending'));
            $this->db->bind(':placed_by_affiliate_id', $affiliate_placing_order_id ? (int)$affiliate_placing_order_id : null);


            if (!$this->db->execute()) { 
                error_log("OrderModel::createOrder - Failed to insert into orders table. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false; 
            }
            $order_id = $this->db->lastInsertId();
            if (!$order_id) { 
                error_log("OrderModel::createOrder - lastInsertId() returned invalid ID after inserting into orders table.");
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false; 
            }
            error_log("OrderModel::createOrder - Order #{$order_id} created. Processing items...");

            // 2. Insert order items
            foreach ($data['cart_items'] as $item_cart_id => $cart_item) {
                if (!isset($cart_item['product_id'], $cart_item['name'], $cart_item['quantity'], $cart_item['price'])) { 
                    error_log("OrderModel::createOrder - Invalid cart item structure for item_cart_id: {$item_cart_id}. Item: " . print_r($cart_item, true));
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack(); 
                    return false; 
                }

                $product_details = $this->productModel->getProductById((int)$cart_item['product_id']);
                if (!$product_details) { 
                    error_log("OrderModel::createOrder - CRITICAL: Product details not found for product_id: " . $cart_item['product_id'] . " in cart item: " . print_r($cart_item, true));
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return false; 
                }
                error_log("OrderModel::createOrder - Product details for item {$cart_item['product_id']}: " . print_r($product_details, true));

                $vendor_id_for_item = (isset($product_details['vendor_id']) && !empty($product_details['vendor_id'])) ? (int)$product_details['vendor_id'] : null;
                $item_sub_total = (float)$cart_item['price'] * (int)$cart_item['quantity'];
                
                $platform_commission_rate_to_store = defined('PLATFORM_COMMISSION_RATE') ? (float)PLATFORM_COMMISSION_RATE : null;
                $platform_commission_amount_for_item = 0.00; 
                $vendor_earning_for_item = 0.00; 

                if ($vendor_id_for_item !== null) { 
                    if ($platform_rate_defined > 0) {
                        $platform_commission_amount_for_item = round($item_sub_total * $platform_rate_defined, 2);
                        $vendor_earning_for_item = $item_sub_total - $platform_commission_amount_for_item;
                    } else { 
                        $vendor_earning_for_item = $item_sub_total; // No platform commission, vendor gets full amount
                        $platform_commission_amount_for_item = 0.00;
                    }
                } else { // Product belongs to the store itself (vendor_id is NULL)
                    $platform_commission_amount_for_item = 0.00; 
                    $vendor_earning_for_item = 0.00; 
                }
                error_log("OrderModel::createOrder - For item {$cart_item['product_id']}: Subtotal={$item_sub_total}, VendorID={$vendor_id_for_item}, PlatformCommRateToStore={$platform_commission_rate_to_store}, PlatformCommAmt={$platform_commission_amount_for_item}, VendorEarning={$vendor_earning_for_item}");

                $this->db->query('INSERT INTO order_items 
                                    (order_id, product_id, variation_id, product_name, quantity, price_at_purchase, sub_total, 
                                     vendor_id, platform_commission_rate, platform_commission_amount, vendor_earning, payout_status)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                
                $params_order_item = [
                    $order_id, 
                    (int)$cart_item['product_id'],
                    (isset($cart_item['variation_id']) && !empty($cart_item['variation_id'])) ? (int)$cart_item['variation_id'] : null,
                    $cart_item['name'], 
                    (int)$cart_item['quantity'], 
                    (float)$cart_item['price'], 
                    $item_sub_total,
                    $vendor_id_for_item,
                    $platform_commission_rate_to_store, 
                    $platform_commission_amount_for_item, 
                    $vendor_earning_for_item, 
                    'unpaid'
                ];

                if (!$this->db->execute($params_order_item)) { 
                    error_log("OrderModel::createOrder - Failed to insert order_item. OrderID:{$order_id}, ProdID:{$cart_item['product_id']}. DBError:" . implode('|',$this->db->getErrorInfo()));
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return false; 
                }
                $order_item_id = $this->db->lastInsertId();
                error_log("OrderModel::createOrder - OrderItem #{$order_item_id} created for Order #{$order_id}.");

                // 3. Record affiliate commission
                $affiliate_id_to_credit = null;
                if ($affiliate_placing_order_id !== null) { // Order placed by an affiliate for a customer
                    $affiliate_id_to_credit = $affiliate_placing_order_id;
                    error_log("OrderModel::createOrder - Order placed by affiliate ID: {$affiliate_id_to_credit}");
                } elseif (isset($_SESSION['referred_by_affiliate_code']) && !empty($_SESSION['referred_by_affiliate_code'])) { // Order placed via referral link
                    if ($this->userModel) {
                        $affiliateUser = $this->userModel->findUserByAffiliateCode($_SESSION['referred_by_affiliate_code']);
                        if ($affiliateUser) {
                            $affiliate_id_to_credit = $affiliateUser['id'];
                            error_log("OrderModel::createOrder - Order referred by affiliate ID: {$affiliate_id_to_credit} (Code: {$_SESSION['referred_by_affiliate_code']})");
                        } else {
                            error_log("OrderModel::createOrder - Affiliate code {$_SESSION['referred_by_affiliate_code']} in session, but no user found with this code.");
                        }
                    } else {
                        error_log("OrderModel::createOrder - UserModel not available for referral code lookup.");
                    }
                }

                if ($affiliate_id_to_credit && $product_details) {
                    $this->recordAffiliateCommission(
                        $affiliate_id_to_credit, $order_id, $order_item_id,
                        (int)$cart_item['product_id'], $item_sub_total, 
                        $product_details['affiliate_commission_type'] ?? null,
                        $product_details['affiliate_commission_value'] ?? null
                    );
                }
            } // End foreach cart_items

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return $order_id;
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in OrderModel::createOrder: " . $e->getMessage());
            return false;
        }
    }

    public function recordAffiliateCommission($affiliate_id, $order_id, $order_item_id, $product_id, $sale_amount, $commission_type, $commission_value) {
        error_log("OrderModel::recordAffiliateCommission - Args: affID={$affiliate_id}, ordID={$order_id}, itemID={$order_item_id}, prodID={$product_id}, saleAmt={$sale_amount}, commType={$commission_type}, commVal={$commission_value}");

        if (empty($affiliate_id) || empty($commission_type) || $commission_type === 'none' || $commission_value === null || (float)$commission_value <= 0) {
            error_log("OrderModel::recordAffiliateCommission - No commission to record (invalid params or type 'none'). Affiliate: {$affiliate_id}, Type: {$commission_type}, Value: {$commission_value}");
            return false; 
        }

        $commission_earned = 0;
        $rate_to_store = null;
        $fixed_to_store = null;

        if ($commission_type === 'percentage') {
            $commission_earned = round($sale_amount * ((float)$commission_value / 100), 2);
            $rate_to_store = (float)$commission_value / 100;
            error_log("OrderModel::recordAffiliateCommission - Type: Percentage, Rate: {$rate_to_store}, Earned: {$commission_earned}");
        } elseif ($commission_type === 'fixed') {
            $commission_earned = (float)$commission_value;
            $fixed_to_store = (float)$commission_value;
            error_log("OrderModel::recordAffiliateCommission - Type: Fixed, Amount: {$fixed_to_store}, Earned: {$commission_earned}");
        } else {
            error_log("OrderModel::recordAffiliateCommission - Invalid commission type: {$commission_type}");
            return false; 
        }

        if ($commission_earned > 0) {
            $this->db->query("INSERT INTO affiliate_commissions 
                                (affiliate_id, order_id, order_item_id, product_id, commission_rate, commission_fixed_amount, sale_amount, commission_earned, status)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $params = [
                (int)$affiliate_id, (int)$order_id, (int)$order_item_id, (int)$product_id,
                $rate_to_store, $fixed_to_store, (float)$sale_amount, (float)$commission_earned, 'pending'
            ];
            if ($this->db->execute($params)) {
                error_log("Affiliate commission recorded successfully: Affiliate ID {$affiliate_id}, OrderItem ID {$order_item_id}, Amount {$commission_earned}");
                return true;
            } else {
                error_log("Failed to record affiliate commission. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                return false;
            }
        } else {
            error_log("OrderModel::recordAffiliateCommission - Commission earned is zero or less, not recording.");
        }
        return false;
    }

    public function getOrdersByUserId($user_id) {
        $this->db->query("SELECT o.*, u.username as customer_username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.user_id = :user_id ORDER BY o.created_at DESC");
        $this->db->bind(':user_id', (int)$user_id);
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    public function getOrderDetailsById($order_id, $user_id = null, $vendor_id = null) {
        $sql = "SELECT * FROM orders WHERE id = :order_id";
        if ($user_id) { 
            $sql .= " AND user_id = :user_id";
        }
        $this->db->query($sql);
        $this->db->bind(':order_id', (int)$order_id);
        if ($user_id) {
            $this->db->bind(':user_id', (int)$user_id);
        }
        $order = $this->db->single();

        if ($order) {
            $items_sql = "SELECT oi.id as order_item_id, oi.order_id, oi.product_id, oi.variation_id, 
                                 oi.product_name, oi.quantity, oi.price_at_purchase, oi.sub_total,
                                 oi.vendor_id, oi.platform_commission_rate, oi.platform_commission_amount, 
                                 oi.vendor_earning, oi.payout_status, oi.payout_id,
                                 p.image_url as product_image_url, 
                                 pv.image_url as variation_image_url
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
                    $item['display_image_url'] = !empty($item['variation_image_url']) ? $item['variation_image_url'] : $item['product_image_url'];
                    
                    if ($vendor_id !== null) { 
                        if (isset($item['vendor_id']) && $item['vendor_id'] == $vendor_id) {
                            $filtered_items[] = $item;
                        }
                    } else { 
                        $filtered_items[] = $item;
                    }
                }
            }
            $order['items'] = $filtered_items;

            if ($vendor_id !== null && empty($order['items'])) {
                return false; 
            }
            return $order;
        }
        return false;
    }

    public function getAllOrders() {
        $this->db->query("SELECT o.*, u.username as customer_username, u.email as customer_email,
                                 CONCAT(u.first_name, ' ', u.last_name) as customer_full_name
                          FROM orders o
                          JOIN users u ON o.user_id = u.id
                          ORDER BY o.created_at DESC");
        return $this->db->resultSet() ?: [];
    }

    public function updateOrderStatus($order_id, $order_status, $payment_status = null) {
        $sql = "UPDATE orders SET order_status = :order_status";
        if ($payment_status !== null) {
            $sql .= ", payment_status = :payment_status";
        }
        $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE id = :order_id"; // Add updated_at

        $this->db->query($sql);
        $this->db->bind(':order_status', $order_status);
        if ($payment_status !== null) {
            $this->db->bind(':payment_status', $payment_status);
        }
        $this->db->bind(':order_id', (int)$order_id);

        return $this->db->execute();
    }

    public function getOrdersForVendor($vendor_id) {
        $this->db->query("SELECT o.*, u.username as customer_username, u.email as customer_email, u.first_name as customer_first_name, u.last_name as customer_last_name
                          FROM orders o
                          JOIN users u ON o.user_id = u.id
                          WHERE o.id IN (
                              SELECT DISTINCT oi.order_id 
                              FROM order_items oi
                              JOIN products p ON oi.product_id = p.id
                              WHERE p.vendor_id = :vendor_id
                          )
                          ORDER BY o.created_at DESC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    public function getVendorWithdrawableBalance($vendor_id) {
        $this->db->query("SELECT SUM(oi.vendor_earning) as total_withdrawable
                          FROM order_items oi
                          JOIN orders o ON oi.order_id = o.id
                          WHERE oi.vendor_id = :vendor_id
                          AND oi.payout_status = :payout_status_unpaid
                          AND o.order_status IN (:status_delivered, :status_shipped, :status_completed) 
                          AND o.payment_status = :payment_status_paid"); 

        $this->db->bind(':vendor_id', (int)$vendor_id);
        $this->db->bind(':payout_status_unpaid', 'unpaid');
        $this->db->bind(':status_delivered', 'delivered'); 
        $this->db->bind(':status_shipped', 'shipped');     
        $this->db->bind(':status_completed', 'completed'); // Add if you use this status
        $this->db->bind(':payment_status_paid', 'paid');   

        $result = $this->db->single();
        return $result && isset($result['total_withdrawable']) && $result['total_withdrawable'] !== null ? (float)$result['total_withdrawable'] : 0.00;
    }

    public function getUnpaidOrderItemsForVendor($vendor_id) {
        $this->db->query("SELECT oi.id as order_item_id, oi.order_id, oi.product_name, oi.quantity, oi.vendor_earning, o.created_at as order_date
                          FROM order_items oi
                          JOIN orders o ON oi.order_id = o.id
                          WHERE oi.vendor_id = :vendor_id
                          AND oi.payout_status = :payout_status_unpaid
                          AND o.order_status IN (:status_delivered, :status_shipped, :status_completed)
                          AND o.payment_status = :payment_status_paid
                          ORDER BY o.created_at ASC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        $this->db->bind(':payout_status_unpaid', 'unpaid');
        $this->db->bind(':status_delivered', 'delivered');
        $this->db->bind(':status_shipped', 'shipped');
        $this->db->bind(':status_completed', 'completed');
        $this->db->bind(':payment_status_paid', 'paid');
        
        return $this->db->resultSet() ?: [];
    }

    public function requestVendorPayout($vendor_id, $requested_amount, $order_item_ids, $payout_method = 'bank_transfer', $payment_details = null) {
        if (method_exists($this->db, 'beginTransaction')) $this->db->beginTransaction();
        try {
            $this->db->query("INSERT INTO vendor_payouts (vendor_id, requested_amount, payout_method, payment_details, status) 
                              VALUES (?, ?, ?, ?, ?)");
            $params_payout = [(int)$vendor_id, (float)$requested_amount, $payout_method, $payment_details, 'requested'];
            if (!$this->db->execute($params_payout)) {
                error_log("OrderModel::requestVendorPayout - Failed to insert into vendor_payouts. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
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
                $placeholders = rtrim(str_repeat('?,', count($order_item_ids)), ',');
                $sql_update_items = "UPDATE order_items 
                                     SET payout_status = ?, payout_id = ? 
                                     WHERE vendor_id = ? AND payout_status = ? AND id IN ({$placeholders})";
                $this->db->query($sql_update_items);
                $params_update_items = ['requested', $payout_id, (int)$vendor_id, 'unpaid'];
                foreach ($order_item_ids as $item_id) { $params_update_items[] = (int)$item_id; }

                if (!$this->db->execute($params_update_items)) {
                    error_log("OrderModel::requestVendorPayout - Failed to update order_items for payout_id: {$payout_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    $this->db->query("DELETE FROM vendor_payouts WHERE id = :p_id_del_on_fail"); 
                    $this->db->bind(':p_id_del_on_fail', $payout_id); 
                    $this->db->execute();
                    return 'db_error_item_update';
                }
                if ($this->db->rowCount() != count($order_item_ids)) {
                    error_log("OrderModel::requestVendorPayout - Mismatch in updated order_items count for payout_id: {$payout_id}. Expected: " . count($order_item_ids) . ", Affected: " . $this->db->rowCount());
                }
            } else { 
                error_log("OrderModel::requestVendorPayout - No order_item_ids provided for payout_id: {$payout_id} while requested_amount was {$requested_amount}.");
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
            error_log("Error in OrderModel::requestVendorPayout: " . $e->getMessage());
            return 'general_exception';
        }
    }

    public function getPayoutRequestsByVendorId($vendor_id) {
        $this->db->query("SELECT * FROM vendor_payouts WHERE vendor_id = :vendor_id ORDER BY requested_at DESC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        return $this->db->resultSet() ?: [];
    }

    // --- Admin Payout Methods ---
    public function getAllPayoutRequests($startDate = null, $endDate = null, $status = null) {
        $sql = "SELECT vp.*, u.username as vendor_username, u.email as vendor_email, 
                       CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                FROM vendor_payouts vp
                JOIN users u ON vp.vendor_id = u.id";
        
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = "vp.requested_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "vp.requested_at <= :end_date";
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
                                 oi.product_name, oi.quantity, oi.price_at_purchase, oi.sub_total,
                                 oi.vendor_id, oi.platform_commission_rate, oi.platform_commission_amount, 
                                 oi.vendor_earning, oi.payout_status, oi.payout_id,
                                 p.name as parent_product_name 
                          FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id
                          WHERE oi.payout_id = :payout_id");
        $this->db->bind(':payout_id', (int)$payout_id);
        return $this->db->resultSet() ?: [];
    }

    public function processVendorPayout($payout_id, $new_status, $admin_user_id, $payout_amount_paid = null, $admin_notes = null, $payment_details_admin = null) {
        if (method_exists($this->db, 'beginTransaction')) $this->db->beginTransaction();
        try {
            $this->db->query("UPDATE vendor_payouts 
                              SET status = :status, 
                                  payout_amount = :payout_amount, 
                                  notes = :notes, 
                                  payment_details = :payment_details_admin,
                                  processed_at = CURRENT_TIMESTAMP,
                                  processed_by_admin_id = :processed_by_admin_id
                              WHERE id = :payout_id");
            $this->db->bind(':status', $new_status);
            $this->db->bind(':payout_amount', ($payout_amount_paid !== null) ? (float)$payout_amount_paid : null);
            $this->db->bind(':notes', $admin_notes);
            $this->db->bind(':payment_details_admin', $payment_details_admin);
            $this->db->bind(':processed_by_admin_id', (int)$admin_user_id); 
            $this->db->bind(':payout_id', (int)$payout_id);

            if (!$this->db->execute()) {
                error_log("OrderModel::processVendorPayout - Failed to update vendor_payouts for payout_id: {$payout_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false;
            }

            if ($new_status === 'completed') {
                $this->db->query("UPDATE order_items 
                                  SET payout_status = :payout_status_paid
                                  WHERE payout_id = :payout_id AND payout_status = :payout_status_requested");
                $this->db->bind(':payout_status_paid', 'paid');
                $this->db->bind(':payout_id', (int)$payout_id);
                $this->db->bind(':payout_status_requested', 'requested'); 
                if (!$this->db->execute()) {
                     error_log("OrderModel::processVendorPayout - Failed to update order_items payout_status to 'paid' for payout_id: {$payout_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                     if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                     return false; 
                }
            } 
            elseif (in_array($new_status, ['rejected', 'cancelled'])) { 
                 $this->db->query("UPDATE order_items 
                                  SET payout_status = :payout_status_unpaid, payout_id = NULL
                                  WHERE payout_id = :payout_id AND payout_status = :payout_status_requested");
                $this->db->bind(':payout_status_unpaid', 'unpaid');
                $this->db->bind(':payout_id', (int)$payout_id);
                $this->db->bind(':payout_status_requested', 'requested');
                $this->db->execute(); 
            }

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true;
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in OrderModel::processVendorPayout: " . $e->getMessage());
            return false;
        }
    }

    // --- Platform Commission Methods ---
    public function getOrdersWithPlatformCommission($startDate = null, $endDate = null) {
        $sql = "SELECT o.*, 
                       u.username as customer_username, 
                       u.email as customer_email,
                       CONCAT(u.first_name, ' ', u.last_name) as customer_full_name,
                       (SELECT SUM(oi.platform_commission_amount) 
                        FROM order_items oi 
                        WHERE oi.order_id = o.id) as total_order_platform_commission
                FROM orders o
                JOIN users u ON o.user_id = u.id";
        
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = "o.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "o.created_at <= :end_date";
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
                $conditions[] = "o.created_at >= :start_date";
                $params[':start_date'] = $startDate;
            }
            if ($endDate) {
                $conditions[] = "o.created_at <= :end_date";
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

    /**
     * Record an affiliate commission for a sale.
     * @param int $affiliate_id
     * @param int $order_id
     * @param int $order_item_id
     * @param int $product_id
     * @param float $sale_amount Amount of the item/sale commission is based on
     * @param string|null $commission_type Type from product ('percentage', 'fixed', or null/none)
     * @param float|null $commission_value Value from product
     * @return bool
     */
     /**
     * Get detailed order items for export, filtered by date range and order status.
     * @param string|null $startDate YYYY-MM-DD HH:MM:SS
     * @param string|null $endDate YYYY-MM-DD HH:MM:SS
     * @param string|null $orderStatus
     * @return array
     */
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
            $conditions[] = "o.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "o.created_at <= :end_date";
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
    /**
     * Get all commission records for a specific affiliate.
     * @param int $affiliate_id
     * @return array
     */
    public function getCommissionsByAffiliateId($affiliate_id) {
        $this->db->query("SELECT ac.*, p.name as product_name, o.created_at as order_date
                          FROM affiliate_commissions ac
                          LEFT JOIN products p ON ac.product_id = p.id
                          LEFT JOIN orders o ON ac.order_id = o.id
                          WHERE ac.affiliate_id = :affiliate_id
                          ORDER BY ac.created_at DESC");
        $this->db->bind(':affiliate_id', (int)$affiliate_id);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Get all payout requests for a specific affiliate.
     * (این متد را برای سازگاری با نام‌گذاری getPayoutRequestsByVendorId ایجاد می‌کنیم)
     * @param int $affiliate_id
     * @return array
     */
    public function getPayoutsByAffiliateId($affiliate_id) {
        $this->db->query("SELECT * FROM affiliate_payouts 
                          WHERE affiliate_id = :affiliate_id 
                          ORDER BY requested_at DESC");
        $this->db->bind(':affiliate_id', (int)$affiliate_id);
        return $this->db->resultSet() ?: [];
    }
    
    /**
     * Create a new affiliate payout request.
     * @param int $affiliate_id
     * @param float $requested_amount
     * @param string $payment_details Affiliate's payment info (e.g., bank account)
     * @param string $payout_method (Optional, default 'bank_transfer')
     * @return int|false Payout ID on success, false on failure.
     */
    public function createAffiliatePayoutRequest($affiliate_id, $requested_amount, $payment_details, $payout_method = 'bank_transfer') {
        try {
            $this->db->query("INSERT INTO affiliate_payouts (affiliate_id, requested_amount, payout_method, payment_details, status) 
                              VALUES (?, ?, ?, ?, ?)");
            $params = [
                (int)$affiliate_id,
                (float)$requested_amount,
                $payout_method,
                $payment_details,
                'requested' // وضعیت اولیه درخواست
            ];
            if ($this->db->execute($params)) {
                return $this->db->lastInsertId();
            } else {
                error_log("OrderModel::createAffiliatePayoutRequest - Failed to insert into affiliate_payouts. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                return false;
            }
        } catch (Exception $e) {
            error_log("Error in OrderModel::createAffiliatePayoutRequest: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all affiliate commissions with details for admin panel.
     * @param string|null $filter_status Filter by commission status
     * @return array
     */
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

    /**
     * Get a single affiliate commission by its ID.
     * @param int $commission_id
     * @return mixed
     */
    public function getAffiliateCommissionById($commission_id) {
        $this->db->query("SELECT * FROM affiliate_commissions WHERE id = :commission_id");
        $this->db->bind(':commission_id', (int)$commission_id);
        return $this->db->single() ?: false;
    }

    /**
     * Update the status of an affiliate commission.
     * @param int $commission_id
     * @param string $new_status
     * @return bool
     */
    public function updateAffiliateCommissionStatus($commission_id, $new_status) {
        // اطمینان از اینکه وضعیت جدید معتبر است (می‌توانید این را در کنترلر هم انجام دهید)
        $allowed_statuses = ['pending', 'approved', 'rejected', 'paid', 'cancelled'];
        if (!in_array($new_status, $allowed_statuses)) {
            error_log("OrderModel::updateAffiliateCommissionStatus - Invalid new status: {$new_status}");
            return false;
        }

        $this->db->query("UPDATE affiliate_commissions SET status = :status " . 
                          ($new_status === 'approved' ? ", approved_at = CURRENT_TIMESTAMP" : "") .
                          " WHERE id = :commission_id");
        $this->db->bind(':status', $new_status);
        $this->db->bind(':commission_id', (int)$commission_id);
        
        if ($this->db->execute()) {
            return $this->db->rowCount() > 0;
        }
        error_log("OrderModel::updateAffiliateCommissionStatus - Failed for ID {$commission_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        return false;
    }

    // --- Affiliate Payout Management by Admin ---

    /**
     * Get all affiliate payout requests for admin panel, filtered by date and status.
     * @param string|null $startDate YYYY-MM-DD HH:MM:SS (for requested_at)
     * @param string|null $endDate YYYY-MM-DD HH:MM:SS (for requested_at)
     * @param string|null $status
     * @return array
     */
    public function getAllAffiliatePayoutRequests($startDate = null, $endDate = null, $status = null) {
        $sql = "SELECT ap.*, u.username as affiliate_username, u.email as affiliate_email, 
                       CONCAT(u.first_name, ' ', u.last_name) as affiliate_full_name
                FROM affiliate_payouts ap
                JOIN users u ON ap.affiliate_id = u.id";
        
        $conditions = [];
        $params = [];

        if ($startDate) {
            $conditions[] = "ap.requested_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "ap.requested_at <= :end_date";
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

    /**
     * Get a single affiliate payout request by its ID.
     * @param int $payout_id
     * @return mixed
     */
    public function getAffiliatePayoutRequestById($payout_id) {
        $this->db->query("SELECT ap.*, u.username as affiliate_username, u.email as affiliate_email,
                                 CONCAT(u.first_name, ' ', u.last_name) as affiliate_full_name
                          FROM affiliate_payouts ap
                          JOIN users u ON ap.affiliate_id = u.id
                          WHERE ap.id = :payout_id");
        $this->db->bind(':payout_id', (int)$payout_id);
        return $this->db->single() ?: false;
    }
    
    /**
     * Get affiliate commission items associated with a specific affiliate payout ID.
     * (This assumes you link affiliate_commissions to affiliate_payouts via payout_id)
     * @param int $payout_id
     * @return array
     */
    public function getAffiliateCommissionsByPayoutId($payout_id) {
        // این متد باید کمیسیون‌هایی را برگرداند که payout_id آنها با این درخواست تسویه یکی است
        // و وضعیت آنها 'approved' بوده (قبل از اینکه 'paid' شوند)
        $this->db->query("SELECT ac.*, p.name as product_name, o.created_at as order_date
                          FROM affiliate_commissions ac
                          LEFT JOIN products p ON ac.product_id = p.id
                          LEFT JOIN orders o ON ac.order_id = o.id
                          WHERE ac.payout_id = :payout_id");
        $this->db->bind(':payout_id', (int)$payout_id);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Process an affiliate payout request (update status, payment details, and affiliate balance).
     * @param int $payout_id
     * @param string $new_status e.g., 'completed', 'rejected', 'processing'
     * @param int $admin_user_id ID of the admin processing the payout
     * @param float|null $payout_amount_paid Actual amount paid
     * @param string|null $admin_notes
     * @param string|null $payment_details_admin
     * @return bool
     */
    

    public function processAffiliatePayout($payout_id, $new_status, $admin_user_id, $payout_amount_paid = null, $admin_notes = null, $payment_details_admin = null) {
        if (!$this->userModel && class_exists('User')) { // اطمینان از بارگذاری UserModel
            $this->userModel = new User();
        }
        if (!$this->userModel) {
            error_log("OrderModel::processAffiliatePayout - UserModel is not available. Cannot update affiliate balance.");
            return false; // یا یک کد خطای خاص
        }

        if (method_exists($this->db, 'beginTransaction')) $this->db->beginTransaction();
        try {
            $payoutRequest = $this->getAffiliatePayoutRequestById($payout_id);
            if (!$payoutRequest) {
                error_log("OrderModel::processAffiliatePayout - Payout request ID {$payout_id} not found.");
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'payout_not_found';
            }
            $affiliate_id = $payoutRequest['affiliate_id'];
            $requested_amount = (float)$payoutRequest['requested_amount'];

            // 1. به‌روزرسانی جدول affiliate_payouts
            $this->db->query("UPDATE affiliate_payouts 
                              SET status = :status, 
                                  payout_amount = :payout_amount, 
                                  notes = :notes, 
                                  payment_details = :payment_details_admin,
                                  processed_at = CURRENT_TIMESTAMP,
                                  processed_by_admin_id = :processed_by_admin_id
                              WHERE id = :payout_id");
            $this->db->bind(':status', $new_status);
            $this->db->bind(':payout_amount', ($payout_amount_paid !== null) ? (float)$payout_amount_paid : null);
            $this->db->bind(':notes', $admin_notes);
            $this->db->bind(':payment_details_admin', $payment_details_admin);
            $this->db->bind(':processed_by_admin_id', (int)$admin_user_id); 
            $this->db->bind(':payout_id', (int)$payout_id);

            if (!$this->db->execute()) {
                error_log("OrderModel::processAffiliatePayout - Failed to update affiliate_payouts for payout_id: {$payout_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return 'db_error_payout_update';
            }
            error_log("OrderModel::processAffiliatePayout - affiliate_payouts table updated for ID {$payout_id} to status {$new_status}.");


            // 2. اگر وضعیت "تکمیل شده" (completed) است، موجودی کیف پول همکار را کم کن
            // و وضعیت کمیسیون‌های مرتبط را به 'paid' تغییر بده
            if ($new_status === 'completed') {
                if ($payout_amount_paid === null || $payout_amount_paid <= 0) {
                    error_log("OrderModel::processAffiliatePayout - Payout amount is not valid for completed status. Payout ID: {$payout_id}");
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return 'invalid_payout_amount_for_completed';
                }

                // کسر مبلغ از کیف پول همکار
                if (!$this->userModel->updateAffiliateBalance($affiliate_id, -(float)$payout_amount_paid)) { 
                    error_log("OrderModel::processAffiliatePayout - Failed to DECREMENT affiliate balance for user_id: {$affiliate_id}, amount: " . (-(float)$payout_amount_paid));
                    if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                    return 'balance_update_failed'; 
                }
                error_log("OrderModel::processAffiliatePayout - Affiliate balance DECREMENTED for user_id: {$affiliate_id} by " . $payout_amount_paid);


                // به‌روزرسانی وضعیت کمیسیون‌های مرتبط با این پرداخت به 'paid'
                // فقط کمیسیون‌هایی که وضعیتشان 'payout_requested' (یا 'approved' اگر مستقیماً از تایید به پرداخت می‌آید) بوده را آپدیت کن
                $this->db->query("UPDATE affiliate_commissions 
                                  SET status = :status_paid
                                  WHERE payout_id = :payout_id AND status = :status_before_paid"); 
                $this->db->bind(':status_paid', 'paid');
                $this->db->bind(':payout_id', (int)$payout_id);
                // وضعیت کمیسیون‌ها قبل از پرداخت باید 'payout_requested' باشد (که توسط createAffiliatePayoutRequest تنظیم شده)
                // یا اگر مستقیماً از 'approved' به 'paid' می‌رویم (بدون مرحله میانی درخواست پرداخت توسط همکار)، باید 'approved' باشد.
                // با توجه به جریان فعلی، همکار درخواست می‌دهد و کمیسیون‌ها به payout_requested تغییر می‌کنند.
                $this->db->bind(':status_before_paid', 'payout_requested'); 
                
                if (!$this->db->execute()) {
                     error_log("OrderModel::processAffiliatePayout - Failed to update affiliate_commissions status to 'paid' for payout_id: {$payout_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                     if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                     return 'commission_status_update_failed'; 
                }
                error_log("OrderModel::processAffiliatePayout - affiliate_commissions status updated to 'paid' for payout_id: {$payout_id}. Rows affected: " . $this->db->rowCount());
            } 
            // اگر وضعیت "رد شده" (rejected) یا "لغو شده" (cancelled_by_admin) است،
            // کمیسیون‌های مرتبط با این درخواست پرداخت باید به 'approved' برگردند (تا دوباره قابل درخواست باشند)
            // و payout_id آنها NULL شود.
            elseif (in_array($new_status, ['rejected', 'cancelled'])) { 
                 $this->db->query("UPDATE affiliate_commissions 
                                  SET status = :status_reverted, payout_id = NULL
                                  WHERE payout_id = :payout_id AND status = :status_payout_requested");
                $this->db->bind(':status_reverted', 'approved'); // برگرداندن به وضعیت تایید شده
                $this->db->bind(':payout_id', (int)$payout_id);
                $this->db->bind(':status_payout_requested', 'payout_requested');
                if ($this->db->execute()) {
                    error_log("OrderModel::processAffiliatePayout - affiliate_commissions status reverted for payout_id: {$payout_id}. Rows affected: " . $this->db->rowCount());
                } else {
                    error_log("OrderModel::processAffiliatePayout - Failed to revert affiliate_commissions status for payout_id: {$payout_id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
                    // این خطا ممکن است حیاتی نباشد اگر آپدیت خود payout موفق بوده، اما باید بررسی شود.
                }
            }

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true; // موفقیت‌آمیز
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in OrderModel::processAffiliatePayout: " . $e->getMessage());
            return 'general_exception';
        }
    }
}
// تگ پایانی PHP را حذف کنید
