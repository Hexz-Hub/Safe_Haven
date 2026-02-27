-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2026 at 02:37 AM
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
-- Database: `spot`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `full_name`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@spotlightlistings.ng', 'Admin User', '2026-01-28 22:04:38', '2026-02-23 02:20:50');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `preferred_time` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

CREATE TABLE `inspections` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `inspection_date` date DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_requests`
--

CREATE TABLE `media_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `service_type` varchar(100) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `inspection_id` int(11) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `message_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `status` varchar(20) DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `published_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `content`, `excerpt`, `image`, `author`, `category`, `status`, `views`, `created_at`, `updated_at`, `published_at`, `created_by`) VALUES
(1, 'Abuja Real Estate Market Trends 2026', 'abuja-real-estate-market-trends-2026', 'The Abuja real estate market continues to show strong growth in 2026, particularly in premium areas like Guzape, Maitama, and Asokoro. Property values have appreciated by an average of 12% year-over-year, driven by increased demand from high-net-worth individuals and diaspora investors.\r\n\r\nKey factors driving this growth include improved infrastructure, enhanced security measures, and the Federal Capital Territory Administration\'s commitment to urban planning. Areas like Katampe Extension and Lifecamp are emerging as hotspots for investment.\r\n\r\nExperts predict continued growth throughout 2026, with particular emphasis on verified properties that meet stringent legal and safety standards. Buyers are increasingly prioritizing transparency and proper documentation over quick purchases.', 'Analysis of the latest trends and investment opportunities in Abuja\'s real estate market for 2026.', 'market-trends-2026.jpg', 'Spotlight Team', 'Market Update', 'published', 8, '2026-01-28 22:04:38', '2026-02-03 11:42:32', '2026-01-30 11:42:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `news_images`
--

CREATE TABLE `news_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `news_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news_images`
--

INSERT INTO `news_images` (`id`, `news_id`, `image_path`, `display_order`, `created_at`) VALUES
(1, 1, '697c930353a8c.jpg', 0, '2026-01-30 11:16:19');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `property_type` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `bedrooms` int(11) DEFAULT 0,
  `bathrooms` int(11) DEFAULT 0,
  `area_sqm` decimal(10,2) DEFAULT 0.00,
  `features` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'available',
  `featured` tinyint(1) DEFAULT 0,
  `verification_status` varchar(20) DEFAULT 'verified',
  `main_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `description`, `price`, `property_type`, `location`, `bedrooms`, `bathrooms`, `area_sqm`, `features`, `status`, `featured`, `verification_status`, `main_image`, `created_at`, `updated_at`, `created_by`) VALUES
(1, '5 Bedroom Duplex', 'Luxurious 5-bedroom duplex with modern amenities, pool, and garden. Perfect for families seeking comfort and elegance.', 350000000.00, 'Duplex', 'Guzape, Abuja', 5, 5, 450.00, NULL, 'available', 0, 'verified', '697aa2b69b84c.jpg', '2026-01-28 22:04:38', '2026-01-28 23:58:46', 1),
(2, 'Luxury 3 Bed Apt', 'Exquisite 3-bedroom apartment in the heart of Wuse II with 24/7 security, parking, and premium finishes.', 120000000.00, 'Apartment', 'Wuse II, Abuja', 3, 3, 180.00, NULL, 'available', 0, 'verified', '697aa2c98176c.jpg', '2026-01-28 22:04:38', '2026-01-28 23:59:05', 1),
(3, 'Investment Plot', 'Prime 600sqm land in rapidly developing Katampe Extension. Perfect for investment or building your dream home.', 85000000.00, 'Land', 'Katampe Ext.', 0, 0, 600.00, NULL, 'available', 1, 'verified', '697aa2e583f8d.jpg', '2026-01-28 22:04:38', '2026-01-28 23:59:33', 1),
(4, 'HackBerry Estate - Idu', 'ZEEKS HOMES ESTATE - HACKBERRY ESTATE\r\n\r\nESTATE OVERVIEW:\r\nDeveloped by Zeeks Homes & Suraab, HackBerry Estate is a premium gated community located in Idu, offering modern residential living with world-class amenities.\r\n\r\nTITLE DOCUMENT:\r\n- R of O (Right of Occupancy)\r\n- FCDA Approved\r\n- Buyers receive: Receipt, Allocation Letter, Contract of Sale\r\n\r\nESTATE FEATURES:\r\n✓ Street Lights\r\n✓ Green Area\r\n✓ Underground Drainage System\r\n✓ 24/7 Power Supply\r\n✓ Security\r\n✓ Good Road Network\r\n✓ Gated Community\r\n✓ Recreational Spaces\r\n✓ Shopping Mall\r\n✓ Sport Centre\r\n\r\nLANDMARKS & ACCESSIBILITY:\r\n- National Institute for Pharmaceutical Research and Development\r\n- Efab Global Estate\r\n- Idu Train Station\r\n\r\nAVAILABLE PROTOTYPES:\r\n• 4 Bedrooms Fully Detached Duplex + Attached 2 Rooms Guest Chalet\r\n• 4 Bedrooms Fully Detached Duplex + Attached 1 Room Guest Chalet\r\n• 4 Bedrooms Semi Detached Duplex + Attached 2 Room Guest Chalet\r\n• 3 Bedrooms Fully Detached Duplex + Attached 1 Room Guest Chalet\r\n\r\nTOTAL DEVELOPMENT PACKAGE (TDP):\r\n- Infrastructure Fee: ₦2,500,000\r\n- Sundry: ₦500,000\r\n- Application Fee: ₦10,000\r\n\r\nPRICING OPTIONS:\r\nPlot Size | Outright | 50% Initial | 3 Months | 6 Months\r\n250 Sqm  | ₦9M      | ₦4.5M      | ₦1.5M   | ₦750K\r\n300 Sqm  | ₦10.8M   | ₦5.4M      | ₦1.8M   | ₦900K\r\n350 Sqm  | ₦12.6M   | ₦6.6M      | ₦2.2M   | ₦1.1M\r\n700 Sqm  | ₦27M     | ₦13.5M     | ₦4.4M   | ₦2.25M\r\n\r\nContact us for viewing and more information.', 9000000.00, 'Land', 'Idu, Abuja', 4, 4, 250.00, 'Street Lights, Green Area, Underground Drainage System, 24/7 Power Supply, Security, Good Road Network, Gated Community, Recreational Spaces, Shopping Mall, Sport Centre', 'available', 1, 'verified', '697aa2a2c4c50.jpg', '2026-01-28 23:46:17', '2026-01-28 23:58:26', 1),
(5, 'Zeeks Elite City - Kuchako, Kuje', 'ZEEKS HOMES ESTATE - ZEEKS ELITE CITY\r\n\r\nESTATE OVERVIEW:\r\nDeveloped by Zeeks Homes & Suraab, Zeeks Elite City is a premium gated community located in Kuchako 2- Bamishi, Kuje, offering modern residential living with world-class amenities.\r\n\r\nTITLE DOCUMENT:\r\n- R of O (Right of Occupancy)\r\n- FCDA Approved\r\n- Area Council\r\n- Buyers receive: Receipt, Allocation Letter, Contract of Sale\r\n\r\nESTATE FEATURES:\r\n✓ Street Lights\r\n✓ Green Area\r\n✓ Underground Drainage System\r\n✓ 24/7 Power Supply\r\n✓ Security\r\n✓ Good Road Network\r\n✓ Gated Community\r\n✓ Recreational Spaces\r\n✓ Shopping Mall\r\n✓ Sport Centre\r\n\r\nLANDMARKS & ACCESSIBILITY:\r\n- Centenary City\r\n- Market Square\r\n- Military Checkpoint\r\n- SS & Jude Seminary School\r\n- Kuje Filling Station, Kuchiako\r\n\r\nAVAILABLE PROTOTYPES:\r\n• 4 Bedroom Semi Detached Duplex\r\n• 4 Bedroom Fully Detached Duplex\r\n• 4 Units, 2 Bedroom Block of Flats\r\n\r\nTOTAL DEVELOPMENT PACKAGE (TDP):\r\n- Infrastructure Fee: ₦2,500,000\r\n- Sundry: ₦500,000\r\n- Application Fee: ₦10,000\r\n\r\nPRICING OPTIONS:\r\nPlot Size | Outright | 50% Initial | 3 Months  | 6 Months\r\n300 Sqm  | ₦4.2M    | ₦2.1M      | ₦700K     | ₦350K\r\n500 Sqm  | ₦5.5M    | ₦2.75M     | ₦916.7K   | ₦459K\r\n700 Sqm  | ₦6.5M    | ₦3.25M     | ₦1.084M   | ₦542K\r\n1000 Sqm | ₦10M     | ₦5M        | ₦1.667M   | ₦834K\r\n\r\nContact us for viewing and more information.', 4200000.00, 'Land', 'Kuchako 2- Bamishi, Kuje', 4, 4, 300.00, 'Street Lights, Green Area, Underground Drainage System, 24/7 Power Supply, Security, Good Road Network, Gated Community, Recreational Spaces, Shopping Mall, Sport Centre', 'available', 1, 'verified', '697aa250730a9.jpg', '2026-01-28 23:51:03', '2026-01-28 23:57:04', 1),
(6, 'Beacon City - Pyakasa', 'ZEEKS HOMES ESTATE - BEACON CITY\r\n\r\nESTATE OVERVIEW:\r\nDeveloped by Zeeks Homes, Beacon City is a premium gated community located in Pyakasa, offering modern residential living with world-class amenities.\r\n\r\nTITLE DOCUMENT:\r\n- R of O (Right of Occupancy)\r\n- FCDA Approved\r\n- Buyers receive: Receipt, Allocation Letter, Contract of Sale\r\n\r\nESTATE FEATURES:\r\n✓ Street Lights\r\n✓ Green Area\r\n✓ Underground Drainage System\r\n✓ 24/7 Power Supply\r\n✓ Security\r\n✓ Good Road Network\r\n✓ Gated Community\r\n✓ Recreational Spaces\r\n✓ Shopping Mall\r\n✓ Sport Centre\r\n\r\nLANDMARKS & ACCESSIBILITY:\r\n- Olivia Garden Estate\r\n- After Sherriti\r\n\r\nAVAILABLE PROTOTYPES:\r\n• 3 Bedroom Semi Detached Duplex\r\n• 4 Bedroom Fully Detached Duplex with Guest Chalet\r\n\r\nTOTAL DEVELOPMENT PACKAGE (TDP):\r\n- Infrastructure Fee: ₦2,500,000\r\n- Sundry: ₦500,000\r\n- Application Fee: ₦10,000\r\n\r\nPRICING OPTIONS:\r\nPlot Size | Outright | 50% Initial | 3 Months  | 6 Months\r\n300 Sqm  | ₦10M     | ₦5M        | ₦1.67M    | ₦834K\r\n500 Sqm  | ₦15M     | ₦7.5M      | ₦2.5M     | ₦1.25M\r\n\r\nContact us for viewing and more information.', 10000000.00, 'Land', 'Pyakasa, Abuja', 4, 4, 300.00, 'Street Lights, Green Area, Underground Drainage System, 24/7 Power Supply, Security, Good Road Network, Gated Community, Recreational Spaces, Shopping Mall, Sport Centre', 'available', 1, 'verified', '697aa1f0bfce6.jpg', '2026-01-28 23:51:03', '2026-01-28 23:55:28', 1),
(7, 'Pearl Homes - Karsanq', 'ZEEKS HOMES ESTATE - PEARL HOMES\r\n\r\nESTATE OVERVIEW:\r\nDeveloped by Zeeks Homes, Pearl Homes is a premium gated community located in Karsanq, offering modern residential living with world-class amenities.\r\n\r\nTITLE DOCUMENT:\r\n- R of O (Right of Occupancy)\r\n- FCDA Approved\r\n- Buyers receive: Receipt, Allocation Letter, Contract of Sale\r\n\r\nESTATE FEATURES:\r\n✓ Street Lights\r\n✓ Green Area\r\n✓ Underground Drainage System\r\n✓ 24/7 Power Supply\r\n✓ Security\r\n✓ Good Road Network\r\n✓ Gated Community\r\n✓ Recreational Spaces\r\n✓ Shopping Mall\r\n✓ Sport Centre\r\n\r\nLANDMARKS & ACCESSIBILITY:\r\n- Olivia Garden Estate\r\n- After Sherriti\r\n\r\nTOTAL DEVELOPMENT PACKAGE (TDP):\r\n- Infrastructure Fee: ₦2,500,000\r\n- Sundry: ₦500,000\r\n- Application Fee: ₦10,000\r\n\r\nPRICING OPTIONS:\r\nPlot Size | Outright | 50% Initial | 3 Months  | 6 Months\r\n250 Sqm  | ₦20M     | ₦10M       | ₦3.3M     | ₦1.6M\r\n350 Sqm  | ₦28M     | ₦14M       | ₦4.6M     | ₦2.3M\r\n500 Sqm  | ₦40M     | ₦20M       | ₦6.6M     | ₦3.3M\r\n1000 Sqm | ₦80M     | ₦40M       | ₦13.3M    | ₦2.21M\r\n\r\nContact us for viewing and more information.', 20000000.00, 'Land', 'Karsanq, Abuja', 3, 3, 250.00, 'Street Lights, Green Area, Underground Drainage System, 24/7 Power Supply, Security, Good Road Network, Gated Community, Recreational Spaces, Shopping Mall, Sport Centre', 'available', 1, 'verified', '697aa26645e3a.jpg', '2026-01-28 23:51:03', '2026-01-28 23:57:26', 1),
(8, 'Paramount Groove - Bamishi, Kuje', 'ZEEKS HOMES ESTATE - PARAMOUNT GROOVE\r\n\r\nESTATE OVERVIEW:\r\nDeveloped by Zeeks Homes, Paramount Groove is a premium Bungalow Housing Estate located in Bamishi, Kuje, offering fully furnished and developed modern homes.\r\n\r\nTITLE DOCUMENT:\r\n- R of O (Right of Occupancy)\r\n- FCDA Approved\r\n- Buyers receive: Receipt, Allocation Letter, Contract of Sale\r\n\r\nESTATE FEATURES:\r\n✓ Street Lights\r\n✓ Green Area\r\n✓ Underground Drainage System\r\n✓ 24/7 Power Supply\r\n✓ Security\r\n✓ Good Road Network\r\n✓ Gated Community\r\n✓ Recreational Spaces\r\n✓ Shopping Mall\r\n✓ Sport Centre\r\n\r\nLANDMARKS & ACCESSIBILITY:\r\n- Centenary City\r\n- Market Square\r\n- Military Check Point\r\n- SS & Jude Seminary\r\n\r\nAVAILABLE PROTOTYPE:\r\n• 3 Bedroom Bungalow Housing Estate\r\n\r\nTOTAL DEVELOPMENT PACKAGE (TDP):\r\n- Infrastructure Fee: ₦2,500,000\r\n- Sundry: ₦500,000\r\n- Application Fee: ₦10,000\r\n\r\nPRICING:\r\n₦60,000,000 - Fully Furnished and Developed by Zeeks Homes\r\nInitial Deposit: ₦30,000,000\r\n\r\nThis is a complete, move-in-ready bungalow with all modern amenities and finishes.\r\n\r\nContact us for viewing and more information.', 60000000.00, 'House', 'Bamishi, Kuje', 3, 3, 350.00, 'Street Lights, Green Area, Underground Drainage System, 24/7 Power Supply, Security, Good Road Network, Gated Community, Recreational Spaces, Shopping Mall, Sport Centre, Fully Furnished', 'available', 1, 'verified', '697aa279881d4.jpg', '2026-01-28 23:51:03', '2026-01-28 23:57:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `created_at`, `last_login`) VALUES
(1, 'olisa clinton', 'ukatuolisa1@gmail.com', '$2y$10$PKoaLFRbSFHbEZpBwyj98ekpdsmHLRAqE/aE4TIS6Xlw86LAdThUK', NULL, 'user', '2026-01-28 22:12:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `request_type` varchar(100) DEFAULT NULL,
  `concern` text DEFAULT NULL,
  `property_location` varchar(255) DEFAULT NULL,
  `property_link` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `admin_feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verifications`
--

INSERT INTO `verifications` (`id`, `user_id`, `name`, `email`, `phone`, `request_type`, `concern`, `property_location`, `property_link`, `message`, `status`, `admin_feedback`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ukatu Olisa Clinton', 'ukatuolisa1@gmail.com', '+2347055046933', 'buy', NULL, NULL, NULL, '', 'approved', '', '2026-01-29 10:55:31', '2026-01-29 11:09:09'),
(2, 1, 'Ukatu Olisa Clinton', 'ukatuolisa1@gmail.com', '+2347055046933', 'verification_report', '???? Overpaying/Valuation', 'Area 1', '', 'it is seemingly expensive', 'approved', 'Hi olisa, we would look into it', '2026-01-29 11:22:38', '2026-01-29 11:23:27'),
(3, 1, 'Ukatu Olisa Clinton', 'ukatuolisa1@gmail.com', '+2347055046933', 'verification_report', '???? Overpaying/Valuation', 'Area 1', '', 'it is seemingly expensive', 'pending', NULL, '2026-01-29 11:32:00', '2026-01-29 11:32:00'),
(4, 1, 'Esmond Nwabunwanne Ukatu', 'esmondukatu1@gmail.com', '+2348033242294', 'buy', '???? None - just want home', NULL, NULL, '', 'pending', NULL, '2026-01-29 11:57:06', '2026-01-29 11:57:06'),
(5, 1, 'olisa clinton', 'ukatuolisa1@gmail.com', '+2347055046933', 'verification_report', '⚠️ Fake documents/scams', 'Area 1', '', 'ITS A FIVE BED ROOM DUPLEX', 'pending', NULL, '2026-01-30 14:33:01', '2026-01-30 14:33:01'),
(6, 1, 'Ukatu Olisa Clinton', 'ukatuolisa1@gmail.com', '+2347055046933', 'verification_report', '⚖️ Legal disputes/litigation', 'kuje village', '', 'i want clearification on who owns the land, so avoid legitimate disputes', 'rejected', '', '2026-01-30 14:43:23', '2026-01-30 14:44:19'),
(7, 1, 'Ukatu Olisa Clinton', 'ukatuolisa1@gmail.com', '+2347055046933', 'buy', '???? None - just want home', NULL, NULL, '', 'pending', NULL, '2026-01-30 14:45:35', '2026-01-30 14:45:35'),
(8, 1, 'Ukatu Olisa Clinton', 'ukatuolisa1@gmail.com', '+2347055046933', 'verification_report', '???? Overpaying/Valuation', 'kuje village', '', 'i want to know original owners of the property to avoid scam .', 'pending', NULL, '2026-01-30 14:51:40', '2026-01-30 14:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `verification_status_history`
--

CREATE TABLE `verification_status_history` (
  `id` int(11) NOT NULL,
  `verification_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `admin_feedback` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_status_history`
--

INSERT INTO `verification_status_history` (`id`, `verification_id`, `status`, `admin_feedback`, `changed_at`) VALUES
(1, 2, 'approved', 'Hi olisa, we would look into it', '2026-01-29 11:23:31'),
(2, 6, 'rejected', '', '2026-01-30 14:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `virtual_tours`
--

CREATE TABLE `virtual_tours` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `youtube_url` varchar(500) NOT NULL,
  `youtube_video_id` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `virtual_tours`
--

INSERT INTO `virtual_tours` (`id`, `title`, `description`, `youtube_url`, `youtube_video_id`, `display_order`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Maitama Main House', '5-Bed Detached Villa with Private Pool', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ', 1, 'active', NULL, '2026-01-30 10:28:52', '2026-01-30 11:50:14'),
(2, 'Guzape Smart Home', 'Automation with Panoramic City Views', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ', 2, 'active', NULL, '2026-01-30 10:28:52', '2026-01-30 10:28:52'),
(3, 'Wuse II Luxury Apt', 'Serviced 3-bedroom with Concierge', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ', 3, 'active', NULL, '2026-01-30 10:28:52', '2026-01-30 10:28:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_consultations_user` (`user_id`),
  ADD KEY `idx_consultations_status` (`status`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `inspections`
--
ALTER TABLE `inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspections_user` (`user_id`),
  ADD KEY `idx_inspections_property` (`property_id`),
  ADD KEY `idx_inspections_status` (`status`);

--
-- Indexes for table `media_requests`
--
ALTER TABLE `media_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `responded_by` (`responded_by`),
  ADD KEY `idx_media_requests_user` (`user_id`),
  ADD KEY `idx_media_requests_status` (`status`),
  ADD KEY `idx_media_requests_service` (`service_type`),
  ADD KEY `idx_media_requests_date` (`preferred_date`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `idx_messages_to_user` (`to_user_id`),
  ADD KEY `idx_messages_status` (`status`),
  ADD KEY `idx_messages_consultation` (`consultation_id`),
  ADD KEY `idx_messages_inspection` (`inspection_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `news_images`
--
ALTER TABLE `news_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news` (`news_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `verification_status_history`
--
ALTER TABLE `verification_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verification_id` (`verification_id`);

--
-- Indexes for table `virtual_tours`
--
ALTER TABLE `virtual_tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_virtual_tours_status` (`status`),
  ADD KEY `idx_virtual_tours_order` (`display_order`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_requests`
--
ALTER TABLE `media_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `news_images`
--
ALTER TABLE `news_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `verification_status_history`
--
ALTER TABLE `verification_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `virtual_tours`
--
ALTER TABLE `virtual_tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inspections`
--
ALTER TABLE `inspections`
  ADD CONSTRAINT `inspections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inspections_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `media_requests`
--
ALTER TABLE `media_requests`
  ADD CONSTRAINT `media_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `media_requests_ibfk_2` FOREIGN KEY (`responded_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`inspection_id`) REFERENCES `inspections` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verifications`
--
ALTER TABLE `verifications`
  ADD CONSTRAINT `verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `virtual_tours`
--
ALTER TABLE `virtual_tours`
  ADD CONSTRAINT `virtual_tours_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
