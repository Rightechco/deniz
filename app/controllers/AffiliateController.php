<?php
// app/controllers/AffiliateController.php

class AffiliateController extends Controller {
    private $userModel;
    private $orderModel; 
    private $productModel; 
    private $attributeModel; 
    private $categoryModel; // اضافه کردن مدل دسته‌بندی

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product'); 
        $this->orderModel = $this->model('Order'); 
        $this->attributeModel = $this->model('ProductAttribute'); 
        $this->categoryModel = $this->model('Category'); // بارگذاری مدل دسته‌بندی

        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'affiliate') {
            flash('auth_required', 'برای دسترسی به پنل همکاری، لطفاً وارد شوید.', 'alert alert-danger');
            $_SESSION['redirect_after_login'] = BASE_URL . 'affiliate/dashboard'; 
            header('Location: ' . BASE_URL . 'auth/login');
            exit();
        }
    }

    public function dashboard() { /* ... (کد از affiliate_controller_v3_request_payout_logic) ... */ }
    public function marketingTools() { /* ... (کد از affiliate_controller_v3_request_payout_logic) ... */ }
    public function commissions() { /* ... (کد از affiliate_controller_v3_request_payout_logic) ... */ }
    public function payoutHistory() { /* ... (کد از affiliate_controller_v3_request_payout_logic) ... */ }
    public function requestPayout() { /* ... (کد از affiliate_controller_v3_request_payout_logic) ... */ }

    /**
    * نمایش فرم و پردازش ثبت سفارش برای مشتری توسط همکار
    * URL: affiliate/createOrderForCustomer
    */
    public function createOrderForCustomer() {
        $affiliate_id = $_SESSION['user_id'];
        $affiliate_user = $this->userModel->findUserById($affiliate_id);

        if (!$affiliate_user) {
            flash('error_message', 'اطلاعات کاربری شما یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'auth/logout');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $customer_data = [
                'first_name' => trim($_POST['customer_first_name']),
                'last_name' => trim($_POST['customer_last_name']),
                'email' => trim($_POST['customer_email']),
                'phone' => trim($_POST['customer_phone']),
                'address' => trim($_POST['customer_address']),
                'city' => trim($_POST['customer_city']),
                'postal_code' => trim($_POST['customer_postal_code']),
                'notes' => trim($_POST['order_notes'] ?? '')
            ];
            $customer_errors = [];
            // اعتبارسنجی داده‌های مشتری (مشابه قبل)
            if (empty($customer_data['first_name'])) { $customer_errors['first_name_err'] = 'نام مشتری الزامی است.'; }
            // ... سایر اعتبارسنجی‌ها ...

            // دریافت آیتم‌های سفارش از فیلد مخفی (که توسط جاوااسکریپت پر شده)
            $cart_items_json = $_POST['order_items_json'] ?? '[]';
            $cart_items_array = json_decode($cart_items_json, true);

            if (empty($cart_items_array) || !is_array($cart_items_array)) {
                flash('order_fail', 'سبد سفارش خالی است یا اطلاعات آن نامعتبر می‌باشد.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'affiliate/createOrderForCustomer');
                exit();
            }
            
            // اعتبارسنجی تک تک آیتم‌های سبد (موجودی و ...)
            $order_total_price = 0;
            $valid_cart_items_for_model = [];
            $product_errors_on_submit = [];

            foreach ($cart_items_array as $cart_key => $cart_item_data) {
                $product_id_to_check = (int)($cart_item_data['product_id'] ?? 0);
                $variation_id_to_check = !empty($cart_item_data['variation_id']) ? (int)$cart_item_data['variation_id'] : null;
                $quantity_to_check = (int)($cart_item_data['quantity'] ?? 0);
                $current_stock_item = 0;

                if ($quantity_to_check <= 0) {
                    $product_errors_on_submit[] = "تعداد برای محصول '{$cart_item_data['name']}' باید مثبت باشد.";
                    continue;
                }

                if ($variation_id_to_check) {
                    $variation = $this->attributeModel->getVariationById($variation_id_to_check);
                    if ($variation && $variation['is_active'] && $variation['parent_product_id'] == $product_id_to_check) {
                        $current_stock_item = (int)$variation['stock_quantity'];
                        if ($current_stock_item < $quantity_to_check) {
                            $product_errors_on_submit[] = "موجودی تنوع '{$cart_item_data['name']}' ({$current_stock_item} عدد) کافی نیست.";
                        } else {
                            $valid_cart_items_for_model[] = $cart_item_data; // آیتم معتبر است
                            $order_total_price += (float)($cart_item_data['price'] ?? 0) * $quantity_to_check;
                        }
                    } else { $product_errors_on_submit[] = "تنوع '{$cart_item_data['name']}' نامعتبر است."; }
                } else {
                    $product = $this->productModel->getProductById($product_id_to_check);
                    if ($product && $product['product_type'] === 'simple') {
                        $current_stock_item = (int)$product['stock_quantity'];
                         if ($current_stock_item < $quantity_to_check) {
                            $product_errors_on_submit[] = "موجودی محصول '{$cart_item_data['name']}' ({$current_stock_item} عدد) کافی نیست.";
                        } else {
                            $valid_cart_items_for_model[] = $cart_item_data; // آیتم معتبر است
                            $order_total_price += (float)($cart_item_data['price'] ?? 0) * $quantity_to_check;
                        }
                    } else { $product_errors_on_submit[] = "محصول '{$cart_item_data['name']}' نامعتبر است."; }
                }
            }
            
            if (!empty($product_errors_on_submit)) {
                 flash('order_fail', "خطا در محصولات انتخابی: <br>" . implode("<br>", $product_errors_on_submit), 'alert alert-danger');
                 header('Location: ' . BASE_URL . 'affiliate/createOrderForCustomer'); // بازگشت به فرم
                 exit();
            }
            if (empty($valid_cart_items_for_model)) { // اگر پس از اعتبارسنجی هیچ آیتمی نماند
                 flash('order_fail', 'هیچ محصول معتبری برای ثبت سفارش انتخاب نشده است.', 'alert alert-danger');
                 header('Location: ' . BASE_URL . 'affiliate/createOrderForCustomer');
                 exit();
            }


            // اگر خطای اعتبارسنجی مشتری یا محصول نبود، ادامه بده
            if (empty($customer_errors)) {
                // محاسبه کمیسیون همکاری برای کل سفارش (مجموع کمیسیون آیتم‌ها)
                $total_affiliate_commission_for_order = 0;
                foreach($valid_cart_items_for_model as $processed_item){
                    $p_details = $this->productModel->getProductById($processed_item['product_id']);
                    if ($p_details && isset($p_details['affiliate_commission_type']) && $p_details['affiliate_commission_type'] !== 'none' && isset($p_details['affiliate_commission_value']) && (float)$p_details['affiliate_commission_value'] > 0) {
                        $item_price_for_commission = (float)($processed_item['price'] ?? 0) * (int)($processed_item['quantity'] ?? 0);
                        if ($p_details['affiliate_commission_type'] === 'percentage') {
                            $total_affiliate_commission_for_order += round($item_price_for_commission * ((float)$p_details['affiliate_commission_value'] / 100), 2);
                        } elseif ($p_details['affiliate_commission_type'] === 'fixed') {
                            $total_affiliate_commission_for_order += (float)$p_details['affiliate_commission_value'] * (int)($processed_item['quantity'] ?? 0);
                        }
                    }
                }

                $payment_method_by_affiliate = $_POST['payment_method_by_affiliate'] ?? 'customer_pays';
                $net_payable_by_affiliate = $order_total_price - $total_affiliate_commission_for_order;
                if ($net_payable_by_affiliate < 0) $net_payable_by_affiliate = 0;

                $order_data_for_model = array_merge($customer_data, [
                    'cart_items' => $valid_cart_items_for_model,
                    'total_price' => $order_total_price,
                    'payment_method' => 'cod', 
                    'order_status' => 'pending_confirmation',
                    'payment_status' => 'pending' 
                ]);

                if ($payment_method_by_affiliate === 'pay_from_balance') {
                    $affiliate_balance = (float)($affiliate_user['affiliate_balance'] ?? 0);
                    if ($affiliate_balance >= $net_payable_by_affiliate) {
                        $order_data_for_model['payment_status'] = 'paid'; 
                        $order_data_for_model['payment_method'] = 'affiliate_balance';
                        
                        $order_id = $this->orderModel->createOrder($order_data_for_model, $affiliate_id);
                        if ($order_id) {
                            foreach($valid_cart_items_for_model as $ordered_item){ // کاهش موجودی
                                if (!empty($ordered_item['variation_id'])) $this->attributeModel->decreaseVariationStock((int)$ordered_item['variation_id'], (int)$ordered_item['quantity']);
                                else $this->productModel->decreaseStock((int)$ordered_item['product_id'], (int)$ordered_item['quantity']);
                            }
                            $this->userModel->updateAffiliateBalance($affiliate_id, -$net_payable_by_affiliate);
                            flash('order_success', 'سفارش برای مشتری با شناسه #' . $order_id . ' با موفقیت ثبت و از موجودی شما پرداخت شد.');
                            header('Location: ' . BASE_URL . 'affiliate/dashboard');
                            exit();
                        } else { flash('order_fail', 'خطا در ثبت سفارش.', 'alert alert-danger'); }
                    } else {
                        flash('order_fail', 'موجودی کیف پول شما برای پرداخت این سفارش کافی نیست. موجودی: '.number_format($affiliate_balance).' ت، مبلغ لازم: '.number_format($net_payable_by_affiliate).' ت', 'alert alert-danger');
                    }
                } else { // customer_pays
                    $order_data_for_model['payment_method'] = 'cod'; // یا هر روش پیش‌فرض دیگر
                    $order_data_for_model['payment_status'] = 'pending_on_delivery';
                    
                    $order_id = $this->orderModel->createOrder($order_data_for_model, $affiliate_id);
                    if ($order_id) {
                        foreach($valid_cart_items_for_model as $ordered_item){ // کاهش موجودی
                            if (!empty($ordered_item['variation_id'])) $this->attributeModel->decreaseVariationStock((int)$ordered_item['variation_id'], (int)$ordered_item['quantity']);
                            else $this->productModel->decreaseStock((int)$ordered_item['product_id'], (int)$ordered_item['quantity']);
                        }
                        flash('order_success', 'سفارش برای مشتری با شناسه #' . $order_id . ' با موفقیت ثبت شد (پرداخت توسط مشتری).');
                        header('Location: ' . BASE_URL . 'affiliate/dashboard');
                        exit();
                    } else { flash('order_fail', 'خطا در ثبت سفارش.', 'alert alert-danger'); }
                }
            }
            
            // اگر خطا وجود داشت، فرم را با خطاها و داده‌های قبلی نمایش بده
            $data_for_view = array_merge($customer_data, $_POST, [
                'pageTitle' => 'ثبت سفارش برای مشتری',
                'affiliate_balance' => $affiliate_user['affiliate_balance'] ?? 0.00,
                'categories' => $this->categoryModel->getAllCategories(),
                'products' => $this->productModel->getAllProducts(), // ارسال مجدد محصولات
                'all_attributes' => $this->attributeModel->getAllAttributes(), // برای نمایش ویژگی‌ها در صورت نیاز
                'product_variations_grouped' => [], // این بخش باید با جاوااسکریپت مدیریت شود یا مدل تغییر کند
                'errors' => array_merge($customer_errors, $product_errors_on_submit ?? [])
            ]);
             if(!empty($data_for_view['errors'])) {
                 $error_msg_combined = "";
                 foreach($data_for_view['errors'] as $err_key => $err_val) { 
                     if (is_array($err_val)) $error_msg_combined .= $err_key . ": " . implode(", ",$err_val) . "<br>";
                     else $error_msg_combined .= (string)$err_val . "<br>";
                 }
                 flash('form_error_create_order', $error_msg_combined, 'alert alert-danger');
             }
            $this->view('affiliate/create_order_form', $data_for_view);
            exit();

        } else { // GET
            $data = [
                'pageTitle' => 'ثبت سفارش برای مشتری',
                'affiliate_balance' => $affiliate_user['affiliate_balance'] ?? 0.00,
                'categories' => $this->categoryModel->getAllCategories(),
                'products' => $this->productModel->getAllProducts(), // ارسال تمام محصولات برای نمایش اولیه
                'all_attributes' => $this->attributeModel->getAllAttributes(), // برای نمایش ویژگی‌ها
                'product_variations_grouped' => [], // این باید در مدل ProductAttribute آماده شود و به ویو ارسال گردد
                                                    // یا اینکه با جاوااسکریپت و AJAX لود شود
                'errors' => []
            ];
            // برای محصولات متغیر، نیاز به داده‌های تنوع‌ها داریم
            // بهتر است این داده‌ها به صورت JSON در ویو قرار گیرند تا جاوااسکریپت از آنها استفاده کند
            $all_variations_for_js = [];
            if ($data['products']) {
                foreach ($data['products'] as $p) {
                    if ($p['product_type'] === 'variable') {
                        $variations_for_product = $this->attributeModel->getVariationsForProduct($p['id']);
                        if ($variations_for_product) {
                            $all_variations_for_js[$p['id']] = $variations_for_product;
                        }
                    }
                }
            }
            $data['product_variations_json_map'] = json_encode($all_variations_for_js);


            $this->view('affiliate/create_order_form', $data);
        }
    }
}
?>
