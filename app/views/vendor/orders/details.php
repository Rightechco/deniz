    <?php // ویو: app/views/vendor/orders/details.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'جزئیات سفارش'); ?></h1>
    <?php flash('error_message'); ?>

    <?php if (isset($data['order']) && $data['order'] && isset($data['order']['items'])): ?>
        <?php $order = $data['order']; $customer = isset($data['customer']) ? $data['customer'] : null; ?>
        
        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
            <div style="flex:1; min-width: 300px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; border-radius: 4px;">
                <h3>اطلاعات کلی سفارش</h3>
                <p><strong>شناسه سفارش:</strong> #<?php echo htmlspecialchars($order['id']); ?></p>
                <p><strong>تاریخ ثبت:</strong> <?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); // شمسی شد ?></p>
                <p><strong>وضعیت سفارش:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
                <p><strong>وضعیت پرداخت:</strong> <?php echo htmlspecialchars($order['payment_status']); ?></p>
                 <p><strong>مبلغ کل سفارش (برای تمام آیتم‌ها):</strong> <?php echo htmlspecialchars(number_format((float)$order['total_amount'])); ?> تومان</p>
            </div>
            <div style="flex:1; min-width: 300px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9; border-radius: 4px;">
                <h3>اطلاعات مشتری</h3>
                <?php if ($customer): ?>
                    <p><strong>نام مشتری:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
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
            <?php if (!empty($order['notes'])): ?>
                <p><strong>یادداشت مشتری:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            <?php endif; ?>
        </div>

        <h3>محصولات شما در این سفارش</h3>
        <?php if (!empty($order['items'])): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size:0.9em;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 8px; border: 1px solid #ddd;">تصویر</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">نام محصول (و تنوع)</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">قیمت واحد (زمان خرید)</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">تعداد</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">جمع جزء (برای شما)</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">درآمد شما از این آیتم</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_vendor_earning_for_this_order = 0;
                    foreach ($order['items'] as $item): 
                        $vendor_earning_item = isset($item['vendor_earning']) ? (float)$item['vendor_earning'] : 0;
                        $total_vendor_earning_for_this_order += $vendor_earning_item;
                    ?>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <?php 
                                $item_display_image_path_vendor = (!empty($item['display_image_url'])) ? BASE_URL . htmlspecialchars($item['display_image_url']) : BASE_URL . 'images/placeholder.png';
                                ?>
                                <img src="<?php echo $item_display_image_path_vendor; ?>" alt="<?php echo htmlspecialchars(isset($item['product_name']) ? $item['product_name'] : ''); ?>" style="width: 40px; height: auto; max-height:40px; object-fit:cover; border-radius:3px;">
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($item['product_name']) ? $item['product_name'] : ''); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($item['price_at_purchase']) ? number_format((float)$item['price_at_purchase']) : '0'); ?> تومان</td>
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo htmlspecialchars(isset($item['quantity']) ? $item['quantity'] : '0'); ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(isset($item['sub_total']) ? number_format((float)$item['sub_total']) : '0'); ?> تومان</td>
                            <td style="padding: 8px; border: 1px solid #ddd; color: green; font-weight: bold;"><?php echo htmlspecialchars(number_format($vendor_earning_item)); ?> تومان</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                 <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right; font-weight:bold; padding:10px; border:1px solid #ddd;">مجموع درآمد شما از این سفارش:</td>
                        <td style="font-weight:bold; padding:10px; border:1px solid #ddd; color:green;"><?php echo number_format($total_vendor_earning_for_this_order); ?> تومان</td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <p>هیچ محصولی از شما در این سفارش یافت نشد.</p>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            <a href="<?php echo BASE_URL; ?>vendor/orders" class="button-link button-secondary">بازگشت به لیست سفارشات شما</a>
        </p>

    <?php else: ?>
        <p>اطلاعات سفارش یافت نشد یا شما به این سفارش دسترسی ندارید.</p>
         <a href="<?php echo BASE_URL; ?>vendor/orders" class="button-link button-secondary">بازگشت به لیست سفارشات شما</a>
    <?php endif; ?>
    