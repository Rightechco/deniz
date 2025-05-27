<?php // ویو: app/views/vendor/payouts/history.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'تاریخچه تسویه حساب‌ها'); ?></h1>

<?php 
flash('payout_success'); // برای پیام موفقیت درخواست جدید (اگر کاربر به این صفحه ریدایرکت شود)
flash('payout_fail'); 
flash('error_message');
?>

<?php if (isset($data['payouts']) && !empty($data['payouts'])): ?>
    <div style="overflow-x:auto;"> <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">شناسه درخواست</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">مبلغ درخواستی</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">مبلغ پرداخت شده</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">وضعیت</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">تاریخ درخواست</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">تاریخ پردازش</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">جزئیات/یادداشت پرداخت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['payouts'] as $payout): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($payout['id']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$payout['requested_amount'])); ?> تومان</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php echo (isset($payout['payout_amount']) && $payout['payout_amount'] !== null) ? htmlspecialchars(number_format((float)$payout['payout_amount'])) . ' تومان' : '<em>-</em>'; ?>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                            <?php 
                            // تبدیل وضعیت به فارسی (اختیاری)
                            $status_fa = $payout['status'];
                            switch ($payout['status']) {
                                case 'requested': $status_fa = 'درخواست شده'; break;
                                case 'processing': $status_fa = 'در حال پردازش'; break;
                                case 'completed': $status_fa = 'تکمیل شده'; break;
                                case 'rejected': $status_fa = 'رد شده'; break;
                                case 'cancelled_by_vendor': $status_fa = 'لغو شده توسط فروشنده'; break;
                            }
                            echo htmlspecialchars($status_fa); 
                            ?>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($payout['requested_at']) ? date('Y/m/d H:i', strtotime($payout['requested_at'])) : '-'); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo (isset($payout['processed_at']) && $payout['processed_at']) ? htmlspecialchars(date('Y/m/d H:i', strtotime($payout['processed_at']))) : '<em>-</em>'; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php echo nl2br(htmlspecialchars($payout['payment_details'] ?? ($payout['notes'] ?? ''))); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>شما تاکنون هیچ درخواست تسویه حسابی ثبت نکرده‌اید.</p>
<?php endif; ?>

<p style="margin-top: 30px;">
    <a href="<?php echo BASE_URL; ?>vendor/dashboard" class="button-link button-secondary">بازگشت به داشبورد</a>
</p>
