<?php
// app/controllers/AuthController.php

class AuthController extends Controller {
    private $userModel;
    private $categoryModel; 

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->categoryModel = $this->model('Category'); 
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register() {
        $menu_categories_data = method_exists($this->categoryModel, 'getParentCategories') ? $this->categoryModel->getParentCategories(5) : [];
        
        $data = [
            'pageTitle' => 'ایجاد حساب کاربری جدید',
            'username' => $_SESSION['_form_data_register']['username'] ?? '', 
            'email' => $_SESSION['_form_data_register']['email'] ?? '', 
            'password' => '', 'confirm_password' => '', // Passwords should not be repopulated
            'first_name' => $_SESSION['_form_data_register']['first_name'] ?? '', 
            'last_name' => $_SESSION['_form_data_register']['last_name'] ?? '', 
            'phone' => $_SESSION['_form_data_register']['phone'] ?? '',
            'account_type' => $_SESSION['_form_data_register']['account_type'] ?? 'customer', 
            'shop_name' => $_SESSION['_form_data_register']['shop_name'] ?? '', 
            'vendor_payment_details' => $_SESSION['_form_data_register']['vendor_payment_details'] ?? '', 
            'affiliate_payment_details' => $_SESSION['_form_data_register']['affiliate_payment_details'] ?? '', 
            'username_err' => '', 'email_err' => '', 'password_err' => '', 'confirm_password_err' => '',
            'first_name_err' => '', 'last_name_err' => '', 'phone_err' => '', 'account_type_err' => '',
            'shop_name_err' => '', 'vendor_payment_details_err' => '', 'affiliate_payment_details_err' => '',
            'menu_categories' => $menu_categories_data
        ];
        unset($_SESSION['_form_data_register']); // Clear after use

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Update data with current POST values
            $data['username'] = trim($_POST['username'] ?? '');
            $data['email'] = trim($_POST['email'] ?? '');
            $data['password'] = $_POST['password'] ?? ''; 
            $data['confirm_password'] = $_POST['confirm_password'] ?? '';
            $data['first_name'] = trim($_POST['first_name'] ?? '');
            $data['last_name'] = trim($_POST['last_name'] ?? '');
            $data['phone'] = trim($_POST['phone'] ?? '');
            $data['account_type'] = trim($_POST['account_type'] ?? 'customer');
            
            if ($data['account_type'] === 'vendor') {
                $data['shop_name'] = trim($_POST['shop_name'] ?? '');
                $data['vendor_payment_details'] = trim($_POST['vendor_payment_details'] ?? '');
            }
            if ($data['account_type'] === 'affiliate') {
                $data['affiliate_payment_details'] = trim($_POST['affiliate_payment_details'] ?? '');
            }

            // --- Validation ---
            if (empty($data['username'])) { $data['username_err'] = 'لطفاً نام کاربری را وارد کنید.'; }
            elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $data['username'])) { $data['username_err'] = 'نام کاربری باید بین ۳ تا ۲۰ کاراکتر (حروف انگلیسی، اعداد، "_") باشد.';}
            elseif ($this->userModel->findUserByUsername($data['username'])) { $data['username_err'] = 'این نام کاربری قبلاً ثبت شده است.';}
            
            if (empty($data['email'])) { $data['email_err'] = 'لطفاً ایمیل را وارد کنید.'; }
            elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { $data['email_err'] = 'فرمت ایمیل نامعتبر است.'; }
            elseif ($this->userModel->findUserByEmail($data['email'])) { $data['email_err'] = 'این ایمیل قبلاً ثبت شده است.';}

            if (empty($data['password'])) { $data['password_err'] = 'لطفاً رمز عبور را وارد کنید.'; }
            elseif (strlen($data['password']) < 6) { $data['password_err'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';}
            
            if (empty($data['confirm_password'])) { $data['confirm_password_err'] = 'لطفاً تکرار رمز عبور را وارد کنید.';}
            elseif ($data['password'] !== $data['confirm_password']) { $data['confirm_password_err'] = 'رمزهای عبور مطابقت ندارند.';}

            if (!in_array($data['account_type'], ['customer', 'vendor', 'affiliate'])) {
                $data['account_type_err'] = 'نوع حساب نامعتبر است.'; $data['account_type'] = 'customer';
            }

            if (empty($data['username_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err']) && empty($data['account_type_err']) && empty($data['shop_name_err'])) {
                
                $user_data_to_register = [
                    'username' => $data['username'], 
                    'email' => $data['email'],
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT), // Hash password HERE
                    'first_name' => $data['first_name'], 
                    'last_name' => $data['last_name'], 
                    'phone' => $data['phone'],
                    'role' => $data['account_type'],
                    'shop_name' => ($data['account_type'] === 'vendor') ? $data['shop_name'] : null,
                    'vendor_payment_details' => ($data['account_type'] === 'vendor') ? $data['vendor_payment_details'] : null,
                    'affiliate_payment_details' => ($data['account_type'] === 'affiliate') ? $data['affiliate_payment_details'] : null,
                    'status' => 1 
                ];
                
                $registration_result = $this->userModel->register($user_data_to_register);

                if (is_int($registration_result) && $registration_result > 0) {
                    flash('register_success', 'ثبت نام شما با موفقیت انجام شد. اکنون می‌توانید وارد شوید.');
                    $_SESSION['_form_data_login'] = ['username_or_email' => $data['username']]; 
                    header('Location: ' . BASE_URL . 'auth/login');
                    exit();
                } else {
                    $reg_fail_msg = is_string($registration_result) ? $registration_result : 'خطایی در هنگام ثبت نام رخ داد. لطفاً دوباره تلاش کنید.';
                    flash('register_fail', $reg_fail_msg, 'alert alert-danger');
                    $_SESSION['_form_data_register'] = $data; // Store form data to repopulate
                    header('Location: ' . BASE_URL . 'auth/register'); // Redirect to show flash and repopulate
                    exit();
                }
            } else {
                 flash('register_fail', 'لطفاً خطاهای فرم را برطرف نمایید.', 'alert alert-danger'); 
                 $_SESSION['_form_data_register'] = $data; // Store form data to repopulate
                 header('Location: ' . BASE_URL . 'auth/register'); // Redirect to show flash and repopulate
                 exit();
            }
        }
        // For GET request, or if POST failed and redirected back
        $this->view('auth/register', $data);
    }

    public function login() {
        $menu_categories_data = method_exists($this->categoryModel, 'getParentCategories') ? $this->categoryModel->getParentCategories(5) : [];
        $data = [
            'pageTitle' => 'ورود به حساب کاربری',
            'username_or_email' => '', // Changed from email_username
            'password' => '',
            'username_or_email_err' => '',
            'password_err' => '',
            'menu_categories' => $menu_categories_data
        ];

        // Repopulate username/email if redirected from a failed POST attempt or successful registration
        if (isset($_SESSION['_form_data_login']['username_or_email'])) {
            $data['username_or_email'] = $_SESSION['_form_data_login']['username_or_email'];
            unset($_SESSION['_form_data_login']);
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data['username_or_email'] = trim($_POST['username_or_email'] ?? '');
            $data['password'] = $_POST['password'] ?? ''; // Do not trim password

            if(empty($data['username_or_email'])){ $data['username_or_email_err'] = 'لطفاً نام کاربری یا ایمیل را وارد کنید.'; }
            if(empty($data['password'])){ $data['password_err'] = 'لطفاً رمز عبور را وارد کنید.'; }

            if(empty($data['username_or_email_err']) && empty($data['password_err'])){
                $loggedInUser = $this->userModel->login($data['username_or_email'], $data['password']);

                if($loggedInUser){
                    $this->createUserSession($loggedInUser);
                    
                    $redirect_url = $_SESSION['redirect_after_login'] ?? null;
                    unset($_SESSION['redirect_after_login']); 

                    if (!$redirect_url) { 
                        switch ($loggedInUser['role']) {
                            case 'admin': $redirect_url = BASE_URL . 'admin/dashboard'; break;
                            case 'vendor': $redirect_url = BASE_URL . 'vendor/dashboard'; break;
                            case 'affiliate': $redirect_url = BASE_URL . 'affiliate/dashboard'; break;
                            case 'customer': default: $redirect_url = BASE_URL . 'customer/dashboard'; break;
                        }
                    }
                    // flash('login_success', 'شما با موفقیت وارد شدید.', 'alert alert-success'); // Optional
                    header('Location: ' . $redirect_url);
                    exit();
                } else {
                    flash('login_fail', 'نام کاربری/ایمیل یا رمز عبور اشتباه است یا حساب شما غیرفعال می‌باشد.', 'alert alert-danger');
                }
            } else {
                $error_combined = ($data['username_or_email_err'] ?? '') . 
                                  (!empty($data['username_or_email_err']) && !empty($data['password_err']) ? '<br>' : '') . 
                                  ($data['password_err'] ?? '');
                if (!empty(trim($error_combined))) {
                    flash('login_fail', $error_combined, 'alert alert-danger');
                }
            }
            // If validation errors or login failed, redirect back to login to show flash message
            // and preserve input (except password)
            $_SESSION['_form_data_login'] = ['username_or_email' => $data['username_or_email']];
            header('Location: ' . BASE_URL . 'auth/login');
            exit();
        }
        
        // For initial GET request or after redirect from POST failure (to show flash messages)
        $this->view('auth/login', $data);
    }

    private function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_first_name'] = $user['first_name'] ?? '';
        $_SESSION['user_last_name'] = $user['last_name'] ?? '';
        $_SESSION['shop_name'] = $user['shop_name'] ?? ''; 
        $_SESSION['affiliate_code'] = $user['affiliate_code'] ?? ''; 
        $_SESSION['user_phone'] = $user['phone'] ?? '';
    }

    public function logout() {
        unset($_SESSION['user_id']); 
        unset($_SESSION['username']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_first_name']);
        unset($_SESSION['user_last_name']);
        unset($_SESSION['shop_name']);
        unset($_SESSION['affiliate_code']);
        unset($_SESSION['user_phone']);
        unset($_SESSION['cart']); 
        session_destroy();
        flash('logout_success', 'شما با موفقیت از حساب کاربری خود خارج شدید.', 'alert alert-info');
        header('Location: ' . BASE_URL . 'auth/login');
        exit();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>
