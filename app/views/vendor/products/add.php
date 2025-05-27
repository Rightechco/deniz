<?php // ویو: app/views/vendor/products/add.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'افزودن محصول جدید'); ?></h1>

<?php 
flash('product_action_fail'); 
flash('product_form_error'); 
flash('file_upload_error'); 
?>

<form action="<?php echo BASE_URL; ?>vendor/addProduct" method="post" enctype="multipart/form-data">
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
        <select name="product_type" id="product_type_vendor_add" onchange="toggleVendorAddProductFields()" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <option value="simple" <?php echo (isset($data['product_type']) && $data['product_type'] == 'simple') ? 'selected' : ''; ?>>ساده</option>
            <option value="variable" <?php echo (isset($data['product_type']) && $data['product_type'] == 'variable') ? 'selected' : ''; ?>>متغیر</option>
        </select>
        <span class="error-text"><?php echo isset($data['product_type_err']) ? $data['product_type_err'] : ''; ?></span>
    </div>

    <div id="vendorSimpleProductFields_add" style="border: 1px dashed #ccc; padding: 10px; margin-bottom:15px; border-radius:4px;">
        <p><strong><small>برای محصول ساده (یا محصول والد متغیر اگر قیمت/موجودی کلی دارد):</small></strong></p>
        <div style="margin-bottom: 15px;">
            <label for="price_vendor_add">قیمت (تومان): <span id="vendor_price_required_star_add" class="required_star">*</span></label>
            <input type="number" step="0.01" name="price" id="price_vendor_add" value="<?php echo htmlspecialchars(isset($data['price']) ? $data['price'] : ''); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <span class="error-text"><?php echo isset($data['price_err']) ? $data['price_err'] : ''; ?></span>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="stock_quantity_vendor_add">موجودی (اولیه و فعلی): <span id="vendor_stock_required_star_add" class="required_star">*</span></label>
            <input type="number" name="stock_quantity" id="stock_quantity_vendor_add" value="<?php echo htmlspecialchars(isset($data['stock_quantity']) ? $data['stock_quantity'] : '0'); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <span class="error-text"><?php echo isset($data['stock_quantity_err']) ? $data['stock_quantity_err'] : ''; ?></span>
        </div>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="product_image_vendor_add">تصویر محصول:</label>
        <input type="file" name="product_image" id="product_image_vendor_add" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        <small>فرمت‌های مجاز: JPG, JPEG, PNG, GIF.</small>
        <span class="error-text"><?php echo isset($data['image_err']) ? $data['image_err'] : ''; ?></span>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="category_id_vendor_add">دسته‌بندی:</label>
        <select name="category_id" id="category_id_vendor_add" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
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

    <div id="vendorVariableProductFields_add" style="display:none; border: 1px dashed #007bff; padding:15px; margin-top:15px; border-radius:5px;">
        <h4>ویژگی‌های قابل تنظیم برای محصول متغیر</h4>
        <p><small>ویژگی‌هایی را انتخاب کنید که برای ایجاد تنوع‌های این محصول استفاده خواهند شد. این ویژگی‌ها توسط ادمین سایت تعریف شده‌اند.</small></p>
        <?php if (!empty($data['all_attributes'])): ?>
            <?php foreach($data['all_attributes'] as $attribute): ?>
                <div style="margin-bottom: 5px;">
                    <input type="checkbox" name="configurable_attributes[]" id="attr_vendor_add_<?php echo $attribute['id']; ?>" value="<?php echo $attribute['id']; ?>"
                           <?php echo (!empty($data['configurable_attributes_for_product']) && in_array($attribute['id'], $data['configurable_attributes_for_product'])) ? 'checked' : ''; ?>>
                    <label for="attr_vendor_add_<?php echo $attribute['id']; ?>"><?php echo htmlspecialchars($attribute['name']); ?></label>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>هیچ ویژگی‌ای توسط ادمین سایت تعریف نشده است.</p>
        <?php endif; ?>
        <p style="margin-top:15px;"><small>پس از ذخیره این محصول متغیر، به صفحه ویرایش آن بروید و سپس روی "مدیریت تنوع‌ها" کلیک کنید تا تنوع‌ها (مانند تیشرت قرمز-سایز M) را با قیمت و موجودی مجزا ایجاد نمایید.</small></p>
    </div>
    
    <div style="margin-top: 30px;">
        <button type="submit" name="add_product_submit" class="button-link" style="background-color:#28a745;">افزودن محصول</button>
        <a href="<?php echo BASE_URL; ?>vendor/myProducts" class="button-link button-secondary" style="margin-left: 10px;">انصراف</a>
    </div>
</form>

<script>
    function toggleVendorAddProductFields() {
        var productType = document.getElementById('product_type_vendor_add').value;
        var simpleFields = document.getElementById('vendorSimpleProductFields_add');
        var variableFields = document.getElementById('vendorVariableProductFields_add');
        var priceInput = document.getElementById('price_vendor_add');
        var stockInput = document.getElementById('stock_quantity_vendor_add');
        var priceRequiredStar = document.getElementById('vendor_price_required_star_add');
        var stockRequiredStar = document.getElementById('vendor_stock_required_star_add');

        if (productType === 'variable') {
            if(simpleFields) simpleFields.style.display = 'block'; // قیمت و موجودی والد برای متغیر هم می‌تواند وارد شود (اختیاری)
            if(variableFields) variableFields.style.display = 'block';
            if(priceInput) priceInput.required = false; // قیمت برای محصول والد متغیر اختیاری است
            if(stockInput) stockInput.required = false; // موجودی محصول والد متغیر اختیاری است (می‌تواند 0 باشد)
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
    document.addEventListener('DOMContentLoaded', toggleVendorAddProductFields);
</script>
<style>.required_star { color: red; }</style>
