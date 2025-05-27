    <?php // ویو: app/views/affiliate/request_payout_form.php ?>
    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'درخواست تسویه حساب'); ?></h1>

    <?php 
    flash('payout_success');
    flash('payout_fail');
    ?>

    <p>موجودی قابل برداشت فعلی شما: <strong style="color:green;"><?php echo htmlspecialchars(number_format(isset($data['current_balance']) ? (float)$data['current_balance'] : 0)); ?> تومان</strong></p>

    <?php if (isset($data['current_balance']) && $data['current_balance'] > 0): ?>
        <form action="<?php echo BASE_URL; ?>affiliate/requestPayout" method="post" style="border:1px solid #ccc; padding:20px; border-radius:5px; margin-top:20px;">
            <div style="margin-bottom:15px;">
                <label for="requested_amount">مبلغ درخواستی (تومان):</label>
                <input type="number" name="requested_amount" id="requested_amount" value="<?php echo htmlspecialchars(isset($data['current_balance']) ? $data['current_balance'] : '0'); ?>" max="<?php echo htmlspecialchars(isset($data['current_balance']) ? $data['current_balance'] : '0'); ?>" min="10000" required style="width:100%; padding:8px;">
                <small>حداقل مبلغ قابل درخواست 10,000 تومان است. شما می‌توانید کل موجودی خود را درخواست دهید.</small>
            </div>
            <div style="margin-bottom:15px;">
                <label for="payment_details_affiliate">اطلاعات حساب بانکی (شماره شبا یا کارت): <sup>*</sup></label>
                <textarea name="payment_details" id="payment_details_affiliate" rows="3" required style="width:100%; padding:8px;"></textarea>
                <small>این اطلاعات برای واریز مبلغ استفاده خواهد شد.</small>
            </div>
            <button type="submit" class="button-link button-success">ثبت درخواست تسویه</button>
        </form>
    <?php else: ?>
        <p style="color:orange;">در حال حاضر موجودی قابل برداشتی برای شما وجود ندارد.</p>
    <?php endif; ?>

    <p style="margin-top:30px;">
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link button-secondary">بازگشت به داشبورد</a>
    </p>
    