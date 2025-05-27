<?php // ویو: app/views/admin/products/edit_variation.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'ویرایش تنوع'); ?></h1>
<p>محصول والد: <strong><?php echo htmlspecialchars($data['parentProduct']['name']); ?></strong></p>

<?php 
flash('variation_action_success');
flash('variation_action_fail');
flash('variation_form_error');
flash('file_upload_error');
?>

<?php if (isset($data['variation']) && $data['variation']): ?>
    <?php $variation = $data['variation']; ?>
    <form action="<?php echo BASE_URL; ?>admin/editVariation/<?php echo $variation['id']; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="variation_id" value="<?php echo $variation['id']; ?>">
        <input type="hidden" name="current_variation_image_url" value="<?php echo htmlspecialchars($variation['image_url'] ?? ''); ?>">

        <h4>ویژگی‌های این تنوع (غیرقابل تغییر در اینجا):</h4>
        <div style="margin-bottom: 15px; padding: 10px; background-color: #f0f0f0; border-radius: 4px;">
            <?php
            $attrs_display = [];
            if (!empty($variation['attributes'])) {
                foreach ($variation['attributes'] as $attr_val) {
                    $attrs_display[] = htmlspecialchars($attr_val['attribute_name']) . ': <strong>' . htmlspecialchars($attr_val['attribute_value']) . '</strong>';
                }
            }
            echo implode(' &nbsp; | &nbsp; ', $attrs_display);
            ?>
        </div>

        <div style="margin-bottom: 10px;">
            <label for="variation_sku">SKU تنوع (اختیاری):</label>
            <input type="text" name="variation_sku" id="variation_sku" value="<?php echo htmlspecialchars($variation['sku'] ?? ''); ?>" style="width:100%; padding:8px;">
             <span class="error-text"><?php echo isset($data['errors']['sku_err']) ? $data['errors']['sku_err'] : ''; ?></span>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="variation_price">قیمت تنوع (تومان - اگر خالی باشد از قیمت والد استفاده می‌شود):</label>
            <input type="number" step="0.01" name="variation_price" id="variation_price" value="<?php echo htmlspecialchars($variation['price'] ?? ''); ?>" style="width:100%; padding:8px;">
            <span class="error-text"><?php echo isset($data['errors']['price_err']) ? $data['errors']['price_err'] : ''; ?></span>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="variation_stock">موجودی فعلی تنوع: <sup>*</sup></label>
            <input type="number" name="variation_stock" id="variation_stock" value="<?php echo htmlspecialchars($variation['stock_quantity'] ?? '0'); ?>" required min="0" style="width:100%; padding:8px;">
            <span class="error-text"><?php echo isset($data['errors']['stock_err']) ? $data['errors']['stock_err'] : ''; ?></span>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="variation_image">تغییر تصویر تنوع (اختیاری):</label>
            <input type="file" name="variation_image" id="variation_image">
            <small>اگر تصویری انتخاب نکنید، تصویر فعلی تنوع (در صورت وجود) یا تصویر محصول والد استفاده خواهد شد.</small>
            <span class="error-text"><?php echo isset($data['errors']['image_err']) ? $data['errors']['image_err'] : ''; ?></span>
            <?php if (!empty($variation['image_url'])): ?>
                <div style="margin-top: 10px;">
                    <p>تصویر فعلی تنوع:</p>
                    <img src="<?php echo BASE_URL . htmlspecialchars($variation['image_url']); ?>" alt="تصویر تنوع" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd;">
                </div>
            <?php elseif (!empty($data['parentProduct']['image_url'])): ?>
                 <div style="margin-top: 10px;">
                    <p>تصویر محصول والد (این تنوع تصویر اختصاصی ندارد):</p>
                    <img src="<?php echo BASE_URL . htmlspecialchars($data['parentProduct']['image_url']); ?>" alt="تصویر محصول والد" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 10px;">
            <input type="checkbox" name="variation_is_active" id="variation_is_active" value="1" <?php echo (isset($variation['is_active']) && $variation['is_active']) ? 'checked' : ''; ?>>
            <label for="variation_is_active">این تنوع فعال باشد</label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="button-link button-warning">ذخیره تغییرات تنوع</button>
            <a href="<?php echo BASE_URL; ?>admin/manageProductVariations/<?php echo $data['parentProduct']['id']; ?>" class="button-link button-secondary" style="margin-left: 10px;">انصراف و بازگشت</a>
        </div>
    </form>
<?php else: ?>
    <p>اطلاعات تنوع یافت نشد.</p>
    <a href="<?php echo BASE_URL; ?>admin/products" class="button-link button-secondary">بازگشت به لیست محصولات</a>
<?php endif; ?>

