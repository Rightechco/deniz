<?php
// app/controllers/CheckoutController.php

class CheckoutController extends Controller {
    private $userModel;
    private $orderModel;
    private $productModel;
    private $attributeModel; // برای بررسی موجودی تنوع‌ها و کاهش آن

    public function __construct() {
        $this->userModel = $this->model('User');
        // ترتیب بارگذاری مدل‌ها مهم است اگر OrderModel به ProductModel نیاز دارد
        $this->productModel = $this->model('Product'); 
        $this->orderModel = $this->model('Order'); 
        $this->attributeModel = $this->model('ProductAttribute'); 

        if (session_status() == PHP_SESSION_NONE) { 
            session_start(); 
        }

        // بررسی لاگین بودن کاربر باید اولین کار باشد
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = BASE_URL . 'checkout/index';
            flash('checkout_login_required', 'لطفاً برای ادامه پرداخت، ابتدا وارد حساب کاربری خود شوید یا ثبت نام کنید.', 'alert alert-info');
            header('Location: ' . BASE_URL . 'auth/login');
            exit();
        }
    }

    public function index() {
        // بررسی خالی بودن سبد خرید در ابتدای متد index
        if (empty($_SESSION['cart'])) {
            flash('cart_empty', 'سبد خرید شما خالی است. ابتدا محصولی به سبد اضافه کنید.', 'alert alert-warning');
            header('Location: ' . BASE_URL . 'products'); 
            exit();
        }

        // بررسی بارگذاری صحیح تمام مدل‌ها
        if (!$this->userModel || !$this->orderModel || !$this->productModel || !$this->attributeModel) {
            error_log("CheckoutController Error: One or more models are not loaded correctly in index(). User: " .($this->userModel ? 'OK':'FAIL'). ", Order: ".($this->orderModel ? 'OK':'FAIL').", Product: ".($this->productModel ? 'OK':'FAIL').", Attribute: ".($this->attributeModel ? 'OK':'FAIL'));
            flash('error_message', 'خطای داخلی سرور، لطفاً بعداً تلاش کنید یا با پشتیبانی تماس بگیرید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'cart/index'); 
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [ 
                'user_id' => $_SESSION['user_id'],
                'first_name' => trim($_POST['first_name']), 
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']), 
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']), 
                'city' => trim($_POST['city']),
                'postal_code' => trim($_POST['postal_code']),
                'payment_method' => isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'cod',
                'notes' => isset($_POST['notes']) ? trim($_POST['notes']) : '',
                'cart_items' => isset($_SESSION['cart']) ? $_SESSION['cart'] : [],
                'total_price' => 0,
                // فیلدهای خطا
                'first_name_err' => '', 'last_name_err' => '', 'email_err' => '', 'phone_err' => '',
                'address_err' => '', 'city_err' => '', 'postal_code_err' => '', 'payment_method_err' => ''
            ];

            if (empty($data['cart_items'])) {
                flash('cart_empty', 'سبد خرید شما در حین پردازش خالی شد. لطفاً دوباره تلاش کنید.', 'alert alert-warning');
                header('Location: ' . BASE_URL . 'products');
                exit();
            }

            // بررسی مجدد موجودی و محاسبه قیمت کل
            foreach ($data['cart_items'] as $item_cart_id => $item) {
                if (!is_array($item) || !isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                    error_log("CheckoutController - Invalid cart item structure in POST. Item ID in cart: {$item_cart_id}, Item data: " . print_r($item, true));
                    flash('error_message', 'خطایی در اطلاعات سبد خرید شما وجود دارد. لطفاً سبد خرید را بررسی کنید.', 'alert alert-danger');
                    header('Location: ' . BASE_URL . 'cart/index'); 
                    exit();
                }

                $product_id_for_check = (int)$item['product_id']; 
                $variation_id_for_check = isset($item['variation_id']) ? (int)$item['variation_id'] : null;
                $quantity_in_cart = (int)$item['quantity'];
                $current_stock_in_db = 0;
                $product_name_for_msg = htmlspecialchars($item['name']);

                if ($variation_id_for_check) {
                    $variation = $this->attributeModel->getVariationById($variation_id_for_check);
                    if ($variation && $variation['is_active'] && $variation['parent_product_id'] == $product_id_for_check) {
                        $current_stock_in_db = (int)$variation['stock_quantity'];
                    } else { 
                        flash('checkout_stock_issue', 'تنوعی از محصول ' . $product_name_for_msg . ' دیگر معتبر یا فعال نیست. لطفاً سبد خرید خود را بررسی کنید.', 'alert alert-danger'); 
                        header('Location: ' . BASE_URL . 'cart/index'); exit(); 
                    }
                } else { // محصول ساده
                    $product_db = $this->productModel->getProductById($product_id_for_check);
                    if ($product_db && $product_db['product_type'] === 'simple') {
                        $current_stock_in_db = (int)$product_db['stock_quantity'];
                    } else { 
                        flash('checkout_stock_issue', 'محصول ' . $product_name_for_msg . ' دیگر موجود نیست یا از نوع ساده نمی‌باشد. لطفاً سبد خرید خود را بررسی کنید.', 'alert alert-danger'); 
                        header('Location: ' . BASE_URL . 'cart/index'); exit(); 
                    }
                }

                if ($current_stock_in_db < $quantity_in_cart) { 
                    flash('checkout_stock_issue', 'متاسفانه موجودی محصول ' . $product_name_for_msg . ' برای تعداد درخواستی (' . $quantity_in_cart . ') کافی نیست (موجودی فعلی: '.$current_stock_in_db.'). لطفاً سبد خرید خود را بررسی و به‌روز کنید.', 'alert alert-danger'); 
                    header('Location: ' . BASE_URL . 'cart/index'); exit(); 
                }
                $data['total_price'] += (float)$item['price'] * $quantity_in_cart;
            }

            // اعتبارسنجی‌های فرم
            if (empty($data['first_name'])) { $data['first_name_err'] = 'لطفاً نام خود را وارد کنید.'; }
            if (empty($data['last_name'])) { $data['last_name_err'] = 'لطفاً نام خانوادگی خود را وارد کنید.'; }
            if (empty($data['email'])) { $data['email_err'] = 'لطفاً ایمیل خود را وارد کنید.'; }
            elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { $data['email_err'] = 'فرمت ایمیل نامعتبر است.'; }
            if (empty($data['phone'])) { $data['phone_err'] = 'لطفاً شماره تلفن خود را وارد کنید.'; }
            if (empty($data['address'])) { $data['address_err'] = 'لطفاً آدرس خود را وارد کنید.'; }
            if (empty($data['city'])) { $data['city_err'] = 'لطفاً شهر خود را وارد کنید.'; }
            if (empty($data['postal_code'])) { $data['postal_code_err'] = 'لطفاً کد پستی خود را وارد کنید.'; }
            if (empty($data['payment_method'])) { $data['payment_method_err'] = 'لطفاً روش پرداخت را انتخاب کنید.'; }


            if (empty($data['first_name_err']) && empty($data['last_name_err']) && empty($data['email_err']) &&
                empty($data['phone_err']) && empty($data['address_err']) && empty($data['city_err']) &&
                empty($data['postal_code_err']) && empty($data['payment_method_err'])) {

                // اینجا $data['cart_items'] شامل product_id (والد) و variation_id (در صورت وجود) است
                $order_id = $this->orderModel->createOrder($data); 

                if ($order_id) {
                    $stock_update_successful = true;
                    foreach($data['cart_items'] as $cart_item) {
                        $product_id_parent = (int)$cart_item['product_id']; 
                        $variation_id_to_decrease = isset($cart_item['variation_id']) ? (int)$cart_item['variation_id'] : null;
                        $quantity_to_decrease = (int)$cart_item['quantity'];
                        
                        $decrease_result = false;
                        if ($variation_id_to_decrease) {
                            // کاهش موجودی تنوع
                            if (method_exists($this->attributeModel, 'decreaseVariationStock')) {
                                error_log("CheckoutController: Decreasing stock for variation_id: {$variation_id_to_decrease} by {$quantity_to_decrease} for order {$order_id}");
                                $decrease_result = $this->attributeModel->decreaseVariationStock($variation_id_to_decrease, $quantity_to_decrease);
                            } else {
                                error_log("CRITICAL: Method decreaseVariationStock does not exist in ProductAttributeModel for var ID {$variation_id_to_decrease}");
                                $stock_update_successful = false; 
                            }
                        } else { // محصول ساده
                            error_log("CheckoutController: Decreasing stock for simple product_id: {$product_id_parent} by {$quantity_to_decrease} for order {$order_id}");
                            $decrease_result = $this->productModel->decreaseStock($product_id_parent, $quantity_to_decrease);
                        }

                        if (!$decrease_result) {
                            error_log("CheckoutController: Failed to decrease stock for item (ParentP:{$product_id_parent}, VarID:{$variation_id_to_decrease}) in order {$order_id}");
                            $stock_update_successful = false; 
                        } else {
                             error_log("CheckoutController: Stock successfully decreased for item (ParentP:{$product_id_parent}, VarID:{$variation_id_to_decrease}) in order {$order_id}");
                        }
                    }

                    unset($_SESSION['cart']);
                    $_SESSION['last_order_id'] = $order_id;
                    
                    // === پاک کردن کد همکاری از سشن پس از ثبت موفق سفارش ===
                    if (isset($_SESSION['referred_by_affiliate_code'])) {
                        unset($_SESSION['referred_by_affiliate_code']);
                        error_log("Affiliate code unset from session after order {$order_id}.");
                    }
                    // === پایان پاک کردن کد همکاری ===
                    
                    flash('order_success', 'سفارش شما با شناسه #' . $order_id . ' با موفقیت ثبت شد!');
                    if (!$stock_update_successful) {
                         flash('order_warning', 'توجه: در به‌روزرسانی موجودی برخی محصولات مشکلی پیش آمد. سفارش شما ثبت شده و توسط پشتیبانی بررسی خواهد شد.', 'alert alert-warning');
                    }
                    header('Location: ' . BASE_URL . 'checkout/success');
                    exit();

                } else { 
                    flash('order_fail', 'خطایی در ثبت سفارش شما در پایگاه داده رخ داد. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'alert alert-danger');
                }
            } 
            
            $data['pageTitle'] = 'تکمیل اطلاعات پرداخت';
            $has_form_errors = !empty($data['first_name_err']) || !empty($data['last_name_err']) || !empty($data['email_err']) ||
                               !empty($data['phone_err']) || !empty($data['address_err']) || !empty($data['city_err']) ||
                               !empty($data['postal_code_err']) || !empty($data['payment_method_err']);
            if($has_form_errors){ 
                 flash('checkout_form_error', 'لطفاً خطاهای فرم را برطرف کنید.', 'alert alert-danger');
            }
            $this->view('checkout/index', $data); 
            exit(); 

        } else { // اگر درخواست GET است
            $user = $this->userModel->findUserById($_SESSION['user_id']);
            $cart_items_session = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
            $total_price_calc = 0;
            if (is_array($cart_items_session)) {
                foreach ($cart_items_session as $item_key => $item) {
                     if (is_array($item) && isset($item['price']) && isset($item['quantity'])) {
                        $total_price_calc += (float)$item['price'] * (int)$item['quantity'];
                    } else {
                        error_log("CheckoutController GET: Invalid cart item structure in session for key {$item_key}. Item: " . print_r($item, true));
                        flash('error_message', 'خطایی در اطلاعات سبد خرید شما وجود دارد. لطفاً سبد خرید را بررسی کنید.', 'alert alert-danger');
                        header('Location: ' . BASE_URL . 'cart/index');
                        exit();
                    }
                }
            }

            $data = [
                'pageTitle' => 'تکمیل اطلاعات پرداخت',
                'first_name' => $user['first_name'] ?? '', 
                'last_name' => $user['last_name'] ?? '',
                'email' => $user['email'] ?? '', 
                'phone' => $user['phone'] ?? '', 
                'address' => '', 
                'city' => '', 
                'postal_code' => '',
                'payment_method' => 'cod', 
                'notes' => '',
                'cart_items' => $cart_items_session, 
                'total_price' => $total_price_calc,
                'first_name_err' => '', 'last_name_err' => '', 'email_err' => '', 'phone_err' => '',
                'address_err' => '', 'city_err' => '', 'postal_code_err' => '', 'payment_method_err' => ''
            ];
            $this->view('checkout/index', $data);
        }
    }

    public function success() {
        $last_order_id = isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : null;
        
        if (!isset($_SESSION['flash']['order_success']) && !$last_order_id) { 
            header('Location: ' . BASE_URL);
            exit();
        }
        $data = [
            'pageTitle' => 'سفارش شما با موفقیت ثبت شد',
            'order_id' => $last_order_id
        ];
        // unset($_SESSION['last_order_id']); // می‌توانید پس از نمایش، آن را پاک کنید
        $this->view('checkout/success', $data);
    }
}
?>
