-- ========================================
-- قاعدة البيانات المحدثة - الهيكل الجديد
-- تم إنشاؤه بتاريخ: 2025-09-16
-- ========================================

-- إعداد قاعدة البيانات
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- إنشاء قاعدة البيانات إذا لم تكن موجودة
CREATE DATABASE IF NOT EXISTS `taxif_cstaxi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `taxif_cstaxi`;

-- ========================================
-- جدول: roles
-- ========================================
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: users
-- ========================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `status` enum('pending','active','banned') DEFAULT 'pending',
  `role_id` int(11) NOT NULL DEFAULT 3,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_is_online` (`is_online`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: permissions
-- ========================================
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7595 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: user_permissions
-- ========================================
CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: role_permissions
-- ========================================
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: telegram_links
-- ========================================
CREATE TABLE `telegram_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `telegram_user_id` bigint(20) NOT NULL,
  `telegram_chat_id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `telegram_links_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: car_types
-- ========================================
CREATE TABLE `car_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: countries
-- ========================================
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(3) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: teams
-- ========================================
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `team_leader_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `team_leader_id` (`team_leader_id`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`team_leader_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: team_members
-- ========================================
CREATE TABLE `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_categories
-- ========================================
CREATE TABLE `ticket_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_subcategories
-- ========================================
CREATE TABLE `ticket_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id` (`category_id`,`name`),
  CONSTRAINT `ticket_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_codes
-- ========================================
CREATE TABLE `ticket_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subcategory_id` (`subcategory_id`,`name`),
  CONSTRAINT `ticket_codes_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `ticket_subcategories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: platforms
-- ========================================
CREATE TABLE `platforms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: tickets
-- ========================================
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10600 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_details
-- ========================================
CREATE TABLE `ticket_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `is_vip` tinyint(1) DEFAULT 0,
  `platform_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `code_id` int(11) NOT NULL,
  `notes` mediumtext DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `assigned_team_leader_id` int(11) NOT NULL,
  `edited_by` int(11) NOT NULL,
  `team_id_at_action` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `platform_id` (`platform_id`),
  KEY `category_id` (`category_id`),
  KEY `subcategory_id` (`subcategory_id`),
  KEY `code_id` (`code_id`),
  KEY `country_id` (`country_id`),
  KEY `assigned_team_leader_id` (`assigned_team_leader_id`),
  KEY `edited_by` (`edited_by`),
  KEY `team_id_at_action` (`team_id_at_action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ticket_details_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_details_ibfk_2` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`),
  CONSTRAINT `ticket_details_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`),
  CONSTRAINT `ticket_details_ibfk_4` FOREIGN KEY (`subcategory_id`) REFERENCES `ticket_subcategories` (`id`),
  CONSTRAINT `ticket_details_ibfk_5` FOREIGN KEY (`code_id`) REFERENCES `ticket_codes` (`id`),
  CONSTRAINT `ticket_details_ibfk_6` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `ticket_details_ibfk_7` FOREIGN KEY (`assigned_team_leader_id`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_details_ibfk_8` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_details_ibfk_9` FOREIGN KEY (`team_id_at_action`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14901 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: drivers
-- ========================================
CREATE TABLE `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `car_type_id` int(11) DEFAULT 1,
  `rating` decimal(3,2) DEFAULT 0.00,
  `app_status` enum('active','inactive','banned') DEFAULT 'inactive',
  `main_system_status` enum('pending','waiting_chat','no_answer','rescheduled','completed','blocked','reconsider','needs_documents') DEFAULT 'pending',
  `registered_at` mediumtext DEFAULT NULL,
  `data_source` enum('form','referral','telegram','staff','excel') NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `hold` tinyint(1) DEFAULT 0,
  `hold_by` int(11) DEFAULT NULL,
  `has_missing_documents` tinyint(1) DEFAULT 0,
  `notes` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  KEY `car_type_id` (`car_type_id`),
  KEY `added_by` (`added_by`),
  KEY `idx_hold` (`hold`),
  KEY `idx_status_hold` (`main_system_status`,`hold`),
  KEY `idx_phone` (`phone`),
  KEY `idx_hold_by` (`hold_by`),
  KEY `idx_country_id` (`country_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_updated_at` (`updated_at`),
  CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`car_type_id`) REFERENCES `car_types` (`id`),
  CONSTRAINT `drivers_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  CONSTRAINT `drivers_ibfk_3` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `drivers_ibfk_4` FOREIGN KEY (`hold_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: driver_attributes
-- ========================================
CREATE TABLE `driver_attributes` (
  `driver_id` int(11) NOT NULL,
  `has_many_trips` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`driver_id`),
  CONSTRAINT `driver_attributes_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: driver_calls
-- ========================================
CREATE TABLE `driver_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `call_by` int(11) NOT NULL,
  `call_status` enum('no_answer','answered','busy','not_available','wrong_number','rescheduled') DEFAULT 'no_answer',
  `notes` mediumtext DEFAULT NULL,
  `next_call_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ticket_category_id` int(11) DEFAULT NULL,
  `ticket_subcategory_id` int(11) DEFAULT NULL,
  `ticket_code_id` int(11) DEFAULT NULL,
  `team_id_at_action` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_category_id` (`ticket_category_id`),
  KEY `ticket_subcategory_id` (`ticket_subcategory_id`),
  KEY `ticket_code_id` (`ticket_code_id`),
  KEY `team_id_at_action` (`team_id_at_action`),
  KEY `idx_next_call_at` (`next_call_at`),
  KEY `idx_driver_id` (`driver_id`),
  KEY `idx_call_by` (`call_by`),
  KEY `idx_call_status` (`call_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `driver_calls_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`),
  CONSTRAINT `driver_calls_ibfk_2` FOREIGN KEY (`call_by`) REFERENCES `users` (`id`),
  CONSTRAINT `driver_calls_ibfk_3` FOREIGN KEY (`ticket_category_id`) REFERENCES `ticket_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `driver_calls_ibfk_4` FOREIGN KEY (`ticket_subcategory_id`) REFERENCES `ticket_subcategories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `driver_calls_ibfk_5` FOREIGN KEY (`ticket_code_id`) REFERENCES `ticket_codes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `driver_calls_ibfk_6` FOREIGN KEY (`team_id_at_action`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: driver_assignments
-- ========================================
CREATE TABLE `driver_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `note` mediumtext DEFAULT NULL,
  `is_seen` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `driver_id` (`driver_id`),
  KEY `from_user_id` (`from_user_id`),
  KEY `idx_to_user_id` (`to_user_id`),
  KEY `idx_is_seen` (`is_seen`),
  CONSTRAINT `driver_assignments_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`),
  CONSTRAINT `driver_assignments_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `driver_assignments_ibfk_3` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: document_types
-- ========================================
CREATE TABLE `document_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: driver_documents_required
-- ========================================
CREATE TABLE `driver_documents_required` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `status` enum('missing','submitted','rejected') DEFAULT 'missing',
  `note` mediumtext DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `driver_id` (`driver_id`,`document_type_id`),
  KEY `document_type_id` (`document_type_id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `driver_documents_required_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`),
  CONSTRAINT `driver_documents_required_ibfk_2` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  CONSTRAINT `driver_documents_required_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: incoming_calls
-- ========================================
CREATE TABLE `incoming_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caller_phone_number` varchar(20) NOT NULL,
  `call_received_by` int(11) NOT NULL,
  `call_started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `call_ended_at` datetime DEFAULT NULL,
  `status` enum('answered','missed') DEFAULT 'answered',
  `linked_ticket_detail_id` int(11) DEFAULT NULL,
  `team_id_at_action` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `call_received_by` (`call_received_by`),
  KEY `linked_ticket_detail_id` (`linked_ticket_detail_id`),
  KEY `team_id_at_action` (`team_id_at_action`),
  KEY `idx_caller_phone` (`caller_phone_number`),
  KEY `idx_call_started_at` (`call_started_at`),
  CONSTRAINT `incoming_calls_ibfk_1` FOREIGN KEY (`call_received_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incoming_calls_ibfk_2` FOREIGN KEY (`linked_ticket_detail_id`) REFERENCES `ticket_details` (`id`) ON DELETE SET NULL,
  CONSTRAINT `incoming_calls_ibfk_3` FOREIGN KEY (`team_id_at_action`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: reviews
-- ========================================
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reviewable_id` int(11) NOT NULL,
  `reviewable_type` varchar(50) NOT NULL,
  `reviewed_by` int(11) NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Review rating from 0 to 100',
  `review_notes` mediumtext DEFAULT NULL,
  `reviewed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ticket_category_id` int(11) DEFAULT NULL,
  `ticket_subcategory_id` int(11) DEFAULT NULL,
  `ticket_code_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reviewable` (`reviewable_id`,`reviewable_type`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `ticket_category_id` (`ticket_category_id`),
  KEY `ticket_subcategory_id` (`ticket_subcategory_id`),
  KEY `ticket_code_id` (`ticket_code_id`),
  KEY `idx_reviewed_at` (`reviewed_at`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`ticket_category_id`) REFERENCES `ticket_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`ticket_subcategory_id`) REFERENCES `ticket_subcategories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`ticket_code_id`) REFERENCES `ticket_codes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=434 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: discussions
-- ========================================
CREATE TABLE `discussions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discussable_id` int(11) NOT NULL,
  `discussable_type` varchar(50) NOT NULL,
  `opened_by` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `notes` mediumtext DEFAULT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_discussable` (`discussable_id`,`discussable_type`),
  KEY `opened_by` (`opened_by`),
  CONSTRAINT `discussions_ibfk_1` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: discussion_replies
-- ========================================
CREATE TABLE `discussion_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discussion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` mediumtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `discussion_id` (`discussion_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `discussion_replies_ibfk_1` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discussion_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: coupons
-- ========================================
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `country_id` int(11) DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `held_by` int(11) DEFAULT NULL,
  `held_at` datetime DEFAULT NULL,
  `used_by` int(11) DEFAULT NULL,
  `used_in_ticket` int(11) DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `used_for_phone` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `country_id` (`country_id`),
  KEY `used_by` (`used_by`),
  KEY `used_in_ticket` (`used_in_ticket`),
  KEY `held_by` (`held_by`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `coupons_ibfk_2` FOREIGN KEY (`used_by`) REFERENCES `users` (`id`),
  CONSTRAINT `coupons_ibfk_3` FOREIGN KEY (`used_in_ticket`) REFERENCES `tickets` (`id`),
  CONSTRAINT `coupons_ibfk_4` FOREIGN KEY (`held_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_coupons
-- ========================================
CREATE TABLE `ticket_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `ticket_detail_id` int(11) DEFAULT NULL,
  `coupon_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_id` (`ticket_id`,`coupon_id`),
  KEY `ticket_detail_id` (`ticket_detail_id`),
  KEY `coupon_id` (`coupon_id`),
  CONSTRAINT `ticket_coupons_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_coupons_ibfk_2` FOREIGN KEY (`ticket_detail_id`) REFERENCES `ticket_details` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ticket_coupons_ibfk_3` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_vip_assignments
-- ========================================
CREATE TABLE `ticket_vip_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_detail_id` int(11) NOT NULL,
  `marketer_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_detail_id` (`ticket_detail_id`),
  KEY `marketer_id` (`marketer_id`),
  CONSTRAINT `ticket_vip_assignments_ibfk_1` FOREIGN KEY (`ticket_detail_id`) REFERENCES `ticket_details` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_vip_assignments_ibfk_2` FOREIGN KEY (`marketer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=351 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: referral_visits
-- ========================================
CREATE TABLE `referral_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_user_id` int(11) DEFAULT NULL,
  `visit_recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `referer_url` text DEFAULT NULL,
  `query_params` text DEFAULT NULL,
  `referer_source` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `isp` varchar(255) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `browser_name` varchar(50) DEFAULT NULL,
  `operating_system` varchar(50) DEFAULT NULL,
  `registration_status` enum('visit_only','form_opened','attempted','successful','duplicate_phone','failed_other') DEFAULT 'visit_only',
  `registration_attempted_at` datetime DEFAULT NULL,
  `registered_driver_id` int(11) DEFAULT NULL,
  `visit_date` date GENERATED ALWAYS AS (cast(`visit_recorded_at` as date)) STORED,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_affiliate_ip_date` (`affiliate_user_id`,`ip_address`,`visit_date`),
  KEY `idx_affiliate_user_id` (`affiliate_user_id`),
  KEY `idx_registered_driver_id` (`registered_driver_id`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_visit_recorded_at` (`visit_recorded_at`),
  CONSTRAINT `referral_visits_ibfk_1` FOREIGN KEY (`affiliate_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `referral_visits_ibfk_2` FOREIGN KEY (`registered_driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=259 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: driver_snoozes
-- ========================================
CREATE TABLE `driver_snoozes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `snoozed_until` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `driver_id` (`driver_id`),
  KEY `idx_user_snooze` (`user_id`,`snoozed_until`),
  CONSTRAINT `driver_snoozes_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `driver_snoozes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: agents
-- ========================================
CREATE TABLE `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `state` varchar(100) NOT NULL,
  `is_online_only` tinyint(1) DEFAULT 1,
  `phone` varchar(20) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `map_url` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: working_hours
-- ========================================
CREATE TABLE `working_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `day_of_week` enum('Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_closed` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_id` (`agent_id`,`day_of_week`),
  CONSTRAINT `working_hours_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1016 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: notifications
-- ========================================
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL,
  `link` varchar(512) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: user_notifications
-- ========================================
CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `notification_id` (`notification_id`),
  CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_notifications_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_code_points
-- ========================================
CREATE TABLE `ticket_code_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_id` int(11) NOT NULL,
  `is_vip` tinyint(1) NOT NULL DEFAULT 0,
  `points` decimal(10,2) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `code_id` (`code_id`,`is_vip`,`valid_from`,`valid_to`),
  CONSTRAINT `ticket_code_points_ibfk_1` FOREIGN KEY (`code_id`) REFERENCES `ticket_codes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: call_points
-- ========================================
CREATE TABLE `call_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `points` decimal(10,2) NOT NULL,
  `call_type` enum('incoming','outgoing') NOT NULL DEFAULT 'outgoing',
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `valid_from` (`valid_from`,`valid_to`),
  KEY `call_type` (`call_type`,`valid_from`,`valid_to`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: user_monthly_bonus
-- ========================================
CREATE TABLE `user_monthly_bonus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bonus_percent` decimal(5,2) NOT NULL,
  `bonus_year` smallint(6) NOT NULL,
  `bonus_month` tinyint(4) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`bonus_year`,`bonus_month`),
  KEY `granted_by` (`granted_by`),
  CONSTRAINT `user_monthly_bonus_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_monthly_bonus_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: bonus_settings
-- ========================================
CREATE TABLE `bonus_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `min_bonus_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `max_bonus_percent` decimal(5,2) NOT NULL DEFAULT 100.00,
  `predefined_bonus_1` decimal(5,2) NOT NULL DEFAULT 5.00,
  `predefined_bonus_2` decimal(5,2) NOT NULL DEFAULT 10.00,
  `predefined_bonus_3` decimal(5,2) NOT NULL DEFAULT 15.00,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `bonus_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: knowledge_base
-- ========================================
CREATE TABLE `knowledge_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_code_id` int(11) DEFAULT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `knowledge_base_ticket_code_id_foreign` (`ticket_code_id`),
  KEY `knowledge_base_created_by_foreign` (`created_by`),
  KEY `knowledge_base_updated_by_foreign` (`updated_by`),
  KEY `idx_folder_id` (`folder_id`),
  FULLTEXT KEY `title_content_fulltext` (`title`,`content`),
  CONSTRAINT `knowledge_base_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knowledge_base_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `knowledge_base_folders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knowledge_base_ticket_code_id_foreign` FOREIGN KEY (`ticket_code_id`) REFERENCES `ticket_codes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knowledge_base_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: knowledge_base_folders
-- ========================================
CREATE TABLE `knowledge_base_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(20) DEFAULT '#3B82F6',
  `icon` varchar(50) DEFAULT 'fas fa-folder',
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `knowledge_base_folders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knowledge_base_folders_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `knowledge_base_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- جدول: password_resets
-- ========================================
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: delegation_types
-- ========================================
CREATE TABLE `delegation_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: user_delegations
-- ========================================
CREATE TABLE `user_delegations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `delegation_type_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `applicable_month` int(11) NOT NULL,
  `applicable_year` int(11) NOT NULL,
  `assigned_by_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_month_year_delegation` (`user_id`,`applicable_month`,`applicable_year`),
  KEY `delegation_type_id` (`delegation_type_id`),
  KEY `assigned_by_user_id` (`assigned_by_user_id`),
  CONSTRAINT `user_delegations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_delegations_ibfk_2` FOREIGN KEY (`delegation_type_id`) REFERENCES `delegation_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_delegations_ibfk_3` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: employee_evaluations
-- ========================================
CREATE TABLE `employee_evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `evaluator_id` int(11) NOT NULL,
  `score` decimal(4,2) NOT NULL,
  `comment` mediumtext DEFAULT NULL,
  `applicable_month` int(11) NOT NULL,
  `applicable_year` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_evaluation_period` (`user_id`,`applicable_month`,`applicable_year`),
  KEY `evaluator_id` (`evaluator_id`),
  CONSTRAINT `employee_evaluations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_evaluations_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_score` CHECK (`score` >= 0 and `score` <= 10)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: establishments
-- ========================================
CREATE TABLE `establishments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `establishment_name` varchar(255) DEFAULT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `taxpayer_number` varchar(50) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `house_number` varchar(50) DEFAULT NULL,
  `postal_zip` varchar(20) DEFAULT NULL,
  `establishment_email` varchar(255) DEFAULT NULL,
  `establishment_phone` varchar(50) DEFAULT NULL,
  `owner_full_name` varchar(255) DEFAULT NULL,
  `owner_position` varchar(100) DEFAULT NULL,
  `owner_email` varchar(255) DEFAULT NULL,
  `owner_phone` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `establishment_logo` text DEFAULT NULL,
  `establishment_header_image` text DEFAULT NULL,
  `marketer_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marketer_id` (`marketer_id`),
  CONSTRAINT `establishments_ibfk_1` FOREIGN KEY (`marketer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: breaks
-- ========================================
CREATE TABLE `breaks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_active` (`user_id`,`is_active`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `breaks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: ticket_edit_logs
-- ========================================
CREATE TABLE `ticket_edit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_detail_id` int(11) NOT NULL,
  `edited_by` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ticket_detail_id` (`ticket_detail_id`),
  KEY `idx_edited_by` (`edited_by`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ticket_edit_logs_ibfk_1` FOREIGN KEY (`ticket_detail_id`) REFERENCES `ticket_details` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_edit_logs_ibfk_2` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: user_discussion_read_status
-- ========================================
CREATE TABLE `user_discussion_read_status` (
  `user_id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `last_read_reply_id` int(11) NOT NULL,
  `last_read_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`,`discussion_id`),
  KEY `discussion_id` (`discussion_id`),
  CONSTRAINT `user_discussion_read_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_discussion_read_status_ibfk_2` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: restaurants
-- ========================================
CREATE TABLE `restaurants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `governorate` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_chain` tinyint(1) DEFAULT NULL,
  `num_stores` int(11) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `pdf_path` varchar(500) DEFAULT NULL,
  `referred_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_restaurants_referred_by` (`referred_by_user_id`),
  CONSTRAINT `fk_restaurants_referred_by` FOREIGN KEY (`referred_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- جدول: restaurant_referral_visits
-- ========================================
CREATE TABLE `restaurant_referral_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_user_id` int(11) DEFAULT NULL,
  `visit_recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `referer_url` text DEFAULT NULL,
  `registration_status` enum('visit_only','form_opened','attempted','successful') DEFAULT 'visit_only',
  `registered_restaurant_id` int(10) unsigned DEFAULT NULL,
  `visit_date` date GENERATED ALWAYS AS (cast(`visit_recorded_at` as date)) STORED,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_user_id` (`affiliate_user_id`),
  KEY `idx_registered_restaurant_id` (`registered_restaurant_id`),
  CONSTRAINT `restaurant_referral_visits_ibfk_1` FOREIGN KEY (`affiliate_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_referral_visits_ibfk_2` FOREIGN KEY (`registered_restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
