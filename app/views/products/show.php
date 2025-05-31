<?php 
// app/views/products/show.php
if (!defined('APPROOT')) { define('APPROOT', dirname(dirname(dirname(__FILE__)))); }
require_once(__DIR__ . '/../layouts/header.php');
?>

<div class="container mx-auto px-4 py-8 lg:py-12 font-vazir">
    <?php flash('product_error'); ?>
    <?php if (isset($data['product']) && $data['product']): ?>
        <?php $product = $data['product']; ?>
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500">
            <ol class="list-none p-0 inline-flex space-x-2 space-x-reverse">
                <li class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>" class="hover:text-primary transition-colors">صفحه اصلی</a>
                    <i class="fas fa-angle-left mx-2 text-gray-400"></i>
                </li>
                <li class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>products" class="hover:text-primary transition-colors">فروشگاه</a>
                    <?php if (isset($product['category_id']) && isset($product['category_name'])): ?>
                        <i class="fas fa-angle-left mx-2 text-gray-400"></i>
                    <?php endif; ?>
                </li>
                <?php if (isset($product['category_id']) && isset($product['category_name'])): ?>
                <li class="flex items-center">
                    <a href="<?php echo BASE_URL . 'products/category/' . ($product['category_slug'] ?? $product['category_id']); ?>" class="hover:text-primary transition-colors">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                     <i class="fas fa-angle-left mx-2 text-gray-400"></i>
                </li>
                <?php endif; ?>
                <li class="text-neutral-dark font-medium" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <div class="product-gallery">
                <div class="bg-white rounded-xl shadow-xl p-4 sticky top-24">
                    <div class="mb-4 h-80 md:h-96 lg:h-[500px] flex items-center justify-center overflow-hidden rounded-lg bg-gray-100">
                        <img id="mainProductImage" 
                             src="<?php echo !empty($product['image_url']) ? BASE_URL . htmlspecialchars($product['image_url']) : 'https://placehold.co/600x600/e2e8f0/333?text=' . urlencode($product['name']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="max-w-full max-h-full object-contain transition-opacity duration-300 ease-in-out cursor-zoom-in"
                             onclick="openImageModal(this.src)">
                    </div>
                    <?php 
                    $gallery_items = [];
                    if (!empty($product['image_url'])) {
                        $gallery_items[] = ['full_url' => BASE_URL . htmlspecialchars($product['image_url']), 'alt_text' => 'تصویر اصلی - ' . htmlspecialchars($product['name'])];
                    }
                    if (isset($data['gallery_images']) && !empty($data['gallery_images'])) {
                        foreach ($data['gallery_images'] as $g_img) {
                            $gallery_items[] = [
                                'full_url' => $g_img['full_url'] ?? (BASE_URL . ($g_img['image_path'] ?? 'images/placeholder.png')),
                                'alt_text' => $g_img['alt_text'] ?? $product['name']
                            ];
                        }
                    }
                    ?>
                    <?php if (count($gallery_items) > 1): ?>
                        <div class="swiper thumbnail-slider" style="padding-bottom: 30px;">
                             <div class="swiper-wrapper">
                                <?php foreach ($gallery_items as $index => $galleryItem): ?>
                                    <div class="swiper-slide p-1">
                                        <img src="<?php echo htmlspecialchars($galleryItem['full_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($galleryItem['alt_text']); ?>" 
                                             class="w-full h-20 object-cover rounded-md cursor-pointer border-2 border-transparent hover:border-primary transition-all gallery-thumbnail <?php echo ($index === 0 && !empty($product['image_url'])) ? 'active-thumbnail' : ''; ?>"
                                             onclick="changeMainImage('<?php echo htmlspecialchars($galleryItem['full_url']); ?>', this)">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-button-next-thumb text-xs !text-gray-500 hover:!text-primary"></div>
                            <div class="swiper-button-prev-thumb text-xs !text-gray-500 hover:!text-primary"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-xl shadow-xl p-6 md:p-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-neutral-darkest mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="flex items-center space-x-2 space-x-reverse text-sm text-gray-500 mb-4">
                        <?php if (isset($product['category_name']) && !empty($product['category_name'])): ?>
                            <a href="<?php echo BASE_URL . 'products/category/' . ($product['category_slug'] ?? $product['category_id']); ?>" class="hover:text-primary">
                                <i class="fas fa-tag mr-1"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                            <span>&bull;</span>
                        <?php endif; ?>
                        <?php // نمایش نام فروشنده
                        $vendor_name_display = 'فروشگاه اصلی'; // پیش‌فرض
                        if (isset($product['vendor_id']) && $product['vendor_id']) {
                            if (isset($product['vendor_full_name']) && !empty(trim($product['vendor_full_name']))) {
                                $vendor_name_display = $product['vendor_full_name'];
                            } elseif (isset($product['vendor_username']) && !empty($product['vendor_username'])) {
                                $vendor_name_display = $product['vendor_username'];
                            }
                        }
                        ?>
                        <span>فروشنده: <a href="#" class="hover:text-primary"><?php echo htmlspecialchars($vendor_name_display); ?></a></span>
                    </div>


                    <div id="productPriceSection" class="mb-6">
                         <?php if ($product['product_type'] == 'variable'): ?>
                            <p class="text-3xl font-extrabold text-primary">
                                <span id="dynamicProductPrice">
                                    <small class="text-lg text-gray-500 font-medium">لطفاً گزینه‌ها را انتخاب کنید</small>
                                </span>
                            </p>
                        <?php else: ?>
                            <p class="text-3xl font-extrabold text-primary">
                                <?php echo (isset($product['price']) && $product['price'] > 0) ? number_format((float)$product['price']) . ' <span class="text-lg font-medium">تومان</span>' : 'تماس بگیرید'; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-6 text-sm text-gray-600 leading-relaxed prose prose-sm max-w-none">
                        <?php echo nl2br(htmlspecialchars(substr($product['description'] ?? '', 0, 250) . (strlen($product['description'] ?? '') > 250 ? '...' : ''))); ?>
                         <?php if (strlen($product['description'] ?? '') > 250): ?>
                            <a href="#full_description_tab_content" class="text-primary hover:underline font-medium"> بیشتر بخوانید</a>
                        <?php endif; ?>
                    </div>

                    <form action="<?php echo BASE_URL; ?>cart/add" method="post" id="addToCartFormFrontend">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <?php if ($product['product_type'] == 'variable'): ?>
                            <div id="variation_selectors_container_frontend" class="mb-6 space-y-4">
                                <?php if (isset($data['product_configurable_attributes']) && !empty($data['product_configurable_attributes'])): ?>
                                    <?php foreach ($data['product_configurable_attributes'] as $attribute): ?>
                                        <div class="attribute-selector-group-frontend">
                                            <label for="attr_frontend_<?php echo $attribute['id']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                                انتخاب <?php echo htmlspecialchars($attribute['name']); ?>:
                                            </label>
                                            <select name="attributes[<?php echo $attribute['id']; ?>]" 
                                                    id="attr_frontend_<?php echo $attribute['id']; ?>" 
                                                    class="variation-attribute-select-frontend mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                                    data-attribute-id="<?php echo $attribute['id']; ?>">
                                                <option value="">-- <?php echo htmlspecialchars($attribute['name']); ?> --</option>
                                                <?php if (!empty($attribute['values'])): ?>
                                                    <?php foreach ($attribute['values'] as $value_item): ?>
                                                        <option value="<?php echo $value_item['id']; ?>">
                                                            <?php echo htmlspecialchars($value_item['value']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                     <p class="text-sm text-red-500">ویژگی‌های قابل تنظیمی برای این محصول یافت نشد.</p>
                                <?php endif; ?>
                                <input type="hidden" name="variation_id" id="selected_variation_id_frontend" value="">
                                <div id="variation_info_frontend" class="mt-2 text-sm min-h-[20px]"></div>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center mb-6">
                            <label for="quantity_frontend" class="ml-3 text-sm font-medium text-gray-700">تعداد:</label>
                            <div class="flex items-center border border-gray-300 rounded-md">
                                <button type="button" onclick="updateQuantity(-1)" class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-r-md focus:outline-none">-</button>
                                <input type="number" name="quantity" id="quantity_frontend" value="1" min="1" 
                                       class="w-16 text-center border-t border-b border-gray-300 py-2 focus:outline-none focus:ring-0 focus:border-gray-300">
                                <button type="button" onclick="updateQuantity(1)" class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-l-md focus:outline-none">+</button>
                            </div>
                        </div>

                        <div id="stock_status_frontend" class="mb-6 text-sm min-h-[20px]">
                            <?php if ($product['product_type'] == 'simple' && isset($product['stock_quantity'])): ?>
                                <?php if ((int)$product['stock_quantity'] > 0): ?>
                                    <span class="text-green-600 font-semibold"><i class="fas fa-check-circle mr-1"></i> موجود در انبار (<?php echo $product['stock_quantity']; ?> عدد)</span>
                                <?php else: ?>
                                    <span class="text-red-500 font-semibold"><i class="fas fa-times-circle mr-1"></i> اتمام موجودی</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <button type="submit" 
                                id="addToCartButtonFrontend"
                                class="w-full flex items-center justify-center bg-accent hover:bg-accent-dark text-white font-bold py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-lg disabled:opacity-60 disabled:cursor-not-allowed"
                                <?php echo ($product['product_type'] == 'simple' && (!isset($product['stock_quantity']) || (int)$product['stock_quantity'] <= 0)) ? 'disabled' : ''; ?>>
                            <i class="fas fa-cart-plus mr-2"></i>افزودن به سبد خرید
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-10 md:mt-16 bg-white rounded-xl shadow-xl p-6 md:p-8">
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-4 space-x-reverse" aria-label="Tabs">
                    <a href="#full_description_tab_content" id="tab_description" onclick="switchTab(event, 'description')"
                       class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-primary text-primary">
                       توضیحات کامل
                    </a>
                    <a href="#specifications_tab_content" id="tab_specifications" onclick="switchTab(event, 'specifications')"
                       class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                       مشخصات فنی
                    </a>
                    <a href="#reviews_tab_content" id="tab_reviews" onclick="switchTab(event, 'reviews')"
                       class="tab-button whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                       نظرات کاربران (0)
                    </a>
                </nav>
            </div>
            <div>
                <div id="full_description_tab_content" class="tab-content prose prose-sm max-w-none text-gray-700 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'توضیحات بیشتری برای این محصول ارائه نشده است.')); ?>
                </div>
                <div id="specifications_tab_content" class="tab-content hidden">
                    <h3 class="text-lg font-semibold mb-3">مشخصات فنی</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>وزن: <?php echo htmlspecialchars($product['weight'] ?? '-'); ?> گرم</li>
                        <li>ابعاد: <?php echo htmlspecialchars($product['dimensions'] ?? '-'); ?></li>
                    </ul>
                </div>
                <div id="reviews_tab_content" class="tab-content hidden">
                     <h3 class="text-lg font-semibold mb-3">نظرات کاربران</h3>
                    <p class="text-sm text-gray-500">هنوز نظری برای این محصول ثبت نشده است. شما اولین نفر باشید!</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-exclamation-triangle fa-4x text-red-400 mb-4"></i>
            <p class="text-xl text-gray-600">محصول مورد نظر یافت نشد.</p>
            <a href="<?php echo BASE_URL; ?>products" class="mt-6 inline-block bg-primary text-white font-semibold py-2 px-6 rounded-md hover:bg-primary-dark transition-colors">
                بازگشت به فروشگاه
            </a>
        </div>
    <?php endif; ?>
</div>

<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[100] hidden p-4" onclick="closeImageModal()">
    <div class="bg-white p-2 rounded-lg max-w-3xl max-h-[90vh] relative" onclick="event.stopPropagation();">
        <img id="modalImage" src="" alt="بزرگنمایی تصویر محصول" class="max-w-full max-h-[85vh] object-contain">
        <button onclick="closeImageModal()" class="absolute top-2 right-2 text-gray-600 hover:text-red-500 bg-white bg-opacity-75 rounded-full p-1 text-2xl leading-none">
            &times;
        </button>
    </div>
</div>

<script>
    const mainProductImage = document.getElementById('mainProductImage');
    const galleryThumbnails = document.querySelectorAll('.gallery-thumbnail');

    function changeMainImage(newSrc, clickedThumbnail) { /* ... as before ... */ }
    if (galleryThumbnails.length > 0) { galleryThumbnails[0].classList.add('border-primary', 'ring-2', 'ring-primary-light'); }
    if (document.querySelector('.thumbnail-slider')) { new Swiper('.thumbnail-slider', { /* ... config ... */ }); }
    function updateQuantity(change) { /* ... as before ... */ }
    function switchTab(event, tabName) { /* ... as before ... */ }
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    function openImageModal(src) { /* ... as before ... */ }
    function closeImageModal() { /* ... as before ... */ }

    <?php if (isset($product['product_type']) && $product['product_type'] == 'variable' && isset($data['product_variations_json'])): ?>
    const variationsDataElFrontend = <?php echo $data['product_variations_json']; ?>; // This is already a JSON string
    let variationsDataFrontend = [];
    console.log("Raw variationsDataFrontend JSON string from PHP:", variationsDataElFrontend);
    try {
        // Check if it's already an object (if json_encode was not used in controller for this specific var)
        if (typeof variationsDataElFrontend === 'string') {
            variationsDataFrontend = JSON.parse(variationsDataElFrontend);
        } else {
            variationsDataFrontend = variationsDataElFrontend; // Assume it's already an object/array
        }
        console.log("Parsed variationsDataFrontend:", variationsDataFrontend);
    } catch (e) {
        console.error("Error parsing product_variations_json:", e, "Content was:", variationsDataElFrontend);
        variationsDataFrontend = []; // Fallback to empty array on error
        // Optionally display an error to the user
        const variationInfoDiv = document.getElementById('variation_info_frontend');
        if (variationInfoDiv) {
            variationInfoDiv.innerHTML = '<span class="text-red-500 font-semibold">خطا در بارگذاری تنوع‌ها. لطفاً با پشتیبانی تماس بگیرید.</span>';
        }
    }

    const attributeSelectsFrontend = document.querySelectorAll('.variation-attribute-select-frontend');
    const priceDisplayFrontend = document.getElementById('dynamicProductPrice');
    const stockStatusDisplayFrontend = document.getElementById('stock_status_frontend');
    const selectedVariationIdInputFrontend = document.getElementById('selected_variation_id_frontend');
    const addToCartButtonFrontend = document.getElementById('addToCartButtonFrontend');
    const quantityInputFrontend = document.getElementById('quantity_frontend');

    function updateVariationDetailsFrontend() {
        const selectedOptions = {};
        let allSelected = true;
        attributeSelectsFrontend.forEach(select => {
            if (select.value) {
                selectedOptions[select.dataset.attributeId] = select.value;
            } else {
                allSelected = false;
            }
        });

        if (!allSelected || (attributeSelectsFrontend.length > 0 && Object.keys(selectedOptions).length < attributeSelectsFrontend.length) ) {
            if(priceDisplayFrontend) priceDisplayFrontend.innerHTML = '<small class="text-lg text-gray-500 font-medium">لطفاً تمام گزینه‌ها را انتخاب کنید</small>';
            if(stockStatusDisplayFrontend) stockStatusDisplayFrontend.innerHTML = '';
            if(selectedVariationIdInputFrontend) selectedVariationIdInputFrontend.value = '';
            if(addToCartButtonFrontend) addToCartButtonFrontend.disabled = true;
            return;
        }
        
        let matchedVariation = null;
        if (variationsDataFrontend && variationsDataFrontend.length > 0) {
            if (attributeSelectsFrontend.length === 0 && variationsDataFrontend.length === 1 && (!variationsDataFrontend[0].attributes || variationsDataFrontend[0].attributes.length === 0)) {
                matchedVariation = variationsDataFrontend[0];
            } else {
                 matchedVariation = variationsDataFrontend.find(variation => {
                    if (!variation.attributes || !Array.isArray(variation.attributes) || variation.attributes.length !== Object.keys(selectedOptions).length) return false;
                    return variation.attributes.every(attr => String(selectedOptions[attr.attribute_id]) === String(attr.attribute_value_id));
                });
            }
        }

        if (matchedVariation && parseInt(matchedVariation.is_active) === 1) {
            if(priceDisplayFrontend) priceDisplayFrontend.innerHTML = parseFloat(matchedVariation.price || 0).toLocaleString('fa-IR') + ' <span class="text-lg font-medium">تومان</span>';
            if(selectedVariationIdInputFrontend) selectedVariationIdInputFrontend.value = matchedVariation.id;
            
            if (parseInt(matchedVariation.stock_quantity) > 0) {
                if(stockStatusDisplayFrontend) stockStatusDisplayFrontend.innerHTML = `<span class="text-green-600 font-semibold"><i class="fas fa-check-circle mr-1"></i> موجود (${matchedVariation.stock_quantity} عدد)</span>`;
                if(addToCartButtonFrontend) addToCartButtonFrontend.disabled = false;
                if(quantityInputFrontend) quantityInputFrontend.max = matchedVariation.stock_quantity;
            } else {
                if(stockStatusDisplayFrontend) stockStatusDisplayFrontend.innerHTML = '<span class="text-red-500 font-semibold"><i class="fas fa-times-circle mr-1"></i> اتمام موجودی</span>';
                if(addToCartButtonFrontend) addToCartButtonFrontend.disabled = true;
            }
        } else {
            if(priceDisplayFrontend) priceDisplayFrontend.innerHTML = '<small class="text-lg text-red-500 font-medium">این ترکیب از محصول موجود نیست.</small>';
            if(stockStatusDisplayFrontend) stockStatusDisplayFrontend.innerHTML = '';
            if(selectedVariationIdInputFrontend) selectedVariationIdInputFrontend.value = '';
            if(addToCartButtonFrontend) addToCartButtonFrontend.disabled = true;
        }
    }

    attributeSelectsFrontend.forEach(select => {
        select.addEventListener('change', updateVariationDetailsFrontend);
    });
    
    if (variationsDataFrontend && variationsDataFrontend.length === 1 && (!variationsDataFrontend[0].attributes || variationsDataFrontend[0].attributes.length === 0)) {
        if(selectedVariationIdInputFrontend) selectedVariationIdInputFrontend.value = variationsDataFrontend[0].id;
        updateVariationDetailsFrontend(); 
    } else if (attributeSelectsFrontend.length > 0) {
        updateVariationDetailsFrontend(); 
    }
    <?php endif; ?>

    const addToCartFormFrontend = document.getElementById('addToCartFormFrontend');
    if (addToCartFormFrontend) {
        addToCartFormFrontend.addEventListener('submit', function(event) {
            const productType = "<?php echo $product['product_type'] ?? 'simple'; ?>";
            if (productType === 'variable') {
                const variationId = document.getElementById('selected_variation_id_frontend').value;
                if (!variationId) {
                    event.preventDefault();
                    const variationInfoDiv = document.getElementById('variation_info_frontend');
                    if (variationInfoDiv) {
                        variationInfoDiv.innerHTML = '<span class="text-red-500 font-semibold">لطفاً تمام گزینه‌ها را برای انتخاب تنوع تکمیل کنید.</span>';
                        setTimeout(() => { variationInfoDiv.innerHTML = ''; }, 3000);
                    }
                }
            }
        });
    }
</script>

<?php require_once(__DIR__ . '/../layouts/footer.php'); ?>
