<?php 
// app/views/auth/login.php
// این ویو از $data['pageTitle'], $data['username_or_email'], $data['username_or_email_err'], 
// $data['password_err'] و توابع flash() استفاده می‌کند.

// اطمینان از اینکه APPROOT تعریف شده است
if (!defined('APPROOT')) {
    // اگر config.php به درستی include شده باشد، این نباید اتفاق بیفتد
    // اما به عنوان یک fallback ساده:
    define('APPROOT', dirname(dirname(dirname(__FILE__)))); 
}
// شما ممکن است یک فایل هدر مخصوص صفحات احراز هویت داشته باشید یا از هدر اصلی استفاده کنید.
// require_once APPROOT . '/views/layouts/header_auth.php'; 
// یا اگر هدر عمومی دارید و می‌خواهید تمام صفحه باشد، ممکن است نیاز به تنظیمات خاصی در آن هدر برای این صفحات داشته باشید.
// برای سادگی، فرض می‌کنیم header.php عمومی (frontend_header_v4) استفاده می‌شود و با Tailwind تمام صفحه را می‌پوشاند.
require_once(__DIR__ . '/../layouts/header.php');
?>

<div class="min-h-screen flex flex-col items-center justify-center bg-neutral-lightest py-12 px-4 sm:px-6 lg:px-8 font-vazir">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-xl shadow-2xl">
        <div>
            <a href="<?php echo BASE_URL; ?>" class="inline-block mb-6 mx-auto w-full text-center">
                 <?php if (defined('SITE_LOGO_URL') && SITE_LOGO_URL && defined('FCPATH') && file_exists(FCPATH . ltrim(SITE_LOGO_URL, '/'))): ?>
                    <img src="<?php echo BASE_URL . SITE_LOGO_URL; ?>" alt="<?php echo SITE_NAME; ?>" class="h-12 mx-auto">
                <?php elseif (defined('SITE_LOGO_TEXT') && SITE_LOGO_TEXT): ?>
                    <h1 class="text-4xl font-bold text-primary"><?php echo SITE_LOGO_TEXT; ?></h1>
                <?php else: ?>
                    <h1 class="text-4xl font-bold text-primary"><?php echo SITE_NAME; ?></h1>
                <?php endif; ?>
            </a>
            <h2 class="mt-4 text-center text-2xl sm:text-3xl font-bold text-neutral-darkest">
                <?php echo htmlspecialchars($data['pageTitle'] ?? 'ورود به حساب کاربری'); ?>
            </h2>
            <p class="mt-2 text-center text-sm text-neutral-medium">
                یا اگر هنوز حساب کاربری ندارید، 
                <a href="<?php echo BASE_URL; ?>auth/register" class="font-medium text-primary hover:text-primary-dark transition-colors">
                    ثبت نام کنید
                </a>
            </p>
        </div>

        <?php flash('login_fail'); ?>
        <?php flash('register_success'); ?>
        <?php flash('auth_required'); ?>
        <?php flash('logout_success'); ?>
        <?php flash('checkout_login_required'); ?>


        <form class="mt-8 space-y-6 needs-validation" action="<?php echo BASE_URL; ?>auth/login" method="post" novalidate>
            
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username_or_email" class="block text-sm font-medium text-gray-700 mb-1">نام کاربری یا ایمیل <sup class="text-red-500">*</sup></label>
                    <input id="username_or_email" name="username_or_email" type="text" autocomplete="username email" required 
                           class="appearance-none relative block w-full px-3 py-3 border <?php echo !empty($data['username_or_email_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> placeholder-gray-400 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm transition-shadow" 
                           placeholder="نام کاربری یا آدرس ایمیل" value="<?php echo htmlspecialchars($data['username_or_email'] ?? ''); ?>">
                    <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['username_or_email_err'] ?? ''; ?></span>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">رمز عبور <sup class="text-red-500">*</sup></label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none relative block w-full px-3 py-3 border <?php echo !empty($data['password_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> placeholder-gray-400 text-gray-900 rounded-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm transition-shadow" 
                           placeholder="رمز عبور">
                    <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['password_err'] ?? ''; ?></span>
                </div>
            </div>

            <div class="flex items-center justify-between mt-5 text-sm">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-primary focus:ring-primary-dark border-gray-300 rounded cursor-pointer">
                    <label for="remember_me" class="mr-2 block text-gray-900 cursor-pointer">
                        مرا به خاطر بسپار
                    </label>
                </div>

                <div>
                    <a href="#" class="font-medium text-primary hover:text-primary-dark hover:underline">
                        رمز عبور خود را فراموش کرده‌اید؟
                    </a>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark transition duration-150 ease-in-out shadow-md hover:shadow-lg">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-lock h-5 w-5 text-blue-300 group-hover:text-blue-200 transition-colors"></i>
                    </span>
                    ورود به حساب کاربری
                </button>
            </div>
        </form>
         <p class="mt-8 text-center text-xs text-gray-500">
            با ورود به حساب، شما <a href="<?php echo BASE_URL; ?>pages/terms" class="text-gray-600 hover:text-primary underline">شرایط خدمات</a> و <a href="<?php echo BASE_URL; ?>pages/privacy" class="text-gray-600 hover:text-primary underline">سیاست حفظ حریم خصوصی</a> ما را می‌پذیرید.
        </p>
    </div>
</div>
<script>
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
          }, false)
        })
    })()
</script>
<?php require_once(__DIR__ . '/../layouts/footer.php'); ?>
?>
