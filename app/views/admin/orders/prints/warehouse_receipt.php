<?php // ویو: app/views/admin/orders/prints/warehouse_receipt.php ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'رسید انبار'); ?></title>
    <style>
        body { 
            font-family: 'Vazirmatn', 'Tahoma', sans-serif; 
            direction: rtl; 
            margin: 0; 
            padding: 15px; 
            background-color: #fff; 
            font-size: 11px; /* فونت کوچکتر برای چاپ مناسب‌تر */
            line-height: 1.5; 
        }
        .receipt-box { 
            max-width: 800px; /* یا عرض استاندارد کاغذ A4 در حالت عمودی */
            margin: auto; 
            padding: 15px; 
            border: 1px solid #ccc; 
        }
        .receipt-header { 
            text-align: center; 
            margin-bottom: 15px; 
        }
        .receipt-header h2 { 
            margin: 0 0 5px 0; 
            font-size: 1.6em; /* کمی بزرگتر */
        }
        .receipt-header p {
            margin: 3px 0;
            font-size: 0.95em;
        }
        .order-info p { 
            margin: 4px 0; 
            font-size: 0.95em; 
        }
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        .items-table th, .items-table td { 
            border: 1px solid #999; 
            padding: 6px 8px; /* پدینگ کمی بیشتر */
            text-align: right; 
        }
        .items-table th { 
            background-color: #f0f0f0; 
            font-weight: bold; 
        }
        .items-table td.product-name {
            min-width: 200px; /* برای جلوگیری از شکستن زیاد نام محصول */
        }
        .text-center { 
            text-align: center !important; 
        }
        .notes-section { 
            margin-top: 20px; 
            padding-top:10px; 
            border-top: 1px dashed #ccc; 
            font-size:0.9em;
        }
        .signature-section { 
            margin-top: 40px; 
            display: flex; 
            justify-content: space-around; 
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .signature-section div { 
            text-align: center; 
            width: 40%; 
        }
        .signature-section p {
            margin-bottom: 40px; /* فضای بیشتر برای امضا */
        }
        .no-print { 
            display: none; 
        }

        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                padding:10mm; /* مارجین برای چاپ */
                margin: 0;
                font-size: 10pt; /* اندازه فونت مناسب برای چاپ */
            }
            .receipt-box { 
                box-shadow: none; 
                border: none; 
                margin: 0; 
                max-width: 100%; 
                padding:0;
            }
            .no-print { 
                display: none !important; 
            }
            .button-link { /* مخفی کردن دکمه‌ها هنگام چاپ */
                display: none !important;
            }
             @page {
                size: A4 portrait; /* یا landscape بسته به نیاز */
                margin: 10mm;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="receipt-box">
        <?php if (isset($data['order']) && $data['order']): $order = $data['order']; $store = $data['store_info'] ?? []; ?>
            <div class="receipt-header">
                <p>سفارش شماره: #<?php echo htmlspecialchars($order['id']); ?></p>
                <p>تاریخ ثبت سفارش: <?php echo htmlspecialchars(to_jalali_datetime($order['created_at'])); // استفاده از تابع شمسی ساز ?></p>
                <?php if (isset($store['name']) && !empty($store['name'])): ?>
                    <p>فروشگاه: <?php echo htmlspecialchars($store['name']); ?></p>
                <?php endif; ?>
            </div>

            <div class="order-info" style="margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #eee;">
                <p><strong>نام مشتری:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                <p><strong>آدرس ارسال:</strong> <?php echo htmlspecialchars($order['address'] . '، ' . $order['city'] . ' - کدپستی: ' . $order['postal_code']); ?></p>
                <p><strong>تلفن تماس:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                <p><strong>روش ارسال (پیشنهادی):</strong> <em>[این بخش را می‌توانید بر اساس روش ارسال سفارش یا یادداشت‌های انبار پر کنید]</em></p>
            </div>

            <h4>لیست محصولات جهت جمع‌آوری:</h4>
            <?php if (isset($order['items']) && !empty($order['items'])): ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:5%;">ردیف</th>
                            <th style="width:40%;">نام محصول / تنوع</th>
                            <th class="text-center" style="width:15%;">SKU (اگر دارد)</th>
                            <th class="text-center" style="width:10%;">تعداد</th>
                            <th class="text-center" style="width:15%;">محل در انبار (اختیاری)</th>
                            <th class="text-center" style="width:15%;">چک شد؟</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $item_counter_wh = 1; ?>
                        <?php foreach($order['items'] as $item): ?>
                            <?php 
                            // برای نمایش SKU تنوع اگر وجود دارد، در غیر این صورت SKU محصول والد
                            // این بخش نیاز به ارسال اطلاعات SKU از کنترلر دارد.
                            // فرض می‌کنیم $item['sku'] (اگر از JOIN با products/product_variations آمده) یا یک مقدار پیش‌فرض داریم.
                            $sku_to_display = $item['sku_from_variation_or_product'] ?? '-'; // این کلید باید در کنترلر یا مدل تنظیم شود
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $item_counter_wh++; ?></td>
                                <td class="product-name"><?php echo htmlspecialchars(isset($item['product_name']) ? $item['product_name'] : ''); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($sku_to_display); ?></td>
                                <td class="text-center" style="font-weight:bold; font-size:1.2em;"><?php echo htmlspecialchars(isset($item['quantity']) ? $item['quantity'] : ''); ?></td>
                                <td style="height: 2.5em;"></td> <td style="width:70px; height: 2.5em;"></td> </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>هیچ آیتمی برای این سفارش یافت نشد.</p>
            <?php endif; ?>

            <?php if (!empty($order['notes'])): ?>
               <div class="notes-section">
                   <strong>یادداشت مشتری برای این سفارش:</strong>
                   <p style="background-color:#fff8e1; padding:10px; border:1px solid #ffe082; border-radius:4px; margin-top:5px;"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
               </div>
            <?php endif; ?>

             <div style="text-align:center; margin-top:30px;" class="no-print">
                <button onclick="window.print();" class="button-link" style="background-color:#007bff; padding: 10px 25px;">چاپ رسید انبار</button>
                <a href="<?php echo BASE_URL; ?>admin/orderDetails/<?php echo $order['id']; ?>" class="button-link button-secondary" style="margin-left: 10px;">بازگشت به جزئیات سفارش</a>
            </div>

        <?php else: ?>
            <p>اطلاعات سفارش برای چاپ رسید انبار یافت نشد.</p>
            <p style="margin-top: 20px;">
                 <a href="<?php echo BASE_URL; ?>admin/orders" class="button-link button-secondary">بازگشت به لیست سفارشات</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
