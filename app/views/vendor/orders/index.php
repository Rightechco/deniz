<?php // ویو: app/views/vendor/orders/index.php ?>

<div class="container mt-4" style="font-family: 'Vazirmatn', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'سفارشات محصولات شما'); ?></h1>
        <a href="<?php echo BASE_URL; ?>vendor/dashboard" class="button-link btn-sm">بازگشت به داشبورد</a>
    </div>

    <?php flash('info_message'); ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">لیست سفارشات</h5>
        </div>
        <div class="card-body">
            <?php if (isset($data['orders']) && !empty($data['orders'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" style="font-size: 0.9em;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">شناسه سفارش</th>
                                <th style="width: 20%;">مشتری</th>
                                <th style="width: 15%;">مبلغ کل (تومان)</th>
                                <th style="width: 15%;">وضعیت سفارش</th>
                                <th style="width: 15%;">وضعیت پرداخت</th>
                                <th style="width: 15%;">تاریخ ثبت</th>
                                <th style="width: 10%; text-align: center;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['orders'] as $order): ?>
                                <tr>
                                    <td>#<?php echo isset($order['order_id']) ? htmlspecialchars($order['order_id']) : 'N/A'; ?></td>
                                    <td>
                                        <?php 
                                        $customerName = '';
                                        if (isset($order['customer_full_name']) && !empty(trim($order['customer_full_name']))) {
                                            $customerName = $order['customer_full_name'];
                                        } elseif (isset($order['customer_username']) && !empty($order['customer_username'])) {
                                            $customerName = $order['customer_username'];
                                        }
                                        echo htmlspecialchars($customerName ?: 'نامشخص'); 
                                        ?>
                                    </td>
                                    <td><?php echo isset($order['total_amount']) ? htmlspecialchars(number_format((float)$order['total_amount'])) : '0'; ?></td>
                                    <td>
                                        <span class="badge <?php echo function_exists('get_order_status_class') ? get_order_status_class($order['order_status'] ?? '') : 'bg-secondary text-white'; ?>">
                                            <?php echo htmlspecialchars(function_exists('translate_order_status') ? translate_order_status(isset($order['order_status']) ? $order['order_status'] : 'نامشخص') : ($order['order_status'] ?? 'نامشخص')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo function_exists('get_payment_status_class') ? get_payment_status_class($order['payment_status'] ?? '') : 'bg-secondary text-white'; ?>">
                                            <?php echo htmlspecialchars(function_exists('translate_payment_status') ? translate_payment_status(isset($order['payment_status']) ? $order['payment_status'] : 'نامشخص') : ($order['payment_status'] ?? 'نامشخص')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo isset($order['order_date']) ? htmlspecialchars(to_jalali_datetime($order['order_date'])) : '<em>نامشخص</em>'; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?php echo BASE_URL . 'vendor/orderDetails/' . (isset($order['order_id']) ? htmlspecialchars($order['order_id']) : ''); ?>" class="button-link" style="background-color: #17a2b8; font-size:0.9em; padding: 4px 8px;">مشاهده جزئیات</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    هنوز هیچ سفارشی برای محصولات شما ثبت نشده است.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
//  توابع کمکی از اینجا حذف شده‌اند و باید از طریق فایل helpers/status_helper.php بارگذاری شوند.
// require_once APPROOT . '/views/layouts/footer_vendor.php'; 
?>
