<?php 
// app/views/cart/index.php
// This view expects $data['pageTitle'], $data['cartItems'] (from $_SESSION['cart']), 
// and $data['totalPrice'] from CartController::index()

// Ensure APPROOT is defined (should be in config.php loaded by public/index.php)
if (!defined('APPROOT')) {
    // Fallback or error, though this should not happen if config is loaded
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
            <li class="text-neutral-dark font-medium" aria-current="page">سبد خرید</li>
        </ol>
    </nav>

    <h1 class="text-3xl md:text-4xl font-bold text-neutral-darkest mb-8 text-center">
        <i class="fas fa-shopping-cart text-primary mr-3"></i>سبد خرید شما
    </h1>

    <?php flash('cart_message'); ?>
    <?php flash('cart_error'); ?>

    <?php if (isset($data['cartItems']) && !empty($data['cartItems'])): ?>
        <div class="bg-white shadow-xl rounded-lg p-4 sm:p-6 md:p-8">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 rounded-t-lg">
                        <tr>
                            <th scope="col" class="px-4 py-3 sm:px-6 min-w-[80px] sm:min-w-[100px]">محصول</th>
                            <th scope="col" class="px-4 py-3 sm:px-6"></th>
                            <th scope="col" class="px-4 py-3 sm:px-6 text-center min-w-[100px]">قیمت واحد</th>
                            <th scope="col" class="px-4 py-3 sm:px-6 text-center min-w-[120px] md:min-w-[140px]">تعداد</th>
                            <th scope="col" class="px-4 py-3 sm:px-6 text-center min-w-[100px]">جمع جزء</th>
                            <th scope="col" class="px-4 py-3 sm:px-6 text-center">حذف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['cartItems'] as $item_key => $item): ?>
                            <?php
                                // $item_key is the cart item key (e.g., product_id or product_id-variation_id)
                                if (!is_array($item) || !isset($item['product_id']) || !isset($item['name']) || !isset($item['quantity']) || !isset($item['price'])) {
                                    error_log("Cart item with key {$item_key} has invalid structure: " . print_r($item, true));
                                    continue; 
                                }
                                $product_page_url = BASE_URL . 'products/show/' . $item['product_id'];
                                $image_display_url = !empty($item['image_url']) ? BASE_URL . htmlspecialchars($item['image_url']) : 'https://placehold.co/80x80/e2e8f0/333?text=' . urlencode(mb_substr($item['name'],0,10,'UTF-8'));
                            ?>
                            <tr class="bg-white border-b hover:bg-gray-50 product-row" data-item-id="<?php echo htmlspecialchars($item_key); ?>">
                                <td class="p-2 sm:p-4">
                                    <a href="<?php echo $product_page_url; ?>">
                                        <img src="<?php echo $image_display_url; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-md shadow-sm hover:shadow-md transition-shadow">
                                    </a>
                                </td>
                                <td class="px-3 sm:px-6 py-4 font-medium text-gray-900">
                                    <a href="<?php echo $product_page_url; ?>" class="hover:text-primary transition-colors text-sm md:text-base">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                    <?php if (isset($item['variation_name']) && !empty($item['variation_name'])): ?>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($item['variation_name']); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-center font-mono text-sm md:text-base">
                                    <?php echo number_format((float)$item['price']); ?> ت
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-center">
                                    <form action="<?php echo BASE_URL; ?>cart/update" method="post" class="inline-flex items-center update-quantity-form">
                                        <input type="hidden" name="item_key" value="<?php echo htmlspecialchars($item_key); ?>">
                                        <button type="button" class="quantity-change-btn p-1 sm:px-2 sm:py-1 border border-gray-300 rounded-r-md hover:bg-gray-100 focus:outline-none text-sm" data-change="-1" aria-label="کاهش تعداد">-</button>
                                        <input type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" 
                                               class="w-10 sm:w-12 text-center border-t border-b border-gray-300 py-1 sm:py-1.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm quantity-input" 
                                               onchange="this.form.submit()">
                                        <button type="button" class="quantity-change-btn p-1 sm:px-2 sm:py-1 border border-gray-300 rounded-l-md hover:bg-gray-100 focus:outline-none text-sm" data-change="1" aria-label="افزایش تعداد">+</button>
                                    </form>
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-center font-semibold font-mono text-sm md:text-base">
                                    <?php echo number_format((float)$item['price'] * (int)$item['quantity']); ?> ت
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-center">
                                    <form action="<?php echo BASE_URL; ?>cart/remove" method="post" onsubmit="return confirm('آیا از حذف این محصول از سبد خرید مطمئن هستید؟');">
                                        <input type="hidden" name="item_key_to_remove" value="<?php echo htmlspecialchars($item_key); ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" aria-label="حذف محصول">
                                            <i class="fas fa-trash-alt text-base md:text-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="w-full md:w-auto">
                    <a href="<?php echo BASE_URL; ?>products" class="text-primary hover:text-primary-dark font-medium transition-colors inline-flex items-center">
                        <i class="fas fa-arrow-right ml-2"></i> ادامه خرید و مشاهده سایر محصولات
                    </a>
                </div>
                <div class="w-full md:max-w-sm bg-neutral-light p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-lg font-semibold text-gray-700">جمع کل سبد خرید:</span>
                        <span class="text-2xl font-bold text-primary font-mono">
                            <?php echo isset($data['totalPrice']) ? number_format((float)$data['totalPrice']) : '0'; ?> تومان
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">هزینه ارسال و مالیات در مرحله بعد محاسبه خواهد شد.</p>
                    <a href="<?php echo BASE_URL; ?>checkout" 
                       class="block w-full text-center bg-accent hover:bg-accent-dark text-white font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-md text-base">
                        ادامه جهت تسویه حساب <i class="fas fa-credit-card ml-2"></i>
                    </a>
                </div>
            </div>

        </div>
    <?php else: ?>
        <div class="text-center py-12 bg-white shadow-xl rounded-lg">
            <i class="fas fa-shopping-cart fa-4x text-gray-300 mb-6"></i>
            <p class="text-2xl text-gray-700 font-semibold mb-3">سبد خرید شما خالی است!</p>
            <p class="text-gray-500 mb-6">به نظر می‌رسد هنوز محصولی به سبد خرید خود اضافه نکرده‌اید.</p>
            <a href="<?php echo BASE_URL; ?>products" 
               class="bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-8 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-md">
                شروع خرید <i class="fas fa-store ml-2"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityChangeButtons = document.querySelectorAll('.quantity-change-btn');
    quantityChangeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.update-quantity-form');
            const input = form.querySelector('.quantity-input');
            if (!input || !form) return;
            let currentValue = parseInt(input.value);
            const change = parseInt(this.dataset.change);
            currentValue += change;
            if (currentValue < 1) {
                currentValue = 1;
            }
            // Optional: Add max stock check if available from product data
            // const maxStock = parseInt(input.getAttribute('max'));
            // if (maxStock && currentValue > maxStock) currentValue = maxStock;
            input.value = currentValue;
            form.submit(); // Submit the form to update quantity via backend
        });
    });
});
</script>
<?php require_once(__DIR__ . '/../layouts/footer.php'); ?>
