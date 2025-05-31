<?php
// ویو: app/views/affiliate/request_payout_form.php
// این ویو از $data['pageTitle'], $data['affiliate_user'], $data['current_balance'],
// $data['requested_amount'], $data['payment_details'], $data['amount_err'], $data['details_err'] استفاده می‌کند.
// require_once APPROOT . '/views/layouts/header_affiliate.php'; // یا هدر عمومی
?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'درخواست تسویه حساب'); ?></h1>
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link btn-sm">بازگشت به داشبورد</a>
    </div>

    <?php 
    flash('payout_request_success'); 
    flash('payout_request_fail'); 
    // خطاهای اعتبارسنجی فرم مستقیماً از $data خوانده می‌شوند
    ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">موجودی و درخواست تسویه</h5>
        </div>
        <div class="card-body">
            <p class="lead">موجودی قابل برداشت فعلی شما: 
                <strong style="color: #28a745; font-size: 1.2em;">
                    <?php echo htmlspecialchars(number_format(isset($data['current_balance']) ? (float)$data['current_balance'] : 0)); ?> تومان
                </strong>
            </p>
            <hr>

            <?php
                $min_payout_for_view = defined('MIN_AFFILIATE_PAYOUT_AMOUNT') ? (float)MIN_AFFILIATE_PAYOUT_AMOUNT : 10000; // مثال
                $current_balance_float = isset($data['current_balance']) ? (float)$data['current_balance'] : 0;
                $can_request_payout_view = $current_balance_float >= $min_payout_for_view;
            ?>

            <?php if ($can_request_payout_view): ?>
                <form action="<?php echo BASE_URL; ?>affiliate/requestPayout" method="post" id="requestPayoutForm">
                    <div class="mb-3">
                        <label for="requested_amount" class="form-label">مبلغ درخواستی (تومان):</label>
                        <input type="number" name="requested_amount" id="requested_amount" class="form-control <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars(isset($data['requested_amount']) ? $data['requested_amount'] : $current_balance_float); ?>" 
                               max="<?php echo htmlspecialchars($current_balance_float); ?>" 
                               min="<?php echo $min_payout_for_view; ?>" 
                               required>
                        <div class="form-text">حداقل مبلغ قابل درخواست <?php echo number_format($min_payout_for_view); ?> تومان است. شما می‌توانید تا سقف موجودی قابل برداشت خود درخواست دهید.</div>
                        <?php if (isset($data['amount_err']) && !empty($data['amount_err'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $data['amount_err']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="payment_details" class="form-label">اطلاعات حساب بانکی جهت واریز (شماره شبا یا کارت): <sup class="text-danger">*</sup></label>
                        <textarea name="payment_details" id="payment_details" class="form-control <?php echo (!empty($data['details_err'])) ? 'is-invalid' : ''; ?>" rows="3" required><?php echo htmlspecialchars(isset($data['payment_details']) ? $data['payment_details'] : ($data['affiliate_user']['affiliate_payment_details'] ?? '')); ?></textarea>
                        <div class="form-text">این اطلاعات برای واریز مبلغ به حساب شما استفاده خواهد شد. لطفاً با دقت وارد کنید.</div>
                        <?php if (isset($data['details_err']) && !empty($data['details_err'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $data['details_err']; ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="button-link button-success btn-lg"><i class="fas fa-paper-plane me-2"></i>ثبت درخواست تسویه</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    <?php if ($current_balance_float > 0): ?>
                        موجودی قابل برداشت شما (<?php echo number_format($current_balance_float); ?> تومان) کمتر از حداقل مبلغ تسویه (<?php echo number_format($min_payout_for_view); ?> تومان) است.
                    <?php else: ?>
                        در حال حاضر موجودی قابل برداشتی برای شما وجود ندارد یا موجودی شما کمتر از حداقل مبلغ تسویه (<?php echo number_format($min_payout_for_view); ?> تومان) است.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// require_once APPROOT . '/views/layouts/footer_affiliate.php'; // یا فوتر عمومی
?>