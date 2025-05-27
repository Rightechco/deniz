    <?php // ویو: app/views/admin/affiliates/payouts_index.php ?>
    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'درخواست‌های تسویه حساب همکاران'); ?></h1>

    <?php 
    flash('payout_processed_success');
    flash('payout_processed_fail');
    flash('error_message'); 
    ?>
    <form method="get" action="<?php echo BASE_URL; ?>admin/affiliatePayoutRequests" style="margin-bottom:20px; padding:15px; background-color:#f0f0f0; border-radius:5px;">
        <div style="display:flex; flex-wrap:wrap; gap:15px;">
            <div>
                <label for="filter_start_date_aff_payout">از تاریخ درخواست:</label>
                <input type="text" name="start_date" id="filter_start_date_aff_payout" class="jalali-datepicker" value="<?php echo htmlspecialchars($data['filter_start_date'] ?? ''); ?>" placeholder="YYYY/MM/DD">
            </div>
            <div>
                <label for="filter_end_date_aff_payout">تا تاریخ درخواست:</label>
                <input type="text" name="end_date" id="filter_end_date_aff_payout" class="jalali-datepicker" value="<?php echo htmlspecialchars($data['filter_end_date'] ?? ''); ?>" placeholder="YYYY/MM/DD">
            </div>
            <div>
                <label for="filter_status_aff_payout">وضعیت:</label>
                <select name="status" id="filter_status_aff_payout">
                    <option value="">همه</option>
                    <option value="requested" <?php echo (($data['filter_status'] ?? '') == 'requested') ? 'selected' : ''; ?>>درخواست شده</option>
                    <option value="processing" <?php echo (($data['filter_status'] ?? '') == 'processing') ? 'selected' : ''; ?>>در حال پردازش</option>
                    <option value="completed" <?php echo (($data['filter_status'] ?? '') == 'completed') ? 'selected' : ''; ?>>تکمیل شده</option>
                    <option value="rejected" <?php echo (($data['filter_status'] ?? '') == 'rejected') ? 'selected' : ''; ?>>رد شده</option>
                    <option value="cancelled_by_affiliate" <?php echo (($data['filter_status'] ?? '') == 'cancelled_by_affiliate') ? 'selected' : ''; ?>>لغو شده توسط همکار</option>
                </select>
            </div>
            <div>
                <button type="submit" class="button-link" style="margin-top:25px;">اعمال فیلتر</button>
                <a href="<?php echo BASE_URL; ?>admin/affiliatePayoutRequests" class="button-link button-secondary" style="margin-top:25px;">پاک کردن فیلتر</a>
            </div>
        </div>
    </form>


    <?php if (isset($data['payouts']) && !empty($data['payouts'])): ?>
        <div style="overflow-x:auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th>شناسه</th>
                    <th>همکار فروش</th>
                    <th>مبلغ درخواستی</th>
                    <th>مبلغ پرداخت شده</th>
                    <th>وضعیت</th>
                    <th>تاریخ درخواست</th>
                    <th>تاریخ پردازش</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['payouts'] as $payout): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($payout['id']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($payout['affiliate_full_name'] ?? ($payout['affiliate_username'] ?? '')); ?>
                            <br><small>(<?php echo htmlspecialchars($payout['affiliate_email'] ?? ''); ?>)</small>
                        </td>
                        <td><?php echo htmlspecialchars(number_format((float)$payout['requested_amount'])); ?> ت</td>
                        <td><?php echo ($payout['payout_amount'] !== null) ? htmlspecialchars(number_format((float)$payout['payout_amount'])) . ' ت' : '<em>-</em>'; ?></td>
                        <td style="text-align:center;"><?php echo htmlspecialchars($payout['status']); ?></td>
                        <td><?php echo htmlspecialchars(to_jalali_datetime($payout['requested_at'])); ?></td>
                        <td><?php echo $payout['processed_at'] ? htmlspecialchars(to_jalali_datetime($payout['processed_at'])) : '<em>-</em>'; ?></td>
                        <td style="text-align: center;">
                            <a href="<?php echo BASE_URL; ?>admin/processAffiliatePayout/<?php echo $payout['id']; ?>" class="button-link button-warning">بررسی و پردازش</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>در حال حاضر هیچ درخواست تسویه حسابی برای نمایش (با فیلترهای اعمال شده) وجود ندارد.</p>
    <?php endif; ?>
     <p style="margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>admin/dashboard" class="button-link button-secondary">بازگشت به داشبورد ادمین</a>
    </p>
    <script>
    // کد جاوااسکریپت برای فعال‌سازی تقویم شمسی (مشابه صفحه گزارشات)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof kamaDatepicker === 'function') {
            if(document.getElementById('filter_start_date_aff_payout')) kamaDatepicker('filter_start_date_aff_payout', { twodigit: true, closeAfterSelect: true, placeholder: "YYYY/MM/DD", syncField:true });
            if(document.getElementById('filter_end_date_aff_payout')) kamaDatepicker('filter_end_date_aff_payout', { twodigit: true, closeAfterSelect: true, placeholder: "YYYY/MM/DD", syncField:true });
        }
    });
    </script>
    