    <?php // ویو: app/views/customer/order_details.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'جزئیات سفارش'); ?></h1>

    <?php if (isset($data['order']) && $data['order'] && isset($data['order']['items'])): ?>
        <?php $order = $data['order']; ?>
        <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; border-radius:4px;">
            <h3>اطلاعات کلی سفارش</h3>
            <p><strong>شناسه سفارش:</strong> #<?php echo htmlspecialchars($order['id']); ?></p>
            <p><strong>تاریخ ثبت:</strong> <?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); // شمسی شد ?></p>
            <p><strong>مبلغ کل:</strong> <?php echo htmlspecialchars(number_format((float)$order['total_amount'])); ?> تومان</p>
            <p><strong>وضعیت سفارش:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
            <p><strong>وضعیت پرداخت:</strong> <?php echo htmlspecialchars($order['payment_status']); ?></p>
            <p><strong>روش پرداخت:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        </div>

        <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius:4px;">
            <h3>اطلاعات ارسال</h3>
            <p><strong>گیرنده:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
            <p><strong>ایمیل:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>تلفن:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            <p><strong>آدرس:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
            <p><strong>شهر:</strong> <?php echo htmlspecialchars($order['city']); ?></p>
            <p><strong>کد پستی:</strong> <?php echo htmlspecialchars($order['postal_code']); ?></p>
            <?php if (!empty($order['notes'])): ?>
                <p><strong>یادداشت شما:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            <?php endif; ?>
        </div>

        <h3>محصولات سفارش داده شده</h3>
        <?php if (!empty($order['items'])): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size:0.9em;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 8px; border: 1px solid #ddd;">تصویر</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">نام محصول</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">قیمت واحد (زمان خرید)</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">تعداد</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">جمع جزء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <?php 
                                $item_image_path_cust = !empty($item['display_image_url']) ? BASE_URL . htmlspecialchars($item['display_image_url']) : BASE_URL . 'images/placeholder.png';
                                ?>
                                <img src="<?php echo $item_image_path_cust; ?>" alt="<?php echo htmlspecialchars(isset($item['product_name']) ? $item['product_name'] : ''); ?>" style="width: 40px; height: auto; max-height:40px; object-fit:cover; border-radius:3px;">
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($item['product_name']) ? $item['product_name'] : ''); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($item['price_at_purchase']) ? number_format((float)$item['price_at_purchase']) : '0'); ?> تومان</td>
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars(isset($item['quantity']) ? $item['quantity'] : '0'); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($item['sub_total']) ? number_format((float)$item['sub_total']) : '0'); ?> تومان</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>هیچ محصولی برای این سفارش یافت نشد.</p>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            <a href="<?php echo BASE_URL; ?>customer/orders" class="button-link button-secondary">بازگشت به تاریخچه سفارشات</a>
        </p>

    <?php else: ?>
        <p>اطلاعات سفارش یافت نشد.</p>
        <a href="<?php echo BASE_URL; ?>customer/orders" class="button-link button-secondary">بازگشت به تاریخچه سفارشات</a>
    <?php endif; ?>
    