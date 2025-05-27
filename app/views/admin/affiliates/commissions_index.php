    <?php // ویو: app/views/admin/affiliates/commissions_index.php ?>
    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'مدیریت کمیسیون‌های همکاری'); ?></h1>

    <?php 
    flash('commission_status_success');
    flash('commission_status_fail');
    flash('commission_status_warning');
    ?>

    <!-- 
    <form method="get" action="<?php echo BASE_URL; ?>admin/affiliateCommissions">
        <select name="status_filter">
            <option value="">همه وضعیت‌ها</option>
            <option value="pending">در انتظار</option>
            <option value="approved">تایید شده</option>
            <option value="rejected">رد شده</option>
            <option value="paid">پرداخت شده</option>
        </select>
        <button type="submit">فیلتر</button>
    </form>
    -->

    <?php if (isset($data['commissions']) && !empty($data['commissions'])): ?>
        <div style="overflow-x:auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th>شناسه کمیسیون</th>
                    <th>همکار فروش</th>
                    <th>سفارش</th>
                    <th>محصول</th>
                    <th>مبلغ فروش مبنا</th>
                    <th>کمیسیون کسب شده</th>
                    <th>وضعیت فعلی</th>
                    <th>تاریخ ثبت</th>
                    <th>تاریخ تایید</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['commissions'] as $commission): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($commission['id']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($commission['affiliate_full_name'] ?? $commission['affiliate_username']); ?>
                            <br><small>(ID: <?php echo htmlspecialchars($commission['affiliate_id']); ?>)</small>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>admin/orderDetails/<?php echo $commission['order_id']; ?>" target="_blank">سفارش #<?php echo htmlspecialchars($commission['order_id']); ?></a>
                            <br><small>(<?php echo htmlspecialchars(to_jalali_datetime($commission['order_date'])); ?>)</small>
                        </td>
                        <td><?php echo htmlspecialchars($commission['product_name'] ?? 'محصول حذف شده'); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$commission['sale_amount'])); ?> ت</td>
                        <td style="color:green; font-weight:bold;"><?php echo htmlspecialchars(number_format((float)$commission['commission_earned'])); ?> ت</td>
                        <td><?php echo htmlspecialchars($commission['status']); ?></td>
                        <td><?php echo htmlspecialchars(to_jalali_datetime($commission['created_at'])); ?></td>
                        <td><?php echo $commission['approved_at'] ? htmlspecialchars(to_jalali_datetime($commission['approved_at'])) : '-'; ?></td>
                        <td>
                            <form action="<?php echo BASE_URL; ?>admin/updateAffiliateCommissionStatus/<?php echo $commission['id']; ?>" method="post" style="display:inline-block;">
                                <select name="commission_status" style="padding: 3px; font-size:0.9em;">
                                    <option value="pending" <?php echo ($commission['status'] == 'pending') ? 'selected' : ''; ?>>در انتظار</option>
                                    <option value="approved" <?php echo ($commission['status'] == 'approved') ? 'selected' : ''; ?>>تایید شده</option>
                                    <option value="rejected" <?php echo ($commission['status'] == 'rejected') ? 'selected' : ''; ?>>رد شده</option>
                                    <option value="paid" <?php echo ($commission['status'] == 'paid') ? 'selected' : ''; ?>>پرداخت شده</option>
                                    <option value="cancelled" <?php echo ($commission['status'] == 'cancelled') ? 'selected' : ''; ?>>لغو شده</option>
                                </select>
                                <button type="submit" class="button-link button-warning btn-sm" style="padding: 3px 6px; font-size:0.85em;">تغییر وضعیت</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>هیچ کمیسیون همکاری برای نمایش وجود ندارد.</p>
    <?php endif; ?>
    <p style="margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>admin/dashboard" class="button-link button-secondary">بازگشت به داشبورد ادمین</a>
    </p>
    