<?php
/**
 * Database migration to add duplicate invoice number prevention
 * This ensures full invoice numbers (prefix + number) are unique per invoice type
 * Run this to set up the database indexes for duplicate prevention
 */

// Load the application framework
$dir = dirname(dirname(__FILE__));
define('BASE_PATH', $dir);
require_once $dir . '/index.php';

try {
    $db = ORM::get_db();
    $table_name = 'sys_invoices';
    $old_index_name = 'unique_invoicenum_type';
    $new_index_name = 'unique_invoicenum_cn_type';
    
    // Check if old prefix-only unique index exists
    $result = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.STATISTICS 
                         WHERE TABLE_SCHEMA = DATABASE() 
                         AND TABLE_NAME = '{$table_name}' 
                         AND INDEX_NAME = '{$old_index_name}'");
    
    // Handle both mysqli and PDO result types
    $row = null;
    if ($result instanceof mysqli_result) {
        $row = $result->fetch_assoc();
    } else {
        $row = $result->fetch(PDO::FETCH_ASSOC);
    }
    
    if ($row['cnt'] > 0) {
        $db->query("ALTER TABLE `{$table_name}` DROP INDEX `{$old_index_name}`");
        echo "Dropped old unique index on (invoicenum, type).\n";
    }

    // Check if new full invoice number unique index exists
    $result = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.STATISTICS 
                         WHERE TABLE_SCHEMA = DATABASE() 
                         AND TABLE_NAME = '{$table_name}' 
                         AND INDEX_NAME = '{$new_index_name}'");

    $row = null;
    if ($result instanceof mysqli_result) {
        $row = $result->fetch_assoc();
    } else {
        $row = $result->fetch(PDO::FETCH_ASSOC);
    }

    if ($row['cnt'] == 0) {
        // Add unique index on full invoice number (prefix + number) and type
        $db->query("ALTER TABLE `{$table_name}` ADD UNIQUE INDEX `{$new_index_name}` (`invoicenum`(100), `cn`(100), `type`)");
        echo "Successfully added unique index on (invoicenum, cn, type) combination.\n";
    } else {
        echo "Unique index on (invoicenum, cn, type) already exists. Skipping.\n";
    }
    
    // Verify the index was created
    $verify = $db->query("SHOW INDEXES FROM `{$table_name}` WHERE Key_name = '{$new_index_name}'");
    
    // Handle both mysqli and PDO for row counting
    $row_count = 0;
    if ($verify instanceof mysqli_result) {
        $row_count = $verify->num_rows;
    } else {
        $row_count = $verify->rowCount();
    }
    
    if ($row_count > 0) {
        echo "✓ Duplicate invoice number prevention database schema is now active.\n";
        echo "✓ Full invoice numbers (prefix + number) are now unique per type (Invoice vs Credit Note).\n";
    } else {
        echo "Warning: Could not verify index creation.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
