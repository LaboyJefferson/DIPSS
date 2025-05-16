-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 02, 2024 at 03:08 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=85826257 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'paint'),
(23324260, 'paint'),
(35932965, 'paint'),
(37755756, 'paint'),
(39990356, 'paint'),
(42055810, 'Battery'),
(46375871, 'ab'),
(52231141, 'sdsdf'),
(52761566, 'Engine'),
(58053808, 'wheel'),
(75592005, 'paint'),
(85826256, 'Battery');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `purchase_price_per_unit` float NOT NULL,
  `sale_price_per_unit` float DEFAULT NULL,
  `unit_of_measure` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `in_stock` int(6) NOT NULL,
  `reorder_level` int(6) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`inventory_id`),
  KEY `inventory_productFK` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67255889 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `purchase_price_per_unit`, `sale_price_per_unit`, `unit_of_measure`, `in_stock`, `reorder_level`, `created_at`, `updated_at`) VALUES
(20249977, 23837585, 17645, 25999, 'pcs', 11, 2, '2024-11-01 09:40:03', '2024-11-01 14:18:46'),
(67255888, 21847183, 3980, 4680, 'pcs', 11, 1, '2024-11-01 11:42:41', '2024-11-01 13:33:28');

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
  `new_store_quantity` int(6) NOT NULL,
  `new_stockroom_quantity` int(6) NOT NULL,
  `variance` float NOT NULL,
  `reason` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `audit_date` timestamp NOT NULL,
  PRIMARY KEY (`audit_id`),
  KEY `audit_inventoryFK` (`inventory_id`),
  KEY `audit_userFK` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71384172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_audit`
--

INSERT INTO `inventory_audit` (`audit_id`, `inventory_id`, `user_id`, `previous_quantity_on_hand`, `new_quantity_on_hand`, `new_store_quantity`, `new_stockroom_quantity`, `variance`, `reason`, `audit_date`) VALUES
(48127875, 67255888, 20240001, 10, 11, 1, 10, 1, 'miscalculation', '2024-11-01 13:33:28'),
(71384171, 20249977, 20240001, 10, 11, 4, 7, 1, 'missed', '2024-11-01 14:18:46');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`password_reset_id`, `email`, `token`, `created_at`) VALUES
(2, '20223765@s.ubaguio.edu', '$2y$10$y9eVatZeVXtPxZ0Y/8xVGu9C01bGiRip0yX3NJTosRAjc6T9VqktC', '2024-10-24 09:13:02');

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
) ENGINE=InnoDB AUTO_INCREMENT=23837586 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `category_id`, `supplier_id`, `product_name`, `description`) VALUES
(21847183, 52761566, 32211237, 'Mill', '{\"color\":null,\"size\":null,\"description\":\"Gasoline Engine\"}'),
(23837585, 85826256, 80639714, 'Dutch', '{\"color\":null,\"size\":null,\"description\":\"Lithium Iron Phosphate\"}');

-- --------------------------------------------------------

--
-- Table structure for table `return_product`
--

DROP TABLE IF EXISTS `return_product`;
CREATE TABLE IF NOT EXISTS `return_product` (
  `return_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `return_quantity` int(11) NOT NULL,
  `total_return_amount` float NOT NULL,
  `return_reason` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `return_date` timestamp NULL DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`return_product_id`),
  KEY `return_product_userFK` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `sales_id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL,
  `total_amount` float NOT NULL,
  `sales_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sales_id`),
  KEY `sales_userFK` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89434095 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sales_id`, `user_id`, `total_amount`, `sales_date`) VALUES
(20982160, 20240000, 40039, '2024-11-01 11:47:05'),
(60871070, 20240000, 103996, '2024-11-01 10:47:47'),
(89384234, 20240000, 103996, '2024-11-01 10:57:59'),
(89434094, 20240000, 103996, '2024-11-01 10:40:05');

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
  PRIMARY KEY (`sales_details_id`),
  KEY `sales_details_inventoryFK` (`inventory_id`),
  KEY `sales_details_returnProductFK` (`return_product_id`),
  KEY `sales_details_productFK` (`product_id`),
  KEY `sales_id` (`sales_id`,`product_id`,`return_product_id`,`inventory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=91040733 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_details`
--

INSERT INTO `sales_details` (`sales_details_id`, `sales_id`, `product_id`, `return_product_id`, `inventory_id`, `sales_quantity`) VALUES
(23140448, 60871070, 23837585, NULL, 20249977, 4),
(44077701, 20982160, 21847183, NULL, 67255888, 3),
(67622288, 20982160, 23837585, NULL, 20249977, 1),
(77605056, 89384234, 23837585, NULL, 20249977, 4),
(91040732, 89434094, 23837585, NULL, 20249977, 4);

-- --------------------------------------------------------

--
-- Table structure for table `scrap_product`
--

DROP TABLE IF EXISTS `scrap_product`;
CREATE TABLE IF NOT EXISTS `scrap_product` (
  `scrap_product_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `stockroom_id` int(8) NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scrap_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`scrap_product_id`),
  KEY `scrap_product_productFK` (`product_id`),
  KEY `scrap_product_stockroomFK` (`stockroom_id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=51896850 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stockroom`
--

INSERT INTO `stockroom` (`stockroom_id`, `aisle_number`, `cabinet_level`, `product_quantity`, `category_id`) VALUES
(51240996, 2, 1, 10, 52761566),
(51896849, 1, 1, 7, 85826256);

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
) ENGINE=InnoDB AUTO_INCREMENT=93930888 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfer`
--

INSERT INTO `stock_transfer` (`stock_transfer_id`, `product_id`, `user_id`, `from_stockroom_id`, `to_stockroom_id`, `transfer_quantity`, `transfer_date`) VALUES
(45251421, 21847183, 20240000, NULL, 51240996, 9, '2024-11-01 11:42:41'),
(50702794, 23837585, 20240000, NULL, 51896849, 12, '2024-11-01 09:40:03'),
(72130414, 23837585, 20240000, 51896849, NULL, 1, '2024-11-01 10:57:59'),
(93930887, 23837585, 20240000, 51896849, NULL, 5, '2024-11-01 11:38:34');

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
) ENGINE=InnoDB AUTO_INCREMENT=80639715 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `company_name`, `contact_person`, `mobile_number`, `email`, `address`) VALUES
(32211237, 'ABC', 'prig', '09264444555', 'prig@gmail.com', '123moo'),
(80639714, 'ABC', 'prig', '09264444555', 'prig@gmail.com', '123moo');

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
  `password` varchar(65) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20240002 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `image_url`, `mobile_number`, `email`, `username`, `password`, `role`, `created_at`, `email_verified_at`, `updated_at`) VALUES
(1, 'Admin', 'Dumpstreet', 'Screenshot 2024-07-15 224616_1729079763.png', '09555111222', 'admin@gmail.com', 'admin', '$2y$10$Bv9Rch9DpeNPMMcK4GXP2.PP5ZzpI1JsLaio.hqYKSe8ujERkffrS', 'Administrator', '2024-10-16 03:56:03', NULL, '2024-10-16 03:56:03'),
(20240000, 'Aira', 'Carillo', 'Screenshot 2024-09-05 144341_1729083003.png', '09264003199', '20223765@s.ubaguio.edu', 'aira', '$2y$10$.s55mEkQtzbhhAcuDtlvjui4Hvm2KhKEM3kpHprJ3FnMHUrPnN1eW', 'Inventory Manager', '2024-10-16 12:50:03', '2024-10-16 12:51:26', '2024-10-16 13:14:52'),
(20240001, 'Preyl', 'Carillo', 'Screenshot 2024-09-05 224041_1730378345.png', '09264111555', 'carilloaira@gmail.com', 'preyl', '$2y$10$yfxjM5.GeHRH0BkFezl8UuyTZP9F3jKrmn/Dnn6hAJ8PhqJAw1Uoy', 'Auditor', '2024-10-31 12:39:05', '2024-10-31 12:39:59', '2024-10-31 12:39:59');

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
  ADD CONSTRAINT `scrap_product_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scrap_product_stockroomFK` FOREIGN KEY (`stockroom_id`) REFERENCES `stockroom` (`stockroom_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
