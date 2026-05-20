-- ============================================================
-- Call Center CRM — Migration V2
-- Adds: Roles, Projects, Contracts, Invoices, Expenses
-- MySQL 8.0 compatible (no ADD COLUMN IF NOT EXISTS)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── 0. Expand users.role ENUM to include developer/partner ───
ALTER TABLE users MODIFY COLUMN role
  ENUM('admin','caller','developer','partner') NOT NULL DEFAULT 'caller';

-- ── 1. user_roles pivot ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` INT UNSIGNED NOT NULL,
  `role`    ENUM('admin','caller','developer','partner') NOT NULL,
  PRIMARY KEY (`user_id`, `role`),
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Extend deals (safe via DROP/ADD trick) ─────────────────
-- Add developer_id if missing
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'deals' AND COLUMN_NAME = 'developer_id');
SET @sql = IF(@col = 0,
  'ALTER TABLE deals ADD COLUMN developer_id INT UNSIGNED DEFAULT NULL AFTER caller_id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add partner_id if missing
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'deals' AND COLUMN_NAME = 'partner_id');
SET @sql = IF(@col = 0,
  'ALTER TABLE deals ADD COLUMN partner_id INT UNSIGNED DEFAULT NULL AFTER developer_id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add contract_signed if missing
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'deals' AND COLUMN_NAME = 'contract_signed');
SET @sql = IF(@col = 0,
  'ALTER TABLE deals ADD COLUMN contract_signed TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK: deals → developer
SET @fk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'deals' AND CONSTRAINT_NAME = 'fk_deal_developer');
SET @sql = IF(@fk = 0,
  'ALTER TABLE deals ADD CONSTRAINT fk_deal_developer FOREIGN KEY (developer_id) REFERENCES users(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK: deals → partner
SET @fk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'deals' AND CONSTRAINT_NAME = 'fk_deal_partner');
SET @sql = IF(@fk = 0,
  'ALTER TABLE deals ADD CONSTRAINT fk_deal_partner FOREIGN KEY (partner_id) REFERENCES users(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── 3. Extend commissions (role_type column) ──────────────────
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commissions' AND COLUMN_NAME = 'role_type');
SET @sql = IF(@col = 0,
  "ALTER TABLE commissions ADD COLUMN role_type ENUM('caller','developer','partner') NOT NULL DEFAULT 'caller' AFTER rate",
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop old unique key on deal_id and replace with (deal_id, role_type)
SET @uk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commissions' AND CONSTRAINT_NAME = 'uq_deal');
SET @sql = IF(@uk > 0, 'ALTER TABLE commissions DROP INDEX uq_deal', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @uk = (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commissions' AND INDEX_NAME = 'uq_deal_role');
SET @sql = IF(@uk = 0,
  'ALTER TABLE commissions ADD UNIQUE KEY uq_deal_role (deal_id, role_type)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── 4. projects ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `projects` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `deal_id`       INT UNSIGNED  NOT NULL,
  `developer_id`  INT UNSIGNED  DEFAULT NULL,
  `title`         VARCHAR(255)  NOT NULL,
  `description`   TEXT          DEFAULT NULL,
  `status`        ENUM('awaiting_assignment','in_progress','testing','on_hold','completed') NOT NULL DEFAULT 'awaiting_assignment',
  `priority`      ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `start_date`    DATE          DEFAULT NULL,
  `deadline`      DATE          DEFAULT NULL,
  `actual_end`    DATE          DEFAULT NULL,
  `budget`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tech_stack`    VARCHAR(255)  DEFAULT NULL,
  `repo_url`      VARCHAR(255)  DEFAULT NULL,
  `staging_url`   VARCHAR(255)  DEFAULT NULL,
  `live_url`      VARCHAR(255)  DEFAULT NULL,
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proj_deal` (`deal_id`),
  KEY `fk_proj_dev` (`developer_id`),
  CONSTRAINT `fk_proj_deal` FOREIGN KEY (`deal_id`)      REFERENCES `deals`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_proj_dev`  FOREIGN KEY (`developer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. project_phases ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_phases` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`   INT UNSIGNED NOT NULL,
  `name`         VARCHAR(255) NOT NULL,
  `description`  TEXT         DEFAULT NULL,
  `status`       ENUM('pending','in_progress','completed','skipped') NOT NULL DEFAULT 'pending',
  `order_num`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `due_date`     DATE         DEFAULT NULL,
  `completed_at` TIMESTAMP    NULL DEFAULT NULL,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_phase_proj` (`project_id`),
  CONSTRAINT `fk_phase_proj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. project_notes ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_notes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `body`        TEXT         NOT NULL,
  `is_internal` TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pnote_proj` (`project_id`),
  KEY `fk_pnote_user` (`user_id`),
  CONSTRAINT `fk_pnote_proj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pnote_user` FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. contracts ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contracts` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deal_id`       INT UNSIGNED NOT NULL,
  `filename`      VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `uploaded_by`   INT UNSIGNED NOT NULL,
  `notes`         TEXT         DEFAULT NULL,
  `uploaded_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_con_deal` (`deal_id`),
  KEY `fk_con_user` (`uploaded_by`),
  CONSTRAINT `fk_con_deal` FOREIGN KEY (`deal_id`)     REFERENCES `deals`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_con_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 8. invoices ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `deal_id`       INT UNSIGNED  NOT NULL,
  `invoice_no`    VARCHAR(100)  DEFAULT NULL,
  `amount`        DECIMAL(12,2) NOT NULL,
  `vat_rate`      DECIMAL(5,2)  NOT NULL DEFAULT 24.00,
  `vat_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `filename`      VARCHAR(255)  DEFAULT NULL,
  `original_name` VARCHAR(255)  DEFAULT NULL,
  `status`        ENUM('draft','issued','sent','paid') NOT NULL DEFAULT 'draft',
  `issued_at`     DATE          DEFAULT NULL,
  `due_at`        DATE          DEFAULT NULL,
  `paid_at`       DATE          DEFAULT NULL,
  `uploaded_by`   INT UNSIGNED  DEFAULT NULL,
  `notes`         TEXT          DEFAULT NULL,
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_inv_deal` (`deal_id`),
  CONSTRAINT `fk_inv_deal` FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 9. expenses ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `expenses` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `project_id`   INT UNSIGNED  DEFAULT NULL,
  `deal_id`      INT UNSIGNED  DEFAULT NULL,
  `description`  VARCHAR(255)  NOT NULL,
  `amount`       DECIMAL(12,2) NOT NULL,
  `category`     ENUM('hosting','software','hardware','subcontractor','marketing','salary','tax','other') NOT NULL DEFAULT 'other',
  `receipt_file` VARCHAR(255)  DEFAULT NULL,
  `expense_date` DATE          DEFAULT NULL,
  `created_by`   INT UNSIGNED  DEFAULT NULL,
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_exp_proj` (`project_id`),
  KEY `fk_exp_deal` (`deal_id`),
  CONSTRAINT `fk_exp_proj` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_exp_deal` FOREIGN KEY (`deal_id`)    REFERENCES `deals`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
