<?php
// app/helpers/jalali_helper.php

// اطمینان از شروع سشن (اگر قبلاً شروع نشده باشد، اگرچه معمولاً در index.php انجام می‌شود)
// این خط اگر در session_helper.php هم هست، ممکن است باعث هشدار شود، اما معمولاً مشکلی نیست.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// بارگذاری کتابخانه jdf.php - مسیر را نسبت به این فایل تنظیم کنید
// یا اطمینان حاصل کنید که jdf.php در یک مسیر include_path قرار دارد یا قبلاً include شده.
if (!function_exists('jdate')) { // برای جلوگیری از include مجدد
    $jdf_path = dirname(__FILE__) . '/jdf.php';
    if (file_exists($jdf_path)) {
        require_once $jdf_path;
    } else {
        error_log("jdf.php not found at: " . $jdf_path . ". Jalali conversion functions might not work correctly.");
    }
}

/**
 * تبدیل تاریخ و زمان میلادی به فرمت شمسی خوانا با استفاده از jdf.php
 * @param string $gregorian_datetime تاریخ و زمان میلادی (مثلاً از پایگاه داده 'YYYY-MM-DD HH:MM:SS')
 * @param string $format فرمت خروجی مورد نظر (مشابه پارامتر فرمت تابع jdate())
 * @param bool $include_time آیا زمان هم نمایش داده شود یا خیر (در jdate معمولاً با فرمت کنترل می‌شود)
 * @return string تاریخ و زمان شمسی فرمت شده (یا رشته جایگزین اگر تبدیل ناموفق بود)
 */
function to_jalali_datetime($gregorian_datetime, $format = 'Y/m/d H:i:s', $include_time = true) {
    if (empty($gregorian_datetime) || $gregorian_datetime === '0000-00-00 00:00:00' || $gregorian_datetime === null) {
        return '<em>نامشخص</em>';
    }
    
    if (!function_exists('jdate')) {
        // Fallback to Gregorian if jdf.php is not loaded
        try {
            $date_obj = new DateTime($gregorian_datetime); // PHP's default timezone (Asia/Tehran) will be used
            if (!$include_time) {
                $format = 'Y-m-d'; // Use a standard Gregorian format for fallback
            } else {
                $format = 'Y-m-d H:i:s';
            }
            return $date_obj->format($format) . ' (میلادی)'; 
        } catch (Exception $e) { 
            error_log("Error in to_jalali_datetime (fallback DateTime): " . $e->getMessage() . " for datetime: " . $gregorian_datetime);
            return '<em>تاریخ نامعتبر (میلادی)</em>'; 
        }
    }

    try {
        $timestamp = strtotime($gregorian_datetime);
        if ($timestamp === false) {
            // تلاش برای پارس کردن با فرمت‌های دیگر اگر strtotime استاندارد شکست خورد
            $date_obj_fallback = new DateTime($gregorian_datetime);
            $timestamp = $date_obj_fallback->getTimestamp();
            if($timestamp === false) return '<em>تاریخ ورودی نامعتبر</em>';
        }

        if (!$include_time) {
            $format = 'Y/m/d';
        }
        // پارامتر چهارم jdate برای منطقه زمانی است، اگر خالی باشد از date_default_timezone_get() استفاده می‌کند
        // پارامتر پنجم برای زبان اعداد ('en' یا 'fa')
        return jdate($format, $timestamp, '', 'Asia/Tehran', 'fa');

    } catch (Exception $e) {
        error_log("Error in to_jalali_datetime (using jdate): " . $e->getMessage() . " for datetime: " . $gregorian_datetime);
        return '<em>خطا در تبدیل به شمسی</em>';
    }
}

/**
 * یک تابع ساده‌تر فقط برای نمایش تاریخ شمسی (بدون زمان)
 */
function to_jalali_date($gregorian_date) {
    return to_jalali_datetime($gregorian_date, 'Y/m/d', false);
}

/**
 * تبدیل تاریخ شمسی (مثلاً از datepicker با فرمت YYYY/MM/DD) به میلادی (YYYY-MM-DD)
 * برای استفاده در کوئری‌های پایگاه داده. نیاز به jdf.php دارد.
 * @param string $jalali_date_string تاریخ شمسی (مثلاً '1403/03/08')
 * @return string|null تاریخ میلادی 'YYYY-MM-DD' یا null اگر ورودی نامعتبر است
 */
function to_gregorian_date($jalali_date_string) {
    if (empty($jalali_date_string)) {
        return null;
    }
    if (!function_exists('jdate_to_gregorian') && !function_exists('jmktime')) {
        error_log("jdf.php functions (jdate_to_gregorian or jmktime) not available for Jalali to Gregorian conversion.");
        // Fallback: try to parse as if it might be Gregorian already or simple format
        $timestamp = strtotime(str_replace('/', '-', $jalali_date_string));
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
    try {
        $parts = preg_split('/[-\/]/', $jalali_date_string);
        if (count($parts) === 3) {
            $jy = (int)$parts[0];
            $jm = (int)$parts[1];
            $jd = (int)$parts[2];
            
            if (function_exists('jdate_to_gregorian')) { // ارجحیت با jdate_to_gregorian
                 $gregorian_array = jdate_to_gregorian($jy, $jm, $jd);
                 if($gregorian_array && count($gregorian_array) === 3) {
                    return sprintf('%04d-%02d-%02d', $gregorian_array[0], $gregorian_array[1], $gregorian_array[2]);
                 }
            } elseif (function_exists('jmktime')) { // استفاده از jmktime به عنوان جایگزین
                $timestamp = jmktime(0, 0, 0, $jm, $jd, $jy);
                return date('Y-m-d', $timestamp);
            }
        }
        error_log("to_gregorian_date: Could not parse Jalali date format: " . $jalali_date_string . " or jdf functions failed.");
        return null;
    } catch (Exception $e) {
        error_log("Error in to_gregorian_date: " . $e->getMessage() . " for date: " . $jalali_date_string);
        return null;
    }
}
?>
