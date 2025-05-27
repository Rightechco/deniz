    <?php // ویو: app/views/checkout/success.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'عملیات موفق'); ?></h1>

    <?php 
    // نمایش فلش مسیج‌ها از کنترلر
    flash('order_success'); 
    flash('order_warning'); 
    ?>

    <?php if (isset($data['order_id']) && !empty($data['order_id'])): ?>
        <p>سفارش شما با شناسه پیگیری <strong>#<?php echo htmlspecialchars($data['order_id']); ?></strong> با موفقیت ثبت شد و در حال پردازش است.</p>
    <?php elseif (!isset($_SESSION['flash']['order_success'])): // اگر order_id هم نبود و فلش هم نبود، یک پیام عمومی‌تر ?>
        <p>عملیات شما با موفقیت انجام شد.</p>
    <?php endif; ?>

    <p>از خرید شما سپاسگزاریم.</p>
    <p>جزئیات سفارش به ایمیل شما ارسال خواهد شد (در صورت پیاده‌سازی).</p>
    <p>می‌توانید وضعیت سفارش خود را از طریق <a href="<?php echo BASE_URL; ?>customer/orders">تاریخچه سفارشات</a> پیگیری کنید.</p>

    <p style="margin-top: 30px;">
        <a href="<?php echo BASE_URL; ?>products" class="button-link" style="background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">بازگشت به فروشگاه</a>
    </p>
    