<?php
require 'base.php';
require '../system/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$c_mysqli = false;
$c_pdo = false;

if (mysqli_connect_errno()) {
    try {
        $dbh = new pdo(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            DB_USER,
            DB_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $c_pdo = true;
    } catch (PDOException $ex) {
    }
} else {
    $c_mysqli = true;
}

// Import primary schema
$sql = file_get_contents('primary.sql');
$sql = str_replace(
    '---PurchaseKeyPlaceHolder---',
    $_SESSION['purchase_key'],
    $sql
);

if (!$c_mysqli && !$c_pdo) {
    echo 'Failed';
    exit();
}

try {
    if ($c_mysqli) {
        if (!$mysqli->multi_query($sql)) {
            throw new Exception('Primary schema import failed: ' . $mysqli->error);
        }

        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }

            if ($mysqli->errno) {
                throw new Exception('Primary schema import failed: ' . $mysqli->error);
            }
        } while ($mysqli->more_results() && $mysqli->next_result());

        if ($mysqli->errno) {
            throw new Exception('Primary schema import failed: ' . $mysqli->error);
        }
    } else {
        $dbh->exec($sql);
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$schemaCheckSql = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_users'";

if ($c_mysqli) {
    $schemaCheck = $mysqli->query($schemaCheckSql);
    $schemaRow = $schemaCheck ? $schemaCheck->fetch_assoc() : null;
    $sysUsersExists = !empty($schemaRow) && (int) $schemaRow['table_count'] === 1;
} else {
    $schemaCheck = $dbh->query($schemaCheckSql);
    $sysUsersExists = (int) $schemaCheck->fetchColumn() === 1;
}

if (!$sysUsersExists) {
    echo 'Primary schema import did not complete. sys_users was not created.';
    exit();
}

// Run database migrations for all new features
// This ensures all necessary database schema modifications are applied
runDatabaseMigrations($c_mysqli, $c_pdo, $mysqli, $dbh ?? null);

echo '1';

/**
 * Apply all pending database migrations
 * Migrations are idempotent and can be run multiple times safely
 */
function runDatabaseMigrations($c_mysqli, $c_pdo, $mysqli = null, $pdo = null)
{
    try {
        // Migration: Add ZATCA Phase-2 columns
        applyZatcaMigration($c_mysqli, $c_pdo, $mysqli, $pdo);
        
        // Migration: Add duplicate invoice prevention index
        applyDuplicateInvoicePreventionMigration($c_mysqli, $c_pdo, $mysqli, $pdo);
        
    } catch (Exception $e) {
        // Log migration errors but don't fail the installation
        error_log('Database Migration Error: ' . $e->getMessage());
    }
}

/**
 * Apply ZATCA Phase-2 columns migration
 * Adds columns needed for ZATCA compliance tracking
 */
function applyZatcaMigration($c_mysqli, $c_pdo, $mysqli, $pdo)
{
    $zatca_columns = [
        'zatca_status' => "VARCHAR(50) NULL DEFAULT NULL COMMENT 'ZATCA submission status: submitted, failed, not_submitted'",
        'zatca_uuid' => "VARCHAR(255) NULL DEFAULT NULL COMMENT 'ZATCA unique identifier'",
        'zatca_invoice_type' => "VARCHAR(50) NULL DEFAULT NULL COMMENT 'ZATCA invoice type'",
        'zatca_last_submit_at' => "DATETIME NULL DEFAULT NULL COMMENT 'Last ZATCA submission timestamp'",
        'zatca_last_response' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Last ZATCA API response'",
        'zatca_submission_http_code' => "INT(3) NULL DEFAULT NULL COMMENT 'HTTP response code from ZATCA'",
        'zatca_submission_response' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Full submission response'",
        'zatca_hash' => "VARCHAR(512) NULL DEFAULT NULL COMMENT 'Invoice SHA256 hash'",
        'zatca_invoice_hash' => "VARCHAR(512) NULL DEFAULT NULL COMMENT 'Alternative invoice hash field'",
        'zatca_signature' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Invoice signature'",
        'zatca_public_key' => "LONGTEXT NULL DEFAULT NULL COMMENT 'Public key for verification'",
        'zatca_ca_signature' => "LONGTEXT NULL DEFAULT NULL COMMENT 'CA certificate signature'",
        'zatca_qr_signature' => "LONGTEXT NULL DEFAULT NULL COMMENT 'QR code signature'",
    ];
    
    $db = $c_mysqli ? $mysqli : $pdo;
    $table_name = 'sys_invoices';
    
    foreach ($zatca_columns as $column_name => $column_def) {
        // Check if column already exists using INFORMATION_SCHEMA
        if ($c_mysqli) {
            $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS 
                               WHERE TABLE_SCHEMA = DATABASE() 
                               AND TABLE_NAME = '{$table_name}' 
                               AND COLUMN_NAME = '{$column_name}'");
            $row = $check->fetch_assoc();
            $column_exists = $row['cnt'] > 0;
        } else {
            $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS 
                               WHERE TABLE_SCHEMA = DATABASE() 
                               AND TABLE_NAME = '{$table_name}' 
                               AND COLUMN_NAME = '{$column_name}'")->fetch();
            $column_exists = $check['cnt'] > 0;
        }
        
        // Add column only if it doesn't exist
        if (!$column_exists) {
            $sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$column_name}` {$column_def}";
            if ($c_mysqli) {
                $db->query($sql);
            } else {
                $db->exec($sql);
            }
        }
    }
}

/**
 * Apply duplicate invoice prevention migration
 * Adds unique index on (invoicenum, cn, type) to prevent duplicates by full invoice number
 */
function applyDuplicateInvoicePreventionMigration($c_mysqli, $c_pdo, $mysqli, $pdo)
{
    $db = $c_mysqli ? $mysqli : $pdo;
    $table_name = 'sys_invoices';
    $old_index_name = 'unique_invoicenum_type';
    $new_index_name = 'unique_invoicenum_cn_type';
    
    // Check if old prefix-only unique index exists
    if ($c_mysqli) {
        $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.STATISTICS 
                           WHERE TABLE_SCHEMA = DATABASE() 
                           AND TABLE_NAME = '{$table_name}' 
                           AND INDEX_NAME = '{$old_index_name}'");
        $row = $check->fetch_assoc();
        $old_index_exists = $row['cnt'] > 0;
    } else {
        $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.STATISTICS 
                           WHERE TABLE_SCHEMA = DATABASE() 
                           AND TABLE_NAME = '{$table_name}' 
                           AND INDEX_NAME = '{$old_index_name}'")->fetch();
        $old_index_exists = $check['cnt'] > 0;
    }

    // Drop old prefix-only unique index if present
    if ($old_index_exists) {
        $sql = "ALTER TABLE `{$table_name}` DROP INDEX `{$old_index_name}`";
        if ($c_mysqli) {
            $db->query($sql);
        } else {
            $db->exec($sql);
        }
    }

    // Check if new full invoice number unique index already exists
    if ($c_mysqli) {
        $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.STATISTICS 
                           WHERE TABLE_SCHEMA = DATABASE() 
                           AND TABLE_NAME = '{$table_name}' 
                           AND INDEX_NAME = '{$new_index_name}'");
        $row = $check->fetch_assoc();
        $new_index_exists = $row['cnt'] > 0;
    } else {
        $check = $db->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.STATISTICS 
                           WHERE TABLE_SCHEMA = DATABASE() 
                           AND TABLE_NAME = '{$table_name}' 
                           AND INDEX_NAME = '{$new_index_name}'")->fetch();
        $new_index_exists = $check['cnt'] > 0;
    }
    
    // Add full invoice number unique index only if it doesn't exist
    if (!$new_index_exists) {
        $sql = "ALTER TABLE `{$table_name}` ADD UNIQUE INDEX `{$new_index_name}` (`invoicenum`(100), `cn`(100), `type`)";
        if ($c_mysqli) {
            $db->query($sql);
        } else {
            $db->exec($sql);
        }
    }
}
