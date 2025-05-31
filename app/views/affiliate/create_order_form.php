<?php
// ویو: app/views/affiliate/create_order_form.php
// require_once APPROOT . '/views/layouts/header_affiliate.php';
?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'ثبت سفارش جدید برای مشتری'); ?></h1>
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link btn-sm">بازگشت به داشبورد</a>
    </div>

    <?php 
    flash('order_success');
    flash('order_fail_validation');
    flash('order_fail_user');
    flash('order_fail_db');
    flash('order_fail_balance');
    flash('form_error_create_order'); 
    ?>

    <form action="<?php echo BASE_URL; ?>affiliate/createOrderForCustomer" method="post" id="affiliateCreateOrderForm">
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-basket me-2"></i>۱. انتخاب محصولات</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="category_filter" class="form-label">فیلتر بر اساس دسته‌بندی:</label>
                            <select id="category_filter" class="form-select form-select-sm">
                                <option value="">همه محصولات</option>
                                <?php if (isset($data['categories']) && is_array($data['categories']) && !empty($data['categories'])): ?>
                                    <?php foreach($data['categories'] as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div id="product_list_container_affiliate" style="max-height: 450px; overflow-y: auto; border: 1px solid #dee2e6; padding: 15px; border-radius: 0.25rem;">
                            <?php /* محصولات توسط جاوااسکریپت در اینجا رندر می‌شوند */ ?>
                            <p class="text-center text-muted" id="product_list_placeholder_affiliate">در حال بارگذاری محصولات...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>۲. آیتم‌های سفارش فعلی</h5>
                    </div>
                    <div class="card-body">
                        <div id="current_order_items_display_affiliate" style="min-height:100px; max-height:250px; overflow-y:auto; padding:10px; border:1px solid #eee; border-radius: 0.25rem; background-color:#f8f9fa;">
                            <p class="text-center text-muted placeholder-text-order-items"><em>هنوز محصولی انتخاب نشده است.</em></p>
                        </div>
                        <hr class="my-3">
                        <h6 class="mb-1"><strong>مجموع قیمت سفارش:</strong> <span id="total_order_price_display_affiliate" class="fw-bold">0</span> تومان</h6>
                        <h6 class="mb-1"><strong>کمیسیون شما (تخمینی):</strong> <span id="affiliate_commission_display_affiliate" class="fw-bold text-success">0</span> تومان</h6>
                        <h6 class="mb-0"><strong>مبلغ خالص قابل پرداخت:</strong> <span id="net_payable_display_affiliate" class="fw-bold text-primary">0</span> تومان</h6>
                        <input type="hidden" name="order_items_json" id="order_items_json_input_affiliate" value="<?php echo htmlspecialchars($data['current_cart_items_json'] ?? '[]'); ?>">
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>۳. اطلاعات مشتری (گیرنده)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label for="customer_first_name" class="form-label">نام: <sup class="text-danger">*</sup></label>
                            <input type="text" name="customer_first_name" id="customer_first_name" class="form-control form-control-sm <?php echo !empty($data['errors']['first_name_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>" required>
                            <div class="invalid-feedback"><?php echo $data['errors']['first_name_err'] ?? ''; ?></div>
                        </div>
                        <div class="mb-2">
                            <label for="customer_last_name" class="form-label">نام خانوادگی: <sup class="text-danger">*</sup></label>
                            <input type="text" name="customer_last_name" id="customer_last_name" class="form-control form-control-sm <?php echo !empty($data['errors']['last_name_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['last_name'] ?? ''); ?>" required>
                            <div class="invalid-feedback"><?php echo $data['errors']['last_name_err'] ?? ''; ?></div>
                        </div>
                        <div class="mb-2">
                            <label for="customer_email" class="form-label">ایمیل: <sup class="text-danger">*</sup></label>
                            <input type="email" name="customer_email" id="customer_email" class="form-control form-control-sm <?php echo !empty($data['errors']['email_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" required>
                            <div class="invalid-feedback"><?php echo $data['errors']['email_err'] ?? ''; ?></div>
                        </div>
                        <div class="mb-2">
                            <label for="customer_phone" class="form-label">تلفن: <sup class="text-danger">*</sup></label>
                            <input type="tel" name="customer_phone" id="customer_phone" class="form-control form-control-sm <?php echo !empty($data['errors']['phone_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" required>
                            <div class="invalid-feedback"><?php echo $data['errors']['phone_err'] ?? ''; ?></div>
                        </div>
                        <div class="mb-2">
                            <label for="customer_address" class="form-label">آدرس: <sup class="text-danger">*</sup></label>
                            <textarea name="customer_address" id="customer_address" rows="2" class="form-control form-control-sm <?php echo !empty($data['errors']['address_err']) ? 'is-invalid' : ''; ?>" required><?php echo htmlspecialchars($data['address'] ?? ''); ?></textarea>
                            <div class="invalid-feedback"><?php echo $data['errors']['address_err'] ?? ''; ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="customer_city" class="form-label">شهر: <sup class="text-danger">*</sup></label>
                                <input type="text" name="customer_city" id="customer_city" class="form-control form-control-sm <?php echo !empty($data['errors']['city_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['city'] ?? ''); ?>" required>
                                <div class="invalid-feedback"><?php echo $data['errors']['city_err'] ?? ''; ?></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="customer_postal_code" class="form-label">کد پستی: <sup class="text-danger">*</sup></label>
                                <input type="text" name="customer_postal_code" id="customer_postal_code" class="form-control form-control-sm <?php echo !empty($data['errors']['postal_code_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['postal_code'] ?? ''); ?>" required>
                                <div class="invalid-feedback"><?php echo $data['errors']['postal_code_err'] ?? ''; ?></div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label for="order_notes" class="form-label">یادداشت سفارش (اختیاری):</label>
                            <textarea name="order_notes" id="order_notes" rows="2" class="form-control form-control-sm"><?php echo htmlspecialchars($data['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                     <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>۴. پرداخت</h5>
                    </div>
                    <div class="card-body">
                        <p>موجودی کیف پول شما: <strong class="text-success"><?php echo number_format(isset($data['affiliate_balance']) ? (float)$data['affiliate_balance'] : 0); ?> تومان</strong></p>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method_by_affiliate" value="customer_pays" id="customer_pays_radio" checked>
                            <label class="form-check-label" for="customer_pays_radio">
                                پرداخت توسط مشتری (مثلاً پرداخت در محل)
                            </label>
                        </div>
                        <?php 
                            $affiliateBalance = isset($data['affiliate_balance']) ? (float)$data['affiliate_balance'] : 0;
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method_by_affiliate" value="pay_from_balance" id="pay_from_balance_radio" <?php echo $affiliateBalance <= 0 ? 'disabled' : ''; ?>>
                            <label class="form-check-label" for="pay_from_balance_radio">
                                پرداخت از موجودی کیف پول من
                            </label>
                            <small class="d-block text-muted" id="pay_from_balance_helper_text">
                                <?php if ($affiliateBalance <= 0): ?>
                                    (موجودی شما صفر یا منفی است)
                                <?php else: ?>
                                    (مبلغ سفارش پس از کسر کمیسیون شما، از موجودی کسر خواهد شد. لطفاً از کافی بودن موجودی برای <span class="fw-bold" id="net_payable_for_balance_check_display">مبلغ خالص</span> اطمینان حاصل کنید.)
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-grid">
            <button type="submit" class="button-link button-primary btn-lg"><i class="fas fa-check-circle me-2"></i>ثبت نهایی سفارش برای مشتری</button>
        </div>
    </form>
</div>

<script type="text/template" id="all_products_data_template_affiliate">
    <?php echo json_encode(isset($data['products']) && is_array($data['products']) ? $data['products'] : []); ?>
</script>
<script type="text/template" id="all_attributes_data_template_affiliate">
    <?php echo json_encode(isset($data['all_attributes']) && is_array($data['all_attributes']) ? $data['all_attributes'] : []); ?>
</script>
<script type="text/template" id="all_variations_data_template_affiliate">
    <?php echo isset($data['product_variations_json_map']) ? $data['product_variations_json_map'] : '{}'; ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Affiliate Create Order Form JS Loaded. V3 - Debugging Variation Selectors");

    let allProducts = [];
    try {
        const productsJsonEl = document.getElementById('all_products_data_template_affiliate');
        if (productsJsonEl) {
            allProducts = JSON.parse(productsJsonEl.textContent || '[]');
        }
        console.log("Products loaded for JS:", allProducts.length); //, allProducts);
    } catch(e) { console.error("Error parsing allProducts JSON (affiliate):", e); }

    let allAttributes = []; 
    try {
        const attributesJsonEl = document.getElementById('all_attributes_data_template_affiliate');
        if (attributesJsonEl) {
            allAttributes = JSON.parse(attributesJsonEl.textContent || '[]');
        }
        console.log("Global Attributes (with values) loaded for JS:", allAttributes.length, JSON.stringify(allAttributes));
    } catch(e) { console.error("Error parsing allAttributes JSON (affiliate):", e); }
    
    let productVariationsMap = {}; 
    try {
        const variationsJsonEl = document.getElementById('all_variations_data_template_affiliate');
        if (variationsJsonEl) {
            productVariationsMap = JSON.parse(variationsJsonEl.textContent || '{}');
        }
        console.log("Product Variations Map loaded for JS:", Object.keys(productVariationsMap).length, JSON.stringify(productVariationsMap));
    } catch(e) { console.error("Error parsing productVariationsMap JSON (affiliate):", e); }

    const categoryFilter = document.getElementById('category_filter');
    const productListContainer = document.getElementById('product_list_container_affiliate');
    const productListPlaceholder = document.getElementById('product_list_placeholder_affiliate');
    const currentOrderItemsDisplay = document.getElementById('current_order_items_display_affiliate');
    const totalOrderPriceDisplay = document.getElementById('total_order_price_display_affiliate');
    const affiliateCommissionDisplay = document.getElementById('affiliate_commission_display_affiliate');
    const netPayableDisplay = document.getElementById('net_payable_display_affiliate');
    const orderItemsJsonInput = document.getElementById('order_items_json_input_affiliate');
    const placeholderTextOrderItems = currentOrderItemsDisplay ? currentOrderItemsDisplay.querySelector('.placeholder-text-order-items') : null;
    const netPayableForBalanceCheckDisplay = document.getElementById('net_payable_for_balance_check_display');
    const payFromBalanceRadio = document.getElementById('pay_from_balance_radio');
    const payFromBalanceHelperText = document.getElementById('pay_from_balance_helper_text');

    let currentOrderItems = [];
    try {
        if(orderItemsJsonInput && orderItemsJsonInput.value){
            const initialCartItems = JSON.parse(orderItemsJsonInput.value);
            if (Array.isArray(initialCartItems)) {
                currentOrderItems = initialCartItems;
            }
        }
    } catch(e) { console.error("Error parsing initial cart items JSON:", e); currentOrderItems = []; }

    function getProductById(productId) {
        return allProducts.find(p => String(p.id) === String(productId));
    }

    function getVariationById(productId, variationId) {
        const variations = productVariationsMap[String(productId)] || [];
        return variations.find(v => String(v.id) === String(variationId));
    }
    
    function roundToTwo(num) {
        return +(Math.round(num + "e+2")  + "e-2");
    }
    
    function htmlspecialchars(str) {
        if (typeof str !== 'string') {
            if (str === null || typeof str === 'undefined') return '';
            try { str = String(str); } catch (e) { return ''; }
        }
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return str.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function renderProducts(categoryId = null) {
        if (!productListContainer) { console.error("Product list container not found"); return; }
        if(productListPlaceholder) productListPlaceholder.style.display = 'none';
        
        let html = '';
        const productsToRender = categoryId ? allProducts.filter(p => String(p.category_id) === String(categoryId)) : allProducts;
        // console.log("Rendering products. Count:", productsToRender.length, "For category:", categoryId || "All");

        if (productsToRender.length === 0) {
            html = `<p class="text-center text-muted p-3">${categoryId ? 'محصولی در این دسته‌بندی یافت نشد.' : 'محصولی برای نمایش یافت نشد.'}</p>`;
        } else {
            productsToRender.forEach(product => {
                html += `
                    <div class="product-item-selectable-affiliate mb-3 pb-3 border-bottom" data-product-id-render="${product.id}" data-category-id="${product.category_id || ''}">
                        <div class="d-flex align-items-center">
                            <img src="${product.image_url ? '<?php echo BASE_URL; ?>' + htmlspecialchars(product.image_url) : '<?php echo BASE_URL; ?>images/placeholder.png'}" 
                                 alt="${htmlspecialchars(product.name)}" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.25rem; margin-left: 10px;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-size: 0.95rem;">${htmlspecialchars(product.name)} <small class="text-muted" style="font-size:0.8em;">(شناسه: ${product.id})</small></h6>
                                <small class="d-block text-muted product-base-price-display-${product.id}">
                                    قیمت پایه: ${parseFloat(product.price || 0).toLocaleString('fa-IR')} ت
                                </small>
                                ${product.product_type == 'variable' ? '<span class="badge bg-info text-dark" style="font-size:0.75em;">متغیر</span>' : ''}
                            </div>
                        </div>
                        <div class="variation-selectors-container-for-${product.id} mt-2" data-product-id="${product.id}">
                            ${product.product_type == 'variable' ? renderVariationSelectorsForProduct(product.id) : ''}
                        </div>
                        <div class="variation-info-display-for-${product.id} mt-1" style="font-size: 0.8em; color: green; min-height: 1.1em;"></div>
                        <div class="mt-2 d-flex align-items-center justify-content-end">
                            <label for="qty_aff_${product.id}" class="form-label ms-2 mb-0" style="font-size: 0.85em;">تعداد:</label>
                            <input type="number" id="qty_aff_${product.id}" value="1" min="1" class="form-control form-control-sm product-quantity-input-affiliate" style="width: 65px;">
                            <button type="button" class="button-link btn-sm ms-2 add-to-order-btn-affiliate" 
                                data-product-id="${product.id}"
                                style="white-space: nowrap; font-size:0.85em; padding: 0.25rem 0.5rem;">
                                <i class="fas fa-plus me-1"></i>افزودن
                            </button>
                        </div>
                    </div>
                `;
            });
        }
        productListContainer.innerHTML = html;
        initializeProductItems();
    }
    
    function renderVariationSelectorsForProduct(productId) {
        console.log(`RENDER_SELECTORS: Attempting for product ID: ${productId}`);
        const product = getProductById(productId);
        if (!product || product.product_type !== 'variable') {
            console.log(`RENDER_SELECTORS: Product ${productId} is not variable or not found.`);
            return '';
        }

        const variations = productVariationsMap[String(productId)] || [];
        console.log(`RENDER_SELECTORS: Found ${variations.length} variations for product ${productId}.`, variations);

        if (variations.length === 0) {
            console.log(`RENDER_SELECTORS: No variations defined for variable product ${productId}.`);
            return '<small class="text-danger d-block mt-1">تنوعی برای این محصول متغیر در سیستم تعریف نشده است.</small>';
        }

        const attributesForSelectors = {}; // Store as { attribute_id: {name, values: {value_id: value_name}} }
        variations.forEach((variation, v_idx) => {
            console.log(`RENDER_SELECTORS: Processing variation ${v_idx} for product ${productId}:`, variation);
            if (variation.attributes && Array.isArray(variation.attributes)) {
                variation.attributes.forEach(varAttr => { 
                    // varAttr is expected to be {attribute_id, attribute_value_id, attribute_name, attribute_value}
                    if (!varAttr.attribute_id || !varAttr.attribute_name || !varAttr.attribute_value_id || !varAttr.attribute_value) {
                        console.warn(`RENDER_SELECTORS: Incomplete attribute data in variation for product ${productId}:`, varAttr);
                        return; // Skip this malformed attribute
                    }
                    if (!attributesForSelectors[varAttr.attribute_id]) {
                        attributesForSelectors[varAttr.attribute_id] = {
                            id: varAttr.attribute_id,
                            name: varAttr.attribute_name, 
                            values: {} 
                        };
                    }
                    attributesForSelectors[varAttr.attribute_id].values[varAttr.attribute_value_id] = varAttr.attribute_value;
                });
            } else {
                 console.log(`RENDER_SELECTORS: Variation ${v_idx} for product ${productId} has no 'attributes' array or it's not an array.`);
            }
        });
        console.log(`RENDER_SELECTORS: Attributes collected for selectors (product ${productId}):`, JSON.parse(JSON.stringify(attributesForSelectors)));
        
        if (Object.keys(attributesForSelectors).length === 0 && variations.length === 1) {
             console.log(`RENDER_SELECTORS: Product ${productId} has one default variation (no specific attributes to select). Auto-selecting it.`);
             return `<input type="hidden" class="selected-variation-id-for-${productId}" value="${variations[0].id}">`;
        }
         if (Object.keys(attributesForSelectors).length === 0 && variations.length > 0) {
            console.warn(`RENDER_SELECTORS: Product ${productId} has variations but no attributes could be extracted for selectors. Check data structure of variation.attributes. It should contain attribute_id, attribute_name, attribute_value_id, attribute_value.`);
            return '<small class="text-warning d-block mt-1">امکان انتخاب تنوع برای این محصول وجود ندارد (خطای پیکربندی داده تنوع‌ها).</small>';
        }

        let selectorsHtml = `<div class="variation-selectors-group-affiliate mt-2" data-product-id-var-select="${productId}">`;
        Object.values(attributesForSelectors).forEach(attribute => {
            if (attribute.values && Object.keys(attribute.values).length > 0) { 
                selectorsHtml += `
                    <div class="mb-2">
                        <label for="var_attr_aff_${productId}_${attribute.id}" class="form-label form-label-sm" style="font-size:0.8rem;">${htmlspecialchars(attribute.name)}:</label>
                        <select class="form-select form-select-sm variation-attribute-select-affiliate" 
                                data-product-id="${productId}" 
                                data-attribute-id="${attribute.id}" 
                                id="var_attr_aff_${productId}_${attribute.id}">
                            <option value="">انتخاب کنید...</option>
                            ${Object.entries(attribute.values).map(([valueId, valueName]) => `<option value="${valueId}">${htmlspecialchars(valueName)}</option>`).join('')}
                        </select>
                    </div>
                `;
            } else {
                console.warn(`RENDER_SELECTORS: Attribute ${attribute.name} (ID: ${attribute.id}) for product ${productId} has no values to render a selector.`);
            }
        });
        selectorsHtml += `<input type="hidden" class="selected-variation-id-for-${productId}" value=""></div>`;
        console.log(`RENDER_SELECTORS: Generated selectors HTML for ${productId} includes <select>:`, selectorsHtml.includes('<select'));
        return selectorsHtml;
    }

    function updateVariationInfoForProduct(productId) {
        const productItemDiv = document.querySelector(`.product-item-selectable-affiliate[data-product-id-render="${productId}"]`);
        if (!productItemDiv) { console.error(`UPDATE_VAR_INFO: Product item div not found for ${productId}`); return; }

        const variationInfoDiv = productItemDiv.querySelector(`.variation-info-display-for-${productId}`);
        const hiddenVariationIdInput = productItemDiv.querySelector(`.selected-variation-id-for-${productId}`);
        const addToOrderBtn = productItemDiv.querySelector('.add-to-order-btn-affiliate');
        const quantityInputEl = productItemDiv.querySelector('.product-quantity-input-affiliate');
        const basePriceDisplaySpan = productItemDiv.querySelector(`.product-base-price-display-${productId}`); 

        if (!variationInfoDiv || !hiddenVariationIdInput || !addToOrderBtn || !quantityInputEl || !basePriceDisplaySpan) {
            console.error(`UPDATE_VAR_INFO: One or more UI elements missing for product ${productId}`);
            return;
        }

        const selectorsGroup = productItemDiv.querySelector(`.variation-selectors-group-affiliate`);
        let selectedAttributes = {}; 
        let allOptionsSelected = true;
        let hasSelectors = false;

        if (selectorsGroup) {
            const selects = selectorsGroup.querySelectorAll('.variation-attribute-select-affiliate');
            hasSelectors = selects.length > 0;
            if (selects.length === 0) { 
                const variations = productVariationsMap[String(productId)] || [];
                if (variations.length === 1 && (!variations[0].attributes || variations[0].attributes.length === 0)) {
                     hiddenVariationIdInput.value = variations[0].id; 
                     displaySelectedVariationDetails(productId, variations[0], variationInfoDiv, hiddenVariationIdInput, addToOrderBtn, quantityInputEl, basePriceDisplaySpan);
                     return;
                }
                allOptionsSelected = false; 
            }
            
            selects.forEach(select => {
                if (select.value) {
                    selectedAttributes[String(select.dataset.attributeId)] = String(select.value);
                } else {
                    allOptionsSelected = false;
                }
            });
        } else { 
            const product = getProductById(productId);
            if (product && product.product_type === 'simple') {
                addToOrderBtn.disabled = !(parseInt(product.stock_quantity) > 0);
                if(product.price !== null) basePriceDisplaySpan.innerHTML = `قیمت پایه: ${parseFloat(product.price || 0).toLocaleString('fa-IR')} ت (موجودی: ${product.stock_quantity})`;
            }
            const variations = productVariationsMap[String(productId)] || [];
            if (product && product.product_type === 'variable' && variations.length === 1 && (!variations[0].attributes || variations[0].attributes.length === 0)) {
                hiddenVariationIdInput.value = variations[0].id;
                displaySelectedVariationDetails(productId, variations[0], variationInfoDiv, hiddenVariationIdInput, addToOrderBtn, quantityInputEl, basePriceDisplaySpan);
            }
            return; 
        }

        if (!allOptionsSelected) { 
            variationInfoDiv.innerHTML = (hasSelectors) ? '<span class="text-warning">لطفاً تمام گزینه‌های تنوع را انتخاب کنید.</span>' : '';
            hiddenVariationIdInput.value = '';
            addToOrderBtn.disabled = true;
            const product = getProductById(productId);
            if(product && product.price !== null) basePriceDisplaySpan.innerHTML = `قیمت پایه: ${parseFloat(product.price || 0).toLocaleString('fa-IR')} ت`;
            return;
        }
        
        const matchedVariation = findMatchingVariation(productId, selectedAttributes);
        console.log(`UPDATE_VAR_INFO: Product ${productId}, Selected Attrs:`, selectedAttributes, "Matched Variation:", matchedVariation);
        displaySelectedVariationDetails(productId, matchedVariation, variationInfoDiv, hiddenVariationIdInput, addToOrderBtn, quantityInputEl, basePriceDisplaySpan);
    }

    function findMatchingVariation(productId, selectedAttributes) {
        const variations = productVariationsMap[String(productId)] || [];
        const selectedCount = Object.keys(selectedAttributes).length;

        if (selectedCount === 0 && variations.length === 1 && (!variations[0].attributes || variations[0].attributes.length === 0)) {
            return variations[0]; 
        }
        return variations.find(variation => {
            if (!variation.attributes || !Array.isArray(variation.attributes) || variation.attributes.length !== selectedCount) {
                return false; 
            }
            return Object.entries(selectedAttributes).every(([selectedAttrId, selectedValId]) => {
                return variation.attributes.some(varAttr => 
                    String(varAttr.attribute_id) === String(selectedAttrId) && 
                    String(varAttr.attribute_value_id) === String(selectedValId)
                );
            });
        });
    }

    function displaySelectedVariationDetails(productId, variation, infoDiv, hiddenIdInput, addButton, qtyInput, basePriceSpan) {
        const product = getProductById(productId); 
        if (variation && parseInt(variation.is_active) === 1) {
            infoDiv.innerHTML = `قیمت: <strong style="user-select:all;">${parseFloat(variation.price || 0).toLocaleString('fa-IR')} ت</strong> - موجودی: ${variation.stock_quantity}`;
            hiddenIdInput.value = variation.id;
            
            if (parseInt(variation.stock_quantity) > 0) {
                addButton.disabled = false;
                qtyInput.max = variation.stock_quantity;
            } else {
                infoDiv.innerHTML += ' <strong class="text-danger">(اتمام موجودی)</strong>';
                addButton.disabled = true;
            }
        } else {
            const productItemDiv = document.querySelector(`.product-item-selectable-affiliate[data-product-id-render="${productId}"]`);
            const hasSelectors = productItemDiv ? productItemDiv.querySelectorAll('.variation-attribute-select-affiliate').length > 0 : false;
            infoDiv.innerHTML = variation ? '<span class="text-danger">این تنوع فعال نیست یا موجود نیست.</span>' : (hasSelectors ? '<span class="text-danger">ترکیب انتخاب شده موجود نیست.</span>' : '');
            hiddenIdInput.value = '';
            addButton.disabled = true;
            if(product && product.price !== null) basePriceSpan.innerHTML = `قیمت پایه: ${parseFloat(product.price || 0).toLocaleString('fa-IR')} ت`;
        }
    }
    
    function calculateItemAffiliateCommission(itemPrice, itemQuantity, productData) {
        let commission = 0;
        if (!productData) return 0;

        const subtotal = parseFloat(itemPrice) * parseInt(itemQuantity);
        const commType = productData.affiliate_commission_type;
        const commValue = parseFloat(productData.affiliate_commission_value || 0);

        if (commType && commType !== 'none' && commValue > 0) {
            if (commType === 'percentage') {
                commission = roundToTwo(subtotal * (commValue / 100));
            } else if (commType === 'fixed_amount') {
                commission = roundToTwo(commValue * parseInt(itemQuantity));
            }
        }
        return commission;
    }

    function addToOrderHandler(event) {
        const button = event.currentTarget;
        const productId = button.dataset.productId;
        const productItemDiv = document.querySelector(`.product-item-selectable-affiliate[data-product-id-render="${productId}"]`);
        const quantityInput = productItemDiv.querySelector('.product-quantity-input-affiliate');
        const quantity = parseInt(quantityInput.value);

        const product = getProductById(productId);
        if (!product) { alert('محصول یافت نشد.'); return; }

        let itemToAdd = {
            product_id: product.id,
            name: product.name,
            quantity: quantity,
            price: parseFloat(product.price || 0), 
            variation_id: null,
            variation_name: '',
            affiliate_commission_type: product.affiliate_commission_type || 'none',
            affiliate_commission_value: parseFloat(product.affiliate_commission_value || 0)
        };

        if (product.product_type === 'variable') {
            const hiddenVarIdInput = productItemDiv.querySelector(`.selected-variation-id-for-${productId}`);
            const variationId = hiddenVarIdInput ? hiddenVarIdInput.value : null;
            if (!variationId) {
                alert('لطفاً ابتدا یک تنوع معتبر برای محصول انتخاب کنید.');
                return;
            }
            const variation = getVariationById(productId, variationId);
            if (!variation || parseInt(variation.is_active) !== 1 || parseInt(variation.stock_quantity) < quantity) {
                alert('تنوع انتخاب شده معتبر نیست یا موجودی (' + (variation ? variation.stock_quantity : 'N/A') + ') کافی ندارد.');
                return;
            }
            itemToAdd.variation_id = variation.id;
            itemToAdd.price = parseFloat(variation.price || 0);
            itemToAdd.variation_name = variation.attributes ? variation.attributes.map(a => htmlspecialchars(a.attribute_value)).join(' / ') : '';
            itemToAdd.affiliate_commission_type = variation.parent_affiliate_commission_type || product.affiliate_commission_type || 'none';
            itemToAdd.affiliate_commission_value = parseFloat(variation.parent_affiliate_commission_value || product.affiliate_commission_value || 0);
        } else { 
             if (parseInt(product.stock_quantity) < quantity) {
                alert('موجودی محصول ساده (' + product.stock_quantity + ') کافی نیست.');
                return;
            }
        }
        
        const existingItemIndex = currentOrderItems.findIndex(item => 
            String(item.product_id) === String(itemToAdd.product_id) && 
            (item.variation_id ? String(item.variation_id) : null) === (itemToAdd.variation_id ? String(itemToAdd.variation_id) : null)
        );

        if (existingItemIndex > -1) {
            currentOrderItems[existingItemIndex].quantity += quantity;
        } else {
            currentOrderItems.push(itemToAdd);
        }
        
        renderCurrentOrderList();
        quantityInput.value = 1; 
        if (product.product_type === 'variable') { 
            const varSelectors = productItemDiv.querySelectorAll('.variation-attribute-select-affiliate');
            varSelectors.forEach(sel => sel.selectedIndex = 0); 
            updateVariationInfoForProduct(productId); 
        }
    }

    function renderCurrentOrderList() {
        if (!currentOrderItemsDisplay) return;
        let html = '';
        let totalPrice = 0;
        let totalAffiliateCommission = 0;

        if (currentOrderItems.length === 0) {
            if(placeholderTextOrderItems) placeholderTextOrderItems.style.display = 'block';
            currentOrderItemsDisplay.innerHTML = ''; 
        } else {
            if(placeholderTextOrderItems) placeholderTextOrderItems.style.display = 'none';
            html = '<ul class="list-group list-group-flush">';
            currentOrderItems.forEach((item, index) => {
                const itemSubtotal = item.price * item.quantity;
                totalPrice += itemSubtotal;
                
                const productDataForCommission = { 
                    affiliate_commission_type: item.affiliate_commission_type,
                    affiliate_commission_value: item.affiliate_commission_value
                };
                const itemCommission = calculateItemAffiliateCommission(item.price, item.quantity, productDataForCommission);
                totalAffiliateCommission += itemCommission;

                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-2" style="font-size:0.85rem;">
                        <div>
                            <strong>${htmlspecialchars(item.name)}</strong> 
                            ${item.variation_name ? '<small class="text-muted">(' + htmlspecialchars(item.variation_name) + ')</small>' : ''}
                            <br>
                            <small class="text-muted">${item.quantity} عدد × ${item.price.toLocaleString('fa-IR')} ت = ${itemSubtotal.toLocaleString('fa-IR')} ت</small>
                        </div>
                        <button type="button" class="button-link button-danger btn-sm remove-from-order-btn-affiliate" data-index="${index}" style="padding: 0.1rem 0.4rem; font-size:0.75rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </li>
                `;
            });
            html += '</ul>';
            currentOrderItemsDisplay.innerHTML = html;
        }
        
        if(totalOrderPriceDisplay) totalOrderPriceDisplay.textContent = totalPrice.toLocaleString('fa-IR');
        if(affiliateCommissionDisplay) affiliateCommissionDisplay.textContent = totalAffiliateCommission.toLocaleString('fa-IR');
        const netPayable = totalPrice - totalAffiliateCommission;
        if(netPayableDisplay) netPayableDisplay.textContent = netPayable.toLocaleString('fa-IR');
        if(netPayableForBalanceCheckDisplay) netPayableForBalanceCheckDisplay.textContent = netPayable.toLocaleString('fa-IR');
        
        if (payFromBalanceRadio && payFromBalanceHelperText) {
            const affiliateUserBalanceElement = document.querySelector('div.card-body p strong.text-success'); 
            const affiliateCurrentBalance = affiliateUserBalanceElement ? parseFloat(affiliateUserBalanceElement.textContent.replace(/[^0-9.-]+/g,"")) : 0;
            
            if (netPayable > 0 && affiliateCurrentBalance >= netPayable && currentOrderItems.length > 0) {
                payFromBalanceRadio.disabled = false;
                payFromBalanceHelperText.textContent = '(مبلغ سفارش پس از کسر کمیسیون شما، از موجودی کسر خواهد شد)';
            } else if (netPayable <= 0 && currentOrderItems.length > 0) { 
                 payFromBalanceRadio.disabled = false; 
                 payFromBalanceHelperText.textContent = '(کمیسیون شما بیشتر یا مساوی مبلغ سفارش است. سفارش رایگان خواهد بود.)';
            }
            else {
                payFromBalanceRadio.disabled = true;
                if (payFromBalanceRadio.checked) { 
                    const customerPaysRadio = document.getElementById('customer_pays_radio');
                    if(customerPaysRadio) customerPaysRadio.checked = true; 
                }
                payFromBalanceHelperText.textContent = affiliateCurrentBalance <= 0 ? '(موجودی شما صفر یا منفی است)' : '(موجودی شما برای پرداخت این سفارش کافی نیست)';
            }
        }

        if(orderItemsJsonInput) orderItemsJsonInput.value = JSON.stringify(currentOrderItems.map(item => ({ 
            product_id: item.product_id,
            variation_id: item.variation_id,
            name: item.name, 
            variation_name: item.variation_name, 
            quantity: item.quantity,
            price: item.price 
        })));
        
        attachRemoveFromOrderListeners();
    }

    function attachRemoveFromOrderListeners() {
        document.querySelectorAll('.remove-from-order-btn-affiliate').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            newButton.addEventListener('click', function() {
                const indexToRemove = parseInt(this.dataset.index);
                currentOrderItems.splice(indexToRemove, 1);
                renderCurrentOrderList();
            });
        });
    }
    
    function initializeProductItems() {
        document.querySelectorAll('.product-item-selectable-affiliate').forEach(productItemDiv => {
            const productId = productItemDiv.dataset.productIdRender;
            if (!productId) return; 

            const product = getProductById(productId);
            if (!product) return;

            const addToOrderBtn = productItemDiv.querySelector('.add-to-order-btn-affiliate');
            if (addToOrderBtn) {
                const newBtn = addToOrderBtn.cloneNode(true);
                addToOrderBtn.parentNode.replaceChild(newBtn, addToOrderBtn);
                newBtn.addEventListener('click', addToOrderHandler);
            }

            if (product.product_type === 'variable') {
                const selectorsContainer = productItemDiv.querySelector(`.variation-selectors-container-for-${productId}`);
                if (selectorsContainer && selectorsContainer.innerHTML.trim() === '') { 
                    selectorsContainer.innerHTML = renderVariationSelectorsForProduct(productId);
                }

                const varSelects = productItemDiv.querySelectorAll('.variation-attribute-select-affiliate');
                varSelects.forEach(select => {
                    const newSelect = select.cloneNode(true);
                    select.parentNode.replaceChild(newSelect, select);
                    newSelect.addEventListener('change', function() {
                        updateVariationInfoForProduct(productId);
                    });
                });
                updateVariationInfoForProduct(productId); 
            } else { 
                 if (addToOrderBtn) addToOrderBtn.disabled = !(parseInt(product.stock_quantity) > 0);
                 const basePriceDisplay = productItemDiv.querySelector(`.product-base-price-display-${productId}`);
                 if(basePriceDisplay && product.price !== null) basePriceDisplay.innerHTML = `قیمت پایه: ${parseFloat(product.price || 0).toLocaleString('fa-IR')} ت (موجودی: ${product.stock_quantity})`;
            }
        });
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            renderProducts(this.value);
        });
    }
    renderProducts(); 
    renderCurrentOrderList(); 

});
</script>

<?php
// require_once APPROOT . '/views/layouts/footer_affiliate.php';
?>
