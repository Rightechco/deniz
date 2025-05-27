<?php // ویو: app/views/auth/login.php ?>

<h2>ورود به حساب کاربری</h2>
<p>لطفا اطلاعات کاربری خود را وارد کنید.</p>

<?php echo flash('register_success'); // اگر کاربر از صفحه ثبت نام به اینجا ریدایرکت شده ?>
<?php echo flash('logout_success'); // اگر کاربر از سیستم خارج شده ?>
<?php if (!empty($data['login_err'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($data['login_err']); ?></div>
<?php endif; ?>


<form action="<?php echo BASE_URL; ?>auth/login" method="post">
    <div>
        <label for="email_username">ایمیل یا نام کاربری: <sup>*</sup></label>
        <input type="text" name="email_username" value="<?php echo htmlspecialchars($data['email_username']); ?>">
        <span><?php echo $data['email_username_err']; ?></span>
    </div>
    <div>
        <label for="password">رمز عبور: <sup>*</sup></label>
        <input type="password" name="password" value="<?php echo htmlspecialchars($data['password']); ?>">
        <span><?php echo $data['password_err']; ?></span>
    </div>
    <div>
        <input type="submit" value="ورود">
    </div>
</form>
<p>هنوز حساب کاربری ندارید؟ <a href="<?php echo BASE_URL; ?>auth/register">ثبت نام کنید</a></p>