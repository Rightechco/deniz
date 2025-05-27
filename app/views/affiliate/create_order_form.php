<?php // ویو: app/views/affiliate/create_order_form.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'ثبت سفارش جدید برای مشتری'); ?></h1>

<?php 
flash('order_success');
flash('order_fail');
flash('form_error_create_order'); // نام فلش مسیج برای خطاهای این فرم
?>

<form action="<?php echo BASE_URL; ?>affiliate/createOrderForCustomer" method="post" id="affiliateCreateOrderForm">
    <div style="display:flex; flex-wrap:wrap; gap:20px;">
        
        <div style="flex:2; min-width:400px; border: 1px solid #007bff; padding: 15px; border-radius: 5px;">
            <h3 style="margin-top:0;">۱. انتخاب محصولات</h3>
            <div style="margin-bottom: 15px;">
                <label for="category_filter">انتخاب دسته‌بندی:</label>
                <select id="category_filter" style="width:100%; padding:8px;">
                    <option value="">همه محصولات</option>
                    <?php if (isset($data['categories']) && !empty($data['categories'])): ?>
                        <?php foreach($data['categories'] as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div id="product_list_container" style="max-height:400px; overflow-y:auto; border:1px solid #eee; padding:10px;">
                <?php if (isset($data['products']) && !empty($data['products'])): ?>
                    <?php foreach($data['products'] as $product): ?>
                        <div class="product-item-selectable" data-category-id="<?php echo htmlspecialchars($product['category_id'] ?? ''); ?>" style="border-bottom:1px solid #f0f0f0; padding-bottom:10px; margin-bottom:10px;">
                            <div style="display:flex; gap:10px;">
                                <img src="<?php echo !empty($product['image_url']) ? BASE_URL . htmlspecialchars($product['image_url']) : BASE_URL . 'images/placeholder.png'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                                <div>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong> (ID: <?php echo $product['id']; ?>)<br>
                                    <small>قیمت پایه: <?php echo htmlspecialchars(number_format((float)($product['price'] ?? 0))); ?> ت</small>
                                    <?php if ($product['product_type'] == 'variable'): ?>
                                        <span style="color:blue; font-size:0.8em;">(متغیر)</span>
                                        <div class="variation-selectors-for-<?php echo $product['id']; ?>" style="margin-top:5px; display:none;">
                                            </div>
                                        <input type="hidden" class="selected-variation-id-for-<?php echo $product['id']; ?>" value="">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="margin-top:5px; display:flex; align-items:center;">
                                <label for="qty_<?php echo $product['id']; ?>" style="margin:0 5px 0 0; font-size:0.9em;">تعداد:</label>
                                <input type="number" id="qty_<?php echo $product['id']; ?>" value="1" min="1" style="width:60px; padding:5px;" class="product-quantity-input">
                                <button type="button" class="button-link btn-sm add-to-order-btn" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" data-product-price="<?php echo htmlspecialchars($product['price'] ?? '0'); ?>" data-product-type="<?php echo $product['product_type']; ?>" style="margin-right:10px; padding:5px 10px; font-size:0.9em;">افزودن به سفارش</button>
                            </div>
                             <div class="variation-info-for-<?php echo $product['id']; ?>" style="font-size:0.85em; color:green; margin-top:5px;"></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>محصولی برای نمایش یافت نشد.</p>
                <?php endif; ?>
            </div>
        </div>

        <div style="flex:1; min-width:300px;">
            <fieldset style="border: 1px solid #198754; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <legend style="color:#198754; font-weight:bold;">۲. آیتم‌های سفارش فعلی</legend>
                <div id="current_order_items_display" style="min-height:100px; max-height:250px; overflow-y:auto; background-color:#fdfdfd; padding:10px; border:1px solid #eee;">
                    <p><em>هنوز محصولی انتخاب نشده است.</em></p>
                </div>
                <hr>
                <p><strong>مجموع قیمت سفارش:</strong> <span id="total_order_price_display">0</span> تومان</p>
                <p><strong>کمیسیون شما از این سفارش:</strong> <span id="affiliate_commission_display">0</span> تومان</p>
                <p><strong>مبلغ خالص قابل پرداخت (توسط شما یا مشتری):</strong> <span id="net_payable_display">0</span> تومان</p>
                <input type="hidden" name="order_items_json" id="order_items_json_input" value="[]">
            </fieldset>

            <fieldset style="border: 1px solid #0dcaf0; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <legend style="color:#0dcaf0; font-weight:bold;">۳. اطلاعات مشتری (گیرنده)</legend>
                <div style="margin-bottom: 10px;"><label for="customer_first_name">نام: <sup>*</sup></label><input type="text" name="customer_first_name" id="customer_first_name" value="<?php echo htmlspecialchars($data['customer_first_name'] ?? ''); ?>" required> <span class="error-text"><?php echo isset($data['errors']['first_name_err']) ? $data['errors']['first_name_err'] : ''; ?></span></div>
                <div style="margin-bottom: 10px;"><label for="customer_last_name">نام خانوادگی: <sup>*</sup></label><input type="text" name="customer_last_name" id="customer_last_name" value="<?php echo htmlspecialchars($data['customer_last_name'] ?? ''); ?>" required> <span class="error-text"><?php echo isset($data['errors']['last_name_err']) ? $data['errors']['last_name_err'] : ''; ?></span></div>
                <div style="margin-bottom: 10px;"><label for="customer_email">ایمیل: <sup>*</sup></label><input type="email" name="customer_email" id="customer_email" value="<?php echo htmlspecialchars($data['customer_email'] ?? ''); ?>" required> <span class="error-text"><?php echo isset($data['errors']['email_err']) ? $data['errors']['email_err'] : ''; ?></span></div>
                <div style="margin-bottom: 10px;"><label for="customer_phone">تلفن: <sup>*</sup></label><input type="tel" name="customer_phone" id="customer_phone" value="<?php echo htmlspecialchars($data['customer_phone'] ?? ''); ?>" required> <span class="error-text"><?php echo isset($data['errors']['phone_err']) ? $data['errors']['phone_err'] : ''; ?></span></div>
                <div style="margin-bottom: 10px;"><label for="customer_address">آدرس: <sup>*</sup></label><textarea name="customer_address" id="customer_address" rows="2" required><?php echo htmlspecialchars($data['customer_address'] ?? ''); ?></textarea> <span class="error-text"><?php echo isset($data['errors']['address_err']) ? $data['errors']['address_err'] : ''; ?></span></div>
                <div style="margin-bottom: 10px;"><label for="customer_city">شهر: <sup>*</sup></label><input type="text" name="customer_city" id="customer_city" value="<?php echo htmlspecialchars($data['customer_city'] ?? ''); ?>" required> <span class="error-text"><?php echo isset($data['errors']['city_err']) ? $data['errors']['city_err'] : ''; ?></span></div>
                <div style="margin-bottom: 10px;"><label for="customer_postal_code">کد پستی: <sup>*</sup></label><input type="text" name="customer_postal_code" id="customer_postal_code" value="<?php echo htmlspecialchars($data['customer_postal_code'] ?? ''); ?>" required> <span class="error-text"><?php echo isset($data['errors']['postal_code_err']) ? $data['errors']['postal_code_err'] : ''; ?></span></div>
                <div style="margin-bottom: 10px;"><label for="order_notes">یادداشت سفارش (اختیاری):</label><textarea name="order_notes" id="order_notes" rows="2"><?php echo htmlspecialchars($data['order_notes'] ?? ''); ?></textarea></div>
            </fieldset>

            <fieldset style="border: 1px solid #6f42c1; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <legend style="color:#6f42c1; font-weight:bold;">۴. پرداخت</legend>
                <p>موجودی کیف پول شما: <strong style="color:green;"><?php echo number_format($data['affiliate_balance'] ?? 0); ?> تومان</strong></p>
                
                <div style="margin-bottom: 10px;">
                    <input type="radio" name="payment_method_by_affiliate" value="customer_pays" id="customer_pays" checked>
                    <label for="customer_pays" style="display:inline-block;">پرداخت توسط مشتری (مثلاً COD)</label>
                </div>
                <?php if (isset($data['affiliate_balance']) && $data['affiliate_balance'] > 0): ?>
                <div style="margin-bottom: 10px;">
                    <input type="radio" name="payment_method_by_affiliate" value="pay_from_balance" id="pay_from_balance">
                    <label for="pay_from_balance" style="display:inline-block;">پرداخت از موجودی کیف پول من</label>
                    <br><small>(مبلغ سفارش پس از کسر کمیسیون شما، از موجودی کسر خواهد شد)</small>
                </div>
                <?php endif; ?>
            </fieldset>
        </div>
    </div>

    <button type="submit" class="button-link" style="background-color:#28a745; font-size:1.2em; padding: 12px 25px;">ثبت نهایی سفارش برای مشتری</button>
    <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link button-secondary" style="margin-left:10px;">انصراف</a>
</form>

<script type="text/template" id="all_products_data_template">
    <?php echo json_encode(isset($data['products']) ? $data['products'] : []); ?>
</script>
<script type="text/template" id="all_attributes_data_template">
    <?php echo json_encode(isset($data['all_attributes']) ? $data['all_attributes'] : []); /* این باید شامل مقادیر هم باشد */ ?>
</script>
<script type="text/template" id="all_variations_data_template">
    <?php echo isset($data['product_variations_json_map']) ? $data['product_variations_json_map'] : '{}'; ?>
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- دریافت داده‌های اولیه از تمپلیت‌ها ---
    let allProducts = [];
    try { allProducts = JSON.parse(document.getElementById('all_products_data_template').textContent); }
    catch(e) { console.error("Error parsing allProducts JSON", e); }

    let allAttributes = []; // این باید شامل ساختار ویژگی‌ها و مقادیرشان باشد
    try { allAttributes = JSON.parse(document.getElementById('all_attributes_data_template').textContent); }
    catch(e) { console.error("Error parsing allAttributes JSON", e); }
    
    let productVariationsMap = {}; // { product_id: [variations] }
    try { productVariationsMap = JSON.parse(document.getElementById('all_variations_data_template').textContent); }
    catch(e) { console.error("Error parsing productVariationsMap JSON", e); }


    const categoryFilter = document.getElementById('category_filter');
    const productListContainer = document.getElementById('product_list_container');
    const currentOrderItemsDisplay = document.getElementById('current_order_items_display');
    const totalOrderPriceDisplay = document.getElementById('total_order_price_display');
    const affiliateCommissionDisplay = document.getElementById('affiliate_commission_display');
    const netPayableDisplay = document.getElementById('net_payable_display');
    const orderItemsJsonInput = document.getElementById('order_items_json_input');
    
    let currentOrderItems = []; // آرایه‌ای برای نگهداری آیتم‌های سفارش فعلی

    // --- تابع برای رندر کردن لیست محصولات بر اساس دسته‌بندی ---
    function renderProducts(categoryId = null) {
        let html = '';
        const productsToRender = categoryId ? allProducts.filter(p => String(p.category_id) === String(categoryId)) : allProducts;

        if (productsToRender.length === 0) {
            html = '<p>محصولی در این دسته‌بندی یافت نشد.</p>';
        } else {
            productsToRender.forEach(product => {
                html += `
                    <div class="product-item-selectable" data-category-id="${product.category_id || ''}" style="border-bottom:1px solid #f0f0f0; padding-bottom:10px; margin-bottom:10px;">
                        <div style="display:flex; gap:10px;">
                            <img src="${product.image_url ? '<?php echo BASE_URL; ?>' + product.image_url : '<?php echo BASE_URL; ?>images/placeholder.png'}" alt="${product.name}" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                            <div>
                                <strong>${product.name}</strong> (ID: ${product.id})<br>
                                <small>قیمت پایه: ${parseFloat(product.price || 0).toLocaleString('fa-IR')} ت</small>
                                ${product.product_type == 'variable' ? '<span style="color:blue; font-size:0.8em;">(متغیر)</span>' : ''}
                            </div>
                        </div>
                        ${product.product_type == 'variable' ? renderVariationSelectors(product.id) : ''}
                        <div class="variation-info-for-${product.id}" style="font-size:0.85em; color:green; margin-top:5px; min-height:1.2em;"></div>
                        <div style="margin-top:5px; display:flex; align-items:center;">
                            <label for="qty_${product.id}" style="margin:0 5px 0 0; font-size:0.9em;">تعداد:</label>
                            <input type="number" id="qty_${product.id}" value="1" min="1" style="width:60px; padding:5px;" class="product-quantity-input">
                            <button type="button" class="button-link btn-sm add-to-order-btn" 
                                data-product-id="${product.id}" 
                                data-product-name="${product.name}" 
                                data-product-price="${product.price || '0'}" 
                                data-product-type="${product.product_type}"
                                data-affiliate-commission-type="${product.affiliate_commission_type || 'none'}"
                                data-affiliate-commission-value="${product.affiliate_commission_value || '0'}"
                                style="margin-right:10px; padding:5px 10px; font-size:0.9em;">افزودن به سفارش</button>
                        </div>
                    </div>
                `;
            });
        }
        productListContainer.innerHTML = html;
        attachAddToOrderListeners(); // اتصال مجدد event listener ها
        attachVariationSelectListeners(); // اتصال مجدد برای انتخابگرهای تنوع
    }

    // --- تابع برای ایجاد انتخابگرهای ویژگی برای محصولات متغیر ---
    function renderVariationSelectors(productId) {
        let selectorsHtml = '<div class="variation-selectors-group" data-product-id-var-select="'+productId+'" style="margin-top:5px;">';
        const productConfigurableAttributes = allAttributes.filter(attr => {
            // این بخش نیاز به داده product_configurable_attributes برای هر محصول دارد
            // یا اینکه همه ویژگی‌ها را نمایش دهیم و با JS فیلتر کنیم
            // فعلاً همه ویژگی‌های موجود را نمایش می‌دهیم (این باید اصلاح شود تا فقط ویژگی‌های قابل تنظیم محصول نمایش داده شود)
            return true; // TODO: Filter based on actual configurable attributes for THIS product
        });

        // برای این کار، باید داده product_configurable_attributes از کنترلر به صورت JSON ارسال شود
        // و در اینجا بر اساس آن فیلتر شود. یا اینکه all_attributes شامل مقادیر هر ویژگی باشد.
        // فرض می‌کنیم allAttributes ساختاری شبیه به $data['product_configurable_attributes'] در ویو show.php دارد

        const productVariations = productVariationsMap[productId] || [];
        if (productVariations.length > 0 && productVariations[0].attributes && productVariations[0].attributes.length > 0) {
            // استخراج ویژگی‌های منحصر به فرد از تنوع‌های موجود برای این محصول
            const uniqueAttributesForProduct = {};
            productVariations.forEach(variation => {
                variation.attributes.forEach(attr => {
                    if (!uniqueAttributesForProduct[attr.attribute_id]) {
                        uniqueAttributesForProduct[attr.attribute_id] = {
                            id: attr.attribute_id,
                            name: attr.attribute_name,
                            values: {}
                        };
                    }
                    uniqueAttributesForProduct[attr.attribute_id].values[attr.attribute_value_id] = attr.attribute_value;
                });
            });

            Object.values(uniqueAttributesForProduct).forEach(attribute => {
                selectorsHtml += `
                    <div style="margin-bottom:5px;">
                        <label for="var_attr_${productId}_${attribute.id}" style="font-size:0.85em; margin-right:5px;">${attribute.name}:</label>
                        <select class="variation-attribute-select-dynamic" data-product-id="${productId}" data-attribute-id="${attribute.id}" id="var_attr_${productId}_${attribute.id}" style="padding:3px; font-size:0.85em;">
                            <option value="">انتخاب کنید</option>
                            ${Object.entries(attribute.values).map(([valId, valName]) => `<option value="${valId}">${valName}</option>`).join('')}
                        </select>
                    </div>
                `;
            });
        } else if (productVariations.length > 0) { // محصول متغیر است اما تنوع‌ها ویژگی خاصی ندارند (یک تنوع پیش‌فرض)
             selectorsHtml += '<small><em>این محصول متغیر یک گزینه پیش‌فرض دارد.</em></small>';
        }
         else {
            selectorsHtml += '<small><em>ویژگی‌های قابل انتخاب برای این محصول متغیر تعریف نشده‌اند.</em></small>';
        }
        selectorsHtml += '</div>';
        return selectorsHtml;
    }
    
    // --- تابع برای به‌روزرسانی اطلاعات تنوع (قیمت، موجودی) ---
    function updateDynamicVariationInfo(productId) {
        const variationSelectorsGroup = document.querySelector(`.variation-selectors-group[data-product-id-var-select="${productId}"]`);
        if (!variationSelectorsGroup) return;

        const selectedDynamicOptions = {};
        let allDynamicSelected = true;
        const dynamicSelects = variationSelectorsGroup.querySelectorAll('.variation-attribute-select-dynamic');
        
        if(dynamicSelects.length === 0 && (productVariationsMap[productId] || []).length === 1 && (!productVariationsMap[productId][0].attributes || productVariationsMap[productId][0].attributes.length === 0) ){
            // حالت محصول متغیر با یک تنوع پیش‌فرض بدون ویژگی
            const defaultVariation = productVariationsMap[productId][0];
             displayVariationDetails(productId, defaultVariation);
            return;
        }


        dynamicSelects.forEach(select => {
            if (select.value) {
                selectedDynamicOptions[select.dataset.attributeId] = select.value;
            } else {
                allDynamicSelected = false;
            }
        });

        const variationInfoDiv = document.querySelector(`.variation-info-for-${productId}`);
        const hiddenVariationIdInput = document.querySelector(`.product-item-selectable input[data-product-id="${productId}"]`).closest('.product-item-selectable').querySelector('input[type="hidden"]'); // پیدا کردن فیلد مخفی مربوطه

        if (!allDynamicSelected) {
            variationInfoDiv.innerHTML = '<span style="color:orange;">لطفاً تمام گزینه‌ها را انتخاب کنید.</span>';
            if(hiddenVariationIdInput) hiddenVariationIdInput.value = '';
            return;
        }

        const variationsForThisProduct = productVariationsMap[productId] || [];
        let matchedVariation = null;
        for (const variation of variationsForThisProduct) {
            if (!variation.attributes || variation.attributes.length !== Object.keys(selectedDynamicOptions).length) continue;
            let currentMatch = true;
            const variationAttrsMap = {};
            variation.attributes.forEach(attr => { variationAttrsMap[String(attr.attribute_id)] = String(attr.attribute_value_id); });
            for (const selectedAttrId in selectedDynamicOptions) {
                if (String(variationAttrsMap[selectedAttrId]) !== String(selectedDynamicOptions[selectedAttrId])) {
                    currentMatch = false;
                    break;
                }
            }
            if (currentMatch) {
                matchedVariation = variation;
                break;
            }
        }
        displayVariationDetails(productId, matchedVariation);
    }

    function displayVariationDetails(productId, variation){
        const variationInfoDiv = document.querySelector(`.variation-info-for-${productId}`);
        const productItemDiv = document.querySelector(`.product-item-selectable input[data-product-id="${productId}"]`).closest('.product-item-selectable');
        const addToOrderBtn = productItemDiv.querySelector('.add-to-order-btn');
        const quantityInputEl = productItemDiv.querySelector('.product-quantity-input');
        const hiddenVariationIdInput = productItemDiv.querySelector('input[type="hidden"]'); // این باید به درستی انتخاب شود

        if (variation && parseInt(variation.is_active) === 1) {
            variationInfoDiv.innerHTML = `قیمت: <strong>${parseFloat(variation.price || 0).toLocaleString('fa-IR')} ت</strong> - موجودی: ${variation.stock_quantity}`;
            if(hiddenVariationIdInput) hiddenVariationIdInput.value = variation.id; // ذخیره شناسه تنوع
            addToOrderBtn.dataset.currentPrice = variation.price || '0'; // ذخیره قیمت تنوع در دکمه
            addToOrderBtn.dataset.variationId = variation.id;
            addToOrderBtn.dataset.variationName = variation.attributes ? variation.attributes.map(a => a.attribute_value).join(' / ') : '';

            if (parseInt(variation.stock_quantity) > 0) {
                addToOrderBtn.disabled = false;
                quantityInputEl.max = variation.stock_quantity;
            } else {
                variationInfoDiv.innerHTML += ' <strong style="color:red;">(اتمام موجودی)</strong>';
                addToOrderBtn.disabled = true;
            }
        } else {
            variationInfoDiv.innerHTML = variation ? '<span style="color:red;">این تنوع فعال نیست یا موجود نیست.</span>' : '<span style="color:red;">ترکیب انتخاب شده موجود نیست.</span>';
            if(hiddenVariationIdInput) hiddenVariationIdInput.value = '';
            addToOrderBtn.dataset.currentPrice = productItemDiv.querySelector('.add-to-order-btn').dataset.productPrice; // بازگشت به قیمت والد
            addToOrderBtn.dataset.variationId = '';
            addToOrderBtn.dataset.variationName = '';
            addToOrderBtn.disabled = true;
        }
    }


    // --- تابع برای افزودن آیتم به سفارش فعلی (در کلاینت) ---
    function addToCurrentOrder(item) {
        // بررسی تکراری بودن (بر اساس product_id و variation_id)
        const existingItemIndex = currentOrderItems.findIndex(
            i => i.product_id === item.product_id && (i.variation_id || null) === (item.variation_id || null)
        );
        if (existingItemIndex > -1) {
            currentOrderItems[existingItemIndex].quantity = parseInt(currentOrderItems[existingItemIndex].quantity) + parseInt(item.quantity);
        } else {
            currentOrderItems.push(item);
        }
        renderCurrentOrder();
    }

    // --- تابع برای حذف آیتم از سفارش فعلی ---
    function removeFromCurrentOrder(index) {
        currentOrderItems.splice(index, 1);
        renderCurrentOrder();
    }

    // --- تابع برای رندر کردن آیتم‌های سفارش فعلی در صفحه ---
    function renderCurrentOrder() {
        let html = '';
        let totalPrice = 0;
        let totalAffiliateCommission = 0;

        if (currentOrderItems.length === 0) {
            html = '<p><em>هنوز محصولی انتخاب نشده است.</em></p>';
        } else {
            html = '<ul style="list-style:none; padding:0;">';
            currentOrderItems.forEach((item, index) => {
                const itemSubtotal = parseFloat(item.price) * parseInt(item.quantity);
                totalPrice += itemSubtotal;

                let itemAffiliateCommission = 0;
                if (item.affiliate_commission_type && item.affiliate_commission_type !== 'none' && item.affiliate_commission_value > 0) {
                    if (item.affiliate_commission_type === 'percentage') {
                        itemAffiliateCommission = roundToTwo(itemSubtotal * (parseFloat(item.affiliate_commission_value) / 100));
                    } else if (item.affiliate_commission_type === 'fixed') {
                        itemAffiliateCommission = parseFloat(item.affiliate_commission_value) * parseInt(item.quantity);
                    }
                }
                totalAffiliateCommission += itemAffiliateCommission;

                html += `
                    <li style="margin-bottom:10px; padding-bottom:10px; border-bottom:1px dotted #eee; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong>${item.name}</strong> ${item.variation_name ? '('+item.variation_name+')' : ''}<br>
                            <small>${item.quantity} عدد × ${parseFloat(item.price).toLocaleString('fa-IR')} ت = ${itemSubtotal.toLocaleString('fa-IR')} ت</small>
                        </div>
                        <button type="button" class="button-link button-danger btn-sm remove-from-order-btn" data-index="${index}" style="padding:3px 6px; font-size:0.8em;">حذف</button>
                    </li>
                `;
            });
            html += '</ul>';
        }
        currentOrderItemsDisplay.innerHTML = html;
        totalOrderPriceDisplay.textContent = totalPrice.toLocaleString('fa-IR');
        affiliateCommissionDisplay.textContent = totalAffiliateCommission.toLocaleString('fa-IR');
        netPayableDisplay.textContent = (totalPrice - totalAffiliateCommission).toLocaleString('fa-IR');
        
        // به‌روزرسانی فیلد مخفی با داده‌های JSON سفارش
        orderItemsJsonInput.value = JSON.stringify(currentOrderItems);
        
        // اتصال مجدد event listener برای دکمه‌های حذف
        attachRemoveFromOrderListeners();
    }
    
    function roundToTwo(num) {
        return +(Math.round(num + "e+2")  + "e-2");
    }

    // --- اتصال Event Listener ها ---
    function attachAddToOrderListeners() {
        document.querySelectorAll('.add-to-order-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const productType = this.dataset.productType;
                const productPrice = this.dataset.productPrice; // قیمت پایه محصول
                const affCommType = this.dataset.affiliateCommissionType;
                const affCommValue = this.dataset.affiliateCommissionValue;

                const quantityInputEl = document.getElementById('qty_' + productId);
                const quantity = parseInt(quantityInputEl.value);

                let variationId = null;
                let variationName = '';
                let finalPrice = productPrice; // قیمت نهایی آیتم

                if (productType === 'variable') {
                    const hiddenVarIdInput = document.querySelector(`.product-item-selectable input[data-product-id="${productId}"]`).closest('.product-item-selectable').querySelector('input[type="hidden"]'); // پیدا کردن فیلد مخفی صحیح
                    variationId = hiddenVarIdInput ? hiddenVarIdInput.value : null;
                    
                    if (!variationId) {
                        alert('لطفاً ابتدا یک تنوع معتبر برای محصول متغیر انتخاب کنید.');
                        return;
                    }
                    // قیمت و نام تنوع از dataset دکمه یا از اطلاعات ذخیره شده خوانده می‌شود
                    finalPrice = this.dataset.currentPrice || productPrice; // قیمت تنوع
                    variationName = this.dataset.variationName || '';
                }

                if (quantity > 0) {
                    addToCurrentOrder({
                        product_id: productId,
                        variation_id: variationId,
                        name: productName, // نام محصول والد
                        variation_name: variationName, // نام ویژگی‌های تنوع
                        price: finalPrice,
                        quantity: quantity,
                        affiliate_commission_type: affCommType,
                        affiliate_commission_value: affCommValue
                    });
                    quantityInputEl.value = 1; // ریست کردن تعداد
                    if(productType === 'variable') { // ریست کردن انتخابگرهای تنوع
                        const varSelectors = document.querySelectorAll(`.variation-attribute-select-dynamic[data-product-id="${productId}"]`);
                        varSelectors.forEach(sel => sel.value = "");
                        updateDynamicVariationInfo(productId); // برای پاک کردن اطلاعات تنوع
                    }
                } else {
                    alert('تعداد باید حداقل ۱ باشد.');
                }
            });
        });
    }

    function attachRemoveFromOrderListeners() {
        document.querySelectorAll('.remove-from-order-btn').forEach(button => {
            button.addEventListener('click', function() {
                const indexToRemove = parseInt(this.dataset.index);
                removeFromCurrentOrder(indexToRemove);
            });
        });
    }
    
    function attachVariationSelectListeners() {
         document.querySelectorAll('.variation-attribute-select-dynamic').forEach(select => {
            // ابتدا event listener قبلی را حذف کن (اگر وجود دارد) تا از چند بار فعال شدن جلوگیری شود
            // این روش ساده است، برای مدیریت پیچیده‌تر می‌توان از AbortController یا بررسی وجود listener استفاده کرد
            const newSelect = select.cloneNode(true);
            select.parentNode.replaceChild(newSelect, select);
            
            newSelect.addEventListener('change', function() {
                const prodId = this.dataset.productId;
                updateDynamicVariationInfo(prodId);
            });
        });
    }


    // --- اجرای اولیه ---
    if(categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            renderProducts(this.value);
        });
    }
    renderProducts(); // نمایش اولیه همه محصولات
    renderCurrentOrder(); // نمایش اولیه سبد خرید (خالی)

});
</script>
