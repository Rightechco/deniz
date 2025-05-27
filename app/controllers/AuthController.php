<?php
// app/controllers/AuthController.php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        // بارگذاری مدل User
        // متد model() از کلاس Controller والد به ارث رسیده است.
        $this->userModel = $this->model('User');
        // اطمینان از اینکه Database.php توسط User model قابل دسترسی است.
        // اگر مدل User خودش new Database() می‌کند، باید Database.php قبل از فراخوانی $this->model('User') لود شده باشد.
        // Database.php در index.php لود شده، پس مشکلی نیست.
    }

    // متد برای نمایش فرم و پردازش ثبت نام
    public function register() {
        // بررسی اینکه آیا درخواست از نوع POST است (یعنی فرم ارسال شده)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // پردازش فرم

            // پاکسازی داده‌های POST برای جلوگیری از حملات XSS
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'role' => 'customer', // نقش پیش‌فرض برای کاربران جدید
                'first_name_err' => '',
                'last_name_err' => '',
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // اعتبارسنجی نام
            if (empty($data['first_name'])) {
                $data['first_name_err'] = 'لطفا نام خود را وارد کنید.';
            }
            if (empty($data['last_name'])) {
                $data['last_name_err'] = 'لطفا نام خانوادگی خود را وارد کنید.';
            }

            // اعتبارسنجی نام کاربری
            if (empty($data['username'])) {
                $data['username_err'] = 'لطفا نام کاربری را وارد کنید.';
            } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $data['username'])) {
                $data['username_err'] = 'نام کاربری باید بین ۳ تا ۲۰ حرف و شامل حروف انگلیسی، اعداد و _ باشد.';
            } elseif ($this->userModel->findUserByUsername($data['username'])) {
                $data['username_err'] = 'این نام کاربری قبلا ثبت شده است.';
            }

            // اعتبارسنجی ایمیل
            if (empty($data['email'])) {
                $data['email_err'] = 'لطفا ایمیل خود را وارد کنید.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'فرمت ایمیل نامعتبر است.';
            } elseif ($this->userModel->findUserByEmail($data['email'])) {
                $data['email_err'] = 'این ایمیل قبلا ثبت شده است.';
            }

            // اعتبارسنجی رمز عبور
            if (empty($data['password'])) {
                $data['password_err'] = 'لطفا رمز عبور را وارد کنید.';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
            }

            // اعتبارسنجی تکرار رمز عبور
            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'لطفا تکرار رمز عبور را وارد کنید.';
            } elseif ($data['password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'رمزهای عبور یکسان نیستند.';
            }

            // بررسی اینکه آیا خطایی در اعتبارسنجی وجود دارد
            if (empty($data['first_name_err']) && empty($data['last_name_err']) && empty($data['username_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                // تمام اعتبارسنجی‌ها موفقیت آمیز بوده

                // هش کردن رمز عبور
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                // ثبت نام کاربر با استفاده از مدل
                if ($this->userModel->register($data)) {
                    // تنظیم پیام موفقیت آمیز بودن ثبت نام
                    flash('register_success', 'ثبت نام شما با موفقیت انجام شد. اکنون می‌توانید وارد شوید.');
                    // ریدایرکت به صفحه ورود
                    header('Location: ' . BASE_URL . 'auth/login');
                    exit();
                } else {
                    die('خطایی در هنگام ثبت نام رخ داد. لطفا بعدا تلاش کنید.');
                }
            } else {
                // اگر خطا وجود دارد، فرم را با خطاها و داده‌های قبلی نمایش بده
                $data['pageTitle'] = 'ثبت نام کاربر';
                $this->view('auth/register', $data);
            }

        } else {
            // اگر درخواست از نوع GET است (کاربر برای اولین بار صفحه را باز کرده)
            // مقداردهی اولیه داده‌ها برای فرم خالی
            $data = [
                'pageTitle' => 'ثبت نام کاربر',
                'first_name' => '',
                'last_name' => '',
                'username' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'first_name_err' => '',
                'last_name_err' => '',
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            // نمایش فرم ثبت نام
            $this->view('auth/register', $data);
        }
    }


    // متد برای نمایش فرم و پردازش ورود
    public function login() {
        // بررسی اینکه آیا درخواست از نوع POST است
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // پردازش فرم
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'email_username' => trim($_POST['email_username']), // می‌تواند ایمیل یا نام کاربری باشد
                'password' => trim($_POST['password']),
                'email_username_err' => '',
                'password_err' => '',
                'login_err' => '' // برای خطای کلی ورود
            ];

            // اعتبارسنجی ایمیل/نام کاربری
            if (empty($data['email_username'])) {
                $data['email_username_err'] = 'لطفا ایمیل یا نام کاربری خود را وارد کنید.';
            }

            // اعتبارسنجی رمز عبور
            if (empty($data['password'])) {
                $data['password_err'] = 'لطفا رمز عبور خود را وارد کنید.';
            }

            // بررسی اینکه آیا کاربر از طریق ایمیل یا نام کاربری وارد شده
            $loggedInUser = null;
            if (empty($data['email_username_err']) && empty($data['password_err'])) {
                if (filter_var($data['email_username'], FILTER_VALIDATE_EMAIL)) {
                    // کاربر با ایمیل وارد شده
                    $loggedInUser = $this->userModel->findUserByEmail($data['email_username']);
                } else {
                    // کاربر با نام کاربری وارد شده
                    $loggedInUser = $this->userModel->findUserByUsername($data['email_username']);
                }

                if ($loggedInUser) {
                    // کاربر پیدا شد، حالا رمز عبور را بررسی کن
                    if (password_verify($data['password'], $loggedInUser['password'])) {
                        // رمز عبور صحیح است
                        // ایجاد سشن برای کاربر
                        $this->createUserSession($loggedInUser);
                        // ریدایرکت به صفحه اصلی یا داشبورد
                        flash('login_success', 'شما با موفقیت وارد شدید.');
                        header('Location: ' . BASE_URL); // به صفحه اصلی
                        exit();
                    } else {
                        // رمز عبور اشتباه است
                        $data['login_err'] = 'نام کاربری/ایمیل یا رمز عبور اشتباه است.';
                        $this->view('auth/login', $data);
                    }
                } else {
                    // کاربر پیدا نشد
                    $data['login_err'] = 'کاربری با این مشخصات یافت نشد.';
                    $this->view('auth/login', $data);
                }
            } else {
                // اگر خطاهای اولیه وجود دارد، فرم را با خطاها نمایش بده
                 $data['pageTitle'] = 'ورود کاربر';
                $this->view('auth/login', $data);
            }

        } else {
            // اگر درخواست GET است، فرم ورود را نمایش بده
            $data = [
                'pageTitle' => 'ورود کاربر',
                'email_username' => '',
                'password' => '',
                'email_username_err' => '',
                'password_err' => '',
                'login_err' => ''
            ];
            $this->view('auth/login', $data);
        }
    }

    // متد برای ایجاد سشن کاربر
    private function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role']; // برای مدیریت دسترسی‌ها
        // می‌توانید اطلاعات دیگری مانند نام و نام خانوادگی را هم در سشن ذخیره کنید
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
    }

    // متد برای خروج کاربر
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_first_name']);
        unset($_SESSION['user_last_name']);
        session_destroy(); // نابود کردن کامل سشن
        // ریدایرکت به صفحه اصلی با پیام
        flash('logout_success', 'شما با موفقیت خارج شدید.', 'alert alert-info');
        header('Location: ' . BASE_URL . 'auth/login');
        exit();
    }

    // متد برای بررسی اینکه آیا کاربر لاگین کرده است (می‌تواند در کنترلر پایه هم باشد)
    public function isLoggedIn() {
        if (isset($_SESSION['user_id'])) {
            return true;
        } else {
            return false;
        }
    }
}
?>