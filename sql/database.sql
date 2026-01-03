-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 06:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `horse_racing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_settings`
--

CREATE TABLE `api_settings` (
  `id` int(11) NOT NULL,
  `api_name` varchar(100) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `base_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `last_sync` datetime DEFAULT NULL,
  `sync_interval` int(11) DEFAULT 60,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_settings`
--

INSERT INTO `api_settings` (`id`, `api_name`, `api_key`, `api_secret`, `base_url`, `is_active`, `last_sync`, `sync_interval`, `settings`, `created_at`, `updated_at`) VALUES
(1, 'TheRacingAPI', NULL, NULL, 'https://api.theracingapi.com', 0, NULL, 60, NULL, '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(2, 'BetfairAPI', NULL, NULL, 'https://api.betfair.com', 0, NULL, 60, NULL, '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(3, 'SportsRadar', NULL, NULL, 'https://api.sportradar.com', 0, NULL, 60, NULL, '2025-12-06 21:48:49', '2025-12-06 21:48:49');

-- --------------------------------------------------------

--
-- Table structure for table `horses`
--

CREATE TABLE `horses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('colt','filly','mare','stallion','gelding') DEFAULT 'colt',
  `color` varchar(50) DEFAULT NULL,
  `sire` varchar(100) DEFAULT NULL,
  `dam` varchar(100) DEFAULT NULL,
  `country_of_birth` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `career_wins` int(11) DEFAULT 0,
  `career_places` int(11) DEFAULT 0,
  `career_shows` int(11) DEFAULT 0,
  `career_starts` int(11) DEFAULT 0,
  `career_earnings` decimal(12,2) DEFAULT 0.00,
  `best_distance` varchar(50) DEFAULT NULL,
  `preferred_going` varchar(50) DEFAULT NULL,
  `equipment` varchar(100) DEFAULT NULL,
  `medication` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `silks_image` varchar(255) DEFAULT NULL,
  `form` varchar(50) DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `horses`
--

INSERT INTO `horses` (`id`, `name`, `age`, `gender`, `color`, `sire`, `dam`, `country_of_birth`, `date_of_birth`, `trainer_id`, `owner_id`, `weight`, `career_wins`, `career_places`, `career_shows`, `career_starts`, `career_earnings`, `best_distance`, `preferred_going`, `equipment`, `medication`, `image`, `silks_image`, `form`, `rating`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Thunder Bolt', 4, 'colt', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 8, 15, 0, 25, 450000.00, '1 1/4 miles', NULL, NULL, NULL, NULL, NULL, '1-2-1-3-1', 95, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(2, 'Lightning Strike', 5, 'stallion', NULL, NULL, NULL, NULL, NULL, 2, 2, NULL, 12, 20, 0, 35, 680000.00, '1 1/4 miles', NULL, NULL, NULL, NULL, NULL, '2-1-1-2-1', 98, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(3, 'Storm Chaser', 3, 'colt', NULL, NULL, NULL, NULL, NULL, 3, 3, NULL, 4, 9, 0, 18, 220000.00, '1 mile', NULL, NULL, NULL, NULL, NULL, '3-4-2-1-5', 88, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(4, 'Wind Runner', 6, 'gelding', NULL, NULL, NULL, NULL, NULL, 4, 4, NULL, 3, 11, 0, 20, 180000.00, '1 1/2 miles', NULL, NULL, NULL, NULL, NULL, '5-3-4-2-3', 82, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(5, 'Desert Rose', 4, 'filly', NULL, NULL, NULL, NULL, NULL, 5, 5, NULL, 7, 14, 0, 24, 380000.00, '1 1/4 miles', NULL, NULL, NULL, NULL, NULL, '1-1-3-2-2', 92, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(6, 'Mountain King', 7, 'gelding', NULL, NULL, NULL, NULL, NULL, 6, 6, NULL, 2, 8, 0, 22, 120000.00, '2 miles', NULL, NULL, NULL, NULL, NULL, '4-5-6-3-4', 78, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(7, 'Ocean Wave', 5, 'mare', NULL, NULL, NULL, NULL, NULL, 7, 7, NULL, 6, 13, 0, 22, 340000.00, '1 1/4 miles', NULL, NULL, NULL, NULL, NULL, '2-3-1-4-1', 90, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(8, 'Golden Arrow', 4, 'colt', NULL, NULL, NULL, NULL, NULL, 8, 8, NULL, 5, 10, 0, 20, 290000.00, '1 mile', NULL, NULL, NULL, NULL, NULL, '3-2-2-5-3', 86, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48');

-- --------------------------------------------------------

--
-- Table structure for table `jockeys`
--

CREATE TABLE `jockeys` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `total_wins` int(11) DEFAULT 0,
  `total_races` int(11) DEFAULT 0,
  `win_percentage` decimal(5,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jockeys`
--

INSERT INTO `jockeys` (`id`, `name`, `country`, `date_of_birth`, `weight`, `height`, `total_wins`, `total_races`, `win_percentage`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'John Smith', 'USA', NULL, NULL, NULL, 245, 1200, 20.42, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(2, 'David Brown', 'UK', NULL, NULL, NULL, 312, 1450, 21.52, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(3, 'James Miller', 'Australia', NULL, NULL, NULL, 189, 980, 19.29, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(4, 'Tom Anderson', 'Ireland', NULL, NULL, NULL, 156, 820, 19.02, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(5, 'Chris Martin', 'USA', NULL, NULL, NULL, 278, 1100, 25.27, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(6, 'Paul Wilson', 'UK', NULL, NULL, NULL, 134, 750, 17.87, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(7, 'Mark Johnson', 'France', NULL, NULL, NULL, 201, 950, 21.16, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(8, 'Steve Davis', 'USA', NULL, NULL, NULL, 167, 890, 18.76, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48');

-- --------------------------------------------------------

--
-- Table structure for table `odds_history`
--

CREATE TABLE `odds_history` (
  `id` int(11) NOT NULL,
  `race_entry_id` int(11) NOT NULL,
  `odds_value` varchar(20) DEFAULT NULL,
  `odds_decimal` decimal(10,2) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `total_horses` int(11) DEFAULT 0,
  `colors` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `name`, `company_name`, `country`, `total_horses`, `colors`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Blue Star Racing', 'Blue Star LLC', 'USA', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(2, 'Thunder Stables', 'Thunder Racing Inc', 'UK', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(3, 'Storm Racing LLC', 'Storm Racing', 'USA', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(4, 'Wind Farm Racing', 'Wind Farm Holdings', 'Ireland', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(5, 'Desert Bloom Stable', 'Desert Bloom LLC', 'UAE', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(6, 'Mountain Top Racing', 'Mountain Top Inc', 'USA', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(7, 'Seaside Stables', 'Seaside Racing Ltd', 'UK', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(8, 'Golden Racing Partners', 'Golden Racing LLC', 'USA', 0, NULL, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `race_id` int(11) NOT NULL,
  `bet_type` enum('win','place','show','exacta','trifecta','superfecta','daily_double') NOT NULL,
  `combination` varchar(100) DEFAULT NULL,
  `payout_amount` decimal(10,2) DEFAULT NULL,
  `pool_total` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `predictions`
--

CREATE TABLE `predictions` (
  `id` int(11) NOT NULL,
  `race_id` int(11) NOT NULL,
  `race_entry_id` int(11) NOT NULL,
  `predicted_position` int(11) DEFAULT NULL,
  `win_probability` decimal(5,2) DEFAULT NULL,
  `place_probability` decimal(5,2) DEFAULT NULL,
  `confidence_level` enum('low','medium','high','very_high') DEFAULT 'medium',
  `value_rating` enum('poor','fair','good','excellent') DEFAULT 'fair',
  `ai_analysis` text DEFAULT NULL,
  `factors_considered` text DEFAULT NULL,
  `recommendation` enum('strong_bet','bet','consider','avoid') DEFAULT 'consider',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `races`
--

CREATE TABLE `races` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `track_id` int(11) DEFAULT NULL,
  `race_date` date NOT NULL,
  `race_time` time NOT NULL,
  `distance` varchar(50) DEFAULT NULL,
  `race_class` varchar(50) DEFAULT NULL,
  `race_type` enum('flat','hurdle','chase','bumper') DEFAULT 'flat',
  `prize_money` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'USD',
  `going` varchar(50) DEFAULT NULL,
  `weather` varchar(50) DEFAULT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `wind` varchar(50) DEFAULT NULL,
  `humidity` varchar(20) DEFAULT NULL,
  `rail_position` varchar(50) DEFAULT NULL,
  `total_runners` int(11) DEFAULT 0,
  `non_runners` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','live','finished','cancelled','postponed') DEFAULT 'scheduled',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `race_entries`
--

CREATE TABLE `race_entries` (
  `id` int(11) NOT NULL,
  `race_id` int(11) NOT NULL,
  `horse_id` int(11) NOT NULL,
  `jockey_id` int(11) DEFAULT NULL,
  `saddle_number` int(11) DEFAULT NULL,
  `draw_position` int(11) DEFAULT NULL,
  `weight_carried` varchar(20) DEFAULT NULL,
  `official_rating` int(11) DEFAULT NULL,
  `current_odds` varchar(20) DEFAULT NULL,
  `odds_decimal` decimal(10,2) DEFAULT NULL,
  `win_probability` decimal(5,2) DEFAULT 0.00,
  `place_probability` decimal(5,2) DEFAULT 0.00,
  `is_favorite` tinyint(1) DEFAULT 0,
  `is_non_runner` tinyint(1) DEFAULT 0,
  `equipment` varchar(100) DEFAULT NULL,
  `medication` varchar(100) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `race_results`
--

CREATE TABLE `race_results` (
  `id` int(11) NOT NULL,
  `race_id` int(11) NOT NULL,
  `race_entry_id` int(11) NOT NULL,
  `finish_position` int(11) DEFAULT NULL,
  `finish_time` varchar(20) DEFAULT NULL,
  `margin` varchar(50) DEFAULT NULL,
  `starting_price` varchar(20) DEFAULT NULL,
  `in_running_comments` text DEFAULT NULL,
  `official_rating_change` int(11) DEFAULT 0,
  `prize_won` decimal(12,2) DEFAULT 0.00,
  `photo_finish` tinyint(1) DEFAULT 0,
  `stewards_inquiry` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json','html') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'RacingPro Analytics', 'text', 'Website name', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(2, 'site_tagline', 'AI-Powered Racing Predictions', 'text', 'Website tagline', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(3, 'contact_email', 'info@racingpro.com', 'text', 'Contact email', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(4, 'contact_phone', '+1 234 567 890', 'text', 'Contact phone', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(5, 'timezone', 'UTC', 'text', 'Default timezone', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(6, 'date_format', 'Y-m-d', 'text', 'Date format', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(7, 'time_format', 'H:i', 'text', 'Time format', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(8, 'currency', 'USD', 'text', 'Default currency', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(9, 'odds_format', 'fractional', 'text', 'Odds display format', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(10, 'maintenance_mode', '0', 'boolean', 'Maintenance mode', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(11, 'google_analytics', '', 'text', 'Google Analytics ID', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(12, 'social_facebook', '', 'text', 'Facebook URL', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(13, 'social_twitter', '', 'text', 'Twitter URL', '2025-12-06 21:48:49', '2025-12-06 21:48:49'),
(14, 'social_instagram', '', 'text', 'Instagram URL', '2025-12-06 21:48:49', '2025-12-06 21:48:49');

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

CREATE TABLE `tracks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `track_type` enum('turf','dirt','synthetic','all-weather') DEFAULT 'turf',
  `track_length` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'UTC',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tracks`
--

INSERT INTO `tracks` (`id`, `name`, `location`, `country`, `track_type`, `track_length`, `description`, `image`, `timezone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Churchill Downs', 'Louisville, Kentucky', 'USA', 'dirt', '1 mile', NULL, NULL, 'UTC', 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(2, 'Ascot Racecourse', 'Berkshire', 'UK', 'turf', '1 mile 6 furlongs', NULL, NULL, 'UTC', 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(3, 'Flemington', 'Melbourne', 'Australia', 'turf', '2 miles', NULL, NULL, 'UTC', 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(4, 'Meydan Racecourse', 'Dubai', 'UAE', 'dirt', '1.5 miles', NULL, NULL, 'UTC', 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(5, 'Longchamp', 'Paris', 'France', 'turf', '2.5 km', NULL, NULL, 'UTC', 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(6, 'Santa Anita Park', 'Arcadia, California', 'USA', 'dirt', '1 mile', NULL, NULL, 'UTC', 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48');

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `stable_name` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `total_wins` int(11) DEFAULT 0,
  `total_horses` int(11) DEFAULT 0,
  `win_percentage` decimal(5,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`id`, `name`, `stable_name`, `location`, `country`, `total_wins`, `total_horses`, `win_percentage`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Mike Johnson', 'Johnson Racing Stables', NULL, 'USA', 156, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(2, 'Sarah Wilson', 'Wilson Thoroughbreds', NULL, 'UK', 203, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(3, 'Robert Davis', 'Davis Racing', NULL, 'Australia', 134, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(4, 'Emma White', 'White Star Stables', NULL, 'Ireland', 98, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(5, 'Lisa Brown', 'Brown Racing LLC', NULL, 'USA', 178, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(6, 'George Taylor', 'Taylor Thoroughbreds', NULL, 'UK', 87, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(7, 'Helen Clark', 'Clark Racing', NULL, 'France', 145, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48'),
(8, 'Peter Moore', 'Moore Stables', NULL, 'USA', 112, 0, 0.00, NULL, 1, '2025-12-06 21:48:48', '2025-12-06 21:48:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','editor','user') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `credit` decimal(12,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bets`
--

CREATE TABLE `bets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `race_id` int(11) NOT NULL,
  `race_entry_id` int(11) NOT NULL,
  `bet_type` varchar(50) DEFAULT 'win',
  `amount` decimal(12,2) NOT NULL,
  `odds_value` varchar(50) DEFAULT NULL,
  `odds_decimal` decimal(10,2) DEFAULT NULL,
  `potential_payout` decimal(12,2) DEFAULT NULL,
  `payout_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('pending','won','lost','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `settled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `avatar`, `phone`, `is_active`, `email_verified`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@racingpro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', NULL, NULL, 1, 1, NULL, '2025-12-06 21:48:46', '2025-12-06 21:48:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_log_user` (`user_id`),
  ADD KEY `idx_activity_log_date` (`created_at`);

--
-- Indexes for table `api_settings`
--
ALTER TABLE `api_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `horses`
--
ALTER TABLE `horses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `jockeys`
--
ALTER TABLE `jockeys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `odds_history`
--
ALTER TABLE `odds_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_odds_history_entry` (`race_entry_id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `race_id` (`race_id`);

--
-- Indexes for table `predictions`
--
ALTER TABLE `predictions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `race_id` (`race_id`),
  ADD KEY `race_entry_id` (`race_entry_id`);

--
-- Indexes for table `races`
--
ALTER TABLE `races`
  ADD PRIMARY KEY (`id`),
  ADD KEY `track_id` (`track_id`),
  ADD KEY `idx_races_date` (`race_date`),
  ADD KEY `idx_races_status` (`status`);

--
-- Indexes for table `race_entries`
--
ALTER TABLE `race_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jockey_id` (`jockey_id`),
  ADD KEY `idx_race_entries_race` (`race_id`),
  ADD KEY `idx_race_entries_horse` (`horse_id`);

--
-- Indexes for table `race_results`
--
ALTER TABLE `race_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `race_entry_id` (`race_entry_id`),
  ADD KEY `idx_race_results_race` (`race_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bets`
--
ALTER TABLE `bets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bets_user` (`user_id`),
  ADD KEY `idx_bets_race` (`race_id`),
  ADD KEY `idx_bets_entry` (`race_entry_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bets`
--
ALTER TABLE `bets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_settings`
--
ALTER TABLE `api_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `horses`
--
ALTER TABLE `horses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jockeys`
--
ALTER TABLE `jockeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `odds_history`
--
ALTER TABLE `odds_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `predictions`
--
ALTER TABLE `predictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `races`
--
ALTER TABLE `races`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `race_entries`
--
ALTER TABLE `race_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `race_results`
--
ALTER TABLE `race_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tracks`
--
ALTER TABLE `tracks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bets`
--
ALTER TABLE `bets`
  ADD CONSTRAINT `bets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bets_ibfk_2` FOREIGN KEY (`race_id`) REFERENCES `races` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bets_ibfk_3` FOREIGN KEY (`race_entry_id`) REFERENCES `race_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `horses`
--
ALTER TABLE `horses`
  ADD CONSTRAINT `horses_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `horses_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `odds_history`
--
ALTER TABLE `odds_history`
  ADD CONSTRAINT `odds_history_ibfk_1` FOREIGN KEY (`race_entry_id`) REFERENCES `race_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `predictions`
--
ALTER TABLE `predictions`
  ADD CONSTRAINT `predictions_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `predictions_ibfk_2` FOREIGN KEY (`race_entry_id`) REFERENCES `race_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `races`
--
ALTER TABLE `races`
  ADD CONSTRAINT `races_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `race_entries`
--
ALTER TABLE `race_entries`
  ADD CONSTRAINT `race_entries_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `race_entries_ibfk_2` FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `race_entries_ibfk_3` FOREIGN KEY (`jockey_id`) REFERENCES `jockeys` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `race_results`
--
ALTER TABLE `race_results`
  ADD CONSTRAINT `race_results_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `race_results_ibfk_2` FOREIGN KEY (`race_entry_id`) REFERENCES `race_entries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
