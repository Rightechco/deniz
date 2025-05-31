<?php 
// app/views/products/_product_card.php
// متغیر $product باید در این ویو در دسترس باشد
if (!isset($product) || !$product) {
    // echo '<p>اطلاعات محصول موجود نیست.</p>';
    // Placeholder for when product data is missing
    echo '<div class="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">';
    echo '    <div class="w-full h-48 bg-gray-300"></div>';
    echo '    <div class="p-4">';
    echo '        <div class="h-4 bg-gray-300 rounded w-3/4 mb-2"></div>';
    echo '        <div class="h-4 bg-gray-300 rounded w-1/2"></div>';
    echo '    </div>';
    echo '</div>';
    return;
}
$product_url = BASE_URL . 'products/show/' . ($product['id'] ?? '#');
$image_url = !empty($product['image_url']) ? BASE_URL . htmlspecialchars($product['image_url']) : 'https://placehold.co/300x300/e2e8f0/333?text=' . urlencode($product['name'] ?? 'محصول');
?>
<div class="product-card bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl group flex flex-col h-full">
    <a href="<?php echo $product_url; ?>" class="block overflow-hidden">
        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['name'] ?? 'تصویر محصول'); ?>" 
             class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
    </a>
    <div class="p-4 flex flex-col flex-grow">
        <h3 class="text-lg font-semibold text-gray-800 mb-2 truncate group-hover:text-primary">
            <a href="<?php echo $product_url; ?>" class="hover:underline">
                <?php echo htmlspecialchars($product['name'] ?? 'نام محصول'); ?>
            </a>
        </h3>
        
        <?php if (isset($product['category_name']) && !empty($product['category_name'])): ?>
            <a href="<?php echo BASE_URL . 'products/category/' . ($product['category_id'] ?? '');?>" class="text-xs text-gray-500 mb-2 hover:text-primary transition-colors">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
        <?php endif; ?>

        <div class="mt-auto"> <p class="text-xl font-bold text-primary mb-3">
                <?php 
                if ($product['product_type'] == 'variable') {
                    // For variable products, you might want to show a price range or "Starting at"
                    // This requires fetching min/max variation prices in the model or controller
                    echo 'شروع قیمت از ...'; // Placeholder
                } else {
                    echo (isset($product['price']) && $product['price'] > 0) ? number_format((float)$product['price']) . ' <span class="text-sm">تومان</span>' : 'ناموجود';
                }
                ?>
            </p>
            <a href="<?php echo $product_url; ?>" 
               class="block w-full text-center bg-secondary hover:bg-secondary-dark text-white font-semibold py-2 px-4 rounded-md transition duration-300 text-sm">
                مشاهده جزئیات
            </a>
        </div>
    </div>
</div>
