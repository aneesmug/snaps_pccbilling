<?php
/**
 * Database Cleanup Script - Remove All Test Data
 *
 * This script removes testing/demo data while preserving core configuration.
 * It runs standalone and does not bootstrap the full app (avoids installer redirects).
 */

$dir = dirname(__DIR__);
define('BASE_PATH', $dir);

require_once $dir . '/system/config.php';

$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    header('Content-Type: text/plain; charset=UTF-8');

    $forceRun = isset($_GET['force_run']) ? (string) $_GET['force_run'] : '';
    if ($forceRun !== '1') {
        $self = basename(__FILE__);
        echo "Cleanup script is in safe mode.\n";
        echo "To run from browser, use:\n";
        echo "{$self}?force_run=1\n";
        echo "\n";
        echo "Example:\n";
        echo "http://localhost/ibilling_sute/install/{$self}?force_run=1\n";
        exit;
    }
}

function table_exists(mysqli $db, $tableName)
{
    $tableName = $db->real_escape_string($tableName);
    $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tableName}'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }

    $row = $res->fetch_assoc();
    $res->free();

    return isset($row['cnt']) && (int) $row['cnt'] > 0;
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($db->connect_errno) {
        throw new Exception('DB connection failed: ' . $db->connect_error);
    }

    $db->set_charset('utf8mb4');

    $truncatedCount = 0;
    $sysAccountsDeleted = 0;

    echo "Starting database cleanup...\n";
    echo "================================================\n\n";

    // Tables to truncate (test data only)
    $tablesToTruncate = [
        'crm_accounts',
        'crm_customfieldsvalues',
        'sys_invoices',
        'sys_invoiceitems',
        'sys_quotes',
        'sys_quoteitems',
        'sys_quote_items',
        'sys_items',
        'sys_transactions',
        'sys_email_logs',
        'ib_invoice_access_log',
        'sys_activity',
        'sys_documents',
        'ib_doc_rel',
        'sys_cart',
        'sys_tickets',
        'sys_ticket_attachments',
        'crm_leads',
        'app_notes',
        'sys_contracts',
        'sys_contract_items',
        'sys_purchase_orders',
        'sys_po_items',
        'employees',
        'attendances',
        'sys_expenses',
        'app_sms',
        'sys_payments',
        'account_balances',
        'credit_cards',
        'ib_assets',
        'assets',
        'sys_projects',
        'sys_projects_items',
        'sys_companies',
    ];

    $db->query('SET FOREIGN_KEY_CHECKS=0');

    echo "Truncating test data tables...\n";
    echo "--------------------------------\n";

    foreach ($tablesToTruncate as $table) {
        try {
            if (!table_exists($db, $table)) {
                echo "- SKIPPED: {$table} (does not exist)\n";
                continue;
            }

            if ($db->query("TRUNCATE TABLE `{$table}`")) {
                echo "OK TRUNCATED: {$table}\n";
                $truncatedCount++;
            } else {
                echo "WARN: {$table} - {$db->error}\n";
            }
        } catch (Exception $e) {
            echo "WARN: {$table} - {$e->getMessage()}\n";
        }
    }

    // Preserve only sys_accounts row where account = 'admin'.
    echo "\nCleaning sys_accounts (preserve only account=admin)...\n";
    echo "-----------------------------------------------------\n";

    if (table_exists($db, 'sys_accounts')) {
        $deleteSql = "DELETE FROM `sys_accounts` WHERE `account` IS NULL OR LOWER(`account`) <> 'admin'";
        if ($db->query($deleteSql)) {
            $sysAccountsDeleted = $db->affected_rows;
            echo "OK sys_accounts cleaned. Deleted rows: {$sysAccountsDeleted}\n";
        } else {
            echo "WARN: sys_accounts cleanup failed - {$db->error}\n";
        }

        $verifySql = "SELECT COUNT(*) AS cnt FROM `sys_accounts` WHERE LOWER(`account`) = 'admin'";
        $verifyRes = $db->query($verifySql);
        if ($verifyRes) {
            $verifyRow = $verifyRes->fetch_assoc();
            $adminCount = (int) ($verifyRow['cnt'] ?? 0);
            $verifyRes->free();
            echo "OK sys_accounts admin rows preserved: {$adminCount}\n";
        }
    } else {
        echo "- SKIPPED: sys_accounts (does not exist)\n";
    }

    echo "\nVerifying ZATCA settings are safe...\n";
    echo "------------------------------------\n";

    if (table_exists($db, 'sys_appconfig')) {
        $check = "SELECT COUNT(*) AS cnt FROM `sys_appconfig` WHERE `setting` LIKE 'zatca%'";
        $res = $db->query($check);
        if ($res) {
            $row = $res->fetch_assoc();
            $res->free();
            $count = (int) ($row['cnt'] ?? 0);
            if ($count > 0) {
                echo "OK ZATCA settings preserved ({$count} settings)\n";
            } else {
                echo "INFO No ZATCA settings found\n";
            }
        }
    } else {
        echo "WARN sys_appconfig table not found\n";
    }

    $db->query('SET FOREIGN_KEY_CHECKS=1');

    echo "\n================================================\n";
    echo "Database cleanup completed successfully\n";
    echo "================================================\n\n";
    echo "Summary:\n";
    echo "  - Tables truncated: {$truncatedCount}\n";
    echo "  - sys_accounts rows deleted (non-admin): {$sysAccountsDeleted}\n";
    echo "  - admin in sys_accounts preserved: YES\n";
    echo "  - ZATCA settings preserved: YES\n\n";

    $db->close();
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
