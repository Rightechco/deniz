<?php
// ویو: app/views/affiliate/payout_history.php
// این ویو از $data['payouts'] و $data['pageTitle'] استفاده می‌کند.
// require_once APPROOT . '/views/layouts/header_affiliate.php'; // یا هدر عمومی
?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'تاریخچه تسویه حساب‌های شما'); ?></h1>
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link btn-sm">بازگشت به داشبورد</a>
    </div>

    <?php flash('payout_request_success'); // ممکن است پس از یک درخواست موفق به این صفحه هدایت شود ?>
    <?php flash('info_message'); ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">لیست درخواست‌های تسویه</h5>
        </div>
        <div class="card-body">
            <?php if (isset($data['payouts']) && !empty($data['payouts'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" style="font-size: 0.9em;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">شناسه درخواست</th>
                                <th style="width: 20%;">مبلغ درخواستی (تومان)</th>
                                <th style="width: 20%;">مبلغ پرداخت شده (تومان)</th>
                                <th style="width: 15%;">وضعیت</th>
                                <th style="width: 20%;">تاریخ درخواست</th>
                                <th style="width: 15%;">تاریخ پردازش</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['payouts'] as $payout): ?>
                                <tr>
                                    <td>#<?php echo isset($payout['id']) ? htmlspecialchars($payout['id']) : 'N/A'; ?></td>
                                    <td><?php echo isset($payout['requested_amount']) ? htmlspecialchars(number_format((float)$payout['requested_amount'])) : '0'; ?></td>
                                    <td style="color: <?php echo (isset($payout['status']) && $payout['status'] === 'completed' && isset($payout['payout_amount'])) ? '#28a745' : '#6c757d'; ?>; font-weight: bold;">
                                        <?php 
                                        if (isset($payout['status']) && $payout['status'] === 'completed' && isset($payout['payout_amount'])) {
                                            echo htmlspecialchars(number_format((float)$payout['payout_amount']));
                                        } elseif (isset($payout['status']) && in_array($payout['status'], ['rejected', 'cancelled'])) {
                                            echo '---';
                                        } else {
                                            echo '<i>در انتظار</i>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_text = isset($payout['status']) ? htmlspecialchars($payout['status']) : 'نامشخص';
                                        $status_class = 'badge bg-light text-dark'; // Default
                                        if ($status_text === 'requested') $status_class = 'badge bg-warning text-dark';
                                        else if ($status_text === 'processing') $status_class = 'badge bg-info text-dark';
                                        else if ($status_text === 'completed') $status_class = 'badge bg-success';
                                        else if ($status_text === 'rejected' || $status_text === 'cancelled') $status_class = 'badge bg-danger';
                                        
                                        // تابع کمکی برای ترجمه وضعیت‌ها
                                        echo "<span class='{$status_class}'>" . (function_exists('translate_payout_status') ? translate_payout_status($status_text) : ucfirst($status_text)) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo isset($payout['requested_at']) ? htmlspecialchars(to_jalali_datetime($payout['requested_at'])) : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <?php echo isset($payout['processed_at']) && $payout['processed_at'] ? htmlspecialchars(to_jalali_datetime($payout['processed_at'])) : '---'; ?>
                                    </td>
                                    <?php /*
                                    <td>
                                        <?php if (isset($payout['notes']) && !empty($payout['notes'])): ?>
                                            <small title="<?php echo htmlspecialchars($payout['notes']); ?>">یادداشت...</small>
                                        <?php else: ?>
                                            ---
                                        <?php endif; ?>
                                    </td>
                                    */ ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    شما تاکنون هیچ درخواست تسویه حسابی ثبت نکرده‌اید.
                </div>
            <?php endif; ?>
             <div class="mt-3">
                <a href="<?php echo BASE_URL; ?>affiliate/requestPayout" class="button-link button-success"><i class="fas fa-plus-circle me-1"></i>ثبت درخواست تسویه جدید</a>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to translate payout status (if not already available globally or in a helper file)
// You should ideally place this in a helper file if used in multiple views.
if (!function_exists('translate_payout_status')) {
    function translate_payout_status($status_en) {
        $translations = [
            'requested' => 'درخواست شده',
            'processing' => 'در حال پردازش',
            'completed' => 'تکمیل شده (پرداخت شده)',
            'rejected' => 'رد شده',
            'cancelled' => 'لغو شده',
        ];
        return $translations[strtolower($status_en)] ?? ucfirst($status_en);
    }
}
// require_once APPROOT . '/views/layouts/footer_affiliate.php'; // یا فوتر عمومی
?>
