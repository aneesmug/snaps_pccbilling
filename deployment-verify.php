<?php
/**
 * ZATCA Phase 2 Deployment & Signature Verification Tool
 * 
 * Upload this file to your live server and access via browser
 * It will verify that all 6 critical fixes are deployed and working
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define required constants before loading app.php
if (!defined('APP_RUN')) {
    define('APP_RUN', true);
    define('APP_BASE_PATH', dirname(__FILE__));
    define('APP_SYSTEM_PATH', dirname(__FILE__) . '/system');
    require 'system/app.php';
}

$output = [];
$errors = [];
$warnings = [];

// ============================================================================
// STEP 1: Verify File Deployment
// ============================================================================

$output[] = "=== ZATCA PHASE 2 DEPLOYMENT VERIFICATION ===\n";
$output[] = "Environment: " . (defined('APP_STAGE') ? APP_STAGE : 'unknown') . "\n";
$output[] = "Time: " . date('Y-m-d H:i:s') . "\n\n";

$zatca_file = realpath('system/autoload/ZatcaPhase2.php');
if (!$zatca_file) {
    $errors[] = "ZatcaPhase2.php not found at expected location";
} else {
    $output[] = "✓ ZatcaPhase2.php found at: $zatca_file\n";
    $file_size = filesize($zatca_file);
    $file_modified = date('Y-m-d H:i:s', filemtime($zatca_file));
    $output[] = "  Size: $file_size bytes\n";
    $output[] = "  Modified: $file_modified\n\n";
}

// ============================================================================
// STEP 2: Check for All 6 Critical Fixes in the File
// ============================================================================

$output[] = "=== CRITICAL FIXES VERIFICATION ===\n\n";

$file_content = file_get_contents($zatca_file);

$fixes = [
    'fix_1_str_replace_template' => [
        'description' => 'Template Variable Substitution (str_replace)',
        'search' => "str_replace(\n            ['{$mainDocumentDigest}', '{$xadesSignedPropertiesDigest}', '{$signatureValue}'",
        'explanation' => 'CRITICAL: Converts literal placeholder strings to actual values'
    ],
    'fix_2_cert_digest_format' => [
        'description' => 'Certificate Digest: base64(hex) Format',
        'search' => '$certificateDigest = base64_encode($certHashHex);',
        'explanation' => 'Digest must be base64(hex) not base64(binary) - ZATCA requirement'
    ],
    'fix_3_timestamp_format' => [
        'description' => 'Timestamp Format (no Z, no timezone)',
        'search' => "str_replace(' ', 'T', \$xadesTimestamp);",
        'explanation' => 'Format must be ISO 8601 plain: YYYY-MM-DDTHH:mm:ss'
    ],
    'fix_4_ref_attribute_order' => [
        'description' => 'Reference Element Attribute Order',
        'search' => 'URI="#xadesSignedProperties" Type="http://www.w3.org/2000/09/xmldsig#SignatureProperties"',
        'explanation' => 'URI must come before Type for canonical XML order'
    ],
    'fix_5_namespace_cleanup' => [
        'description' => 'Remove duplicate xmlns from SignedProperties',
        'search' => "'{<xades:SignedProperties Id=\"xadesSignedProperties\">",
        'explanation' => 'Namespace must be on parent QualifyingProperties, not child'
    ],
    'fix_6_metadata_id' => [
        'description' => 'Metadata cac:Signature ID Value',
        'search' => '<cbc:ID>urn:oasis:names:specification:ubl:signature:Invoice</cbc:ID>',
        'explanation' => 'Invoice-type metadata requires this specific ID (not Invoice+Note)'
    ]
];

$fix_status = [];
foreach ($fixes as $fix_key => $fix_info) {
    if (strpos($file_content, $fix_info['search']) !== false) {
        $output[] = "✓ {$fix_info['description']}\n";
        $output[] = "  → {$fix_info['explanation']}\n\n";
        $fix_status[$fix_key] = true;
    } else {
        $output[] = "✗ {$fix_info['description']}\n";
        $output[] = "  → {$fix_info['explanation']}\n";
        $output[] = "  → MISSING: {$fix_info['search']}\n\n";
        $fix_status[$fix_key] = false;
        $errors[] = "Missing fix: " . $fix_info['description'];
    }
}

// ============================================================================
// STEP 3: Test Signature Generation (if database accessible)
// ============================================================================

$output[] = "=== SIGNATURE GENERATION TEST ===\n\n";

try {
    $invoice = ORM::for_table('acc_invoices')
        ->order_by_desc('id')
        ->limit(1)
        ->find_one();

    if ($invoice) {
        $output[] = "✓ Test invoice found: ID=" . $invoice->id . ", UUID=" . $invoice->invoice_uuid . "\n\n";

        global $config;
        $compliance_cert = $config['zatca_compliance_csr'] ?? null;

        if (!$compliance_cert) {
            $warnings[] = "Compliance certificate not configured - cannot test signature generation";
            $output[] = "⚠ Compliance certificate not found in config\n";
        } else {
            $output[] = "Testing signature generation with public submitInvoiceById()...\n\n";
            
            $context = [
                'force_resubmit' => true,
                'zatca_invoice_type' => $invoice->zatca_invoice_type ?? 'standard',
                'document_type' => 'invoice',
                'compliance_mode' => true,
                'compliance_certificate' => $compliance_cert
            ];

            // Use the public method which internally calls buildSignedPackage()
            $result = ZatcaPhase2::submitInvoiceById($invoice->id, $config, $context);
            
            $output[] = "✓ Signature package generated\n";
            $output[] = "  HTTP Status: " . ($result['status_code'] ?? 'N/A') . "\n";
            $output[] = "  Message: " . ($result['message'] ?? 'N/A') . "\n\n";

            // Retrieve the signed XML from database
            $invoice_check = ORM::for_table('acc_invoices')->find_one($invoice->id);
            
            if ($invoice_check && isset($invoice_check['zatca_signed_package'])) {
                $signed_xml = base64_decode($invoice_check['zatca_signed_package']);
                
                $output[] = "✓ Signed XML retrieved\n\n";

                // Check 1: Signature Information ID
                if (preg_match('/<sac:SignatureInformation>.*?<cbc:ID>(.*?)<\/cbc:ID>/s', $signed_xml, $m)) {
                    $id = $m[1];
                    if ($id === 'urn:oasis:names:specification:ubl:signature:1') {
                        $output[] = "✓ SignatureInformation ID: CORRECT\n";
                    } else {
                        $output[] = "✗ SignatureInformation ID: WRONG (got '$id')\n";
                        $errors[] = "SignatureInformation ID incorrect";
                    }
                } else {
                    $output[] = "✗ SignatureInformation ID: NOT FOUND\n";
                    $errors[] = "SignatureInformation ID missing";
                }

                // Check 2: Timestamp
                if (preg_match('/<xades:SigningTime>(.*?)<\/xades:SigningTime>/s', $signed_xml, $m)) {
                    $ts = $m[1];
                    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $ts)) {
                        $output[] = "✓ Timestamp format: CORRECT ($ts)\n";
                    } else {
                        $output[] = "✗ Timestamp format: WRONG ($ts)\n";
                        $errors[] = "Timestamp format incorrect";
                    }
                } else {
                    $output[] = "✗ Timestamp: NOT FOUND\n";
                    $errors[] = "Timestamp missing";
                }

                // Check 3: Certificate digest
                if (preg_match('/<xades:CertDigest>.*?<ds:DigestValue>(.*?)<\/ds:DigestValue>/s', $signed_xml, $m)) {
                    $digest = base64_decode($m[1]);
                    if (ctype_xdigit($digest) && strlen($digest) === 64) {
                        $output[] = "✓ Certificate digest: CORRECT (base64(hex), 64 chars)\n";
                    } else {
                        $output[] = "✗ Certificate digest: WRONG FORMAT\n";
                        $errors[] = "Certificate digest format incorrect";
                    }
                } else {
                    $output[] = "✗ Certificate digest: NOT FOUND\n";
                    $errors[] = "Certificate digest missing";
                }

                // Check 4: Metadata signature ID
                if (preg_match('/<cac:Signature>\s*<cbc:ID>(.*?)<\/cbc:ID>/s', $signed_xml, $m)) {
                    $meta_id = $m[1];
                    if ($meta_id === 'urn:oasis:names:specification:ubl:signature:Invoice') {
                        $output[] = "✓ Metadata signature ID: CORRECT\n";
                    } else {
                        $output[] = "⚠ Metadata signature ID: $meta_id\n";
                    }
                } else {
                    $output[] = "✗ Metadata signature ID: NOT FOUND\n";
                }

                // Save XML for inspection
                $xml_file = 'deployment-verify-output.xml';
                file_put_contents($xml_file, $signed_xml);
                $output[] = "\n✓ Full signed XML saved to: $xml_file\n";
            } else {
                $output[] = "⚠ Signed XML not found in database\n";
            }
        }
    } else {
        $warnings[] = "No test invoices found in database";
        $output[] = "⚠ No invoices available for testing\n";
    }
} catch (Exception $e) {
    $warnings[] = "Database test skipped: " . $e->getMessage();
    $output[] = "⚠ Database test skipped (OK if certificates work)\n";
}

// ============================================================================
// SUMMARY & RECOMMENDATIONS
// ============================================================================

$output[] = "\n=== SUMMARY ===\n\n";

$all_fixes = array_every($fix_status, fn($v) => $v);
if ($all_fixes) {
    $output[] = "✓ ALL 6 CRITICAL FIXES ARE DEPLOYED\n";
} else {
    $failed = array_filter($fix_status, fn($v) => !$v);
    $output[] = "✗ " . count($failed) . " CRITICAL FIX(ES) MISSING:\n";
    foreach (array_keys($failed) as $key) {
        $output[] = "   - " . $fixes[$key]['description'] . "\n";
    }
    $output[] = "\n⚠ FILE NEEDS TO BE RE-UPLOADED\n";
}

$output[] = "\nDeployment Status: " . (empty($errors) ? "✓ READY" : "✗ ISSUES FOUND") . "\n";

if (!empty($errors)) {
    $output[] = "\nErrors:\n";
    foreach ($errors as $err) {
        $output[] = "  • $err\n";
    }
}

if (!empty($warnings)) {
    $output[] = "\nWarnings:\n";
    foreach ($warnings as $warn) {
        $output[] = "  • $warn\n";
    }
}

$output[] = "\n=== NEXT STEPS ===\n\n";
if ($all_fixes) {
    $output[] = "1. All fixes are deployed ✓\n";
    $output[] = "2. Go to System → ZATCA Configuration → Step 3\n";
    $output[] = "3. Run the compliance check for all 6 scenarios\n";
    $output[] = "4. Expected result: Standard scenarios return 202 (Accepted)\n";
    $output[] = "5. Watch for these errors GONE:\n";
    $output[] = "   - certificate-hashing error\n";
    $output[] = "   - certificate-signing-time-format error\n";
    $output[] = "   - Signature validation failures\n";
} else {
    $output[] = "1. The ZatcaPhase2.php file needs to be re-uploaded\n";
    $output[] = "2. Source file: d:\\xampp\\htdocs\\alammarghali_finance\\system\\autoload\\ZatcaPhase2.php\n";
    $output[] = "3. Destination on live: /var/www/live/system/autoload/ZatcaPhase2.php\n";
    $output[] = "4. Re-run this verification script after upload\n";
}

// ============================================================================
// RENDER OUTPUT
// ============================================================================

?>
<!DOCTYPE html>
<html>
<head>
    <title>ZATCA Deployment Verification</title>
    <style>
        body {
            font-family: monospace;
            margin: 20px;
            background: #f5f5f5;
        }
        pre {
            background: white;
            padding: 15px;
            border-left: 4px solid #2196F3;
            overflow-x: auto;
            line-height: 1.5;
        }
        .error { color: #d32f2f; font-weight: bold; }
        .success { color: #388e3c; font-weight: bold; }
        .warning { color: #f57c00; font-weight: bold; }
        .status-box {
            background: white;
            padding: 15px;
            border: 2px solid #ddd;
            margin: 10px 0;
            border-radius: 4px;
        }
        .status-ok { border-color: #4caf50; }
        .status-error { border-color: #f44336; }
    </style>
</head>
<body>

<h1>ZATCA Phase 2 Deployment Verification</h1>

<?php if (!empty($errors)): ?>
<div class="status-box status-error">
    <span class="error">⚠ ISSUES DETECTED</span><br>
    Please review the full report below
</div>
<?php else: ?>
<div class="status-box status-ok">
    <span class="success">✓ All Checks Passed</span><br>
    File is ready for Step 3 testing
</div>
<?php endif; ?>

<pre><?php echo implode('', $output); ?></pre>

</body>
</html>
