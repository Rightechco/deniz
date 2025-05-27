<?php // ویو: app/views/admin/orders/prints/shipping_label.php ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'چاپ لیبل پستی'); ?></title>
    <style>
        body { 
            font-family: 'Vazirmatn', 'Tahoma', sans-serif; 
            direction: rtl; 
            margin: 0; 
            padding: 0; /* برای چاپ تمام صفحه */
            background-color: #fff; 
            font-size: 10pt; /* اندازه فونت مناسب برای لیبل */
            line-height: 1.5; 
        }
        .label-page-container { /* برای نمایش در مرورگر */
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .shipping-label {
            width: 100mm; /* عرض استاندارد لیبل پستی */
            height: 140mm; /* ارتفاع استاندارد لیبل پستی - یا مطابق با نیاز شما */
            border: 1px solid #333; 
            padding: 5mm; 
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden; /* جلوگیری از سرریز محتوا */
        }
        .label-section { 
            margin-bottom: 4mm; 
            padding-bottom: 3mm;
            border-bottom: 1px dashed #999;
        }
        .label-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .label-section h4 { 
            margin: 0 0 2mm 0; 
            font-size: 1.1em; /* کمی بزرگتر برای عناوین بخش */
            font-weight: bold;
            color: #000;
        }
        .label-section p { 
            margin: 1mm 0; 
            font-size: 0.95em;
        }
        .address-block { 
            font-size: 1em; /* فونت کمی بزرگتر برای آدرس */
            line-height: 1.6;
        }
        .order-id-prominent { 
            font-size: 1.3em; 
            font-weight: bold; 
            text-align: center; 
            margin-top: 3mm;
            padding: 2mm;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
        }
        .barcode-area { 
            text-align: center; 
            margin-top: 4mm; 
            min-height: 20mm; /* فضای کافی برای بارکد */
            /* border: 1px dashed #ccc; */ /* برای نمایش محدوده بارکد */
            /* padding: 2mm; */
            font-size:0.8em; 
        }
        .store-logo-small {
            max-width: 80px;
            max-height: 30px;
            float: left; /* یا display: inline-block و تنظیمات دیگر */
            margin-left: 5mm;
        }
        .print-button-container {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background-color: rgba(240,240,240,0.9);
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        .no-print { display: none; } /* برای مخفی کردن دکمه هنگام چاپ */

        @media print {
            body { 
                padding: 0; margin: 0; 
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
                font-size: 9pt; /* ممکن است برای چاپ نیاز به تنظیم دقیق‌تر باشد */
            }
            .label-page-container {
                padding: 0; /* حذف پدینگ در حالت چاپ */
                justify-content: flex-start; /* برای شروع از گوشه صفحه */
            }
            .shipping-label { 
                margin: 0; 
                border: none; /* حذف مرز در چاپ نهایی */
                box-shadow: none; 
                width: 100mm; /* اطمینان از اندازه صحیح در چاپ */
                height: 140mm;
                page-break-after: always; /* هر لیبل در یک صفحه */
            }
            .no-print, .print-button-container { 
                display: none !important; 
            }
            @page {
                size: 100mm 140mm; /* اندازه دقیق لیبل برای پرینتر */
                margin: 0; /* بدون مارجین برای استفاده کامل از صفحه لیبل */
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="print-button-container no-print">
        <button onclick="window.print();" class="button-link" style="background-color:#007bff; padding: 10px 25px; font-size:1.1em;">چاپ لیبل</button>
        <?php if (isset($data['order']) && $data['order']): ?>
        <a href="<?php echo BASE_URL; ?>admin/orderDetails/<?php echo $data['order']['id']; ?>" class="button-link button-secondary" style="margin-left: 10px;">بازگشت به جزئیات سفارش</a>
        <?php else: ?>
        <a href="<?php echo BASE_URL; ?>admin/orders" class="button-link button-secondary" style="margin-left: 10px;">بازگشت به لیست سفارشات</a>
        <?php endif; ?>
    </div>

    <div class="label-page-container">
        <?php if (isset($data['order']) && $data['order']): $order = $data['order']; $store = $data['store_info'] ?? []; ?>
            <div class="shipping-label">
                <div class="label-section sender-info">
                    <?php if (!empty($store['logo_url']) && filter_var($store['logo_url'], FILTER_VALIDATE_URL)): ?>
                       <img src="<?php echo htmlspecialchars($store['logo_url']); ?>" alt="لوگو" class="store-logo-small">
                    <?php endif; ?>
                    <h4>فرستنده:</h4>
                    <p><strong><?php echo htmlspecialchars($store['name'] ?? 'فروشگاه شما'); ?></strong></p>
                    <p class="address-block"><?php echo nl2br(htmlspecialchars($store['address'] ?? 'آدرس فروشگاه')); ?></p>
                    <p>تلفن: <?php echo htmlspecialchars($store['phone'] ?? 'تلفن فروشگاه'); ?></p>
                </div>
                
                <div class="label-section receiver-info">
                    <h4>گیرنده:</h4>
                    <p><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                    <p class="address-block"><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    <p>شهر: <?php echo htmlspecialchars($order['city']); ?> - کد پستی: <strong><?php echo htmlspecialchars($order['postal_code']); ?></strong></p>
                    <p>تلفن: <strong><?php echo htmlspecialchars($order['phone']); ?></strong></p>
                </div>
                
                <div class="order-id-prominent">
                    سفارش: #<?php echo htmlspecialchars($order['id']); ?>
                </div>
                
                <div class="barcode-area">
                    (محل بارکد رهگیری پستی)
                    <?php 
                    // برای تولید بارکد واقعی، نیاز به کتابخانه یا تصویر بارکد دارید
                    // مثال: echo '<img src="generate_barcode.php?code=' . htmlspecialchars($order['tracking_code'] ?? $order['id']) . '" alt="Barcode">';
                    ?>
                </div>
            </div>
        <?php else: ?>
            <p style="text-align:center; width:100%;">اطلاعات سفارش برای چاپ لیبل پستی یافت نشد.</p>
        <?php endif; ?>
    </div>
</body>
</html>
