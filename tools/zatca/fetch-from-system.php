<?php

declare(strict_types=1);

/**
 * Fetch ZATCA CSIDs using the same config stored in current SnapS settings,
 * then save output to JSON and optionally write keys back to sys_appconfig.
 *
 * Usage:
 *   php tools/zatca/fetch-from-system.php
 *   php tools/zatca/fetch-from-system.php --out="tools/zatca/system-zatca-sync.json"
 *   php tools/zatca/fetch-from-system.php --otp="123456" --save=1
 */

$root = dirname(__DIR__, 2);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Composer autoload not found: {$autoload}" . PHP_EOL);
    exit(1);
}

require_once $autoload;
require_once __DIR__ . '/SystemZatcaConfig.php';
require_once __DIR__ . '/ZatcaCsidAutoFetcher.php';

$options = getopt('', [
    'otp::',
    'csr::',
    'vat::',
    'api-base::',
    'out::',
    'save::',
    'help::',
]);

if (isset($options['help'])) {
    printHelp();
    exit(0);
}

$outFile = isset($options['out']) ? (string) $options['out'] : 'system-zatca-sync.json';
$saveToSystem = !isset($options['save']) || (string) $options['save'] === '1';

$system = new SystemZatcaConfig($root);
$cfg = $system->getSystemConfig();

if (empty($cfg['booted'])) {
    fwrite(STDERR, 'Failed to load system config: ' . (string) ($cfg['error'] ?? 'unknown') . PHP_EOL);
    exit(2);
}

$otp = isset($options['otp']) && trim((string) $options['otp']) !== ''
    ? trim((string) $options['otp'])
    : (string) ($cfg['zatca_otp'] ?? '');

$csr = isset($options['csr']) && trim((string) $options['csr']) !== ''
    ? trim((string) $options['csr'])
    : (string) ($cfg['zatca_csr_content'] ?? '');

$vat = isset($options['vat']) && trim((string) $options['vat']) !== ''
    ? preg_replace('/\D+/', '', (string) $options['vat'])
    : (string) ($cfg['zatca_vat_number'] ?? '');

$apiBase = isset($options['api-base']) && trim((string) $options['api-base']) !== ''
    ? trim((string) $options['api-base'])
    : (string) ($cfg['zatca_api_base_resolved'] ?? '');

if ($otp === '') {
    fwrite(STDERR, "OTP is empty. Set it in settings page or pass --otp=VALUE" . PHP_EOL);
    exit(3);
}

if ($csr === '') {
    fwrite(STDERR, "CSR is empty. Set it in settings page or pass --csr=VALUE" . PHP_EOL);
    exit(4);
}

$fetcher = new ZatcaCsidAutoFetcher($apiBase);
$result = $fetcher->fetchAll($otp, $csr, $vat);

$saveStatus = [
    'saved' => false,
    'message' => 'Skipped saving to system config.',
];

if ($saveToSystem && !empty($result['success'])) {
    $saveStatus = $system->saveFetchedKeys($result);
}

$payload = [
    'timestamp' => date('c'),
    'source' => [
        'from_system_settings' => true,
        'api_base' => $apiBase,
        'environment' => (string) ($cfg['zatca_environment'] ?? ''),
        'vat_number' => $vat,
        'otp_masked' => maskValue($otp),
        'csr_present' => $csr !== '',
        'csr_length' => strlen($csr),
    ],
    'system_snapshot' => [
        'zatca_enabled' => (string) ($cfg['zatca_enabled'] ?? ''),
        'zatca_phase2_enabled' => (string) ($cfg['zatca_phase2_enabled'] ?? ''),
        'has_existing_compliance_token' => ((string) ($cfg['zatca_binary_security_token'] ?? '')) !== '',
        'has_existing_production_token' => ((string) ($cfg['zatca_production_binary_security_token'] ?? '')) !== '',
    ],
    'fetch_result' => $result,
    'save_status' => $saveStatus,
];

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json !== false) {
    file_put_contents($outFile, $json);
}

printResult($result, $apiBase, $vat, $outFile, $saveStatus);
exit(!empty($result['success']) ? 0 : 5);

function maskValue(string $s): string
{
    $s = trim($s);
    if ($s === '') {
        return '';
    }
    if (strlen($s) <= 2) {
        return str_repeat('*', strlen($s));
    }
    return substr($s, 0, 1) . str_repeat('*', strlen($s) - 2) . substr($s, -1);
}

function printHelp(): void
{
    $lines = [
        'ZATCA Fetch From System Settings',
        '',
        'Reads config from current app settings and runs Compliance + Production flow.',
        '',
        'Options:',
        '  --otp=VALUE         Override OTP from system settings',
        '  --csr=VALUE         Override CSR content/path from system settings',
        '  --vat=VALUE         Override VAT number from system settings',
        '  --api-base=URL      Override resolved API base',
        '  --out=FILE          JSON output file (default: system-zatca-sync.json)',
        '  --save=1|0          Save fetched keys back to system settings (default: 1)',
        '  --help              Show help',
    ];

    fwrite(STDOUT, implode(PHP_EOL, $lines) . PHP_EOL);
}

function printResult(array $result, string $apiBase, string $vat, string $outFile, array $saveStatus): void
{
    fwrite(STDOUT, PHP_EOL . '=== ZATCA Fetch Using System Config ===' . PHP_EOL);
    fwrite(STDOUT, 'Success: ' . (!empty($result['success']) ? 'YES' : 'NO') . PHP_EOL);
    fwrite(STDOUT, 'Stage:   ' . (string) ($result['stage'] ?? 'unknown') . PHP_EOL);
    fwrite(STDOUT, 'API:     ' . $apiBase . PHP_EOL);
    fwrite(STDOUT, 'VAT:     ' . $vat . PHP_EOL . PHP_EOL);

    foreach (['compliance' => 'Compliance', 'compliance_invoices' => 'Compliance Invoices', 'production' => 'Production'] as $k => $label) {
        if (!isset($result[$k]) || !is_array($result[$k])) {
            continue;
        }
        $stage = $result[$k];
        fwrite(STDOUT, '[' . $label . ']' . PHP_EOL);
        fwrite(STDOUT, 'Status:  ' . (string) ($stage['status_code'] ?? 0) . PHP_EOL);
        fwrite(STDOUT, 'Message: ' . (string) ($stage['message'] ?? '') . PHP_EOL);

        $body = trim((string) ($stage['response_body'] ?? ''));
        if ($body !== '') {
            $decoded = json_decode($body, true);
            $pretty = is_array($decoded)
                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $body;
            fwrite(STDOUT, 'ZATCA Response:' . PHP_EOL . $pretty . PHP_EOL);
        }

        fwrite(STDOUT, PHP_EOL);
    }

    if (!empty($result['success']) && isset($result['keys']) && is_array($result['keys'])) {
        fwrite(STDOUT, 'Fetched Keys:' . PHP_EOL);
        foreach ($result['keys'] as $key => $value) {
            fwrite(STDOUT, $key . '=' . (string) $value . PHP_EOL);
        }
        fwrite(STDOUT, PHP_EOL);
    }

    fwrite(STDOUT, 'Saved to system settings: ' . (!empty($saveStatus['saved']) ? 'YES' : 'NO') . PHP_EOL);
    fwrite(STDOUT, 'Save status: ' . (string) ($saveStatus['message'] ?? '') . PHP_EOL);
    fwrite(STDOUT, 'JSON output: ' . $outFile . PHP_EOL);
}
