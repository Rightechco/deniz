<?php 
// app/views/checkout/index.php
// This view expects $data['pageTitle'], $data['cart_items'], $data['total_price'], 
// and form fields for user info from CheckoutController::index()

// Ensure APPROOT is defined
if (!defined('APPROOT')) {
    define('APPROOT', dirname(dirname(dirname(__FILE__)))); 
}
require_once(__DIR__ . '/../layouts/header.php');
?>

<div class="container mx-auto px-4 py-8 lg:py-12 font-vazir">
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500">
        <ol class="list-none p-0 inline-flex space-x-2 space-x-reverse">
            <li class="flex items-center">
                <a href="<?php echo BASE_URL; ?>" class="hover:text-primary transition-colors">صفحه اصلی</a>
                <i class="fas fa-angle-left mx-2 text-gray-400"></i>
            </li>
            <li class="flex items-center">
                <a href="<?php echo BASE_URL; ?>cart" class="hover:text-primary transition-colors">سبد خرید</a>
                <i class="fas fa-angle-left mx-2 text-gray-400"></i>
            </li>
            <li class="text-neutral-dark font-medium" aria-current="page">تکمیل خرید و پرداخت</li>
        </ol>
    </nav>

    <h1 class="text-3xl md:text-4xl font-bold text-neutral-darkest mb-8 text-center">
       <i class="fas fa-credit-card text-primary mr-3"></i> تکمیل اطلاعات و پرداخت
    </h1>

    <?php flash('checkout_error'); ?>
    <?php flash('checkout_message'); ?>

    <?php if (isset($data['cart_items']) && !empty($data['cart_items']) && isset($data['total_price'])): ?>
        <form action="<?php echo BASE_URL; ?>checkout/placeOrder" method="post" id="checkoutForm" class="needs-validation" novalidate>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
                <div class="lg:col-span-2 bg-white shadow-xl rounded-lg p-6 md:p-8">
                    <h2 class="text-2xl font-semibold text-neutral-dark mb-6 border-b pb-4">اطلاعات ارسال و تماس</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">نام: <sup class="text-red-500">*</sup></label>
                            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($data['first_name'] ?? ($_SESSION['user_first_name'] ?? '')); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm <?php echo !empty($data['first_name_err']) ? 'border-red-500' : ''; ?>">
                            <span class="text-red-500 text-xs mt-1"><?php echo $data['first_name_err'] ?? ''; ?></span>
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">نام خانوادگی: <sup class="text-red-500">*</sup></label>
                            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($data['last_name'] ?? ($_SESSION['user_last_name'] ?? '')); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm <?php echo !empty($data['last_name_err']) ? 'border-red-500' : ''; ?>">
                            <span class="text-red-500 text-xs mt-1"><?php echo $data['last_name_err'] ?? ''; ?></span>
                        </div>
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">آدرس ایمیل: <sup class="text-red-500">*</sup></label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($data['email'] ?? ($_SESSION['user_email'] ?? '')); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm <?php echo !empty($data['email_err']) ? 'border-red-500' : ''; ?>">
                            <span class="text-red-500 text-xs mt-1"><?php echo $data['email_err'] ?? ''; ?></span>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">شماره تلفن همراه: <sup class="text-red-500">*</sup></label>
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($data['phone'] ?? ($_SESSION['user_phone'] ?? '')); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm <?php echo !empty($data['phone_err']) ? 'border-red-500' : ''; ?>" placeholder="مثال: 09123456789">
                            <span class="text-red-500 text-xs mt-1"><?php echo $data['phone_err'] ?? ''; ?></span>
                        </div>
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">آدرس دقیق: <sup class="text-red-500">*</sup></label>
                            <textarea name="address" id="address" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm <?php echo !empty($data['address_err']) ? 'border-red-500' : ''; ?>"><?php echo htmlspecialchars($data['address'] ?? ''); ?></textarea>
                            <span class="text-red-500 text-xs mt-1"><?php echo $data['address_err'] ?? ''; ?></span>
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">شهر: <sup class="text-red-500">*</sup></label>
                            <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($data['city'] ?? ''); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm <?php echo !empty($data['city_err']) ? 'border-red-500' : ''; ?>">
                            <span class="text-red-500 text-xs mt-1"><?php echo $data['city_err'] ?? ''; ?></span>
                        </div>
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">کد پستی: <sup class="text-red-500">*</sup></label>
                            <input type="text" name="postal_code" id="postal_code" value="<?php echo htmlspecialchars($data['postal_code'] ?? ''); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm <?php echo !empty($data['postal_code_err']) ? 'border-red-500' : ''; ?>" placeholder="بدون خط تیره">
                            <span class="text-red-500 text-xs mt-1"><?php echo $data['postal_code_err'] ?? ''; ?></span>
                        </div>
                         <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">توضیحات سفارش (اختیاری):</label>
                            <textarea name="notes" id="notes" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"><?php echo htmlspecialchars($data['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white shadow-xl rounded-lg p-6 md:p-8 sticky top-24">
                        <h2 class="text-2xl font-semibold text-neutral-dark mb-6 border-b pb-4">خلاصه سفارش</h2>
                        <div class="space-y-3 mb-6 text-sm max-h-60 overflow-y-auto pr-2">
                            <?php $calculated_total = 0; ?>
                            <?php foreach ($data['cart_items'] as $item_id => $item): ?>
                                <?php 
                                    if (!is_array($item) || !isset($item['quantity']) || !isset($item['price'])) continue;
                                    $item_total = (float)$item['price'] * (int)$item['quantity'];
                                    $calculated_total += $item_total;
                                ?>
                                <div class="flex justify-between items-start text-gray-600 py-2 border-b border-gray-100 last:border-b-0">
                                    <div class="flex-grow">
                                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-xs text-gray-500">تعداد: <?php echo (int)$item['quantity']; ?> &times; <?php echo number_format((float)$item['price']); ?> ت</p>
                                        <?php if (isset($item['variation_name']) && !empty($item['variation_name'])): ?>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['variation_name']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-mono text-gray-800 whitespace-nowrap pl-2"><?php echo number_format($item_total); ?> ت</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr class="my-4">
                        <div class="flex justify-between items-center text-gray-700 mb-2">
                            <span>جمع جزء:</span>
                            <span class="font-mono"><?php echo number_format($calculated_total); ?> تومان</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-700 mb-2">
                            <span>هزینه ارسال:</span>
                            <span class="font-mono"><?php echo isset($data['shipping_cost']) ? number_format((float)$data['shipping_cost']) : 'رایگان'; ?></span>
                        </div>
                        <?php 
                            // Assume $data['total_price'] from controller already includes shipping if applicable
                            $grand_total = isset($data['total_price']) ? (float)$data['total_price'] : $calculated_total + (isset($data['shipping_cost']) ? (float)$data['shipping_cost'] : 0);
                        ?>
                        <hr class="my-4 border-dashed">
                        <div class="flex justify-between items-center text-xl font-bold text-neutral-darkest mb-6">
                            <span>مبلغ قابل پرداخت:</span>
                            <span class="font-mono text-primary"><?php echo number_format($grand_total); ?> تومان</span>
                        </div>

                        <h3 class="text-lg font-semibold text-neutral-dark mb-3">روش پرداخت</h3>
                        <div class="space-y-3">
                            <label for="payment_cod" class="flex items-center p-3 border border-gray-200 rounded-md hover:border-primary transition-colors has-[:checked]:border-primary has-[:checked]:ring-1 has-[:checked]:ring-primary cursor-pointer">
                                <input id="payment_cod" name="payment_method" type="radio" value="cod" class="h-4 w-4 text-primary border-gray-300 focus:ring-primary focus:ring-offset-0" checked>
                                <span class="mr-3 block text-sm font-medium text-gray-700">
                                    پرداخت در محل
                                    <small class="block text-gray-500">پرداخت هزینه هنگام تحویل کالا (فقط شهرهای منتخب)</small>
                                </span>
                            </label>
                            <label for="payment_online" class="flex items-center p-3 border border-gray-200 rounded-md hover:border-primary transition-colors opacity-60 cursor-not-allowed has-[:checked]:border-primary has-[:checked]:ring-1 has-[:checked]:ring-primary">
                                <input id="payment_online" name="payment_method" type="radio" value="online" class="h-4 w-4 text-primary border-gray-300 focus:ring-primary focus:ring-offset-0" disabled>
                                <span class="mr-3 block text-sm font-medium text-gray-500">
                                    پرداخت آنلاین (درگاه بانکی)
                                    <small class="block text-gray-400">(به زودی فعال خواهد شد)</small>
                                </span>
                            </label>
                        </div>
                        <span class="text-red-500 text-xs mt-1 block"><?php echo $data['payment_method_err'] ?? ''; ?></span>

                        <div class="mt-8">
                            <button type="submit" 
                                    class="w-full bg-accent hover:bg-accent-dark text-white font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-lg flex items-center justify-center">
                                <i class="fas fa-lock mr-2"></i> ثبت نهایی سفارش و پرداخت
                            </button>
                        </div>
                         <p class="text-xs text-gray-500 mt-4 text-center">
                            با ثبت سفارش، شما <a href="<?php echo BASE_URL; ?>pages/terms" class="text-primary hover:underline">شرایط و قوانین</a> و <a href="<?php echo BASE_URL; ?>pages/privacy" class="text-primary hover:underline">سیاست حفظ حریم خصوصی</a> ما را می‌پذیرید.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="text-center py-12 bg-white shadow-xl rounded-lg">
             <i class="fas fa-info-circle fa-4x text-blue-400 mb-6"></i>
            <p class="text-2xl text-gray-700 font-semibold mb-3">سبد خرید شما برای تکمیل سفارش خالی است.</p>
            <p class="text-gray-500 mb-6">لطفاً ابتدا محصولاتی را به سبد خرید خود اضافه کنید.</p>
            <a href="<?php echo BASE_URL; ?>products" 
               class="bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-8 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-md">
                بازگشت به فروشگاه
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// Basic form validation feedback (Tailwind doesn't do this automatically like Bootstrap)
// You might want to add more sophisticated JS validation or rely on server-side.
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
        // Add was-validated class to show Tailwind's peer-invalid styles if you set them up
        // For now, server-side validation is primary.
      }, false)
    })
})()

// Quantity update buttons in cart page (if any were present, they are now removed from checkout)
// If you need quantity update on checkout summary, it would be more complex.
</script>

<?php require_once(__DIR__ . '/../layouts/footer.php'); ?>
