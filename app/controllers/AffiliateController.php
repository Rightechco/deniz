<?php
// app/controllers/AffiliateController.php

class AffiliateController extends Controller {
    private $userModel;
    private $orderModel;
    private $productModel;
    private $categoryModel;
    private $attributeModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->attributeModel = $this->model('ProductAttribute');

        if (!$this->userModel || !$this->orderModel || !$this->productModel || !$this->categoryModel || !$this->attributeModel) {
            error_log("AffiliateController FATAL: One or more models failed to load in constructor.");
            die("A critical error occurred: Models could not be loaded in AffiliateController.");
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'affiliate') {
            flash('auth_required', 'برای دسترسی به پنل همکاری، لطفاً ابتدا به عنوان همکار وارد شوید.', 'alert alert-danger');
            $_SESSION['redirect_after_login'] = BASE_URL . 'affiliate/dashboard'; 
            header('Location: ' . BASE_URL . 'auth/login');
            exit();
        }
    }

    public function dashboard() {
        $affiliate_id = $_SESSION['user_id'];
        $affiliate_user = $this->userModel->findUserById($affiliate_id);
        
        if (!$affiliate_user) {
            flash('error_message', 'اطلاعات کاربری شما یافت نشد. لطفاً دوباره وارد شوید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'auth/logout');
            exit();
        }

        $commissions_summary = $this->userModel->getAffiliateCommissionsSummary($affiliate_id);
        if ($commissions_summary === false || !is_array($commissions_summary)) {
            error_log("AffiliateController::dashboard - Failed to get commission summary for affiliate ID: {$affiliate_id}");
            $commissions_summary = ['total_clicks' => 0, 'total_sales' => 0, 'total_commissions_earned' => 0, 'total_commissions_paid' => 0, 'withdrawable_balance' => 0 ];
            flash('info_message', 'در حال حاضر آمار دقیقی برای نمایش وجود ندارد.', 'alert alert-info');
        }

        $recent_commissions = $this->orderModel->getCommissionsByAffiliateId($affiliate_id, 5);

        $data = [
            'pageTitle' => 'داشبورد همکاری در فروش',
            'affiliate_user' => $affiliate_user,
            'commissions_summary' => $commissions_summary,
            'recent_commissions' => $recent_commissions ?: []
        ];
        $this->view('affiliate/dashboard', $data);
    }

    public function marketingTools() {
        $affiliate_id = $_SESSION['user_id'];
        $affiliate_user = $this->userModel->findUserById($affiliate_id);

        if (!$affiliate_user) {
            flash('error_message', 'اطلاعات کاربری شما یافت نشد. لطفاً دوباره وارد شوید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'auth/logout');
            exit();
        }

        $affiliate_code = $affiliate_user['affiliate_code'] ?? '';
        $general_affiliate_link = !empty($affiliate_code) ? (BASE_URL . '?ref=' . $affiliate_code) : '';
        
        $products = $this->productModel->getAllProducts(); 

        $data = [
            'pageTitle' => 'ابزارهای بازاریابی',
            'affiliate_user' => $affiliate_user,
            'affiliate_code' => $affiliate_code,
            'general_affiliate_link' => $general_affiliate_link,
            'products' => $products ?: []
        ];
        $this->view('affiliate/marketing_tools', $data);
    }

    public function commissions() {
        $affiliate_id = $_SESSION['user_id'];
        $commissions = $this->orderModel->getCommissionsByAffiliateId($affiliate_id);
        $affiliate_user = $this->userModel->findUserById($affiliate_id);

        $data = [
            'pageTitle' => 'لیست کامل کمیسیون‌ها' . (isset($affiliate_user['username']) ? ' (' . htmlspecialchars($affiliate_user['username']) . ')' : ''),
            'affiliate_user' => $affiliate_user,
            'commissions' => $commissions ?: []
        ];
        $this->view('affiliate/commissions_list', $data);
    }

    public function payoutHistory() {
        $affiliate_id = $_SESSION['user_id'];
        $payouts = $this->orderModel->getPayoutsByAffiliateId($affiliate_id);
        $affiliate_user = $this->userModel->findUserById($affiliate_id);

        $data = [
            'pageTitle' => 'تاریخچه تسویه حساب‌ها',
            'affiliate_user' => $affiliate_user,
            'payouts' => $payouts ?: []
        ];
        $this->view('affiliate/payout_history', $data);
    }

    public function requestPayout() {
        $affiliate_id = $_SESSION['user_id'];
        $affiliate_user = $this->userModel->findUserById($affiliate_id);

        if (!$affiliate_user) {
            flash('error_message', 'اطلاعات کاربری شما یافت نشد. لطفاً دوباره وارد شوید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'auth/logout');
            exit();
        }
        
        if (!method_exists($this->userModel, 'getAffiliateWithdrawableBalance')) {
            error_log("AffiliateController FATAL: Method getAffiliateWithdrawableBalance does not exist in UserModel.");
            flash('error_message', 'خطای سیستمی: امکان دریافت موجودی قابل برداشت وجود ندارد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'affiliate/dashboard');
            exit();
        }
        $withdrawable_balance = $this->userModel->getAffiliateWithdrawableBalance($affiliate_id); 
        if ($withdrawable_balance === false) {
            error_log("AffiliateController::requestPayout - Failed to get withdrawable balance for affiliate ID: {$affiliate_id}");
            $withdrawable_balance = 0;
            flash('error_message', 'خطا در دریافت موجودی قابل برداشت. لطفاً با پشتیبانی تماس بگیرید.', 'alert alert-danger');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $requested_amount_input = trim($_POST['requested_amount'] ?? '0');
            $payment_details_input = trim($_POST['payment_details'] ?? ($affiliate_user['affiliate_payment_details'] ?? ''));

            $data_for_view = [
                'pageTitle' => 'درخواست تسویه حساب',
                'affiliate_user' => $affiliate_user,
                'current_balance' => (float)$withdrawable_balance,
                'requested_amount' => $requested_amount_input,
                'payment_details' => $payment_details_input,
                'amount_err' => '',
                'details_err' => ''
            ];

            $requested_amount = (float)$requested_amount_input;
            $min_payout_amount = defined('MIN_AFFILIATE_PAYOUT_AMOUNT') ? (float)MIN_AFFILIATE_PAYOUT_AMOUNT : 10000; 

            if ($requested_amount < $min_payout_amount) {
                $data_for_view['amount_err'] = 'مبلغ درخواستی باید حداقل ' . number_format($min_payout_amount) . ' تومان باشد.';
            } elseif ($requested_amount > (float)$withdrawable_balance) {
                $data_for_view['amount_err'] = 'مبلغ درخواستی نمی‌تواند بیشتر از موجودی قابل برداشت شما باشد.';
            }
            if (empty($data_for_view['payment_details'])) {
                $data_for_view['details_err'] = 'لطفاً جزئیات حساب بانکی (مانند شماره شبا) را برای واریز وارد کنید.';
            }

            if (empty($data_for_view['amount_err']) && empty($data_for_view['details_err'])) {
                $payout_id = $this->orderModel->createAffiliatePayoutRequest($affiliate_id, $requested_amount, $data_for_view['payment_details']);
                if ($payout_id) {
                    flash('payout_request_success', 'درخواست تسویه شما با موفقیت ثبت شد و پس از بررسی توسط مدیر، پرداخت خواهد شد.');
                    header('Location: ' . BASE_URL . 'affiliate/payoutHistory');
                    exit();
                } else {
                    flash('payout_request_fail', 'خطا در ثبت درخواست تسویه. لطفاً دوباره تلاش کنید.', 'alert alert-danger');
                    $this->view('affiliate/request_payout_form', $data_for_view);
                    exit();
                }
            }
            $this->view('affiliate/request_payout_form', $data_for_view);
        } else { // GET request
            $data = [
                'pageTitle' => 'درخواست تسویه حساب',
                'affiliate_user' => $affiliate_user,
                'current_balance' => (float)$withdrawable_balance,
                'requested_amount' => (float)$withdrawable_balance > 0 ? (float)$withdrawable_balance : '',
                'payment_details' => $affiliate_user['affiliate_payment_details'] ?? '',
                'amount_err' => '',
                'details_err' => ''
            ];
            $this->view('affiliate/request_payout_form', $data);
        }
    }

    public function createOrderForCustomer() {
        $affiliate_id = $_SESSION['user_id']; 
        $affiliate_user = $this->userModel->findUserById($affiliate_id);

        if (!$affiliate_user) {
            flash('error_message', 'اطلاعات کاربری شما (همکار) یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'auth/logout');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $customer_data_from_form = [
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
            
            if (empty($customer_data_from_form['first_name'])) { $customer_errors['first_name_err'] = 'نام مشتری الزامی است.'; }
            if (empty($customer_data_from_form['last_name'])) { $customer_errors['last_name_err'] = 'نام خانوادگی مشتری الزامی است.'; }
            if (empty($customer_data_from_form['email'])) { $customer_errors['email_err'] = 'ایمیل مشتری الزامی است.'; }
            elseif (!filter_var($customer_data_from_form['email'], FILTER_VALIDATE_EMAIL)) { $customer_errors['email_err'] = 'فرمت ایمیل نامعتبر است.'; }
            if (empty($customer_data_from_form['phone'])) { $customer_errors['phone_err'] = 'تلفن مشتری الزامی است.'; }
            
            $cart_items_json_raw = $_POST['order_items_json'] ?? '[]';
            error_log("AffiliateController - Received order_items_json (raw): " . $cart_items_json_raw);

            // Decode HTML entities before JSON decoding
            $cart_items_json_decoded_entities = html_entity_decode($cart_items_json_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            error_log("AffiliateController - order_items_json (after html_entity_decode): " . $cart_items_json_decoded_entities);

            $cart_items_array_from_client = json_decode($cart_items_json_decoded_entities, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("AffiliateController - JSON Decode Error: " . json_last_error_msg() . " | Original JSON string: " . $cart_items_json_decoded_entities);
                flash('order_fail_validation', 'خطا در پردازش اطلاعات سبد خرید. اطلاعات نامعتبر است.', 'alert alert-danger');
                $this->renderCreateOrderFormWithError($affiliate_user, $customer_data_from_form, $customer_errors, []); // Pass empty array if JSON is invalid
                exit();
            }
            error_log("AffiliateController - Decoded cart_items_array: " . print_r($cart_items_array_from_client, true));


            if (empty($cart_items_array_from_client) || !is_array($cart_items_array_from_client)) {
                flash('order_fail_validation', 'سبد سفارش خالی است یا اطلاعات آن نامعتبر می‌باشد.', 'alert alert-danger');
                $this->renderCreateOrderFormWithError($affiliate_user, $customer_data_from_form, $customer_errors, $cart_items_array_from_client);
                exit();
            }
            
            $order_total_price = 0; 
            $valid_cart_items_for_model = [];
            $product_errors_on_submit = [];

            foreach ($cart_items_array_from_client as $client_item) {
                $product_id = (int)($client_item['product_id'] ?? 0);
                $variation_id = !empty($client_item['variation_id']) ? (int)$client_item['variation_id'] : null;
                $quantity = (int)($client_item['quantity'] ?? 0);
                $item_name_for_error = htmlspecialchars($client_item['name'] ?? 'محصول ناشناس');

                if ($quantity <= 0) {
                    $product_errors_on_submit[] = "تعداد برای محصول '{$item_name_for_error}' باید مثبت باشد.";
                    continue;
                }

                $product_db_details = null;
                $item_price_from_db = 0;
                $item_stock_ok = false;
                $actual_item_name = $item_name_for_error; 

                if ($variation_id) {
                    $variation_details = $this->attributeModel->getVariationById($variation_id);
                    if ($variation_details && (int)$variation_details['is_active'] === 1 && (int)$variation_details['parent_product_id'] == $product_id) {
                        if ((int)$variation_details['stock_quantity'] >= $quantity) {
                            $item_price_from_db = (float)$variation_details['price'];
                            $actual_item_name = $variation_details['parent_product_name'] ?? $item_name_for_error;
                            if (!empty($variation_details['attributes'])) {
                                $attr_names = array_column($variation_details['attributes'], 'attribute_value');
                                $actual_item_name .= ' (' . implode(' / ', $attr_names) . ')';
                            }
                            $item_stock_ok = true;
                            $product_db_details = $this->productModel->getProductById($product_id); 
                        } else {
                            $product_errors_on_submit[] = "موجودی تنوع '" . htmlspecialchars($actual_item_name) . "' کافی نیست (موجود: {$variation_details['stock_quantity']}).";
                        }
                    } else { $product_errors_on_submit[] = "تنوع '" . htmlspecialchars($actual_item_name) . "' نامعتبر یا غیرفعال است."; }
                } else { 
                    $product_db_details = $this->productModel->getProductById($product_id);
                    if ($product_db_details && $product_db_details['product_type'] === 'simple') {
                        if ((int)$product_db_details['stock_quantity'] >= $quantity) {
                            $item_price_from_db = (float)$product_db_details['price'];
                            $actual_item_name = $product_db_details['name'];
                            $item_stock_ok = true;
                        } else {
                            $product_errors_on_submit[] = "موجودی محصول '" . htmlspecialchars($actual_item_name) . "' کافی نیست (موجود: {$product_db_details['stock_quantity']}).";
                        }
                    } else { $product_errors_on_submit[] = "محصول '" . htmlspecialchars($actual_item_name) . "' نامعتبر یا از نوع ساده نیست."; }
                }

                if ($item_stock_ok && $product_db_details) { 
                    $valid_cart_items_for_model[] = [
                        'product_id' => $product_id,
                        'variation_id' => $variation_id,
                        'name' => $actual_item_name, 
                        'quantity' => $quantity,
                        'price' => $item_price_from_db, 
                        'affiliate_commission_type' => $product_db_details['affiliate_commission_type'] ?? null,
                        'affiliate_commission_value' => $product_db_details['affiliate_commission_value'] ?? null,
                    ];
                    $order_total_price += $item_price_from_db * $quantity;
                }
            }
            
            if (!empty($product_errors_on_submit)) {
                 flash('order_fail_validation', "خطا در محصولات انتخابی: <br>" . implode("<br>", $product_errors_on_submit), 'alert alert-danger');
                 $this->renderCreateOrderFormWithError($affiliate_user, $customer_data_from_form, array_merge($customer_errors, ['product_selection_err' => implode("; ",$product_errors_on_submit)]), $cart_items_array_from_client);
                 exit();
            }
            if (empty($valid_cart_items_for_model)) { 
                 flash('order_fail_validation', 'هیچ محصول معتبری برای ثبت سفارش انتخاب نشده است.', 'alert alert-danger');
                 $this->renderCreateOrderFormWithError($affiliate_user, $customer_data_from_form, $customer_errors, $cart_items_array_from_client);
                 exit();
            }

            if (empty($customer_errors)) {
                $customerUser = $this->userModel->findUserByEmail($customer_data_from_form['email']);
                $customer_user_id_for_order = null;

                if ($customerUser) {
                    $customer_user_id_for_order = $customerUser['id'];
                } else {
                    $new_customer_data = [
                        'username'   => $customer_data_from_form['email'], 
                        'email'      => $customer_data_from_form['email'],
                        'password'   => bin2hex(random_bytes(8)), 
                        'first_name' => $customer_data_from_form['first_name'],
                        'last_name'  => $customer_data_from_form['last_name'],
                        'phone'      => $customer_data_from_form['phone'],
                        'role'       => 'customer', 
                        'status'     => 1 
                    ];
                    $registration_result = $this->userModel->register($new_customer_data, true);
                    if (is_int($registration_result) && $registration_result > 0) {
                        $customer_user_id_for_order = $registration_result;
                    } else {
                        $reg_error = is_string($registration_result) ? $registration_result : 'خطای نامشخص در ثبت کاربر جدید.';
                        flash('order_fail_user', 'خطا در ایجاد حساب مشتری: ' . $reg_error, 'alert alert-danger');
                        $this->renderCreateOrderFormWithError($affiliate_user, $customer_data_from_form, array_merge($customer_errors, ['user_registration_err' => $reg_error]), $cart_items_array_from_client);
                        exit();
                    }
                }

                if ($customer_user_id_for_order) {
                    $order_data_for_model = array_merge($customer_data_from_form, [
                        'user_id'       => $customer_user_id_for_order,
                        'cart_items'    => $valid_cart_items_for_model, 
                        'total_price'   => $order_total_price, 
                        'payment_method'=> 'cod', 
                        'order_status'  => 'pending_confirmation',
                        'payment_status'=> 'pending_on_delivery' 
                    ]);
                                        
                    $order_id = $this->orderModel->createOrder($order_data_for_model, $affiliate_id); 
                    
                    if ($order_id) {
                        foreach($valid_cart_items_for_model as $ordered_item){ 
                            if (!empty($ordered_item['variation_id'])) {
                                $this->attributeModel->decreaseVariationStock((int)$ordered_item['variation_id'], (int)$ordered_item['quantity']);
                            } else {
                                $this->productModel->decreaseStock((int)$ordered_item['product_id'], (int)$ordered_item['quantity']);
                            }
                        }
                        flash('order_success', 'سفارش برای مشتری با شناسه #' . $order_id . ' با موفقیت ثبت شد.');
                        header('Location: ' . BASE_URL . 'affiliate/dashboard'); 
                        exit();
                    } else { 
                        flash('order_fail_db', 'خطا در ثبت نهایی سفارش در پایگاه داده.', 'alert alert-danger');
                    }
                } else {
                     flash('order_fail_user', 'خطای بحرانی: شناسه کاربری مشتری برای ثبت سفارش در دسترس نیست.', 'alert alert-danger');
                }
            }
            $this->renderCreateOrderFormWithError($affiliate_user, $customer_data_from_form, array_merge($customer_errors, $product_errors_on_submit), $cart_items_array_from_client);
            exit();

        } else { // GET request
            $this->renderCreateOrderFormWithError($affiliate_user);
        }
    }

    private function renderCreateOrderFormWithError($affiliate_user, $form_data = [], $errors = [], $current_cart_items_for_repopulation = []) {
        $data = [
            'pageTitle' => 'ثبت سفارش برای مشتری توسط همکار',
            'affiliate_user' => $affiliate_user,
            'affiliate_balance' => $affiliate_user['affiliate_balance'] ?? 0.00,
            'categories' => $this->categoryModel->getAllCategories() ?: [],
            'products' => $this->productModel->getAllProducts() ?: [],
            'all_attributes' => $this->attributeModel->getAllAttributesWithValues() ?: [],
            'errors' => $errors,
            'current_cart_items_json' => json_encode($current_cart_items_for_repopulation)
        ];
        $data = array_merge($data, $form_data);

        $all_variations_for_js = [];
        if (!empty($data['products'])) {
            foreach ($data['products'] as $p) {
                if (isset($p['product_type']) && $p['product_type'] === 'variable' && isset($p['id'])) {
                    $variations_for_product = $this->attributeModel->getVariationsForProduct($p['id']);
                    if ($variations_for_product) {
                        $all_variations_for_js[$p['id']] = $variations_for_product;
                    }
                }
            }
        }
        $data['product_variations_json_map'] = json_encode($all_variations_for_js);
        
        if(!empty($errors)) {
             $error_msg_combined = "";
             foreach($errors as $err_key => $err_val) { 
                 if (is_array($err_val)) $error_msg_combined .= implode("<br>",$err_val) . "<br>";
                 else $error_msg_combined .= (string)$err_val . "<br>";
             }
             if(!empty($error_msg_combined)) flash('form_error_create_order', $error_msg_combined, 'alert alert-danger', true);
         }
        $this->view('affiliate/create_order_form', $data);
    }
}
?>
