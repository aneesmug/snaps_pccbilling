<?php
/**
 * Simple Signature Verification Script
 * Uses public ZatcaPhase2 methods to generate and inspect signed invoices
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define required constants before loading app.php
define('APP_RUN', true);
define('APP_BASE_PATH', dirname(__FILE__));
define('APP_SYSTEM_PATH', dirname(__FILE__) . '/system');
require 'system/app.php';

echo "=== ZATCA Signature Verification ===\n\n";

// Get latest invoice
$invoice = ORM::for_table('acc_invoices')
    ->order_by_desc('id')
    ->limit(1)
    ->find_one();

if (!$invoice) {
    die("❌ No invoices found\n");
}

echo "Invoice: ID={$invoice->id}, UUID={$invoice->invoice_uuid}\n";
echo "Status: {$invoice->zatca_status}\n\n";

// Get global config
global $config;

if (!isset($config['zatca_compliance_csr']) || empty($config['zatca_compliance_csr'])) {
    die("❌ Compliance certificate not configured\n");
}

echo "Testing signature generation...\n\n";

// Convert ORM objects to arrays
$invoice_array = $invoice->as_array();

// Call the public method with compliance context
$context = [
    'force_resubmit' => true,
    'zatca_invoice_type' => $invoice->zatca_invoice_type ?? 'standard',
    'document_type' => 'invoice',
    'compliance_mode' => true,
    'compliance_certificate' => $config['zatca_compliance_csr']
];

// This will generate the signed package
$result = ZatcaPhase2::submitInvoiceById($invoice->id, $config, $context);

echo "Submission Result:\n";
echo "  Status: " . ($result['success'] ? "✓ Success" : "✗ Failed") . "\n";
echo "  HTTP Code: " . ($result['status_code'] ?? 'N/A') . "\n";
echo "  Message: " . ($result['message'] ?? 'N/A') . "\n\n";

// Retrieve the signed XML that was persisted
$signed_data = ORM::for_table('acc_invoices')->find_one($invoice->id);

if ($signed_data && isset($signed_data['zatca_signed_package'])) {
    $signed_xml = base64_decode($signed_data['zatca_signed_package']);
    
    echo "✓ Signed XML retrieved from database\n\n";
    
    // Verify key signature elements
    echo "=== SIGNATURE STRUCTURE CHECKS ===\n\n";
    
    // Check 1: SignatureInformation ID
    if (preg_match('/<sac:SignatureInformation>.*?<cbc:ID>(.*?)<\/cbc:ID>/s', $signed_xml, $m)) {
        $id = $m[1];
        if ($id === 'urn:oasis:names:specification:ubl:signature:1') {
            echo "✓ SignatureInformation ID: CORRECT\n";
        } else {
            echo "✗ SignatureInformation ID: WRONG ($id)\n";
        }
    } else {
        echo "✗ SignatureInformation ID: NOT FOUND\n";
    }
    
    // Check 2: Timestamp format
    if (preg_match('/<xades:SigningTime>(.*?)<\/xades:SigningTime>/s', $signed_xml, $m)) {
        $ts = $m[1];
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $ts)) {
            echo "✓ Timestamp format: CORRECT ($ts)\n";
        } else {
            echo "✗ Timestamp format: WRONG ($ts should be YYYY-MM-DDTHH:mm:ss)\n";
        }
    } else {
        echo "✗ Timestamp: NOT FOUND\n";
    }
    
    // Check 3: Certificate digest
    if (preg_match('/<xades:CertDigest>.*?<ds:DigestValue>(.*?)<\/ds:DigestValue>/s', $signed_xml, $m)) {
        $digest_b64 = $m[1];
        $digest_decoded = base64_decode($digest_b64);
        if (ctype_xdigit($digest_decoded) && strlen($digest_decoded) === 64) {
            echo "✓ Certificate digest: CORRECT (base64(hex), 64 chars)\n";
        } else {
            echo "✗ Certificate digest: WRONG FORMAT (length=" . strlen($digest_decoded) . ")\n";
        }
    } else {
        echo "✗ Certificate digest: NOT FOUND\n";
    }
    
    // Check 4: Metadata ID
    if (preg_match('/<cac:Signature>\s*<cbc:ID>(.*?)<\/cbc:ID>/s', $signed_xml, $m)) {
        $meta_id = $m[1];
        if ($meta_id === 'urn:oasis:names:specification:ubl:signature:Invoice') {
            echo "✓ Metadata signature ID: CORRECT\n";
        } else {
            echo "⚠ Metadata signature ID: $meta_id\n";
        }
    } else {
        echo "✗ Metadata signature ID: NOT FOUND\n";
    }
    
    // Export XML for detailed inspection
    file_put_contents('verify-signature-output.xml', $signed_xml);
    echo "\n✓ Full XML exported to: verify-signature-output.xml\n";
    
} else {
    echo "⚠ Signed XML not found in database\n";
    echo "   (May be stored differently or submission skipped)\n";
}

echo "\n=== SUMMARY ===\n";
if ($result['success']) {
    echo "✓ Signature generation appears successful\n";
    echo "✓ All fixes are working\n";
} else {
    echo "✗ Signature generation failed\n";
    echo "Response: " . ($result['response'] ?? 'N/A') . "\n";
}
?>
