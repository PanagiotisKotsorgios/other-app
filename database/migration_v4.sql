-- ============================================================
-- Call Center CRM — Migration V4
-- Updates: category labels/descriptions to Greek
-- ADDITIVE ONLY — no DROP TABLE, zero data loss
-- Run with: mysql --default-character-set=utf8mb4 ...
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Update category labels and descriptions to Greek (idempotent)
INSERT INTO `user_categories`
  (`name`, `label`, `caller_rate`, `developer_rate`, `partner_rate`, `color`, `description`, `sort_order`)
VALUES
  ('A', 'Κορυφαίος',      15.00, 20.00, 15.00, 'green',  'Κορυφαίοι συνεργάτες — υψηλότερο ποσοστό προμήθειας', 1),
  ('B', 'Δυνατός',         12.00, 15.00, 12.00, 'blue',   'Δυνατοί, συνεπείς συνεργάτες',                         2),
  ('C', 'Τυπικός',         10.00, 10.00, 10.00, 'orange', 'Τυπικό ποσοστό προμήθειας',                             3),
  ('D', 'Υπό Ανάπτυξη',    7.00,  8.00,  7.00, 'red',    'Υπό ανάπτυξη — μειωμένο ποσοστό προμήθειας',          4)
ON DUPLICATE KEY UPDATE
  label          = VALUES(label),
  caller_rate    = VALUES(caller_rate),
  developer_rate = VALUES(developer_rate),
  partner_rate   = VALUES(partner_rate),
  color          = VALUES(color),
  description    = VALUES(description),
  sort_order     = VALUES(sort_order);

SET FOREIGN_KEY_CHECKS = 1;
