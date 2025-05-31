<?php
// app/views/layouts/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page_url = $_GET['url'] ?? '';
$user_logged_in = isset($_SESSION['user_id']);
$user_name = '';
if ($user_logged_in) {
    $user_name = $_SESSION['user_first_name'] ?? $_SESSION['username'] ?? 'کاربر';
}
$cart_item_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item_cart_id => $item_details) {
        if (isset($item_details['quantity'])) {
            $cart_item_count += (int)$item_details['quantity'];
        }
    }
}
// این بخش برای منوی دسته‌بندی‌ها است، باید در کنترلر مربوطه (مثلاً PagesController یا یک BaseController) واکشی شود
// $menu_categories = $this->categoryModel->getParentCategoriesWithChildren(); 
// در اینجا به عنوان مثال، فرض می‌کنیم از $data پاس داده شده است.
$menu_categories_data = $data['menu_categories'] ?? []; 
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(isset($data['pageTitle']) ? $data['pageTitle'] . ' | ' . (defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما') : (defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما')); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" xintegrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=<?php echo time(); // برای جلوگیری از کش شدن ?>">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'vazir': ['Vazirmatn', 'sans-serif'],
                    },
                    colors: {
                        primary: { 
                            light: '#60a5fa', // blue-400
                            DEFAULT: '#3b82f6', // blue-500
                            dark: '#1d4ed8', // blue-700
                        },
                        secondary: { 
                            light: '#5eead4', // teal-300
                            DEFAULT: '#14b8a6', // teal-500
                            dark: '#0f766e', // teal-700
                        },
                        accent: { 
                            light: '#fbbf24', // amber-400
                            DEFAULT: '#f59e0b', // amber-500
                            dark: '#b45309', // amber-700
                        },
                        neutral: {
                            lightest: '#f9fafb', // gray-50
                            light: '#f3f4f6', // gray-100
                            DEFAULT: '#e5e7eb', // gray-200
                            medium: '#9ca3af', // gray-400
                            dark: '#4b5563', // gray-600
                            darkest: '#1f2937' // gray-800
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Vazirmatn', Tahoma, sans-serif; background-color: theme('colors.neutral.lightest'); color: theme('colors.neutral.darkest');}
        .swiper-button-next, .swiper-button-prev { color: theme('colors.primary.DEFAULT'); }
        .swiper-pagination-bullet-active { background-color: theme('colors.primary.DEFAULT'); }
        html { scroll-behavior: smooth; }
        .nav-link-active { color: theme('colors.primary.DEFAULT'); font-weight: 600; /* border-bottom: 2px solid theme('colors.primary.DEFAULT'); */ }
        .dropdown:hover .dropdown-menu { display: block; }
        .dropdown-menu { display: none; }
        /* Animation for slide up */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slideUp 0.5s ease-out forwards; }
        .animation-delay-200 { animation-delay: 0.2s; }
        .animation-delay-400 { animation-delay: 0.4s; }
    </style>
</head>
<body class="antialiased">
    <header class="bg-white shadow-md sticky top-0 z-50 transition-all duration-300 ease-in-out" id="mainHeader">
        <div class="container mx-auto px-4">
            <div class="hidden md:flex justify-between items-center py-2 text-xs text-gray-500 border-b border-gray-200">
                <div>
                    <a href="tel:<?php echo defined('SITE_PHONE') ? SITE_PHONE : '021-00000000'; ?>" class="hover:text-primary transition-colors"><i class="fas fa-phone-alt mr-1"></i> پشتیبانی: <?php echo defined('SITE_PHONE_DISPLAY') ? SITE_PHONE_DISPLAY : '۰۲۱-۱۲۳۴۵۶۷۸'; ?></a>
                    <span class="mx-2 text-gray-300">|</span>
                    <a href="mailto:<?php echo defined('SITE_EMAIL') ? SITE_EMAIL : 'info@example.com'; ?>" class="hover:text-primary transition-colors"><i class="fas fa-envelope mr-1"></i> <?php echo defined('SITE_EMAIL') ? SITE_EMAIL : 'info@example.com'; ?></a>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <?php if ($user_logged_in): ?>
                        <a href="<?php echo BASE_URL; ?>customer/dashboard" class="hover:text-primary transition-colors">سلام، <?php echo htmlspecialchars($user_name); ?>! (حساب کاربری)</a>
                        <a href="<?php echo BASE_URL; ?>auth/logout" class="hover:text-red-500 transition-colors">خروج</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/login" class="hover:text-primary transition-colors">ورود</a>
                        <span class="text-gray-300">|</span>
                        <a href="<?php echo BASE_URL; ?>auth/register" class="hover:text-primary transition-colors">ثبت نام</a>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="flex items-center justify-between py-3">
                <a href="<?php echo BASE_URL; ?>" class="text-3xl font-bold text-primary hover:opacity-80 transition-opacity">
                    <?php echo defined('SITE_LOGO_TEXT') ? SITE_LOGO_TEXT : (defined('SITE_NAME') ? SITE_NAME : 'فروشگاه'); ?>
                </a>
                <div class="hidden lg:flex items-center space-x-5 space-x-reverse text-sm font-medium">
                    <a href="<?php echo BASE_URL; ?>" class="text-gray-600 hover:text-primary pb-1 <?php echo ($current_page_url == '' || $current_page_url == 'pages/index'  || $current_page_url == 'index.php') ? 'nav-link-active' : ''; ?>">صفحه اصلی</a>
                    <a href="<?php echo BASE_URL; ?>products" class="text-gray-600 hover:text-primary pb-1 <?php echo (strpos($current_page_url, 'products') === 0 && strpos($current_page_url, 'products/category') === false && $current_page_url !== 'products/categories') ? 'nav-link-active' : ''; ?>">فروشگاه</a>
                    <div class="relative dropdown">
                        <button class="text-gray-600 hover:text-primary pb-1 flex items-center <?php echo (strpos($current_page_url, 'products/category') === 0 || $current_page_url === 'products/categories') ? 'nav-link-active' : ''; ?>">
                            دسته‌بندی‌ها <i class="fas fa-chevron-down text-xs mr-1 transform group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-20 border border-gray-100 max-h-96 overflow-y-auto">
                            <?php if(!empty($menu_categories_data)): ?>
                                <?php foreach($menu_categories_data as $catMenu): ?>
                                    <a href="<?php echo BASE_URL . 'products/category/' . ($catMenu['slug'] ?? $catMenu['id']); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-light hover:text-white rounded-md transition-colors"><?php echo htmlspecialchars($catMenu['name']); ?></a>
                                <?php endforeach; ?>
                                <hr class="my-1">
                            <?php else: ?>
                                <span class="block px-4 py-2 text-sm text-gray-400">داده‌ای برای منو نیست</span>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL . 'products/categories'; ?>" class="block px-4 py-2 text-sm text-primary hover:bg-primary-light hover:text-white rounded-md font-semibold transition-colors">همه دسته‌بندی‌ها</a>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>pages/about" class="text-gray-600 hover:text-primary pb-1 <?php echo ($current_page_url == 'pages/about') ? 'nav-link-active' : ''; ?>">درباره ما</a>
                    <a href="<?php echo BASE_URL; ?>pages/contact" class="text-gray-600 hover:text-primary pb-1 <?php echo ($current_page_url == 'pages/contact') ? 'nav-link-active' : ''; ?>">تماس با ما</a>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <button id="searchIcon" aria-label="جستجو" class="text-gray-500 hover:text-primary focus:outline-none transition-colors">
                        <i class="fas fa-search text-lg"></i>
                    </button>
                    <a href="<?php echo BASE_URL; ?>cart" aria-label="سبد خرید" class="relative text-gray-500 hover:text-primary transition-colors">
                        <i class="fas fa-shopping-bag text-xl"></i>
                        <?php if ($cart_item_count > 0): ?>
                            <span id="cart-item-count-badge" class="absolute -top-2 -right-2.5 bg-accent text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-white">
                                <?php echo $cart_item_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="lg:hidden">
                        <button id="mobileMenuButton" aria-label="منوی موبایل" class="text-gray-500 hover:text-primary focus:outline-none transition-colors">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </nav>
             <div id="searchBarContainer" class="hidden absolute top-full right-0 left-0 bg-white shadow-md z-40 p-4 border-t border-gray-200">
                <form action="<?php echo BASE_URL; ?>products/search" method="get" class="flex w-full max-w-2xl mx-auto">
                    <input type="text" name="query" placeholder="محصول مورد نظر خود را جستجو کنید..." class="w-full px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent transition-shadow text-sm" autofocus>
                    <button type="submit" aria-label="جستجو" class="bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-l-lg transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div id="mobileMenu" class="lg:hidden hidden bg-white shadow-lg absolute top-full right-0 left-0 z-30 border-t border-gray-200">
            <a href="<?php echo BASE_URL; ?>" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors border-b border-gray-100">صفحه اصلی</a>
            <a href="<?php echo BASE_URL; ?>products" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors border-b border-gray-100">فروشگاه</a>
            <a href="<?php echo BASE_URL; ?>products/categories" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors border-b border-gray-100">دسته‌بندی‌ها</a>
            <a href="<?php echo BASE_URL; ?>pages/about" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors border-b border-gray-100">درباره ما</a>
            <a href="<?php echo BASE_URL; ?>pages/contact" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors border-b border-gray-100">تماس با ما</a>
            <hr class="my-2">
            <?php if ($user_logged_in): ?>
                <a href="<?php echo BASE_URL; ?>customer/dashboard" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors border-b border-gray-100">حساب کاربری من</a>
                <a href="<?php echo BASE_URL; ?>auth/logout" class="block px-4 py-3 text-red-500 hover:bg-red-500 hover:text-white transition-colors">خروج</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/login" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors border-b border-gray-100">ورود</a>
                <a href="<?php echo BASE_URL; ?>auth/register" class="block px-4 py-3 text-gray-700 hover:bg-primary-light hover:text-white transition-colors">ثبت نام</a>
            <?php endif; ?>
        </div>
    </header>
    <main class="pb-16 bg-neutral-lightest"> 