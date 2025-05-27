<?php // ویو: app/views/pages/index.php ?>

<h1><?php echo htmlspecialchars($welcomeMessage); ?></h1>
<p>این صفحه اصلی فروشگاه است که توسط PagesController و متد index مدیریت می‌شود.</p>
<p><a href="<?php echo BASE_URL; ?>index.php?url=pages/about">درباره ما</a></p>
<p><a href="<?php echo BASE_URL; ?>index.php?url=nonexistentcontroller/method">تست لینک خراب (کنترلر ناموجود)</a></p>
<p><a href="<?php echo BASE_URL; ?>index.php?url=pages/nonexistentmethod">تست لینک خراب (متد ناموجود)</a></p>