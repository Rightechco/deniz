<?php // ویو: app/views/admin/products/edit_variation.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'ویرایش تنوع محصول'); ?></h1>

<?php if (isset($data['parentProduct']) && $data['parentProduct']): ?>
    <p>محصول والد: <strong><?php echo htmlspecialchars($data['parentProduct']['name']); ?> (ID: <?php echo $data['parentProduct']['id']; ?>)</strong></p>
<?php endif; ?>

<?php 
flash('variation_action_success');
flash('variation_action_fail');
flash('variation_form_error'); // برای خطاهای کلی فرم
flash('file_upload_error');    // برای خطاهای آپلود فایل
flash('error_message');        // برای پیام‌های خطای عمومی
?>

<?php if (isset($data['variation']) && $data['variation']): ?>
    <?php $variation = $data['variation']; ?>
    <form action="<?php echo BASE_URL; ?>admin/editVariation/<?php echo $variation['id']; ?>" method="post" enctype="multipart/form-data" style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; background-color: #f9f9f9;">
        <input type="hidden" name="variation_id" value="<?php echo $variation['id']; ?>">
        <input type="hidden" name="current_variation_image_url" value="<?php echo htmlspecialchars(isset($variation['image_url']) ? $variation['image_url'] : ''); ?>">

        <h4>
            ویژگی‌های این تنوع (غیرقابل تغییر در این فرم):
            <?php
            $attrs_display = [];
            if (!empty($variation['attributes'])) {
                foreach ($variation['attributes'] as $attr_val) {
                    $attrs_display[] = htmlspecialchars($attr_val['attribute_name']) . ': <strong>' . htmlspecialchars($attr_val['attribute_value']) . '</strong>';
                }
            }
            echo implode(' &nbsp; | &nbsp; ', $attrs_display);
            ?>
        </h4>
        <hr style="margin: 10px 0 20px;">

        <div style="margin-bottom: 15px;">
            <label for="variation_sku_edit">SKU تنوع (اختیاری):</label>
            <input type="text" name="variation_sku" id="variation_sku_edit" value="<?php echo htmlspecialchars(isset($data['errors']) && isset($data['variation']['sku']) ? $data['variation']['sku'] : ($variation['sku'] ?? '')); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <span class="error-text"><?php echo isset($data['errors']['sku_err']) ? $data['errors']['sku_err'] : ''; ?></span>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="variation_price_edit">قیمت تنوع (تومان - اگر خالی باشد از قیمت والد استفاده می‌شود):</label>
            <input type="number" step="0.01" name="variation_price" id="variation_price_edit" value="<?php echo htmlspecialchars(isset($data['errors']) && isset($data['variation']['price']) ? $data['variation']['price'] : ($variation['price'] ?? '')); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <span class="error-text"><?php echo isset($data['errors']['price_err']) ? $data['errors']['price_err'] : ''; ?></span>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="variation_initial_stock_edit">موجودی اولیه تنوع:</label>
            <input type="number" name="variation_initial_stock_edit" id="variation_initial_stock_edit" value="<?php echo htmlspecialchars(isset($data['errors']) && isset($data['variation']['initial_stock_quantity']) ? $data['variation']['initial_stock_quantity'] : ($variation['initial_stock_quantity'] ?? '0')); ?>" min="0" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <small>این مقدار معمولاً پس از ایجاد تنوع تغییر نمی‌کند، مگر برای اصلاحات.</small>
            <span class="error-text"><?php echo isset($data['errors']['initial_stock_err']) ? $data['errors']['initial_stock_err'] : ''; ?></span>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="variation_stock_edit">موجودی فعلی تنوع: <sup>*</sup></label>
            <input type="number" name="variation_stock" id="variation_stock_edit" value="<?php echo htmlspecialchars(isset($data['errors']) && isset($data['variation']['stock_quantity']) ? $data['variation']['stock_quantity'] : ($variation['stock_quantity'] ?? '0')); ?>" required min="0" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <span class="error-text"><?php echo isset($data['errors']['stock_err']) ? $data['errors']['stock_err'] : ''; ?></span>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="variation_image_edit">تغییر تصویر تنوع (اختیاری):</label>
            <input type="file" name="variation_image" id="variation_image_edit" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <small>اگر تصویری انتخاب نکنید، تصویر فعلی تنوع (در صورت وجود) یا تصویر محصول والد استفاده خواهد شد.</small>
            <span class="error-text"><?php echo isset($data['errors']['image_err']) ? $data['errors']['image_err'] : ''; ?></span>
            
            <?php 
            $display_image_url_edit = '';
            $image_source_text_edit = '';
            if (!empty($variation['image_url'])) {
                $display_image_url_edit = BASE_URL . htmlspecialchars($variation['image_url']);
                $image_source_text_edit = "تصویر فعلی تنوع:";
            } elseif (isset($data['parentProduct']['image_url']) && !empty($data['parentProduct']['image_url'])) {
                $display_image_url_edit = BASE_URL . htmlspecialchars($data['parentProduct']['image_url']);
                $image_source_text_edit = "تصویر محصول والد (این تنوع تصویر اختصاصی ندارد):";
            }
            ?>
            <?php if (!empty($display_image_url_edit)): ?>
                <div style="margin-top: 10px;">
                    <p><?php echo $image_source_text_edit; ?></p>
                    <img src="<?php echo $display_image_url_edit; ?>" alt="تصویر محصول/تنوع" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius:4px;">
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 20px;">
            <input type="checkbox" name="variation_is_active" id="variation_is_active_edit" value="1" <?php 
                $is_active_checked = true; // پیش‌فرض فعال
                if (isset($data['errors']) && isset($data['variation']['is_active'])) { // اگر فرم با خطا بازگشته
                    $is_active_checked = (bool)$data['variation']['is_active'];
                } elseif (isset($variation['is_active'])) { // برای اولین بار
                    $is_active_checked = (bool)$variation['is_active'];
                }
                echo $is_active_checked ? 'checked' : ''; 
            ?>>
            <label for="variation_is_active_edit">این تنوع فعال باشد</label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="button-link button-warning">ذخیره تغییرات تنوع</button>
            <a href="<?php echo BASE_URL; ?>admin/manageProductVariations/<?php echo $variation['parent_product_id']; ?>" class="button-link button-secondary" style="margin-left: 10px;">انصراف و بازگشت</a>
        </div>
    </form>
<?php else: ?>
    <p>اطلاعات تنوع برای ویرایش یافت نشد.</p>
    <a href="<?php echo BASE_URL; ?>admin/products" class="button-link button-secondary">بازگشت به لیست محصولات</a>
<?php endif; ?>

<style>.error-text { color: red; font-size: 0.9em; display: block; margin-top:3px; }</style>
