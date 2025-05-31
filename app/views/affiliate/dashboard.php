<?php 
// ویو: app/views/affiliate/dashboard.php
// این ویو از $data['affiliate_user'], $data['commissions_summary'], $data['recent_commissions'] استفاده می‌کند.
// اطمینان حاصل کنید که هدر و فوتر در Controller->view() به درستی include می‌شوند.
// require_once APPROOT . '/views/layouts/header_affiliate.php'; // یا هدر عمومی
?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'پنل همکاری در فروش'); ?></h1>
        <a href="<?php echo BASE_URL; ?>auth/logout" class="button-link button-danger btn-sm">خروج</a>
    </div>

    <p>سلام <strong><?php echo htmlspecialchars(
        (isset($data['affiliate_user']['first_name']) && !empty(trim($data['affiliate_user']['first_name']))) ? 
        trim($data['affiliate_user']['first_name'] . ' ' . $data['affiliate_user']['last_name']) : 
        ($data['affiliate_user']['username'] ?? 'همکار گرامی')
    ); ?></strong>، به پنل همکاری خود خوش آمدید!</p>

    <?php flash('payout_request_success'); ?>
    <?php flash('payout_request_fail'); ?>
    <?php flash('info_message'); ?>
    <?php flash('error_message'); ?>


    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">اطلاعات و لینک همکاری شما</h5>
        </div>
        <div class="card-body">
            <?php if (isset($data['affiliate_user']['affiliate_code']) && !empty($data['affiliate_user']['affiliate_code'])): ?>
                <p class="mb-2">
                    <strong>کد همکاری شما:</strong> 
                    <span style="color: #007bff; user-select: all; background-color: #e9ecef; padding: 3px 6px; border-radius: 4px; font-weight: bold; display: inline-block;">
                        <?php echo htmlspecialchars($data['affiliate_user']['affiliate_code']); ?>
                    </span>
                </p>
                <p class="mb-1"><strong>لینک عمومی همکاری شما:</strong></p>
                <div class="input-group mb-3">
                    <input type="text" readonly 
                           value="<?php echo BASE_URL . '?ref=' . htmlspecialchars($data['affiliate_user']['affiliate_code']); ?>" 
                           class="form-control" 
                           id="affiliateLinkInputMain"
                           style="direction: ltr; text-align: left;">
                    <button class="button-link btn-sm btn-outline-secondary" type="button" onclick="copyToClipboard('affiliateLinkInputMain', this)">کپی</button>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    کد همکاری برای شما ایجاد نشده است یا در دسترس نیست. لطفاً برای دریافت کد با مدیریت تماس بگیرید.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row text-center mb-3">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 text-primary mb-2"><i class="fas fa-mouse-pointer"></i></div>
                    <h6 class="card-title text-muted">کلیک‌ها (اختیاری)</h6>
                    <p class="card-text fs-3 fw-bold"><?php echo isset($data['commissions_summary']['total_clicks']) ? number_format((int)$data['commissions_summary']['total_clicks']) : '۰'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 text-info mb-2"><i class="fas fa-shopping-cart"></i></div>
                    <h6 class="card-title text-muted">ارجاع‌های موفق (سفارشات)</h6>
                    <p class="card-text fs-3 fw-bold"><?php echo isset($data['commissions_summary']['total_sales']) ? number_format((int)$data['commissions_summary']['total_sales']) : '۰'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 text-warning mb-2"><i class="fas fa-coins"></i></div>
                    <h6 class="card-title text-muted">کل کمیسیون کسب شده</h6>
                    <p class="card-text fs-3 fw-bold"><?php echo isset($data['commissions_summary']['total_commissions_earned']) ? number_format((float)$data['commissions_summary']['total_commissions_earned']) : '۰'; ?> <small>تومان</small></p>
                </div>
            </div>
        </div>
         <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fs-4 text-secondary mb-2"><i class="fas fa-hand-holding-usd"></i></div>
                    <h6 class="card-title text-muted">کمیسیون پرداخت شده</h6>
                    <p class="card-text fs-3 fw-bold"><?php echo isset($data['commissions_summary']['total_commissions_paid']) ? number_format((float)$data['commissions_summary']['total_commissions_paid']) : '۰'; ?> <small>تومان</small></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4 shadow-lg" style="border-left: 5px solid #28a745;">
        <div class="card-body text-center py-4">
             <h4 class="card-title mb-2">موجودی قابل برداشت فعلی:</h4>
             <p class="card-text display-4 fw-bolder text-success">
                <?php echo isset($data['commissions_summary']['withdrawable_balance']) ? number_format((float)$data['commissions_summary']['withdrawable_balance']) : '۰'; ?>
                <small class="fs-5">تومان</small>
            </p>
            <?php 
                $min_payout = defined('MIN_AFFILIATE_PAYOUT_AMOUNT') ? (float)MIN_AFFILIATE_PAYOUT_AMOUNT : 10000;
                $can_request_payout = (isset($data['commissions_summary']['withdrawable_balance']) && (float)$data['commissions_summary']['withdrawable_balance'] >= $min_payout);
            ?>
            <?php if ($can_request_payout): ?>
                <a href="<?php echo BASE_URL; ?>affiliate/requestPayout" class="button-link button-success btn-lg mt-3 px-5 py-2">
                    <i class="fas fa-money-check-alt me-2"></i>درخواست تسویه حساب
                </a>
            <?php else: ?>
                <p class="text-muted mt-3">
                    <i>
                        <?php if (isset($data['commissions_summary']['withdrawable_balance']) && (float)$data['commissions_summary']['withdrawable_balance'] > 0): ?>
                            موجودی قابل برداشت شما (<?php echo number_format((float)$data['commissions_summary']['withdrawable_balance']); ?> تومان) کمتر از حداقل مبلغ تسویه (<?php echo number_format($min_payout); ?> تومان) است.
                        <?php else: ?>
                            برای درخواست تسویه، ابتدا باید کمیسیون تایید شده و قابل برداشت در کیف پول خود داشته باشید (حداقل <?php echo number_format($min_payout); ?> تومان).
                        <?php endif; ?>
                    </i>
                </p>
            <?php endif; ?>
        </div>
    </div>


    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">۵ کمیسیون اخیر</h5>
        </div>
        <div class="card-body">
            <?php if (isset($data['recent_commissions']) && !empty($data['recent_commissions'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" style="font-size: 0.9em;">
                        <thead class="table-light">
                            <tr>
                                <th>شناسه سفارش</th>
                                <th>محصول</th>
                                <th>مبلغ کمیسیون</th>
                                <th>وضعیت</th>
                                <th>تاریخ سفارش</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['recent_commissions'] as $commission): ?>
                                <tr>
                                    <td>#<?php echo isset($commission['order_id']) ? htmlspecialchars($commission['order_id']) : 'N/A'; ?></td>
                                    <td><?php echo isset($commission['product_name']) ? htmlspecialchars($commission['product_name']) : 'N/A'; ?></td>
                                    <td><?php echo isset($commission['commission_earned']) ? htmlspecialchars(number_format((float)$commission['commission_earned'])) : '0'; ?> ت</td>
                                    <td>
                                        <?php 
                                        $status_text = isset($commission['status']) ? htmlspecialchars($commission['status']) : 'نامشخص';
                                        $status_class = 'badge bg-light text-dark'; // Default
                                        if ($status_text === 'pending') $status_class = 'badge bg-warning text-dark';
                                        else if ($status_text === 'approved') $status_class = 'badge bg-info text-dark';
                                        else if ($status_text === 'paid') $status_class = 'badge bg-success';
                                        else if ($status_text === 'rejected' || $status_text === 'cancelled') $status_class = 'badge bg-danger';
                                        else if ($status_text === 'payout_requested') $status_class = 'badge bg-primary';
                                        
                                        // Assuming translate_commission_status function is globally available or defined in helpers
                                        echo "<span class='{$status_class}'>" . (function_exists('translate_commission_status') ? translate_commission_status($status_text) : ucfirst($status_text)) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo isset($commission['order_date']) ? htmlspecialchars(to_jalali_datetime($commission['order_date'])) : (isset($commission['created_at']) ? htmlspecialchars(to_jalali_datetime($commission['created_at'])) : 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                 <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>affiliate/commissions" class="button-link btn-sm">مشاهده تمام کمیسیون‌ها</a>
                </div>
            <?php else: ?>
                <div class="alert alert-light" role="alert">
                    هنوز کمیسیونی برای شما ثبت نشده است. با اشتراک‌گذاری لینک همکاری خود شروع کنید!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">دسترسی سریع</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <a href="<?php echo BASE_URL; ?>affiliate/marketingTools" class="button-link d-block text-center"><i class="fas fa-link me-2"></i>ابزارهای بازاریابی</a>
                </div>
                <div class="col-md-4 mb-2">
                    <a href="<?php echo BASE_URL; ?>affiliate/payoutHistory" class="button-link d-block text-center"><i class="fas fa-history me-2"></i>تاریخچه تسویه حساب</a>
                </div>
                 <div class="col-md-4 mb-2">
                    <a href="<?php echo BASE_URL; ?>affiliate/createOrderForCustomer" class="button-link button-primary d-block text-center"><i class="fas fa-plus-circle me-2"></i>ثبت سفارش برای مشتری</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId, buttonElement) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    try {
        var successful = document.execCommand('copy');
        var originalText = buttonElement.innerHTML;
        if(successful){
            buttonElement.innerHTML = 'کپی شد!';
            buttonElement.classList.add('btn-success');
            buttonElement.classList.remove('btn-outline-secondary');
        } else {
            buttonElement.innerHTML = 'خطا در کپی';
            buttonElement.classList.add('btn-danger');
             buttonElement.classList.remove('btn-outline-secondary');
        }
        setTimeout(function(){
            buttonElement.innerHTML = originalText;
            buttonElement.classList.remove('btn-success', 'btn-danger');
            buttonElement.classList.add('btn-outline-secondary');
        }, 2000);
    } catch (err) {
        alert('خطا در کپی کردن لینک. مرورگر شما ممکن است از این قابلیت پشتیبانی نکند.');
    }
}
// Add FontAwesome if not already included in your layout
// <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</script>

<?php 
// require_once APPROOT . '/views/layouts/footer_affiliate.php'; // یا فوتر عمومی
?>
