<?php
declare(strict_types=1);

/**
 * ZATCA CSID Web Fetch Tool
 * Paste your OTP and CSR from the ZATCA portal and get your Compliance + Production IDs.
 *
 * Access at: http://localhost/snaps_billing_finance/tools/zatca/
 */

$root     = dirname(__DIR__, 2);
$autoload = $root . '/vendor/autoload.php';

$result  = null;
$error   = '';
$success = false;
$saveStatus = null;
$jsonOutputPath = '';
$companyPatchJson = '';
$companyPatchPhp = '';
$companySettingsOutPath = '';
$fetchNotice = '';
$csrPrecheck = null;

$systemDefaults = [
    'zatca_setup_mode' => 'csr_otp',
    'zatca_otp' => '',
    'zatca_vat_number' => '',
    'zatca_csr_content' => '',
    'zatca_api_base_resolved' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal',
    'zatca_invoice_type' => 'simplified',
    'zatca_seller_name' => '',
    'zatca_seller_crn' => '',
    'zatca_egs_serial' => '',
    'zatca_building_no' => '',
    'zatca_street_name' => '',
    'zatca_district' => '',
    'zatca_city' => '',
    'zatca_postal_code' => '',
    'zatca_country_code' => 'SA',
    'zatca_private_key_passphrase' => '',
    'zatca_private_key' => '',
    'zatca_binary_security_token' => '',
    'zatca_secret' => '',
    'zatca_compliance_request_id' => '',
    'zatca_compliance_csid' => '',
    'zatca_compliance_secret' => '',
    'zatca_production_binary_security_token' => '',
    'zatca_production_secret' => '',
    'zatca_production_request_id' => '',
    'zatca_production_csid' => '',
];

$zatcaConfigFilePath = __DIR__ . '/zatca-config.json';
if (is_file($zatcaConfigFilePath)) {
    $loadedJsonCfg = json_decode((string)file_get_contents($zatcaConfigFilePath), true);
    if (is_array($loadedJsonCfg) && !empty($loadedJsonCfg)) {
        $systemDefaults = array_merge($systemDefaults, $loadedJsonCfg);
    }
}

$activeSetupMode = (string)($_POST['setup_mode'] ?? ($systemDefaults['zatca_setup_mode'] ?? 'csr_otp'));
if (!in_array($activeSetupMode, ['csr_otp', 'portal_keys'], true)) {
    $activeSetupMode = 'csr_otp';
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!file_exists($autoload)) {
        $error = 'Composer autoload not found. Run `composer install` from the project root.';
    } else {
        require_once $autoload;
        require_once __DIR__ . '/ZatcaCsidAutoFetcher.php';

        $actionMode = trim((string)($_POST['action_mode'] ?? 'manual_fetch'));
        $setupMode = trim((string)($_POST['setup_mode'] ?? ($systemDefaults['zatca_setup_mode'] ?? 'csr_otp')));
        if (!in_array($setupMode, ['csr_otp', 'portal_keys'], true)) {
            $setupMode = 'csr_otp';
        }
        $otp     = trim((string)($_POST['otp'] ?? ''));
        $csr     = trim((string)($_POST['csr'] ?? ''));
        $apiBase = trim((string)($_POST['api_base'] ?? ''));
        $vat     = trim((string)($_POST['vat'] ?? ''));
        $invoiceType = strtolower(trim((string)($_POST['invoice_type'] ?? ($systemDefaults['zatca_invoice_type'] ?? 'simplified'))));
        if (!in_array($invoiceType, ['simplified', 'standard', 'both'], true)) {
            $invoiceType = 'simplified';
        }
        $sellerName = trim((string)($_POST['seller_name'] ?? ($systemDefaults['zatca_seller_name'] ?? '')));
        $crn = preg_replace('/\D+/', '', (string)($_POST['seller_crn'] ?? ($systemDefaults['zatca_seller_crn'] ?? '')));
        $egsSerial = trim((string)($_POST['egs_serial'] ?? ($systemDefaults['zatca_egs_serial'] ?? '')));
        $buildingNo = trim((string)($_POST['building_no'] ?? ($systemDefaults['zatca_building_no'] ?? '')));
        $streetName = trim((string)($_POST['street_name'] ?? ($systemDefaults['zatca_street_name'] ?? '')));
        $district = trim((string)($_POST['district'] ?? ($systemDefaults['zatca_district'] ?? '')));
        $city = trim((string)($_POST['city'] ?? ($systemDefaults['zatca_city'] ?? '')));
        $postalCode = trim((string)($_POST['postal_code'] ?? ($systemDefaults['zatca_postal_code'] ?? '')));
        $countryCode = strtoupper(trim((string)($_POST['country_code'] ?? ($systemDefaults['zatca_country_code'] ?? 'SA'))));
        $privateKeyPassphrase = trim((string)($_POST['private_key_passphrase'] ?? ($systemDefaults['zatca_private_key_passphrase'] ?? '')));
        $privateKey = trim((string)($_POST['private_key'] ?? ($systemDefaults['zatca_private_key'] ?? '')));
        $manualComplianceToken = trim((string)($_POST['manual_compliance_token'] ?? ($systemDefaults['zatca_binary_security_token'] ?? '')));
        $manualComplianceSecret = trim((string)($_POST['manual_compliance_secret'] ?? ($systemDefaults['zatca_secret'] ?? '')));
        $manualComplianceRequestId = trim((string)($_POST['manual_compliance_request_id'] ?? ($systemDefaults['zatca_compliance_request_id'] ?? '')));
        $manualProductionToken = trim((string)($_POST['manual_production_token'] ?? ($systemDefaults['zatca_production_binary_security_token'] ?? '')));
        $manualProductionSecret = trim((string)($_POST['manual_production_secret'] ?? ($systemDefaults['zatca_production_secret'] ?? '')));
        $manualProductionRequestId = trim((string)($_POST['manual_production_request_id'] ?? ($systemDefaults['zatca_production_request_id'] ?? '')));

        // Fallback to values already saved in Settings > ZATCA
        if ($otp === '') {
            $otp = trim((string)($systemDefaults['zatca_otp'] ?? ''));
        }
        if ($csr === '') {
            $csr = trim((string)($systemDefaults['zatca_csr_content'] ?? ''));
        }
        if ($vat === '') {
            $vat = trim((string)($systemDefaults['zatca_vat_number'] ?? ''));
        }
        if ($apiBase === '') {
            $apiBase = trim((string)($systemDefaults['zatca_api_base_resolved'] ?? ''));
        }

        if ($sellerName === '' || $crn === '' || $buildingNo === '' || $streetName === '' || $district === '' || $city === '' || $postalCode === '' || $countryCode === '') {
            $error = 'Complete seller profile is required (Seller Name, CRN, Building, Street, District, City, Postal Code, Country).';
        } elseif ($setupMode === 'csr_otp' && strlen(preg_replace('/\D+/', '', $vat)) !== 15) {
            $error = 'VAT Number must be 15 digits in CSR + OTP mode.';
        } elseif ($setupMode === 'portal_keys' && $vat !== '' && strlen(preg_replace('/\D+/', '', $vat)) !== 15) {
            $error = 'VAT Number must be 15 digits (or keep it empty) in Portal Keys mode.';
        } else {
            if ($setupMode === 'portal_keys') {
                // Portal keys mode: do NOT re-execute OTP/CSR flow from this page.
                if ($manualComplianceToken === '' || $manualComplianceSecret === '') {
                    $error = 'Manual mode requires Compliance Token and Compliance Secret.';
                } elseif ($manualComplianceRequestId === '') {
                    $error = 'Manual mode requires Compliance Request ID to fetch Production CSID.';
                } else {
                    $fetcher = new ZatcaCsidAutoFetcher($apiBase);

                    // In portal_keys mode we skip compliance invoice check because
                    // compliance was already executed in ZATCA portal.
                    $complianceInvoiceResult = null;

                    if ($manualProductionToken !== '' && $manualProductionSecret !== '') {
                        $productionResult = [
                            'success' => true,
                            'status_code' => 200,
                            'message' => 'Using manually pasted Production credentials (no OTP execution from this page).',
                            'response_body' => '',
                            'binary_security_token' => $manualProductionToken,
                            'secret' => $manualProductionSecret,
                            'request_id' => $manualProductionRequestId,
                        ];
                    } else {
                        $productionResult = $fetcher->requestProductionCsid(
                            $manualComplianceToken,
                            $manualComplianceSecret,
                            $manualComplianceRequestId
                        );
                    }

                    $result = [
                        'success' => !empty($productionResult['success']),
                        'stage' => !empty($productionResult['success']) ? 'completed_manual_portal_keys' : 'production',
                        'api_base' => $apiBase,
                        'compliance' => [
                            'success' => true,
                            'status_code' => 200,
                            'message' => 'Using manually pasted Compliance credentials (no OTP execution from this page).',
                            'response_body' => '',
                            'binary_security_token' => $manualComplianceToken,
                            'secret' => $manualComplianceSecret,
                            'request_id' => $manualComplianceRequestId,
                        ],
                        'compliance_invoices' => $complianceInvoiceResult,
                        'production' => $productionResult,
                        'keys' => [
                            'zatca_binary_security_token' => $manualComplianceToken,
                            'zatca_secret' => $manualComplianceSecret,
                            'zatca_compliance_request_id' => $manualComplianceRequestId,
                            'zatca_production_binary_security_token' => (string)($productionResult['binary_security_token'] ?? ''),
                            'zatca_production_secret' => (string)($productionResult['secret'] ?? ''),
                            'zatca_production_request_id' => (string)($productionResult['request_id'] ?? ''),
                        ],
                    ];
                    $success = !empty($result['success']);
                }
            } else {
                if ($otp === '') {
                    $error = 'OTP is required in CSR + OTP mode.';
                } elseif ($csr === '') {
                    $error = 'CSR content is required in CSR + OTP mode.';
                } else {
                    $vatDigits = preg_replace('/\D+/', '', $vat);
                    $csrPrecheck = inspectCsrSubjectHints($csr);

                    if (!empty($csrPrecheck['summary'])) {
                        $fetchNotice = appendNotice($fetchNotice, (string)$csrPrecheck['summary']);
                    }

                    if (
                        !empty($csrPrecheck['parseable'])
                        && !empty($csrPrecheck['vat_number'])
                        && $vatDigits !== ''
                        && (string)$csrPrecheck['vat_number'] !== $vatDigits
                    ) {
                        $error = 'CSR VAT mismatch: CSR appears to contain VAT ' . (string)$csrPrecheck['vat_number']
                            . ' while Seller VAT is ' . $vatDigits
                            . '. Generate a new CSR for the same VAT and EGS device used for OTP.';
                    }

                    if ($error === '' && $egsSerial !== '') {
                        $inputSerialNorm = normalizeEgsSerial($egsSerial);
                        $csrSerialNorm = normalizeEgsSerial((string)($csrPrecheck['serial'] ?? ''));

                        if ($csrSerialNorm === '') {
                            $fetchNotice = appendNotice(
                                $fetchNotice,
                                'EGS Serial check: CSR serial hint was not found. Confirm CSR was generated from the same registered device.'
                            );
                        } elseif ($inputSerialNorm !== '' && $csrSerialNorm !== $inputSerialNorm) {
                            $error = 'CSR EGS serial mismatch: CSR serial hint (' . (string)$csrPrecheck['serial']
                                . ') does not match entered EGS Serial (' . $egsSerial
                                . '). Generate CSR from the same device used for OTP.';
                        }
                    }

                    if ($error !== '') {
                        $result = [
                            'success' => false,
                            'stage' => 'compliance',
                            'api_base' => $apiBase,
                            'compliance' => [
                                'success' => false,
                                'status_code' => 0,
                                'message' => $error,
                                'response_body' => '',
                            ],
                            'compliance_invoices' => null,
                            'production' => null,
                        ];
                        $success = false;
                    }

                    if ($error === '') {
                        $fetcher = new ZatcaCsidAutoFetcher($apiBase);

                    // Match settings/zatca-verify behavior exactly:
                    // 1) Compliance CSID (or skip if existing credentials are already present)
                    // 2) Production CSID
                    // No compliance-invoices step in this flow.
                    $existingComplianceToken = trim((string)($systemDefaults['zatca_binary_security_token'] ?? ''));
                    $existingComplianceSecret = trim((string)($systemDefaults['zatca_secret'] ?? ''));
                    $existingComplianceRequestId = trim((string)($systemDefaults['zatca_compliance_request_id'] ?? ''));
                    $hasExistingComplianceCredentials = ($existingComplianceToken !== '' && $existingComplianceSecret !== '');

                    $compliance = null;
                    if ($hasExistingComplianceCredentials) {
                        $compliance = [
                            'success' => true,
                            'status_code' => 200,
                            'message' => 'Existing compliance credentials detected. Compliance fetch was skipped (same as settings verify flow).',
                            'response_body' => '',
                            'binary_security_token' => $existingComplianceToken,
                            'secret' => $existingComplianceSecret,
                            'request_id' => $existingComplianceRequestId,
                        ];
                    } else {
                        $compliance = $fetcher->requestComplianceCsid($otp, $csr);

                        // Developer portal often returns a generic 400 without useful details.
                        // Retry on simulation automatically to provide actionable diagnostics.
                        $apiLower = strtolower($apiBase);
                        $isDeveloperPortal = strpos($apiLower, '/developer-portal') !== false;
                        $complianceStatus = (int)($compliance['status_code'] ?? 0);

                        if (empty($compliance['success']) && $isDeveloperPortal && $complianceStatus === 400) {
                            $simulationBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation';
                            $retryFetcher = new ZatcaCsidAutoFetcher($simulationBase);
                            $retryCompliance = $retryFetcher->requestComplianceCsid($otp, $csr);

                            $compliance = $retryCompliance;
                            $apiBase = $simulationBase;
                            if (!empty($retryCompliance['success'])) {
                                $fetchNotice = 'Developer Portal returned generic 400. Auto-retry on Simulation succeeded and was used for this result.';
                            } else {
                                $fetchNotice = 'Developer Portal returned generic 400. Auto-retry on Simulation returned detailed diagnostics below.';
                            }
                        }
                    }

                    if (empty($compliance['success'])) {
                        $result = [
                            'success' => false,
                            'stage' => 'compliance',
                            'api_base' => $apiBase,
                            'compliance' => $compliance,
                            'compliance_invoices' => null,
                            'production' => null,
                        ];
                        $success = false;
                    } else {
                        $token = (string)($compliance['binary_security_token'] ?? '');
                        $secret = (string)($compliance['secret'] ?? '');
                        $requestId = (string)($compliance['request_id'] ?? '');

                        $production = $fetcher->requestProductionCsid($token, $secret, $requestId);

                        $result = [
                            'success' => !empty($production['success']),
                            'stage' => !empty($production['success']) ? 'completed' : 'production',
                            'api_base' => $apiBase,
                            'compliance' => $compliance,
                            'compliance_invoices' => null,
                            'production' => $production,
                            'keys' => [
                                'zatca_binary_security_token' => $token,
                                'zatca_secret' => $secret,
                                'zatca_compliance_request_id' => $requestId,
                                'zatca_production_binary_security_token' => (string)($production['binary_security_token'] ?? ''),
                                'zatca_production_secret' => (string)($production['secret'] ?? ''),
                                'zatca_production_request_id' => (string)($production['request_id'] ?? ''),
                            ],
                        ];
                        $success = !empty($result['success']);
                    }
                    }
                }
            }

            if ($error === '') {

                $jsonOutputPath = __DIR__ . '/system-zatca-sync.json';
                $payload = [
                    'timestamp' => date('c'),
                    'source' => [
                        'mode' => $actionMode,
                        'setup_mode' => $setupMode,
                        'from_system_settings' => true,
                        'api_base' => $apiBase,
                        'environment' => (string) ($systemDefaults['zatca_environment'] ?? ''),
                        'vat_number' => $vat,
                        'otp_masked' => $setupMode === 'csr_otp' ? maskValue($otp) : '',
                        'csr_present' => $setupMode === 'csr_otp' ? ($csr !== '') : false,
                        'csr_length' => $setupMode === 'csr_otp' ? strlen($csr) : 0,
                    ],
                    'fetch_result' => $result,
                ];

                $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if ($json !== false) {
                    file_put_contents($jsonOutputPath, $json);
                }

                if ($actionMode === 'system_verify_fetch') {
                    if ($success && isset($result['keys']) && is_array($result['keys'])) {
                        $existingCfg = [];
                        if (is_file($zatcaConfigFilePath)) {
                            $existingCfgRaw = json_decode((string)file_get_contents($zatcaConfigFilePath), true);
                            if (is_array($existingCfgRaw)) {
                                $existingCfg = $existingCfgRaw;
                            }
                        }
                        $updatedCfg = array_merge($existingCfg, $result['keys']);
                        $written = file_put_contents($zatcaConfigFilePath, json_encode($updatedCfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                        $saveStatus = [
                            'saved' => $written !== false,
                            'message' => $written !== false
                                ? 'Keys saved to zatca-config.json successfully.'
                                : 'Failed to write zatca-config.json.',
                        ];
                    } else {
                        $saveStatus = [
                            'saved' => false,
                            'message' => 'Keys were not saved because fetch did not succeed.',
                        ];
                    }
                }

                if ($success && isset($result['keys']) && is_array($result['keys'])) {
                $resolvedEnvironment = 'sandbox';
                $apiLower = strtolower($apiBase);
                if (strpos($apiLower, '/simulation') !== false) {
                    $resolvedEnvironment = 'simulation';
                } elseif (strpos($apiLower, '/core') !== false) {
                    $resolvedEnvironment = 'production';
                }

                $companySettings = [
                    'zatca_enabled' => '1',
                    'zatca_phase2_enabled' => '1',
                    'zatca_setup_mode' => $setupMode,
                    'zatca_environment' => $resolvedEnvironment,
                    'zatca_api_base_url' => rtrim($apiBase, '/'),
                    'zatca_invoice_type' => $invoiceType,
                    'zatca_seller_name' => $sellerName,
                    'zatca_vat_number' => preg_replace('/\D+/', '', $vat),
                    'zatca_seller_crn' => $crn,
                    'zatca_egs_serial' => $egsSerial,
                    'zatca_building_no' => $buildingNo,
                    'zatca_street_name' => $streetName,
                    'zatca_district' => $district,
                    'zatca_city' => $city,
                    'zatca_postal_code' => $postalCode,
                    'zatca_country_code' => $countryCode,
                    'zatca_csr_content' => $setupMode === 'csr_otp' ? $csr : '',
                    'zatca_otp' => $setupMode === 'csr_otp' ? $otp : '',
                    'zatca_private_key_passphrase' => $privateKeyPassphrase,
                    'zatca_private_key' => $privateKey,
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

                $companyPatchJson = (string) json_encode($companySettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                $phpLines = [
                    "<?php",
                    "// Patch for new company ZATCA setup",
                    "// Run inside app context where update_option() is available",
                    "\$zatcaSettings = " . var_export($companySettings, true) . ";",
                    "foreach (\$zatcaSettings as \$setting => \$value) {",
                    "    update_option(\$setting, \$value);",
                    "}",
                ];
                $companyPatchPhp = implode("\n", $phpLines);

                // Save to a per-company folder under tools/zatca/companies/
                $companyFolderSlug = (string) preg_replace('/_+/', '_', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $sellerName));
                $companyFolderSlug = trim($companyFolderSlug, '_');
                if ($companyFolderSlug === '') {
                    $companyFolderSlug = 'company_' . preg_replace('/\D+/', '', $vat);
                }
                $companyFolderSlug = substr($companyFolderSlug, 0, 60);
                $companyDir = __DIR__ . '/companies/' . $companyFolderSlug;
                if (!is_dir($companyDir)) {
                    mkdir($companyDir, 0755, true);
                }
                $companySettingsOutPath = $companyDir . '/zatca-settings.json';
                file_put_contents($companySettingsOutPath, $companyPatchJson);
                file_put_contents($companyDir . '/patch.php', $companyPatchPhp);

                // POST-Redirect-GET after verify+save so the form loads clean
                if ($actionMode === 'system_verify_fetch') {
                    $redirectBase = strtok((string)($_SERVER['PHP_SELF'] ?? '/'), '?');
                    header('Location: ' . $redirectBase . '?saved=1&company=' . urlencode($companyFolderSlug));
                    exit;
                }
                }
            }
        }
    }
}

// --- Flash message after save redirect ---
$savedFlash = null;
if (!empty($_GET['saved']) && !empty($_GET['company'])) {
    $flashSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_GET['company']);
    if ($flashSlug !== '') {
        $savedFlash = [
            'company' => $flashSlug,
            'path'    => 'tools/zatca/companies/' . $flashSlug . '/',
        ];
    }
}

// CSR Content is never pre-filled — user must paste or upload it manually.
$defaultCsr = '';

// --- Helpers ---
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function resultBadge(bool $ok): string
{
    return $ok
        ? '<span class="badge ok">SUCCESS</span>'
        : '<span class="badge fail">FAILED</span>';
}

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

function appendNotice(string $base, string $next): string
{
    $base = trim($base);
    $next = trim($next);

    if ($next === '') {
        return $base;
    }

    if ($base === '') {
        return $next;
    }

    return $base . ' | ' . $next;
}

function inspectCsrSubjectHints(string $csrInput): array
{
    $pem = normalizeCsrToPem($csrInput);
    if ($pem === '') {
        return [
            'parseable' => false,
            'serial' => '',
            'vat_number' => '',
            'summary' => 'CSR pre-check: CSR format could not be normalized locally. Verify full CSR content or file path.',
        ];
    }

    if (!function_exists('openssl_csr_get_subject')) {
        return [
            'parseable' => false,
            'serial' => '',
            'vat_number' => '',
            'summary' => 'CSR pre-check: OpenSSL CSR parser is not available on this server.',
        ];
    }

    $subject = @openssl_csr_get_subject($pem, false);
    if (!is_array($subject) || empty($subject)) {
        return [
            'parseable' => false,
            'serial' => '',
            'vat_number' => '',
            'summary' => 'CSR pre-check: CSR subject could not be parsed. CSR may be invalid or not generated by ZATCA SDK.',
        ];
    }

    $serial = '';
    foreach (['serialNumber', 'serialnumber', 'SN', 'UID'] as $key) {
        if (!empty($subject[$key])) {
            $serial = trim((string)$subject[$key]);
            break;
        }
    }

    $vat = '';
    $subjectJson = json_encode($subject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (is_string($subjectJson) && preg_match('/3\d{13}3/', $subjectJson, $m)) {
        $vat = (string)$m[0];
    }

    $summaryParts = [];
    if ($serial !== '') {
        $summaryParts[] = 'CSR serial hint: ' . $serial;
    }
    if ($vat !== '') {
        $summaryParts[] = 'CSR VAT hint: ' . $vat;
    }
    if (empty($summaryParts)) {
        $summaryParts[] = 'CSR pre-check: parsed successfully, but no VAT/serial hints were found in CSR subject.';
    }

    return [
        'parseable' => true,
        'serial' => $serial,
        'vat_number' => $vat,
        'summary' => implode(' | ', $summaryParts),
    ];
}

function normalizeCsrToPem(string $csrInput): string
{
    $raw = resolveCsrTextValue($csrInput);
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    $compactRaw = preg_replace('/\s+/', '', $raw);
    $decoded = base64_decode((string)$compactRaw, true);
    if ($decoded !== false && stripos($decoded, 'BEGIN CERTIFICATE REQUEST') !== false) {
        $raw = $decoded;
    }

    if (stripos($raw, 'BEGIN CERTIFICATE REQUEST') !== false) {
        return trim($raw) . "\n";
    }

    $body = preg_replace('/-----BEGIN CERTIFICATE REQUEST-----/i', '', $raw);
    $body = preg_replace('/-----END CERTIFICATE REQUEST-----/i', '', (string)$body);
    $body = preg_replace('/\s+/', '', (string)$body);
    $body = trim((string)$body);
    if ($body === '') {
        return '';
    }

    return "-----BEGIN CERTIFICATE REQUEST-----\n"
        . chunk_split($body, 64, "\n")
        . "-----END CERTIFICATE REQUEST-----\n";
}

function resolveCsrTextValue(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    if (is_file($raw) && is_readable($raw)) {
        $content = file_get_contents($raw);
        return $content === false ? '' : (string)$content;
    }

    return str_replace(["\\r\\n", "\\n"], ["\n", "\n"], $raw);
}

function normalizeEgsSerial(string $value): string
{
    $value = strtoupper(trim($value));
    return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZATCA CSID Fetch Tool</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f4f8; color: #1e293b; min-height: 100vh; }

        .topbar {
            background: #1d4ed8;
            color: #fff;
            padding: 18px 32px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .topbar h1 { font-size: 1.25rem; font-weight: 700; letter-spacing: .3px; }
        .topbar small { opacity: .75; font-size: .82rem; }

        .container { max-width: 960px; margin: 36px auto; padding: 0 20px 60px; }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            padding: 32px;
            margin-bottom: 28px;
        }
        .card h2 { font-size: 1.05rem; font-weight: 700; margin-bottom: 22px; color: #1d4ed8; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }

        label { display: block; font-size: .85rem; font-weight: 600; color: #475569; margin-bottom: 6px; }
        label span.required { color: #ef4444; margin-left: 2px; }

        input[type="text"], textarea, select {
            width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px;
            padding: 10px 13px; font-size: .92rem; color: #1e293b;
            transition: border-color .2s;
            font-family: inherit;
        }
        input[type="text"]:focus, textarea:focus, select:focus {
            outline: none; border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,.12);
        }
        textarea { resize: vertical; font-family: 'Cascadia Code', 'Consolas', monospace; font-size: .82rem; }

        .file-upload-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .file-upload-row label { margin: 0; }
        .upload-btn { background: #e0e7ff; color: #3730a3; border: 1.5px solid #a5b4fc; border-radius: 7px; padding: 7px 16px; font-size: .84rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .15s; }
        .upload-btn:hover { background: #c7d2fe; }
        #csr-file-input { display: none; }
        #pk-file-input { display: none; }
        #csr-filename { font-size: .8rem; color: #64748b; }

        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .row.full { grid-template-columns: 1fr; }
        @media(max-width: 640px) { .row { grid-template-columns: 1fr; } }

        .hint { font-size: .78rem; color: #94a3b8; margin-top: 5px; }

        .btn {
            background: #1d4ed8; color: #fff; border: none; border-radius: 8px;
            padding: 12px 32px; font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: background .2s;
        }
        .btn:hover { background: #1e40af; }
        .btn:disabled { background: #94a3b8; cursor: not-allowed; }
        .btn.alt { background: #1f3b73; }
        .btn.alt:hover { background: #162a52; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }

        .step-guide { display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; margin-bottom:16px; }
        .step-pill { border:1px solid #bfdbfe; background:#eff6ff; color:#1e3a8a; border-radius:8px; padding:10px 12px; font-size:.78rem; font-weight:600; }
        .step-pill strong { display:block; font-size:.8rem; margin-bottom:3px; }
        @media(max-width: 900px) { .step-guide { grid-template-columns: 1fr 1fr; } }
        @media(max-width: 520px) { .step-guide { grid-template-columns: 1fr; } }

        .alert { border-radius: 8px; padding: 14px 18px; font-size: .9rem; margin-bottom: 20px; }
        .alert.error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert.success { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
        .alert.warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
        .mode-box { background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:12px; margin-bottom:16px; }
        .mode-radio { display:flex; gap:18px; flex-wrap:wrap; margin-top:6px; }
        .mode-radio label { display:flex; align-items:center; gap:6px; margin:0; font-size:.84rem; }

        .badge { display: inline-block; border-radius: 5px; padding: 3px 10px; font-size: .78rem; font-weight: 700; }
        .badge.ok   { background: #dcfce7; color: #15803d; }
        .badge.fail { background: #fee2e2; color: #b91c1c; }

        .result-section { margin-bottom: 24px; }
        .result-section h3 { font-size: .95rem; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }

        .kv-table { width: 100%; border-collapse: collapse; }
        .kv-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; font-size: .88rem; vertical-align: top; }
        .kv-table td:first-child { width: 38%; font-weight: 600; color: #475569; }
        .kv-table td .mono { font-family: 'Cascadia Code', 'Consolas', monospace; font-size: .8rem; word-break: break-all; background: #f8fafc; padding: 4px 7px; border-radius: 4px; display: inline-block; }
        .kv-table tr:last-child td { border-bottom: none; }

        .keys-grid { display: grid; grid-template-columns: 1fr; gap: 0; }
        .key-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 10px; }
        .key-item .key-name { font-size: .78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
        .key-item .key-value { font-family: 'Cascadia Code', 'Consolas', monospace; font-size: .8rem; word-break: break-all; color: #1e293b; background: #fff; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .key-item .copy-btn { margin-top: 7px; background: #e0e7ff; color: #3730a3; border: none; border-radius: 5px; padding: 4px 12px; font-size: .76rem; font-weight: 600; cursor: pointer; }
        .key-item .copy-btn:hover { background: #c7d2fe; }

        .raw-body { background: #0f172a; color: #e2e8f0; border-radius: 8px; padding: 14px; font-family: 'Cascadia Code', 'Consolas', monospace; font-size: .78rem; white-space: pre-wrap; word-break: break-all; max-height: 220px; overflow-y: auto; margin-top: 8px; }
        .patch-box { background: #0b1020; color: #dbeafe; border-radius: 8px; padding: 12px; font-family: 'Cascadia Code', 'Consolas', monospace; font-size: .76rem; white-space: pre-wrap; word-break: break-word; max-height: 280px; overflow-y: auto; border: 1px solid #1e3a8a; }
        .copy-row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:8px; }
        .copy-row .title { font-weight:700; color:#1e3a8a; }
        .copy-row .copy-btn-inline { background:#1e40af; color:#fff; border:none; border-radius:6px; padding:6px 10px; font-size:.74rem; cursor:pointer; }
        .copy-row .copy-btn-inline:hover { background:#1d4ed8; }

        .section-toggle { background: none; border: none; font-size: .8rem; color: #3b82f6; cursor: pointer; text-decoration: underline; margin-left: auto; }
        details summary { cursor: pointer; font-size: .82rem; color: #64748b; margin-top: 8px; }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <h1>ZATCA CSID Fetch Tool</h1>
        <small>Enter your OTP and CSR to get Compliance &amp; Production IDs</small>
    </div>
</div>

<div class="container">

    <?php if ($savedFlash !== null): ?>
        <div class="alert success">
            <strong>&#10003; Company settings saved!</strong>
            Folder: <code><?= h($savedFlash['path']) ?></code>
            &mdash; <code>zatca-settings.json</code> and <code>patch.php</code> created.
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert error"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="card">
        <h2>Step 1 &mdash; ZATCA Company Onboarding Details</h2>
        <form method="POST" action="" id="fetchForm">
            <input type="hidden" name="action_mode" id="action_mode" value="manual_fetch">

            <div class="step-guide">
                <div class="step-pill">
                    <strong>1. Fill Company Details</strong>
                    Enter VAT, seller profile, CSR, and private key path.
                </div>
                <div class="step-pill">
                    <strong>2. Generate OTP</strong>
                    Get fresh OTP from portal. Do not click Execute there.
                </div>
                <div class="step-pill">
                    <strong>3. Verify and Fetch Keys</strong>
                    Click verify button to run compliance then production flow.
                </div>
                <div class="step-pill">
                    <strong>4. Copy New Company Patch</strong>
                    Copy JSON/PHP patch output and apply for new company setup.
                </div>
            </div>

            <div class="mode-box">
                <label style="margin-bottom:4px;">ZATCA Onboarding Method <span class="required">*</span></label>
                <?php $setupModeSelected = (string)($_POST['setup_mode'] ?? ($systemDefaults['zatca_setup_mode'] ?? 'csr_otp')); ?>
                <div class="mode-radio">
                    <label>
                        <input type="radio" name="setup_mode" value="csr_otp" <?= $setupModeSelected === 'csr_otp' ? 'checked' : '' ?>>
                        CSR + OTP (Auto Fetch from this page)
                    </label>
                    <label>
                        <input type="radio" name="setup_mode" value="portal_keys" <?= $setupModeSelected === 'portal_keys' ? 'checked' : '' ?>>
                        Portal Keys (Manual Paste, do not re-execute OTP)
                    </label>
                </div>
                <p class="hint" id="setupModeHint" style="margin-top:8px;">
                    Use one onboarding method only. In Portal Keys mode, this page will not call Compliance OTP endpoint again and skips compliance invoice check.
                </p>
            </div>

            <div class="row">
                <div>
                    <label for="seller_name">Seller Name <span class="required">*</span></label>
                    <input type="text" id="seller_name" name="seller_name" placeholder="e.g. SnapS Production House"
                        value="<?= h((string)($_POST['seller_name'] ?? ($systemDefaults['zatca_seller_name'] ?? ''))) ?>">
                </div>
                <div>
                    <label for="seller_crn">Unified No. (CRN 700) <span class="required">*</span></label>
                    <input type="text" id="seller_crn" name="seller_crn" placeholder="e.g. 7003478593"
                        value="<?= h((string)($_POST['seller_crn'] ?? ($systemDefaults['zatca_seller_crn'] ?? ''))) ?>" maxlength="20">
                </div>
            </div>

            <div id="csrOtpSection">
            <div class="row">
                <div>
                    <label for="otp">OTP (from ZATCA Portal) <span class="required">*</span></label>
                    <input type="text" id="otp" name="otp" placeholder="e.g. 123456"
                        value="<?= h((string)($_POST['otp'] ?? ($systemDefaults['zatca_otp'] ?? ''))) ?>" autocomplete="off" maxlength="20">
                    <p class="hint">One-time password generated from the ZATCA Fatoora portal. Use it immediately &mdash; OTPs expire in ~1 hour.</p>
                </div>
                <div>
                    <label for="api_base">API Environment</label>
                    <select id="api_base" name="api_base">
                        <?php
                        $envs = [
                            'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal' => 'Developer Portal (Sandbox)',
                            'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation'        => 'Simulation',
                            'https://gw-fatoora.zatca.gov.sa/e-invoicing/core'              => 'Production',
                        ];
                        $selectedApi = trim((string)($_POST['api_base'] ?? ($systemDefaults['zatca_api_base_resolved'] ?? 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation')));
                        foreach ($envs as $val => $label): ?>
                            <option value="<?= h($val) ?>" <?= $selectedApi === $val ? 'selected' : '' ?>><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="hint"><strong>Simulation</strong> = ZATCA sandbox (recommended, gives detailed errors). <strong>Production</strong> = live environment only.</p>
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="vat">Seller VAT Number <span class="required">*</span></label>
                    <input type="text" id="vat" name="vat" placeholder="e.g. 312345678900003"
                        value="<?= h((string)($_POST['vat'] ?? ($systemDefaults['zatca_vat_number'] ?? ''))) ?>" maxlength="15">
                    <p class="hint">15-digit VAT registration number used for seller onboarding and configuration.</p>
                </div>
                <div>
                    <label for="invoice_type">ZATCA Invoice Type</label>
                    <select id="invoice_type" name="invoice_type">
                        <?php $invoiceTypeSelected = strtolower((string)($_POST['invoice_type'] ?? ($systemDefaults['zatca_invoice_type'] ?? 'simplified'))); ?>
                        <option value="simplified" <?= $invoiceTypeSelected === 'simplified' ? 'selected' : '' ?>>Simplified (Reporting)</option>
                        <option value="standard" <?= $invoiceTypeSelected === 'standard' ? 'selected' : '' ?>>Standard (Clearance)</option>
                        <option value="both" <?= $invoiceTypeSelected === 'both' ? 'selected' : '' ?>>Both (Standard + Simplified)</option>
                    </select>
                    <p class="hint">Use <strong>Both</strong> when the company needs to issue both simplified and standard invoices.</p>
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="building_no">Building No. <span class="required">*</span></label>
                    <input type="text" id="building_no" name="building_no" placeholder="e.g. 7788"
                        value="<?= h((string)($_POST['building_no'] ?? ($systemDefaults['zatca_building_no'] ?? ''))) ?>">
                </div>
                <div>
                    <label for="street_name">Street Name <span class="required">*</span></label>
                    <input type="text" id="street_name" name="street_name" placeholder="e.g. Sandbox Street 12"
                        value="<?= h((string)($_POST['street_name'] ?? ($systemDefaults['zatca_street_name'] ?? ''))) ?>">
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="district">District <span class="required">*</span></label>
                    <input type="text" id="district" name="district" placeholder="e.g. Makkah"
                        value="<?= h((string)($_POST['district'] ?? ($systemDefaults['zatca_district'] ?? ''))) ?>">
                </div>
                <div>
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" placeholder="e.g. Jeddah"
                        value="<?= h((string)($_POST['city'] ?? ($systemDefaults['zatca_city'] ?? ''))) ?>">
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="postal_code">Postal Code <span class="required">*</span></label>
                    <input type="text" id="postal_code" name="postal_code" placeholder="e.g. 12211"
                        value="<?= h((string)($_POST['postal_code'] ?? ($systemDefaults['zatca_postal_code'] ?? ''))) ?>">
                </div>
                <div>
                    <label for="country_code">Country Code <span class="required">*</span></label>
                    <input type="text" id="country_code" name="country_code" placeholder="SA" maxlength="2"
                        value="<?= h((string)($_POST['country_code'] ?? ($systemDefaults['zatca_country_code'] ?? 'SA'))) ?>">
                </div>
            </div>

            <div class="row full">
                <div style="display:flex;align-items:flex-end;padding-bottom:6px">
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 14px;font-size:.8rem;color:#1e40af;width:100%">
                        <strong>This page follows Settings Verify flow:</strong><br>
                        1. Compliance CSID (OTP + CSR)<br>
                        2. Production CSID (using compliance credentials)<br><br>
                        <strong style="color:#b45309">&#9888; OTP + CSR must match:</strong><br>
                        The OTP is issued for a specific device registered on the ZATCA portal. The CSR must be generated by the <strong>ZATCA SDK</strong> for that exact device (same EGS serial + VAT number). A pre-generated or mismatched CSR will be rejected.
                    </div>
                </div>
            </div>

            <div class="row full">
                <div>
                    <div class="file-upload-row">
                        <label for="csr">CSR Content <span class="required">*</span></label>
                        <button type="button" class="upload-btn" onclick="document.getElementById('csr-file-input').click()">
                            &#128193; Browse .csr file
                        </button>
                        <span id="csr-filename">No file selected</span>
                        <input type="file" id="csr-file-input" accept=".csr,.cer,.txt" onchange="loadCsrFile(this)">
                    </div>
                    <textarea id="csr" name="csr" rows="10"
                        placeholder="Paste your CSR content here, or click 'Browse' to load a .csr file from your computer."><?= h((string)($_POST['csr'] ?? $defaultCsr)) ?></textarea>
                    <p class="hint">
                        Paste the full content of your <code>.csr</code> file, or use the Browse button to load it from disk.
                        Both PEM format (<code>-----BEGIN CERTIFICATE REQUEST-----</code>) and raw base64 are accepted.
                        <?php if ($defaultCsr !== ''): ?>
                            &mdash; <strong>Auto-filled from current system settings (and fallback local CSR file)</strong>. Replace with your real CSR if needed.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            </div>

            <div id="portalKeysSection" style="display:none;">
                <div class="row">
                    <div>
                        <label for="manual_compliance_token">Compliance Binary Security Token <span class="required">*</span></label>
                        <textarea id="manual_compliance_token" name="manual_compliance_token" rows="3" placeholder="Paste compliance binary security token"><?= h((string)($_POST['manual_compliance_token'] ?? ($systemDefaults['zatca_binary_security_token'] ?? ''))) ?></textarea>
                    </div>
                    <div>
                        <label for="manual_compliance_secret">Compliance Secret <span class="required">*</span></label>
                        <input type="text" id="manual_compliance_secret" name="manual_compliance_secret" placeholder="Paste compliance secret"
                            value="<?= h((string)($_POST['manual_compliance_secret'] ?? ($systemDefaults['zatca_secret'] ?? ''))) ?>">
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label for="manual_compliance_request_id">Compliance Request ID</label>
                        <input type="text" id="manual_compliance_request_id" name="manual_compliance_request_id" placeholder="Required to fetch production CSID"
                            value="<?= h((string)($_POST['manual_compliance_request_id'] ?? ($systemDefaults['zatca_compliance_request_id'] ?? ''))) ?>">
                        <p class="hint">Paste the Compliance Request ID returned by the portal execute call.</p>
                    </div>
                    <div>
                        <label for="manual_production_request_id">Production Request ID</label>
                        <input type="text" id="manual_production_request_id" name="manual_production_request_id" placeholder="Optional but recommended"
                            value="<?= h((string)($_POST['manual_production_request_id'] ?? ($systemDefaults['zatca_production_request_id'] ?? ''))) ?>">
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label for="manual_production_token">Production Binary Security Token (optional)</label>
                        <textarea id="manual_production_token" name="manual_production_token" rows="3" placeholder="Optional: paste if you already have production token"><?= h((string)($_POST['manual_production_token'] ?? ($systemDefaults['zatca_production_binary_security_token'] ?? ''))) ?></textarea>
                    </div>
                    <div>
                        <label for="manual_production_secret">Production Secret (optional)</label>
                        <input type="text" id="manual_production_secret" name="manual_production_secret" placeholder="Optional: paste if you already have production secret"
                            value="<?= h((string)($_POST['manual_production_secret'] ?? ($systemDefaults['zatca_production_secret'] ?? ''))) ?>">
                        <p class="hint">Leave production fields empty to fetch Production CSID from ZATCA using compliance credentials above.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="private_key_passphrase">Private Key Passphrase</label>
                    <input type="text" id="private_key_passphrase" name="private_key_passphrase" placeholder="Optional"
                        value="<?= h((string)($_POST['private_key_passphrase'] ?? ($systemDefaults['zatca_private_key_passphrase'] ?? ''))) ?>">
                </div>
                <div>
                    <div class="file-upload-row">
                        <label for="private_key">Private Key</label>
                        <button type="button" class="upload-btn" onclick="document.getElementById('pk-file-input').click()">
                            &#128193; Browse .pem file
                        </button>
                        <span id="pk-filename">No file selected</span>
                        <input type="file" id="pk-file-input" accept=".pem,.key,.txt" onchange="loadPrivateKeyFile(this)">
                    </div>
                    <textarea id="private_key" name="private_key" rows="3" placeholder="Paste private key content here, or click 'Browse' to load a .pem file."><?= h((string)($_POST['private_key'] ?? ($systemDefaults['zatca_private_key'] ?? ''))) ?></textarea>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn" id="submitBtn" onclick="setActionMode('manual_fetch')">Step 3A: Fetch Compliance &amp; Production IDs</button>
                <button type="submit" class="btn alt" id="verifyFetchBtn" onclick="setActionMode('system_verify_fetch')">Step 3B: Verify and Fetch Keys (System Config + Save JSON)</button>
            </div>
        </form>
    </div>

    <?php if ($result !== null): ?>

    <!-- RESULTS -->
    <div class="card">
        <h2>
            Step 2 &mdash; Results
            <?= resultBadge($success) ?>
        </h2>

        <?php if (!$success): ?>
            <div class="alert <?= ($result['stage'] ?? '') === 'compliance' ? 'error' : 'warning' ?>">
                <?php
                $stage = $result['stage'] ?? '';
                if ($stage === 'compliance') {
                    echo h((string)($result['compliance']['message'] ?? 'Compliance step failed.'));
                } elseif ($stage === 'compliance_invoices') {
                    echo h((string)($result['compliance_invoices']['message'] ?? 'Compliance invoice check failed.'));
                } else {
                    echo h((string)($result['production']['message'] ?? 'Production step failed.'));
                }
                ?>
            </div>
            <?php if ($activeSetupMode === 'csr_otp'): ?>
                <div class="alert warning" style="font-size:.82rem">
                    <strong>Tip:</strong> OTPs are <strong>single-use</strong> and expire quickly. If you clicked <em>Execute</em> inside the ZATCA portal Swagger UI, that OTP is now consumed &mdash; generate a new one and submit it here immediately without testing it first.
                </div>
            <?php else: ?>
                <div class="alert warning" style="font-size:.82rem">
                    <strong>Tip:</strong> You selected <strong>Portal Keys</strong> mode. This page does not re-execute OTP in this mode; it only uses the manually pasted portal credentials.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($jsonOutputPath !== ''): ?>
            <div class="alert success" style="font-size:.82rem">
                JSON snapshot saved to: <?= h(str_replace('\\', '/', $jsonOutputPath)) ?>
            </div>
        <?php endif; ?>

        <?php if ($fetchNotice !== ''): ?>
            <div class="alert warning" style="font-size:.82rem">
                <strong>Notice:</strong> <?= h($fetchNotice) ?>
            </div>
        <?php endif; ?>

        <?php if (is_array($saveStatus)): ?>
            <div class="alert <?= !empty($saveStatus['saved']) ? 'success' : 'warning' ?>" style="font-size:.82rem">
                <strong>System Save:</strong> <?= h((string)($saveStatus['message'] ?? '')) ?>
            </div>
        <?php endif; ?>

        <!-- COMPLIANCE -->
        <?php
        $comp = $result['compliance'] ?? [];
        $compOk = !empty($comp['success']);
        ?>
        <div class="result-section">
            <h3>Step 1 &mdash; Compliance CSID <?= resultBadge($compOk) ?></h3>
            <?php if ($compOk): ?>
                <table class="kv-table">
                    <tr>
                        <td>Binary Security Token</td>
                        <td><span class="mono"><?= h((string)($comp['binary_security_token'] ?? '')) ?></span></td>
                    </tr>
                    <tr>
                        <td>Secret</td>
                        <td><span class="mono"><?= h((string)($comp['secret'] ?? '')) ?></span></td>
                    </tr>
                    <tr>
                        <td>Compliance Request ID</td>
                        <td><span class="mono"><?= h((string)($comp['request_id'] ?? '')) ?></span></td>
                    </tr>
                    <tr>
                        <td>HTTP Status</td>
                        <td><?= h((string)($comp['status_code'] ?? '')) ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <div class="alert error" style="margin-top:10px"><?= h((string)($comp['message'] ?? 'Compliance request failed.')) ?></div>
                <?php if (!empty($comp['response_body'])): ?>
                    <details>
                        <summary>Show ZATCA response body</summary>
                        <?php
                        $decoded = json_decode((string)$comp['response_body'], true);
                        $body = is_array($decoded)
                            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : (string)$comp['response_body'];
                        ?>
                        <div class="raw-body"><?= h($body) ?></div>
                    </details>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- COMPLIANCE INVOICE CHECK -->
        <?php
        $ci   = $result['compliance_invoices'] ?? null;
        $ciOk = !empty($ci['success']);
        ?>
        <div class="result-section">
            <h3>Step 2 &mdash; Compliance Invoice Check <?= $ci !== null ? resultBadge($ciOk) : '<span class="badge" style="background:#e2e8f0;color:#64748b">SKIPPED</span>' ?></h3>
            <?php if ($ci === null): ?>
                <div class="alert warning" style="margin-top:10px">
                    <?= $activeSetupMode === 'csr_otp'
                        ? 'Skipped in this tool to match Settings Verify flow (Compliance CSID -> Production CSID).'
                        : 'Skipped in Portal Keys mode.' ?>
                </div>
            <?php elseif ($ciOk): ?>
                <table class="kv-table">
                    <tr><td>HTTP Status</td><td><?= h((string)($ci['status_code'] ?? '')) ?></td></tr>
                    <tr><td>Message</td><td><?= h((string)($ci['message'] ?? '')) ?></td></tr>
                </table>
                <?php if (!empty($ci['response_body'])): ?>
                    <details>
                        <summary>Show ZATCA response</summary>
                        <?php
                        $decoded = json_decode((string)$ci['response_body'], true);
                        $body = is_array($decoded)
                            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : (string)$ci['response_body'];
                        ?>
                        <div class="raw-body"><?= h($body) ?></div>
                    </details>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert warning" style="margin-top:10px"><?= h((string)($ci['message'] ?? 'Compliance invoice check failed.')) ?></div>
                <?php if (!empty($ci['response_body'])): ?>
                    <details>
                        <summary>Show ZATCA response body</summary>
                        <?php
                        $decoded = json_decode((string)$ci['response_body'], true);
                        $body = is_array($decoded)
                            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : (string)$ci['response_body'];
                        ?>
                        <div class="raw-body"><?= h($body) ?></div>
                    </details>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- PRODUCTION -->
        <?php
        $prod = $result['production'] ?? [];
        $prodOk = !empty($prod['success']);
        ?>
        <div class="result-section">
            <h3>Step 3 &mdash; Production CSID <?= $prod ? resultBadge($prodOk) : '<span class="badge" style="background:#e2e8f0;color:#64748b">SKIPPED</span>' ?></h3>
            <?php if ($prodOk): ?>
                <table class="kv-table">
                    <tr>
                        <td>Binary Security Token</td>
                        <td><span class="mono"><?= h((string)($prod['binary_security_token'] ?? '')) ?></span></td>
                    </tr>
                    <tr>
                        <td>Secret</td>
                        <td><span class="mono"><?= h((string)($prod['secret'] ?? '')) ?></span></td>
                    </tr>
                    <tr>
                        <td>Production Request ID</td>
                        <td><span class="mono"><?= h((string)($prod['request_id'] ?? '')) ?></span></td>
                    </tr>
                    <tr>
                        <td>HTTP Status</td>
                        <td><?= h((string)($prod['status_code'] ?? '')) ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <?php if ($compOk): ?>
                    <div class="alert warning" style="margin-top:10px"><?= h((string)($prod['message'] ?? 'Production request failed.')) ?></div>
                    <?php if (!empty($prod['response_body'])): ?>
                        <details>
                            <summary>Show ZATCA response body</summary>
                            <?php
                            $decoded = json_decode((string)$prod['response_body'], true);
                            $body = is_array($decoded)
                                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                : (string)$prod['response_body'];
                            ?>
                            <div class="raw-body"><?= h($body) ?></div>
                        </details>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert warning" style="margin-top:10px">Skipped — compliance step must succeed first.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- SUCCESS: ALL KEYS -->
        <?php if ($success && isset($result['keys']) && is_array($result['keys'])): ?>
            <hr style="border:none;border-top:2px solid #e2e8f0;margin:24px 0">
            <h3 style="margin-bottom:14px;color:#15803d;">All Keys (save these to your app config)</h3>
            <div class="alert success" style="margin-bottom:18px">Both steps succeeded. Copy these keys into your application settings.</div>
            <div class="keys-grid">
                <?php foreach ($result['keys'] as $keyName => $keyValue): ?>
                    <div class="key-item" id="ki-<?= h($keyName) ?>">
                        <div class="key-name"><?= h(str_replace('_', ' ', $keyName)) ?></div>
                        <div class="key-value" id="kv-<?= h($keyName) ?>"><?= h((string)$keyValue) ?></div>
                        <button class="copy-btn" onclick="copyKey('<?= h($keyName) ?>')">Copy</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($companyPatchJson !== '' || $companyPatchPhp !== ''): ?>
                <hr style="border:none;border-top:2px solid #e2e8f0;margin:24px 0">
                <h3 style="margin-bottom:14px;color:#0f766e;">New Company Setup Patch (Copy &amp; Apply)</h3>
                <?php if ($companySettingsOutPath !== ''): ?>
                    <div class="alert success" style="font-size:.82rem">
                        New company settings JSON saved to: <?= h(str_replace('\\', '/', $companySettingsOutPath)) ?>
                    </div>
                <?php endif; ?>

                <?php if ($companyPatchJson !== ''): ?>
                    <div class="copy-row">
                        <div class="title">1) JSON settings package</div>
                        <button type="button" class="copy-btn-inline" onclick="copyBlock('companyPatchJson', this)">Copy JSON</button>
                    </div>
                    <div class="patch-box" id="companyPatchJson"><?= h($companyPatchJson) ?></div>
                <?php endif; ?>

                <?php if ($companyPatchPhp !== ''): ?>
                    <div class="copy-row" style="margin-top:14px;">
                        <div class="title">2) PHP patch script (update_option)</div>
                        <button type="button" class="copy-btn-inline" onclick="copyBlock('companyPatchPhp', this)">Copy PHP Patch</button>
                    </div>
                    <div class="patch-box" id="companyPatchPhp"><?= h($companyPatchPhp) ?></div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <p style="font-size:.78rem;color:#94a3b8;text-align:center;margin-top:8px">
        ZATCA CSID Fetch Tool &mdash; For internal use only. Do not expose this page publicly.
    </p>

</div>

<script>
function setActionMode(mode) {
    var modeEl = document.getElementById('action_mode');
    if (modeEl) {
        modeEl.value = mode;
    }
}

function syncSetupModeSections() {
    var selected = document.querySelector('input[name="setup_mode"]:checked');
    var mode = selected ? selected.value : 'csr_otp';

    var csrSection = document.getElementById('csrOtpSection');
    var portalSection = document.getElementById('portalKeysSection');
    var modeHint = document.getElementById('setupModeHint');
    var submitBtn = document.getElementById('submitBtn');
    var verifyBtn = document.getElementById('verifyFetchBtn');
    var egsInput = document.getElementById('egs_serial');

    if (csrSection) csrSection.style.display = (mode === 'csr_otp') ? '' : 'none';
    if (portalSection) portalSection.style.display = (mode === 'portal_keys') ? '' : 'none';
    if (modeHint) {
        modeHint.innerHTML = mode === 'portal_keys'
            ? 'Portal Keys mode uses pasted Compliance credentials and <strong>does not call OTP/Compliance API again</strong>; compliance invoice check is skipped and Production CSID is fetched using Compliance Request ID.'
            : 'CSR + OTP mode calls ZATCA Compliance API from this page using the OTP and CSR.';
    }

    if (submitBtn) {
        submitBtn.textContent = mode === 'portal_keys'
            ? 'Step 3A: Fetch Production from Compliance Keys'
            : 'Step 3A: Fetch Compliance & Production IDs';
    }

    if (verifyBtn) {
        verifyBtn.textContent = mode === 'portal_keys'
            ? 'Step 3B: Fetch and Save Keys (System Config + JSON)'
            : 'Step 3B: Verify and Fetch Keys (System Config + Save JSON)';
    }
}

function copyKey(keyName) {
    var el = document.getElementById('kv-' + keyName);
    if (!el) return;
    var text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(function () {
        var btn = el.parentElement.querySelector('.copy-btn');
        if (btn) { btn.textContent = 'Copied!'; setTimeout(function() { btn.textContent = 'Copy'; }, 1800); }
    });
}

function copyBlock(id, btn) {
    var el = document.getElementById(id);
    if (!el) return;
    var text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(function () {
        if (btn) {
            var old = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(function() { btn.textContent = old; }, 1500);
        }
    });
}

function parseCsrHints() {
    var ta = document.getElementById('csr');
    var box = document.getElementById('csr-hints');
    if (!ta || !box) return;
    var raw = ta.value.trim();
    if (!raw) { box.style.display = 'none'; box.innerHTML = ''; return; }

    // Extract base64 body regardless of headers
    var b64 = raw.replace(/-----[^-]+-----/g, '').replace(/[\r\n\s]/g, '');
    if (!b64) { box.style.display = 'none'; return; }

    var serial = '', vat = '';
    try {
        var bin = atob(b64);
        // Scan binary string for 1-TST| pattern (ZATCA serial format)
        var sMatch = bin.match(/1-[A-Za-z0-9]{1,40}\|2-[A-Za-z0-9\-]{1,40}\|3-[A-Za-z0-9\-]{8,36}/);
        if (sMatch) serial = sMatch[0];
        // Scan for VAT-like 15-digit number starting and ending with 3
        var vMatch = bin.match(/3\d{13}3/);
        if (vMatch) vat = vMatch[0];
    } catch (e) {}

    if (!serial && !vat) { box.style.display = 'none'; box.innerHTML = ''; return; }

    var html = '<strong>\u{1F50D} CSR contains:</strong> ';
    var parts = [];
    if (serial) parts.push('Serial: <code style="background:#d0eaff;padding:1px 5px;border-radius:3px;">' + serial + '</code>');
    if (vat)    parts.push('VAT: <code style="background:#d0eaff;padding:1px 5px;border-radius:3px;">' + vat + '</code>');
    html += parts.join(' &nbsp;&bull;&nbsp; ');
    html += '<br><span style="color:#555;">Make sure the EGS Serial above and the OTP match these values.</span>';
    box.innerHTML = html;
    box.style.display = '';
}

function loadCsrFile(input) {
    var file = input.files[0];
    if (!file) return;
    var nameEl = document.getElementById('csr-filename');
    if (nameEl) nameEl.textContent = file.name;
    var reader = new FileReader();
    reader.onload = function(e) {
        var ta = document.getElementById('csr');
        if (ta) { ta.value = e.target.result; parseCsrHints(); }
    };
    reader.readAsText(file);
}

function loadPrivateKeyFile(input) {
    var file = input.files[0];
    if (!file) return;
    var nameEl = document.getElementById('pk-filename');
    if (nameEl) nameEl.textContent = file.name;
    var reader = new FileReader();
    reader.onload = function(e) {
        var ta = document.getElementById('private_key');
        if (ta) ta.value = e.target.result;
    };
    reader.readAsText(file);
}

document.getElementById('fetchForm').addEventListener('submit', function() {
    var modeEl = document.getElementById('action_mode');
    var mode = modeEl ? modeEl.value : 'manual_fetch';
    var btn = document.getElementById('submitBtn');
    var verifyBtn = document.getElementById('verifyFetchBtn');

    if (btn) {
        btn.disabled = true;
        if (mode === 'manual_fetch') {
            btn.textContent = 'Fetching... please wait';
        }
    }

    if (verifyBtn) {
        verifyBtn.disabled = true;
        if (mode === 'system_verify_fetch') {
            verifyBtn.textContent = 'Verifying and fetching... please wait';
        }
    }
});

document.querySelectorAll('input[name="setup_mode"]').forEach(function(el) {
    el.addEventListener('change', syncSetupModeSections);
});

var csrTa = document.getElementById('csr');
if (csrTa) {
    csrTa.addEventListener('input', parseCsrHints);
    csrTa.addEventListener('change', parseCsrHints);
    // Run once on page load in case CSR is pre-filled
    parseCsrHints();
}

syncSetupModeSections();
</script>

</body>
</html>
