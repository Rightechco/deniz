    <?php // ویو: app/views/affiliate/dashboard.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'پنل همکاری در فروش'); ?></h1>
    <p>خوش آمدید، <?php echo htmlspecialchars(isset($data['affiliate_name']) ? $data['affiliate_name'] : 'همکار گرامی'); ?>!</p>

    <?php 
    flash('payout_request_info'); 
    flash('info_message'); 
    ?>

    
    <div style="margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px; border: 1px solid #eee;">
        <h3>اطلاعات همکاری شما:</h3>
        <p><strong>کد همکاری شما:</strong> 
            <?php if (isset($data['affiliate_code']) && !empty($data['affiliate_code'])): ?>
                <strong style="color: #007bff; user-select: all;"><?php echo htmlspecialchars($data['affiliate_code']); ?></strong>
            <?php else: ?>
                <em>کد همکاری برای شما ایجاد نشده است. با مدیر تماس بگیرید.</em>
            <?php endif; ?>
        </p>
        <p><strong>لینک عمومی همکاری شما:</strong>
            <?php if (isset($data['affiliate_code']) && !empty($data['affiliate_code'])): ?>
                 <input type="text" value="<?php echo rtrim(BASE_URL, '/'); ?>?ref=<?php echo htmlspecialchars($data['affiliate_code']); ?>" readonly style="width:80%; padding:5px; background-color:#eee;" onclick="this.select(); document.execCommand('copy'); alert('لینک کپی شد!');">
            <?php else: ?>
                 <em>-</em>
            <?php endif; ?>
        </p>

        <p style="font-size: 1.2em;"><strong>موجودی کیف پول شما (کمیسیون قابل برداشت):</strong> 
            <strong style="color: green;"><?php echo htmlspecialchars(isset($data['affiliate_balance']) ? number_format((float)$data['affiliate_balance']) : '0.00'); ?> تومان</strong>
        </p>
         <?php if (isset($data['affiliate_balance']) && $data['affiliate_balance'] > 0): ?>
            <p><a href="<?php echo BASE_URL; ?>affiliate/requestPayout" class="button-link button-success" style="margin-top:10px;">درخواست تسویه حساب</a></p>
        <?php else: ?>
            <p style="margin-top:10px; color: #6c757d;"><i>برای درخواست تسویه، ابتدا باید کمیسیون تایید شده در کیف پول خود داشته باشید.</i></p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 30px; display:flex; gap:15px; flex-wrap:wrap;">
        <a href="<?php echo BASE_URL; ?>affiliate/marketingTools" class="button-link" style="background-color:#17a2b8;">لینک‌ها و ابزارها</a>
        <a href="<?php echo BASE_URL; ?>affiliate/commissions" class="button-link" style="background-color:#ffc107; color:black;">لیست کمیسیون‌ها</a>
        <a href="<?php echo BASE_URL; ?>affiliate/payoutHistory" class="button-link" style="background-color:#6c757d;">تاریخچه تسویه</a>
        <a href="<?php echo BASE_URL; ?>affiliate/createOrderForCustomer" class="button-link" style="background-color:#fd7e14;">ثبت سفارش برای مشتری</a>
    </div>
    