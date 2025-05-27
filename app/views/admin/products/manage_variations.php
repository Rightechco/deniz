<?php // ویو: app/views/admin/products/manage_variations.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'مدیریت تنوع‌ها'); ?></h1>
<?php if (isset($data['parentProduct']) && $data['parentProduct']): ?>
    <p>محصول والد: <strong><?php echo htmlspecialchars($data['parentProduct']['name']); ?> (ID: <?php echo $data['parentProduct']['id']; ?>)</strong></p>
<?php endif; ?>

<?php 
flash('variation_action_success'); 
flash('variation_action_fail'); 
flash('error_message'); 
?>

<hr>
<h2>افزودن تنوع جدید</h2>
<?php if (isset($data['configurableAttributes']) && !empty($data['configurableAttributes'])): ?>
    <form action="<?php echo BASE_URL; ?>admin/addVariation/<?php echo isset($data['parentProduct']['id']) ? $data['parentProduct']['id'] : ''; ?>" method="post" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background-color:#f9f9f9; border-radius: 5px;">
        <?php foreach ($data['configurableAttributes'] as $attribute): ?>
            <div style="margin-bottom: 10px;">
                <label for="attr_val_<?php echo $attribute['id']; ?>" style="font-weight:bold;"><?php echo htmlspecialchars($attribute['name']); ?>: <sup>*</sup></label>
                <select name="variation_attributes[<?php echo $attribute['id']; ?>]" id="attr_val_<?php echo $attribute['id']; ?>" required style="width:100%; padding: 8px; border:1px solid #ccc; border-radius:4px; margin-top:5px;">
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

        <div style="margin-bottom: 10px;">
            <label for="variation_sku">SKU تنوع (اختیاری):</label>
            <input type="text" name="variation_sku" id="variation_sku" value="<?php echo htmlspecialchars(isset($data['variation_sku']) ? $data['variation_sku'] : ''); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="variation_price">قیمت تنوع (تومان - اگر خالی باشد از قیمت والد استفاده می‌شود):</label>
            <input type="number" step="0.01" name="variation_price" id="variation_price" value="<?php echo htmlspecialchars(isset($data['variation_price']) ? $data['variation_price'] : ''); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="variation_stock">موجودی فعلی تنوع: <sup>*</sup></label>
            <input type="number" name="variation_stock" id="variation_stock" value="<?php echo htmlspecialchars(isset($data['variation_stock']) ? $data['variation_stock'] : '0'); ?>" required min="0" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>
         <div style="margin-bottom: 10px;">
            <label for="variation_initial_stock">موجودی اولیه تنوع: <sup>*</sup> (با موجودی فعلی یکی در نظر گرفته می‌شود هنگام افزودن)</label>
            <input type="number" name="initial_stock_quantity" id="variation_initial_stock" value="<?php echo htmlspecialchars(isset($data['variation_stock']) ? $data['variation_stock'] : '0'); ?>" required min="0" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" title="هنگام افزودن، موجودی اولیه برابر با موجودی فعلی خواهد بود. در ویرایش قابل تغییر است.">
        </div>
        <!-- 
        <div>
            <label for="variation_image">تصویر تنوع (اختیاری):</label>
            <input type="file" name="variation_image" id="variation_image">
        </div>
        -->
        <div style="margin-bottom: 10px;">
            <input type="checkbox" name="variation_is_active" id="variation_is_active" value="1" checked>
            <label for="variation_is_active">این تنوع فعال باشد</label>
        </div>

        <button type="submit" class="button-link" style="background-color: #28a745;">افزودن این تنوع</button>
    </form>
<?php elseif(isset($data['parentProduct'])): ?>
    <p style="color:orange;">هیچ ویژگی قابل تنظیمی برای این محصول انتخاب نشده است. لطفاً ابتدا از <a href="<?php echo BASE_URL; ?>admin/editProduct/<?php echo $data['parentProduct']['id']; ?>">صفحه ویرایش محصول</a>، نوع محصول را "متغیر" انتخاب کرده و حداقل یک ویژگی قابل تنظیم برای آن مشخص کنید.</p>
<?php else: ?>
    <p style="color:red;">اطلاعات محصول والد یافت نشد.</p>
<?php endif; ?>

<hr>
<h2>تنوع‌های موجود</h2>
<?php if (isset($data['existingVariations']) && !empty($data['existingVariations'])): ?>
    <table style="width: 100%; font-size: 0.9em;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="padding: 8px; border: 1px solid #dee2e6;">شناسه</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">ویژگی‌ها</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">SKU</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">قیمت</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">موجودی اولیه</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">موجودی فعلی</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">فروش</th>
                <th style="padding: 8px; border: 1px solid #dee2e6;">باقی‌مانده (از اولیه)</th>
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
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($variation['price'] ? number_format((float)$variation['price']) . ' تومان' : '<em>(والد)</em>'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['initial_stock_quantity']) ? $variation['initial_stock_quantity'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['current_stock_quantity']) ? $variation['current_stock_quantity'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['sales_count']) ? $variation['sales_count'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($variation['remaining_stock_from_initial']) ? $variation['remaining_stock_from_initial'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo (isset($variation['is_active']) && $variation['is_active']) ? 'بله' : 'خیر'; ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center; white-space:nowrap;">
                        <a href="<?php echo BASE_URL; ?>admin/editVariation/<?php echo $variation['id']; ?>" class="button-link button-warning" style="font-size:0.9em; padding: 4px 8px; margin-bottom:3px; display:inline-block;">ویرایش</a>
                        <form action="<?php echo BASE_URL; ?>admin/deleteVariation/<?php echo $variation['id']; ?>" method="post" style="display: inline;" onsubmit="return confirm('آیا از حذف این تنوع مطمئن هستید؟');">
                            <button type="submit" class="button-danger" style="font-size:0.9em; padding: 4px 8px;">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>هنوز هیچ تنوعی برای این محصول ایجاد نشده است.</p>
<?php endif; ?>

<p style="margin-top: 30px;">
    <?php if (isset($data['parentProduct']) && $data['parentProduct']): ?>
        <a href="<?php echo BASE_URL; ?>admin/editProduct/<?php echo $data['parentProduct']['id']; ?>" class="button-link button-secondary">بازگشت به ویرایش محصول والد</a>
    <?php endif; ?>
    <a href="<?php echo BASE_URL; ?>admin/products" class="button-link button-secondary" style="margin-left:10px;">بازگشت به لیست محصولات</a>
</p>
