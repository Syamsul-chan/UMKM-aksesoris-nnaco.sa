-- Data Dummy untuk nnaco_sa

SET FOREIGN_KEY_CHECKS=0;

TRUNCATE TABLE `roles`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `promos`;

-- Insert Roles
INSERT INTO `roles` (`id`, `name`) VALUES 
(1, 'Customer'),
(2, 'Admin'),
(3, 'Operator'),
(4, 'Owner');

-- Insert Users (Password default 'password123' -> hashed)
-- password_hash('password123', PASSWORD_BCRYPT) => $2y$10$w0...
INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `is_active`) VALUES 
(1, 4, 'Owner Nnaco', 'owner@nnaco.sa', '$2y$10$tZ2E742d/mXmXQO1G8WlF.L2L3H.R1F2X9J9Vl8u5D8kZ5rQ5w9w2', 1),
(2, 2, 'Admin Utama', 'admin@nnaco.sa', '$2y$10$tZ2E742d/mXmXQO1G8WlF.L2L3H.R1F2X9J9Vl8u5D8kZ5rQ5w9w2', 1),
(3, 3, 'Operator 1', 'operator@nnaco.sa', '$2y$10$tZ2E742d/mXmXQO1G8WlF.L2L3H.R1F2X9J9Vl8u5D8kZ5rQ5w9w2', 1),
(4, 1, 'Budi Customer', 'budi@gmail.com', '$2y$10$tZ2E742d/mXmXQO1G8WlF.L2L3H.R1F2X9J9Vl8u5D8kZ5rQ5w9w2', 1);

-- Insert Categories
INSERT INTO `categories` (`id`, `name`, `slug`) VALUES 
(1, 'Kalung', 'kalung'),
(2, 'Gelang', 'gelang'),
(3, 'Cincin', 'cincin'),
(4, 'Anting', 'anting');

-- Insert Products
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `is_active`) VALUES 
(1, 1, 'Kalung Emas Murni', 'Kalung emas desain elegan', 1500000.00, 10, 'kalung_emas.jpg', 1),
(2, 2, 'Gelang Mutiara', 'Gelang cantik berhias mutiara asli', 500000.00, 20, 'gelang_mutiara.jpg', 1),
(3, 3, 'Cincin Berlian', 'Cincin berlian untuk tunangan', 3000000.00, 5, 'cincin_berlian.jpg', 1),
(4, 4, 'Anting Perak', 'Anting perak ringan dipakai', 250000.00, 50, 'anting_perak.jpg', 1);

-- Insert Promos
INSERT INTO `promos` (`id`, `name`, `code`, `discount_type`, `discount_value`, `start_date`, `end_date`, `is_active`) VALUES 
(1, 'Promo Grand Opening', 'OPEN10', 'percentage', 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1),
(2, 'Diskon 50Rb', 'DISKON50', 'fixed', 50000.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1);

SET FOREIGN_KEY_CHECKS=1;
