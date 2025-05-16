-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 02, 2024 at 01:12 PM
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
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(8) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62251422 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(45992576, 'wheel'),
(51431926, 'Battery'),
(62251421, 'hinahawakan');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `purchase_price_per_unit` decimal(10,2) NOT NULL,
  `sale_price_per_unit` decimal(10,2) DEFAULT NULL,
  `unit_of_measure` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `in_stock` int(6) NOT NULL,
  `reorder_level` int(6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`inventory_id`),
  KEY `inventory_productFK` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59712525 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `purchase_price_per_unit`, `sale_price_per_unit`, `unit_of_measure`, `in_stock`, `reorder_level`, `created_at`, `updated_at`) VALUES
(19826892, 40739618, '55000.00', '62000.00', 'pcs', 14, 2, '2024-12-02 02:36:49', '2024-12-02 11:25:24'),
(31141065, 50220795, '36.00', '37.00', 'pcs', 16, 2, '2024-11-26 14:54:02', '2024-12-02 11:25:24'),
(59712524, 31299885, '24.00', '32.00', 'pcs', 10, 1, '2024-12-02 11:43:27', '2024-12-02 11:43:27');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_audit`
--

DROP TABLE IF EXISTS `inventory_audit`;
CREATE TABLE IF NOT EXISTS `inventory_audit` (
  `audit_id` int(8) NOT NULL AUTO_INCREMENT,
  `inventory_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `previous_quantity_on_hand` int(6) NOT NULL,
  `new_quantity_on_hand` int(6) NOT NULL,
  `previous_store_quantity` int(6) NOT NULL,
  `new_store_quantity` int(6) NOT NULL,
  `previous_stockroom_quantity` int(6) NOT NULL,
  `new_stockroom_quantity` int(6) NOT NULL,
  `in_stock_discrepancy` int(12) NOT NULL,
  `store_stock_discrepancy` int(12) NOT NULL,
  `stockroom_stock_discrepancy` int(12) NOT NULL,
  `discrepancy_reason` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resolve_steps` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `audit_date` timestamp NOT NULL,
  PRIMARY KEY (`audit_id`),
  KEY `audit_inventoryFK` (`inventory_id`),
  KEY `audit_userFK` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98300098 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_audit`
--

INSERT INTO `inventory_audit` (`audit_id`, `inventory_id`, `user_id`, `previous_quantity_on_hand`, `new_quantity_on_hand`, `previous_store_quantity`, `new_store_quantity`, `previous_stockroom_quantity`, `new_stockroom_quantity`, `in_stock_discrepancy`, `store_stock_discrepancy`, `stockroom_stock_discrepancy`, `discrepancy_reason`, `resolve_steps`, `audit_date`) VALUES
(14173389, 19826892, 20240001, 11, 10, 8, 5, 3, 5, -1, -3, 2, 'Incorrect', 'dutch resolve here', '2024-12-02 09:38:47'),
(29776903, 31141065, 20240001, 3, 10, 1, 5, 2, 5, -2, -3, 1, 'Human Error', 'kambyo here', '2024-12-02 10:00:48'),
(49998956, 19826892, 20240001, 12, 12, 8, 6, 4, 6, 0, -2, 2, 'Incorrect', 'dutch here', '2024-12-02 10:00:48'),
(52610482, 31141065, 20240001, 10, 16, 5, 8, 5, 8, 6, 3, 3, 'Human Error', 'kambyo here', '2024-12-02 11:25:24'),
(53272814, 19826892, 20240001, 12, 14, 6, 7, 6, 7, 2, 1, 1, 'Incorrect', 'dutch here', '2024-12-02 11:25:24'),
(57563330, 31141065, 20240001, 16, 17, 9, 12, 7, 5, 1, 3, -2, 'Incorrect count', '1. Actions Taken to Resolve Discrepancies Here.', '2024-12-01 23:27:17'),
(98300097, 19826892, 20240001, 11, 8, 8, 4, 3, 4, -3, -4, 1, 'Human Error', 'kambyo resolve here', '2024-12-02 09:38:47');

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
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `password_reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`password_reset_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`password_reset_id`, `email`, `token`, `created_at`) VALUES
(2, '20223765@s.ubaguio.edu', '$2y$10$y9eVatZeVXtPxZ0Y/8xVGu9C01bGiRip0yX3NJTosRAjc6T9VqktC', '2024-10-24 09:13:02'),
(3, 'carilloaira@gmail.com', '$2y$10$ENEnzDvIwf2yg.YszrGXBeyy/0QOSM5cRW0Nhmzw4r2uJVL8Z0EzK', '2024-11-26 13:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `product_id` int(8) NOT NULL AUTO_INCREMENT,
  `category_id` int(8) NOT NULL,
  `supplier_id` int(8) NOT NULL,
  `product_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`product_id`),
  KEY `product_categoryFK` (`category_id`),
  KEY `product_supplierFK` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=50220796 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `category_id`, `supplier_id`, `product_name`, `description`) VALUES
(31299885, 51431926, 84444515, 'Mill', '{\"color\":null,\"size\":null,\"description\":\"AA Battery\"}'),
(40739618, 45992576, 64213870, 'Dutch', '{\"color\":\"black\",\"size\":\"large\",\"description\":\"this is an item\"}'),
(50220795, 62251421, 49056642, 'kambyo', '{\"color\":\"pink\",\"size\":\"small\",\"description\":\"this is an item\"}');

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
  `return_date` timestamp NOT NULL,
  `scrap_product_id` int(8) DEFAULT NULL,
  `user_id` int(8) NOT NULL,
  PRIMARY KEY (`return_product_id`),
  KEY `return_product_userFK` (`user_id`),
  KEY `return_product_scrapFK` (`scrap_product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98811517 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `return_product`
--

INSERT INTO `return_product` (`return_product_id`, `return_quantity`, `total_return_amount`, `return_reason`, `return_date`, `scrap_product_id`, `user_id`) VALUES
(83851557, 1, 37, 'Damaged Product', '2024-12-02 07:11:34', NULL, 20240000),
(88942119, 1, 62000, 'Damaged Product', '2024-12-02 07:10:45', NULL, 20240000),
(98811516, 2, 74, 'Damaged Product', '2024-12-02 07:01:32', NULL, 20240000);

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
) ENGINE=InnoDB AUTO_INCREMENT=75275549 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sales_id`, `user_id`, `total_amount`, `sales_date`) VALUES
(22725940, 20240000, '37.00', '2024-12-02 03:29:31'),
(24995497, 20240000, '185.00', '2024-12-02 06:15:28'),
(41031817, 20240000, '62037.00', '2024-12-02 03:39:02'),
(54819379, 20240000, '124037.00', '2024-12-02 03:29:57'),
(61052879, 20240000, '62111.00', '2024-12-02 06:26:36'),
(70205673, 20240000, '310000.00', '2024-12-02 04:21:08'),
(75275548, 20240000, '185.00', '2024-12-02 08:50:15');

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
) ENGINE=InnoDB AUTO_INCREMENT=83763224 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_details`
--

INSERT INTO `sales_details` (`sales_details_id`, `sales_id`, `product_id`, `return_product_id`, `inventory_id`, `sales_quantity`, `amount`) VALUES
(28693595, 75275548, 50220795, NULL, 31141065, 5, '185.00'),
(30043645, 41031817, 50220795, 83851557, 31141065, 1, '37.00'),
(34569205, 61052879, 50220795, NULL, 31141065, 3, '111.00'),
(37536115, 41031817, 40739618, NULL, 19826892, 1, '62000.00'),
(64155091, 61052879, 40739618, NULL, 19826892, 1, '62000.00'),
(65423007, 22725940, 50220795, NULL, 31141065, 1, '0.00'),
(67240953, 54819379, 40739618, NULL, 19826892, 2, '0.00'),
(74423025, 54819379, 50220795, NULL, 31141065, 1, '0.00'),
(79125718, 24995497, 50220795, 98811516, 31141065, 5, '259.00'),
(83763223, 70205673, 40739618, 88942119, 19826892, 5, '310000.00');

-- --------------------------------------------------------

--
-- Table structure for table `scrap_product`
--

DROP TABLE IF EXISTS `scrap_product`;
CREATE TABLE IF NOT EXISTS `scrap_product` (
  `scrap_product_id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL,
  `scrap_quantity` int(11) NOT NULL,
  `scrap_date` timestamp NOT NULL,
  PRIMARY KEY (`scrap_product_id`),
  KEY `scrap_userFK` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockroom`
--

DROP TABLE IF EXISTS `stockroom`;
CREATE TABLE IF NOT EXISTS `stockroom` (
  `stockroom_id` int(8) NOT NULL AUTO_INCREMENT,
  `aisle_number` int(3) NOT NULL,
  `cabinet_level` int(3) NOT NULL,
  `product_quantity` int(6) NOT NULL,
  `category_id` int(8) NOT NULL,
  PRIMARY KEY (`stockroom_id`),
  KEY `stockroom_categoryFK` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94821359 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stockroom`
--

INSERT INTO `stockroom` (`stockroom_id`, `aisle_number`, `cabinet_level`, `product_quantity`, `category_id`) VALUES
(29923391, 1, 1, 7, 45992576),
(31876767, 1, 1, 8, 62251421),
(94821358, 2, 1, 8, 51431926);

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
) ENGINE=InnoDB AUTO_INCREMENT=95226749 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfer`
--

INSERT INTO `stock_transfer` (`stock_transfer_id`, `product_id`, `user_id`, `from_stockroom_id`, `to_stockroom_id`, `transfer_quantity`, `transfer_date`) VALUES
(24840858, 40739618, 20240000, NULL, 29923391, 2, '2024-12-02 06:00:21'),
(31237881, 40739618, 20240000, NULL, 29923391, 1, '2024-12-02 06:08:55'),
(42592048, 50220795, 20240000, 31876767, NULL, 1, '2024-12-02 08:51:02'),
(52147713, 40739618, 20240000, NULL, 29923391, 7, '2024-12-02 02:36:49'),
(52544926, 50220795, 20240000, NULL, 31876767, 7, '2024-11-26 14:54:02'),
(57771144, 40739618, 20240000, 29923391, NULL, 7, '2024-12-02 05:18:54'),
(58564383, 31299885, 20240000, NULL, 94821358, 8, '2024-12-02 11:43:27'),
(58654608, 50220795, 20240000, 31876767, NULL, 2, '2024-12-02 08:50:15'),
(66397573, 40739618, 20240000, NULL, 29923391, 2, '2024-12-02 06:14:20'),
(66763632, 50220795, 20240000, NULL, 31876767, 5, '2024-12-02 06:25:14'),
(80232968, 50220795, 20240000, 31876767, NULL, 1, '2024-12-02 06:16:09'),
(82975880, 50220795, 20240000, 31876767, NULL, 1, '2024-12-02 06:31:04'),
(95226748, 50220795, 20240000, 31876767, NULL, 3, '2024-12-02 06:24:47');

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
) ENGINE=InnoDB AUTO_INCREMENT=84444516 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `company_name`, `contact_person`, `mobile_number`, `email`, `address`) VALUES
(49056642, 'ABC', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(64213870, 'XYZ', 'prim', '09264444555', 'prig@gmail.com', '123moo'),
(84444515, 'DCB', 'prig', '09264444555', 'prig@gmail.com', '123moo');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(8) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_password` varchar(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20240003 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `image_url`, `mobile_number`, `email`, `username`, `password`, `role`, `default_password`, `created_at`, `email_verified_at`, `updated_at`) VALUES
(1, 'Admin', 'Dumpstreet', 'Screenshot 2024-07-15 224616_1729079763.png', '09555111222', 'admin@gmail.com', 'admin', '$2y$10$Bv9Rch9DpeNPMMcK4GXP2.PP5ZzpI1JsLaio.hqYKSe8ujERkffrS', 'Administrator', '', '2024-10-16 03:56:03', NULL, '2024-10-16 03:56:03'),
(20240000, 'Aira', 'Carillo', 'Screenshot 2024-09-05 144341_1729083003.png', '09264003199', '20223765@s.ubaguio.edu', 'aira', '$2y$10$.s55mEkQtzbhhAcuDtlvjui4Hvm2KhKEM3kpHprJ3FnMHUrPnN1eW', 'Inventory Manager', '', '2024-10-16 12:50:03', '2024-10-16 12:51:26', '2024-10-16 13:14:52'),
(20240001, 'Preyl', 'Carillo', 'Screenshot 2024-09-05 224041_1730378345.png', '09264111555', 'carilloaira@gmail.com', 'preyl', '$2y$10$8yxROKHCtdvP2m/c8aDM8OKxTqQK47x89qytfhBEMT1V4HpWLq4tq', 'Auditor', '', '2024-10-31 12:39:05', '2024-10-31 12:39:59', '2024-12-01 23:58:45'),
(20240002, 'Aira', 'Carillo', 'Screenshot 2024-09-05 144341_1733124319.png', '09260003223', 'hiddenskylink@gmail.com', 'airaC', '$2y$10$KZvnHYHuufEMBe1bVyLW5eJcyld.5uHt/iOCCTco71E2fubW5YDHW', 'Inventory Manager', NULL, '2024-12-02 07:25:19', '2024-12-02 07:25:47', '2024-12-02 11:54:21');

--
-- Constraints for dumped tables
--

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
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_categoryFK` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_supplierFK` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
