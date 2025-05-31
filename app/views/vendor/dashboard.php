<?php // ویو: app/views/vendor/dashboard.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'داشبورد فروشنده'); ?></h1>
<p>خوش آمدید، <?php echo htmlspecialchars(isset($data['vendor_name']) ? $data['vendor_name'] : 'فروشنده گرامی'); ?>!</p>

<?php 
flash('payout_success');
flash('payout_fail');
flash('error_message'); // For general errors passed to dashboard
flash('payout_request_fail_db'); // Specific error from payout request
flash('no_items_for_payout'); // Specific error from payout request
?>

<div style="margin-top: 20px; padding: 15px; background-color: #e7f3fe; border-left: 5px solid #2196F3; border-radius: 4px;">
    <h3>موجودی قابل برداشت شما</h3>
    <p style="font-size: 1.8em; font-weight: bold; color: #1976D2;">
        <?php echo htmlspecialchars(isset($data['withdrawable_balance']) ? number_format((float)$data['withdrawable_balance']) : '0'); ?> تومان
    </p>
    <small>این مبلغ بر اساس آیتم‌های سفارش تایید شده و پرداخت شده توسط مشتری که هنوز با شما تسویه نشده‌اند، محاسبه می‌شود.</small>
</div>

<div style="margin-top: 30px; display:flex; gap:20px; flex-wrap:wrap;">
    <a href="<?php echo BASE_URL; ?>vendor/myProducts" class="button-link">مدیریت محصولات من</a>
    <a href="<?php echo BASE_URL; ?>vendor/orders" class="button-link" style="background-color:#ffc107; color:black;">سفارشات محصولات من</a>
    <a href="<?php echo BASE_URL; ?>vendor/payoutHistory" class="button-link" style="background-color:#6c757d;">تاریخچه تسویه حساب‌ها</a>
</div>

<hr style="margin: 30px 0;">

<?php 
$min_payout_display = defined('MIN_VENDOR_PAYOUT_AMOUNT') ? (float)MIN_VENDOR_PAYOUT_AMOUNT : 50000;
$balance_is_sufficient = isset($data['withdrawable_balance']) && (float)$data['withdrawable_balance'] >= $min_payout_display;
$has_unpaid_items = isset($data['unpaid_items']) && !empty($data['unpaid_items']);
?>

<?php if ($balance_is_sufficient && $has_unpaid_items): ?>
    <h2>درخواست تسویه حساب</h2>
    <form action="<?php echo BASE_URL; ?>vendor/requestVendorPayout" method="post" style="border: 1px solid #28a745; padding: 20px; border-radius: 5px;">
        <p>شما می‌توانید برای کل مبلغ قابل برداشت خود (<strong><?php echo htmlspecialchars(number_format((float)$data['withdrawable_balance'])); ?> تومان</strong>) درخواست تسویه ثبت کنید.</p>
        
        <input type="hidden" name="requested_amount" value="<?php echo htmlspecialchars((float)$data['withdrawable_balance']); ?>">
        
        <div style="margin-bottom: 15px;">
            <label for="payment_details">اطلاعات حساب بانکی (شماره شبا یا کارت): <sup class="text-danger">*</sup></label>
            <textarea name="payment_details" id="payment_details" rows="3" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" placeholder="مثال: IR123456789012345678901234 یا شماره کارت" required><?php echo htmlspecialchars($data['vendor_user']['vendor_payment_details'] ?? ''); ?></textarea>
            <small>این اطلاعات فقط برای این درخواست تسویه استفاده می‌شود. برای ذخیره دائمی، از بخش پروفایل اقدام کنید.</small>
             <?php if (isset($data['details_err']) && !empty($data['details_err'])): ?>
                <div style="color:red; font-size:0.9em;"><?php echo $data['details_err']; ?></div>
            <?php endif; ?>
        </div>

        <p><strong>آیتم‌هایی که در این درخواست تسویه لحاظ می‌شوند (مجموع درآمد: <?php echo htmlspecialchars(number_format((float)$data['withdrawable_balance'])); ?> تومان):</strong></p>
        <?php if ($has_unpaid_items): ?>
            <ul style="font-size:0.9em; max-height:150px; overflow-y:auto; background:#fefefe; padding:10px; border:1px solid #eee;">
                <?php foreach($data['unpaid_items'] as $item): ?>
                    <li>
                        سفارش #<?php echo htmlspecialchars($item['order_id']); ?> - 
                        <?php echo htmlspecialchars($item['product_name']); ?> (<?php echo htmlspecialchars($item['quantity']); ?> عدد) - 
                        درآمد شما: <?php echo htmlspecialchars(number_format((float)$item['vendor_earning'])); ?> تومان
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
         <?php if (isset($data['amount_err']) && !empty($data['amount_err'])): ?>
            <div style="color:red; font-size:0.9em; margin-top:10px;"><?php echo $data['amount_err']; ?></div>
        <?php endif; ?>

        <button type="submit" class="button-link" style="background-color: #28a745; font-size: 1.1em; margin-top:15px;">ثبت درخواست تسویه کل مبلغ</button>
    </form>
<?php elseif (isset($data['withdrawable_balance']) && (float)$data['withdrawable_balance'] > 0 && !$has_unpaid_items): ?>
    <div class="alert alert-info" role="alert">
        موجودی قابل برداشت شما <strong><?php echo htmlspecialchars(number_format((float)$data['withdrawable_balance'])); ?> تومان</strong> است، اما در حال حاضر آیتم سفارش واجد شرایطی برای تسویه یافت نشد. این ممکن است به دلیل وضعیت سفارشات مرتبط باشد (باید پرداخت شده و تکمیل/ارسال/تحویل شده باشند).
    </div>
<?php else: ?>
    <p style="color:green; margin-top:20px;">در حال حاضر هیچ درآمد قابل تسویه‌ای برای شما وجود ندارد (حداقل مبلغ تسویه: <?php echo number_format($min_payout_display); ?> تومان).</p>
<?php endif; ?>

