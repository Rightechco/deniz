<?php // ویو: app/views/errors/404.php ?>

<h1><?php echo htmlspecialchars(isset($pageTitle) ? $pageTitle : 'خطای 404'); ?></h1>
<p><?php echo htmlspecialchars(isset($errorMessage) ? $errorMessage : 'متاسفانه صفحه مورد نظر شما یافت نشد.'); ?></p>
<p><a href="<?php echo BASE_URL; ?>index.php?url=pages/index">بازگشت به صفحه اصلی</a></p>