<?php

declare(strict_types=1);

/**
 * CLI: Auto fetch Compliance + Production CSIDs using CSR + OTP.
 *
 * Usage:
 *   php tools/zatca/fetch-csids.php --otp="123456" --csr="C:\\path\\to\\csr.pem"
 *   php tools/zatca/fetch-csids.php --otp="123456" --csr="...raw or base64 CSR..." --api-base="https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal"
 */

$root = dirname(__DIR__, 2);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Composer autoload not found: {$autoload}" . PHP_EOL);
    exit(1);
}

require_once $autoload;
require_once __DIR__ . '/ZatcaCsidAutoFetcher.php';

$options = getopt('', [
    'otp:',
    'csr:',
    'api-base::',
    'out::',
    'help::',
]);

if (isset($options['help']) || !isset($options['otp']) || !isset($options['csr'])) {
    printHelp();
    exit(isset($options['help']) ? 0 : 1);
}

$otp = (string) $options['otp'];
$csr = (string) $options['csr'];
$apiBase = isset($options['api-base']) ? (string) $options['api-base'] : '';
$out = isset($options['out']) ? (string) $options['out'] : '';

$fetcher = new ZatcaCsidAutoFetcher($apiBase);
$result = $fetcher->fetchAll($otp, $csr);

if ($out !== '') {
    $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json !== false) {
        file_put_contents($out, $json);
    }
}

printHumanReadableResult($result);

if (!empty($result['success'])) {
    exit(0);
}

exit(2);

function printHelp(): void
{
    $help = [
        'ZATCA CSID Auto Fetch Utility',
        '',
        'Required arguments:',
        '  --otp=VALUE                 Compliance OTP from ZATCA portal',
        '  --csr=VALUE                 CSR content or CSR file path',
        '',
        'Optional arguments:',
        '  --api-base=URL              Base URL (default: developer-portal)',
        '  --out=FILE                  Save full JSON response to file',
        '  --help                      Show this help',
        '',
        'Example:',
        '  php tools/zatca/fetch-csids.php --otp="123456" --csr="C:\\certs\\my-zatca.csr" --out="tools/zatca/latest-csids.json"',
    ];

    fwrite(STDOUT, implode(PHP_EOL, $help) . PHP_EOL);
}

/**
 * @param array<string,mixed> $result
 */
function printHumanReadableResult(array $result): void
{
    fwrite(STDOUT, PHP_EOL . '=== ZATCA CSID Auto Fetch Result ===' . PHP_EOL);
    fwrite(STDOUT, 'Success: ' . (!empty($result['success']) ? 'YES' : 'NO') . PHP_EOL);
    fwrite(STDOUT, 'Stage:   ' . (string) ($result['stage'] ?? 'unknown') . PHP_EOL);
    fwrite(STDOUT, 'API:     ' . (string) ($result['api_base'] ?? '') . PHP_EOL . PHP_EOL);

    if (isset($result['compliance']) && is_array($result['compliance'])) {
        fwrite(STDOUT, '[Compliance]' . PHP_EOL);
        fwrite(STDOUT, 'Status:  ' . (string) ($result['compliance']['status_code'] ?? 0) . PHP_EOL);
        fwrite(STDOUT, 'Message: ' . (string) ($result['compliance']['message'] ?? '') . PHP_EOL);
        $complianceBody = trim((string) ($result['compliance']['response_body'] ?? ''));
        if ($complianceBody !== '') {
            $decoded = json_decode($complianceBody, true);
            $prettyBody = is_array($decoded)
                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $complianceBody;
            fwrite(STDOUT, 'ZATCA Response:' . PHP_EOL . $prettyBody . PHP_EOL);
        }
        fwrite(STDOUT, PHP_EOL);
    }

    if (isset($result['production']) && is_array($result['production'])) {
        fwrite(STDOUT, '[Production]' . PHP_EOL);
        fwrite(STDOUT, 'Status:  ' . (string) ($result['production']['status_code'] ?? 0) . PHP_EOL);
        fwrite(STDOUT, 'Message: ' . (string) ($result['production']['message'] ?? '') . PHP_EOL);
        $productionBody = trim((string) ($result['production']['response_body'] ?? ''));
        if ($productionBody !== '') {
            $decoded = json_decode($productionBody, true);
            $prettyBody = is_array($decoded)
                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $productionBody;
            fwrite(STDOUT, 'ZATCA Response:' . PHP_EOL . $prettyBody . PHP_EOL);
        }
        fwrite(STDOUT, PHP_EOL);
    }

    if (!empty($result['success']) && isset($result['keys']) && is_array($result['keys'])) {
        fwrite(STDOUT, 'Use these keys in your app settings:' . PHP_EOL);
        foreach ($result['keys'] as $k => $v) {
            fwrite(STDOUT, $k . '=' . (string) $v . PHP_EOL);
        }
        fwrite(STDOUT, PHP_EOL);
    }
}
