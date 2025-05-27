<?php
// app/views/layouts/header.php

// اطمینان از شروع سشن (اگر قبلاً شروع نشده باشد)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] . ' - ' . (defined('SITE_NAME') ? SITE_NAME : 'فروشگاه من') : (defined('SITE_NAME') ? SITE_NAME : 'فروشگاه من')); ?></title>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/kamadatepicker.min.css">
    
    <style>
        /* حداقل استایل برای جلوگیری از به هم ریختگی کامل */
        body { 
            direction: rtl;
            font-family: Tahoma, Arial, sans-serif; /* یک فونت پیش‌فرض ساده */
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
        }
        header.main-header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        header.main-header h1 a {
            color: white;
            text-decoration: none;
        }
        nav.main-nav ul {
            list-style: none;
            padding: 0;
            text-align: center;
        }
        nav.main-nav ul li {
            display: inline;
            margin-right: 20px;
        }
        nav.main-nav ul li a {
            color: white;
            text-decoration: none;
        }
        .button-link {
            display: inline-block;
            padding: 8px 15px;
            background-color: #5cb85c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
        .button-warning { background-color: #f0ad4e;}
        .button-danger { background-color: #d9534f;}
        .button-secondary { background-color: #777;}
        .button-info { background-color: #5bc0de;}

        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .alert-danger { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
        .alert-info { color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }
        .alert-warning { color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc; }

    </style>
</head>
<body>
    <header class="main-header">
        <h1><a href="<?php echo BASE_URL; ?>"><?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'فروشگاه آنلاین'); ?></a></h1>
        <nav class="main-nav">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>products" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'products') ? 'active' : ''; ?>">محصولات</a></li>
                <li><a href="<?php echo BASE_URL; ?>cart/index" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'cart') ? 'active' : ''; ?>">سبد خرید (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a></li>
                
               <?php if (isset($_SESSION['user_id'])): ?>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
        <li><a href="<?php echo BASE_URL; ?>admin/products" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'admin_dashboard') ? 'active' : ''; ?>">پنل ادمین</a></li>
    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'vendor'): ?>
        <li><a href="<?php echo BASE_URL; ?>vendor/dashboard" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'vendor_dashboard') ? 'active' : ''; ?>">پنل فروشنده</a></li>
    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'affiliate'): ?>
        <li><a href="<?php echo BASE_URL; ?>affiliate/dashboard" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'affiliate_dashboard') ? 'active' : ''; ?>">پنل همکاری</a></li> <?php endif; ?>
    <li><a href="<?php echo BASE_URL; ?>customer/orders" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'customer_orders') ? 'active' : ''; ?>">سفارشات من</a></li>
    <li><a href="<?php echo BASE_URL; ?>auth/logout">خروج (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
<?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>auth/login" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'login') ? 'active' : ''; ?>">ورود</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/register" class="<?php echo (isset($data['active_nav']) && $data['active_nav'] == 'register') ? 'active' : ''; ?>">ثبت نام</a></li>
    <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <main>
            <?php // محتوای اصلی صفحات در اینجا توسط متد view از Controller بارگذاری می‌شود ?>
