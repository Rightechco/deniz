    <?php // ویو: app/views/admin/products/index.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'مدیریت محصولات'); ?></h1>

    <?php 
    flash('product_added_success'); 
    flash('product_action_fail'); 
    flash('product_updated_success'); 
    flash('product_deleted_success'); 
    flash('report_message'); 
    ?>

    <p style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px;">
        <a href="<?php echo BASE_URL; ?>admin/addProduct" class="button-link" style="background-color: #28a745;">افزودن محصول جدید</a>
        <a href="<?php echo BASE_URL; ?>admin/categories" class="button-link" style="background-color: #fd7e14;">مدیریت دسته‌بندی‌ها</a>
        <a href="<?php echo BASE_URL; ?>admin/attributes" class="button-link" style="background-color: #6f42c1; color:white;">مدیریت ویژگی‌ها</a>
        <a href="<?php echo BASE_URL; ?>admin/orders" class="button-link" style="background-color: #17a2b8;">مدیریت سفارشات</a>
        <a href="<?php echo BASE_URL; ?>admin/payoutRequests" class="button-link" style="background-color: #dc3545; color:white;">درخواست‌های تسویه</a>
        <a href="<?php echo BASE_URL; ?>admin/platformCommissions" class="button-link" style="background-color: #0dcaf0; color:black;">گزارش کمیسیون‌ها</a>
        <a href="<?php echo BASE_URL; ?>admin/reports" class="button-link" style="background-color: #6610f2; color:white;">گزارشات و خروجی‌ها</a>
        <a href="<?php echo BASE_URL; ?>admin/affiliateCommissions" class="button-link" style="background-color: #20c997; color:white;">مدیریت کمیسیون همکاری</a> 
        <a href="<?php echo BASE_URL; ?>admin/affiliatePayoutRequests" class="button-link" style="background-color: #fd7e14; color:white; margin-left:10px;">تسویه حساب همکاران</a>
        </p>

    <?php if (isset($data['products']) && !empty($data['products'])): ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.85em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 8px; border: 1px solid #ddd;">شناسه</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تصویر</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">نام محصول</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">فروشنده</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">دسته‌بندی</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: center;" title="موجودی اولیه کل محصول">موجودی اولیه کل</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: center;" title="تعداد کل فروخته شده">فروش کل</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: center;" title="موجودی اولیه کل - فروش کل">باقی‌مانده کل</th>
                    <th style="padding: 8px; border: 1px solid #ddd; min-width: 380px;">جزئیات قیمت و تنوع‌ها</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['products'] as $product): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($product['id']) ? $product['id'] : 'N/A'); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo BASE_URL . htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars(isset($product['name']) ? $product['name'] : 'محصول'); ?>" 
                                     style="width: 40px; height: auto; max-height:40px; object-fit:cover; border-radius:3px;">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>images/placeholder.png" 
                                     alt="تصویر" 
                                     style="width: 40px; height: auto; max-height:40px; object-fit:cover; border-radius:3px;">
                            <?php endif; ?>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($product['name']) ? $product['name'] : 'N/A'); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php 
                            if (isset($product['vendor_id']) && $product['vendor_id']) {
                                $vendor_display_name = isset($product['vendor_full_name']) && !empty(trim($product['vendor_full_name'])) ? $product['vendor_full_name'] : ($product['vendor_username'] ?? 'فروشنده #' . $product['vendor_id']);
                                echo htmlspecialchars($vendor_display_name);
                            } else {
                                echo '<em>فروشگاه</em>';
                            }
                            ?>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($product['category_name']) ? $product['category_name'] : '<em>-</em>'); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars(isset($product['initial_total_stock']) ? $product['initial_total_stock'] : '0'); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars(isset($product['sales_count_total_product']) ? $product['sales_count_total_product'] : '0'); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars(isset($product['remaining_total_stock']) ? $product['remaining_total_stock'] : '0'); ?></td>
                        
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php if (isset($product['product_type']) && $product['product_type'] == 'simple'): ?>
                                <strong>قیمت:</strong> <?php echo (isset($product['price']) && $product['price'] !== null) ? htmlspecialchars(number_format((float)$product['price'])) . ' تومان' : '---'; ?><br>
                                <strong>موجودی فعلی:</strong> <?php echo htmlspecialchars(isset($product['current_total_stock']) ? $product['current_total_stock'] : (isset($product['stock_quantity']) ? $product['stock_quantity'] : '0')); ?>
                            <?php elseif (isset($product['product_type']) && $product['product_type'] == 'variable'): ?>
                                <?php if (!empty($product['variations_details'])): ?>
                                    <ul style="margin:0; padding-left: 15px; font-size:0.9em; max-height: 120px; overflow-y:auto; list-style-type: none;">
                                        <?php foreach($product['variations_details'] as $variation): ?>
                                            <li style="margin-bottom:8px; padding-bottom:8px; <?php if(end($product['variations_details']) !== $variation) echo 'border-bottom:1px dotted #ccc;'; ?>">
                                                <?php
                                                $attrs_display_var = [];
                                                if (!empty($variation['attributes'])) {
                                                    foreach($variation['attributes'] as $attr_val) {
                                                        $attrs_display_var[] = htmlspecialchars($attr_val['attribute_value']);
                                                    }
                                                }
                                                echo '<strong>' . implode(' / ', $attrs_display_var) . '</strong><br>';
                                                ?>
                                                &nbsp;&nbsp;قیمت: <?php echo (isset($variation['price']) && $variation['price'] !== null) ? htmlspecialchars(number_format((float)$variation['price'])) . ' ت' : '<em>(والد)</em>'; ?><br>
                                                &nbsp;&nbsp;موجودی اولیه: <?php echo htmlspecialchars(isset($variation['initial_stock_quantity']) ? $variation['initial_stock_quantity'] : '0'); ?><br>
                                                &nbsp;&nbsp;موجودی فعلی: <?php echo htmlspecialchars(isset($variation['current_stock_quantity']) ? $variation['current_stock_quantity'] : '0'); ?><br>
                                                &nbsp;&nbsp;فروش: <?php echo htmlspecialchars(isset($variation['sales_count']) ? $variation['sales_count'] : '0'); ?><br>
                                                &nbsp;&nbsp;باقی‌مانده (از اولیه): <?php echo htmlspecialchars(isset($variation['remaining_stock_from_initial']) ? $variation['remaining_stock_from_initial'] : '0'); ?>
                                                <?php echo (isset($variation['is_active']) && $variation['is_active']) ? '' : '<span style="color:red; font-size:0.9em;"> (غیرفعال)</span>'; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <em>هنوز تنوعی تعریف نشده.</em><br>
                                    <small>قیمت والد: <?php echo (isset($product['price']) && $product['price'] !== null) ? htmlspecialchars(number_format((float)$product['price'])) . ' تومان' : '---'; ?></small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center; white-space: nowrap;">
                            <a href="<?php echo BASE_URL; ?>admin/editProduct/<?php echo htmlspecialchars(isset($product['id']) ? $product['id'] : ''); ?>" class="button-link button-warning" style="margin-right: 5px; margin-bottom: 5px; display:inline-block; padding: 5px 8px; font-size:0.85em;">ویرایش</a>
                            <?php if (isset($product['product_type']) && $product['product_type'] == 'variable'): ?>
                                <a href="<?php echo BASE_URL; ?>admin/manageProductVariations/<?php echo htmlspecialchars(isset($product['id']) ? $product['id'] : ''); ?>" class="button-link" style="background-color: #5cb85c; margin-bottom: 5px; display:inline-block; padding: 5px 8px; font-size:0.85em;">تنوع‌ها</a>
                            <?php endif; ?>
                            <form action="<?php echo BASE_URL; ?>admin/deleteProduct/<?php echo htmlspecialchars(isset($product['id']) ? $product['id'] : ''); ?>" method="post" style="display: inline;" onsubmit="return confirm('آیا از حذف این محصول مطمئن هستید؟');">
                                <button type="submit" class="button-danger" style="padding: 5px 8px; font-size:0.85em;">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>هیچ محصولی برای نمایش وجود ندارد.</p>
    <?php endif; ?>
    