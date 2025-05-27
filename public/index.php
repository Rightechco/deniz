<?php
// public/index.php

// این کدها باید اولین چیز در فایل باشند برای نمایش و ثبت خطا
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/my_app_errors.log');

// تعریف ثابت FCPATH برای مسیر مطلق به پوشه public
if (!defined('FCPATH')) {
    define('FCPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}
// داخل public/index.php
// ...
// === بارگذاری Autoloader کامپوزر ===
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    die("خطای حیاتی: فایل autoload.php کامپوزر در مسیر " . __DIR__ . "/../../vendor/autoload.php یافت نشد. لطفاً دستور 'composer install' را در ریشه پروژه اجرا کنید.");
}
// === پایان بارگذاری Autoloader کامپوزر ===
// ...

// شروع Session برای مدیریت اطلاعات کاربر
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// بارگذاری فایل‌های پیکربندی، کمکی و هسته اصلی برنامه
require_once '../config/database.php';   // تنظیمات پایگاه داده و BASE_URL
require_once '../app/helpers/session_helper.php'; 
require_once '../app/helpers/jalali_helper.php'; // اگر تابع شمسی‌ساز را اینجا require می‌کنید

// فایل‌های هسته مربوط به معماری MVC مانند ما
require_once '../app/core/Database.php';   
require_once '../app/core/Controller.php'; 
require_once '../app/core/Router.php';     

$router = new Router();

// بارگذاری فایل‌های پیکربندی، کمکی و هسته اصلی برنامه
require_once '../config/database.php';   // تنظیمات پایگاه داده و BASE_URL
require_once '../app/helpers/session_helper.php'; // توابع کمکی برای سشن و پیام‌های لحظه‌ای

// فایل‌های هسته مربوط به معماری MVC مانند ما
require_once '../app/core/Database.php';   // کلاس کار با پایگاه داده
require_once '../app/core/Controller.php'; // کلاس کنترلر پایه
require_once '../app/core/Router.php';     // کلاس روتینگ
// public/index.php
// ...
require_once '../app/helpers/session_helper.php';
require_once '../app/helpers/jalali_helper.php'; // <<-- اضافه کردن این خط
// ...


    // === رهگیری کد همکاری در فروش ===
    if (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $affiliate_code = filter_var(trim($_GET['ref']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // کد همکاری را در سشن ذخیره کن تا در طول بازدید کاربر در دسترس باشد
        // می‌توانید یک تاریخ انقضا هم برای آن در نظر بگیرید (مثلاً با کوکی)
        $_SESSION['referred_by_affiliate_code'] = $affiliate_code;
        
        // (اختیاری) ثبت کلیک در جدول affiliate_clicks
        // این کار نیاز به نمونه‌سازی مدل User یا یک مدل Affiliate دارد
        // و بهتر است در یک کنترلر یا سرویس انجام شود تا index.php شلوغ نشود.
        // فعلاً این بخش را برای سادگی اینجا کامنت می‌کنیم.
        /*
        if (file_exists(PROJECT_ROOT . '/app/models/User.php') && file_exists(PROJECT_ROOT . '/app/core/Database.php')) {
            // این require ها ممکن است تکراری باشند اگر در ادامه هم هستند، اما برای این مثال لازم است
            require_once PROJECT_ROOT . '/app/core/Database.php';
            require_once PROJECT_ROOT . '/app/models/User.php';
            if (class_exists('User')) {
                $userModelForAffiliate = new User();
                $affiliateUser = $userModelForAffiliate->findUserByAffiliateCode($affiliate_code);
                if ($affiliateUser) {
                    // فرض وجود متد logAffiliateClick در مدل User یا Affiliate
                    // $userModelForAffiliate->logAffiliateClick($affiliateUser['id'], $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);
                }
            }
        }
        */
    }
    // === پایان رهگیری کد همکاری ===
    $router = new Router();
    ?>
    