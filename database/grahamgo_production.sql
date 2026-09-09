-- =====================================================================
-- GrahamGo — Production Database Setup
-- =====================================================================
-- Import this ONE file via phpMyAdmin (or your host's SQL import tool)
-- into a freshly created, empty database. It creates every table and
-- seeds only what's needed to start clean:
--   - one Owner account (CHANGE THE PASSWORD BELOW BEFORE IMPORTING,
--     or right after logging in via Owner > Profile)
--   - the two starting products (Graham Mango, Oreo Graham)
--   - the migrations tracking rows, so `php spark migrate` still works
--     later if your host ever gives you SSH/terminal access
--
-- It does NOT include any of the test customers, test reservations, or
-- sales created during development — production starts empty of those.
-- =====================================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- ---------------------------------------------------------------------
-- Schema
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `inventory_transactions`;
CREATE TABLE `inventory_transactions` (
  `transaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `transaction_type` enum('Stock In','Reserved','Sold','Cancelled Return','Adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `inventory_transactions_product_id_foreign` (`product_id`),
  CONSTRAINT `inventory_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `reset_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`reset_id`),
  KEY `password_resets_user_id_foreign` (`user_id`),
  KEY `token` (`token`),
  CONSTRAINT `password_resets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` int(10) unsigned NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','GCash','Other') NOT NULL DEFAULT 'Cash',
  `payment_date` datetime NOT NULL,
  `status` enum('Paid','Refunded') NOT NULL DEFAULT 'Paid',
  PRIMARY KEY (`payment_id`),
  KEY `payments_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `payments_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `reorder_level` int(10) unsigned NOT NULL DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `reservation_details`;
CREATE TABLE `reservation_details` (
  `detail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `reservation_details_reservation_id_foreign` (`reservation_id`),
  KEY `reservation_details_product_id_foreign` (`product_id`),
  CONSTRAINT `reservation_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_details_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `reservation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `reservation_date` datetime NOT NULL,
  `claim_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
  `status` enum('Pending','Confirmed','Ready','Claimed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`reservation_id`),
  KEY `reservations_user_id_foreign` (`user_id`),
  CONSTRAINT `reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `sale_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned NOT NULL,
  `sale_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`sale_id`),
  UNIQUE KEY `reservation_id` (`reservation_id`),
  KEY `sales_payment_id_foreign` (`payment_id`),
  CONSTRAINT `sales_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE,
  CONSTRAINT `sales_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `customer_type` enum('Student','Faculty','Staff','Other') NOT NULL DEFAULT 'Student',
  `role` enum('customer','owner') NOT NULL DEFAULT 'customer',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Migration tracking rows — lets `php spark migrate` recognize these
-- tables as already up to date if you ever get CLI access later.
-- ---------------------------------------------------------------------
INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
('2026-09-02-000001', 'App\\Database\\Migrations\\CreateGrahamGoTables', 'default', 'App', UNIX_TIMESTAMP(), 1),
('2026-09-03-000001', 'App\\Database\\Migrations\\CreatePasswordResetsTable', 'default', 'App', UNIX_TIMESTAMP(), 2),
('2026-09-03-000002', 'App\\Database\\Migrations\\CreateSettingsTable', 'default', 'App', UNIX_TIMESTAMP(), 3),
('2026-09-03-000003', 'App\\Database\\Migrations\\AddAvatarToUsers', 'default', 'App', UNIX_TIMESTAMP(), 4);

-- ---------------------------------------------------------------------
-- Seed: Owner account
-- ---------------------------------------------------------------------
-- Email:    owner@yourdomain.com   <-- CHANGE THIS to your real email
--           before importing (find/replace in this file), or update it
--           later via phpMyAdmin.
-- Password: 3535b749b988
--           This is a random one-time password. Log in immediately
--           after import and change it via Owner > Profile — don't
--           leave this default password on a live site.
-- ---------------------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `contact_number`, `customer_type`, `role`, `created_at`, `updated_at`) VALUES
('GrahamGo Owner', 'owner@yourdomain.com', '$2y$10$BFAS1MjElZH.NgyWDZwWM.Fg4Pzet9ZyZZ0zvZklIGIm9.IROk8QS', NULL, 'Other', 'owner', NOW(), NOW());

-- ---------------------------------------------------------------------
-- Seed: starting products
-- ---------------------------------------------------------------------
INSERT INTO `products` (`product_name`, `description`, `price`, `stock`, `reorder_level`, `status`, `created_at`, `updated_at`) VALUES
('Graham Mango', 'Layers of crushed graham, creamy custard, and fresh mango slices.', 60.00, 30, 5, 'Active', NOW(), NOW()),
('Oreo Graham', 'Classic graham dessert layered with crushed Oreo cookies and creamy filling.', 60.00, 30, 5, 'Active', NOW(), NOW());

INSERT INTO `inventory_transactions` (`product_id`, `transaction_type`, `quantity`, `notes`, `transaction_date`) VALUES
(1, 'Stock In', 30, 'Initial stock', NOW()),
(2, 'Stock In', 30, 'Initial stock', NOW());

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
