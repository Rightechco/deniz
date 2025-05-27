<?php // ویو: app/views/cart/index.php ?>

<h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>

<?php flash('cart_action_success'); ?>
<?php flash('cart_action_fail'); ?>
<?php flash('cart_action_info'); ?>


<?php if (!empty($data['cart_items'])): ?>
    <form action="<?php echo BASE_URL; ?>cart/updateQuantity" method="post">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">تصویر</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">نام محصول</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">قیمت واحد</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center; width: 120px;">تعداد</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">قیمت کل</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['cart_items'] as $item_cart_id => $item): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <?php 
                            // مسیر تصویر را با BASE_URL ترکیب می‌کنیم
                            $cart_image_path = !empty($item['image_url']) ? BASE_URL . htmlspecialchars($item['image_url']) : BASE_URL . 'images/placeholder.png';
                            ?>
                            <img src="<?php echo $cart_image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: auto; max-height: 60px; object-fit: cover; border-radius: 3px;">
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$item['price'])); ?> تومان</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                            <input type="number" name="quantity[<?php echo $item_cart_id; ?>]" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="0" style="width: 60px; text-align: center; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                            <input type="hidden" name="item_cart_id_in_form[<?php echo $item_cart_id; ?>]" value="<?php echo $item_cart_id; ?>"> </td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$item['price'] * (int)$item['quantity'])); ?> تومان</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                            <a href="<?php echo BASE_URL; ?>cart/remove/<?php echo $item_cart_id; ?>" onclick="return confirm('آیا از حذف این محصول از سبد خرید مطمئن هستید؟');" style="color: red; text-decoration: none;">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="padding: 10px; border: 1px solid #ddd; text-align: right;">
                        <button type="submit" name="update_cart_action" value="update_qty" class="button-link button-warning">به‌روزرسانی سبد</button>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right; font-weight: bold;">جمع کل:</td>
                    <td colspan="2" style="padding: 10px; border: 1px solid #ddd; font-weight: bold;"><?php echo htmlspecialchars(number_format((float)$data['total_price'])); ?> تومان</td>
                </tr>
            </tfoot>
        </table>
    </form>

    <div style="text-align: left; margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>checkout/index" class="button-link" style="background-color: #28a745;">ادامه و پرداخت</a>
        <a href="<?php echo BASE_URL; ?>products" class="button-link" style="background-color: #007bff; margin-right:10px;">ادامه خرید</a>
    </div>
<?php else: ?>
    <p>سبد خرید شما خالی است.</p>
    <a href="<?php echo BASE_URL; ?>products" class="button-link">بازگشت به فروشگاه</a>
<?php endif; ?>
