<?php // ویو: app/views/auth/register.php ?>

<h2>ثبت نام کاربر جدید</h2>
<p>لطفا فرم زیر را برای ایجاد حساب کاربری تکمیل کنید.</p>

<?php echo flash('register_success'); // نمایش پیام موفقیت ثبت نام اگر وجود داشته باشد ?>

<form action="<?php echo BASE_URL; ?>auth/register" method="post">
    <div>
        <label for="first_name">نام: <sup>*</sup></label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($data['first_name']); ?>">
        <span><?php echo $data['first_name_err']; ?></span>
    </div>
    <div>
        <label for="last_name">نام خانوادگی: <sup>*</sup></label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($data['last_name']); ?>">
        <span><?php echo $data['last_name_err']; ?></span>
    </div>
    <div>
        <label for="username">نام کاربری: <sup>*</sup></label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($data['username']); ?>">
        <span><?php echo $data['username_err']; ?></span>
    </div>
    <div>
        <label for="email">ایمیل: <sup>*</sup></label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>">
        <span><?php echo $data['email_err']; ?></span>
    </div>
    <div>
        <label for="password">رمز عبور: <sup>*</sup></label>
        <input type="password" name="password" value="<?php echo htmlspecialchars($data['password']); ?>">
        <span><?php echo $data['password_err']; ?></span>
    </div>
    <div>
        <label for="confirm_password">تکرار رمز عبور: <sup>*</sup></label>
        <input type="password" name="confirm_password" value="<?php echo htmlspecialchars($data['confirm_password']); ?>">
        <span><?php echo $data['confirm_password_err']; ?></span>
    </div>
    <div>
        <input type="submit" value="ثبت نام">
    </div>
</form>
<p>قبلا ثبت نام کرده‌اید؟ <a href="<?php echo BASE_URL; ?>auth/login">وارد شوید</a></p>