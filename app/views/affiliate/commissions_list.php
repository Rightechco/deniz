    <?php // ویو: app/views/affiliate/commissions_list.php ?>
    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'لیست کمیسیون‌های کسب شده'); ?></h1>

    <?php if (isset($data['commissions']) && !empty($data['commissions'])): ?>
        <div style="overflow-x:auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 8px; border: 1px solid #ddd;">شناسه کمیسیون</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">سفارش</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">محصول</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">مبلغ فروش مبنا</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">نرخ/مبلغ ثابت</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">کمیسیون کسب شده</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">وضعیت</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تاریخ ثبت</th>
                    <th style="padding: 8px; border: 1px solid #ddd;">تاریخ تایید/پرداخت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['commissions'] as $commission): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($commission['id']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <a href="<?php echo BASE_URL; ?>customer/orderDetails/<?php echo $commission['order_id']; ?>" target="_blank">سفارش #<?php echo htmlspecialchars($commission['order_id']); ?></a>
                            <br><small>(<?php echo htmlspecialchars(to_jalali_datetime($commission['order_date'])); ?>)</small>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($commission['product_name'] ?? 'محصول حذف شده'); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$commission['sale_amount'])); ?> ت</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <?php 
                            if ($commission['commission_rate'] !== null) {
                                echo htmlspecialchars($commission['commission_rate'] * 100) . '%';
                            } elseif ($commission['commission_fixed_amount'] !== null) {
                                echo htmlspecialchars(number_format((float)$commission['commission_fixed_amount'])) . ' ت (ثابت)';
                            } else { echo '-'; }
                            ?>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd; color:green; font-weight:bold;"><?php echo htmlspecialchars(number_format((float)$commission['commission_earned'])); ?> ت</td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($commission['status']); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(to_jalali_datetime($commission['created_at'])); ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $commission['approved_at'] ? htmlspecialchars(to_jalali_datetime($commission['approved_at'])) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>شما هنوز هیچ کمیسیونی کسب نکرده‌اید.</p>
    <?php endif; ?>
    <p style="margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link button-secondary">بازگشت به داشبورد</a>
    </p>
    