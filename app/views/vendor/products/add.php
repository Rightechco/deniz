<?php // ویو: app/views/vendor/products/add.php 
// require_once APPROOT . '/views/layouts/header_vendor.php'; // یا هدر مربوط به پنل فروشنده
?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'افزودن محصول جدید'); ?></h1>
        <a href="<?php echo BASE_URL; ?>vendor/myProducts" class="button-link btn-sm">بازگشت به محصولات من</a>
    </div>

    <?php 
    flash('product_action_fail'); 
    flash('product_form_error'); 
    flash('file_upload_error'); 
    ?>

    <form action="<?php echo BASE_URL; ?>vendor/addProduct" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">اطلاعات اصلی محصول</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name_vendor_add" class="form-label">نام محصول: <sup class="text-danger">*</sup></label>
                            <input type="text" name="name" id="name_vendor_add" class="form-control <?php echo !empty($data['name_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : ''); ?>" required>
                            <div class="invalid-feedback"><?php echo isset($data['name_err']) ? $data['name_err'] : 'لطفا نام محصول را وارد کنید.'; ?></div>
                        </div>

                        <div class="mb-3">
                            <label for="description_vendor_add" class="form-label">توضیحات محصول:</label>
                            <textarea name="description" id="description_vendor_add" rows="5" class="form-control"><?php echo htmlspecialchars(isset($data['description']) ? $data['description'] : ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="product_type_vendor_add" class="form-label">نوع محصول: <sup class="text-danger">*</sup></label>
                            <select name="product_type" id="product_type_vendor_add" class="form-select <?php echo !empty($data['product_type_err']) ? 'is-invalid' : ''; ?>" onchange="toggleProductFieldsVendorAdd()">
                                <option value="simple" <?php echo (isset($data['product_type']) && $data['product_type'] == 'simple') ? 'selected' : ''; ?>>ساده</option>
                                <option value="variable" <?php echo (isset($data['product_type']) && $data['product_type'] == 'variable') ? 'selected' : ''; ?>>متغیر</option>
                            </select>
                            <div class="invalid-feedback"><?php echo isset($data['product_type_err']) ? $data['product_type_err'] : ''; ?></div>
                        </div>

                        <div id="simpleProductFieldsVendorAdd">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price_vendor_add" class="form-label">قیمت (تومان): <span id="price_required_star_vendor_add" class="text-danger">*</span></label>
                                    <input type="number" step="any" name="price" id="price_vendor_add" class="form-control <?php echo !empty($data['price_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(isset($data['price']) ? $data['price'] : ''); ?>">
                                    <div class="invalid-feedback"><?php echo isset($data['price_err']) ? $data['price_err'] : 'قیمت برای محصول ساده الزامی است.'; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock_quantity_vendor_add" class="form-label">تعداد موجودی: <span id="stock_required_star_vendor_add" class="text-danger">*</span></label>
                                    <input type="number" name="stock_quantity" id="stock_quantity_vendor_add" class="form-control <?php echo !empty($data['stock_quantity_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(isset($data['stock_quantity']) ? $data['stock_quantity'] : '0'); ?>">
                                    <div class="invalid-feedback"><?php echo isset($data['stock_quantity_err']) ? $data['stock_quantity_err'] : 'موجودی برای محصول ساده الزامی است.'; ?></div>
                                </div>
                            </div>
                        </div>
                         <div class="mb-3">
                            <label for="initial_stock_quantity_vendor_add" class="form-label">موجودی اولیه (برای ردیابی فروش):</label>
                            <input type="number" name="initial_stock_quantity" id="initial_stock_quantity_vendor_add" class="form-control" value="<?php echo htmlspecialchars(isset($data['initial_stock_quantity']) ? $data['initial_stock_quantity'] : (isset($data['stock_quantity']) ? $data['stock_quantity'] : '0')); ?>">
                            <small class="form-text text-muted">اگر خالی بگذارید، برابر با تعداد موجودی فعلی در نظر گرفته می‌شود.</small>
                        </div>

                        <div id="variableProductFieldsVendorAdd" style="display:none; border: 1px solid #007bff; padding:15px; margin-top:15px; border-radius:5px;">
                            <h4>ویژگی‌های قابل تنظیم برای محصول متغیر</h4>
                            <p><small>ویژگی‌هایی را انتخاب کنید که برای ایجاد تنوع‌های این محصول استفاده خواهند شد (مانند رنگ، سایز). پس از ذخیره، می‌توانید تنوع‌ها را مدیریت کنید.</small></p>
                            <?php if (isset($data['all_attributes']) && !empty($data['all_attributes'])): ?>
                                <?php foreach($data['all_attributes'] as $attribute): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="configurable_attributes[]" id="attr_cfg_vendor_add_<?php echo $attribute['id']; ?>" value="<?php echo $attribute['id']; ?>"
                                               <?php echo (isset($data['configurable_attributes_for_product']) && is_array($data['configurable_attributes_for_product']) && in_array($attribute['id'], $data['configurable_attributes_for_product'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="attr_cfg_vendor_add_<?php echo $attribute['id']; ?>"><?php echo htmlspecialchars($attribute['name']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">ابتدا باید ویژگی‌هایی (مانند رنگ، سایز) توسط ادمین در بخش "مدیریت ویژگی‌ها" تعریف شود.</p>
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
                            <label for="product_image_vendor_add" class="form-label">تصویر اصلی محصول:</label>
                            <input type="file" name="product_image" id="product_image_vendor_add" class="form-control <?php echo !empty($data['image_err']) ? 'is-invalid' : ''; ?>" accept="image/jpeg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">فرمت‌های مجاز: JPG, JPEG, PNG, GIF, WEBP. حداکثر حجم: 5MB.</small>
                            <div class="invalid-feedback"><?php echo isset($data['image_err']) ? $data['image_err'] : ''; ?></div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="gallery_images_new_vendor_add" class="form-label">تصاویر گالری (می‌توانید چند تصویر انتخاب کنید):</label>
                            <input type="file" name="gallery_images_new[]" id="gallery_images_new_vendor_add" class="form-control" multiple accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewGalleryImagesVendorAdd(event)">
                            <small class="form-text text-muted">برای انتخاب چند تصویر، کلید Ctrl (یا Cmd در مک) را نگه دارید.</small>
                        </div>
                        <div id="gallery_previews_and_alts_vendor_add" class="mt-3 row">
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
                            <label for="category_id_vendor_add" class="form-label">دسته‌بندی:</label>
                            <select name="category_id" id="category_id_vendor_add" class="form-select">
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
                            <label for="status_vendor_add" class="form-label">وضعیت محصول:</label>
                            <select name="status" id="status_vendor_add" class="form-select">
                                <option value="active" <?php echo (isset($data['status']) && $data['status'] == 'active') ? 'selected' : ''; ?>>فعال (پس از تایید ادمین)</option>
                                <option value="draft" <?php echo (!isset($data['status']) || $data['status'] == 'draft') ? 'selected' : 'selected'; ?>>پیش‌نویس</option>
                                 <option value="pending_review" <?php echo (isset($data['status']) && $data['status'] == 'pending_review') ? 'selected' : ''; ?>>در انتظار بازبینی</option>
                            </select>
                             <small class="form-text text-muted">محصولات جدید معمولاً نیاز به تایید ادمین دارند.</small>
                        </div>
                    </div>
                </div>
                 </div>
        </div>

        <div class="mt-3 mb-4">
            <button type="submit" name="add_product_submit" class="button-link button-primary btn-lg">افزودن محصول</button>
            <a href="<?php echo BASE_URL; ?>vendor/myProducts" class="button-link button-secondary btn-lg" style="margin-right: 10px;">انصراف</a>
        </div>
    </form>
</div>

<script>
    function toggleProductFieldsVendorAdd() {
        var productType = document.getElementById('product_type_vendor_add').value;
        var simpleFields = document.getElementById('simpleProductFieldsVendorAdd');
        var variableFields = document.getElementById('variableProductFieldsVendorAdd');
        var priceInput = document.getElementById('price_vendor_add');
        var stockInput = document.getElementById('stock_quantity_vendor_add');
        var priceRequiredStar = document.getElementById('price_required_star_vendor_add');
        var stockRequiredStar = document.getElementById('stock_required_star_vendor_add');

        if (productType === 'variable') {
            if(simpleFields) simpleFields.style.display = 'block'; 
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

    function previewGalleryImagesVendorAdd(event) {
        var previewContainer = document.getElementById('gallery_previews_and_alts_vendor_add');
        previewContainer.innerHTML = ''; 
        var files = event.target.files;

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (!file.type.startsWith('image/')){ continue } 

            var reader = new FileReader();
            reader.onload = (function(f, index) { 
                return function(e) {
                    var colDiv = document.createElement('div');
                    colDiv.classList.add('col-md-3', 'col-sm-4', 'col-6', 'mb-3');

                    var cardDiv = document.createElement('div');
                    cardDiv.classList.add('card', 'h-100');
                    
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('card-img-top');
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    
                    var cardBody = document.createElement('div');
                    cardBody.classList.add('card-body', 'p-2');

                    var altLabel = document.createElement('label');
                    altLabel.htmlFor = 'gallery_alt_new_vendor_add_' + index;
                    altLabel.textContent = 'Alt Text تصویر ' + (index + 1) + ':';
                    altLabel.classList.add('form-label', 'form-label-sm', 'mb-1');

                    var altInput = document.createElement('input');
                    altInput.type = 'text';
                    altInput.name = 'gallery_alt_texts_new[' + index + ']'; 
                    altInput.id = 'gallery_alt_new_vendor_add_' + index;
                    altInput.classList.add('form-control', 'form-control-sm');
                    altInput.placeholder = f.name; 

                    cardBody.appendChild(altLabel);
                    cardBody.appendChild(altInput);
                    cardDiv.appendChild(img);
                    cardDiv.appendChild(cardBody);
                    colDiv.appendChild(cardDiv);
                    previewContainer.appendChild(colDiv);
                };
            })(file, i);
            reader.readAsDataURL(file);
        }
    }
    document.addEventListener('DOMContentLoaded', toggleProductFieldsVendorAdd);
</script>
<style>
    /* استایل‌های پایه مشابه فرم ادمین */
    .error-text { color: red; font-size: 0.85em; }
    .required_star { color: red; }
    .form-label sup { color: red; }
    .form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; font-weight: 400; line-height: 1.5; color: #212529; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; appearance: none; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
    .form-select { display: block; width: 100%; padding: .375rem 2.25rem .375rem .75rem; font-size: 1rem; font-weight: 400; line-height: 1.5; color: #212529; background-color: #fff; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right .75rem center; background-size: 16px 12px; border: 1px solid #ced4da; border-radius: .25rem; appearance: none; }
    .card { position: relative; display: flex; flex-direction: column; min-width: 0; word-wrap: break-word; background-color: #fff; background-clip: border-box; border: 1px solid rgba(0,0,0,.125); border-radius: .25rem; }
    .card-header { padding: .5rem 1rem; margin-bottom: 0; background-color: rgba(0,0,0,.03); border-bottom: 1px solid rgba(0,0,0,.125); }
    .card-body { flex: 1 1 auto; padding: 1rem 1rem; }
    .shadow-sm { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important; }
    .mb-3 { margin-bottom: 1rem !important; } .mt-3 { margin-top: 1rem !important; }
    .row { display: flex; flex-wrap: wrap; margin-top: calc(-1 * var(--bs-gutter-y)); margin-right: calc(-.5 * var(--bs-gutter-x)); margin-left: calc(-.5 * var(--bs-gutter-x)); }
    .row > * { flex-shrink: 0; width: 100%; max-width: 100%; padding-right: calc(var(--bs-gutter-x) * .5); padding-left: calc(var(--bs-gutter-x) * .5); margin-top: var(--bs-gutter-y); }
    .col-md-8 { flex: 0 0 auto; width: 66.66666667%; } .col-md-4 { flex: 0 0 auto; width: 33.33333333%; } .col-md-6 { flex: 0 0 auto; width: 50%; }
    .col-sm-4 { flex: 0 0 auto; width: 33.33333333%; } .col-6 { flex: 0 0 auto; width: 50%; }
     :root { --bs-gutter-x: 1.5rem; --bs-gutter-y: 0; }
    .text-danger { color: red !important; }
    .invalid-feedback { display: none; width: 100%; margin-top: .25rem; font-size: .875em; color: #dc3545; }
    .is-invalid ~ .invalid-feedback { display: block; }
    .form-control.is-invalid { border-color: #dc3545; }
</style>

<?php 
// require_once APPROOT . '/views/layouts/footer_vendor.php'; 
?>
