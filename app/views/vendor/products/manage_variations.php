<?php // ویو: app/views/vendor/products/manage_variations.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'مدیریت تنوع‌ها'); ?></h1>

<?php if (isset($data['parentProduct']) && $data['parentProduct']): ?>
    <p>محصول والد: <strong><?php echo htmlspecialchars($data['parentProduct']['name']); ?> (ID: <?php echo $data['parentProduct']['id']; ?>)</strong></p>
<?php endif; ?>

<?php 
flash('variation_action_success'); 
flash('variation_action_fail'); 
flash('error_message'); 
?>

<hr style="margin: 20px 0;">
<h2>افزودن تنوع جدید</h2>
<?php if (isset($data['configurableAttributes']) && !empty($data['configurableAttributes'])): ?>
    <form action="<?php echo BASE_URL; ?>vendor/addVariation/<?php echo isset($data['parentProduct']['id']) ? $data['parentProduct']['id'] : ''; ?>" method="post" style="border: 1px solid #ccc; padding: 20px; margin-bottom: 30px; background-color:#f9f9f9; border-radius: 5px;">
        <p><strong>ویژگی‌های تنوع جدید را انتخاب کنید:</strong></p>
        <?php foreach ($data['configurableAttributes'] as $attribute): ?>
            <div style="margin-bottom: 10px;">
                <label for="attr_val_<?php echo $attribute['id']; ?>" style="font-weight:bold; display:inline-block; min-width: 80px;"><?php echo htmlspecialchars($attribute['name']); ?>: <sup>*</sup></label>
                <select name="variation_attributes[<?php echo $attribute['id']; ?>]" id="attr_val_<?php echo $attribute['id']; ?>" required style="padding: 8px; border:1px solid #ccc; border-radius:4px; min-width: 200px;">
                    <option value="">-- انتخاب <?php echo htmlspecialchars($attribute['name']); ?> --</option>
                    <?php if (!empty($attribute['values'])): ?>
                        <?php foreach ($attribute['values'] as $value_item): ?>
                            <option value="<?php echo $value_item['id']; ?>" <?php echo (isset($data['selected_attributes'][$attribute['id']]) && $data['selected_attributes'][$attribute['id']] == $value_item['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($value_item['value']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        <?php endforeach; ?>

        <hr style="margin: 15px 0;">
        <div style="margin-bottom: 10px;">
            <label for="variation_sku_add">SKU تنوع (اختیاری):</label>
            <input type="text" name="variation_sku" id="variation_sku_add" value="<?php echo htmlspecialchars(isset($data['variation_sku']) ? $data['variation_sku'] : ''); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="variation_price_add">قیمت تنوع (تومان - اگر خالی باشد از قیمت والد استفاده می‌شود):</label>
            <input type="number" step="0.01" name="variation_price" id="variation_price_add" value="<?php echo htmlspecialchars(isset($data['variation_price']) ? $data['variation_price'] : ''); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="variation_initial_stock_add">موجودی اولیه تنوع: <sup>*</sup></label>
            <input type="number" name="variation_initial_stock" id="variation_initial_stock_add" value="<?php echo htmlspecialchars(isset($data['variation_initial_stock']) ? $data['variation_initial_stock'] : '0'); ?>" required min="0" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>
         <div style="margin-bottom: 10px;">
            <label for="variation_stock_add">موجودی فعلی تنوع: <sup>*</sup> (هنگام افزودن، برابر با موجودی اولیه در نظر گرفته می‌شود)</label>
            <input type="number" name="variation_stock" id="variation_stock_add" value="<?php echo htmlspecialchars(isset($data['variation_stock']) ? $data['variation_stock'] : '0'); ?>" required min="0" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
            <small>این مقدار پس از فروش کاهش می‌یابد.</small>
        </div>
        
        <!-- 
        <div style="margin-bottom: 10px;">
            <label for="variation_image_add">تصویر تنوع (اختیاری):</label>
            <input type="file" name="variation_image" id="variation_image_add" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>
        -->
        <div style="margin-bottom: 10px;">
            <input type="checkbox" name="variation_is_active" id="variation_is_active_add" value="1" checked>
            <label for="variation_is_active_add">این تنوع فعال باشد</label>
        </div>

        <button type="submit" class="button-link" style="background-color: #28a745;">افزودن این تنوع</button>
    </form>
<?php elseif(isset($data['parentProduct'])): ?>
    <p style="color:orange;">هیچ ویژگی قابل تنظیمی برای این محصول انتخاب نشده است. لطفاً ابتدا از <a href="<?php echo BASE_URL; ?>vendor/editProduct/<?php echo $data['parentProduct']['id']; ?>">صفحه ویرایش محصول</a>، نوع محصول را "متغیر" انتخاب کرده و حداقل یک ویژگی قابل تنظیم برای آن مشخص کنید.</p>
<?php else: ?>
    <p style="color:red;">اطلاعات محصول والد یافت نشد.</p>
<?php endif; ?>

<hr style="margin: 30px 0;">
<h2>تنوع‌های موجود</h2>
<?php if (isset($data['existingVariations']) && !empty($data['existingVariations'])): ?>
    <div style="overflow-x:auto;"> <table style="width: 100%; font-size: 0.9em; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="padding: 8px; border: 1px solid #dee2e6;">شناسه</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">ویژگی‌ها</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">SKU</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">قیمت</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;" title="موجودی اولیه این تنوع">موجودی اولیه</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;" title="موجودی فعلی این تنوع پس از فروش">موجودی فعلی</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;" title="تعداد فروخته شده از این تنوع">فروش</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;" title="موجودی اولیه منهای تعداد فروش">باقی‌مانده (از اولیه)</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">فعال</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['existingVariations'] as $variation): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($variation['id']); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                        <?php
                        $attrs_display = [];
                        if (!empty($variation['attributes'])) {
                            foreach ($variation['attributes'] as $attr_val) {
                                $attrs_display[] = htmlspecialchars($attr_val['attribute_name']) . ': <strong>' . htmlspecialchars($attr_val['attribute_value']) . '</strong>';
                            }
                        }
                        echo implode('<br>', $attrs_display);
                        ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($variation['sku'] ? $variation['sku'] : '-'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($variation['price'] ? number_format((float)$variation['price']) . ' تومان' : '<em>(قیمت والد)</em>'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['initial_stock_quantity']) ? $variation['initial_stock_quantity'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['current_stock_quantity']) ? $variation['current_stock_quantity'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['sales_count']) ? $variation['sales_count'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['remaining_stock_from_initial']) ? $variation['remaining_stock_from_initial'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo (isset($variation['is_active']) && $variation['is_active']) ? 'بله' : 'خیر'; ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center; white-space:nowrap;">
                        <a href="<?php echo BASE_URL; ?>vendor/editVariation/<?php echo $variation['id']; ?>" class="button-link button-warning" style="font-size:0.9em; padding: 4px 8px; margin-bottom:3px; display:inline-block;">ویرایش</a>
                        <form action="<?php echo BASE_URL; ?>vendor/deleteVariation/<?php echo $variation['id']; ?>" method="post" style="display: inline;" onsubmit="return confirm('آیا از حذف این تنوع مطمئن هستید؟');">
                            <button type="submit" class="button-danger" style="font-size:0.9em; padding: 4px 8px;">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    <p>هنوز هیچ تنوعی برای این محصول ایجاد نشده است.</p>
<?php endif; ?>

<p style="margin-top: 30px;">
    <?php if (isset($data['parentProduct']) && $data['parentProduct']): ?>
        <a href="<?php echo BASE_URL; ?>vendor/editProduct/<?php echo $data['parentProduct']['id']; ?>" class="button-link button-secondary">بازگشت به ویرایش محصول والد</a>
    <?php endif; ?>
    <a href="<?php echo BASE_URL; ?>vendor/myProducts" class="button-link button-secondary" style="margin-left:10px;">بازگشت به لیست محصولات من</a>
</p>
