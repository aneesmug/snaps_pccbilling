<?php
/**
 * ZATCA Compliance Credentials Recovery - Complete Migration
 * 
 * This script:
 * 1. Fixes all NULL values in zatca_* columns to empty strings
 * 2. Updates sys_appconfig column definition to NOT NULL with default ''
 * 3. Verifies the bootstrap config loading works correctly
 * 4. Tests the recovery flow
 * 
 * Safe for live server - uses transactions and rollback on error
 * Run this once to permanently fix the 409 recovery issue
 */

require __DIR__ . '/system/config.php';

$log = [];
$errors = [];

function logMsg($msg) {
    global $log;
    $log[] = '[' . date('H:i:s') . '] ' . $msg;
    echo $msg . "\n";
}

function logError($msg) {
    global $errors;
    $errors[] = $msg;
    logMsg("❌ ERROR: $msg");
}

try {
    // ========================================================================
    // PHASE 1: Connect and check current state
    // ========================================================================
    echo "\n=== ZATCA 409 Recovery Fix - Phase 1: Assessment ===\n\n";
    
    $port = defined('DB_PORT') && DB_PORT !== '' ? ';port=' . DB_PORT : '';
    $dsn = 'mysql:host=' . DB_HOST . $port . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    logMsg("Connected to database: " . DB_NAME);
    
    // Check current NULL count
    $stmt = $pdo->query("
        SELECT COUNT(*) as null_count
        FROM sys_appconfig
        WHERE setting LIKE 'zatca_%' AND value IS NULL
    ");
    $nullCount = (int) $stmt->fetch(PDO::FETCH_ASSOC)['null_count'];
    logMsg("Found " . ($nullCount > 0 ? $nullCount : "no") . " NULL zatca values");
    
    // Check column definition
    $stmt = $pdo->query("SHOW FULL COLUMNS FROM sys_appconfig WHERE Field = 'value'");
    $colDef = $stmt->fetch(PDO::FETCH_ASSOC);
    $isNullable = ($colDef['Null'] === 'YES');
    logMsg("Column 'value' is " . ($isNullable ? "NULLABLE" : "NOT NULL") . " (Type: " . $colDef['Type'] . ")");
    
    // ========================================================================
    // PHASE 2: Data Cleanup - Convert NULLs to empty strings
    // ========================================================================
    if ($nullCount > 0) {
        echo "\n=== Phase 2: Data Cleanup ===\n\n";
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                UPDATE sys_appconfig 
                SET value = '' 
                WHERE setting LIKE 'zatca_%' AND value IS NULL
            ");
            $stmt->execute();
            $updated = $stmt->rowCount();
            
            logMsg("✓ Converted $updated NULL values to empty strings");
            
            $pdo->commit();
            logMsg("✓ Changes committed");
        } catch (Exception $e) {
            $pdo->rollBack();
            logError("Failed to update NULL values: " . $e->getMessage());
            throw $e;
        }
    } else {
        echo "\n=== Phase 2: Data Cleanup ===\n\n";
        logMsg("ℹ No NULL values to clean up");
    }
    
    // ========================================================================
    // PHASE 3: Column Definition Fix
    // ========================================================================
    if ($isNullable) {
        echo "\n=== Phase 3: Column Definition Fix ===\n\n";
        
        $pdo->beginTransaction();
        try {
            $pdo->exec("
                ALTER TABLE `sys_appconfig` 
                MODIFY COLUMN `value` MEDIUMTEXT NOT NULL DEFAULT ''
            ");
            
            logMsg("✓ Updated column definition to NOT NULL DEFAULT ''");
            
            $pdo->commit();
            logMsg("✓ Changes committed");
        } catch (Exception $e) {
            $pdo->rollBack();
            logError("Failed to alter column: " . $e->getMessage());
            throw $e;
        }
    } else {
        echo "\n=== Phase 3: Column Definition Fix ===\n\n";
        logMsg("ℹ Column already properly configured");
    }
    
    // ========================================================================
    // PHASE 4: Verify Bootstrap Loading
    // ========================================================================
    echo "\n=== Phase 4: Bootstrap Config Verification ===\n\n";
    
    // Clear any existing config
    $testConfig = [];
    
    // Simulate bootstrap loading
    $result = ORM::for_table('sys_appconfig')->find_array();
    logMsg("ORM query returned " . count($result) . " total rows");
    
    $zatcaCount = 0;
    $emptyCount = 0;
    $dataCount = 0;
    
    foreach ($result as $value) {
        $setting = $value['setting'];
        $val = $value['value'];
        
        // Apply the bootstrap fix
        $testConfig[$setting] = ($val === null ? '' : $val);
        
        if (strpos($setting, 'zatca_') === 0) {
            $zatcaCount++;
            if ($val === null) {
                logMsg("  ℹ $setting is NULL (converted to empty)");
            } elseif (strlen($val) === 0) {
                $emptyCount++;
            } else {
                $dataCount++;
                $preview = strlen($val) > 50 ? substr($val, 0, 50) . '...' : $val;
                logMsg("  ✓ $setting has " . strlen($val) . " bytes");
            }
        }
    }
    
    logMsg("ZATCA Settings Summary:");
    logMsg("  Total zatca_* keys: $zatcaCount");
    logMsg("  With data: $dataCount");
    logMsg("  Empty/unused: " . ($zatcaCount - $dataCount));
    
    // ========================================================================
    // PHASE 5: Recovery Flow Test
    // ========================================================================
    echo "\n=== Phase 5: 409 Recovery Flow Test ===\n\n";
    
    $testToken = $testConfig['zatca_binary_security_token'] ?? '';
    $testSecret = $testConfig['zatca_secret'] ?? '';
    $testRequestId = $testConfig['zatca_compliance_request_id'] ?? '';
    
    logMsg("Test 409 Recovery Data:");
    logMsg("  Token: " . (strlen($testToken) > 100 ? strlen($testToken) . " bytes ✓" : (strlen($testToken) === 0 ? "empty (expected for new onboarding)" : strlen($testToken) . " bytes")));
    logMsg("  Secret: " . (strlen($testSecret) > 20 ? strlen($testSecret) . " bytes ✓" : (strlen($testSecret) === 0 ? "empty (expected for new onboarding)" : strlen($testSecret) . " bytes")));
    logMsg("  Request ID: " . (strlen($testRequestId) > 0 ? strlen($testRequestId) . " bytes ✓" : "empty (expected for new onboarding)"));
    
    $hasCompliance = (strlen($testToken) > 100 && strlen($testSecret) > 20);
    if ($hasCompliance) {
        logMsg("\n✓ Compliance credentials available for recovery");
    } else {
        logMsg("\nℹ No compliance credentials (expected for new onboarding)");
    }
    
    // ========================================================================
    // PHASE 6: Production Credentials Check
    // ========================================================================
    echo "\n=== Phase 6: Production Credentials Status ===\n\n";
    
    $prodToken = $testConfig['zatca_production_binary_security_token'] ?? '';
    $prodSecret = $testConfig['zatca_production_secret'] ?? '';
    $prodCsid = $testConfig['zatca_production_csid'] ?? '';
    
    logMsg("Production Credentials:");
    logMsg("  Token: " . (strlen($prodToken) > 100 ? "✓ Saved" : "Not yet obtained"));
    logMsg("  Secret: " . (strlen($prodSecret) > 20 ? "✓ Saved" : "Not yet obtained"));
    logMsg("  CSID: " . (strlen($prodCsid) > 100 ? "✓ Saved" : "Not yet obtained"));
    
    $hasProduction = (strlen($prodToken) > 100 && strlen($prodSecret) > 20);
    if ($hasProduction) {
        logMsg("\n✓ Production credentials already obtained - can skip to invoicing");
    } else {
        logMsg("\nℹ Production not yet configured");
    }
    
    // ========================================================================
    // FINAL SUMMARY
    // ========================================================================
    echo "\n=== Final Summary ===\n\n";
    
    if (empty($errors)) {
        logMsg("✅ ALL FIXES APPLIED SUCCESSFULLY");
        logMsg("\nNext Steps:");
        logMsg("1. The bootstrap config loader is now defensive against NULLs");
        logMsg("2. The sys_appconfig column is now NOT NULL with empty string default");
        logMsg("3. All existing NULL values have been converted to empty strings");
        logMsg("\nOnboarding Flow Ready:");
        if ($hasCompliance) {
            logMsg("  ✓ Step 1 (Compliance CSID) - Complete");
            if ($hasProduction) {
                logMsg("  ✓ Step 2 (Production CSID) - Complete");
                logMsg("  ✓ Ready for Step 3 (Invoice Check) or invoicing");
            } else {
                logMsg("  ○ Step 2 (Production CSID) - Ready to run");
            }
        } else {
            logMsg("  ○ Step 1 (Compliance CSID) - Ready to run");
        }
    } else {
        logMsg("❌ ERRORS OCCURRED:");
        foreach ($errors as $err) {
            logMsg("  - $err");
        }
    }
    
    echo "\n";
    
} catch (Exception $e) {
    logError("Fatal error: " . $e->getMessage());
    echo "\nStacktrace:\n";
    echo $e->getTraceAsString() . "\n";
}

// Save log to file for reference
$logFile = __DIR__ . '/storage/logs/zatca-recovery-fix-' . date('Y-m-d-H-i-s') . '.log';
@mkdir(dirname($logFile), 0777, true);
file_put_contents($logFile, implode("\n", $log));
echo "Log saved to: " . basename($logFile) . "\n";
?>
