<?php
// ویو: app/views/affiliate/commissions_list.php
// این ویو از $data['commissions'] و $data['pageTitle'] استفاده می‌کند.
// require_once APPROOT . '/views/layouts/header_affiliate.php'; // یا هدر عمومی
?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'لیست کمیسیون‌های شما'); ?></h1>
        <a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="button-link btn-sm">بازگشت به داشبورد</a>
    </div>

    <?php flash('info_message'); ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">تاریخچه کمیسیون‌ها</h5>
        </div>
        <div class="card-body">
            <?php if (isset($data['commissions']) && !empty($data['commissions'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" style="font-size: 0.9em;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">شناسه کمیسیون</th>
                                <th style="width: 15%;">شناسه سفارش</th>
                                <th style="width: 25%;">محصول/آیتم</th>
                                <th style="width: 15%;">مبلغ کمیسیون (تومان)</th>
                                <th style="width: 15%;">وضعیت</th>
                                <th style="width: 20%;">تاریخ ثبت سفارش</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['commissions'] as $commission): ?>
                                <tr>
                                    <td>#<?php echo isset($commission['id']) ? htmlspecialchars($commission['id']) : 'N/A'; ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL . 'customer/orderDetails/' . (isset($commission['order_id']) ? $commission['order_id'] : ''); // Assuming customer can view their order details ?>">
                                            #<?php echo isset($commission['order_id']) ? htmlspecialchars($commission['order_id']) : 'N/A'; ?>
                                        </a>
                                    </td>
                                    <td><?php echo isset($commission['product_name']) ? htmlspecialchars($commission['product_name']) : (isset($commission['order_item_id']) ? 'آیتم سفارش #' . $commission['order_item_id'] : 'N/A'); ?></td>
                                    <td style="font-weight: bold; color: #28a745;">
                                        <?php echo isset($commission['commission_earned']) ? htmlspecialchars(number_format((float)$commission['commission_earned'])) : '0'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_text = isset($commission['status']) ? htmlspecialchars($commission['status']) : 'نامشخص';
                                        $status_class = 'badge bg-light text-dark'; // Default
                                        if ($status_text === 'pending') $status_class = 'badge bg-warning text-dark';
                                        else if ($status_text === 'approved') $status_class = 'badge bg-info text-dark';
                                        else if ($status_text === 'paid') $status_class = 'badge bg-success';
                                        else if ($status_text === 'rejected' || $status_text === 'cancelled') $status_class = 'badge bg-danger';
                                        else if ($status_text === 'payout_requested') $status_class = 'badge bg-primary';
                                        
                                        echo "<span class='{$status_class}'>" . (function_exists('translate_commission_status') ? translate_commission_status($status_text) : ucfirst($status_text)) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $date_to_display = $commission['order_date'] ?? ($commission['created_at'] ?? null);
                                        echo $date_to_display ? htmlspecialchars(to_jalali_datetime($date_to_display)) : 'N/A'; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    شما هنوز هیچ کمیسیونی کسب نکرده‌اید.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to translate commission status (if not already available globally or in a helper file)
// You should ideally place this in a helper file if used in multiple views.
if (!function_exists('translate_commission_status')) {
    function translate_commission_status($status_en) {
        $translations = [
            'pending' => 'در انتظار تایید',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'paid' => 'پرداخت شده',
            'cancelled' => 'لغو شده',
            'payout_requested' => 'درخواست تسویه'
            // Add more translations if needed
        ];
        return $translations[strtolower($status_en)] ?? ucfirst($status_en);
    }
}
// require_once APPROOT . '/views/layouts/footer_affiliate.php'; // یا فوتر عمومی
?>
