    <?php // ویو: app/views/checkout/index.php ?>

    <h1><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] : 'تکمیل اطلاعات پرداخت'); ?></h1>

    <?php
    // نمایش تمام فلش مسیج‌هایی که ممکن است تنظیم شده باشند
    $flash_messages = [
        'checkout_form_error', 'order_fail', 'checkout_stock_issue', 
        'cart_empty', 'error_message', 'checkout_login_required'
    ];
    foreach ($flash_messages as $flash_name) {
        flash($flash_name); // تابع flash() خودش بررسی می‌کند که آیا پیام وجود دارد یا خیر
    }
    ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px;">

        <div style="flex: 2; min-width: 300px; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
            <h2>اطلاعات ارسال و پرداخت</h2>
            <form action="<?php echo BASE_URL; ?>checkout/index" method="post">
                <fieldset style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <legend style="padding: 0 10px; font-weight: bold;">اطلاعات شخصی</legend>
                    <div style="margin-bottom: 15px;">
                        <label for="first_name">نام: <sup>*</sup></label>
                        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars(isset($data['first_name']) ? $data['first_name'] : ''); ?>" required style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['first_name_err']) ? $data['first_name_err'] : ''; ?></span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="last_name">نام خانوادگی: <sup>*</sup></label>
                        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars(isset($data['last_name']) ? $data['last_name'] : ''); ?>" required style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['last_name_err']) ? $data['last_name_err'] : ''; ?></span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="email">ایمیل: <sup>*</sup></label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars(isset($data['email']) ? $data['email'] : ''); ?>" required style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['email_err']) ? $data['email_err'] : ''; ?></span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="phone">شماره تلفن: <sup>*</sup></label>
                        <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars(isset($data['phone']) ? $data['phone'] : ''); ?>" required style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['phone_err']) ? $data['phone_err'] : ''; ?></span>
                    </div>
                </fieldset>

                <fieldset style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <legend style="padding: 0 10px; font-weight: bold;">آدرس ارسال</legend>
                    <div style="margin-bottom: 15px;">
                        <label for="address">آدرس کامل: <sup>*</sup></label>
                        <textarea name="address" id="address" rows="3" required style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;"><?php echo htmlspecialchars(isset($data['address']) ? $data['address'] : ''); ?></textarea>
                        <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['address_err']) ? $data['address_err'] : ''; ?></span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="city">شهر: <sup>*</sup></label>
                        <input type="text" name="city" id="city" value="<?php echo htmlspecialchars(isset($data['city']) ? $data['city'] : ''); ?>" required style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['city_err']) ? $data['city_err'] : ''; ?></span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="postal_code">کد پستی: <sup>*</sup></label>
                        <input type="text" name="postal_code" id="postal_code" value="<?php echo htmlspecialchars(isset($data['postal_code']) ? $data['postal_code'] : ''); ?>" required style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['postal_code_err']) ? $data['postal_code_err'] : ''; ?></span>
                    </div>
                </fieldset>

                <fieldset style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <legend style="padding: 0 10px; font-weight: bold;">روش پرداخت</legend>
                    <div style="margin-bottom: 10px;">
                        <input type="radio" name="payment_method" id="payment_cod" value="cod" <?php echo (isset($data['payment_method']) && $data['payment_method'] == 'cod') ? 'checked' : ''; ?> required>
                        <label for="payment_cod">پرداخت در محل (COD)</label>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <input type="radio" name="payment_method" id="payment_online" value="online" <?php echo (isset($data['payment_method']) && $data['payment_method'] == 'online') ? 'checked' : ''; ?> disabled> 
                        <label for="payment_online" style="color:#aaa;">پرداخت آنلاین (به زودی)</label>
                    </div>
                     <span class="error-text" style="color: red; font-size: 0.9em; display: block;"><?php echo isset($data['payment_method_err']) ? $data['payment_method_err'] : ''; ?></span>
                </fieldset>

                <fieldset style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <legend style="padding: 0 10px; font-weight: bold;">یادداشت سفارش (اختیاری)</legend>
                    <div>
                        <textarea name="notes" id="notes" rows="3" style="width: calc(100% - 22px); padding: 10px; border: 1px solid #ccc; border-radius: 4px;"><?php echo htmlspecialchars(isset($data['notes']) ? $data['notes'] : ''); ?></textarea>
                    </div>
                </fieldset>

                <div style="margin-top: 30px;">
                    <button type="submit" name="place_order" class="button-link" style="background-color: #28a745; padding: 12px 25px; font-size: 1.1em;">ثبت سفارش</button>
                </div>
            </form>
        </div>

        <div style="flex: 1; min-width: 280px; background-color: #f9f9f9; padding: 20px; border-radius: 5px; align-self: flex-start; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
            <h3>خلاصه سفارش شما</h3>
            <?php if (isset($data['cart_items']) && !empty($data['cart_items']) && is_array($data['cart_items'])): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach($data['cart_items'] as $item_cart_id => $item): ?>
                        <?php if (is_array($item) && isset($item['name']) && isset($item['quantity']) && isset($item['price'])): ?>
                            <li style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; display: flex; align-items: center;">
                                <?php 
                                $checkout_item_image_path = (!empty($item['image_url'])) ? BASE_URL . htmlspecialchars($item['image_url']) : BASE_URL . 'images/placeholder.png';
                                ?>
                                <img src="<?php echo $checkout_item_image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border-radius: 3px;">
                                <div style="flex-grow: 1;">
                                    <?php echo htmlspecialchars($item['name']); ?> (<?php echo htmlspecialchars((int)$item['quantity']); ?> عدد)
                                </div>
                                <div style="font-weight: bold; white-space: nowrap;">
                                    <?php echo htmlspecialchars(number_format((float)$item['price'] * (int)$item['quantity'])); ?> ت
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <p style="font-size: 1.2em; font-weight: bold; text-align: left;">
                    جمع کل: <?php echo htmlspecialchars(isset($data['total_price']) ? number_format((float)$data['total_price']) : '0'); ?> تومان
                </p>
            <?php else: ?>
                <p>سبد خرید شما خالی است یا مشکلی در بارگذاری آیتم‌ها وجود دارد.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php // تگ پایانی PHP را از انتهای فایل حذف کنید اگر این آخرین چیز در فایل است ?>
    