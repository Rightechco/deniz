    <?php // ویو: app/views/products/show.php ?>

    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <section style="flex-grow: 1; width: 100%;">
            <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'جزئیات محصول'); ?></h1>

            <?php 
            flash('cart_action_success'); 
            flash('cart_action_fail'); 
            flash('error_message');
            ?>

            <?php if (isset($data['product']) && $data['product']): ?>
                <div class="product-detail" style="display: flex; flex-wrap: wrap; gap: 30px; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <div class="product-image" style="flex: 1; min-width: 280px; text-align:center;">
                        <?php
                        $default_product_image_path = !empty($data['product']['image_url']) ? BASE_URL . htmlspecialchars($data['product']['image_url']) : BASE_URL . 'images/placeholder.png';
                        $default_product_alt_text = !empty($data['product']['image_url']) ? htmlspecialchars($data['product']['name']) : 'تصویر محصول';
                        ?>
                        <img id="main_product_image" src="<?php echo $default_product_image_path; ?>" alt="<?php echo $default_product_alt_text; ?>" style="max-width: 100%; height: auto; max-height: 400px; border-radius: 5px; border: 1px solid #eee;">
                    </div>

                    <div class="product-info" style="flex: 2; min-width: 300px;">
                        <p style="font-size: 1.1em; color: #555;">
                            دسته: 
                            <?php if (isset($data['product']['category_id']) && isset($data['product']['category_name'])): ?>
                                <a href="<?php echo BASE_URL; ?>products/category/<?php echo $data['product']['category_id']; ?>" style="text-decoration:none; color: #007bff;">
                                    <?php echo htmlspecialchars($data['product']['category_name']); ?>
                                </a>
                            <?php else: ?>
                                <em>بدون دسته</em>
                            <?php endif; ?>
                        </p>
                        
                        <?php if (isset($data['product']['vendor_id']) && $data['product']['vendor_id']): ?>
                            <p style="font-size: 1em; color: #555;">
                                فروشنده: 
                                <?php 
                                $vendor_display_name_public = isset($data['product']['vendor_full_name']) && !empty(trim($data['product']['vendor_full_name'])) ? $data['product']['vendor_full_name'] : ($data['product']['vendor_username'] ?? 'فروشنده #' . $data['product']['vendor_id']);
                                // لینک به صفحه فروشگاه فروشنده (در آینده)
                                // echo '<a href="' . BASE_URL . 'store/' . $data['product']['vendor_id'] . '" style="text-decoration:none; color: #007bff;">' . htmlspecialchars($vendor_display_name_public) . '</a>';
                                echo htmlspecialchars($vendor_display_name_public);
                                ?>
                            </p>
                        <?php else: ?>
                             <p style="font-size: 1em; color: #555;">فروشنده: فروشگاه <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'ما'); ?></p>
                        <?php endif; ?>
                        <hr>
                        <p><strong>توضیحات:</strong><br><?php echo nl2br(htmlspecialchars(isset($data['product']['description']) ? $data['product']['description'] : '')); ?></p>
                        
                        <?php if (isset($data['product']['product_type']) && $data['product']['product_type'] == 'simple'): ?>
                            <p id="simple_product_price_display" style="font-size: 1.5em; font-weight: bold; color: #d9534f; margin: 20px 0;">
                                <?php 
                                if (isset($data['product']['price']) && $data['product']['price'] !== null) {
                                    echo htmlspecialchars(number_format((float)$data['product']['price'])) . ' تومان';
                                } else { echo '---'; }
                                ?>
                            </p>
                            <p id="simple_product_stock_display" style="font-size: 1em; color: #777; margin-bottom:20px;">
                                موجودی انبار: <?php echo htmlspecialchars(isset($data['product']['stock_quantity']) ? $data['product']['stock_quantity'] : '0'); ?> عدد
                            </p>
                            <?php if (isset($data['product']['stock_quantity']) && $data['product']['stock_quantity'] > 0): ?>
                                <form action="<?php echo BASE_URL; ?>cart/add" method="post" style="margin-top: 20px;">
                                    <input type="hidden" name="product_id" value="<?php echo $data['product']['id']; ?>">
                                    <label for="quantity_<?php echo $data['product']['id']; ?>" style="margin-right: 10px;">تعداد:</label>
                                    <input type="number" name="quantity" id="quantity_<?php echo $data['product']['id']; ?>" value="1" min="1" max="<?php echo htmlspecialchars($data['product']['stock_quantity']); ?>" style="width: 70px; padding: 8px; margin-right: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                    <button type="submit" class="button-link" style="background-color: #28a745; padding: 10px 20px;">افزودن به سبد خرید</button>
                                </form>
                            <?php else: ?>
                                <p style="color: red; margin-top: 20px; font-weight: bold;">اتمام موجودی</p>
                            <?php endif; ?>
                        <?php elseif (isset($data['product']['product_type']) && $data['product']['product_type'] == 'variable'): ?>
                            <form action="<?php echo BASE_URL; ?>cart/add" method="post" id="variable_product_form" style="margin-top: 20px;">
                                <input type="hidden" name="product_id" value="<?php echo $data['product']['id']; ?>">
                                <input type="hidden" name="variation_id" id="selected_variation_id" value="">
                                <div id="product_attributes_options">
                                    <?php if (!empty($data['product_configurable_attributes'])): ?>
                                        <p><strong>گزینه‌ها را انتخاب کنید:</strong></p>
                                        <?php foreach ($data['product_configurable_attributes'] as $attribute): ?>
                                            <div class="attribute-selector-group" style="margin-bottom:15px;">
                                                <label for="attr_<?php echo $attribute['id']; ?>" style="display:block; margin-bottom:5px; font-weight:bold;"><?php echo htmlspecialchars($attribute['name']); ?>:</label>
                                                <select name="attributes[<?php echo $attribute['id']; ?>]" id="attr_<?php echo $attribute['id']; ?>" class="variation-attribute-select" data-attribute-id="<?php echo $attribute['id']; ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
                                                    <option value="">-- انتخاب <?php echo htmlspecialchars($attribute['name']); ?> --</option>
                                                    <?php if (!empty($attribute['values'])): ?>
                                                        <?php foreach ($attribute['values'] as $value_item): ?>
                                                            <option value="<?php echo $value_item['id']; ?>"><?php echo htmlspecialchars($value_item['value']); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p><em>این محصول متغیر هنوز ویژگی‌های قابل تنظیمی برای انتخاب ندارد.</em></p>
                                    <?php endif; ?>
                                </div>
                                <div id="variation_price_display" style="font-size: 1.5em; font-weight: bold; color: #d9534f; margin: 20px 0; min-height: 1.5em;"></div>
                                <div id="variation_stock_display" style="font-size: 1em; color: #777; margin-bottom:20px; min-height: 1em;"></div>
                                <div id="add_to_cart_section_variable" style="visibility:hidden;"> 
                                    <label for="quantity_variable_<?php echo $data['product']['id']; ?>" style="margin-right: 10px;">تعداد:</label>
                                    <input type="number" name="quantity" id="quantity_variable_<?php echo $data['product']['id']; ?>" value="1" min="1" style="width: 70px; padding: 8px; margin-right: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                    <button type="submit" id="add_to_cart_variable_btn" class="button-link" style="background-color: #28a745; padding: 10px 20px;">افزودن به سبد خرید</button>
                                </div>
                                <p id="variation_message" style="color:red; margin-top:10px; min-height: 1.2em; font-weight:bold;"></p>
                            </form>
                            <?php if (empty($data['product_configurable_attributes']) && $data['product']['product_type'] == 'variable'): ?>
                                 <p style="margin-top:15px;"><small>برای فعال کردن انتخاب تنوع، ابتدا ویژگی‌های قابل تنظیم را برای این محصول در پنل ادمین مشخص کنید و سپس تنوع‌ها را در "مدیریت تنوع‌ها" ایجاد نمایید.</small></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <br>
                <a href="<?php echo BASE_URL; ?>products/index" class="button-link button-secondary" style="display: inline-block; margin-top: 20px;">بازگشت به لیست محصولات</a>
            <?php else: ?>
                <p>اطلاعات محصول یافت نشد.</p>
            <?php endif; ?>
        </section>
    </div>

    <script type="text/template" id="product_variations_data">
        <?php echo isset($data['product_variations_json']) ? $data['product_variations_json'] : '[]'; ?>
    </script>

    <?php if (isset($data['product']) && $data['product']['product_type'] == 'variable' && !empty($data['product_configurable_attributes'])): ?>
    <script>
        // کد جاوااسکریپت برای مدیریت انتخاب تنوع‌ها (از آرتیفکت view_products_show_v4_no_sidebar)
        // ... (این کد طولانی است و در پاسخ قبلی به طور کامل آمده است)
        document.addEventListener('DOMContentLoaded', function() {
            const attributeSelectors = document.querySelectorAll('.variation-attribute-select');
            const variationsDataElement = document.getElementById('product_variations_data');
            let variations = [];
            const base_url_js = '<?php echo BASE_URL; ?>';
            const defaultImageSrc = document.getElementById('main_product_image') ? document.getElementById('main_product_image').src : '<?php echo BASE_URL . 'images/placeholder.png'; ?>';
            const defaultImageAlt = document.getElementById('main_product_image') ? document.getElementById('main_product_image').alt : 'تصویر محصول';

            if (variationsDataElement && variationsDataElement.textContent.trim() !== "") {
                try { variations = JSON.parse(variationsDataElement.textContent); } catch (e) { console.error("JS Error: Parsing variations JSON failed:", e); variations = []; }
            }
            
            const priceDisplay = document.getElementById('variation_price_display');
            const stockDisplay = document.getElementById('variation_stock_display');
            const variationMessage = document.getElementById('variation_message');
            const addToCartSection = document.getElementById('add_to_cart_section_variable');
            const addToCartButton = document.getElementById('add_to_cart_variable_btn');
            const quantityInput = document.getElementById('quantity_variable_<?php echo $data['product']['id']; ?>');
            const selectedVariationIdInput = document.getElementById('selected_variation_id');
            const mainProductImage = document.getElementById('main_product_image');

            function findMatchingVariation() {
                const selectedOptions = {}; 
                let allAttributesSelected = true;
                if(attributeSelectors.length === 0 && variations.length === 1 && (!variations[0].attributes || variations[0].attributes.length === 0)) { return variations[0]; }
                if(attributeSelectors.length === 0 && variations.length > 0) { allAttributesSelected = false; }
                attributeSelectors.forEach(selector => {
                    const attributeId = selector.dataset.attributeId;
                    if (selector.value && selector.value !== "") { selectedOptions[attributeId] = selector.value; } else { allAttributesSelected = false; }
                });
                if (!allAttributesSelected) { updateVariationDisplay(null, 'لطفاً تمام گزینه‌ها را انتخاب کنید.'); return null; }
                for (const variation of variations) {
                    if (!variation.attributes || !Array.isArray(variation.attributes) || variation.attributes.length !== Object.keys(selectedOptions).length) { continue; }
                    let match = true;
                    const variationAttrsMap = {};
                    variation.attributes.forEach(attr => { variationAttrsMap[String(attr.attribute_id)] = String(attr.attribute_value_id); });
                    for (const selectedAttrId in selectedOptions) {
                        if (String(variationAttrsMap[selectedAttrId]) !== String(selectedOptions[selectedAttrId])) { match = false; break; }
                    }
                    if (match) { updateVariationDisplay(variation); return variation; }
                }
                updateVariationDisplay(null, 'این ترکیب از ویژگی‌ها موجود نیست.'); return null; 
            }

            function updateVariationDisplay(variation, message = '') {
                if (variation && parseInt(variation.is_active) === 1) { 
                    priceDisplay.innerHTML = variation.price ? parseFloat(variation.price).toLocaleString('fa-IR') + ' تومان' : '<em>(قیمت محصول اصلی)</em>';
                    stockDisplay.innerHTML = 'موجودی: ' + parseInt(variation.stock_quantity) + ' عدد';
                    variationMessage.textContent = '';
                    addToCartSection.style.visibility = 'visible';
                    if (parseInt(variation.stock_quantity) > 0) {
                        addToCartButton.disabled = false; quantityInput.disabled = false;
                        quantityInput.max = variation.stock_quantity; quantityInput.value = 1; 
                    } else {
                        stockDisplay.innerHTML = '<strong style="color:red;">اتمام موجودی</strong>';
                        addToCartButton.disabled = true; quantityInput.disabled = true;
                        variationMessage.textContent = 'این تنوع موجود نیست.';
                    }
                    selectedVariationIdInput.value = variation.id;
                    if (variation.image_url && mainProductImage) { mainProductImage.src = base_url_js + variation.image_url; mainProductImage.alt = variation.name || 'تصویر تنوع'; } 
                    else if (mainProductImage) { mainProductImage.src = defaultImageSrc; mainProductImage.alt = defaultImageAlt; }
                } else {
                    priceDisplay.textContent = ''; stockDisplay.textContent = '';
                    addToCartSection.style.visibility = 'hidden'; addToCartButton.disabled = true; quantityInput.disabled = true;
                    selectedVariationIdInput.value = '';
                    if(mainProductImage) { mainProductImage.src = defaultImageSrc; mainProductImage.alt = defaultImageAlt; }
                    if (variation && parseInt(variation.is_active) === 0) { variationMessage.textContent = 'این تنوع در حال حاضر فعال نیست.';} 
                    else if (message) { variationMessage.textContent = message; } 
                    else {
                        let allSel = true; if(attributeSelectors.length > 0) attributeSelectors.forEach(s => { if(s.value === "") allSel = false; });
                        if(attributeSelectors.length > 0 && !allSel) { variationMessage.textContent = 'لطفاً تمام گزینه‌ها را انتخاب کنید.';} 
                        else if (attributeSelectors.length > 0) { variationMessage.textContent = 'این ترکیب از ویژگی‌ها موجود نیست.';}
                        else { variationMessage.textContent = ''; }
                    }
                }
            }
            attributeSelectors.forEach(s => { s.addEventListener('change', findMatchingVariation); });
            if(attributeSelectors.length > 0){ updateVariationDisplay(null, 'لطفاً گزینه‌ها را برای مشاهده قیمت و موجودی انتخاب کنید.'); } 
            else if (variations.length === 1 && (!variations[0].attributes || variations[0].attributes.length === 0) && parseInt(variations[0].is_active) === 1) { updateVariationDisplay(variations[0]); }
            else { addToCartSection.style.visibility = 'hidden'; addToCartButton.disabled = true; quantityInput.disabled = true; if (variations.length === 0 && attributeSelectors.length > 0) { variationMessage.textContent = 'هیچ تنوعی برای این محصول تعریف نشده است.';}}
        });
    </script>
    <?php endif; ?>
    