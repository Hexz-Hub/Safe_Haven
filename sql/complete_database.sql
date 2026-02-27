-- ============================================
-- SPOTLIGHT LISTINGS - COMPLETE DATABASE SCHEMA
-- ============================================
-- This file contains ALL tables, indexes, and sample data
-- Run this file in phpMyAdmin to set up the entire database
-- 
-- Created: January 30, 2026
-- ============================================

-- Set database charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- SECTION 1: USER MANAGEMENT TABLES
-- ============================================

-- Regular Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `full_name` VARCHAR(150),
    `role` VARCHAR(50) DEFAULT 'admin',
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECTION 2: PROPERTY MANAGEMENT TABLES
-- ============================================

-- Properties Table
CREATE TABLE IF NOT EXISTS `properties` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `property_type` VARCHAR(50),
    `status` VARCHAR(50) DEFAULT 'available',
    `price` DECIMAL(15, 2),
    `location` VARCHAR(200),
    `address` TEXT,
    `bedrooms` INT,
    `bathrooms` INT,
    `area_sqft` INT,
    `year_built` YEAR,
    `features` TEXT,
    `main_image` VARCHAR(255),
    `featured` TINYINT(1) DEFAULT 0,
    `views` INT DEFAULT 0,
    `created_by` INT UNSIGNED,
    `sold_price` DECIMAL(15, 2) NULL,
    `sold_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_property_type` (`property_type`),
    KEY `idx_location` (`location`),
    KEY `idx_featured` (`featured`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Images Table
CREATE TABLE IF NOT EXISTS `property_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_property` (`property_id`),
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Inquiries Table
CREATE TABLE IF NOT EXISTS `property_inquiries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` INT UNSIGNED,
    `user_id` INT UNSIGNED,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50),
    `message` TEXT NOT NULL,
    `status` VARCHAR(50) DEFAULT 'new',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_property` (`property_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECTION 3: VERIFICATION SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `verifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50),
    `request_type` VARCHAR(100),
    `concern` TEXT,
    `property_location` VARCHAR(255),
    `property_link` VARCHAR(255),
    `message` TEXT,
    `status` VARCHAR(50) DEFAULT 'pending',
    `admin_notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verification Status History
CREATE TABLE IF NOT EXISTS `verification_status_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `verification_id` INT NOT NULL,
    `old_status` VARCHAR(50),
    `new_status` VARCHAR(50),
    `changed_by` INT UNSIGNED,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_verification` (`verification_id`),
    FOREIGN KEY (`verification_id`) REFERENCES `verifications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECTION 4: SERVICE REQUESTS
-- ============================================

-- Consultations
CREATE TABLE IF NOT EXISTS `consultations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(20),
    `subject` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `preferred_time` VARCHAR(50),
    `status` VARCHAR(50) DEFAULT 'pending',
    `admin_notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Inspections
CREATE TABLE IF NOT EXISTS `inspections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(20),
    `property_id` INT UNSIGNED,
    `inspection_date` DATE,
    `message` TEXT NOT NULL,
    `status` VARCHAR(50) DEFAULT 'pending',
    `admin_notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`),
    KEY `idx_property` (`property_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media Service Requests
CREATE TABLE IF NOT EXISTS `media_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(20),
    `service_type` VARCHAR(100) NOT NULL,
    `preferred_date` DATE NOT NULL,
    `preferred_time` TIME NOT NULL,
    `message` TEXT,
    `status` VARCHAR(50) DEFAULT 'pending',
    `admin_notes` TEXT,
    `admin_response` TEXT,
    `responded_by` INT UNSIGNED,
    `responded_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_service` (`service_type`),
    KEY `idx_date` (`preferred_date`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`responded_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECTION 5: MESSAGING SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `from_user_id` INT UNSIGNED,
    `to_user_id` INT UNSIGNED NOT NULL,
    `consultation_id` INT,
    `inspection_id` INT,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `message_type` VARCHAR(50),
    `status` VARCHAR(50) DEFAULT 'unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_from_user` (`from_user_id`),
    KEY `idx_to_user` (`to_user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_consultation` (`consultation_id`),
    KEY `idx_inspection` (`inspection_id`),
    FOREIGN KEY (`from_user_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`consultation_id`) REFERENCES `consultations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`inspection_id`) REFERENCES `inspections`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECTION 6: CONTENT MANAGEMENT
-- ============================================

-- News & Blog Articles
CREATE TABLE IF NOT EXISTS `news` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(250) NOT NULL,
    `slug` VARCHAR(250) NOT NULL UNIQUE,
    `excerpt` TEXT,
    `content` LONGTEXT NOT NULL,
    `image` VARCHAR(255),
    `category` VARCHAR(100),
    `tags` VARCHAR(500),
    `author` VARCHAR(150),
    `status` VARCHAR(50) DEFAULT 'draft',
    `views` INT DEFAULT 0,
    `created_by` INT UNSIGNED,
    `published_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_slug` (`slug`),
    KEY `idx_status` (`status`),
    KEY `idx_category` (`category`),
    KEY `idx_published` (`published_at`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News Images Table
CREATE TABLE IF NOT EXISTS `news_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `news_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_news` (`news_id`),
    FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Virtual Property Tours
CREATE TABLE IF NOT EXISTS `virtual_tours` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `youtube_url` VARCHAR(500) NOT NULL,
    `youtube_video_id` VARCHAR(50) NOT NULL,
    `display_order` INT DEFAULT 0,
    `status` VARCHAR(20) DEFAULT 'active',
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_status` (`status`),
    KEY `idx_order` (`display_order`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECTION 7: SAMPLE DATA
-- ============================================

-- Sample Admin User (username: admin, password: admin123)
INSERT IGNORE INTO `admin_users` (`id`, `username`, `password`, `email`, `full_name`, `role`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@spotlightlistings.ng', 'System Administrator', 'admin');

-- Sample Virtual Tours (Replace with your actual YouTube videos)
INSERT INTO `virtual_tours` (`title`, `description`, `youtube_url`, `youtube_video_id`, `display_order`, `status`) VALUES
('Maitama Main House', '5-Bed Detached Villa with Private Pool', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ', 1, 'active'),
('Guzape Smart Home', 'Automation with Panoramic City Views', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ', 2, 'active'),
('Wuse II Luxury Apt', 'Serviced 3-bedroom with Concierge', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ', 3, 'active')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- ============================================
-- SECTION 8: FINALIZE
-- ============================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SETUP COMPLETE!
-- ============================================
-- 
-- Next Steps:
-- 1. Login to admin panel with:
--    Username: admin
--    Password: admin123
-- 
-- 2. Change the admin password immediately!
-- 
-- 3. Start adding:
--    - Properties
--    - News articles
--    - Virtual tour videos
--
-- 4. Test user registration and services
--
-- ============================================
