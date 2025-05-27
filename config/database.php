<?php
// config/database.php

// تنظیم منطقه زمانی پیش‌فرض برای تمام توابع تاریخ و زمان در PHP
date_default_timezone_set('Asia/Tehran');

// اطلاعات اتصال به پایگاه داده شما در سرور barenj.ir
define('DB_HOST', 'localhost'); // معمولاً localhost است، مگر اینکه هاستینگ شما آدرس دیگری داده باشد
define('DB_USER', 'winbo_todbb');      // نام کاربری پایگاه داده‌ای که در هاست خود ساخته‌اید
define('DB_PASS', 'D.L+v@ztbKSK');          // رمز عبور پایگاه داده شما
define('DB_NAME', 'winbo_todbb'); // نام پایگاه داده‌ای که در هاست خود ساخته‌اید
// ... (سایر define ها) ...
define('PLATFORM_COMMISSION_RATE', 0.10); // مثال: کمیسیون 10 درصدی برای فروشگاه

// آدرس پایه (BASE_URL) سایت شما
// این آدرس باید به پوشه public شما اشاره کند.
// با توجه به مسیر /home/winbo/public_html/public/index.php :
// اگر دامنه barenj.ir مستقیماً به پوشه /home/winbo/public_html/public/ شما متصل است (یعنی محتویات پوشه public شما در آدرس barenj.ir نمایش داده می‌شود)،
// آنگاه BASE_URL باید 'http://barenj.ir/' باشد.
// اما اگر دامنه barenj.ir به پوشه /home/winbo/public_html/ متصل است (و پوشه public یک زیرپوشه است که در URL دیده می‌شود)،
// آنگاه BASE_URL باید 'http://barenj.ir/public/' باشد.
// لطفاً این مقدار را با توجه به تنظیمات هاست و دامنه خود به دقت تنظیم کنید.
// مثال رایج اگر public_html ریشه دامنه است و پروژه در آن است:
define('BASE_URL', 'https://barenj.ir/public/'); // <<< این مقدار را بررسی و در صورت نیاز اصلاح کنید!

?>