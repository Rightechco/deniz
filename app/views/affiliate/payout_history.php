    <?php // ویو: app/views/affiliate/payout_history.php ?>
    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'تاریخچه تسویه حساب‌های شما'); ?></h1>

    <?php if (isset($data['payouts']) && !empty($data['payouts'])): ?>
        <div style="overflow-x:auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 8px; border: 1px solid #ddd;">شناسه درخواست</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">مبلغ درخواستی</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">مبلغ پرداخت شده</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">وضعیت</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تاریخ درخواست</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تاریخ پردازش</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">جزئیات/یادداشت پرداخت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['payouts'] as $payout): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($payout['id']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$payout['requested_amount'])); ?> تومان</td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo (isset($payout['payout_amount']) && $payout['payout_amount'] !== null) ? htmlspecialchars(number_format((float)$payout['payout_amount'])) . ' تومان' : '<em>-</em>'; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align:center;"><?php echo htmlspecialchars($payout['status']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(to_jalali_datetime($payout['requested_at'])); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $payout['processed_at'] ? htmlspecialchars(to_jalali_datetime($payout['processed_at'])) : '<em>-</em>'; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo nl2br(htmlspecialchars($payout['payment_details'] ?? ($payout['notes'] ?? ''))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>شما تاکنون هیچ درخواست تسویه حسابی ثبت نکرده‌اید.</p>
    <?php endif; ?>
    <p style="margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link button-secondary">بازگشت به داشبورد</a>
    </p>
    