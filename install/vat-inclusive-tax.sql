-- VAT-inclusive tax migration
-- Adds `is_inclusive` column to `sys_tax` and seeds a "VAT (Inclusive)" tax option.
-- Safe to run multiple times (idempotent).
-- Run against your live database, e.g.:
--   mysql -u DB_USER -p DB_NAME < install/vat-inclusive-tax.sql

SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'sys_tax'
      AND COLUMN_NAME = 'is_inclusive'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `sys_tax` ADD COLUMN `is_inclusive` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rate`',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO `sys_tax` (`name`, `state`, `country`, `rate`, `aid`, `bal`, `created_at`, `updated_at`, `is_default`, `is_inclusive`)
SELECT 'VAT (Inclusive)', NULL, NULL, 15.00, NULL, 0.00, NOW(), NOW(), 0, 1
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_tax` WHERE `name` = 'VAT (Inclusive)'
);
