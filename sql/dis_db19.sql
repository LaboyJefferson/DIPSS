-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 17, 2025 at 03:13 AM
-- Server version: 5.7.40
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dis_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

DROP TABLE IF EXISTS `address`;
CREATE TABLE IF NOT EXISTS `address` (
  `address_id` int(8) NOT NULL AUTO_INCREMENT,
  `address` varchar(100) NOT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67021418 DEFAULT CHARSET=utf8mb4;

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

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(8) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98009676 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE IF NOT EXISTS `deliveries` (
  `delivery_id` int(8) NOT NULL AUTO_INCREMENT,
  `issued_date` date NOT NULL,
  `date_delivered` date DEFAULT NULL,
  `purchase_order_id` int(8) NOT NULL,
  PRIMARY KEY (`delivery_id`),
  KEY `delivery_purchase_orderFK` (`purchase_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `purchase_price_per_unit` decimal(10,2) DEFAULT NULL,
  `sale_price_per_unit` decimal(10,2) DEFAULT NULL,
  `unit_of_measure` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_stock` int(6) DEFAULT NULL,
  `reorder_level` int(6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`inventory_id`),
  KEY `inventory_productFK` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95974034 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `purchase_price_per_unit`, `sale_price_per_unit`, `unit_of_measure`, `in_stock`, `reorder_level`, `created_at`, `updated_at`) VALUES
(17191477, 46004118, '24.00', '32.00', 'pcs', 19, 10, '2024-12-03 03:02:35', '2024-12-08 07:22:09'),
(19826892, 40739618, '55000.00', '62000.00', 'pcs', 17, 2, '2024-12-02 02:36:49', '2024-12-08 07:22:09'),
(30678416, 73086209, '15000.00', '18000.00', 'box', 18, 1, '2024-12-16 01:21:58', '2025-05-16 16:12:59'),
(59712524, 31299885, '24.00', '32.00', 'pcs', 14, 1, '2024-12-02 11:43:27', '2024-12-08 07:22:09'),
(82984096, 18975177, '5000.00', '1100.00', 'pair', 14, 5, '2024-12-06 01:27:02', '2025-05-16 16:12:59'),
(85845553, 84495846, '6400.00', '7635.00', 'pcs', 22, 5, '2024-12-06 01:36:05', '2024-12-08 07:22:09'),
(91278222, 87227981, '0.00', '0.00', 'pcs', 0, 3, '2025-05-13 15:17:48', '2025-05-13 15:17:48'),
(95154263, 65174824, '24.00', '32.00', 'pcs', 24, 1, '2024-12-02 14:20:39', '2024-12-08 07:22:09'),
(95974033, 15076522, '15000.00', '18000.00', 'pcs', 15, 8, '2024-12-06 00:19:22', '2024-12-10 17:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_audit`
--

DROP TABLE IF EXISTS `inventory_audit`;
CREATE TABLE IF NOT EXISTS `inventory_audit` (
  `audit_id` int(8) NOT NULL AUTO_INCREMENT,
  `inventory_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `physical_storestock_count` int(6) NOT NULL,
  `system_storestock_record` int(6) NOT NULL,
  `storestock_discrepancies` int(6) NOT NULL,
  `physical_stockroom_count` int(6) NOT NULL,
  `system_stockroom_record` int(6) NOT NULL,
  `stockroom_discrepancies` int(6) NOT NULL,
  `in_stock_discrepancies` int(6) NOT NULL,
  `discrepancy_reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audit_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `audit_inventoryFK` (`inventory_id`),
  KEY `audit_userFK` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_items_id` int(8) NOT NULL AUTO_INCREMENT,
  `quantity` int(6) NOT NULL,
  `price` float DEFAULT NULL,
  `delivered_quantity` int(6) DEFAULT NULL,
  `purchase_order_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL,
  PRIMARY KEY (`order_items_id`),
  KEY `order_items_productFK` (`product_id`),
  KEY `order_items_purchase_ordersFK` (`purchase_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73387781 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_items_id`, `quantity`, `price`, `delivered_quantity`, `purchase_order_id`, `product_id`) VALUES
(31400759, 30, 192000, 30, 25288886, 84495846),
(35916894, 10, 50000, 10, 25288886, 18975177),
(54498344, 24, 576, NULL, 52004142, 46004118),
(71975182, 12, 660000, NULL, 52004142, 40739618),
(73387780, 20, 300000, NULL, 92617103, 15076522);

-- --------------------------------------------------------

--
-- Table structure for table `order_statuses`
--

DROP TABLE IF EXISTS `order_statuses`;
CREATE TABLE IF NOT EXISTS `order_statuses` (
  `order_statuses` int(11) NOT NULL AUTO_INCREMENT,
  `status_name` varchar(15) NOT NULL,
  `status_description` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`order_statuses`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

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

DROP TABLE IF EXISTS `order_supplier`;
CREATE TABLE IF NOT EXISTS `order_supplier` (
  `order_supplier_id` int(8) NOT NULL,
  `purchase_order_id` int(8) NOT NULL,
  `supplier_id` int(8) NOT NULL,
  PRIMARY KEY (`order_supplier_id`),
  KEY `order_supplier_orderFK` (`purchase_order_id`),
  KEY `order_supplier_supplierFK` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_supplier`
--

INSERT INTO `order_supplier` (`order_supplier_id`, `purchase_order_id`, `supplier_id`) VALUES
(11366832, 52004142, 37403692),
(31826687, 25288886, 95562004),
(43820272, 52004142, 28924080),
(65795067, 92617103, 88922363);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `password_reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`password_reset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `product_id` int(8) NOT NULL AUTO_INCREMENT,
  `category_id` int(8) NOT NULL,
  `image_url` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`product_id`),
  KEY `product_categoryFK` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=87227982 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `category_id`, `image_url`, `product_name`, `description`) VALUES
(15076522, 18673629, 'Screenshot 2024-09-05 144341_1733444362.png', 'product1', '{\"color\":null,\"size\":\"large\",\"description\":\"for cars\"}'),
(18975177, 98009675, 'Screenshot 2024-09-05 144341_1733448422.png', 'product2', '{\"color\":null,\"size\":null,\"description\":\"for cars\"}'),
(31299885, 51431926, '', 'Mill', '{\"color\":null,\"size\":null,\"description\":\"AA Battery\"}'),
(40739618, 45992576, '', 'Dutch', '{\"color\":\"black\",\"size\":\"large\",\"description\":\"this is an item\"}'),
(46004118, 36200226, '', 'Side Mirror', '{\"color\":\"Black\",\"size\":\"25\",\"description\":\"AA Battery\"}'),
(65174824, 18673629, '', 'Lcal', '{\"color\":null,\"size\":null,\"description\":\"AA Battery\"}'),
(73086209, 48312275, 'dis_erd.drawio (5)_1734312117.png', 'product6', '{\"color\":null,\"size\":null,\"description\":null}'),
(84495846, 48312275, 'Screenshot 2024-09-05 224041_1733448965.png', 'product3', '{\"color\":null,\"size\":null,\"description\":\"Lead-Acid Batteries\"}'),
(87227981, 18673629, '496604569_700373569522515_776652809181130229_n_1747149468.jpg', 'Suspension', '{\"color\":null,\"size\":null,\"description\":null}');

-- --------------------------------------------------------

--
-- Table structure for table `product_supplier`
--

DROP TABLE IF EXISTS `product_supplier`;
CREATE TABLE IF NOT EXISTS `product_supplier` (
  `product_supplier_id` int(8) NOT NULL,
  `supplier_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL,
  PRIMARY KEY (`product_supplier_id`),
  KEY `product_supplier_productFK` (`product_id`),
  KEY `product_supplier_supplierFK` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product_supplier`
--

INSERT INTO `product_supplier` (`product_supplier_id`, `supplier_id`, `product_id`) VALUES
(19282025, 95562004, 18975177),
(41106632, 91869735, 46004118),
(53666861, 88922363, 15076522),
(55465352, 64213870, 40739618),
(68898922, 38859181, 65174824),
(86477133, 77830888, 73086209),
(87963721, 95562004, 84495846),
(91931877, 84444515, 31299885);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

DROP TABLE IF EXISTS `purchase_order`;
CREATE TABLE IF NOT EXISTS `purchase_order` (
  `purchase_order_id` int(8) NOT NULL AUTO_INCREMENT,
  `type` enum('Purchasing Order','Backorder','','') NOT NULL DEFAULT 'Purchasing Order',
  `payment_method` text NOT NULL,
  `billing_address` varchar(255) DEFAULT NULL,
  `shipping_address` varchar(100) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `reason` varchar(60) DEFAULT NULL,
  `created_by` int(8) NOT NULL,
  `order_status` int(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`purchase_order_id`),
  KEY `purchase_order_order_statusesFK` (`order_status`),
  KEY `purchase_order_userFK` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=92617104 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`purchase_order_id`, `type`, `payment_method`, `billing_address`, `shipping_address`, `total_price`, `reason`, `created_by`, `order_status`, `created_at`, `updated_at`) VALUES
(25288886, 'Purchasing Order', 'Cash on Delivery (COD)', 'Baguio City', '10 Upper Session Rd, Baguio City, Benguet', '484000.00', NULL, 20250000, 3, '2025-05-13 17:58:59', NULL),
(52004142, 'Purchasing Order', 'Cash on Delivery (COD)', NULL, '10 Upper Session Rd, Baguio City, Benguet', '7933824.00', NULL, 20250000, 2, '2025-05-13 18:36:52', '2025-05-13 18:36:51'),
(92617103, 'Purchasing Order', 'Cash on Delivery (COD)', 'La Trinidad, Benguet', '10 Upper Session Rd, Baguio City, Benguet', '600000.00', NULL, 20250000, 2, '2025-05-13 17:58:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `return_product`
--

DROP TABLE IF EXISTS `return_product`;
CREATE TABLE IF NOT EXISTS `return_product` (
  `return_product_id` int(8) NOT NULL AUTO_INCREMENT,
  `return_quantity` int(8) NOT NULL,
  `total_return_amount` float NOT NULL,
  `return_reason` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `return_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scrap_product_id` int(8) DEFAULT NULL,
  `user_id` int(8) NOT NULL,
  PRIMARY KEY (`return_product_id`),
  KEY `return_product_userFK` (`user_id`),
  KEY `return_product_scrapFK` (`scrap_product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99481542 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `sales_id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `sales_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sales_id`),
  KEY `sales_userFK` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94080479 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sales_id`, `user_id`, `total_amount`, `sales_date`) VALUES
(22725940, 20240000, '37.00', '2024-12-02 03:29:31'),
(23249319, 20240000, '62096.00', '2024-12-10 13:39:09'),
(24995497, 20240000, '185.00', '2024-12-02 06:15:28'),
(27709313, 20240000, '4526000.00', '2024-12-02 14:54:39'),
(32333087, 20240002, '400.00', '2024-12-10 14:32:07'),
(41031817, 20240000, '37.00', '2024-12-02 03:39:02'),
(54819379, 20240000, '62037.00', '2024-12-02 03:29:57'),
(55617457, 20240003, '91100.00', '2024-12-16 01:25:20'),
(61052879, 20240000, '62111.00', '2024-12-02 06:26:36'),
(70205673, 20240000, '310000.00', '2024-12-02 04:21:08'),
(75275548, 20240000, '185.00', '2024-12-02 08:50:15'),
(85216851, 20240000, '160.00', '2024-12-10 12:50:41'),
(94080478, 20240000, '133.00', '2024-12-02 14:37:04');

-- --------------------------------------------------------

--
-- Table structure for table `sales_details`
--

DROP TABLE IF EXISTS `sales_details`;
CREATE TABLE IF NOT EXISTS `sales_details` (
  `sales_details_id` int(8) NOT NULL AUTO_INCREMENT,
  `sales_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL,
  `return_product_id` int(8) DEFAULT NULL,
  `inventory_id` int(8) NOT NULL,
  `sales_quantity` int(6) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`sales_details_id`),
  KEY `sales_details_inventoryFK` (`inventory_id`),
  KEY `sales_details_returnProductFK` (`return_product_id`),
  KEY `sales_details_productFK` (`product_id`),
  KEY `sales_id` (`sales_id`,`product_id`,`return_product_id`,`inventory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93788145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_details`
--

INSERT INTO `sales_details` (`sales_details_id`, `sales_id`, `product_id`, `return_product_id`, `inventory_id`, `sales_quantity`, `amount`) VALUES
(22448519, 32333087, 40739618, 42006337, 19826892, 0, '0.00'),
(25563083, 55617457, 15076522, NULL, 95974033, 5, '90000.00'),
(37536115, 41031817, 40739618, 99481541, 19826892, 0, '0.00'),
(57018646, 27709313, 40739618, NULL, 19826892, 73, '4526000.00'),
(63492342, 23249319, 46004118, NULL, 17191477, 3, '96.00'),
(64155091, 61052879, 40739618, NULL, 19826892, 1, '62000.00'),
(67240953, 54819379, 40739618, 50382363, 19826892, 1, '-62000.00'),
(74880949, 85216851, 31299885, NULL, 59712524, 5, '160.00'),
(83763223, 70205673, 40739618, 88942119, 19826892, 5, '310000.00'),
(91836385, 23249319, 40739618, 77122285, 19826892, 1, '62000.00'),
(92939156, 94080478, 31299885, NULL, 59712524, 3, '96.00'),
(93788144, 55617457, 18975177, NULL, 82984096, 1, '1100.00');

-- --------------------------------------------------------

--
-- Table structure for table `scrap_product`
--

DROP TABLE IF EXISTS `scrap_product`;
CREATE TABLE IF NOT EXISTS `scrap_product` (
  `scrap_product_id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL,
  `scrap_quantity` int(11) NOT NULL,
  `scrap_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`scrap_product_id`),
  KEY `scrap_userFK` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98504951 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `stockroom`;
CREATE TABLE IF NOT EXISTS `stockroom` (
  `stockroom_id` int(8) NOT NULL AUTO_INCREMENT,
  `aisle_number` int(3) DEFAULT NULL,
  `cabinet_level` int(3) DEFAULT NULL,
  `product_quantity` int(6) DEFAULT NULL,
  `category_id` int(8) NOT NULL,
  PRIMARY KEY (`stockroom_id`),
  KEY `stockroom_categoryFK` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95596317 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stockroom`
--

INSERT INTO `stockroom` (`stockroom_id`, `aisle_number`, `cabinet_level`, `product_quantity`, `category_id`) VALUES
(25813646, 1, 1, NULL, 18673629),
(28406217, 2, 3, 0, 18673629),
(29923391, 1, 1, 9, 45992576),
(31876767, 1, 1, 6, 62251421),
(42633164, 1, 1, NULL, 18673629),
(44505293, 1, 1, NULL, 18673629),
(52669463, 1, 1, 9, 48312275),
(59810781, 1, 1, NULL, 18673629),
(64511234, 1, 5, 7, 48312275),
(70620176, 1, 1, NULL, 18673629),
(75582219, 1, 1, NULL, 18673629),
(77336261, 3, 1, 7, 18673629),
(80898093, 1, 1, 10, 36200226),
(92056880, 1, 1, 8, 18673629),
(93949212, 3, 5, 20, 43435445),
(94821358, 2, 1, 7, 51431926),
(95596316, 3, 4, 5, 98009675);

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer`
--

DROP TABLE IF EXISTS `stock_transfer`;
CREATE TABLE IF NOT EXISTS `stock_transfer` (
  `stock_transfer_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `from_stockroom_id` int(8) DEFAULT NULL,
  `to_stockroom_id` int(8) DEFAULT NULL,
  `transfer_quantity` int(11) NOT NULL,
  `transfer_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`stock_transfer_id`),
  KEY `stock_transfer_productFK` (`product_id`),
  KEY `stock_transfer_from_stockroomFK` (`from_stockroom_id`),
  KEY `stock_transfer_to_stockroomFK` (`to_stockroom_id`),
  KEY `stock_transfer_userFK` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95625590 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfer`
--

INSERT INTO `stock_transfer` (`stock_transfer_id`, `product_id`, `user_id`, `from_stockroom_id`, `to_stockroom_id`, `transfer_quantity`, `transfer_date`) VALUES
(17926354, 40739618, 20240002, 29923391, 29923391, 1, '2024-12-06 15:24:31'),
(22232209, 18975177, 20240002, NULL, 95596316, 4, '2024-12-06 01:27:02'),
(23230396, 15076522, 20240002, NULL, 77336261, 5, '2024-12-10 17:42:41'),
(24840858, 40739618, 20240000, NULL, 29923391, 2, '2024-12-02 06:00:21'),
(28579847, 31299885, 20240000, 94821358, NULL, 1, '2025-05-16 17:19:44'),
(31237881, 40739618, 20240000, NULL, 29923391, 1, '2024-12-02 06:08:55'),
(39434407, 15076522, 20240002, 77336261, NULL, 7, '2024-12-10 17:41:37'),
(41993101, 46004118, 20240000, NULL, 80898093, 8, '2024-12-03 03:02:35'),
(42484241, 84495846, 20240002, NULL, 64511234, 6, '2024-12-06 01:36:05'),
(47629510, 73086209, 20240003, NULL, 52669463, 6, '2024-12-16 01:21:58'),
(52147713, 40739618, 20240000, NULL, 29923391, 7, '2024-12-02 02:36:49'),
(57771144, 40739618, 20240000, 29923391, NULL, 7, '2024-12-02 05:18:54'),
(58564383, 31299885, 20240000, NULL, 94821358, 8, '2024-12-02 11:43:27'),
(62462124, 31299885, 20240000, 94821358, NULL, 1, '2024-12-02 14:37:04'),
(66397573, 40739618, 20240000, NULL, 29923391, 2, '2024-12-02 06:14:20'),
(72666753, 87227981, 20250000, NULL, 28406217, 0, '2025-05-17 00:26:49'),
(76411566, 65174824, 20240000, NULL, 92056880, 8, '2024-12-02 14:20:39'),
(85055718, 15076522, 20240002, NULL, 77336261, 10, '2024-12-06 15:21:04'),
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

DROP TABLE IF EXISTS `supplier`;
CREATE TABLE IF NOT EXISTS `supplier` (
  `supplier_id` int(8) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95562005 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `company_name`, `contact_person`, `mobile_number`, `email`, `address`) VALUES
(28924080, 'Supplier 10', 'Person1', '09284726182', 'supplier10@email.com', '123 Baguio City'),
(37403692, 'Supplier5', 'Denise Bayawa', '09260003225', 'denise@gmail.com', 'Baguio City'),
(38859181, 'DCB', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(49056642, 'ABC', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(64213870, 'XYZ', 'prim', '09264444555', 'prig@gmail.com', '123moo'),
(77830888, 'ert', 'Preyl Carillo', '09264111500', 'preyl@gmail.com', 'La Trinidad, Benguet'),
(84444515, 'DCB', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(88922363, 'Supplier2', 'Denise Bayawa', '09666320931', 'denise@gmail.com', 'La Trinidad, Benguet'),
(91869735, 'DCB', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(95562004, 'Supplier1', 'Shane Robiego', '09666323330', 'shane@gmail.com', 'Baguio City');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(8) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_number` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(65) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_roles` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_number` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `email_verification_sent_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20250001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `image_url`, `mobile_number`, `email`, `password`, `role`, `user_roles`, `permanent_address`, `current_address`, `emergency_contact`, `emergency_contact_number`, `created_at`, `email_verified_at`, `email_verification_sent_at`, `updated_at`) VALUES
(20240000, 'Aira', 'Carillo', 'Screenshot 2024-09-05 224041_1733851798.png', '09264003199', '20223765@s.ubaguio.edu', '$2y$10$YwMna1d6QUYGuvTHhRK6UOAtBj8x1VOHRWeGIUf5VcWfrPOxaT1aK', NULL, 'Administrator, Purchase Manager, Inventory Manager, Salesperson', NULL, NULL, NULL, NULL, '2024-10-16 12:50:03', '2024-12-10 15:17:22', '2024-12-10 14:10:57', '2025-05-17 03:08:46'),
(20240002, 'Shane', 'Robiego', 'Screenshot 2024-09-05 144341_1733325608.png', '09265004188', 'hiddenskylink@gmail.com', '$2y$10$YYrwA0m8EJBY.4D3hW1DYeAgwIhgM.JxP3PnN8aszCEcgtoMeWzYa', 'Administrator', 'Administrator, Inventory Manager', 'permanent address here with', 'current address here', 'contact person', '09555112999', '2024-12-04 02:34:42', '2024-12-04 14:12:07', '2024-12-04 14:08:48', '2025-04-27 09:32:36'),
(20240003, 'Preyl', 'Carillo', 'Screenshot 2024-09-05 144341_1733840216.png', '09264111534', 'carilloaira@gmail.com', '$2y$10$iGRloW9vCqF7S4ankLXZeek8B5323N.jlHwK1uVaIrTw2M.k3teVa', NULL, 'Inventory Manager, Administrator', 'Baguio City', NULL, NULL, NULL, '2024-12-05 07:52:26', '2024-12-05 07:52:49', '2024-12-05 07:52:26', '2025-05-16 11:38:39'),
(20250000, 'Jefferson', 'Laboy', NULL, NULL, '20217442@s.ubaguio.edu', '$2y$10$mCTI2YutKzT/PFUJpOZAT.vXHDdYE6RZSlFZf6.yGt4U/.2naXfL6', NULL, 'Administrator, Purchase Manager, Inventory Manager, Salesperson', NULL, NULL, NULL, NULL, '2025-04-27 09:30:38', '2025-04-27 09:31:12', '2025-04-27 09:30:38', '2025-05-13 19:07:41');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
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
