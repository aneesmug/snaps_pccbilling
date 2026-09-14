<?php
/**
 * Debug script to inspect the actual generated signature XML
 * from a compliance invoice to verify all fixes are applied
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define required constants before loading app.php
define('APP_RUN', true);
define('APP_BASE_PATH', dirname(__FILE__));
define('APP_SYSTEM_PATH', dirname(__FILE__) . '/system');
require 'system/app.php';

// Get latest invoice
$invoice = ORM::for_table('acc_invoices')
    ->order_by_desc('id')
    ->limit(1)
    ->find_one();

if (!$invoice) {
    die("No invoices found\n");
}

echo "=== ZATCA Phase 2 Signature Debug Tool ===\n";
echo "Invoice ID: " . $invoice->id . "\n";
echo "Invoice UUID: " . $invoice->invoice_uuid . "\n\n";

// Get configuration (already loaded globally from sys_appconfig in bootstrap)
global $config;

// Convert ORM object to array for buildSignedPackage
$invoice_data = $invoice->as_array();

// Get items
$items = ORM::for_table('sys_invoiceitems')
    ->where('invoiceid', $invoice->id)
    ->order_by_asc('id')
    ->find_array();

$customer_obj = ORM::for_table('crm_accounts')
    ->where('id', $invoice->userid)
    ->find_one();

$customer_data = $customer_obj ? $customer_obj->as_array() : [];

// Get compliance certificate
$compliance_cert = $config['zatca_compliance_csr'] ?? null;

if (!$compliance_cert) {
    die("Compliance certificate not configured\n");
}

// Build signed package with compliance mode
$zatca = new ZatcaPhase2();

$context = [
    'force_resubmit' => true,
    'zatca_invoice_type' => 'standard',
    'document_type' => 'invoice',
    'compliance_mode' => true,
    'compliance_certificate' => $compliance_cert
];

$package = $zatca->buildSignedPackage(
    $invoice_data,
    $items,
    $customer_data,
    $config,
    $config['vat_number'] ?? '310235189600003',
    $context
);

if (!$package['success']) {
    die("Package build failed: " . $package['message'] . "\n");
}

// Decode the invoice XML from base64
$invoiceXml = base64_decode($package['invoice_b64']);

// Parse and extract key elements
echo "=== SIGNATURE STRUCTURE VERIFICATION ===\n\n";

// Check 1: Signature Information ID
if (preg_match('/<sac:SignatureInformation>.*?<cbc:ID>(.*?)<\/cbc:ID>/s', $invoiceXml, $matches)) {
    echo "✓ SignatureInformation ID found: " . htmlspecialchars($matches[1]) . "\n";
    if ($matches[1] === 'urn:oasis:names:specification:ubl:signature:1') {
        echo "  ✓ CORRECT - matches required value\n";
    } else {
        echo "  ✗ INCORRECT - should be 'urn:oasis:names:specification:ubl:signature:1'\n";
    }
} else {
    echo "✗ SignatureInformation ID NOT FOUND\n";
}

// Check 2: Timestamp format
if (preg_match('/<xades:SigningTime>(.*?)<\/xades:SigningTime>/s', $invoiceXml, $matches)) {
    $timestamp = $matches[1];
    echo "\n✓ Timestamp found: " . htmlspecialchars($timestamp) . "\n";
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $timestamp)) {
        echo "  ✓ CORRECT format (ISO 8601, no Z, no timezone)\n";
    } else {
        echo "  ✗ INCORRECT format (should be YYYY-MM-DDTHH:mm:ss)\n";
    }
} else {
    echo "\n✗ Timestamp NOT FOUND\n";
}

// Check 3: Certificate digest format
if (preg_match('/<xades:CertDigest>.*?<ds:DigestValue>(.*?)<\/ds:DigestValue>.*?<\/xades:CertDigest>/s', $invoiceXml, $matches)) {
    $certDigest = $matches[1];
    echo "\n✓ Certificate digest found: " . substr($certDigest, 0, 20) . "...\n";
    $decoded = base64_decode($certDigest);
    if (ctype_xdigit($decoded)) {
        echo "  ✓ CORRECT format (base64(hex), decoded to hex string)\n";
        echo "    Length: " . strlen($decoded) . " chars (64 = SHA256)\n";
    } else {
        echo "  ✗ INCORRECT format (appears to be base64(binary))\n";
        echo "    First bytes: " . bin2hex(substr($decoded, 0, 5)) . "...\n";
    }
} else {
    echo "\n✗ Certificate digest NOT FOUND\n";
}

// Check 4: Metadata cac:Signature ID
if (preg_match('/<cac:Signature>\s*<cbc:ID>(.*?)<\/cbc:ID>/s', $invoiceXml, $matches)) {
    $metadataId = $matches[1];
    echo "\n✓ Metadata signature ID found: " . htmlspecialchars($metadataId) . "\n";
    if ($metadataId === 'urn:oasis:names:specification:ubl:signature:Invoice') {
        echo "  ✓ CORRECT (Invoice variant)\n";
    } else {
        echo "  ⚠ Different value: " . htmlspecialchars($metadataId) . "\n";
    }
} else {
    echo "\n✗ Metadata signature ID NOT FOUND\n";
}

// Export full XML for inspection
$xmlFile = 'debug-signature-output.xml';
file_put_contents($xmlFile, $invoiceXml);
echo "\n✓ Full XML exported to: {$xmlFile}\n";

echo "\n=== SUMMARY ===\n";
echo "Hash: " . substr($package['invoice_hash'], 0, 32) . "...\n";
echo "UUID: " . $package['uuid'] . "\n";
echo "\nAll fixes should be reflected in the XML above.\n";
echo "If errors remain, the code may not have been updated or deployed.\n";
?>
