    <?php // ویو: app/views/vendor/orders/index.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'سفارشات محصولات شما'); ?></h1>

    <?php 
    flash('error_message'); 
    flash('access_denied');
    ?>

    <?php if (isset($data['orders']) && !empty($data['orders'])): ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 8px; border: 1px solid #ddd;">شناسه سفارش</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">مشتری</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تاریخ ثبت (شمسی)</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">مبلغ کل سفارش</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">وضعیت سفارش</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">وضعیت پرداخت</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['orders'] as $order): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($order['id']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php echo htmlspecialchars(isset($order['customer_full_name']) && !empty(trim($order['customer_full_name'])) ? $order['customer_full_name'] : ($order['customer_username'] ?? '')); ?><br>
                            <small>(<?php echo htmlspecialchars($order['customer_email'] ?? ''); ?>)</small>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); //  شمسی شد ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($order['total_amount']) ? number_format((float)$order['total_amount']) : '0'); ?> تومان</td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($order['order_status']) ? $order['order_status'] : ''); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($order['payment_status']) ? $order['payment_status'] : ''); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                            <a href="<?php echo BASE_URL; ?>vendor/orderDetails/<?php echo htmlspecialchars($order['id']); ?>" class="button-link" style="background-color: #17a2b8;">مشاهده جزئیات</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>هنوز هیچ سفارشی برای محصولات شما ثبت نشده است.</p>
    <?php endif; ?>
     <p style="margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>vendor/dashboard" class="button-link button-secondary">بازگشت به داشبورد</a>
    </p>
    