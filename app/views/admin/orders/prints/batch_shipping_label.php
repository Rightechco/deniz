<?php // ویو: app/views/admin/orders/prints/batch_shipping_label.php ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'چاپ گروهی لیبل پستی'); ?></title>
    <style>
        body { 
            font-family: 'Vazirmatn', 'Tahoma', Arial, sans-serif; 
            direction: rtl; 
            margin: 0; 
            padding: 0; 
            background-color: #fff; 
            font-size: 9pt; /* اندازه فونت پایه برای لیبل‌ها */
            line-height: 1.4; 
        }
        .page-container-batch {
            width: 210mm; /* عرض A4 */
            min-height: 297mm; /* ارتفاع A4 - استفاده از min-height برای نمایش بهتر در مرورگر */
            padding: 5mm; /* مارجین کلی صفحه برای نمایش در مرورگر */
            box-sizing: border-box;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between; /* توزیع مساوی با فاصله */
            align-content: flex-start; 
            margin: 0 auto; /* مرکز کردن در مرورگر */
        }
        .label-wrapper {
            width: calc(50% - 5mm); /* دو ستون با 5mm فاصله بین آنها و 5mm مارجین از طرفین */
            height: calc(25% - 5mm); /* چهار ردیف با 5mm فاصله بین آنها و 5mm مارجین از بالا/پایین */
            /* ابعاد دقیق‌تر برای لیبل (مثلاً 95mm x 68mm با احتساب مارجین‌ها) */
            /* width: 95mm; */
            /* height: 68mm; */
            border: 1px dotted #b0b0b0; /* مرز نقطه‌چین برای راهنمای برش */
            padding: 3mm; 
            margin-bottom: 5mm; 
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* توزیع محتوا در ارتفاع لیبل */
            background-color: #fff; /* اطمینان از پس‌زمینه سفید */
        }
        .label-section { 
            margin-bottom: 2mm; 
            padding-bottom: 1.5mm;
            border-bottom: 1px dashed #ccc;
        }
        .label-section:last-of-type { /* آخرین بخش در هر لیبل */
            border-bottom: none;
            margin-bottom: 0;
        }
        .label-section h4 { 
            margin: 0 0 1mm 0; 
            font-size: 1.05em; 
            font-weight: bold;
            color: #000;
        }
        .label-section p { 
            margin: 0.5mm 0; 
            font-size: 0.9em;
        }
        .address-block { 
            font-size: 0.95em; 
            line-height: 1.5;
            max-height: 3.5em; /* محدودیت ارتفاع برای آدرس */
            overflow: hidden;
        }
        .order-id-prominent-batch { 
            font-size: 1.1em; 
            font-weight: bold; 
            text-align: center; 
            margin-top: 2mm;
            padding: 1mm 0; 
            border-top: 1px solid #777;
            border-bottom: 1px solid #777;
        }
        .store-logo-small-batch {
            max-width: 60px; /* لوگوی کوچکتر برای لیبل */
            max-height: 20px;
            float: left; 
            margin: 0 0 1mm 3mm;
        }
        .print-button-container-batch {
            text-align:center; 
            padding:15px; 
            background-color:#f0f0f0;
            border-bottom: 1px solid #ccc;
        }
        .no-print { display: none; }

        @media print {
            body { 
                padding: 0; margin: 0; 
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
                font-size: 8pt; /* ممکن است برای چاپ نیاز به تنظیم دقیق‌تر باشد */
            }
            .print-button-container-batch { display: none !important; }
            .page-container-batch {
                padding: 0; /* حذف پدینگ در حالت چاپ */
                margin:0;
                width: 100%;
                height: 100%;
                justify-content: space-between;
            }
            .label-wrapper {
                border: 1px dotted #ccc; /* مرز کمرنگ برای برش */
                width: calc(50% - 2.5mm); /* دو ستون با 5mm فاصله کلی بین آنها */
                height: calc(25% - 2.5mm); /* چهار ردیف با 5mm فاصله کلی بین آنها */
                margin: 0; /* حذف مارجین خود سلول‌ها، فاصله توسط justify-content و align-content */
                margin-bottom: 2mm; /* فاصله عمودی بین ردیف لیبل‌ها */
                page-break-inside: avoid !important; /* تلاش برای جلوگیری از شکستن لیبل در صفحات */
            }
             .label-wrapper:nth-child(2n) { /* برای ستون دوم، مارجین راست نمی‌خواهیم اگر space-between داریم */
                /* margin-right: 0; */
            }
             .label-wrapper:nth-child(n+3) { /* برای ردیف‌های بعدی */
                /* margin-top: 5mm; */
            }


            @page {
                size: A4 portrait;
                margin: 7mm; /* مارجین کلی صفحه چاپ برای اطمینان از جا شدن همه چیز */
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="print-button-container-batch no-print">
        <button onclick="window.print();" class="button-link" style="background-color:#007bff; padding: 10px 25px; font-size:1.1em;">چاپ همه لیبل‌ها</button>
        <a href="<?php echo BASE_URL; ?>admin/orders" class="button-link button-secondary" style="margin-left: 10px;">بازگشت به لیست سفارشات</a>
    </div>

    <div class="page-container-batch">
        <?php if (isset($data['orders']) && !empty($data['orders'])): ?>
            <?php 
            $order_print_count = 0;
            foreach ($data['orders'] as $order): 
                if ($order_print_count >= 8) break; // اطمینان از اینکه حداکثر ۸ لیبل پردازش می‌شود
                $store = $data['store_info'] ?? []; 
            ?>
                <div class="label-wrapper">
                    <div class="label-section sender-info">
                        <?php if (!empty($store['logo_url']) && filter_var($store['logo_url'], FILTER_VALIDATE_URL)): ?>
                           <img src="<?php echo htmlspecialchars($store['logo_url']); ?>" alt="لوگو" class="store-logo-small-batch">
                        <?php endif; ?>
                        <h4>فرستنده:</h4>
                        <p><strong><?php echo htmlspecialchars($store['name'] ?? 'فروشگاه شما'); ?></strong></p>
                        <p class="address-block" title="<?php echo htmlspecialchars($store['address'] ?? 'آدرس فروشگاه'); ?>"><?php echo nl2br(htmlspecialchars(mb_substr($store['address'] ?? 'آدرس فروشگاه', 0, 60) . (mb_strlen($store['address'] ?? '') > 60 ? '...' : ''))); ?></p>
                        <p>تلفن: <?php echo htmlspecialchars($store['phone'] ?? 'تلفن فروشگاه'); ?></p>
                    </div>
                    
                    <div class="label-section receiver-info">
                        <h4>گیرنده:</h4>
                        <p><strong><?php echo htmlspecialchars(isset($order['first_name']) ? $order['first_name'] . ' ' . $order['last_name'] : ''); ?></strong></p>
                        <p class="address-block" title="<?php echo htmlspecialchars($order['address']); ?>"><?php echo nl2br(htmlspecialchars(mb_substr($order['address'], 0, 70) . (mb_strlen($order['address']) > 70 ? '...' : ''))); ?></p>
                        <p>شهر: <?php echo htmlspecialchars($order['city']); ?> - کد پستی: <strong><?php echo htmlspecialchars($order['postal_code']); ?></strong></p>
                        <p>تلفن: <strong><?php echo htmlspecialchars($order['phone']); ?></strong></p>
                    </div>
                    
                    <div class="order-id-prominent-batch">
                        سفارش: #<?php echo htmlspecialchars($order['id']); ?>
                    </div>
                    
                    </div>
            <?php 
            $order_print_count++;
            endforeach; 
            ?>
            <?php 
            // پر کردن خانه‌های خالی اگر کمتر از ۸ سفارش انتخاب شده باشد تا چیدمان حفظ شود
            for ($i = $order_print_count; $i < 8; $i++): ?>
                <div class="label-wrapper" style="border-style: none; background-color:transparent;">
                    </div>
            <?php endfor; ?>
        <?php else: ?>
            <div style="width:100%; text-align:center; padding:20px;">
                 <p>هیچ سفارشی برای چاپ لیبل انتخاب نشده است یا اطلاعات سفارشات یافت نشد.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
