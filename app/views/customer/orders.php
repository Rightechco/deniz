    <?php // ویو: app/views/customer/orders.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'تاریخچه سفارشات شما'); ?></h1>

    <?php flash('auth_required'); ?>
    <?php flash('error_message'); ?>

    <?php if (isset($data['orders']) && !empty($data['orders'])): ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">شناسه سفارش</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">تاریخ ثبت (شمسی)</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">مبلغ کل</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">وضعیت سفارش</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">وضعیت پرداخت</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['orders'] as $order): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($order['id']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); // استفاده از تابع جدید ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$order['total_amount'])); ?> تومان</td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($order['order_status']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($order['payment_status']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                            <a href="<?php echo BASE_URL; ?>customer/orderDetails/<?php echo $order['id']; ?>" class="button-link" style="background-color: #17a2b8;">مشاهده جزئیات</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>شما تاکنون هیچ سفارشی ثبت نکرده‌اید.</p>
        <a href="<?php echo BASE_URL; ?>products" class="button-link">شروع خرید</a>
    <?php endif; ?>
    