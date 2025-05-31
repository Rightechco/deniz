<?php
// ویو: app/views/affiliate/marketing_tools.php
// این ویو از $data['affiliate_user'], $data['affiliate_code'], $data['general_affiliate_link'], $data['products'] استفاده می‌کند.
// اطمینان حاصل کنید که هدر و فوتر در Controller->view() به درستی include می‌شوند.
// require_once APPROOT . '/views/layouts/header_affiliate.php'; // یا هدر عمومی
?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'ابزارهای بازاریابی'); ?></h1>
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link btn-sm">بازگشت به داشبورد</a>
    </div>

    <?php flash('info_message'); ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">لینک همکاری عمومی شما</h5>
        </div>
        <div class="card-body">
            <?php if (isset($data['general_affiliate_link']) && !empty($data['general_affiliate_link'])): ?>
                <p>از این لینک برای ارجاع کاربران به صفحه اصلی فروشگاه استفاده کنید. هر خریدی که از طریق این لینک انجام شود، کمیسیون آن برای شما منظور خواهد شد.</p>
                <div class="input-group mb-3">
                    <input type="text" readonly 
                           value="<?php echo htmlspecialchars($data['general_affiliate_link']); ?>" 
                           class="form-control" 
                           id="generalAffiliateLinkInput"
                           style="direction: ltr; text-align: left;">
                    <button class="button-link btn-sm btn-outline-secondary" type="button" onclick="copyToClipboard('generalAffiliateLinkInput', this)">کپی لینک</button>
                </div>
                <p><small>کد همکاری شما: <strong><?php echo htmlspecialchars($data['affiliate_code'] ?? 'N/A'); ?></strong></small></p>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    لینک همکاری عمومی برای شما در دسترس نیست. ممکن است کد همکاری هنوز برای شما ایجاد نشده باشد.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">لینک همکاری برای محصولات خاص</h5>
        </div>
        <div class="card-body">
            <p>می‌توانید برای هر محصول یک لینک همکاری اختصاصی ایجاد کرده و آن را به اشتراک بگذارید. با کلیک روی این لینک‌ها، کاربر مستقیماً به صفحه محصول هدایت می‌شود.</p>
            
            <?php if (isset($data['products']) && !empty($data['products'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" style="font-size: 0.9em;">
                        <thead class="table-light">
                            <tr>
                                <th>نام محصول</th>
                                <th>لینک همکاری محصول</th>
                                <th>کپی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['products'] as $product): ?>
                                <?php 
                                    $product_affiliate_link = '';
                                    if (isset($data['affiliate_code']) && !empty($data['affiliate_code']) && isset($product['id'])) {
                                        $product_url = BASE_URL . 'products/show/' . $product['id'];
                                        // اطمینان از اینکه علامت سوال به درستی اضافه می‌شود
                                        $product_affiliate_link = $product_url . (strpos($product_url, '?') === false ? '?' : '&') . 'ref=' . htmlspecialchars($data['affiliate_code']);
                                    }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name'] ?? 'محصول بدون نام'); ?></td>
                                    <td style="direction: ltr; text-align: left;">
                                        <?php if (!empty($product_affiliate_link)): ?>
                                            <input type="text" readonly 
                                                   value="<?php echo $product_affiliate_link; ?>" 
                                                   class="form-control form-control-sm product-affiliate-link-input" 
                                                   id="productAffiliateLinkInput_<?php echo $product['id']; ?>">
                                        <?php else: ?>
                                            <em>لینک در دسترس نیست</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($product_affiliate_link)): ?>
                                            <button class="button-link btn-sm btn-outline-secondary" type="button" onclick="copyToClipboard('productAffiliateLinkInput_<?php echo $product['id']; ?>', this)">کپی</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light" role="alert">
                    در حال حاضر محصولی برای ایجاد لینک اختصاصی یافت نشد.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // می‌توانید بخش‌هایی برای بنرهای همکاری یا سایر ابزارهای بازاریابی نیز در اینجا اضافه کنید.
    /*
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">بنرهای تبلیغاتی</h5>
        </div>
        <div class="card-body">
            <p>از بنرهای زیر برای تبلیغ در وبسایت یا شبکه‌های اجتماعی خود استفاده کنید. کد HTML شامل لینک همکاری شما می‌باشد.</p>
            // مثال برای یک بنر
            // <div class="mb-3">
            //     <h6>بنر سایز ۳۰۰x۲۵۰</h6>
            //     <img src="<?php echo BASE_URL; ?>images/banners/banner_300x250.jpg" alt="بنر تبلیغاتی" class="img-fluid mb-2">
            //     <textarea readonly class="form-control" rows="3"><?php 
            //         if (isset($data['general_affiliate_link']) && !empty($data['general_affiliate_link'])) {
            //             echo htmlspecialchars('<a href="' . $data['general_affiliate_link'] . '" target="_blank"><img src="' . BASE_URL . 'images/banners/banner_300x250.jpg" alt="نام فروشگاه شما"></a>');
            //         }
            //     ?></textarea>
            // </div>
        </div>
    </div>
    */
    ?>

</div>

<script>
function copyToClipboard(elementId, buttonElement) {
    var copyText = document.getElementById(elementId);
    if (!copyText) return;

    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    try {
        var successful = document.execCommand('copy');
        var originalText = buttonElement.innerHTML;
        if(successful){
            buttonElement.innerHTML = 'کپی شد!';
            buttonElement.classList.add('btn-success'); // Assuming you have Bootstrap or similar for styling
            buttonElement.classList.remove('btn-outline-secondary');
        } else {
            buttonElement.innerHTML = 'خطا!';
            buttonElement.classList.add('btn-danger');
            buttonElement.classList.remove('btn-outline-secondary');
        }
        setTimeout(function(){
            buttonElement.innerHTML = originalText;
            buttonElement.classList.remove('btn-success', 'btn-danger');
            buttonElement.classList.add('btn-outline-secondary');
        }, 2000);
    } catch (err) {
        console.error('Error copying text: ', err);
        alert('خطا در کپی کردن لینک. مرورگر شما ممکن است از این قابلیت پشتیبانی نکند یا دسترسی به کلیپ‌بورد مسدود شده باشد.');
    }
}
</script>

<?php 
// require_once APPROOT . '/views/layouts/footer_affiliate.php'; // یا فوتر عمومی
?>
