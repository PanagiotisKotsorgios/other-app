-- ============================================================
-- Call Center CRM — Migration V3
-- Adds: user_categories, category_id on users, user_notes,
--       project_assignments
-- ADDITIVE ONLY — no DROP TABLE, zero data loss
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. user_categories ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_categories` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(20)   NOT NULL,
  `label`          VARCHAR(100)  NOT NULL DEFAULT '',
  `caller_rate`    DECIMAL(5,2)  NOT NULL DEFAULT 10.00,
  `developer_rate` DECIMAL(5,2)  NOT NULL DEFAULT 10.00,
  `partner_rate`   DECIMAL(5,2)  NOT NULL DEFAULT 10.00,
  `color`          VARCHAR(30)   NOT NULL DEFAULT 'blue',
  `description`    TEXT          DEFAULT NULL,
  `sort_order`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default A/B/C/D categories (idempotent)
INSERT INTO `user_categories`
  (`name`, `label`, `caller_rate`, `developer_rate`, `partner_rate`, `color`, `description`, `sort_order`)
VALUES
  ('A', 'Top Performer', 15.00, 20.00, 15.00, 'green',  'Elite performers — highest commission rate',  1),
  ('B', 'Strong',        12.00, 15.00, 12.00, 'blue',   'Strong, consistent performers',               2),
  ('C', 'Standard',      10.00, 10.00, 10.00, 'orange', 'Standard commission rate',                    3),
  ('D', 'Developing',     7.00,  8.00,  7.00, 'red',    'Still developing — reduced commission rate',  4)
ON DUPLICATE KEY UPDATE
  label          = VALUES(label),
  caller_rate    = VALUES(caller_rate),
  developer_rate = VALUES(developer_rate),
  partner_rate   = VALUES(partner_rate),
  color          = VALUES(color),
  description    = VALUES(description),
  sort_order     = VALUES(sort_order);

-- ── 2. Add category_id to users ──────────────────────────────
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'category_id');
SET @sql = IF(@col = 0,
  'ALTER TABLE users ADD COLUMN category_id INT UNSIGNED DEFAULT NULL AFTER role',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND CONSTRAINT_NAME = 'fk_user_category');
SET @sql = IF(@fk = 0,
  'ALTER TABLE users ADD CONSTRAINT fk_user_category FOREIGN KEY (category_id) REFERENCES user_categories(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── 3. user_notes ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_notes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `body`       TEXT         NOT NULL,
  `is_pinned`  TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_unote_user` (`user_id`),
  KEY `fk_unote_by`   (`created_by`),
  CONSTRAINT `fk_unote_user` FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_unote_by`   FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. project_assignments ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_assignments` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `role_type`   ENUM('caller','developer','partner') NOT NULL DEFAULT 'developer',
  `assigned_by` INT UNSIGNED DEFAULT NULL,
  `notes`       TEXT         DEFAULT NULL,
  `assigned_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proj_user` (`project_id`, `user_id`),
  KEY `fk_pa_proj` (`project_id`),
  KEY `fk_pa_user` (`user_id`),
  CONSTRAINT `fk_pa_proj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_user` FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
