    <?php // ویو: app/views/admin/orders/index.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'مدیریت سفارشات'); ?></h1>

    <?php 
    flash('auth_required'); 
    flash('access_denied'); 
    flash('error_message'); 
    flash('order_status_updated'); 
    ?>

    <?php if (isset($data['orders']) && !empty($data['orders'])): ?>
        <form action="<?php echo BASE_URL; ?>admin/printBatchWarehouseReceipts" method="post" target="_blank" id="batchPrintForm" style="margin-bottom: 20px;">
            <button type="submit" name="print_selected_receipts" class="button-link" style="background-color: #5a2a94; color:white;">چاپ گروهی رسید انبار (انتخاب شده‌ها)</button>
             <button type="submit" formaction="<?php echo BASE_URL; ?>admin/printBatchShippingLabels" name="print_selected_labels" class="button-link" style="background-color: #6610f2; color:white; margin-left:10px;">چاپ گروهی لیبل پستی (انتخاب شده‌ها)</button>

            <button type="button" onclick="selectAllOrders(true)" class="button-link button-secondary btn-sm">انتخاب همه</button>
            <button type="button" onclick="selectAllOrders(false)" class="button-link button-secondary btn-sm">لغو انتخاب همه</button>
           
            <small>(حداکثر ۸ سفارش برای چاپ گروهی در هر صفحه توصیه می‌شود)</small>
            
            <div style="overflow-x:auto; margin-top:15px;"> 
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 10px; border: 1px solid #ddd; width: 30px;"><input type="checkbox" id="selectAllCheckbox" onclick="toggleAllOrderCheckboxes(this)"></th>
                        <th style="padding: 10px; border: 1px solid #ddd;">شناسه سفارش</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">مشتری</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">تاریخ ثبت (شمسی)</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">مبلغ کل</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">وضعیت سفارش</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">وضعیت پرداخت</th>
                        <th style="padding: 10px; border: 1px solid #ddd; text-align: center; min-width: 300px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['orders'] as $order): ?>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd; text-align:center;">
                                <input type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>" class="order-checkbox">
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <?php echo htmlspecialchars(isset($order['customer_full_name']) && !empty(trim($order['customer_full_name'])) ? $order['customer_full_name'] : ($order['customer_username'] ?? (isset($order['first_name']) ? $order['first_name'] . ' ' . $order['last_name'] : ''))); ?>
                                <br><small>(<?php echo htmlspecialchars(isset($order['customer_email']) ? $order['customer_email'] : ($order['email'] ?? '')); ?>)</small>
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(number_format((float)$order['total_amount'])); ?> تومان</td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($order['order_status']); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($order['payment_status']); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center; white-space:nowrap;">
                                <a href="<?php echo BASE_URL; ?>admin/orderDetails/<?php echo $order['id']; ?>" class="button-link button-info" style="padding: 5px 10px; font-size:0.85em; margin-right: 5px;">جزئیات</a>
                                <a href="<?php echo BASE_URL; ?>admin/printInvoice/<?php echo $order['id']; ?>" target="_blank" class="button-link" style="background-color: #28a745; font-size:0.85em; padding:5px 10px; margin-right: 5px;">چاپ فاکتور</a>
                                <a href="<?php echo BASE_URL; ?>admin/printWarehouseReceipt/<?php echo $order['id']; ?>" target="_blank" class="button-link" style="background-color: #ffc107; color:black; font-size:0.85em; padding:5px 10px; margin-right: 5px;">رسید انبار</a>
                                <a href="<?php echo BASE_URL; ?>admin/printShippingLabel/<?php echo $order['id']; ?>" target="_blank" class="button-link" style="background-color: #6f42c1; color:white; font-size:0.85em; padding:5px 10px;">لیبل پستی</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </form>
    <?php else: ?>
        <p>در حال حاضر هیچ سفارشی در سیستم ثبت نشده است.</p>
    <?php endif; ?>
     <p style="margin-top: 20px;">
        <a href="<?php echo BASE_URL; ?>admin/dashboard" class="button-link button-secondary">بازگشت به داشبورد ادمین</a>
    </p>

    <script>
        function toggleAllOrderCheckboxes(masterCheckbox) {
            var checkboxes = document.querySelectorAll('.order-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = masterCheckbox.checked;
            }
        }
        function selectAllOrders(select) {
            var checkboxes = document.querySelectorAll('.order-checkbox');
            var masterCheckbox = document.getElementById('selectAllCheckbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = select;
            }
            if(masterCheckbox) masterCheckbox.checked = select;
        }
    </script>
    