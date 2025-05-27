<?php // ویو: app/views/admin/orders/details.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'جزئیات سفارش'); ?></h1>

<?php 
// نمایش تمام فلش مسیج‌هایی که ممکن است تنظیم شده باشند
flash('order_status_updated');    
flash('payout_processed_success'); 
flash('payout_processed_fail');   
flash('error_message'); 
?>

<?php if (isset($data['order']) && $data['order']): ?>
    <?php $order = $data['order']; $customer = isset($data['customer']) ? $data['customer'] : null; ?>
    
    <div style="margin-bottom: 20px; display: flex; justify-content: flex-start; gap: 10px;" class="no-print">
        <a href="<?php echo BASE_URL; ?>admin/printInvoice/<?php echo $order['id']; ?>" target="_blank" class="button-link" style="background-color: #28a745;">چاپ فاکتور</a>
        <a href="<?php echo BASE_URL; ?>admin/printWarehouseReceipt/<?php echo $order['id']; ?>" target="_blank" class="button-link" style="background-color: #ffc107; color:black;">چاپ رسید انبار</a>
        <a href="<?php echo BASE_URL; ?>admin/printShippingLabel/<?php echo $order['id']; ?>" target="_blank" class="button-link" style="background-color: #6f42c1; color:white;">چاپ لیبل پستی</a>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
        <div style="flex:1; min-width: 300px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; border-radius: 4px;">
            <h3>اطلاعات کلی سفارش</h3>
            <p><strong>شناسه سفارش:</strong> #<?php echo htmlspecialchars($order['id']); ?></p>
            <p><strong>تاریخ ثبت:</strong> <?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); ?></p>
            <p><strong>تاریخ آخرین به‌روزرسانی:</strong> <?php echo htmlspecialchars(to_jalali_datetime($order['updated_at'])); ?></p>
            <p><strong>مبلغ کل:</strong> <?php echo htmlspecialchars(number_format((float)$order['total_amount'])); ?> تومان</p>
            <p><strong>روش پرداخت:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>یادداشت مشتری:</strong> <?php echo !empty($order['notes']) ? nl2br(htmlspecialchars($order['notes'])) : '<em>بدون یادداشت</em>'; ?></p>
        </div>
        <div style="flex:1; min-width: 300px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; border-radius: 4px;">
            <h3>اطلاعات مشتری</h3>
            <?php if ($customer): ?>
                <p><strong>نام کاربر:</strong> <?php echo htmlspecialchars($customer['username']); ?></p>
                <p><strong>نام کامل ثبت شده در سفارش:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                <p><strong>ایمیل:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>تلفن:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            <?php else: ?>
                <p>اطلاعات مشتری یافت نشد.</p>
            <?php endif; ?>
        </div>
    </div>
     <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 4px;">
        <h3>آدرس ارسال</h3>
        <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
        <p><strong>شهر:</strong> <?php echo htmlspecialchars($order['city']); ?></p>
        <p><strong>کد پستی:</strong> <?php echo htmlspecialchars($order['postal_code']); ?></p>
    </div>

    <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f0f8ff; border-radius: 4px;">
        <h3>وضعیت سفارش و پرداخت</h3>
        <form action="<?php echo BASE_URL; ?>admin/updateOrderStatus/<?php echo $order['id']; ?>" method="post">
            <div style="margin-bottom:10px;">
                <label for="order_status"><strong>وضعیت سفارش:</strong></label>
                <select name="order_status" id="order_status" style="padding:5px; border:1px solid #ccc; border-radius:4px;">
                    <option value="pending_confirmation" <?php echo (isset($order['order_status']) && $order['order_status'] == 'pending_confirmation') ? 'selected' : ''; ?>>در انتظار تایید</option>
                    <option value="processing" <?php echo (isset($order['order_status']) && $order['order_status'] == 'processing') ? 'selected' : ''; ?>>در حال پردازش</option>
                    <option value="shipped" <?php echo (isset($order['order_status']) && $order['order_status'] == 'shipped') ? 'selected' : ''; ?>>ارسال شده</option>
                    <option value="delivered" <?php echo (isset($order['order_status']) && $order['order_status'] == 'delivered') ? 'selected' : ''; ?>>تحویل داده شده</option>
                    <option value="cancelled" <?php echo (isset($order['order_status']) && $order['order_status'] == 'cancelled') ? 'selected' : ''; ?>>لغو شده</option>
                    <option value="refunded" <?php echo (isset($order['order_status']) && $order['order_status'] == 'refunded') ? 'selected' : ''; ?>>مرجوع شده</option>
                </select>
            </div>
            <div style="margin-bottom:10px;">
                <label for="payment_status"><strong>وضعیت پرداخت:</strong></label>
                 <select name="payment_status" id="payment_status" style="padding:5px; border:1px solid #ccc; border-radius:4px;">
                    <option value="pending" <?php echo (isset($order['payment_status']) && $order['payment_status'] == 'pending') ? 'selected' : ''; ?>>در انتظار پرداخت</option>
                    <option value="pending_on_delivery" <?php echo (isset($order['payment_status']) && $order['payment_status'] == 'pending_on_delivery') ? 'selected' : ''; ?>>پرداخت هنگام تحویل</option>
                    <option value="paid" <?php echo (isset($order['payment_status']) && $order['payment_status'] == 'paid') ? 'selected' : ''; ?>>پرداخت شده</option>
                    <option value="failed" <?php echo (isset($order['payment_status']) && $order['payment_status'] == 'failed') ? 'selected' : ''; ?>>پرداخت ناموفق</option>
                    <option value="refunded" <?php echo (isset($order['payment_status']) && $order['payment_status'] == 'refunded') ? 'selected' : ''; ?>>مبلغ بازگردانده شد</option>
                </select>
            </div>
            <button type="submit" class="button-link button-warning">به‌روزرسانی وضعیت</button>
        </form>
    </div>

    <h3>محصولات سفارش داده شده</h3>
    <?php if (isset($order['items']) && !empty($order['items'])): ?>
        <div style="overflow-x:auto;">
        <table style="width:100%; font-size:0.9em; border-collapse:collapse;">
            <thead>
                <tr style="background-color:#f8f9fa;">
                    <th style="padding: 8px; border: 1px solid #dee2e6;">تصویر</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6;">نام محصول</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6; text-align:center;">تعداد</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6;">قیمت واحد</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6;">جمع جزء</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6;">فروشنده</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6;" title="نرخ کمیسیون فروشگاه در زمان فروش">نرخ کمیسیون (%)</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6;">مبلغ کمیسیون فروشگاه</th>
                    <th style="padding: 8px; border: 1px solid #dee2e6;">درآمد خالص فروشنده</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($order['items'] as $item): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                        <?php 
                        $item_display_image_path_admin = (!empty($item['display_image_url'])) ? BASE_URL . htmlspecialchars($item['display_image_url']) : BASE_URL . 'images/placeholder.png';
                        ?>
                        <img src="<?php echo $item_display_image_path_admin; ?>" alt="<?php echo htmlspecialchars(isset($item['product_name']) ? $item['product_name'] : ''); ?>" style="width: 40px; height: auto; max-height:40px; object-fit:cover; border-radius:3px;">
                    </td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars(isset($item['product_name']) ? $item['product_name'] : ''); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;"><?php echo htmlspecialchars(isset($item['quantity']) ? $item['quantity'] : '0'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars(isset($item['price_at_purchase']) ? number_format((float)$item['price_at_purchase']) : '0'); ?> ت</td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars(isset($item['sub_total']) ? number_format((float)$item['sub_total']) : '0'); ?> ت</td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars(isset($item['vendor_id']) ? 'فروشنده #' . $item['vendor_id'] : 'فروشگاه'); ?></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; text-align:center;">
                        <?php echo (isset($item['platform_commission_rate']) && $item['platform_commission_rate'] !== null) ? htmlspecialchars($item['platform_commission_rate'] * 100) . '%' : '<em>-</em>'; ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; color: #dc3545;">
                        <?php echo (isset($item['platform_commission_amount']) && $item['platform_commission_amount'] !== null) ? htmlspecialchars(number_format((float)$item['platform_commission_amount'])) . ' ت' : '<em>-</em>'; ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #dee2e6; color: green;">
                        <?php echo (isset($item['vendor_earning']) && $item['vendor_earning'] !== null) ? htmlspecialchars(number_format((float)$item['vendor_earning'])) . ' ت' : '<em>-</em>'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>هیچ محصولی برای این سفارش یافت نشد.</p>
    <?php endif; ?>

    <p style="margin-top: 30px;">
        <a href="<?php echo BASE_URL; ?>admin/orders" class="button-link button-secondary">بازگشت به لیست سفارشات</a>
    </p>

<?php else: ?>
    <p>اطلاعات سفارش یافت نشد.</p>
    <a href="<?php echo BASE_URL; ?>admin/orders" class="button-link button-secondary">بازگشت به لیست سفارشات</a>
<?php endif; ?>

<style>
    /* برای مخفی کردن دکمه‌ها هنگام چاپ واقعی */
    @media print {
        .no-print { display: none !important; }
    }
</style>
