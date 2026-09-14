<?php
/**
 * Database migration to add ZATCA Phase-2 columns to sys_invoices table
 * Run this to set up the database schema for ZATCA tracking
 */

// Load the application framework
$dir = dirname(dirname(__FILE__));
define('BASE_PATH', $dir);
require_once $dir . '/index.php';

try {
    $db = ORM::get_db();
    
    // List of ZATCA columns to add
    $columns_to_add = [
        ['name' => 'zatca_status', 'type' => "VARCHAR(50) NULL DEFAULT NULL COMMENT 'ZATCA submission status: submitted, failed, not_submitted'"],
        ['name' => 'zatca_uuid', 'type' => "VARCHAR(255) NULL DEFAULT NULL COMMENT 'ZATCA unique identifier'"],
        ['name' => 'zatca_invoice_type', 'type' => "VARCHAR(50) NULL DEFAULT NULL COMMENT 'ZATCA invoice type'"],
        ['name' => 'zatca_last_submit_at', 'type' => "DATETIME NULL DEFAULT NULL COMMENT 'Last ZATCA submission timestamp'"],
        ['name' => 'zatca_last_response', 'type' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Last ZATCA API response'"],
        ['name' => 'zatca_submission_http_code', 'type' => "INT(3) NULL DEFAULT NULL COMMENT 'HTTP response code from ZATCA'"],
        ['name' => 'zatca_submission_response', 'type' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Full submission response'"],
        ['name' => 'zatca_hash', 'type' => "VARCHAR(512) NULL DEFAULT NULL COMMENT 'Invoice SHA256 hash'"],
        ['name' => 'zatca_invoice_hash', 'type' => "VARCHAR(512) NULL DEFAULT NULL COMMENT 'Alternative invoice hash field'"],
        ['name' => 'zatca_signature', 'type' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Invoice signature'"],
        ['name' => 'zatca_public_key', 'type' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Public key for verification'"],
        ['name' => 'zatca_ca_signature', 'type' => "LONGTEXT NULL DEFAULT NULL COMMENT 'CA certificate signature'"],
        ['name' => 'zatca_qr_signature', 'type' => "LONGTEXT NULL DEFAULT NULL COMMENT 'QR code signature'"],
    ];
    
    echo "=== Adding ZATCA Columns to sys_invoices Table ===\n";
    
    foreach ($columns_to_add as $col) {
        $column_name = $col['name'];
        $column_type = $col['type'];
        
        // Check if column already exists
        $check_sql = "SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_NAME = 'sys_invoices' AND COLUMN_NAME = '{$column_name}'";
        $result = $db->query($check_sql)->fetch();
        
        if ($result['cnt'] == 0) {
            $alter_sql = "ALTER TABLE `sys_invoices` ADD `{$column_name}` {$column_type}";
            $db->exec($alter_sql);
            echo "✓ Added column: {$column_name}\n";
        } else {
            echo "~ Column already exists: {$column_name}\n";
        }
    }
    
    echo "\n=== Migration Complete ===\n";
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
