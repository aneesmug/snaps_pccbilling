<?php
/*
 * iBilling Old One-Click Export
 *
 * Upload this file to your old iBilling root folder (same level as system/).
 * Then open in browser:
 *   https://your-old-domain.com/tools/ibilling_old_one_click_export.php?token=CHANGE_ME
 *
 * Optional query parameter:
 *   tables=sys_currencies,crm_accounts,sys_companies
 */

@set_time_limit(0);
@ini_set('memory_limit', '512M');

$exportToken = 'CHANGE_ME_BEFORE_LIVE_USE';
$givenToken = isset($_GET['token']) ? (string) $_GET['token'] : '';

if ($exportToken === 'CHANGE_ME_BEFORE_LIVE_USE') {
    http_response_code(500);
    echo 'Please set $exportToken inside ibilling_old_one_click_export.php first.';
    exit;
}

if ($givenToken === '' || !hash_equals($exportToken, $givenToken)) {
    http_response_code(403);
    echo 'Forbidden: invalid or missing token.';
    exit;
}

$configCandidates = [
    __DIR__ . '/../system/config.php',
    __DIR__ . '/system/config.php',
    __DIR__ . '/../config.php',
];

$configLoaded = false;
foreach ($configCandidates as $configPath) {
    if (is_file($configPath)) {
        require_once $configPath;
        $configLoaded = true;
        break;
    }
}

if (!$configLoaded || !defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_NAME')) {
    http_response_code(500);
    echo 'Unable to load DB credentials. Place this file inside old iBilling install so system/config.php is reachable.';
    exit;
}

$defaultTables = [
    'sys_currencies',
    'crm_accounts',
    'sys_companies',
    'sys_items',
    'sys_invoices',
    'sys_invoiceitems',
    'sys_quotes',
    'sys_quoteitems',
    'sys_accounts',
    'sys_transactions',
];

$tableRequest = isset($_GET['tables']) ? (string) $_GET['tables'] : '';
$selectedTables = $defaultTables;

if (trim($tableRequest) !== '') {
    $requested = array_map('trim', explode(',', $tableRequest));
    $requested = array_filter($requested, static function ($value) {
        return $value !== '';
    });
    $selectedTables = array_values(array_intersect($defaultTables, $requested));
}

if (empty($selectedTables)) {
    http_response_code(400);
    echo 'No valid tables selected.';
    exit;
}

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo 'DB connection failed: ' . $mysqli->connect_error;
    exit;
}

$mysqli->set_charset('utf8mb4');

$fileName = 'ibilling_old_legacy_export_' . date('Y-m-d_H-i-s') . '.sql';

header('Content-Description: File Transfer');
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: public');

$escape = static function ($value) use ($mysqli) {
    if ($value === null) {
        return 'NULL';
    }

    return "'" . $mysqli->real_escape_string((string) $value) . "'";
};

echo "-- iBilling Old Data Export\n";
echo '-- Generated at: ' . date('Y-m-d H:i:s') . "\n";
echo '-- Database: ' . DB_NAME . "\n";
echo '-- Tables: ' . implode(', ', $selectedTables) . "\n\n";

echo "SET NAMES utf8mb4;\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($selectedTables as $table) {
    $tableEsc = $mysqli->real_escape_string($table);
    $existsSql = "SHOW TABLES LIKE '" . $tableEsc . "'";
    $existsRes = $mysqli->query($existsSql);

    if (!$existsRes || $existsRes->num_rows === 0) {
        echo '-- Skipped missing table: ' . $table . "\n\n";
        if ($existsRes) {
            $existsRes->free();
        }
        continue;
    }

    $existsRes->free();

    $columns = [];
    $colRes = $mysqli->query('SHOW COLUMNS FROM `' . $table . '`');
    if ($colRes) {
        while ($col = $colRes->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        $colRes->free();
    }

    if (empty($columns)) {
        echo '-- Skipped table without columns: ' . $table . "\n\n";
        continue;
    }

    $columnSql = '`' . implode('`,`', $columns) . '`';

    echo '-- --------------------------------------------------------' . "\n";
    echo '-- Data for table `' . $table . '`' . "\n";
    echo '-- --------------------------------------------------------' . "\n";

    $rowRes = $mysqli->query('SELECT * FROM `' . $table . '`');
    if (!$rowRes) {
        echo '-- Failed reading table `' . $table . '`: ' . $mysqli->error . "\n\n";
        continue;
    }

    $rowCount = 0;
    while ($row = $rowRes->fetch_assoc()) {
        $vals = [];
        foreach ($columns as $colName) {
            $vals[] = $escape($row[$colName] ?? null);
        }

        echo 'INSERT INTO `' . $table . '` (' . $columnSql . ') VALUES (' . implode(',', $vals) . ');' . "\n";
        $rowCount++;
    }
    $rowRes->free();

    if ($rowCount === 0) {
        echo '-- No rows found in `' . $table . '`' . "\n";
    }

    echo "\n";
}

echo "SET FOREIGN_KEY_CHECKS = 1;\n";

$mysqli->close();
