<?php // ویو: app/views/admin/commissions/index.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'گزارش کمیسیون‌های فروشگاه'); ?></h1>

<div style="margin: 20px 0; padding: 15px; background-color: #e9ecef; border-radius: 5px;">
    <h4>نرخ کمیسیون فعلی فروشگاه (سراسری): <?php echo defined('PLATFORM_COMMISSION_RATE') ? (PLATFORM_COMMISSION_RATE * 100) . '%' : 'تعریف نشده'; ?></h4>

</div>

<div style="margin-bottom: 20px; padding: 15px; background-color: #d4edda; border-left: 5px solid #155724; border-radius: 4px;">
    <h3 style="margin-top:0;">مجموع کل کمیسیون فروشگاه از تمام سفارشات: 
        <span style="font-size: 1.5em; color: #155724;">
            <?php echo htmlspecialchars(isset($data['grand_total_commission']) ? number_format((float)$data['grand_total_commission']) : '0'); ?> تومان
        </span>
    </h3>
</div>


<?php if (isset($data['orders_with_commission']) && !empty($data['orders_with_commission'])): ?>
    <h3>جزئیات کمیسیون برای هر سفارش:</h3>
    <div style="overflow-x:auto;">
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9em;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px; border: 1px solid #ddd;">شناسه سفارش</th>
                <th style="padding: 8px; border: 1px solid #ddd;">مشتری</th>
                <th style="padding: 8px; border: 1px solid #ddd;">تاریخ ثبت (شمسی)</th>
                <th style="padding: 8px; border: 1px solid #ddd;">مبلغ کل سفارش</th>
                <th style="padding: 8px; border: 1px solid #ddd;">مجموع کمیسیون فروشگاه از این سفارش</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['orders_with_commission'] as $order): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($order['id']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <?php echo htmlspecialchars(isset($order['customer_full_name']) && !empty(trim($order['customer_full_name'])) ? $order['customer_full_name'] : ($order['customer_username'] ?? '')); ?>
                        <br><small>(<?php echo htmlspecialchars($order['customer_email'] ?? ''); ?>)</small>
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$order['total_amount'])); ?> تومان</td>
                    <td style="padding: 8px; border: 1px solid #ddd; color: #198754; font-weight:bold;">
                        <?php echo htmlspecialchars(isset($order['total_order_platform_commission']) ? number_format((float)$order['total_order_platform_commission']) : '0'); ?> تومان
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                        <a href="<?php echo BASE_URL; ?>admin/orderDetails/<?php echo $order['id']; ?>" class="button-link button-info" style="padding: 5px 10px; font-size:0.9em;">مشاهده جزئیات سفارش</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    <p>هیچ سفارشی برای نمایش کمیسیون یافت نشد.</p>
<?php endif; ?>
 <p style="margin-top: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/dashboard" class="button-link button-secondary">بازگشت به داشبورد ادمین</a>
</p>
