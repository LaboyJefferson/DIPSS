-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 19, 2024 at 02:12 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `purchase_price_per_unit` int(11) NOT NULL,
  `sale_price_per_unit` int(11) NOT NULL,
  `unit_of_measure` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `in_stock` int(11) NOT NULL,
  `reorder_level` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `stockroom_id` int(8) NOT NULL,
  `stock_transfer_id` int(8) NOT NULL,
  PRIMARY KEY (`inventory_id`),
  KEY `inventory_productFK` (`product_id`),
  KEY `inventory_stockTransferFK` (`stock_transfer_id`),
  KEY `inventory_stockroomFK` (`stockroom_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`password_reset_id`, `email`, `token`, `created_at`) VALUES
(1, '20223765@s.ubaguio.edu', '$2y$10$QG/S/ew04iE2eoan1Z.2H.YUSZY5KSrCHOSGc2xpF0w3ar2RIgfNS', '2024-10-16 13:13:36');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order`
--

DROP TABLE IF EXISTS `sales_order`;
CREATE TABLE IF NOT EXISTS `sales_order` (
  `sales_order_id` int(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(8) NOT NULL,
  `total_amount` int(11) NOT NULL,
  `sales_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sales_order_id`),
  KEY `sales_order_userFK` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_detail`
--

DROP TABLE IF EXISTS `sales_order_detail`;
CREATE TABLE IF NOT EXISTS `sales_order_detail` (
  `sales_order_detail_id` int(8) NOT NULL AUTO_INCREMENT,
  `sale_order_id` int(8) NOT NULL,
  `product_id` int(8) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sales_price_per_unit` int(11) NOT NULL,
  PRIMARY KEY (`sales_order_detail_id`),
  KEY `sales_order_details_productFK` (`product_id`),
  KEY `sales_order_details_salesOrderFK` (`sale_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scrap_product`
--

DROP TABLE IF EXISTS `scrap_product`;
CREATE TABLE IF NOT EXISTS `scrap_product` (
  `scrap_product_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `stockroom_id` int(8) NOT NULL,
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
  `aisle_number` int(11) NOT NULL,
  `cabinet_level` int(11) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `category_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  PRIMARY KEY (`stockroom_id`),
  KEY `stockroom_userFK` (`user_id`),
  KEY `stockroom_categoryFK` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer`
--

DROP TABLE IF EXISTS `stock_transfer`;
CREATE TABLE IF NOT EXISTS `stock_transfer` (
  `stock_transfer_id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(8) NOT NULL,
  `from_stockroom_id` int(8) NOT NULL,
  `to_stockroom_id` int(8) NOT NULL,
  `transfer_quantity` int(11) NOT NULL,
  `transfer_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`stock_transfer_id`),
  KEY `stock_transfer_productFK` (`product_id`),
  KEY `stock_transfer_from_stockroomFK` (`from_stockroom_id`),
  KEY `stock_transfer_to_stockroomFK` (`to_stockroom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=20240001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `image_url`, `mobile_number`, `email`, `username`, `password`, `role`, `created_at`, `email_verified_at`, `updated_at`) VALUES
(1, 'Admin', 'Dumpstreet', 'Screenshot 2024-07-15 224616_1729079763.png', '09555111222', 'admin@gmail.com', 'admin', '$2y$10$Bv9Rch9DpeNPMMcK4GXP2.PP5ZzpI1JsLaio.hqYKSe8ujERkffrS', 'Administrator', '2024-10-16 03:56:03', NULL, '2024-10-16 03:56:03'),
(20240000, 'Aira', 'Carillo', 'Screenshot 2024-09-05 144341_1729083003.png', '09264003199', '20223765@s.ubaguio.edu', 'aira', '$2y$10$.s55mEkQtzbhhAcuDtlvjui4Hvm2KhKEM3kpHprJ3FnMHUrPnN1eW', 'Inventory Manager', '2024-10-16 12:50:03', '2024-10-16 12:51:26', '2024-10-16 13:14:52');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_stockTransferFK` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfer` (`stock_transfer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_stockroomFK` FOREIGN KEY (`stockroom_id`) REFERENCES `stockroom` (`stockroom_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_categoryFK` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_supplierFK` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales_order`
--
ALTER TABLE `sales_order`
  ADD CONSTRAINT `sales_order_userFK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales_order_detail`
--
ALTER TABLE `sales_order_detail`
  ADD CONSTRAINT `sales_order_details_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_order_details_salesOrderFK` FOREIGN KEY (`sale_order_id`) REFERENCES `sales_order` (`sales_order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `stockroom_categoryFK` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stockroom_userFK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  ADD CONSTRAINT `stock_transfer_from_stockroomFK` FOREIGN KEY (`from_stockroom_id`) REFERENCES `stockroom` (`stockroom_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_transfer_productFK` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_transfer_to_stockroomFK` FOREIGN KEY (`to_stockroom_id`) REFERENCES `stockroom` (`stockroom_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
