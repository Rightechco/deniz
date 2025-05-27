<?php
// app/helpers/session_helper.php

// اطمینان از اینکه سشن قبل از استفاده از توابع فلش شروع شده است
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
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        // اگر پیام برای تنظیم ارسال شده و هنوز در سشن ذخیره نشده
        if (!empty($message) && empty($_SESSION[$name])) {
            // حذف پیام قبلی با همین نام اگر وجود داشته باشد
            if (!empty($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }
            // ذخیره پیام و کلاس آن در سشن
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        }
        // اگر پیام برای تنظیم ارسال نشده (یعنی فقط نام داده شده) و پیام در سشن موجود است
        elseif (empty($message) && !empty($_SESSION[$name])) {
            $class_display = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'alert';
            echo '<div class="' . $class_display . '" id="msg-flash-' . htmlspecialchars($name) . '">' . htmlspecialchars($_SESSION[$name]) . '</div>';
            // حذف پیام و کلاس آن از سشن پس از نمایش
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}
?>