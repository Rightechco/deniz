<?php
// app/helpers/session_helper.php

// اطمینان از اینکه سشن قبل از هر عملیاتی شروع شده است
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * تابع برای تنظیم و نمایش پیام‌های لحظه‌ای (Flash Messages)
 * مثال برای تنظیم پیام: flash('register_success', 'ثبت نام شما با موفقیت انجام شد.');
 * مثال برای نمایش پیام در ویو: <?php echo flash('register_success'); ?>
 * @param string $name نام پیام (کلید سشن)
 * @param string $message متن پیام (اگر خالی باشد، سعی در نمایش پیام می‌کند)
 * @param string $class کلاس CSS برای نمایش پیام (مثلاً 'alert alert-success' یا 'alert alert-danger')
 * @param string|null $custom_id (اختیاری) برای جلوگیری از نمایش مجدد پیام در رفرش، یک شناسه یکتا برای پیام تنظیم کنید
 */
function flash($name = '', $message = '', $class = 'alert alert-success', $custom_id = null) {
    if (!empty($name)) {
        $session_key_message = $name;
        $session_key_class = $name . '_class';
        $session_key_displayed = $custom_id ? $name . '_displayed_' . $custom_id : null;

        // اگر پیام برای تنظیم ارسال شده
        if (!empty($message)) {
            // حذف پیام قبلی با همین نام اگر وجود داشته باشد
            if (!empty($_SESSION[$session_key_message])) {
                unset($_SESSION[$session_key_message]);
            }
            if (!empty($_SESSION[$session_key_class])) {
                unset($_SESSION[$session_key_class]);
            }
            if ($session_key_displayed && !empty($_SESSION[$session_key_displayed])) {
                unset($_SESSION[$session_key_displayed]);
            }

            // ذخیره پیام و کلاس آن در سشن
            $_SESSION[$session_key_message] = $message;
            $_SESSION[$session_key_class] = $class;
            if ($session_key_displayed) {
                 $_SESSION[$session_key_displayed] = false; // هنوز نمایش داده نشده
            }
        }
        // اگر پیام برای نمایش درخواست شده (فقط نام داده شده) و در سشن موجود است
        // و اگر custom_id داده شده، هنوز نمایش داده نشده باشد
        elseif (empty($message) && !empty($_SESSION[$session_key_message])) {
            if ($session_key_displayed && isset($_SESSION[$session_key_displayed]) && $_SESSION[$session_key_displayed] === true) {
                // این پیام قبلا با این custom_id نمایش داده شده، پس دوباره نمایش نده
                // و از سشن هم پاکش کن چون دیگر نیازی نیست
                unset($_SESSION[$session_key_message]);
                unset($_SESSION[$session_key_class]);
                unset($_SESSION[$session_key_displayed]);
                return;
            }

            $class_display = !empty($_SESSION[$session_key_class]) ? $_SESSION[$session_key_class] : 'alert';
            echo '<div class="' . $class_display . '" id="msg-flash-' . htmlspecialchars($name) . '">' . htmlspecialchars($_SESSION[$session_key_message]) . '</div>';
            
            // پس از نمایش، پیام را از سشن حذف کن، مگر اینکه قرار است با custom_id مدیریت شود
            if (!$session_key_displayed) {
                unset($_SESSION[$session_key_message]);
                unset($_SESSION[$session_key_class]);
            } elseif ($session_key_displayed) {
                $_SESSION[$session_key_displayed] = true; // علامت‌گذاری به عنوان نمایش داده شده
            }
        }
    }
}

/**
 * بررسی می‌کند آیا کاربر لاگین کرده است یا خیر.
 * @return bool true اگر کاربر لاگین کرده، false در غیر این صورت.
 */
function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * دریافت یک مقدار از سشن.
 * @param string $key کلید مورد نظر در سشن.
 * @return mixed|null مقدار ذخیره شده یا null اگر کلید وجود نداشته باشد.
 */
function getSession($key) {
    if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
    }
    return null;
}

/**
 * تنظیم یک مقدار در سشن.
 * @param string $key کلید مورد نظر.
 * @param mixed $value مقداری که باید ذخیره شود.
 */
function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * حذف یک مقدار از سشن.
 * @param string $key کلید مورد نظر برای حذف.
 */
function removeSession($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * ایجاد سشن برای کاربر پس از لاگین موفق.
 * @param array $user آرایه‌ای شامل اطلاعات کاربر (حداقل باید 'id', 'username', 'email', 'role' را داشته باشد).
 */
function createUserSession($user) {
    if (is_array($user) && isset($user['id'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'] ?? null;
        $_SESSION['user_email'] = $user['email'] ?? null;
        $_SESSION['user_role'] = $user['role'] ?? 'customer'; // نقش پیش‌فرض اگر مشخص نشده
        $_SESSION['user_first_name'] = $user['first_name'] ?? null;
        $_SESSION['user_last_name'] = $user['last_name'] ?? null;
        // می‌توانید هر اطلاعات دیگری که نیاز دارید را اینجا در سشن ذخیره کنید
        // مانند affiliate_code اگر کاربر همکار است
        if (isset($user['affiliate_code'])) {
            $_SESSION['user_affiliate_code'] = $user['affiliate_code'];
        }
    } else {
        error_log("session_helper::createUserSession - Invalid user data provided.");
    }
}

/**
 * خروج کاربر و پاک کردن سشن‌های مربوط به او.
 */
function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);
    unset($_SESSION['user_first_name']);
    unset($_SESSION['user_last_name']);
    unset($_SESSION['user_affiliate_code']); // اگر از این استفاده می‌کنید
    // می‌توانید سایر کلیدهای سشن مربوط به کاربر را هم اینجا unset کنید
    // session_destroy(); // این کل سشن را از بین می‌برد، اگر نیاز دارید استفاده کنید
    // اما معمولاً unset کردن کلیدهای خاص بهتر است تا سشن‌های دیگر (مثل سبد خرید) حفظ شوند
    // مگر اینکه بخواهید سبد خرید هم با خروج کاربر پاک شود.
}

?>
