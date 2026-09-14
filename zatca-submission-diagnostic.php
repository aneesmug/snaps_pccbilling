<?php
/**
 * ZATCA Submission Method Diagnostic
 * 
 * Use this script to check which ZATCA submission method is available:
 * - Java SDK (preferred, faster)
 * - PHP API Client (fallback for shared hosting)
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ZATCA Submission Method Diagnostic\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Attempt to load dependencies
$diag = null;
$error_msg = null;

try {
    // Find app path
    $app_path = __DIR__;
    $system_path = $app_path . '/system';
    
    // Check if paths exist
    if (!is_dir($system_path)) {
        throw new Exception("System directory not found at: " . $system_path);
    }
    
    // Try to load autoloader or classes
    $api_client_file = $system_path . '/autoload/ZatcaApiClient.php';
    $api_submitter_file = $system_path . '/autoload/ZatcaApiSubmitter.php';
    
    if (!file_exists($api_client_file)) {
        throw new Exception("ZatcaApiClient.php not found at: " . $api_client_file);
    }
    
    if (!file_exists($api_submitter_file)) {
        throw new Exception("ZatcaApiSubmitter.php not found at: " . $api_submitter_file);
    }
    
    // Load dependencies
    require_once $api_client_file;
    require_once $api_submitter_file;
    
    // Get diagnostics
    $diag = ZatcaApiSubmitter::getDiagnostics();
    
} catch (Exception $e) {
    $error_msg = $e->getMessage();
    $diag = null;
}

echo "Server Environment:\n";
echo "  PHP Version: " . PHP_VERSION . "\n";
echo "  Operating System: " . php_uname() . "\n";
echo "  Script Path: " . __FILE__ . "\n";
echo "  Working Dir: " . __DIR__ . "\n\n";

// Check for load errors
if ($error_msg !== null) {
    echo "⚠️  FATAL ERROR:\n";
    echo "  " . $error_msg . "\n\n";
    echo "Troubleshooting:\n";
    echo "  1. Ensure ZatcaApiClient.php exists in system/autoload/\n";
    echo "  2. Ensure ZatcaApiSubmitter.php exists in system/autoload/\n";
    echo "  3. Check file permissions (should be readable)\n";
    echo "  4. Check PHP error log for more details\n\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    exit(1);
}

if ($diag === null) {
    echo "❌ Failed to load diagnostics\n\n";
    echo "Possible causes:\n";
    echo "  • PHP classes not properly defined\n";
    echo "  • Missing dependencies in class files\n";
    echo "  • PHP version incompatibility\n\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    exit(1);
}


echo "ZATCA Submission Methods:\n";
echo "  ─────────────────────────────────────────────────────────\n";

// Java availability
echo "  1. Java SDK (Official ZATCA Method)\n";
if ($diag['java_available']) {
    echo "     Status: ✅ AVAILABLE\n";
} else {
    echo "     Status: ❌ NOT AVAILABLE\n";
    echo "     Note: Java runtime not found in PATH\n";
}
echo "\n";

// API availability
echo "  2. PHP API Client (Shared Hosting Fallback)\n";
if ($diag['api_available']) {
    echo "     Status: ✅ AVAILABLE\n";
    echo "     Requirements: All met ✓\n";
} else {
    echo "     Status: ❌ NOT AVAILABLE\n";
    echo "     Missing Requirements:\n";
    foreach ($diag['api_missing_requirements'] as $req) {
        echo "       • " . $req . "\n";
    }
}
echo "\n";

// Preferred method
echo "Recommended Method:\n";
echo "  ─────────────────────────────────────────────────────────\n";
$preferred = $diag['preferred_method'];
if ($preferred === 'java') {
    echo "  🟢 Java SDK (faster, official method)\n";
} elseif ($preferred === 'api') {
    echo "  🟠 PHP API Client (works on shared hosting)\n";
} else {
    echo "  🔴 NO METHOD AVAILABLE\n";
}
echo "\n";

// Technical details
echo "Technical Details:\n";
echo "  ─────────────────────────────────────────────────────────\n";
echo "  cURL Extension: " . ($diag['curl_available'] ? '✅ Yes' : '❌ No') . "\n";
echo "  OpenSSL Extension: " . ($diag['openssl_available'] ? '✅ Yes' : '❌ No') . "\n";
echo "\n";

// Recommendations
echo "Recommendations:\n";
echo "  ─────────────────────────────────────────────────────────\n";

if ($diag['java_available'] && $diag['api_available']) {
    echo "  ✅ Both methods available.\n";
    echo "     Java SDK will be used (faster).\n";
    echo "     API fallback available if Java fails.\n";
} elseif ($diag['api_available']) {
    echo "  ✅ PHP API Client is working.\n";
    echo "     ZATCA submissions will use PHP REST API.\n";
    echo "     No Java installation required!\n";
} elseif ($diag['java_available']) {
    echo "  ⚠️  Only Java SDK is available.\n";
    echo "     Install PHP OpenSSL and cURL extensions for API fallback.\n";
} else {
    echo "  ❌ NO SOLUTION AVAILABLE.\n";
    echo "     For shared hosting:\n";
    echo "       1. Install OpenSSL and cURL PHP extensions (contact hosting)\n";
    echo "       2. OR migrate to VPS/Dedicated server\n";
    echo "       3. OR contact hosting provider to install Java\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Submission will AUTOMATICALLY use the available method.\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
?>
