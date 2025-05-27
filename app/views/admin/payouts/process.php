    <?php // ویو: app/views/admin/payouts/process.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'پردازش درخواست تسویه'); ?></h1>

    <?php 
    flash('payout_processed_success');
    flash('payout_processed_fail');
    flash('error_message'); 
    ?>

    <?php if (isset($data['payoutRequest']) && $data['payoutRequest']): ?>
        <?php $payout = $data['payoutRequest']; ?>
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1; min-width: 300px; background-color: #f9f9f9; padding:15px; border-radius:4px;">
                <h3>جزئیات درخواست</h3>
                <p><strong>شناسه درخواست:</strong> #<?php echo htmlspecialchars($payout['id']); ?></p>
                <p><strong>فروشنده:</strong> <?php echo htmlspecialchars($payout['vendor_full_name'] ?? ($payout['vendor_username'] ?? '')); ?> (<?php echo htmlspecialchars($payout['vendor_email'] ?? ''); ?>)</p>
                <p><strong>مبلغ درخواستی:</strong> <?php echo htmlspecialchars(number_format((float)$payout['requested_amount'])); ?> تومان</p>
                <p><strong>اطلاعات حساب فروشنده (برای واریز):</strong><br><?php echo nl2br(htmlspecialchars($payout['payment_details'] ?? 'وارد نشده')); ?></p>
                <p><strong>تاریخ درخواست:</strong> <?php echo htmlspecialchars(to_jalali_datetime($payout['requested_at'])); // شمسی شد ?></p>
                <p><strong>وضعیت فعلی:</strong> <?php echo htmlspecialchars($payout['status']); ?></p>
                <?php if ($payout['processed_at']): ?>
                    <p><strong>تاریخ پردازش:</strong> <?php echo htmlspecialchars(to_jalali_datetime($payout['processed_at'])); // شمسی شد ?></p>
                    <p><strong>مبلغ پرداخت شده:</strong> <?php echo $payout['payout_amount'] ? htmlspecialchars(number_format((float)$payout['payout_amount'])) . ' تومان' : '-'; ?></p>
                    <p><strong>یادداشت ادمین:</strong> <?php echo nl2br(htmlspecialchars($payout['notes'] ?? '')); ?></p>
                <?php endif; ?>
            </div>

            <div style="flex: 2; min-width: 400px;">
                <h3>پردازش درخواست</h3>
                <?php if (isset($payout['status']) && ($payout['status'] == 'requested' || $payout['status'] == 'processing')): ?>
                    <form action="<?php echo BASE_URL; ?>admin/processPayout/<?php echo $payout['id']; ?>" method="post" style="border:1px solid #ccc; padding:15px; border-radius:4px;">
                        <div style="margin-bottom:15px;">
                            <label for="payout_status">تغییر وضعیت به: <sup>*</sup></label>
                            <select name="payout_status" id="payout_status" required style="width:100%; padding:8px;">
                                <option value="processing" <?php echo ($payout['status'] == 'processing') ? 'selected' : ''; ?>>در حال پردازش</option>
                                <option value="completed" <?php echo ($payout['status'] == 'completed') ? 'selected' : ''; ?>>تکمیل شده (پرداخت شده)</option>
                                <option value="rejected" <?php echo ($payout['status'] == 'rejected') ? 'selected' : ''; ?>>رد شده</option>
                                <option value="on_hold" <?php echo ($payout['status'] == 'on_hold') ? 'selected' : ''; ?>>در انتظار بررسی</option>
                            </select>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label for="payout_amount_paid">مبلغ پرداخت شده (تومان):</label>
                            <input type="number" step="0.01" name="payout_amount_paid" id="payout_amount_paid" value="<?php echo htmlspecialchars($payout['payout_amount'] ?? $payout['requested_amount']); ?>" style="width:100%; padding:8px;">
                            <small>اگر وضعیت "تکمیل شده" است، این فیلد باید پر شود. در غیر این صورت می‌تواند خالی باشد.</small>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label for="payment_details_admin">جزئیات پرداخت (مثلاً شماره تراکنش، توضیحات شما):</label>
                            <textarea name="payment_details_admin" id="payment_details_admin" rows="3" style="width:100%; padding:8px;"><?php echo htmlspecialchars($payout['payment_details'] ?? ''); ?></textarea>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label for="admin_notes">یادداشت ادمین (برای فروشنده نمایش داده نمی‌شود، داخلی):</label>
                            <textarea name="admin_notes" id="admin_notes" rows="3" style="width:100%; padding:8px;"><?php echo htmlspecialchars($payout['notes'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="button-link button-warning">ثبت تغییرات وضعیت</button>
                    </form>
                <?php else: ?>
                    <p>این درخواست قبلاً پردازش شده یا توسط فروشنده لغو شده است (وضعیت فعلی: <?php echo htmlspecialchars($payout['status'] ?? 'نامشخص'); ?>).</p>
                <?php endif; ?>
            </div>
        </div>

        <h4>آیتم‌های سفارش مربوط به این درخواست تسویه</h4>
        <?php if (isset($data['payoutOrderItems']) && !empty($data['payoutOrderItems'])): ?>
            <table style="width:100%; font-size:0.85em; border-collapse:collapse;">
                <thead>
                    <tr style="background-color:#f8f9fa;">
                        <th style="padding: 8px; border: 1px solid #dee2e6;">شناسه آیتم سفارش</th>
                        <th style="padding: 8px; border: 1px solid #dee2e6;">شناسه سفارش والد</th>
                        <th style="padding: 8px; border: 1px solid #dee2e6;">نام محصول</th>
                        <th style="padding: 8px; border: 1px solid #dee2e6;">تعداد</th>
                        <th style="padding: 8px; border: 1px solid #dee2e6;">درآمد فروشنده از آیتم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['payoutOrderItems'] as $item): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($item['id']); ?></td>
                        <td style="padding: 8px; border: 1px solid #dee2e6;">
                            <a href="<?php echo BASE_URL; ?>admin/orderDetails/<?php echo htmlspecialchars($item['order_id']); ?>" target="_blank">
                                #<?php echo htmlspecialchars($item['order_id']); ?>
                            </a>
                        </td>
                        <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars(number_format((float)($item['vendor_earning'] ?? 0))); ?> تومان</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>هیچ آیتم سفارشی برای این درخواست تسویه یافت نشد (این حالت نباید رخ دهد اگر درخواست معتبر است).</p>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            <a href="<?php echo BASE_URL; ?>admin/payoutRequests" class="button-link button-secondary">بازگشت به لیست درخواست‌های تسویه</a>
        </p>
    <?php else: ?>
        <p>اطلاعات درخواست تسویه یافت نشد.</p>
        <a href="<?php echo BASE_URL; ?>admin/payoutRequests" class="button-link button-secondary">بازگشت به لیست درخواست‌های تسویه</a>
    <?php endif; ?>
    