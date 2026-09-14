<?php

declare(strict_types=1);

/**
 * Read/write ZATCA settings directly from sys_appconfig.
 * Uses DB credentials from system/config.php.
 */
class SystemZatcaConfig
{
    /** @var string */
    private $rootPath;

    /** @var PDO|null */
    private $pdo;

    /** @var string */
    private $lastError = '';

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, DIRECTORY_SEPARATOR);
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    private function connect(): bool
    {
        if ($this->pdo instanceof PDO) {
            return true;
        }

        $configFile = $this->rootPath . '/system/config.php';
        if (!is_file($configFile)) {
            $this->lastError = 'system/config.php not found.';
            return false;
        }

        require $configFile;

        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_NAME')) {
            $this->lastError = 'Database constants are missing in system/config.php.';
            return false;
        }

        $dbHost = (string) DB_HOST;
        $dbPort = defined('DB_PORT') ? trim((string) DB_PORT) : '';
        $dbName = (string) DB_NAME;
        $dbUser = (string) DB_USER;
        $dbPass = defined('DB_PASSWORD') ? (string) DB_PASSWORD : '';

        $dsn = 'mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8mb4';
        if ($dbPort !== '') {
            $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';
        }

        try {
            $this->pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return true;
        } catch (Throwable $e) {
            $this->lastError = 'DB connection failed: ' . $e->getMessage();
            return false;
        }
    }

    public function getSystemConfig(): array
    {
        if (!$this->connect()) {
            return [
                'booted' => false,
                'error' => $this->lastError,
            ];
        }

        $keys = [
            'zatca_enabled',
            'zatca_phase2_enabled',
            'zatca_environment',
            'zatca_setup_mode',
            'zatca_api_base_url',
            'zatca_api_base',
            'zatca_invoice_type',
            'zatca_seller_name',
            'zatca_vat_number',
            'zatca_seller_crn',
            'zatca_building_no',
            'zatca_street_name',
            'zatca_district',
            'zatca_city',
            'zatca_postal_code',
            'zatca_country_code',
            'zatca_otp',
            'zatca_csr_content',
            'zatca_csr',
            'zatca_private_key_passphrase',
            'zatca_private_key',
            'zatca_binary_security_token',
            'zatca_secret',
            'zatca_compliance_request_id',
            'zatca_production_binary_security_token',
            'zatca_production_secret',
            'zatca_production_request_id',
        ];

        $in = implode(',', array_fill(0, count($keys), '?'));
        $sql = 'SELECT setting, value FROM sys_appconfig WHERE setting IN (' . $in . ')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($keys);

        $vals = [];
        foreach ($keys as $k) {
            $vals[$k] = '';
        }

        while ($row = $stmt->fetch()) {
            $k = (string) ($row['setting'] ?? '');
            $v = (string) ($row['value'] ?? '');
            if ($k !== '') {
                $vals[$k] = trim($v);
            }
        }

        $environment = strtolower(trim((string) $vals['zatca_environment']));
        if (!in_array($environment, ['sandbox', 'simulation', 'production'], true)) {
            $environment = 'sandbox';
        }

        $apiBaseFallback = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal';
        if ($environment === 'simulation') {
            $apiBaseFallback = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation';
        } elseif ($environment === 'production') {
            $apiBaseFallback = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core';
        }

        $apiBaseCandidate = trim((string) $vals['zatca_api_base_url']);
        if ($apiBaseCandidate === '') {
            $apiBaseCandidate = trim((string) $vals['zatca_api_base']);
        }
        if ($apiBaseCandidate === '') {
            $apiBaseCandidate = $apiBaseFallback;
        }

        $parsedApiBase = @parse_url($apiBaseCandidate);
        $apiPath = '';
        if (is_array($parsedApiBase) && isset($parsedApiBase['path'])) {
            $apiPath = trim((string) $parsedApiBase['path']);
        }
        if ($apiPath === '' || $apiPath === '/') {
            $apiBaseCandidate = $apiBaseFallback;
        } else {
            $apiBaseCandidate = $this->normalizeApiBaseForEnvironment($apiBaseCandidate, $environment, $apiBaseFallback);
        }

        $resolvedApiBase = rtrim($apiBaseCandidate, '/') . '/';

        $csr = trim((string) $vals['zatca_csr_content']);
        if ($csr === '') {
            $csr = trim((string) $vals['zatca_csr']);
        }

        return [
            'booted' => true,
            'zatca_enabled' => (string) $vals['zatca_enabled'],
            'zatca_phase2_enabled' => (string) $vals['zatca_phase2_enabled'],
            'zatca_environment' => $environment,
            'zatca_setup_mode' => (string) $vals['zatca_setup_mode'],
            'zatca_api_base_resolved' => $resolvedApiBase,
            'zatca_api_base_url' => (string) $vals['zatca_api_base_url'],
            'zatca_api_base' => (string) $vals['zatca_api_base'],
            'zatca_invoice_type' => (string) $vals['zatca_invoice_type'],
            'zatca_seller_name' => (string) $vals['zatca_seller_name'],
            'zatca_vat_number' => preg_replace('/\D+/', '', (string) $vals['zatca_vat_number']),
            'zatca_seller_crn' => preg_replace('/\D+/', '', (string) $vals['zatca_seller_crn']),
            'zatca_building_no' => (string) $vals['zatca_building_no'],
            'zatca_street_name' => (string) $vals['zatca_street_name'],
            'zatca_district' => (string) $vals['zatca_district'],
            'zatca_city' => (string) $vals['zatca_city'],
            'zatca_postal_code' => (string) $vals['zatca_postal_code'],
            'zatca_country_code' => (string) $vals['zatca_country_code'],
            'zatca_otp' => (string) $vals['zatca_otp'],
            'zatca_csr_content' => $csr,
            'zatca_private_key_passphrase' => (string) $vals['zatca_private_key_passphrase'],
            'zatca_private_key' => (string) $vals['zatca_private_key'],
            'zatca_binary_security_token' => (string) $vals['zatca_binary_security_token'],
            'zatca_secret' => (string) $vals['zatca_secret'],
            'zatca_compliance_request_id' => (string) $vals['zatca_compliance_request_id'],
            'zatca_production_binary_security_token' => (string) $vals['zatca_production_binary_security_token'],
            'zatca_production_secret' => (string) $vals['zatca_production_secret'],
            'zatca_production_request_id' => (string) $vals['zatca_production_request_id'],
        ];
    }

    private function normalizeApiBaseForEnvironment(string $apiBase, string $environment, string $defaultBase): string
    {
        $apiBase = trim($apiBase);
        $defaultBase = trim($defaultBase);

        if ($apiBase === '') {
            return $defaultBase;
        }

        $parsed = @parse_url($apiBase);
        $path = is_array($parsed) && isset($parsed['path']) ? strtolower(trim((string) $parsed['path'])) : '';

        if ($path === '') {
            return $defaultBase;
        }

        $isSandboxLike = strpos($path, '/e-invoicing/developer-portal') !== false
            || strpos($path, '/e-invoicing/simulation') !== false;
        $isProductionLike = strpos($path, '/e-invoicing/core') !== false;

        if ($environment === 'production' && $isSandboxLike) {
            return $defaultBase;
        }

        if (($environment === 'sandbox' || $environment === 'simulation') && $isProductionLike) {
            return $defaultBase;
        }

        return $apiBase;
    }

    public function saveFetchedKeys(array $result): array
    {
        if (!$this->connect()) {
            return [
                'saved' => false,
                'message' => $this->lastError,
            ];
        }

        if (empty($result['keys']) || !is_array($result['keys'])) {
            return [
                'saved' => false,
                'message' => 'No keys returned to save.',
            ];
        }

        $updates = [
            'zatca_binary_security_token' => (string) ($result['keys']['zatca_binary_security_token'] ?? ''),
            'zatca_secret' => (string) ($result['keys']['zatca_secret'] ?? ''),
            'zatca_compliance_request_id' => (string) ($result['keys']['zatca_compliance_request_id'] ?? ''),
            'zatca_compliance_csid' => (string) ($result['keys']['zatca_binary_security_token'] ?? ''),
            'zatca_compliance_secret' => (string) ($result['keys']['zatca_secret'] ?? ''),
            'zatca_production_binary_security_token' => (string) ($result['keys']['zatca_production_binary_security_token'] ?? ''),
            'zatca_production_secret' => (string) ($result['keys']['zatca_production_secret'] ?? ''),
            'zatca_production_request_id' => (string) ($result['keys']['zatca_production_request_id'] ?? ''),
            'zatca_production_csid' => (string) ($result['keys']['zatca_production_binary_security_token'] ?? ''),
        ];

        if (isset($result['compliance']['response_body'])) {
            $updates['zatca_compliance_last_response'] = (string) $result['compliance']['response_body'];
        }
        if (isset($result['compliance_invoices']['response_body'])) {
            $updates['zatca_compliance_invoices_last_response'] = (string) $result['compliance_invoices']['response_body'];
        }
        if (isset($result['production']['response_body'])) {
            $updates['zatca_production_last_response'] = (string) $result['production']['response_body'];
        }

        foreach ($updates as $k => $v) {
            if ($v === '') {
                continue;
            }

            $exists = $this->pdo->prepare('SELECT setting FROM sys_appconfig WHERE setting = ? LIMIT 1');
            $exists->execute([$k]);
            $row = $exists->fetch();

            if ($row) {
                $upd = $this->pdo->prepare('UPDATE sys_appconfig SET value = ? WHERE setting = ?');
                $upd->execute([$v, $k]);
            } else {
                $ins = $this->pdo->prepare('INSERT INTO sys_appconfig (`setting`, `value`) VALUES (?, ?)');
                $ins->execute([$k, $v]);
            }
        }

        return [
            'saved' => true,
            'message' => 'ZATCA keys saved to sys_appconfig.',
        ];
    }
}
