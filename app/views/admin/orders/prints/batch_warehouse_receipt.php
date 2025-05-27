<?php // ویو: app/views/admin/orders/prints/batch_warehouse_receipt.php ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'چاپ گروهی رسید انبار'); ?></title>
    <style>
        body { 
            font-family: 'Vazirmatn', 'Tahoma', sans-serif; 
            direction: rtl; 
            margin: 0; 
            padding: 0; 
            background-color: #fff; 
            font-size: 8pt; /* فونت بسیار کوچک برای جا شدن اطلاعات */
            line-height: 1.3; 
        }
        .page-container { /* برای شبیه‌سازی صفحه A4 */
            width: 210mm;
            height: 297mm; /* A4 Portrait */
            padding: 10mm; /* مارجین صفحه */
            box-sizing: border-box;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-content: flex-start; /* آیتم‌ها از بالا شروع شوند */
        }
        .receipt-cell {
            width: calc(50% - 5mm); /* دو ستون با کمی فاصله */
            height: calc(25% - 5mm); /* چهار ردیف با کمی فاصله */
            border: 1px solid #ccc;
            padding: 3mm;
            margin-bottom: 5mm; /* فاصله بین ردیف‌ها */
            box-sizing: border-box;
            overflow: hidden; /* جلوگیری از سرریز محتوا */
            display: flex;
            flex-direction: column;
        }
        .receipt-cell h4 { margin: 0 0 3px 0; font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 2px;}
        .receipt-cell p { margin: 2px 0; font-size: 0.9em;}
        .receipt-cell .items-list { list-style: none; padding-right: 10px; margin-top: 3px; font-size:0.85em; max-height: 50px; overflow-y:auto;}
        .receipt-cell .items-list li { margin-bottom: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
        .no-print { display: none; }

        @media print {
            body { 
                padding: 0; margin: 0; 
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
            .page-container {
                width: 100%;
                height: 100%;
                padding: 0; /* مارجین توسط @page کنترل می‌شود */
                box-shadow: none;
                border: none;
            }
            .receipt-cell {
                border: 1px dotted #aaa; /* مرز کمرنگ برای برش */
                 /* page-break-inside: avoid; // سعی در جلوگیری از شکستن سلول در صفحات مختلف */
            }
            .no-print { display: none !important; }
            .print-button-container { display: none !important; }
             @page {
                size: A4 portrait;
                margin: 10mm; /* مارجین صفحه چاپ */
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="print-button-container no-print" style="text-align:center; padding:10px; background-color:#f0f0f0;">
        <button onclick="window.print();" class="button-link" style="background-color:#007bff; padding: 10px 25px;">چاپ</button>
        <a href="<?php echo BASE_URL; ?>admin/orders" class="button-link button-secondary" style="margin-left: 10px;">بازگشت به لیست سفارشات</a>
    </div>

    <div class="page-container">
        <?php if (isset($data['orders']) && !empty($data['orders'])): ?>
            <?php 
            $order_count = 0;
            foreach ($data['orders'] as $order): 
                if ($order_count >= 8) break; // حداکثر ۸ رسید در هر صفحه
            ?>
                <div class="receipt-cell">
                    <h4>رسید انبار - سفارش #<?php echo htmlspecialchars($order['id']); ?></h4>
                    <p><strong>مشتری:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                    <p><strong>تاریخ:</strong> <?php echo htmlspecialchars(to_jalali_date($order['created_at'])); ?></p>
                    <p style="font-size:0.85em;"><strong>آدرس:</strong> <?php echo htmlspecialchars(substr($order['address'], 0, 70) . (strlen($order['address']) > 70 ? '...' : '')); ?></p>
                    
                    <?php if (isset($order['items']) && !empty($order['items'])): ?>
                        <p style="margin-top:5px; margin-bottom:2px; font-weight:bold;">اقلام:</p>
                        <ul class="items-list">
                            <?php foreach($order['items'] as $item): ?>
                                <li>
                                    <?php echo htmlspecialchars($item['quantity']); ?> عدد - 
                                    <?php echo htmlspecialchars(mb_substr($item['product_name'], 0, 30) . (mb_strlen($item['product_name']) > 30 ? '...' : '')); ?>
                                    <?php 
                                    // نمایش SKU در صورت وجود (نیاز به ارسال از کنترلر)
                                    // $sku_display_batch = $item['sku_from_variation_or_product'] ?? '';
                                    // if(!empty($sku_display_batch)) echo ' (SKU: ' . htmlspecialchars($sku_display_batch) . ')';
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                     <p style="margin-top:auto; border-top:1px dashed #ccc; padding-top:3px; font-size:0.8em;">امضاء انباردار: ..........................</p>
                </div>
            <?php 
            $order_count++;
            endforeach; 
            ?>
            <?php 
            // پر کردن خانه‌های خالی اگر کمتر از ۸ سفارش انتخاب شده باشد
            for ($i = $order_count; $i < 8; $i++): ?>
                <div class="receipt-cell" style="border: 1px dashed #eee; background-color:#fafafa;">
                    <p style="text-align:center; color:#bbb; margin-top:40%;"><i>(خالی)</i></p>
                </div>
            <?php endfor; ?>
        <?php else: ?>
            <p>هیچ سفارشی برای چاپ انتخاب نشده است.</p>
        <?php endif; ?>
    </div>
</body>
</html>
