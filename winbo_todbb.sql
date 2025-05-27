-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 27, 2025 at 08:59 PM
-- Server version: 10.11.11-MariaDB-cll-lve
-- PHP Version: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `winbo_todbb`
--

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_clicks`
--

CREATE TABLE `affiliate_clicks` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL COMMENT 'شناسه کاربر همکار از جدول users',
  `product_id` int(11) DEFAULT NULL COMMENT 'شناسه محصولی که لینک به آن بوده (اختیاری)',
  `clicked_at` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referring_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_commissions`
--

CREATE TABLE `affiliate_commissions` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL COMMENT 'شناسه کاربر همکار',
  `order_id` int(11) NOT NULL COMMENT 'شناسه سفارش مرتبط',
  `order_item_id` int(11) DEFAULT NULL COMMENT 'شناسه آیتم سفارش مرتبط (اگر کمیسیون بر اساس هر آیتم است)',
  `product_id` int(11) DEFAULT NULL COMMENT 'شناسه محصول مرتبط',
  `commission_rate` decimal(5,2) DEFAULT NULL COMMENT 'نرخ کمیسیون اعمال شده (درصدی)',
  `commission_fixed_amount` decimal(10,2) DEFAULT NULL COMMENT 'مبلغ ثابت کمیسیون اعمال شده',
  `sale_amount` decimal(12,2) NOT NULL COMMENT 'مبلغ آیتم یا سفارشی که کمیسیون از آن محاسبه شده',
  `commission_earned` decimal(12,2) NOT NULL COMMENT 'مبلغ کمیسیون کسب شده',
  `currency` varchar(3) DEFAULT 'IRT' COMMENT 'واحد پول',
  `status` enum('pending','approved','paid','rejected','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'وضعیت کمیسیون',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `payout_id` int(11) DEFAULT NULL COMMENT 'شناسه پرداخت گروهی که این کمیسیون در آن تسویه شده',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `affiliate_commissions`
--

INSERT INTO `affiliate_commissions` (`id`, `affiliate_id`, `order_id`, `order_item_id`, `product_id`, `commission_rate`, `commission_fixed_amount`, `sale_amount`, `commission_earned`, `currency`, `status`, `created_at`, `approved_at`, `payout_id`, `notes`) VALUES
(1, 3, 25, 30, 9, 0.10, NULL, 45000.00, 4500.00, 'IRT', 'pending', '2025-05-27 08:10:12', NULL, NULL, NULL),
(2, 3, 26, 31, 9, 0.10, NULL, 135000.00, 13500.00, 'IRT', 'pending', '2025-05-27 08:58:21', NULL, NULL, NULL),
(3, 3, 27, 32, 9, 0.10, NULL, 45000.00, 4500.00, 'IRT', 'pending', '2025-05-27 09:15:44', NULL, NULL, NULL),
(4, 3, 27, 33, 9, 0.10, NULL, 46000.00, 4600.00, 'IRT', 'pending', '2025-05-27 09:15:44', NULL, NULL, NULL),
(5, 3, 28, 34, 12, 0.20, NULL, 356000.00, 71200.00, 'IRT', 'approved', '2025-05-27 09:23:12', '2025-05-27 10:20:34', NULL, NULL),
(6, 3, 28, 35, 12, 0.20, NULL, 550000.00, 110000.00, 'IRT', 'approved', '2025-05-27 09:23:12', '2025-05-27 10:20:37', NULL, NULL),
(7, 3, 29, 36, 12, 0.20, NULL, 445000.00, 89000.00, 'IRT', 'approved', '2025-05-27 09:44:19', '2025-05-27 10:20:26', NULL, NULL),
(8, 3, 29, 37, 12, 0.20, NULL, 220000.00, 44000.00, 'IRT', 'approved', '2025-05-27 09:44:19', '2025-05-27 10:20:30', NULL, NULL),
(9, 3, 30, 38, 12, 0.20, NULL, 4450000.00, 890000.00, 'IRT', 'approved', '2025-05-27 10:37:54', '2025-05-27 10:39:31', NULL, NULL),
(10, 3, 30, 39, 12, 0.20, NULL, 3960000.00, 792000.00, 'IRT', 'approved', '2025-05-27 10:37:54', '2025-05-27 10:39:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_payouts`
--

CREATE TABLE `affiliate_payouts` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL COMMENT 'شناسه کاربر همکار',
  `requested_amount` decimal(12,2) NOT NULL,
  `payout_amount` decimal(12,2) DEFAULT NULL,
  `payout_method` varchar(100) DEFAULT NULL,
  `payment_details` text DEFAULT NULL COMMENT 'اطلاعات حساب همکار برای واریز',
  `status` enum('requested','processing','completed','rejected','cancelled_by_affiliate') NOT NULL DEFAULT 'requested',
  `notes` text DEFAULT NULL COMMENT 'یادداشت‌های ادمین',
  `requested_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by_admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `affiliate_payouts`
--

INSERT INTO `affiliate_payouts` (`id`, `affiliate_id`, `requested_amount`, `payout_amount`, `payout_method`, `payment_details`, `status`, `notes`, `requested_at`, `processed_at`, `processed_by_admin_id`) VALUES
(1, 3, 48200.00, 48200.00, 'bank_transfer', '23545468654654654654645', 'rejected', '', '2025-05-27 10:21:20', '2025-05-27 10:34:48', 1),
(2, 3, 48200.00, 48200.00, 'bank_transfer', '2134545612321321321', 'completed', '', '2025-05-27 10:35:19', '2025-05-27 10:35:56', 1),
(3, 3, 1000000.00, 1000000.00, 'bank_transfer', '123546546546546', 'completed', 'gdfgfdgfd', '2025-05-27 10:39:57', '2025-05-27 12:14:51', 1);

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

CREATE TABLE `attributes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'نام ویژگی مانند رنگ، سایز',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attributes`
--

INSERT INTO `attributes` (`id`, `name`, `created_at`, `updated_at`) VALUES
(6, 'رنگ ها', '2025-05-25 15:46:14', '2025-05-25 16:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `attribute_values`
--

CREATE TABLE `attribute_values` (
  `id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL COMMENT 'شناسه ویژگی والد از جدول attributes',
  `value` varchar(100) NOT NULL COMMENT 'مقدار ویژگی مانند قرمز، S',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attribute_values`
--

INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `created_at`, `updated_at`) VALUES
(11, 6, 'مشکی', '2025-05-25 15:46:20', '2025-05-25 15:46:20'),
(12, 6, 'سفید', '2025-05-25 15:46:25', '2025-05-25 15:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `created_at`, `updated_at`) VALUES
(3, 'تست', 'تست', NULL, '2025-05-25 09:21:14', '2025-05-25 09:21:14'),
(4, 'تست', 'تست', 3, '2025-05-25 09:21:21', '2025-05-25 09:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending',
  `order_status` varchar(50) NOT NULL DEFAULT 'pending_confirmation',
  `notes` text DEFAULT NULL,
  `placed_by_affiliate_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `postal_code`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `notes`, `placed_by_affiliate_id`, `created_at`, `updated_at`) VALUES
(14, 1, 'صابر', 'محمدی', 'saber@gmail.com', '556456545', 'یبیبیسب', 'سیبیسبیسب', '56546546', 150000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-25 15:54:49', '2025-05-25 15:56:01'),
(15, 1, 'صابر', 'محمدی', 'saber@gmail.com', '564512312', 'یبیسبیسب', 'یبیسبی', '5465465465', 774000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-25 16:04:56', '2025-05-25 16:05:11'),
(16, 2, 'mahrokh', 'rezvani', 'mahrokh@gmail.com', '12324343', 'fgfdgfdgf', 'dfgdfgff', '3243243432', 315000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-25 19:15:08', '2025-05-25 19:16:01'),
(17, 2, 'mahrokh', 'rezvani', 'mahrokh@gmail.com', '78667565', 'لااتللابی', 'لبلببب', '7665434423', 429000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-25 20:14:23', '2025-05-25 20:44:10'),
(18, 2, 'mahrokh', 'rezvani', 'mahrokh@gmail.com', '67685674', 'اتلاتلب', 'بلتلت', '467674', 392000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-25 20:41:12', '2025-05-25 20:43:57'),
(19, 2, 'mahrokh', 'rezvani', 'mahrokh@gmail.com', '45645654654', 'یببیسبیبیسب', 'سیبیسبیس', '6566546456', 45000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-26 08:46:12', '2025-05-26 08:50:23'),
(20, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'یبلبیلبیل', 'باتاتبلاب', '543544354', 49000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-27 07:35:18', '2025-05-27 07:40:17'),
(21, 4, 'تست', 'تستس', 'test@gmail.com', '55654354543', 'لبیسبس', 'لنتن', '354534', 45000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-27 07:36:21', '2025-05-27 07:37:53'),
(22, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 98000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-27 07:47:41', '2025-05-27 07:48:21'),
(23, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 49000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-27 07:51:35', '2025-05-27 07:52:06'),
(24, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 49000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-27 08:00:40', '2025-05-27 08:04:10'),
(25, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 45000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-27 08:10:12', '2025-05-27 08:31:42'),
(26, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 135000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-27 08:58:21', '2025-05-27 08:58:50'),
(27, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 91000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-27 09:15:44', '2025-05-27 09:16:09'),
(28, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 906000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-27 09:23:12', '2025-05-27 09:23:45'),
(29, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 665000.00, 'cod', 'paid', 'delivered', '', NULL, '2025-05-27 09:44:19', '2025-05-27 09:45:01'),
(30, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 8410000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-27 10:37:54', '2025-05-27 10:38:55'),
(31, 4, 'تست', 'تستس', 'test@gmail.com', '09148874622', 'تیت', 'یسبیبسی', '5465465445', 10900000.00, 'cod', 'paid', 'shipped', '', NULL, '2025-05-27 11:01:29', '2025-05-27 11:01:48');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variation_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL COMMENT 'شناسه فروشنده محصول این آیتم (از جدول users)',
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `sub_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `platform_commission_rate` decimal(5,2) DEFAULT NULL COMMENT 'نرخ کمیسیون فروشگاه در زمان فروش (مثلا 0.10 برای 10%)',
  `platform_commission_amount` decimal(12,2) DEFAULT NULL COMMENT 'مبلغ کمیسیون فروشگاه برای این آیتم',
  `vendor_earning` decimal(12,2) DEFAULT NULL COMMENT 'درآمد خالص فروشنده برای این آیتم',
  `payout_status` enum('unpaid','requested','paid','processing','on_hold','cancelled') NOT NULL DEFAULT 'unpaid' COMMENT 'وضعیت تسویه این آیتم برای فروشنده',
  `payout_id` int(11) DEFAULT NULL COMMENT 'شناسه پرداخت گروهی که این آیتم در آن تسویه شده (از جدول vendor_payouts)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variation_id`, `vendor_id`, `product_name`, `quantity`, `price_at_purchase`, `sub_total`, `created_at`, `platform_commission_rate`, `platform_commission_amount`, `vendor_earning`, `payout_status`, `payout_id`) VALUES
(17, 14, NULL, NULL, NULL, 'تیشرت کد 45 (سفید)', 10, 15000.00, 150000.00, '2025-05-25 15:54:49', 0.20, NULL, NULL, 'unpaid', NULL),
(18, 15, 9, NULL, NULL, 'تست (سفید)', 8, 45000.00, 360000.00, '2025-05-25 16:04:56', NULL, NULL, NULL, 'unpaid', NULL),
(19, 15, 9, NULL, NULL, 'تست (مشکی)', 9, 46000.00, 414000.00, '2025-05-25 16:04:56', NULL, NULL, NULL, 'unpaid', NULL),
(20, 16, 11, 17, NULL, 'تست 12 (سفید)', 7, 45000.00, 315000.00, '2025-05-25 19:15:08', NULL, NULL, NULL, 'unpaid', NULL),
(21, 17, 11, 17, 2, 'تست 12 (سفید)', 3, 45000.00, 135000.00, '2025-05-25 20:14:23', 0.10, 13500.00, 121500.00, 'paid', 6),
(22, 17, 11, 18, 2, 'تست 12 (مشکی)', 6, 49000.00, 294000.00, '2025-05-25 20:14:23', 0.10, 29400.00, 264600.00, 'paid', 6),
(23, 18, 11, 18, 2, 'تست 12 (مشکی)', 8, 49000.00, 392000.00, '2025-05-25 20:41:12', 0.10, 39200.00, 352800.00, 'paid', 6),
(24, 19, 11, 17, 2, 'تست 12 (سفید)', 1, 45000.00, 45000.00, '2025-05-26 08:46:12', 0.10, 4500.00, 40500.00, 'paid', 7),
(25, 20, 11, 18, 2, 'تست 12 (مشکی)', 1, 49000.00, 49000.00, '2025-05-27 07:35:18', 0.10, 4900.00, 44100.00, 'paid', 7),
(26, 21, 11, 17, 2, 'تست 12 (سفید)', 1, 45000.00, 45000.00, '2025-05-27 07:36:21', 0.10, 4500.00, 40500.00, 'paid', 7),
(27, 22, 11, 18, 2, 'تست 12 (مشکی)', 2, 49000.00, 98000.00, '2025-05-27 07:47:41', 0.10, 9800.00, 88200.00, 'paid', 7),
(28, 23, 11, 18, 2, 'تست 12 (مشکی)', 1, 49000.00, 49000.00, '2025-05-27 07:51:35', 0.10, 4900.00, 44100.00, 'paid', 7),
(29, 24, 11, 18, 2, 'تست 12 (مشکی)', 1, 49000.00, 49000.00, '2025-05-27 08:00:40', 0.10, 4900.00, 44100.00, 'paid', 7),
(30, 25, 9, NULL, NULL, 'تست (سفید)', 1, 45000.00, 45000.00, '2025-05-27 08:10:12', NULL, 0.00, 45000.00, 'unpaid', NULL),
(31, 26, 9, NULL, NULL, 'تست (سفید)', 3, 45000.00, 135000.00, '2025-05-27 08:58:21', NULL, 0.00, 135000.00, 'unpaid', NULL),
(32, 27, 9, NULL, NULL, 'تست (سفید)', 1, 45000.00, 45000.00, '2025-05-27 09:15:44', NULL, 0.00, 45000.00, 'unpaid', NULL),
(33, 27, 9, NULL, NULL, 'تست (مشکی)', 1, 46000.00, 46000.00, '2025-05-27 09:15:44', NULL, 0.00, 46000.00, 'unpaid', NULL),
(34, 28, 12, 19, NULL, 'تیشرت کد 54 (سفید)', 4, 89000.00, 356000.00, '2025-05-27 09:23:12', NULL, 0.00, 356000.00, 'unpaid', NULL),
(35, 28, 12, 20, NULL, 'تیشرت کد 54 (مشکی)', 5, 110000.00, 550000.00, '2025-05-27 09:23:12', NULL, 0.00, 550000.00, 'unpaid', NULL),
(36, 29, 12, 19, NULL, 'تیشرت کد 54 (سفید)', 5, 89000.00, 445000.00, '2025-05-27 09:44:19', 0.10, 0.00, 445000.00, 'unpaid', NULL),
(37, 29, 12, 20, NULL, 'تیشرت کد 54 (مشکی)', 2, 110000.00, 220000.00, '2025-05-27 09:44:19', 0.10, 0.00, 220000.00, 'unpaid', NULL),
(38, 30, 12, 19, NULL, 'تیشرت کد 54 (سفید)', 50, 89000.00, 4450000.00, '2025-05-27 10:37:54', 0.10, 0.00, 4450000.00, 'unpaid', NULL),
(39, 30, 12, 20, NULL, 'تیشرت کد 54 (مشکی)', 36, 110000.00, 3960000.00, '2025-05-27 10:37:54', 0.10, 0.00, 3960000.00, 'unpaid', NULL),
(40, 31, 13, 21, 2, 'تیشرت کد 99 (سفید)', 20, 545000.00, 10900000.00, '2025-05-27 11:01:29', 0.10, 1090000.00, 9810000.00, 'paid', 7);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `initial_stock_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'موجودی اولیه محصول که تغییر نمی‌کند',
  `product_type` enum('simple','variable') NOT NULL DEFAULT 'simple' COMMENT 'نوع محصول: ساده یا متغیر',
  `affiliate_commission_type` enum('none','percentage','fixed_amount') NOT NULL DEFAULT 'none' COMMENT 'نوع پورسانت همکاری: درصدی یا مقدار ثابت',
  `affiliate_commission_value` decimal(10,2) DEFAULT NULL COMMENT 'مقدار پورسانت همکاری',
  `category_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL COMMENT 'شناسه فروشنده‌ای که محصول را اضافه کرده (از جدول users)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `stock_quantity`, `initial_stock_quantity`, `product_type`, `affiliate_commission_type`, `affiliate_commission_value`, `category_id`, `vendor_id`, `created_at`, `updated_at`) VALUES
(9, 'تست', 'تست', 52000.00, 'uploads/products/product_68333eede55988.17284060.jpg', 95, 25, 'simple', 'percentage', 10.00, 3, NULL, '2025-05-25 16:01:49', '2025-05-27 13:20:37'),
(11, 'تست 12', 'تست 12', NULL, 'uploads/products/product_2_68336a9da1fc97.87941844.jpg', 23, 23, 'variable', 'none', NULL, 3, 2, '2025-05-25 19:08:13', '2025-05-25 19:11:59'),
(12, 'تیشرت کد 54', 'تیشرت کد 54', NULL, 'uploads/products/product_68358372c50595.05522556.jpg', 160, 160, 'variable', 'percentage', 20.00, 3, NULL, '2025-05-27 09:18:42', '2025-05-27 09:20:32'),
(13, 'تیشرت کد 99', 'تیشرت کد 99', NULL, 'uploads/products/product_2_68359ac849c7e9.89779038.jpg', 145, 145, 'variable', 'fixed_amount', 35000.00, 3, 2, '2025-05-27 10:58:16', '2025-05-27 10:59:39'),
(14, 'تیشرت آبی', 'تیشرت آبی', 500000.00, 'uploads/products/product_6835bcf2696ba7.00481302.jpg', 98, 98, 'simple', 'fixed_amount', 65000.00, 4, NULL, '2025-05-27 13:24:02', '2025-05-27 13:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `product_configurable_attributes`
--

CREATE TABLE `product_configurable_attributes` (
  `product_id` int(11) NOT NULL COMMENT 'شناسه محصول والد از جدول products',
  `attribute_id` int(11) NOT NULL COMMENT 'شناسه ویژگی از جدول attributes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_configurable_attributes`
--

INSERT INTO `product_configurable_attributes` (`product_id`, `attribute_id`) VALUES
(11, 6),
(12, 6),
(13, 6);

-- --------------------------------------------------------

--
-- Table structure for table `product_variations`
--

CREATE TABLE `product_variations` (
  `id` int(11) NOT NULL,
  `parent_product_id` int(11) NOT NULL COMMENT 'شناسه محصول والد (متغیر) از جدول products',
  `sku` varchar(100) DEFAULT NULL COMMENT 'شناسه انبار برای این تنوع خاص (اختیاری)',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'قیمت این تنوع (اگر NULL باشد، از قیمت محصول والد استفاده می‌شود)',
  `stock_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'موجودی این تنوع',
  `initial_stock_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'موجودی اولیه این تنوع که تغییر نمی‌کند',
  `image_url` varchar(255) DEFAULT NULL COMMENT 'تصویر خاص این تنوع (اگر NULL باشد، از تصویر محصول والد)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'آیا این تنوع فعال است',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variations`
--

INSERT INTO `product_variations` (`id`, `parent_product_id`, `sku`, `price`, `stock_quantity`, `initial_stock_quantity`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(17, 11, NULL, 45000.00, 0, 12, NULL, 1, '2025-05-25 19:13:48', '2025-05-27 07:36:21'),
(18, 11, NULL, 49000.00, 15, 34, NULL, 1, '2025-05-25 19:14:00', '2025-05-27 08:00:40'),
(19, 12, NULL, 89000.00, 66, 125, NULL, 1, '2025-05-27 09:21:13', '2025-05-27 10:37:54'),
(20, 12, NULL, 110000.00, 92, 135, NULL, 1, '2025-05-27 09:21:31', '2025-05-27 10:37:54'),
(21, 13, NULL, 545000.00, 90, 110, NULL, 1, '2025-05-27 11:00:09', '2025-05-27 11:01:29'),
(22, 13, NULL, 525000.00, 110, 110, NULL, 1, '2025-05-27 11:00:20', '2025-05-27 11:00:20');

-- --------------------------------------------------------

--
-- Table structure for table `product_variation_attributes`
--

CREATE TABLE `product_variation_attributes` (
  `id` int(11) NOT NULL,
  `product_variation_id` int(11) NOT NULL COMMENT 'شناسه تنوع از جدول product_variations',
  `attribute_id` int(11) NOT NULL COMMENT 'شناسه ویژگی از جدول attributes',
  `attribute_value_id` int(11) NOT NULL COMMENT 'شناسه مقدار ویژگی از جدول attribute_values'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variation_attributes`
--

INSERT INTO `product_variation_attributes` (`id`, `product_variation_id`, `attribute_id`, `attribute_value_id`) VALUES
(17, 17, 6, 12),
(18, 18, 6, 11),
(19, 19, 6, 12),
(20, 20, 6, 11),
(21, 21, 6, 12),
(22, 22, 6, 11);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role` enum('customer','vendor','affiliate','admin') NOT NULL DEFAULT 'customer',
  `wallet_balance` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `affiliate_code` varchar(50) DEFAULT NULL COMMENT 'کد منحصر به فرد همکاری در فروش',
  `affiliate_balance` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'موجودی کیف پول همکار از کمیسیون‌ها'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `first_name`, `last_name`, `role`, `wallet_balance`, `created_at`, `updated_at`, `affiliate_code`, `affiliate_balance`) VALUES
(1, 'saber', '$2y$10$Se4VKuh7Oejnx9WJovOYPeyqY3/RmdVvuObIYaRJhDa8zirAsZsZm', 'saber@gmail.com', 'صابر', 'محمدی', 'admin', 0.00, '2025-05-25 08:02:52', '2025-05-25 08:53:27', NULL, 0.00),
(2, 'mahrokh', '$2y$10$c4Yb/L9EwNN5TFgUJAdQteWzZl3S.Nn0w/EzgDzkPAjjwA8Qqj3Mu', 'mahrokh@gmail.com', 'ماهرخ', 'رضوانی', 'vendor', 0.00, '2025-05-25 17:57:35', '2025-05-27 07:08:26', NULL, 0.00),
(3, 'sahar', '$2y$10$U0IY0StXojnf3A7.P5dt0.bCTEBinkeKnFplRRUKJH9BwGizzXliy', 'sahar@gmail.com', 'سحر', 'قهرمانی', 'affiliate', 0.00, '2025-05-27 07:11:12', '2025-05-27 12:14:51', 'f87uhFG3', 730200.00),
(4, 'test', '$2y$10$j9UMxV4i7MHxbnY5.ciBKOPtFXfrj06hbcKll8kuOrJH/APrkj.WC', 'test@gmail.com', 'تست', 'تستس', 'customer', 0.00, '2025-05-27 07:34:36', '2025-05-27 07:34:36', 'r2ndtrkm', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_payouts`
--

CREATE TABLE `vendor_payouts` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL COMMENT 'شناسه فروشنده از جدول users',
  `requested_amount` decimal(12,2) NOT NULL COMMENT 'مبلغ درخواستی توسط فروشنده',
  `payout_amount` decimal(12,2) DEFAULT NULL COMMENT 'مبلغ نهایی پرداخت شده توسط ادمین',
  `payout_method` varchar(100) DEFAULT NULL COMMENT 'روش پرداخت (مثلا انتقال بانکی)',
  `payment_details` text DEFAULT NULL COMMENT 'جزئیات پرداخت (مثلا شماره تراکنش، توضیحات)',
  `status` enum('requested','processing','completed','rejected','cancelled_by_vendor') NOT NULL DEFAULT 'requested',
  `notes` text DEFAULT NULL COMMENT 'یادداشت‌های ادمین یا فروشنده',
  `requested_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by_admin_id` int(11) DEFAULT NULL COMMENT 'شناسه ادمینی که پرداخت را پردازش کرده'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_payouts`
--

INSERT INTO `vendor_payouts` (`id`, `vendor_id`, `requested_amount`, `payout_amount`, `payout_method`, `payment_details`, `status`, `notes`, `requested_at`, `processed_at`, `processed_by_admin_id`) VALUES
(1, 2, 738900.00, NULL, 'bank_transfer', '456546435435346643543543', 'requested', NULL, '2025-05-25 20:44:43', NULL, NULL),
(2, 2, 738900.00, NULL, 'bank_transfer', '6565778899002134', 'requested', NULL, '2025-05-26 05:57:10', NULL, NULL),
(3, 2, 738900.00, NULL, 'bank_transfer', '5634534354654435435', 'requested', NULL, '2025-05-26 06:04:02', NULL, NULL),
(4, 2, 738900.00, NULL, 'bank_transfer', '5634534354654435435', 'requested', NULL, '2025-05-26 06:06:56', NULL, NULL),
(5, 2, 738900.00, 738900.00, 'bank_transfer', '5634534354654435435', 'rejected', '', '2025-05-26 06:15:41', '2025-05-26 08:45:00', 1),
(6, 2, 738900.00, 738900.00, 'bank_transfer', '5634534354654435435', 'completed', 'پرداخت شد', '2025-05-26 06:19:42', '2025-05-26 06:33:50', 1),
(7, 2, 10111500.00, 10111500.00, 'bank_transfer', '543452133231123543', 'completed', 'asdsdsdsa', '2025-05-27 11:03:55', '2025-05-27 11:04:36', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `affiliate_commissions`
--
ALTER TABLE `affiliate_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `payout_id` (`payout_id`);

--
-- Indexes for table `affiliate_payouts`
--
ALTER TABLE `affiliate_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`),
  ADD KEY `processed_by_admin_id` (`processed_by_admin_id`);

--
-- Indexes for table `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attribute_value_unique` (`attribute_id`,`value`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_order_placed_by_affiliate` (`placed_by_affiliate_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_order_item_variation` (`variation_id`),
  ADD KEY `fk_order_item_vendor` (`vendor_id`),
  ADD KEY `fk_order_item_payout` (`payout_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_category` (`category_id`),
  ADD KEY `fk_product_vendor` (`vendor_id`);

--
-- Indexes for table `product_configurable_attributes`
--
ALTER TABLE `product_configurable_attributes`
  ADD PRIMARY KEY (`product_id`,`attribute_id`),
  ADD KEY `attribute_id` (`attribute_id`);

--
-- Indexes for table `product_variations`
--
ALTER TABLE `product_variations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `parent_product_id` (`parent_product_id`);

--
-- Indexes for table `product_variation_attributes`
--
ALTER TABLE `product_variation_attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `variation_attribute_unique` (`product_variation_id`,`attribute_id`) COMMENT 'هر ویژگی برای یک تنوع فقط یکبار می‌تواند تعریف شود',
  ADD KEY `attribute_id` (`attribute_id`),
  ADD KEY `attribute_value_id` (`attribute_value_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `affiliate_code` (`affiliate_code`),
  ADD KEY `idx_affiliate_code` (`affiliate_code`);

--
-- Indexes for table `vendor_payouts`
--
ALTER TABLE `vendor_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `processed_by_admin_id` (`processed_by_admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `affiliate_commissions`
--
ALTER TABLE `affiliate_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `affiliate_payouts`
--
ALTER TABLE `affiliate_payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attribute_values`
--
ALTER TABLE `attribute_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_variations`
--
ALTER TABLE `product_variations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product_variation_attributes`
--
ALTER TABLE `product_variation_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vendor_payouts`
--
ALTER TABLE `vendor_payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  ADD CONSTRAINT `affiliate_clicks_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affiliate_clicks_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `affiliate_commissions`
--
ALTER TABLE `affiliate_commissions`
  ADD CONSTRAINT `affiliate_commissions_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affiliate_commissions_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affiliate_commissions_ibfk_3` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `affiliate_commissions_ibfk_4` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `affiliate_commissions_ibfk_5` FOREIGN KEY (`payout_id`) REFERENCES `affiliate_payouts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `affiliate_payouts`
--
ALTER TABLE `affiliate_payouts`
  ADD CONSTRAINT `affiliate_payouts_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affiliate_payouts_ibfk_2` FOREIGN KEY (`processed_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD CONSTRAINT `attribute_values_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_placed_by_affiliate` FOREIGN KEY (`placed_by_affiliate_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_item_payout` FOREIGN KEY (`payout_id`) REFERENCES `vendor_payouts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_item_variation` FOREIGN KEY (`variation_id`) REFERENCES `product_variations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_item_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_configurable_attributes`
--
ALTER TABLE `product_configurable_attributes`
  ADD CONSTRAINT `product_configurable_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_configurable_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variations`
--
ALTER TABLE `product_variations`
  ADD CONSTRAINT `product_variations_ibfk_1` FOREIGN KEY (`parent_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variation_attributes`
--
ALTER TABLE `product_variation_attributes`
  ADD CONSTRAINT `product_variation_attributes_ibfk_1` FOREIGN KEY (`product_variation_id`) REFERENCES `product_variations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variation_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variation_attributes_ibfk_3` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_payouts`
--
ALTER TABLE `vendor_payouts`
  ADD CONSTRAINT `vendor_payouts_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_payouts_ibfk_2` FOREIGN KEY (`processed_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
