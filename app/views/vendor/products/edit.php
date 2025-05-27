<?php // ویو: app/views/vendor/products/edit.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'ویرایش محصول'); ?></h1>

<?php 
flash('product_action_fail'); 
flash('product_form_error'); 
flash('file_upload_error');
flash('access_denied'); // اگر فروشنده سعی در ویرایش محصول دیگری داشته باشد
?>

<?php if (isset($data['product']) && $data['product']): // اطمینان از اینکه داده محصول وجود دارد ?>
    <form action="<?php echo BASE_URL; ?>vendor/editProduct/<?php echo $data['id']; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($data['id']) ? $data['id'] : ''); ?>">
        <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars(isset($data['image_url']) ? $data['image_url'] : ''); ?>">

        <div style="margin-bottom: 15px;">
            <label for="name">نام محصول: <sup>*</sup></label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : ''); ?>" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <span class="error-text"><?php echo isset($data['name_err']) ? $data['name_err'] : ''; ?></span>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="description">توضیحات محصول:</label>
            <textarea name="description" id="description" rows="5" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"><?php echo htmlspecialchars(isset($data['description']) ? $data['description'] : ''); ?></textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="product_type">نوع محصول: <sup>*</sup></label>
            <select name="product_type" id="product_type" onchange="toggleVendorProductFields()" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <option value="simple" <?php echo (isset($data['product_type']) && $data['product_type'] == 'simple') ? 'selected' : ''; ?>>ساده</option>
                <option value="variable" <?php echo (isset($data['product_type']) && $data['product_type'] == 'variable') ? 'selected' : ''; ?>>متغیر</option>
            </select>
            <span class="error-text"><?php echo isset($data['product_type_err']) ? $data['product_type_err'] : ''; ?></span>
        </div>

        <div id="vendorSimpleProductFields" style="border: 1px dashed #ccc; padding: 10px; margin-bottom:15px; border-radius:4px;">
            <p><strong><small>برای محصول ساده:</small></strong></p>
            <div style="margin-bottom: 15px;">
                <label for="price">قیمت (تومان): <span id="vendor_price_required_star" class="required_star">*</span></label>
                <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars(isset($data['price']) ? $data['price'] : ''); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <span class="error-text"><?php echo isset($data['price_err']) ? $data['price_err'] : ''; ?></span>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="stock_quantity">موجودی فعلی: <span id="vendor_stock_required_star" class="required_star">*</span></label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="<?php echo htmlspecialchars(isset($data['stock_quantity']) ? $data['stock_quantity'] : '0'); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <span class="error-text"><?php echo isset($data['stock_quantity_err']) ? $data['stock_quantity_err'] : ''; ?></span>
            </div>
             <div style="margin-bottom: 15px;">
                <label for="initial_stock_quantity_edit">موجودی اولیه (این مقدار پس از اولین ذخیره معمولاً تغییر نمی‌کند):</label>
                <input type="number" name="initial_stock_quantity_edit" id="initial_stock_quantity_edit" value="<?php echo htmlspecialchars(isset($data['initial_stock_quantity']) ? $data['initial_stock_quantity'] : '0'); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" readonly title="موجودی اولیه معمولاً پس از ایجاد محصول ثابت است. برای تغییر، با ادمین تماس بگیرید.">
                <small>این فیلد معمولاً توسط ادمین تنظیم و یا پس از ایجاد محصول ثابت می‌ماند.</small>
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="product_image">تغییر تصویر محصول (اختیاری):</label>
            <input type="file" name="product_image" id="product_image" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <small>اگر تصویری انتخاب نکنید، تصویر فعلی حفظ خواهد شد.</small>
            <span class="error-text"><?php echo isset($data['image_err']) ? $data['image_err'] : ''; ?></span>
            <?php if (!empty($data['image_url'])): ?>
                <div style="margin-top: 10px;">
                    <p>تصویر فعلی:</p>
                    <img src="<?php echo BASE_URL . htmlspecialchars($data['image_url']); ?>" alt="تصویر فعلی محصول" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius:4px;">
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="category_id">دسته‌بندی:</label>
            <select name="category_id" id="category_id" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <option value="">-- بدون دسته‌بندی --</option>
                <?php if (!empty($data['categories'])): ?>
                    <?php foreach($data['categories'] as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($data['category_id']) && $data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div id="vendorVariableProductFields" style="display:none; border: 1px dashed #007bff; padding:15px; margin-top:15px; border-radius:5px;">
            <h4>ویژگی‌های قابل تنظیم برای محصول متغیر</h4>
            <p><small>ویژگی‌هایی را انتخاب کنید که برای ایجاد تنوع‌های این محصول استفاده خواهند شد (مانند رنگ، سایز). این ویژگی‌ها توسط ادمین سایت تعریف شده‌اند.</small></p>
            <?php if (!empty($data['all_attributes'])): ?>
                <?php foreach($data['all_attributes'] as $attribute): ?>
                    <div style="margin-bottom: 5px;">
                        <input type="checkbox" name="configurable_attributes[]" id="attr_<?php echo $attribute['id']; ?>" value="<?php echo $attribute['id']; ?>"
                               <?php echo (!empty($data['configurable_attributes_for_product']) && in_array($attribute['id'], $data['configurable_attributes_for_product'])) ? 'checked' : ''; ?>>
                        <label for="attr_<?php echo $attribute['id']; ?>"><?php echo htmlspecialchars($attribute['name']); ?></label>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>هیچ ویژگی‌ای توسط ادمین سایت تعریف نشده است.</p>
            <?php endif; ?>
            <hr style="margin: 15px 0;">
            <p>
                <a href="<?php echo BASE_URL; ?>vendor/manageProductVariations/<?php echo $data['id']; ?>" class="button-link" style="background-color:#5cb85c;">مدیریت تنوع‌های محصول (قیمت، موجودی و...)</a>
            </p>
            <small>ابتدا محصول را با نوع "متغیر" و ویژگی‌های قابل تنظیم انتخاب شده ذخیره کنید، سپس برای مدیریت تنوع‌ها کلیک کنید.</small>
        </div>
        
        <!--
        <fieldset style="margin-top: 20px;">
            <legend>پورسانت همکاری در فروش</legend>
            <p><small>این بخش توسط مدیر سایت تنظیم می‌شود.</small></p>
            <p>نوع پورسانت فعلی: <?php // echo htmlspecialchars(isset($data['affiliate_commission_type']) ? $data['affiliate_commission_type'] : 'none'); ?></p>
            <p>مقدار پورسانت فعلی: <?php // echo htmlspecialchars(isset($data['affiliate_commission_value']) ? $data['affiliate_commission_value'] : ''); ?></p>
        </fieldset>
        -->

        <div style="margin-top: 30px;">
            <button type="submit" name="edit_product_submit" class="button-link button-warning">ذخیره تغییرات</button>
            <a href="<?php echo BASE_URL; ?>vendor/myProducts" class="button-link button-secondary" style="margin-left: 10px;">انصراف</a>
        </div>
    </form>
<?php else: ?>
    <p>اطلاعات محصول برای ویرایش یافت نشد.</p>
    <a href="<?php echo BASE_URL; ?>vendor/myProducts" class="button-link button-secondary">بازگشت به لیست محصولات من</a>
<?php endif; ?>

<script>
    function toggleVendorProductFields() {
        var productType = document.getElementById('product_type').value;
        var simpleFields = document.getElementById('vendorSimpleProductFields');
        var variableFields = document.getElementById('vendorVariableProductFields');
        var priceInput = document.getElementById('price');
        var stockInput = document.getElementById('stock_quantity');
        var priceRequiredStar = document.getElementById('vendor_price_required_star');
        var stockRequiredStar = document.getElementById('vendor_stock_required_star');

        if (productType === 'variable') {
            if(simpleFields) simpleFields.style.display = 'none';
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
    // اجرای تابع در هنگام بارگذاری صفحه برای تنظیم اولیه نمایش فیلدها
    document.addEventListener('DOMContentLoaded', toggleVendorProductFields);
</script>
<style>.required_star { color: red; }</style>
