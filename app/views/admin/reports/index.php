<?php // ویو: app/views/admin/reports/index.php ?>

<h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'گزارشات و خروجی اکسل'); ?></h1>

<p>از این بخش می‌توانید برای داده‌های مختلف فروشگاه با فیلتر زمانی، خروجی اکسل (XLSX) تهیه کنید.</p>

<?php 
flash('report_message'); // برای پیام‌های مربوط به خروجی (مثلاً "هیچ داده‌ای یافت نشد")
flash('error_message');  // برای خطاهای عمومی
?>

<div class="report-section" style="margin-bottom: 30px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px; background-color: #f9f9f9;">
    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">خروجی محصولات</h2>
    <form action="<?php echo BASE_URL; ?>admin/exportProducts" method="post" target="_blank">
        <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
            <div style="flex:1; min-width:200px;">
                <label for="products_start_date">از تاریخ (ایجاد محصول):</label>
                <input type="text" name="start_date" id="products_start_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/01" autocomplete="off">
            </div>
            <div style="flex:1; min-width:200px;">
                <label for="products_end_date">تا تاریخ (ایجاد محصول):</label>
                <input type="text" name="end_date" id="products_end_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/31" autocomplete="off">
            </div>
        </div>
        <button type="submit" class="button-link" style="background-color:#198754;">تهیه خروجی اکسل محصولات</button>
    </form>
</div>

<div class="report-section" style="margin-bottom: 30px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px; background-color: #f9f9f9;">
    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">خروجی سفارشات</h2>
    <form action="<?php echo BASE_URL; ?>admin/exportOrders" method="post" target="_blank">
        <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
            <div style="flex:1; min-width:200px;">
                <label for="orders_start_date">از تاریخ (ثبت سفارش):</label>
                <input type="text" name="start_date" id="orders_start_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/01" autocomplete="off">
            </div>
            <div style="flex:1; min-width:200px;">
                <label for="orders_end_date">تا تاریخ (ثبت سفارش):</label>
                <input type="text" name="end_date" id="orders_end_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/31" autocomplete="off">
            </div>
            <div style="flex:1; min-width:200px;">
                <label for="orders_status_filter">فیلتر وضعیت سفارش:</label>
                <select name="order_status" id="orders_status_filter" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="pending_confirmation">در انتظار تایید</option>
                    <option value="processing">در حال پردازش</option>
                    <option value="shipped">ارسال شده</option>
                    <option value="delivered">تحویل داده شده</option>
                    <option value="cancelled">لغو شده</option>
                    <option value="refunded">مرجوع شده</option>
                </select>
            </div>
        </div>
        <button type="submit" class="button-link" style="background-color:#198754;">تهیه خروجی اکسل سفارشات</button>
    </form>
</div>

<div class="report-section" style="margin-bottom: 30px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px; background-color: #f9f9f9;">
    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">خروجی کمیسیون‌های فروشگاه</h2>
    <form action="<?php echo BASE_URL; ?>admin/exportPlatformCommissions" method="post" target="_blank">
        <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
            <div style="flex:1; min-width:200px;">
                <label for="pcomm_start_date">از تاریخ (ایجاد سفارش):</label>
                <input type="text" name="start_date" id="pcomm_start_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/01" autocomplete="off">
            </div>
            <div style="flex:1; min-width:200px;">
                <label for="pcomm_end_date">تا تاریخ (ایجاد سفارش):</label>
                <input type="text" name="end_date" id="pcomm_end_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/31" autocomplete="off">
            </div>
        </div>
        <button type="submit" class="button-link" style="background-color:#198754;">تهیه خروجی اکسل کمیسیون‌ها</button>
    </form>
</div>

<div class="report-section" style="margin-bottom: 30px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px; background-color: #f9f9f9;">
    <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">خروجی درخواست‌های پرداخت فروشندگان</h2>
    <form action="<?php echo BASE_URL; ?>admin/exportVendorPayouts" method="post" target="_blank">
        <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
            <div style="flex:1; min-width:200px;">
                <label for="vpayout_start_date">از تاریخ درخواست:</label>
                <input type="text" name="start_date" id="vpayout_start_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/01" autocomplete="off">
            </div>
            <div style="flex:1; min-width:200px;">
                <label for="vpayout_end_date">تا تاریخ درخواست:</label>
                <input type="text" name="end_date" id="vpayout_end_date" class="jalali-datepicker" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;" placeholder="مثال: 1403/01/31" autocomplete="off">
            </div>
            <div style="flex:1; min-width:200px;">
                <label for="vpayout_status_filter">فیلتر وضعیت درخواست:</label>
                <select name="payout_status" id="vpayout_status_filter" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width:100%;">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="requested">درخواست شده</option>
                    <option value="processing">در حال پردازش</option>
                    <option value="completed">تکمیل شده</option>
                    <option value="rejected">رد شده</option>
                    <option value="cancelled_by_vendor">لغو شده توسط فروشنده</option>
                </select>
            </div>
        </div>
        <button type="submit" class="button-link" style="background-color:#198754;">تهیه خروجی اکسل درخواست‌های پرداخت</button>
    </form>
</div>

<p style="margin-top: 30px;">
    <a href="<?php echo BASE_URL; ?>admin/dashboard" class="button-link button-secondary">بازگشت به داشبورد ادمین</a>
</p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // console.log('JS: DOMContentLoaded event fired for reports page.'); 

    if (typeof kamaDatepicker === 'function') {
        // console.log('JS: kamaDatepicker function IS available.');
        
        var datepickerInputs = document.querySelectorAll('.jalali-datepicker');
        // console.log('JS: Found ' + datepickerInputs.length + ' elements with class .jalali-datepicker');

        datepickerInputs.forEach(function(inputElement, index) {
            if (inputElement.id) {
                // console.log('JS: Initializing kamaDatepicker for input ID: ' + inputElement.id); 
                try {
                    kamaDatepicker(inputElement.id, {
                        twodigit: true,
                        closeAfterSelect: true,
                        placeholder: inputElement.getAttribute('placeholder') || "YYYY/MM/DD",
                        // nextButtonIcon: "<?php echo BASE_URL; ?>images/datepicker_next.png", 
                        // previousButtonIcon: "<?php echo BASE_URL; ?>images/datepicker_prev.png",
                        buttonsColor: "royalblue",
                        forceFarsiDigits: true,
                        markToday: true,
                        markHolidays: false, // برای سادگی فعلاً غیرفعال
                        highlightSelectedDay: true,
                        syncField: true, // مقدار فیلد با انتخاب همگام شود
                        gotoToday: true
                    });
                    // console.log('JS: kamaDatepicker initialized successfully for ID: ' + inputElement.id); 
                } catch (e) {
                    console.error('JS Error: Failed to initialize kamaDatepicker for ID: ' + inputElement.id, e); 
                }
            } else {
                console.warn('JS Warning: Input element at index ' + index + ' with class .jalali-datepicker is missing an ID.'); 
            }
        });
    } else {
        console.error('JS Error: kamaDatepicker function is NOT available. Make sure the library is loaded before this script.');
    }
});
</script>
