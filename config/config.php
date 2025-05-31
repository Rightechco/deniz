    <?php
    // app/config/config.php
date_default_timezone_set('Asia/Tehran');

    // Database Parameters
    define('DB_HOST', 'localhost'); // هاست پایگاه داده شما (معمولاً localhost)
    define('DB_USER', 'winbo_todbb'); // نام کاربری پایگاه داده شما
    define('DB_PASS', 'D.L+v@ztbKSK'); // رمز عبور پایگاه داده شما
    define('DB_NAME', 'winbo_todbb');    // نام پایگاه داده شما

    // App Root - مسیر به پوشه app شما
    // dirname(dirname(__FILE__)) به پوشه app اشاره می‌کند اگر config.php داخل app/config باشد
    define('APPROOT', dirname(dirname(__FILE__))); 

    // URL Root - آدرس اصلی وب‌سایت شما (حتماً با / در انتها)
    // مثال: http://localhost/yourproject/ یا https://yourdomain.com/
  define('BASE_URL', 'https://barenj.ir/public/'); // <<< این مقدار را بررسی و در خود جایگزین کنید

    // Site Name - نام وب‌سایت شما
    define('SITE_NAME', 'فروشگاه بارنج'); // نام سایت خود را وارد کنید
    define('SITE_LOGO_TEXT', 'بارنج'); // متن یا نامی که برای لوگو استفاده می‌شود (اختیاری)


    // مقادیر پیش‌فرض برای کنترلر و متد (اگر در Router.php استفاده می‌شود)
    define('DEFAULT_CONTROLLER', 'PagesController'); 
    define('DEFAULT_METHOD', 'index');          

    // Timezone - منطقه زمانی سرور شما
    date_default_timezone_set('Asia/Tehran');

    // مدیریت نمایش خطاها و لاگ کردن آن‌ها
    // در محیط توسعه (development) بهتر است نمایش خطاها فعال باشد.
    // در محیط عملیاتی (production) نمایش خطاها باید غیرفعال و لاگ کردن خطاها فعال باشد.
    define('ENVIRONMENT', 'development'); // یا 'production'

    if (ENVIRONMENT == 'development') {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0); // یا E_ALL & ~E_DEPRECATED & ~E_STRICT برای لاگ کردن همه چیز جز deprecated ها
    }

    // مسیر فایل لاگ خطاها (مطمئن شوید پوشه logs در app وجود دارد و قابل نوشتن است)
    define('ERROR_LOG_FILE', APPROOT . '/logs/php_errors.log');
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
    
    // سایر ثابت‌های مورد نیاز برنامه (مثال)
    // define('MIN_VENDOR_PAYOUT_AMOUNT', 50000);
    // define('MIN_AFFILIATE_PAYOUT_AMOUNT', 10000);
    // define('PLATFORM_COMMISSION_RATE', 0.10); // مثال: ۱۰ درصد

    // اطمینان از وجود پوشه logs
    if (!file_exists(dirname(ERROR_LOG_FILE))) {
        mkdir(dirname(ERROR_LOG_FILE), 0755, true);
    }

    ?>
    