<?php
/**
 * Database migration to add VAT-inclusive tax support.
 * Adds `is_inclusive` column to `sys_tax` and seeds a "VAT (Inclusive)" option
 * so invoice items can be taxed either exclusive (added on top) or inclusive
 * (entered price already contains the tax, split out on save).
 * Safe to run multiple times.
 */

$dir = dirname(dirname(__FILE__));
define('BASE_PATH', $dir);
require_once $dir . '/index.php';

try {
    $db = ORM::get_db();
    $table_name = 'sys_tax';
    $column_name = 'is_inclusive';

    $result = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                         WHERE TABLE_SCHEMA = DATABASE()
                         AND TABLE_NAME = '{$table_name}'
                         AND COLUMN_NAME = '{$column_name}'");

    $row = null;
    if ($result instanceof mysqli_result) {
        $row = $result->fetch_assoc();
    } else {
        $row = $result->fetch(PDO::FETCH_ASSOC);
    }

    if ($row['cnt'] == 0) {
        $db->query("ALTER TABLE `{$table_name}` ADD COLUMN `{$column_name}` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rate`");
        echo "Added `is_inclusive` column to `sys_tax`.\n";
    } else {
        echo "`is_inclusive` column already exists on `sys_tax`. Skipping.\n";
    }

    $existing = ORM::for_table('sys_tax')->where('name', 'VAT (Inclusive)')->find_one();

    if (!$existing) {
        $tax = ORM::for_table('sys_tax')->create();
        $tax->name = 'VAT (Inclusive)';
        $tax->rate = 15.00;
        $tax->is_default = 0;
        $tax->is_inclusive = 1;
        $tax->bal = 0.00;
        $tax->save();
        echo "Added 'VAT (Inclusive)' tax option (15%).\n";
    } else {
        echo "'VAT (Inclusive)' tax option already exists. Skipping.\n";
    }

    echo "VAT-inclusive tax migration complete.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
