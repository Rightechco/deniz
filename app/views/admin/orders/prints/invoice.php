   <?php // ویو: app/views/admin/orders/prints/invoice.php ?>
   <!DOCTYPE html>
   <html lang="fa" dir="rtl">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'فاکتور'); ?></title>
       <style>
           body { font-family: 'Vazirmatn', 'Tahoma', sans-serif; direction: rtl; margin: 0; padding: 20px; background-color: #fff; font-size: 12px; line-height: 1.6; }
           .invoice-box { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
           .invoice-header { text-align: center; margin-bottom: 20px; }
           .invoice-header img { max-width: 150px; max-height: 70px; margin-bottom: 10px; }
           .invoice-header h1 { margin: 0; font-size: 1.8em; color: #333; }
           .store-details, .customer-details, .order-details-summary { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed #ccc; }
           .store-details p, .customer-details p, .order-details-summary p { margin: 5px 0; }
           .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
           .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
           .items-table th { background-color: #f2f2f2; font-weight: bold; }
           .items-table .total-row td { font-weight: bold; background-color: #f9f9f9; }
           .text-center { text-align: center !important; }
           .text-left { text-align: left !important; }
           .footer-notes { margin-top: 30px; font-size: 0.9em; color: #777; }
           @media print {
               body { -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 0; margin: 0;}
               .invoice-box { box-shadow: none; border: none; margin: 0; max-width: 100%; padding:0;}
               .no-print { display: none; }
           }
       </style>
       <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
   </head>
   <body>
       <div class="invoice-box">
           <?php if (isset($data['order']) && $data['order']): $order = $data['order']; $store = $data['store_info']; ?>
               <div class="invoice-header">
                   <?php if (!empty($store['logo_url']) && filter_var($store['logo_url'], FILTER_VALIDATE_URL)): ?>
                       <img src="<?php echo htmlspecialchars($store['logo_url']); ?>" alt="لوگوی فروشگاه">
                   <?php endif; ?>
                   <h1>فاکتور فروش</h1>
                   <p>شماره سفارش: #<?php echo htmlspecialchars($order['id']); ?></p>
                   <p>تاریخ صدور: <?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); ?></p>
               </div>

               <table style="width:100%; margin-bottom:20px;">
                   <tr>
                       <td style="width:50%; vertical-align:top;" class="store-details">
                           <h4>اطلاعات فروشنده:</h4>
                           <p><strong><?php echo htmlspecialchars($store['name']); ?></strong></p>
                           <p><?php echo nl2br(htmlspecialchars($store['address'])); ?></p>
                           <p>تلفن: <?php echo htmlspecialchars($store['phone']); ?></p>
                           <p>ایمیل: <?php echo htmlspecialchars($store['email']); ?></p>
                       </td>
                       <td style="width:50%; vertical-align:top;" class="customer-details">
                           <h4>اطلاعات خریدار:</h4>
                           <p><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                           <p>آدرس: <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                           <p>شهر: <?php echo htmlspecialchars($order['city']); ?> - کد پستی: <?php echo htmlspecialchars($order['postal_code']); ?></p>
                           <p>تلفن: <?php echo htmlspecialchars($order['phone']); ?></p>
                           <p>ایمیل: <?php echo htmlspecialchars($order['email']); ?></p>
                       </td>
                   </tr>
               </table>

               <div class="order-details-summary">
                   <p><strong>وضعیت سفارش:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
                   <p><strong>وضعیت پرداخت:</strong> <?php echo htmlspecialchars($order['payment_status']); ?></p>
                   <p><strong>روش پرداخت:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
               </div>

               <h4>اقلام سفارش:</h4>
               <?php if (isset($order['items']) && !empty($order['items'])): ?>
                   <table class="items-table">
                       <thead>
                           <tr>
                               <th>ردیف</th>
                               <th>شرح محصول/خدمات</th>
                               <th class="text-center">تعداد</th>
                               <th>قیمت واحد (تومان)</th>
                               <th>مبلغ کل (تومان)</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php $item_counter = 1; $grand_total = 0; ?>
                           <?php foreach($order['items'] as $item): ?>
                               <tr>
                                   <td class="text-center"><?php echo $item_counter++; ?></td>
                                   <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                   <td class="text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                   <td><?php echo htmlspecialchars(number_format((float)$item['price_at_purchase'])); ?></td>
                                   <td><?php echo htmlspecialchars(number_format((float)$item['sub_total'])); ?></td>
                               </tr>
                               <?php $grand_total += (float)$item['sub_total']; ?>
                           <?php endforeach; ?>
                       </tbody>
                       <tfoot>
                           <tr class="total-row">
                               <td colspan="4" style="text-align:left; padding-right:10px;"><strong>جمع کل مبلغ قابل پرداخت:</strong></td>
                               <td><strong><?php echo htmlspecialchars(number_format($grand_total)); ?> تومان</strong></td>
                           </tr>
                           </tfoot>
                   </table>
               <?php else: ?>
                   <p>هیچ آیتمی برای این سفارش یافت نشد.</p>
               <?php endif; ?>

               <?php if (!empty($order['notes'])): ?>
                   <div class="footer-notes">
                       <strong>یادداشت مشتری:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                   </div>
               <?php endif; ?>
                <div class="footer-notes" style="text-align:center; margin-top:40px;">
                   <p>از خرید شما سپاسگزاریم.</p>
                   <button onclick="window.print();" class="no-print button-link" style="background-color:#007bff; margin-top:10px;">چاپ فاکتور</button>
               </div>
           <?php else: ?>
               <p>اطلاعات سفارش برای چاپ فاکتور یافت نشد.</p>
           <?php endif; ?>
       </div>
   </body>
   </html>
   