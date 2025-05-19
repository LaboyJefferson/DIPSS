-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 03:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `testdrive`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `address_id` int(8) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`address_id`, `address`) VALUES
(41991738, '10 Upper Session Rd, Baguio City, Benguet'),
(67021417, '123 Session Road, Baguio City, Philippines');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(8) NOT NULL,
  `category_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(18673629, 'Vehicle Parts'),
(22295743, 'Wheels'),
(36200226, 'Accessories'),
(43435445, 'Fluids'),
(45992576, 'Tools'),
(48312275, 'Batteries'),
(51431926, 'Tires'),
(62251421, 'Oils'),
(98009675, 'Electricals');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `delivery_id` int(8) NOT NULL,
  `issued_date` date NOT NULL,
  `date_delivered` date DEFAULT NULL,
  `purchase_order_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`delivery_id`, `issued_date`, `date_delivered`, `purchase_order_id`) VALUES
(37750443, '2025-05-18', '2025-05-18', 14544310),
(64706008, '2025-05-18', NULL, 41258009);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL,
  `purchase_price_per_unit` decimal(10,2) DEFAULT NULL,
  `sale_price_per_unit` decimal(10,2) DEFAULT NULL,
  `unit_of_measure` varchar(15) DEFAULT NULL,
  `in_stock` int(6) DEFAULT NULL,
  `reorder_level` int(6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `profit_margin` decimal(5,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) GENERATED ALWAYS AS (`sale_price_per_unit` * `tax_rate`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `purchase_price_per_unit`, `sale_price_per_unit`, `unit_of_measure`, `in_stock`, `reorder_level`, `created_at`, `updated_at`, `profit_margin`, `tax_rate`) VALUES
(17191477, 46004118, 24.00, 32.00, 'pcs', 19, 10, '2024-12-03 03:02:35', '2024-12-08 07:22:09', NULL, NULL),
(19144846, 72142592, 1500.00, NULL, NULL, 0, 5, '2025-05-18 15:52:39', '2025-05-18 15:52:39', NULL, NULL),
(19826892, 40739618, 55000.00, 62000.00, 'pcs', 17, 2, '2024-12-02 02:36:49', '2024-12-08 07:22:09', NULL, NULL),
(19877235, 91761968, 1300.00, NULL, NULL, 0, 5, '2025-05-17 14:34:57', '2025-05-17 14:34:57', NULL, NULL),
(22882096, 84136811, 750.00, NULL, NULL, 0, 5, '2025-05-17 14:35:23', '2025-05-17 14:35:23', NULL, NULL),
(27694810, 10332285, 125.00, NULL, NULL, 0, 10, '2025-05-18 06:01:29', '2025-05-18 06:01:29', NULL, NULL),
(30678416, 73086209, 15000.00, 18000.00, 'box', 18, 1, '2024-12-16 01:21:58', '2025-05-16 16:12:59', NULL, NULL),
(46813078, 30607915, 1200.00, NULL, NULL, 0, 5, '2025-05-18 15:51:54', '2025-05-18 15:51:54', NULL, NULL),
(51675109, 98593146, 299.00, NULL, NULL, 0, 5, '2025-05-18 15:51:26', '2025-05-18 15:51:26', NULL, NULL),
(57241216, 62516636, 220.00, NULL, NULL, 20, 5, '2025-05-18 11:40:21', '2025-05-18 14:22:33', NULL, NULL),
(59712524, 31299885, 24.00, 32.00, 'pcs', 14, 1, '2024-12-02 11:43:27', '2024-12-08 07:22:09', NULL, NULL),
(63316492, 95333368, 1230.00, NULL, NULL, 13, 3, '2025-05-18 11:35:49', '2025-05-18 14:22:34', NULL, NULL),
(64397053, 78592703, 2500.00, NULL, NULL, 0, 5, '2025-05-17 14:34:32', '2025-05-17 14:34:32', NULL, NULL),
(64810391, 60059611, 1300.00, NULL, NULL, 0, 3, '2025-05-17 14:39:16', '2025-05-17 14:39:16', NULL, NULL),
(64910596, 92293430, 1250.00, NULL, NULL, 0, 3, '2025-05-17 14:43:59', '2025-05-17 14:43:59', NULL, NULL),
(64921281, 86556148, 2500.00, NULL, NULL, 0, 3, '2025-05-18 11:32:14', '2025-05-18 11:32:14', NULL, NULL),
(66435197, 29700389, 250.00, NULL, NULL, 10, 5, '2025-05-18 11:37:12', '2025-05-18 14:22:33', NULL, NULL),
(82984096, 18975177, 5000.00, 1100.00, 'pair', 14, 5, '2024-12-06 01:27:02', '2025-05-16 16:12:59', NULL, NULL),
(85845553, 84495846, 6400.00, 7635.00, 'pcs', 22, 5, '2024-12-06 01:36:05', '2024-12-08 07:22:09', NULL, NULL),
(95154263, 65174824, 24.00, 32.00, 'pcs', 24, 1, '2024-12-02 14:20:39', '2024-12-08 07:22:09', NULL, NULL),
(95974033, 15076522, 15000.00, 18000.00, 'pcs', 25, 8, '2024-12-06 00:19:22', '2025-05-18 15:13:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_audit`
--

CREATE TABLE `inventory_audit` (
  `audit_id` int(8) NOT NULL,
  `inventory_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `physical_storestock_count` int(6) NOT NULL,
  `system_storestock_record` int(6) NOT NULL,
  `storestock_discrepancies` int(6) NOT NULL,
  `physical_stockroom_count` int(6) NOT NULL,
  `system_stockroom_record` int(6) NOT NULL,
  `stockroom_discrepancies` int(6) NOT NULL,
  `in_stock_discrepancies` int(6) NOT NULL,
  `discrepancy_reason` varchar(100) DEFAULT NULL,
  `audit_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_audit`
--

INSERT INTO `inventory_audit` (`audit_id`, `inventory_id`, `user_id`, `physical_storestock_count`, `system_storestock_record`, `storestock_discrepancies`, `physical_stockroom_count`, `system_stockroom_record`, `stockroom_discrepancies`, `in_stock_discrepancies`, `discrepancy_reason`, `audit_date`) VALUES
(1, 30678416, 20240000, 9, 10, -1, 9, 10, -1, -2, 'Human Error', '2025-05-16 16:12:59'),
(2, 82984096, 20240000, 9, 12, -3, 5, 12, -7, -10, 'Human Error', '2025-05-16 16:12:59');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_items_id` int(8) NOT NULL,
  `quantity` int(6) NOT NULL,
  `price` float DEFAULT NULL,
  `delivered_quantity` int(6) DEFAULT NULL,
  `damaged_quantity` int(6) DEFAULT NULL,
  `remarks` varchar(100) DEFAULT NULL,
  `purchase_order_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_items_id`, `quantity`, `price`, `delivered_quantity`, `damaged_quantity`, `remarks`, `purchase_order_id`, `product_id`) VALUES
(31400759, 30, 192000, 30, NULL, NULL, 25288886, 84495846),
(35916894, 10, 50000, 10, NULL, NULL, 25288886, 18975177),
(65677516, 10, 2200, 10, 3, '3 damaged product', 14544310, 62516636),
(72155063, 15, 3750, 10, NULL, '5 missing', 14544310, 29700389),
(77031907, 5, 1100, NULL, NULL, NULL, 41258009, 62516636),
(96517746, 13, 15990, 13, NULL, NULL, 14544310, 95333368),
(96517749, 5, 18750, NULL, NULL, NULL, 60119385, 29700389);

-- --------------------------------------------------------

--
-- Table structure for table `order_statuses`
--

CREATE TABLE `order_statuses` (
  `order_statuses` int(11) NOT NULL,
  `status_name` varchar(15) NOT NULL,
  `status_description` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_statuses`
--

INSERT INTO `order_statuses` (`order_statuses`, `status_name`, `status_description`) VALUES
(1, 'To order', 'The order has been planned but not yet placed.'),
(2, 'Ordered', 'The order has been successfully placed and is bein'),
(3, 'Recieved', 'The order has been delivered and received.');

-- --------------------------------------------------------

--
-- Table structure for table `order_supplier`
--

CREATE TABLE `order_supplier` (
  `order_supplier_id` int(8) NOT NULL,
  `purchase_order_id` int(8) NOT NULL,
  `supplier_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_supplier`
--

INSERT INTO `order_supplier` (`order_supplier_id`, `purchase_order_id`, `supplier_id`) VALUES
(31826687, 25288886, 95562004);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `password_reset_id` int(11) NOT NULL,
  `email` varchar(30) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`password_reset_id`, `email`, `token`, `created_at`) VALUES
(11, '20223765@s.ubaguio.edu', '$2y$10$KTaDuVyyN9wN0MnylznroeWFIKzAg47RBeC7NWpkhZmkiJ1pD1fBG', '2024-12-10 08:39:29'),
(7, 'carilloaira@gmail.com', '$2y$10$eSv7oUmj/p3xtj/579YypO04KuiMCxq.Myi4aHfuDf2iuU0HTKiTK', '2024-12-03 02:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(8) NOT NULL,
  `category_id` int(8) NOT NULL,
  `image_url` varchar(75) NOT NULL,
  `product_name` varchar(30) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `category_id`, `image_url`, `product_name`, `description`) VALUES
(10332285, 36200226, '494694805_1097430849071926_719310278944330296_n_1747548082.jpg', 'Sample product 1', 'Sample definition'),
(15076522, 18673629, 'Screenshot 2024-09-05 144341_1733444362.png', 'product1', '{\"color\":null,\"size\":\"large\",\"description\":\"for cars\"}'),
(18975177, 98009675, 'Screenshot 2024-09-05 144341_1733448422.png', 'product2', '{\"color\":null,\"size\":null,\"description\":\"for cars\"}'),
(29700389, 36200226, '497328787_1188331472761632_7622778681514167883_n_1747568232.jpg', 'Sample product 54', 'Sample definition'),
(30607915, 36200226, '497328787_1188331472761632_7622778681514167883_n_1747583513.jpg', 'CargoLock Trunk Organizer', 'Foldable, 22\"x14\"x10\" with adjustable compartments.'),
(31299885, 51431926, '', 'Mill', '{\"color\":null,\"size\":null,\"description\":\"AA Battery\"}'),
(40739618, 45992576, '', 'Dutch', '{\"color\":\"black\",\"size\":\"large\",\"description\":\"this is an item\"}'),
(46004118, 36200226, '', 'Side Mirror', '{\"color\":\"Black\",\"size\":\"25\",\"description\":\"AA Battery\"}'),
(60059611, 51431926, '497328787_1188331472761632_7622778681514167883_n_1747492756.jpg', 'TrailMaster All-Terrain XTR', '265/70R17 all-terrain tire for off-road and highway driving.'),
(62516636, 36200226, '496604569_700373569522515_776652809181130229_n_1747568421.jpg', 'Sample product 78', 'Sample definition'),
(65174824, 18673629, '', 'Lcal', '{\"color\":null,\"size\":null,\"description\":\"AA Battery\"}'),
(72142592, 98009675, '497328787_1188331472761632_7622778681514167883_n_1747583559.jpg', 'BrightBeam LED Headlight Kit', 'H11 size; 6000K color temperature, 10,000 lumens/pair.'),
(73086209, 48312275, 'dis_erd.drawio (5)_1734312117.png', 'product6', '{\"color\":null,\"size\":null,\"description\":null}'),
(78592703, 62251421, '497328787_1188331472761632_7622778681514167883_n_1747492472.jpg', 'UltraClean Engine Flush', '300ml bottle used before oil change. cleans sludge from engines.'),
(84136811, 36200226, '497328787_1188331472761632_7622778681514167883_n_1747492523.jpg', 'LEDGlow Interior Light Kit', 'Fits most cars'),
(84495846, 48312275, 'Screenshot 2024-09-05 224041_1733448965.png', 'product3', '{\"color\":null,\"size\":null,\"description\":\"Lead-Acid Batteries\"}'),
(86556148, 18673629, '497328787_1188331472761632_7622778681514167883_n_1747567934.jpg', 'Sample product 562', 'Sample definition'),
(91761968, 62251421, '496604569_700373569522515_776652809181130229_n_1747492497.jpg', 'MotoEdge 20W-50 Classic Blend', '1-liter bottle suited for vintage and high-mileage motors.'),
(92293430, 51431926, '496604569_700373569522515_776652809181130229_n_1747493039.jpg', 'SpeedGrip Sport ZR', '225/45ZR18 ultra-high-performance tire for sharp handling.'),
(95333368, 18673629, '496604569_700373569522515_776652809181130229_n_1747568148.jpg', 'Sample product 111', 'Sample definition'),
(98593146, 36200226, '497328787_1188331472761632_7622778681514167883_n_1747583486.jpg', 'QuickMount Phone Holder', 'Adjustable to fit phones from 4.7\"–6.9\"; mounts securely to dash or vent.');

-- --------------------------------------------------------

--
-- Table structure for table `product_supplier`
--

CREATE TABLE `product_supplier` (
  `product_supplier_id` int(8) NOT NULL,
  `supplier_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_supplier`
--

INSERT INTO `product_supplier` (`product_supplier_id`, `supplier_id`, `product_id`) VALUES
(19282025, 95562004, 18975177),
(34792707, 77901272, 91761968),
(36905562, 77901272, 78592703),
(38689950, 28924080, 86556148),
(41106632, 91869735, 46004118),
(46288389, 78746914, 30607915),
(53666861, 88922363, 15076522),
(55465352, 64213870, 40739618),
(61699135, 15218775, 60059611),
(67875568, 28924080, 10332285),
(68898922, 38859181, 65174824),
(76220317, 77901272, 84136811),
(86141108, 78746914, 98593146),
(86477133, 77830888, 73086209),
(87963721, 95562004, 84495846),
(91931877, 84444515, 31299885),
(92142081, 15218775, 92293430),
(92445658, 78746914, 72142592);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `purchase_order_id` int(8) NOT NULL,
  `type` enum('Purchasing Order','Backorder','','') NOT NULL DEFAULT 'Purchasing Order',
  `payment_method` text NOT NULL,
  `billing_address` varchar(255) DEFAULT NULL,
  `shipping_address` varchar(100) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `reason` varchar(60) DEFAULT NULL,
  `created_by` int(8) NOT NULL,
  `order_status` int(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`purchase_order_id`, `type`, `payment_method`, `billing_address`, `shipping_address`, `total_price`, `reason`, `created_by`, `order_status`, `created_at`, `updated_at`) VALUES
(14544310, 'Purchasing Order', 'Cash on Delivery (COD)', '123 sample address Baguio City', '10 Upper Session Rd, Baguio City, Benguet', 21940.00, NULL, 20250000, 3, '2025-05-18 14:22:34', '2025-05-18 11:39:30'),
(25288886, 'Purchasing Order', 'Cash on Delivery (COD)', 'Baguio City', '10 Upper Session Rd, Baguio City, Benguet', 484000.00, NULL, 20250000, 3, '2025-05-13 17:58:59', NULL),
(41258009, 'Purchasing Order', 'Cash on Delivery (COD)', '10 Upper Session Rd, Baguio City, Benguet', '10 Upper Session Rd, Baguio City, Benguet', 1100.00, NULL, 20250000, 1, '2025-05-18 13:13:07', '2025-05-18 13:13:07'),
(60119385, 'Backorder', 'Cash on Delivery (COD)', '123 sample address Baguio City', '10 Upper Session Rd, Baguio City, Benguet', 18750.00, NULL, 20250000, 1, '2025-05-18 15:10:15', '2025-05-18 15:10:15');

-- --------------------------------------------------------

--
-- Table structure for table `return_product`
--

CREATE TABLE `return_product` (
  `return_product_id` int(8) NOT NULL,
  `return_quantity` int(8) NOT NULL,
  `total_return_amount` float NOT NULL,
  `return_reason` varchar(30) NOT NULL,
  `return_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `scrap_product_id` int(8) DEFAULT NULL,
  `user_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `return_product`
--

INSERT INTO `return_product` (`return_product_id`, `return_quantity`, `total_return_amount`, `return_reason`, `return_date`, `scrap_product_id`, `user_id`) VALUES
(42006337, 2, 124000, 'Damaged Product', '2024-12-10 14:32:38', 27385634, 20240002),
(50382363, 1, 62000, 'Damaged Product', '2024-12-08 06:19:10', 91766211, 20240002),
(77122285, 1, 62000, 'Damaged Product', '2024-12-10 17:20:17', NULL, 20240002),
(83851557, 1, 37, 'Damaged Product', '2024-12-02 07:11:34', NULL, 20240000),
(88942119, 1, 62000, 'Damaged Product', '2024-12-02 07:10:45', 96143478, 20240000),
(89176778, 6, 222, 'Damaged Product', '2024-12-02 14:38:41', 50979433, 20240000),
(98811516, 2, 74, 'Damaged Product', '2024-12-02 07:01:32', 98504950, 20240000),
(99481541, 1, 62000, 'Damaged Product', '2024-12-07 12:47:14', 38612763, 20240002);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sales_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `sales_date` timestamp NULL DEFAULT NULL,
  `items` text DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sales_id`, `user_id`, `total_amount`, `sales_date`, `items`, `subtotal`, `discount`, `tax`, `payment_method`) VALUES
(22725940, 20240000, 37.00, '2024-12-02 03:29:31', NULL, NULL, NULL, NULL, NULL),
(23249319, 20240000, 62096.00, '2024-12-10 13:39:09', NULL, NULL, NULL, NULL, NULL),
(24995497, 20240000, 185.00, '2024-12-02 06:15:28', NULL, NULL, NULL, NULL, NULL),
(27709313, 20240000, 4526000.00, '2024-12-02 14:54:39', NULL, NULL, NULL, NULL, NULL),
(32333087, 20240002, 400.00, '2024-12-10 14:32:07', NULL, NULL, NULL, NULL, NULL),
(41031817, 20240000, 37.00, '2024-12-02 03:39:02', NULL, NULL, NULL, NULL, NULL),
(54819379, 20240000, 62037.00, '2024-12-02 03:29:57', NULL, NULL, NULL, NULL, NULL),
(55617457, 20240003, 91100.00, '2024-12-16 01:25:20', NULL, NULL, NULL, NULL, NULL),
(61052879, 20240000, 62111.00, '2024-12-02 06:26:36', NULL, NULL, NULL, NULL, NULL),
(70205673, 20240000, 310000.00, '2024-12-02 04:21:08', NULL, NULL, NULL, NULL, NULL),
(75275548, 20240000, 185.00, '2024-12-02 08:50:15', NULL, NULL, NULL, NULL, NULL),
(85216851, 20240000, 160.00, '2024-12-10 12:50:41', NULL, NULL, NULL, NULL, NULL),
(94080478, 20240000, 133.00, '2024-12-02 14:37:04', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_details`
--

CREATE TABLE `sales_details` (
  `sales_details_id` int(8) NOT NULL,
  `sales_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL,
  `return_product_id` int(8) DEFAULT NULL,
  `inventory_id` int(8) NOT NULL,
  `sales_quantity` int(6) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_details`
--

INSERT INTO `sales_details` (`sales_details_id`, `sales_id`, `product_id`, `return_product_id`, `inventory_id`, `sales_quantity`, `amount`) VALUES
(22448519, 32333087, 40739618, 42006337, 19826892, 0, 0.00),
(25563083, 55617457, 15076522, NULL, 95974033, 5, 90000.00),
(37536115, 41031817, 40739618, 99481541, 19826892, 0, 0.00),
(57018646, 27709313, 40739618, NULL, 19826892, 73, 4526000.00),
(63492342, 23249319, 46004118, NULL, 17191477, 3, 96.00),
(64155091, 61052879, 40739618, NULL, 19826892, 1, 62000.00),
(67240953, 54819379, 40739618, 50382363, 19826892, 1, -62000.00),
(74880949, 85216851, 31299885, NULL, 59712524, 5, 160.00),
(83763223, 70205673, 40739618, 88942119, 19826892, 5, 310000.00),
(91836385, 23249319, 40739618, 77122285, 19826892, 1, 62000.00),
(92939156, 94080478, 31299885, NULL, 59712524, 3, 96.00),
(93788144, 55617457, 18975177, NULL, 82984096, 1, 1100.00);

-- --------------------------------------------------------

--
-- Table structure for table `scrap_product`
--

CREATE TABLE `scrap_product` (
  `scrap_product_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `scrap_quantity` int(11) NOT NULL,
  `scrap_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scrap_product`
--

INSERT INTO `scrap_product` (`scrap_product_id`, `user_id`, `scrap_quantity`, `scrap_date`) VALUES
(27385634, 20240002, 2, '2024-12-10 14:33:39'),
(38612763, 20240002, 1, '2024-12-07 12:47:28'),
(50979433, 20240000, 6, '2024-12-02 14:40:02'),
(91766211, 20240002, 1, '2024-12-10 14:33:39'),
(96143478, 20240000, 1, '2024-12-02 15:33:02'),
(98504950, 20240000, 2, '2024-12-02 15:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `stockroom`
--

CREATE TABLE `stockroom` (
  `stockroom_id` int(8) NOT NULL,
  `aisle_number` int(3) DEFAULT NULL,
  `cabinet_level` int(3) DEFAULT NULL,
  `product_quantity` int(6) DEFAULT NULL,
  `category_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stockroom`
--

INSERT INTO `stockroom` (`stockroom_id`, `aisle_number`, `cabinet_level`, `product_quantity`, `category_id`) VALUES
(17903412, NULL, NULL, 0, 36200226),
(18777216, NULL, NULL, 0, 98009675),
(23331217, NULL, NULL, 0, 45992576),
(25813646, 1, 1, NULL, 18673629),
(26775654, NULL, NULL, 0, 98009675),
(28406217, 2, 3, 0, 18673629),
(29923391, 1, 1, 9, 45992576),
(30897042, NULL, NULL, 0, 51431926),
(31876767, 1, 1, 6, 62251421),
(34806001, NULL, NULL, 0, 36200226),
(37221614, NULL, NULL, 0, 36200226),
(39679155, NULL, NULL, 0, 18673629),
(40185993, NULL, NULL, 0, 62251421),
(42633164, 1, 1, NULL, 18673629),
(44505293, 1, 1, NULL, 18673629),
(47676610, NULL, NULL, 0, 36200226),
(48055447, NULL, NULL, 0, 36200226),
(52669463, 1, 1, 9, 48312275),
(59810781, 1, 1, NULL, 18673629),
(64511234, 1, 5, 7, 48312275),
(70620176, 1, 1, NULL, 18673629),
(75049931, NULL, NULL, 0, 36200226),
(75582219, 1, 1, NULL, 18673629),
(77336261, 3, 1, 7, 18673629),
(80898093, 1, 1, 10, 36200226),
(81199495, NULL, NULL, 0, 18673629),
(89085857, NULL, NULL, 0, 36200226),
(92056880, 1, 1, 8, 18673629),
(93949212, 3, 5, 20, 43435445),
(94821358, 2, 1, 7, 51431926),
(95596316, 3, 4, 5, 98009675),
(95964439, NULL, NULL, 0, 62251421),
(98474801, NULL, NULL, 0, 51431926);

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer`
--

CREATE TABLE `stock_transfer` (
  `stock_transfer_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `from_stockroom_id` int(8) DEFAULT NULL,
  `to_stockroom_id` int(8) DEFAULT NULL,
  `transfer_quantity` int(11) NOT NULL,
  `transfer_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfer`
--

INSERT INTO `stock_transfer` (`stock_transfer_id`, `product_id`, `user_id`, `from_stockroom_id`, `to_stockroom_id`, `transfer_quantity`, `transfer_date`) VALUES
(16435070, 91761968, 20250000, NULL, 95964439, 0, '2025-05-17 14:34:57'),
(17926354, 40739618, 20240002, 29923391, 29923391, 1, '2024-12-06 15:24:31'),
(22180668, 29700389, 20250000, NULL, 37221614, 0, '2025-05-18 11:37:12'),
(22232209, 18975177, 20240002, NULL, 95596316, 4, '2024-12-06 01:27:02'),
(23230396, 15076522, 20240002, NULL, 77336261, 5, '2024-12-10 17:42:41'),
(24840858, 40739618, 20240000, NULL, 29923391, 2, '2024-12-02 06:00:21'),
(25114049, 10332285, 20250000, NULL, 89085857, 0, '2025-05-18 06:01:28'),
(28579847, 31299885, 20240000, 94821358, NULL, 1, '2025-05-16 17:19:44'),
(31237881, 40739618, 20240000, NULL, 29923391, 1, '2024-12-02 06:08:55'),
(32542474, 30607915, 20250000, NULL, 47676610, 0, '2025-05-18 15:51:54'),
(35253467, 95333368, 20250000, NULL, 81199495, 0, '2025-05-18 11:35:49'),
(39434407, 15076522, 20240002, 77336261, NULL, 7, '2024-12-10 17:41:37'),
(39941914, 98593146, 20250000, NULL, 17903412, 0, '2025-05-18 15:51:26'),
(41993101, 46004118, 20240000, NULL, 80898093, 8, '2024-12-03 03:02:35'),
(42484241, 84495846, 20240002, NULL, 64511234, 6, '2024-12-06 01:36:05'),
(47629510, 73086209, 20240003, NULL, 52669463, 6, '2024-12-16 01:21:58'),
(49210134, 72142592, 20250000, NULL, 18777216, 0, '2025-05-18 15:52:39'),
(52147713, 40739618, 20240000, NULL, 29923391, 7, '2024-12-02 02:36:49'),
(52346511, 78592703, 20250000, NULL, 40185993, 0, '2025-05-17 14:34:32'),
(57771144, 40739618, 20240000, 29923391, NULL, 7, '2024-12-02 05:18:54'),
(58564383, 31299885, 20240000, NULL, 94821358, 8, '2024-12-02 11:43:27'),
(59383779, 62516636, 20250000, NULL, 34806001, 0, '2025-05-18 11:40:21'),
(62462124, 31299885, 20240000, 94821358, NULL, 1, '2024-12-02 14:37:04'),
(66397573, 40739618, 20240000, NULL, 29923391, 2, '2024-12-02 06:14:20'),
(70284692, 92293430, 20250000, NULL, 98474801, 0, '2025-05-17 14:43:59'),
(76017852, 86556148, 20250000, NULL, 39679155, 0, '2025-05-18 11:32:14'),
(76411566, 65174824, 20240000, NULL, 92056880, 8, '2024-12-02 14:20:39'),
(81140324, 60059611, 20250000, NULL, 30897042, 0, '2025-05-17 14:39:16'),
(85055718, 15076522, 20240002, NULL, 77336261, 10, '2024-12-06 15:21:04'),
(90431545, 84136811, 20250000, NULL, 75049931, 0, '2025-05-17 14:35:23'),
(95625573, 40739618, 20240000, 29923391, NULL, 66, '2024-12-02 14:54:39'),
(95625574, 73086209, 20240000, NULL, 52669463, 6, '2025-05-16 15:45:01'),
(95625575, 18975177, 20240000, NULL, 95596316, -4, '2025-05-16 15:45:01'),
(95625576, 73086209, 20240000, NULL, 52669463, 6, '2025-05-16 15:45:24'),
(95625577, 18975177, 20240000, NULL, 95596316, -4, '2025-05-16 15:45:24'),
(95625578, 73086209, 20240000, NULL, 52669463, 6, '2025-05-16 15:45:32'),
(95625579, 18975177, 20240000, NULL, 95596316, -4, '2025-05-16 15:45:32'),
(95625580, 73086209, 20240000, NULL, 52669463, 6, '2025-05-16 15:49:08'),
(95625581, 18975177, 20240000, NULL, 95596316, -4, '2025-05-16 15:49:08'),
(95625582, 73086209, 20240000, NULL, 52669463, 6, '2025-05-16 15:49:27'),
(95625583, 18975177, 20240000, NULL, 95596316, -4, '2025-05-16 15:49:27'),
(95625584, 73086209, 20240000, NULL, 52669463, 6, '2025-05-16 15:49:52'),
(95625585, 18975177, 20240000, NULL, 95596316, -4, '2025-05-16 15:49:52'),
(95625586, 73086209, 20240000, NULL, 52669463, -2, '2025-05-16 15:52:42'),
(95625587, 18975177, 20240000, NULL, 95596316, 1, '2025-05-16 15:52:42'),
(95625588, 73086209, 20240000, NULL, 52669463, -1, '2025-05-16 16:12:59'),
(95625589, 18975177, 20240000, NULL, 95596316, -7, '2025-05-16 16:12:59');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `supplier_id` int(8) NOT NULL,
  `company_name` varchar(30) NOT NULL,
  `contact_person` varchar(30) NOT NULL,
  `mobile_number` varchar(11) NOT NULL,
  `email` varchar(30) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `company_name`, `contact_person`, `mobile_number`, `email`, `address`) VALUES
(15218775, 'IronClad Auto Supply', 'Andres Miguel Cruz', '09156473829', 'ironcladauto@email.com', '123 Outlook Drive, Barangay Mines View, Baguio City, Benguet, 2600, Philippines'),
(28924080, 'Supplier 10', 'Person1', '09284726182', 'supplier10@email.com', '123 Baguio City'),
(37403692, 'Supplier5', 'Denise Bayawa', '09260003225', 'denise@gmail.com', 'Baguio City'),
(38859181, 'DCB', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(49056642, 'ABC', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(64213870, 'XYZ', 'prim', '09264444555', 'prig@gmail.com', '123moo'),
(77830888, 'ert', 'Preyl Carillo', '09264111500', 'preyl@gmail.com', 'La Trinidad, Benguet'),
(77901272, 'GearLine Automotive', 'Rafael Domingo', '09752852476', 'gearlineauto@email.com', '89 Military Cut-Off Rd, Brgy. Military Cut-Off, Baguio City'),
(78746914, 'AutoVerse Supplies', 'Karla Mae Ramos', '09137535678', 'autoverse@email.com', '234 Marcos Highway, Baguio City, Benguet, Philippines'),
(84444515, 'DCB', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(88922363, 'Supplier2', 'Denise Bayawa', '09666320931', 'denise@gmail.com', 'La Trinidad, Benguet'),
(91869735, 'DCB', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(95562004, 'Supplier1', 'Shane Robiego', '09666323330', 'shane@gmail.com', 'Baguio City');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(8) NOT NULL,
  `first_name` varchar(15) DEFAULT NULL,
  `last_name` varchar(15) DEFAULT NULL,
  `image_url` varchar(75) DEFAULT NULL,
  `mobile_number` varchar(11) DEFAULT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(65) NOT NULL,
  `role` varchar(80) DEFAULT NULL,
  `user_roles` varchar(100) DEFAULT NULL,
  `permanent_address` varchar(200) DEFAULT NULL,
  `current_address` varchar(200) DEFAULT NULL,
  `emergency_contact` varchar(50) DEFAULT NULL,
  `emergency_contact_number` varchar(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `email_verification_sent_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `image_url`, `mobile_number`, `email`, `password`, `role`, `user_roles`, `permanent_address`, `current_address`, `emergency_contact`, `emergency_contact_number`, `created_at`, `email_verified_at`, `email_verification_sent_at`, `updated_at`) VALUES
(20240000, 'Aira', 'Carillo', 'Screenshot 2024-09-05 224041_1733851798.png', '09264003199', '20223765@s.ubaguio.edu', '$2y$10$YwMna1d6QUYGuvTHhRK6UOAtBj8x1VOHRWeGIUf5VcWfrPOxaT1aK', NULL, 'Administrator, Purchase Manager, Inventory Manager, Salesperson', NULL, NULL, NULL, NULL, '2024-10-16 12:50:03', '2024-12-10 15:17:22', '2024-12-10 14:10:57', '2025-05-17 03:08:46'),
(20240002, 'Shane', 'Robiego', 'Screenshot 2024-09-05 144341_1733325608.png', '09265004188', 'hiddenskylink@gmail.com', '$2y$10$YYrwA0m8EJBY.4D3hW1DYeAgwIhgM.JxP3PnN8aszCEcgtoMeWzYa', 'Administrator', 'Administrator, Inventory Manager', 'permanent address here with', 'current address here', 'contact person', '09555112999', '2024-12-04 02:34:42', '2024-12-04 14:12:07', '2024-12-04 14:08:48', '2025-04-27 09:32:36'),
(20240003, 'Preyl', 'Carillo', 'Screenshot 2024-09-05 144341_1733840216.png', '09264111534', 'carilloaira@gmail.com', '$2y$10$iGRloW9vCqF7S4ankLXZeek8B5323N.jlHwK1uVaIrTw2M.k3teVa', NULL, 'Inventory Manager, Administrator', 'Baguio City', NULL, NULL, NULL, '2024-12-05 07:52:26', '2024-12-05 07:52:49', '2024-12-05 07:52:26', '2025-05-16 11:38:39'),
(20250000, 'Jefferson', 'Laboy', NULL, NULL, '20217442@s.ubaguio.edu', '$2y$10$mCTI2YutKzT/PFUJpOZAT.vXHDdYE6RZSlFZf6.yGt4U/.2naXfL6', 'Purchase Manager', 'Administrator, Purchase Manager, Inventory Manager, Salesperson', NULL, NULL, NULL, NULL, '2025-04-27 09:30:38', '2025-04-27 09:31:12', '2025-04-27 09:30:38', '2025-05-18 14:39:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `delivery_purchase_orderFK` (`purchase_order_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `inventory_productFK` (`product_id`);

--
-- Indexes for table `inventory_audit`
--
ALTER TABLE `inventory_audit`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `audit_inventoryFK` (`inventory_id`),
  ADD KEY `audit_userFK` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_items_id`),
  ADD KEY `order_items_productFK` (`product_id`),
  ADD KEY `order_items_purchase_ordersFK` (`purchase_order_id`);

--
-- Indexes for table `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`order_statuses`);

--
-- Indexes for table `order_supplier`
--
ALTER TABLE `order_supplier`
  ADD PRIMARY KEY (`order_supplier_id`),
  ADD KEY `order_supplier_orderFK` (`purchase_order_id`),
  ADD KEY `order_supplier_supplierFK` (`supplier_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`password_reset_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `product_categoryFK` (`category_id`);

--
-- Indexes for table `product_supplier`
--
ALTER TABLE `product_supplier`
  ADD PRIMARY KEY (`product_supplier_id`),
  ADD KEY `product_supplier_productFK` (`product_id`),
  ADD KEY `product_supplier_supplierFK` (`supplier_id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`purchase_order_id`),
  ADD KEY `purchase_order_order_statusesFK` (`order_status`),
  ADD KEY `purchase_order_userFK` (`created_by`);

--
-- Indexes for table `return_product`
--
ALTER TABLE `return_product`
  ADD PRIMARY KEY (`return_product_id`),
  ADD KEY `return_product_userFK` (`user_id`),
  ADD KEY `return_product_scrapFK` (`scrap_product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sales_id`),
  ADD KEY `sales_userFK` (`user_id`);

--
-- Indexes for table `sales_details`
--
ALTER TABLE `sales_details`
  ADD PRIMARY KEY (`sales_details_id`),
  ADD KEY `sales_details_inventoryFK` (`inventory_id`),
  ADD KEY `sales_details_returnProductFK` (`return_product_id`),
  ADD KEY `sales_details_productFK` (`product_id`),
  ADD KEY `sales_id` (`sales_id`,`product_id`,`return_product_id`,`inventory_id`);

--
-- Indexes for table `scrap_product`
--
ALTER TABLE `scrap_product`
  ADD PRIMARY KEY (`scrap_product_id`),
  ADD KEY `scrap_userFK` (`user_id`);

--
-- Indexes for table `stockroom`
--
ALTER TABLE `stockroom`
  ADD PRIMARY KEY (`stockroom_id`),
  ADD KEY `stockroom_categoryFK` (`category_id`);

--
-- Indexes for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  ADD PRIMARY KEY (`stock_transfer_id`),
  ADD KEY `stock_transfer_productFK` (`product_id`),
  ADD KEY `stock_transfer_from_stockroomFK` (`from_stockroom_id`),
  ADD KEY `stock_transfer_to_stockroomFK` (`to_stockroom_id`),
  ADD KEY `stock_transfer_userFK` (`user_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `address_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67021418;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98009676;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `delivery_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93680266;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95974034;

--
-- AUTO_INCREMENT for table `inventory_audit`
--
ALTER TABLE `inventory_audit`
  MODIFY `audit_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_items_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96517750;

--
-- AUTO_INCREMENT for table `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `order_statuses` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `password_reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98593147;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `purchase_order_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98921571;

--
-- AUTO_INCREMENT for table `return_product`
--
ALTER TABLE `return_product`
  MODIFY `return_product_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99481542;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sales_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94080479;

--
-- AUTO_INCREMENT for table `sales_details`
--
ALTER TABLE `sales_details`
  MODIFY `sales_details_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93788145;

--
-- AUTO_INCREMENT for table `scrap_product`
--
ALTER TABLE `scrap_product`
  MODIFY `scrap_product_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98504951;

--
-- AUTO_INCREMENT for table `stockroom`
--
ALTER TABLE `stockroom`
  MODIFY `stockroom_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98474802;

--
-- AUTO_INCREMENT for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  MODIFY `stock_transfer_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95625590;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95562005;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20250001;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_purchase_orderFK` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_order` (`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `inventory_audit`
--
ALTER TABLE `inventory_audit`
  ADD CONSTRAINT `audit_inventoryFK` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `audit_userFK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_purchase_ordersFK` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_order` (`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_supplier`
--
ALTER TABLE `order_supplier`
  ADD CONSTRAINT `order_supplier_orderFK` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_order` (`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_supplier_supplierFK` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_categoryFK` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_supplier`
--
ALTER TABLE `product_supplier`
  ADD CONSTRAINT `product_supplier_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_supplier_supplierFK` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_order_statusesFK` FOREIGN KEY (`order_status`) REFERENCES `order_statuses` (`order_statuses`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_userFK` FOREIGN KEY (`created_by`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `return_product`
--
ALTER TABLE `return_product`
  ADD CONSTRAINT `return_product_scrapFK` FOREIGN KEY (`scrap_product_id`) REFERENCES `scrap_product` (`scrap_product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_product_userFK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_userFK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales_details`
--
ALTER TABLE `sales_details`
  ADD CONSTRAINT `sales_details_inventoryFK` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_details_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_details_returnProductFK` FOREIGN KEY (`return_product_id`) REFERENCES `return_product` (`return_product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_details_salesFK` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`sales_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scrap_product`
--
ALTER TABLE `scrap_product`
  ADD CONSTRAINT `scrap_userFK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stockroom`
--
ALTER TABLE `stockroom`
  ADD CONSTRAINT `stockroom_categoryFK` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  ADD CONSTRAINT `stock_transfer_from_stockroomFK` FOREIGN KEY (`from_stockroom_id`) REFERENCES `stockroom` (`stockroom_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_transfer_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_transfer_to_stockroomFK` FOREIGN KEY (`to_stockroom_id`) REFERENCES `stockroom` (`stockroom_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_transfer_userFK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;