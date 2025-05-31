<?php // ویو: app/views/admin/products/add.php ?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'افزودن محصول جدید'); ?></h1>
        <a href="<?php echo BASE_URL; ?>admin/products" class="button-link btn-sm">بازگشت به لیست محصولات</a>
    </div>

    <?php 
    flash('product_action_fail'); 
    flash('product_form_error'); 
    flash('file_upload_error'); 
    ?>

    <form action="<?php echo BASE_URL; ?>admin/addProduct" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">اطلاعات اصلی محصول</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">نام محصول: <sup class="text-danger">*</sup></label>
                            <input type="text" name="name" id="name" class="form-control <?php echo !empty($data['name_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : ''); ?>" required>
                            <div class="invalid-feedback"><?php echo isset($data['name_err']) ? $data['name_err'] : ''; ?></div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">توضیحات محصول:</label>
                            <textarea name="description" id="description" rows="5" class="form-control"><?php echo htmlspecialchars(isset($data['description']) ? $data['description'] : ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="product_type" class="form-label">نوع محصول: <sup class="text-danger">*</sup></label>
                            <select name="product_type" id="product_type_admin_add" class="form-select <?php echo !empty($data['product_type_err']) ? 'is-invalid' : ''; ?>" onchange="toggleProductFieldsAdminAdd()">
                                <option value="simple" <?php echo (isset($data['product_type']) && $data['product_type'] == 'simple') ? 'selected' : ''; ?>>ساده</option>
                                <option value="variable" <?php echo (isset($data['product_type']) && $data['product_type'] == 'variable') ? 'selected' : ''; ?>>متغیر</option>
                            </select>
                            <div class="invalid-feedback"><?php echo isset($data['product_type_err']) ? $data['product_type_err'] : ''; ?></div>
                        </div>

                        <div id="simpleProductFieldsAdminAdd">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price_admin_add" class="form-label">قیمت (تومان): <span id="price_required_star_admin_add" class="text-danger">*</span></label>
                                    <input type="number" step="any" name="price" id="price_admin_add" class="form-control <?php echo !empty($data['price_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(isset($data['price']) ? $data['price'] : ''); ?>">
                                    <div class="invalid-feedback"><?php echo isset($data['price_err']) ? $data['price_err'] : ''; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock_quantity_admin_add" class="form-label">تعداد موجودی: <span id="stock_required_star_admin_add" class="text-danger">*</span></label>
                                    <input type="number" name="stock_quantity" id="stock_quantity_admin_add" class="form-control <?php echo !empty($data['stock_quantity_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(isset($data['stock_quantity']) ? $data['stock_quantity'] : '0'); ?>">
                                    <div class="invalid-feedback"><?php echo isset($data['stock_quantity_err']) ? $data['stock_quantity_err'] : ''; ?></div>
                                </div>
                            </div>
                        </div>
                         <div class="mb-3">
                            <label for="initial_stock_quantity_admin_add" class="form-label">موجودی اولیه (برای ردیابی فروش):</label>
                            <input type="number" name="initial_stock_quantity" id="initial_stock_quantity_admin_add" class="form-control" value="<?php echo htmlspecialchars(isset($data['initial_stock_quantity']) ? $data['initial_stock_quantity'] : (isset($data['stock_quantity']) ? $data['stock_quantity'] : '0')); ?>">
                            <small class="form-text text-muted">اگر خالی بگذارید، برابر با تعداد موجودی فعلی در نظر گرفته می‌شود.</small>
                        </div>


                        <div id="variableProductFieldsAdminAdd" style="display:none; border: 1px solid #007bff; padding:15px; margin-top:15px; border-radius:5px;">
                            <h4>ویژگی‌های قابل تنظیم برای محصول متغیر</h4>
                            <p><small>ویژگی‌هایی را انتخاب کنید که برای ایجاد تنوع‌های این محصول استفاده خواهند شد (مانند رنگ، سایز). پس از ذخیره، می‌توانید تنوع‌ها را مدیریت کنید.</small></p>
                            <?php if (isset($data['all_attributes']) && !empty($data['all_attributes'])): ?>
                                <?php foreach($data['all_attributes'] as $attribute): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="configurable_attributes[]" id="attr_cfg_<?php echo $attribute['id']; ?>" value="<?php echo $attribute['id']; ?>"
                                               <?php echo (isset($data['configurable_attributes_for_product']) && is_array($data['configurable_attributes_for_product']) && in_array($attribute['id'], $data['configurable_attributes_for_product'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="attr_cfg_<?php echo $attribute['id']; ?>"><?php echo htmlspecialchars($attribute['name']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">ابتدا باید ویژگی‌هایی (مانند رنگ، سایز) را در بخش <a href="<?php echo BASE_URL; ?>admin/attributes">مدیریت ویژگی‌ها</a> تعریف کنید.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">تصاویر محصول</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="product_image" class="form-label">تصویر اصلی محصول:</label>
                            <input type="file" name="product_image" id="product_image" class="form-control <?php echo !empty($data['image_err']) ? 'is-invalid' : ''; ?>">
                            <small class="form-text text-muted">فرمت‌های مجاز: JPG, JPEG, PNG, GIF, WEBP. حداکثر حجم: 5MB.</small>
                            <div class="invalid-feedback"><?php echo isset($data['image_err']) ? $data['image_err'] : ''; ?></div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="gallery_images_new" class="form-label">تصاویر گالری (می‌توانید چند تصویر انتخاب کنید):</label>
                            <input type="file" name="gallery_images_new[]" id="gallery_images_new" class="form-control" multiple onchange="previewGalleryImages(event)">
                            <small class="form-text text-muted">برای انتخاب چند تصویر، کلید Ctrl (یا Cmd در مک) را نگه دارید.</small>
                        </div>
                        <div id="gallery_previews_and_alts" class="mt-3">
                            </div>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">سازماندهی</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">دسته‌بندی:</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">-- بدون دسته‌بندی --</option>
                                <?php if (isset($data['categories']) && !empty($data['categories'])): ?>
                                    <?php foreach($data['categories'] as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($data['category_id']) && $data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">فروشنده (اختیاری):</label>
                            <select name="vendor_id" id="vendor_id" class="form-select">
                                <option value="">-- فروشگاه اصلی --</option>
                                <?php if (isset($data['vendors']) && !empty($data['vendors'])): ?>
                                    <?php foreach($data['vendors'] as $vendor): ?>
                                        <option value="<?php echo $vendor['id']; ?>" <?php echo (isset($data['vendor_id']) && $data['vendor_id'] == $vendor['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($vendor['first_name'] . ' ' . $vendor['last_name'] . ' (' . $vendor['username'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">کمیسیون همکاری در فروش (اختیاری)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="affiliate_commission_type" class="form-label">نوع کمیسیون:</label>
                            <select name="affiliate_commission_type" id="affiliate_commission_type" class="form-select">
                                <option value="none" <?php echo (isset($data['affiliate_commission_type']) && $data['affiliate_commission_type'] == 'none') ? 'selected' : ''; ?>>بدون کمیسیون</option>
                                <option value="percentage" <?php echo (isset($data['affiliate_commission_type']) && $data['affiliate_commission_type'] == 'percentage') ? 'selected' : ''; ?>>درصدی (%)</option>
                                <option value="fixed_amount" <?php echo (isset($data['affiliate_commission_type']) && $data['affiliate_commission_type'] == 'fixed_amount') ? 'selected' : ''; ?>>مقدار ثابت (تومان)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="affiliate_commission_value" class="form-label">مقدار کمیسیون:</label>
                            <input type="number" step="any" name="affiliate_commission_value" id="affiliate_commission_value" class="form-control <?php echo !empty($data['affiliate_commission_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(isset($data['affiliate_commission_value']) ? $data['affiliate_commission_value'] : ''); ?>">
                            <small class="form-text text-muted">اگر نوع درصدی است، عددی بین ۰ تا ۱۰۰. اگر مقدار ثابت است، مبلغ به تومان.</small>
                            <div class="invalid-feedback"><?php echo isset($data['affiliate_commission_err']) ? $data['affiliate_commission_err'] : ''; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 mb-4">
            <button type="submit" name="add_product_submit" class="button-link button-primary btn-lg">افزودن محصول</button>
            <a href="<?php echo BASE_URL; ?>admin/products" class="button-link button-secondary btn-lg" style="margin-right: 10px;">انصراف</a>
        </div>
    </form>
</div>

<script>
    function toggleProductFieldsAdminAdd() {
        var productType = document.getElementById('product_type_admin_add').value;
        var simpleFields = document.getElementById('simpleProductFieldsAdminAdd');
        var variableFields = document.getElementById('variableProductFieldsAdminAdd');
        var priceInput = document.getElementById('price_admin_add');
        var stockInput = document.getElementById('stock_quantity_admin_add');
        var priceRequiredStar = document.getElementById('price_required_star_admin_add');
        var stockRequiredStar = document.getElementById('stock_required_star_admin_add');

        if (productType === 'variable') {
            // For variable product, parent price and stock are optional or can be hidden
            if(simpleFields) simpleFields.style.display = 'block'; // Keep them visible but not required
            if(variableFields) variableFields.style.display = 'block';
            if(priceInput) priceInput.required = false; 
            if(stockInput) stockInput.required = false; 
            if(priceRequiredStar) priceRequiredStar.style.display = 'none';
            if(stockRequiredStar) stockRequiredStar.style.display = 'none';
        } else { // simple
            if(simpleFields) simpleFields.style.display = 'block';
            if(variableFields) variableFields.style.display = 'none';
            if(priceInput) priceInput.required = true;
            if(stockInput) stockInput.required = true;
            if(priceRequiredStar) priceRequiredStar.style.display = 'inline';
            if(stockRequiredStar) stockRequiredStar.style.display = 'inline';
        }
    }

    function previewGalleryImages(event) {
        var previewContainer = document.getElementById('gallery_previews_and_alts');
        previewContainer.innerHTML = ''; // Clear previous previews
        var files = event.target.files;

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (!file.type.startsWith('image/')){ continue } // Skip non-images

            var reader = new FileReader();
            reader.onload = (function(f, index) { // Capture file and index in closure
                return function(e) {
                    var div = document.createElement('div');
                    div.classList.add('mb-3', 'p-2', 'border', 'rounded');
                    div.style.display = 'inline-block';
                    div.style.marginRight = '10px';
                    div.style.position = 'relative';


                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.style.display = 'block';
                    img.style.marginBottom = '5px';
                    
                    var altLabel = document.createElement('label');
                    altLabel.htmlFor = 'gallery_alt_new_' + index;
                    altLabel.textContent = 'متن جایگزین تصویر ' + (index + 1) + ':';
                    altLabel.style.fontSize = '0.8em';
                    altLabel.style.display = 'block';

                    var altInput = document.createElement('input');
                    altInput.type = 'text';
                    altInput.name = 'gallery_alt_texts_new[' + index + ']'; // Array for alt texts
                    altInput.id = 'gallery_alt_new_' + index;
                    altInput.classList.add('form-control', 'form-control-sm');
                    altInput.placeholder = f.name; // Default alt to filename

                    div.appendChild(img);
                    div.appendChild(altLabel);
                    div.appendChild(altInput);
                    previewContainer.appendChild(div);
                };
            })(file, i);
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', toggleProductFieldsAdminAdd);
</script>
<style>
    .error-text { color: red; font-size: 0.85em; }
    .required_star { color: red; }
    .form-label sup { color: red; }
</style>
