    <?php // ویو: app/views/admin/products/edit.php ?>

    <h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>

    <?php flash('product_action_fail'); ?>
    <?php flash('product_form_error'); ?>
    <?php flash('file_upload_error'); ?>

    <form action="<?php echo BASE_URL; ?>admin/editProduct/<?php echo $data['id']; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
        <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars(isset($data['image_url']) ? $data['image_url'] : ''); ?>">

        <div>
            <label for="name">نام محصول: <sup>*</sup></label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : ''); ?>" required>
            <span class="error-text"><?php echo isset($data['name_err']) ? $data['name_err'] : ''; ?></span>
        </div>

        <div>
            <label for="description">توضیحات محصول:</label>
            <textarea name="description" id="description" rows="5"><?php echo htmlspecialchars(isset($data['description']) ? $data['description'] : ''); ?></textarea>
        </div>

        <div>
            <label for="product_type">نوع محصول: <sup>*</sup></label>
            <select name="product_type" id="product_type" onchange="toggleProductFields()">
                <option value="simple" <?php echo (isset($data['product_type']) && $data['product_type'] == 'simple') ? 'selected' : ''; ?>>ساده</option>
                <option value="variable" <?php echo (isset($data['product_type']) && $data['product_type'] == 'variable') ? 'selected' : ''; ?>>متغیر</option>
            </select>
            <span class="error-text"><?php echo isset($data['product_type_err']) ? $data['product_type_err'] : ''; ?></span>
        </div>

        <div id="simpleProductFields">
            <div>
                <label for="price">قیمت (تومان): <span id="price_required_star" class="required_star">*</span></label>
                <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars(isset($data['price']) ? $data['price'] : ''); ?>">
                <span class="error-text"><?php echo isset($data['price_err']) ? $data['price_err'] : ''; ?></span>
            </div>
            <div>
                <label for="stock_quantity">تعداد موجودی: <span id="stock_required_star" class="required_star">*</span></label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="<?php echo htmlspecialchars(isset($data['stock_quantity']) ? $data['stock_quantity'] : '0'); ?>">
                <span class="error-text"><?php echo isset($data['stock_quantity_err']) ? $data['stock_quantity_err'] : ''; ?></span>
            </div>
        </div>

        <div>
            <label for="product_image">تصویر محصول جدید (اختیاری):</label>
            <input type="file" name="product_image" id="product_image">
            <small>اگر تصویری انتخاب نکنید، تصویر فعلی حفظ خواهد شد.</small>
            <span class="error-text"><?php echo isset($data['image_err']) ? $data['image_err'] : ''; ?></span>
            <?php if (!empty($data['image_url'])): ?>
                <div style="margin-top: 10px;">
                    <p>تصویر فعلی:</p>
                    <img src="<?php echo BASE_URL . htmlspecialchars($data['image_url']); ?>" alt="تصویر فعلی محصول" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label for="category_id">دسته‌بندی:</label>
            <select name="category_id" id="category_id">
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

        <div id="variableProductFields" style="display:none; border: 1px solid #007bff; padding:15px; margin-top:15px; border-radius:5px;">
            <h4>ویژگی‌های قابل تنظیم برای محصول متغیر</h4>
            <p><small>ویژگی‌هایی را انتخاب کنید که برای ایجاد تنوع‌های این محصول استفاده خواهند شد (مانند رنگ، سایز).</small></p>
            <?php if (!empty($data['all_attributes'])): ?>
                <?php foreach($data['all_attributes'] as $attribute): ?>
                    <div style="margin-bottom: 5px;">
                        <input type="checkbox" name="configurable_attributes[]" id="attr_<?php echo $attribute['id']; ?>" value="<?php echo $attribute['id']; ?>"
                               <?php echo (!empty($data['configurable_attributes_for_product']) && in_array($attribute['id'], $data['configurable_attributes_for_product'])) ? 'checked' : ''; ?>>
                        <label for="attr_<?php echo $attribute['id']; ?>"><?php echo htmlspecialchars($attribute['name']); ?></label>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>ابتدا باید ویژگی‌هایی را در بخش "مدیریت ویژگی‌ها" تعریف کنید.</p>
            <?php endif; ?>
            <hr>
            <p><a href="<?php echo BASE_URL; ?>admin/manageProductVariations/<?php echo $data['id']; ?>" class="button-link">مدیریت تنوع‌های محصول (قیمت، موجودی و...)</a></p>
            <small>ابتدا محصول را با نوع "متغیر" و ویژگی‌های قابل تنظیم انتخاب شده ذخیره کنید، سپس برای مدیریت تنوع‌ها کلیک کنید.</small>
        </div>

        <fieldset style="margin-top: 20px;">
            <legend>پورسانت همکاری در فروش (اختیاری)</legend>
            <div>
                <label for="affiliate_commission_type">نوع پورسانت:</label>
                <select name="affiliate_commission_type" id="affiliate_commission_type">
                    <option value="none" <?php echo (isset($data['affiliate_commission_type']) && $data['affiliate_commission_type'] == 'none') ? 'selected' : ''; ?>>بدون پورسانت</option>
                    <option value="percentage" <?php echo (isset($data['affiliate_commission_type']) && $data['affiliate_commission_type'] == 'percentage') ? 'selected' : ''; ?>>درصدی (%)</option>
                    <option value="fixed_amount" <?php echo (isset($data['affiliate_commission_type']) && $data['affiliate_commission_type'] == 'fixed_amount') ? 'selected' : ''; ?>>مقدار ثابت (تومان)</option>
                </select>
            </div>
            <div>
                <label for="affiliate_commission_value">مقدار پورسانت:</label>
                <input type="number" step="0.01" name="affiliate_commission_value" id="affiliate_commission_value" value="<?php echo htmlspecialchars(isset($data['affiliate_commission_value']) ? $data['affiliate_commission_value'] : ''); ?>">
            </div>
        </fieldset>

        <div style="margin-top: 20px;">
            <button type="submit" name="edit_product_submit">ذخیره تغییرات</button>
            <a href="<?php echo BASE_URL; ?>admin/products" class="button-link button-secondary" style="margin-right: 10px;">انصراف</a>
        </div>
    </form>

    <script>
        function toggleProductFields() {
            var productType = document.getElementById('product_type').value;
            var simpleFields = document.getElementById('simpleProductFields');
            var variableFields = document.getElementById('variableProductFields');
            var priceInput = document.getElementById('price');
            var stockInput = document.getElementById('stock_quantity');
            var priceRequiredStar = document.getElementById('price_required_star');
            var stockRequiredStar = document.getElementById('stock_required_star');

            if (productType === 'variable') {
                simpleFields.style.display = 'none';
                variableFields.style.display = 'block';
                if(priceInput) priceInput.required = false;
                if(stockInput) stockInput.required = false;
                if(priceRequiredStar) priceRequiredStar.style.display = 'none';
                if(stockRequiredStar) stockRequiredStar.style.display = 'none';
            } else { // simple
                simpleFields.style.display = 'block';
                variableFields.style.display = 'none';
                if(priceInput) priceInput.required = true;
                if(stockInput) stockInput.required = true;
                if(priceRequiredStar) priceRequiredStar.style.display = 'inline';
                if(stockRequiredStar) stockRequiredStar.style.display = 'inline';
            }
        }
        document.addEventListener('DOMContentLoaded', toggleProductFields);
    </script>
    <style>.required_star { color: red; }</style>
    