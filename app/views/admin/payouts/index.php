    <?php // ویو: app/views/admin/payouts/index.php ?>
    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'درخواست‌های تسویه حساب فروشندگان'); ?></h1>

    <?php 
    flash('payout_processed_success');
    flash('payout_processed_fail');
    flash('error_message'); 
    ?>

    <?php if (isset($data['payouts']) && !empty($data['payouts'])): ?>
        <div style="overflow-x:auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 8px; border: 1px solid #ddd;">شناسه درخواست</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">فروشنده</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">مبلغ درخواستی</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">مبلغ پرداخت شده</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">وضعیت</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تاریخ درخواست (شمسی)</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تاریخ پردازش (شمسی)</th>
                    <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['payouts'] as $payout): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($payout['id']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php echo htmlspecialchars(isset($payout['vendor_full_name']) ? $payout['vendor_full_name'] : ($payout['vendor_username'] ?? '')); ?>
                            <br><small>(<?php echo htmlspecialchars($payout['vendor_email'] ?? ''); ?>)</small>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$payout['requested_amount'])); ?> تومان</td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo ($payout['payout_amount'] !== null) ? htmlspecialchars(number_format((float)$payout['payout_amount'])) . ' تومان' : '<em>-</em>'; ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align:center;"><?php echo htmlspecialchars($payout['status']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(to_jalali_datetime($payout['requested_at'])); // شمسی شد ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $payout['processed_at'] ? htmlspecialchars(to_jalali_datetime($payout['processed_at'])) : '<em>-</em>'; // شمسی شد ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                            <a href="<?php echo BASE_URL; ?>admin/processPayout/<?php echo $payout['id']; ?>" class="button-link button-warning">بررسی و پردازش</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>در حال حاضر هیچ درخواست تسویه حسابی برای بررسی وجود ندارد.</p>
    <?php endif; ?>
     <p style="margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>admin/dashboard" class="button-link button-secondary">بازگشت به داشبورد ادمین</a>
    </p>
    