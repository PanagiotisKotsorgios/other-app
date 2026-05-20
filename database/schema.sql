-- ============================================================
-- Call Center CRM — Full Database Schema
-- PHP 8.2 + MySQL 8.0+
-- After importing, run: php tools/setup.php  (seeds admin user)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `call_center`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `call_center`;

-- ────────────────────────────────────────────────────────────
-- Table: users
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120)      NOT NULL,
  `email`      VARCHAR(180)      NOT NULL,
  `password`   VARCHAR(255)      NOT NULL,
  `role`       ENUM('admin','caller','developer','partner') NOT NULL DEFAULT 'caller',
  `phone`      VARCHAR(30)       DEFAULT NULL,
  `avatar`     VARCHAR(255)      DEFAULT NULL,
  `is_active`  TINYINT(1)        NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role`   (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- Table: services
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `services` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(100) NOT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`name`, `slug`, `sort_order`) VALUES
  ('Website',         'website',         1),
  ('E-Shop',          'eshop',           2),
  ('Marketing',       'marketing',       3),
  ('Social Media',    'social_media',    4),
  ('SEO',             'seo',             5),
  ('Custom Software', 'custom_software', 6),
  ('ERP',             'erp',             7),
  ('CRM',             'crm',             8),
  ('Mobile App',      'mobile_app',      9),
  ('Hosting',         'hosting',         10),
  ('Support Package', 'support_package', 11)
ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order);

-- ────────────────────────────────────────────────────────────
-- Table: businesses
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `businesses` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name`  VARCHAR(200) NOT NULL,
  `contact_name`  VARCHAR(120) DEFAULT NULL,
  `email`         VARCHAR(180) DEFAULT NULL,
  `phone`         VARCHAR(50)  DEFAULT NULL,
  `website`       VARCHAR(255) DEFAULT NULL,
  `address`       VARCHAR(255) DEFAULT NULL,
  `city`          VARCHAR(100) DEFAULT NULL,
  `country`       VARCHAR(100) DEFAULT NULL,
  `category`      VARCHAR(100) DEFAULT NULL,
  `notes`         TEXT         DEFAULT NULL,
  `imported_from` VARCHAR(255) DEFAULT NULL,
  `status`        ENUM('new','contacted','interested','not_interested','deal_closed','follow_up') NOT NULL DEFAULT 'new',
  `created_by`    INT UNSIGNED DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_city`     (`city`),
  KEY `idx_category` (`category`),
  KEY `idx_status`   (`status`),
  KEY `fk_biz_created_by` (`created_by`),
  CONSTRAINT `fk_biz_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- Table: caller_assignments
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `caller_assignments` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id` INT UNSIGNED NOT NULL,
  `caller_id`   INT UNSIGNED NOT NULL,
  `assigned_by` INT UNSIGNED DEFAULT NULL,
  `assigned_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_biz_caller` (`business_id`, `caller_id`),
  KEY `fk_ca_caller`   (`caller_id`),
  KEY `fk_ca_assigned` (`assigned_by`),
  CONSTRAINT `fk_ca_business`  FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ca_caller`    FOREIGN KEY (`caller_id`)   REFERENCES `users`      (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ca_assigned`  FOREIGN KEY (`assigned_by`) REFERENCES `users`      (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- Table: interactions
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `interactions` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id`   INT UNSIGNED NOT NULL,
  `caller_id`     INT UNSIGNED NOT NULL,
  `type`          ENUM('call','email','offer','demo','follow_up','messenger','whatsapp','reminder') NOT NULL,
  `result`        ENUM('no_answer','callback','interested','not_interested','left_message','sent','completed') DEFAULT NULL,
  `notes`         TEXT         DEFAULT NULL,
  `proposal_file` VARCHAR(255) DEFAULT NULL,
  `duration_min`  SMALLINT UNSIGNED DEFAULT NULL,
  `scheduled_at`  DATETIME     DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_int_business` (`business_id`),
  KEY `fk_int_caller`   (`caller_id`),
  KEY `idx_type`        (`type`),
  KEY `idx_int_created` (`created_at`),
  CONSTRAINT `fk_int_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_int_caller`   FOREIGN KEY (`caller_id`)   REFERENCES `users`      (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- Table: interaction_services  (pivot)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `interaction_services` (
  `interaction_id` INT UNSIGNED NOT NULL,
  `service_id`     INT UNSIGNED NOT NULL,
  PRIMARY KEY (`interaction_id`, `service_id`),
  KEY `fk_is_service` (`service_id`),
  CONSTRAINT `fk_is_interaction` FOREIGN KEY (`interaction_id`) REFERENCES `interactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_is_service`     FOREIGN KEY (`service_id`)     REFERENCES `services`     (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- Table: deals
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `deals` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `business_id`    INT UNSIGNED  NOT NULL,
  `caller_id`      INT UNSIGNED  NOT NULL,
  `service_id`     INT UNSIGNED  DEFAULT NULL,
  `amount`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency`       CHAR(3)       NOT NULL DEFAULT 'EUR',
  `notes`          TEXT          DEFAULT NULL,
  `status`         ENUM('pending','approved','rejected','in_progress','completed') NOT NULL DEFAULT 'pending',
  `approved_by`    INT UNSIGNED  DEFAULT NULL,
  `approved_at`    TIMESTAMP     NULL DEFAULT NULL,
  `proposal_file`  VARCHAR(255)  DEFAULT NULL,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_deal_business` (`business_id`),
  KEY `fk_deal_caller`   (`caller_id`),
  KEY `fk_deal_service`  (`service_id`),
  KEY `fk_deal_approved` (`approved_by`),
  KEY `idx_deal_status`  (`status`),
  CONSTRAINT `fk_deal_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_deal_caller`   FOREIGN KEY (`caller_id`)   REFERENCES `users`      (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_deal_service`  FOREIGN KEY (`service_id`)  REFERENCES `services`   (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_deal_approved` FOREIGN KEY (`approved_by`) REFERENCES `users`      (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- Table: commissions
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `commissions` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `deal_id`    INT UNSIGNED  NOT NULL,
  `caller_id`  INT UNSIGNED  NOT NULL,
  `amount`     DECIMAL(12,2) NOT NULL,
  `rate`       DECIMAL(5,2)  NOT NULL DEFAULT 10.00,
  `is_paid`    TINYINT(1)    NOT NULL DEFAULT 0,
  `paid_at`    TIMESTAMP     NULL DEFAULT NULL,
  `paid_by`    INT UNSIGNED  DEFAULT NULL,
  `notes`      TEXT          DEFAULT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_deal` (`deal_id`),
  KEY `fk_com_caller` (`caller_id`),
  KEY `fk_com_paid`   (`paid_by`),
  KEY `idx_com_paid`  (`is_paid`),
  CONSTRAINT `fk_com_deal`   FOREIGN KEY (`deal_id`)   REFERENCES `deals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_com_caller` FOREIGN KEY (`caller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_com_paid`   FOREIGN KEY (`paid_by`)   REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- Table: messages
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `messages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id`   INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `subject`     VARCHAR(255) NOT NULL,
  `body`        TEXT         NOT NULL,
  `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
  `read_at`     TIMESTAMP    NULL DEFAULT NULL,
  `parent_id`   INT UNSIGNED DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_msg_sender`   (`sender_id`),
  KEY `fk_msg_receiver` (`receiver_id`),
  KEY `fk_msg_parent`   (`parent_id`),
  KEY `idx_msg_read`    (`is_read`),
  CONSTRAINT `fk_msg_sender`   FOREIGN KEY (`sender_id`)   REFERENCES `users`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_parent`   FOREIGN KEY (`parent_id`)   REFERENCES `messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
