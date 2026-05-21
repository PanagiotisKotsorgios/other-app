-- ============================================================
-- Call Center CRM — Migration V5
-- Adds: partner_involvement to deals
-- ADDITIVE ONLY — no DROP TABLE, zero data loss
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'deals' AND COLUMN_NAME = 'partner_involvement');
SET @sql = IF(@col = 0,
  "ALTER TABLE deals ADD COLUMN partner_involvement ENUM('contact','presentation','active_support','full_closure') DEFAULT NULL AFTER partner_id",
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
