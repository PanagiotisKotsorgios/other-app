-- migration_v6: partner_documents table
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `partner_documents` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `partner_id`    INT UNSIGNED NOT NULL,
    `doc_type`      ENUM('contract','partner_invoice','client_invoice') NOT NULL,
    `filename`      VARCHAR(255)    NOT NULL,
    `original_name` VARCHAR(255)    NOT NULL,
    `title`         VARCHAR(255)    NULL,
    `amount`        DECIMAL(10,2)   NULL,
    `notes`         TEXT            NULL,
    `uploaded_by`   INT UNSIGNED    NOT NULL,
    `created_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_partner_type` (`partner_id`, `doc_type`),
    CONSTRAINT `fk_pd_partner`  FOREIGN KEY (`partner_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pd_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
