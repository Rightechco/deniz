    <?php // ویو: app/views/affiliate/marketing_tools.php ?>
    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'ابزار بازاریابی و لینک همکاری'); ?></h1>

    <?php if (isset($data['affiliate_code']) && !empty($data['affiliate_code'])): ?>
        <?php $affiliate_link = rtrim($data['base_url'], '/') . '?ref=' . htmlspecialchars($data['affiliate_code']); ?>
        <div style="margin-bottom: 20px; padding: 15px; background-color:#e9f7ef; border:1px solid #a6d7c2; border-radius:5px;">
            <h4>لینک همکاری عمومی شما:</h4>
            <p>این لینک را با دیگران به اشتراک بگذارید. هر خریدی که از طریق این لینک (یا با استفاده از کد شما در انتهای لینک محصولات) انجام شود، برای شما کمیسیون خواهد داشت.</p>
            <input type="text" value="<?php echo $affiliate_link; ?>" readonly style="width:100%; padding:10px; background-color:#fff; border:1px solid #ccc; margin-bottom:10px;" onclick="this.select(); document.execCommand('copy'); alert('لینک کپی شد!');">
            <button onclick="document.querySelector('input[value=\'<?php echo $affiliate_link; ?>\']').select(); document.execCommand('copy'); alert('لینک کپی شد!');" class="button-link btn-sm">کپی لینک</button>
        </div>

        <h4>لینک همکاری برای محصولات خاص:</h4>
        <p>برای ایجاد لینک همکاری برای یک محصول خاص، کد همکاری خود را به انتهای URL صفحه آن محصول اضافه کنید:</p>
        <p><code><?php echo rtrim($data['base_url'], '/'); ?>/products/show/شناسه_محصول?ref=<?php echo htmlspecialchars($data['affiliate_code']); ?></code></p>
        <p>به جای "شناسه_محصول"، شناسه محصول مورد نظر را قرار دهید.</p>
        
        <?php else: ?>
        <p style="color:red;">کد همکاری برای شما تعریف نشده است. لطفاً با مدیر سایت تماس بگیرید.</p>
    <?php endif; ?>

    <p style="margin-top:30px;">
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link button-secondary">بازگشت به داشبورد همکاری</a>
    </p>
    