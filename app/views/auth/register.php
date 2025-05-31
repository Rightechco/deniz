<?php 
if (!defined('APPROOT')) { define('APPROOT', dirname(dirname(dirname(__FILE__)))); }
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
                <?php echo htmlspecialchars($data['pageTitle'] ?? 'ایجاد حساب کاربری جدید'); ?>
            </h2>
        </div>

        <?php flash('register_fail'); // Show general registration errors ?>

        <form action="<?php echo BASE_URL; ?>auth/register" method="post" class="space-y-4 needs-validation" novalidate>
            <div>
                <label for="account_type" class="block text-sm font-medium text-gray-700 mb-1">نوع حساب: <sup class="text-red-500">*</sup></label>
                <select name="account_type" id="account_type" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['account_type_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" required>
                    <option value="customer" <?php echo (isset($data['account_type']) && $data['account_type'] == 'customer') ? 'selected' : ''; ?>>مشتری</option>
                    <option value="vendor" <?php echo (isset($data['account_type']) && $data['account_type'] == 'vendor') ? 'selected' : ''; ?>>فروشنده</option>
                    <option value="affiliate" <?php echo (isset($data['account_type']) && $data['account_type'] == 'affiliate') ? 'selected' : ''; ?>>همکار فروش</option>
                </select>
                <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['account_type_err'] ?? ''; ?></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">نام:</label>
                    <input type="text" name="first_name" id="first_name" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['first_name_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>">
                    <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['first_name_err'] ?? ''; ?></span>
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">نام خانوادگی:</label>
                    <input type="text" name="last_name" id="last_name" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['last_name_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['last_name'] ?? ''); ?>">
                    <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['last_name_err'] ?? ''; ?></span>
                </div>
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">نام کاربری: <sup class="text-red-500">*</sup></label>
                <input type="text" name="username" id="username" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['username_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" required>
                <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['username_err'] ?? ''; ?></span>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">ایمیل: <sup class="text-red-500">*</sup></label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['email_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" required>
                <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['email_err'] ?? ''; ?></span>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">شماره تلفن همراه:</label>
                <input type="tel" name="phone" id="phone" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['phone_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>">
                <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['phone_err'] ?? ''; ?></span>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">رمز عبور: <sup class="text-red-500">*</sup></label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['password_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" required>
                <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['password_err'] ?? ''; ?></span>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">تکرار رمز عبور: <sup class="text-red-500">*</sup></label>
                <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full px-3 py-2.5 border <?php echo !empty($data['confirm_password_err']) ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'; ?> rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" required>
                <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['confirm_password_err'] ?? ''; ?></span>
            </div>
            
            <div id="vendor_specific_fields" style="display:none;" class="space-y-4 mt-4 border-t pt-4">
                 <p class="text-sm text-gray-600">اطلاعات تکمیلی برای حساب فروشندگی (اختیاری):</p>
                <div>
                    <label for="shop_name" class="block text-sm font-medium text-gray-700 mb-1">نام فروشگاه/کسب و کار:</label>
                    <input type="text" name="shop_name" id="shop_name" class="mt-1 block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['shop_name'] ?? ''); ?>">
                    <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['shop_name_err'] ?? ''; ?></span>
                </div>
                 <div>
                    <label for="vendor_payment_details" class="block text-sm font-medium text-gray-700 mb-1">اطلاعات حساب بانکی (شبا):</label>
                    <input type="text" name="vendor_payment_details" id="vendor_payment_details" class="mt-1 block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['vendor_payment_details'] ?? ''); ?>" placeholder="IR123456789012345678901234">
                    <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['vendor_payment_details_err'] ?? ''; ?></span>
                </div>
            </div>
             <div id="affiliate_specific_fields" style="display:none;" class="space-y-4 mt-4 border-t pt-4">
                 <p class="text-sm text-gray-600">اطلاعات تکمیلی برای حساب همکاری (اختیاری):</p>
                 <div>
                    <label for="affiliate_payment_details" class="block text-sm font-medium text-gray-700 mb-1">اطلاعات حساب بانکی (شبا):</label>
                    <input type="text" name="affiliate_payment_details" id="affiliate_payment_details" class="mt-1 block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo htmlspecialchars($data['affiliate_payment_details'] ?? ''); ?>" placeholder="IR123456789012345678901234">
                    <span class="text-red-500 text-xs mt-1 block px-1"><?php echo $data['affiliate_payment_details_err'] ?? ''; ?></span>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark transition duration-150">
                    ایجاد حساب کاربری
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            قبلاً حساب کاربری ساخته‌اید؟
            <a href="<?php echo BASE_URL; ?>auth/login" class="font-medium text-primary hover:text-primary-dark">وارد شوید</a>
        </p>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountTypeSelect = document.getElementById('account_type');
        const vendorFields = document.getElementById('vendor_specific_fields');
        const affiliateFields = document.getElementById('affiliate_specific_fields');
        
        function toggleSpecificFields() {
            if (!accountTypeSelect || !vendorFields || !affiliateFields) return;
            const selectedType = accountTypeSelect.value;
            vendorFields.style.display = (selectedType === 'vendor') ? 'block' : 'none';
            affiliateFields.style.display = (selectedType === 'affiliate') ? 'block' : 'none';
        }

        if (accountTypeSelect) {
            accountTypeSelect.addEventListener('change', toggleSpecificFields);
            toggleSpecificFields(); 
        }
    });
</script>

<?php 
require_once(__DIR__ . '/../layouts/footer.php');
?>
