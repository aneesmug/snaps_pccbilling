-- Input VAT (purchases & expenses) migration for KSA VAT / ZATCA VAT return
-- Adds supplier tax-invoice details and VAT treatment to `sys_purchases` and
-- `sys_transactions` (expenses), matching InputVat::migrate() (schema version 1).
-- Safe to run multiple times (idempotent).
-- Run against your live database, e.g.:
--   mysql -u DB_USER -p DB_NAME < install/input-vat-purchases-expenses.sql
--
-- Columns:
--   supplier_invoice_number  Supplier's tax invoice No. (customs declaration No. for imports)
--   supplier_invoice_date    Supplier's tax invoice date
--   supplier_vat_number      Supplier VAT No. as printed on their tax invoice
--   vat_treatment            standard | zero | exempt | import | reverse_charge
--   reverse_charge_vat       VAT self-assessed on imported services (output and input VAT)

DROP PROCEDURE IF EXISTS `input_vat_add_column`;

DELIMITER $$
CREATE PROCEDURE `input_vat_add_column`(IN p_table VARCHAR(64), IN p_column VARCHAR(64), IN p_definition VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table
          AND COLUMN_NAME = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `', p_column, '` ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- Purchases
CALL `input_vat_add_column`('sys_purchases', 'supplier_invoice_number', 'VARCHAR(100) NULL');
CALL `input_vat_add_column`('sys_purchases', 'supplier_invoice_date', 'DATE NULL');
CALL `input_vat_add_column`('sys_purchases', 'supplier_vat_number', 'VARCHAR(20) NULL');
CALL `input_vat_add_column`('sys_purchases', 'vat_treatment', 'VARCHAR(20) NOT NULL DEFAULT ''standard''');
CALL `input_vat_add_column`('sys_purchases', 'reverse_charge_vat', 'DECIMAL(18,2) NOT NULL DEFAULT 0.00');

-- Expenses (and other transactions)
CALL `input_vat_add_column`('sys_transactions', 'supplier_invoice_number', 'VARCHAR(100) NULL');
CALL `input_vat_add_column`('sys_transactions', 'supplier_invoice_date', 'DATE NULL');
CALL `input_vat_add_column`('sys_transactions', 'supplier_vat_number', 'VARCHAR(20) NULL');
CALL `input_vat_add_column`('sys_transactions', 'vat_treatment', 'VARCHAR(20) NOT NULL DEFAULT ''standard''');
CALL `input_vat_add_column`('sys_transactions', 'reverse_charge_vat', 'DECIMAL(18,2) NOT NULL DEFAULT 0.00');

DROP PROCEDURE IF EXISTS `input_vat_add_column`;

-- Mark the schema as applied so the app skips its automatic migration check.
INSERT INTO `sys_appconfig` (`setting`, `value`)
SELECT 'input_vat_schema_version', '1'
WHERE NOT EXISTS (
    SELECT 1 FROM `sys_appconfig` WHERE `setting` = 'input_vat_schema_version'
);

UPDATE `sys_appconfig` SET `value` = '1' WHERE `setting` = 'input_vat_schema_version';
