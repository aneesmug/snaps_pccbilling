<?php

use GuzzleHttp\Client;

class ZatcaPhase2
{
    public static function requestComplianceCsid($config, array $input = [])
    {
        $api_base = self::resolveApiBase($config);

        $otp = isset($input['otp'])
            ? trim((string) $input['otp'])
            : (isset($config['zatca_compliance_otp'])
                ? trim((string) $config['zatca_compliance_otp'])
                : (isset($config['zatca_otp']) ? trim((string) $config['zatca_otp']) : ''));
        $otp = self::normalizeOtpValue($otp);

        $csr_input = isset($input['csr'])
            ? (string) $input['csr']
            : (isset($config['zatca_csr'])
                ? (string) $config['zatca_csr']
                : (isset($config['zatca_csr_content']) ? (string) $config['zatca_csr_content'] : ''));

        if ($otp === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'ZATCA OTP is required before requesting Compliance CSID.',
                'response_body' => '',
            ];
        }

        if (!preg_match('/^[0-9]{6}$/', (string) $otp)) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'ZATCA OTP must be exactly 6 digits.',
                'response_body' => '',
            ];
        }

        self::logZatcaDiagnostic("requestComplianceCsid START - CSR input: " . substr($csr_input, 0, 100));
        self::logZatcaDiagnostic(
            "requestComplianceCsid context - environment: "
            . (isset($config['zatca_environment']) ? (string) $config['zatca_environment'] : 'unknown')
            . ", api_base: " . $api_base
            . ", otp_length: " . strlen((string) $otp)
        );
        
        $resolved = self::resolveTextValue($csr_input);
        self::logZatcaDiagnostic("After resolveTextValue - length: " . strlen($resolved) . ", preview: " . substr($resolved, 0, 100));
        
        $csr = self::normalizeCsr($resolved);
        $csr = self::canonicalizeBase64($csr);
        self::logZatcaDiagnostic("After normalizeCsr - length: " . strlen($csr) . ", preview: " . substr($csr, 0, 100));
        
        // Detect and log the elliptic curve being used
        $curve = self::detectCsrEllipticCurve($resolved);
        self::logZatcaDiagnostic("Detected CSR elliptic curve: " . ($curve ? $curve : 'unknown/not-detected'));
        
        if ($csr === '') {
            $raw_input_trimmed = trim((string) $csr_input);
            $looks_like_path = (
                $raw_input_trimmed !== '' &&
                strpos($raw_input_trimmed, '-----BEGIN') === false &&
                (
                    strpos($raw_input_trimmed, '/') !== false ||
                    strpos($raw_input_trimmed, '\\') !== false ||
                    strpos($raw_input_trimmed, ':') !== false
                )
            );

            $message = 'ZATCA CSR is missing or invalid. Provide CSR text or a readable file path.';
            if ($looks_like_path) {
                $message = 'ZATCA CSR file path could not be read or does not contain a valid PKCS#10 CSR: ' . $raw_input_trimmed;
            }

            self::logZatcaDiagnostic("requestComplianceCsid: CSR is empty after normalization");
            return [
                'success' => false,
                'status_code' => 0,
                'message' => $message,
                'response_body' => '',
            ];
        }

        $client = new Client([
            'base_uri' => $api_base,
            'timeout' => 45,
            'http_errors' => false,
        ]);

        try {
            $csrCandidates = self::buildComplianceCsrCandidates($resolved, $csr);
            $lastStatusCode = 0;
            $lastResponseBody = '';

            foreach ($csrCandidates as $idx => $candidate) {
                $payload_json = json_encode([
                    'csr' => $candidate['value'],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                if ($payload_json === false) {
                    return [
                        'success' => false,
                        'status_code' => 0,
                        'message' => 'Compliance CSID request payload encoding failed.',
                        'response_body' => '',
                    ];
                }

                self::logZatcaDiagnostic(
                    'Compliance attempt #' . ($idx + 1)
                    . ' csr_format=' . $candidate['label']
                    . ' csr_length=' . strlen((string) $candidate['value'])
                    . ' payload_length=' . strlen($payload_json)
                );

                // Log OTP details (partial value for debugging without full exposure)
                $otpDebugMarker = (strlen($otp) > 0)
                    ? (substr($otp, 0, 1) . '*' . substr($otp, -1) . ' (len=' . strlen($otp) . ')')
                    : '(empty)';
                self::logZatcaDiagnostic(
                    'Compliance attempt #' . ($idx + 1)
                    . ' otp_value=' . $otpDebugMarker
                    . ' otp_header_present=true'
                );

                $response = $client->post('compliance', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Accept-Language' => 'en',
                        'Accept-Version' => 'V2',
                        'Content-Type' => 'application/json',
                        'OTP' => $otp,
                    ],
                    'body' => $payload_json,
                ]);

                $status_code = $response->getStatusCode();
                $response_body = (string) $response->getBody();
                $lastStatusCode = (int) $status_code;
                $lastResponseBody = $response_body;
                self::logZatcaDiagnostic(
                    'Compliance attempt #' . ($idx + 1)
                    . ' response_status=' . (int) $status_code
                    . ' response_length=' . strlen((string) $response_body)
                );

                if ((int) $status_code < 200 || (int) $status_code >= 300) {
                    $responseSummary = trim(strip_tags(self::summarizeApiValidationError($response_body)));
                    if ($responseSummary !== '') {
                        $responseSummary = preg_replace('/\s+/', ' ', (string) $responseSummary);
                        if (strlen($responseSummary) > 260) {
                            $responseSummary = substr($responseSummary, 0, 257) . '...';
                        }
                        self::logZatcaDiagnostic(
                            'Compliance attempt #' . ($idx + 1)
                            . ' response_summary=' . $responseSummary
                        );
                    }
                }

                $decoded = json_decode($response_body, true);

                if (!is_array($decoded)) {
                    $decoded = [];
                }

                $binary_token = isset($decoded['binarySecurityToken'])
                    ? trim((string) $decoded['binarySecurityToken'])
                    : '';
                $secret = isset($decoded['secret']) ? trim((string) $decoded['secret']) : '';
                $request_id = self::extractFirstScalarValue($decoded, [
                    'requestID',
                    'requestId',
                    'request_id',
                    'complianceRequestID',
                    'compliance_request_id',
                ]);

                if ($status_code >= 200 && $status_code < 300 && $binary_token !== '' && $secret !== '') {
                    $certificate_pem = self::binaryTokenToPemCertificate($binary_token);

                    self::persistComplianceCredentials([
                        'zatca_binary_security_token' => $binary_token,
                        'zatca_secret' => $secret,
                        'zatca_compliance_csid' => $binary_token,
                        'zatca_compliance_secret' => $secret,
                        'zatca_certificate' => $certificate_pem,
                        'zatca_compliance_request_id' => $request_id,
                        'zatca_compliance_last_response' => $response_body,
                    ]);

                    return [
                        'success' => true,
                        'status_code' => $status_code,
                        'message' => 'Compliance CSID retrieved successfully.',
                        'response_body' => $response_body,
                        'binary_security_token' => $binary_token,
                        'secret' => $secret,
                        'certificate' => $certificate_pem,
                        'request_id' => $request_id,
                    ];
                }

                $isInvalidCsrResponse = (
                    (int) $status_code === 400
                    && stripos($response_body, 'Invalid CSR') !== false
                    && stripos($response_body, 'PKCS10csr') !== false
                );

                $isInvalidOtpResponse = (
                    (int) $status_code === 400
                    && stripos($response_body, 'Invalid-OTP') !== false
                );

                if ($isInvalidOtpResponse) {
                    $environment = isset($config['zatca_environment'])
                        ? strtolower(trim((string) $config['zatca_environment']))
                        : '';
                    $base = strtolower((string) $api_base);
                    $envHint = '';

                    if ($environment === 'production' && strpos($base, '/e-invoicing/core') !== false) {
                        $envHint = ' Ensure OTP is generated from the Production portal (not Simulation/Sandbox) and submitted immediately.';
                    } elseif ($environment === 'simulation' && strpos($base, '/e-invoicing/simulation') !== false) {
                        $envHint = ' Ensure OTP is generated from the Simulation portal and submitted immediately.';
                    } else {
                        $envHint = ' Ensure OTP source matches the selected environment and API base URL.';
                    }

                    $contextHint = ' Endpoint: ' . rtrim((string) $api_base, '/') . '/compliance. Server time: ' . date('Y-m-d H:i:s') . ' UTC.';

                    return [
                        'success' => false,
                        'status_code' => $status_code,
                        'message' => 'Compliance CSID request failed with status code ' . $status_code . ' (Invalid OTP).' . $envHint . $contextHint,
                        'response_body' => $response_body,
                    ];
                }

                if ($isInvalidCsrResponse && ($idx + 1) < count($csrCandidates)) {
                    self::logZatcaDiagnostic(
                        'Compliance attempt #' . ($idx + 1)
                        . ' failed with Invalid CSR. Automatic retry disabled because OTP can be single-use.'
                    );

                    self::persistComplianceCredentials([
                        'zatca_compliance_last_response' => $response_body,
                    ]);

                    return [
                        'success' => false,
                        'status_code' => $status_code,
                        'message' => 'Compliance CSID request failed with status code ' . $status_code . ' (Invalid CSR format). Automatic retry is disabled to avoid consuming OTP in multiple attempts. Regenerate a fresh OTP and retry Step 1 once.',
                        'response_body' => $response_body,
                    ];
                }

                // Handle 409: Compliance transaction already generated (recovery attempt)
                if ((int) $status_code === 409) {
                    self::logZatcaDiagnostic(
                        'Compliance attempt #' . ($idx + 1)
                        . ' returned 409: Compliance transaction already generated. Attempting recovery from database.'
                    );

                    // Try to recover saved credentials from database
                    $saved_token = isset($config['zatca_binary_security_token'])
                        ? trim((string) $config['zatca_binary_security_token'])
                        : '';
                    $saved_secret = isset($config['zatca_secret'])
                        ? trim((string) $config['zatca_secret'])
                        : '';
                    $saved_request_id = isset($config['zatca_compliance_request_id'])
                        ? trim((string) $config['zatca_compliance_request_id'])
                        : '';

                    self::logZatcaDiagnostic('409 Recovery: From config - token=' . (strlen($saved_token) > 0 ? 'YES' : 'NO') . ', secret=' . (strlen($saved_secret) > 0 ? 'YES' : 'NO') . ', request_id=' . (strlen($saved_request_id) > 0 ? 'YES' : 'NO'));

                    // Also try loading from sys_appconfig if not in config
                    if ($saved_token === '' || $saved_secret === '' || $saved_request_id === '') {
                        self::logZatcaDiagnostic('409 Recovery: Missing some credentials from config, querying database...');
                        $appconfig_loaded = self::loadOptionsFromAppConfig();
                        if (is_array($appconfig_loaded)) {
                            self::logZatcaDiagnostic('409 Recovery: Loaded ' . count($appconfig_loaded) . ' keys from database');
                            $saved_token = $saved_token === '' ? (isset($appconfig_loaded['zatca_binary_security_token']) ? trim((string) $appconfig_loaded['zatca_binary_security_token']) : '') : $saved_token;
                            $saved_secret = $saved_secret === '' ? (isset($appconfig_loaded['zatca_secret']) ? trim((string) $appconfig_loaded['zatca_secret']) : '') : $saved_secret;
                            $saved_request_id = $saved_request_id === '' ? (isset($appconfig_loaded['zatca_compliance_request_id']) ? trim((string) $appconfig_loaded['zatca_compliance_request_id']) : '') : $saved_request_id;
                            self::logZatcaDiagnostic('409 Recovery: After merge - token=' . (strlen($saved_token) > 0 ? strlen($saved_token) . ' bytes' : 'NO') . ', secret=' . (strlen($saved_secret) > 0 ? strlen($saved_secret) . ' bytes' : 'NO') . ', request_id=' . (strlen($saved_request_id) > 0 ? 'YES' : 'NO'));
                        }
                    }

                    // Also try option helpers as a final recovery source.
                    if (($saved_token === '' || $saved_secret === '' || $saved_request_id === '') && function_exists('get_option')) {
                        self::logZatcaDiagnostic('409 Recovery: Trying get_option fallback...');

                        if ($saved_token === '') {
                            $saved_token = trim((string) get_option('zatca_binary_security_token'));
                            if ($saved_token === '') {
                                $saved_token = trim((string) get_option('zatca_compliance_csid'));
                            }
                        }

                        if ($saved_secret === '') {
                            $saved_secret = trim((string) get_option('zatca_secret'));
                            if ($saved_secret === '') {
                                $saved_secret = trim((string) get_option('zatca_compliance_secret'));
                            }
                        }

                        if ($saved_request_id === '') {
                            $saved_request_id = trim((string) get_option('zatca_compliance_request_id'));
                            if ($saved_request_id === '') {
                                $saved_request_id = trim((string) get_option('zatca_request_id'));
                            }
                        }

                        self::logZatcaDiagnostic('409 Recovery: get_option result - token=' . (strlen($saved_token) > 0 ? strlen($saved_token) . ' bytes' : 'NO') . ', secret=' . (strlen($saved_secret) > 0 ? strlen($saved_secret) . ' bytes' : 'NO') . ', request_id=' . (strlen($saved_request_id) > 0 ? 'YES' : 'NO'));
                    }

                    self::persistComplianceCredentials([
                        'zatca_compliance_last_response' => $response_body,
                    ]);

                    if ($saved_token !== '' && $saved_secret !== '') {
                        self::logZatcaDiagnostic('409 Recovery: Found saved compliance credentials in database');
                        return [
                            'success' => true,
                            'status_code' => 409,
                            'message' => 'Compliance CSID request returned 409 (already generated for this device). Compliance credentials recovered from database. If errors persist, continue to Step 2 if production credentials exist, or ask portal admin to reset the onboarding device.',
                            'response_body' => $response_body,
                            'binary_security_token' => $saved_token,
                            'secret' => $saved_secret,
                            'request_id' => $saved_request_id,
                        ];
                    } else {
                        self::logZatcaDiagnostic('409 Recovery: FAILED - token=' . (strlen($saved_token) > 0 ? 'YES' : 'NO') . ', secret=' . (strlen($saved_secret) > 0 ? 'YES' : 'NO'));
                        return [
                            'success' => false,
                            'status_code' => 409,
                            'message' => 'ZATCA returned 409: Compliance transaction was already generated before, but no saved compliance credentials were found locally. Do not keep regenerating CSR/OTP for this same onboarding device. Recovery options: (1) Restore previously saved compliance credentials from backup/another environment, (2) If production credentials are already saved, skip Step 1 and continue using Step 2/invoicing, (3) Ask a portal admin to reset/revoke the onboarding device, then onboard fresh.',
                            'response_body' => $response_body,
                        ];
                    }
                }

                self::persistComplianceCredentials([
                    'zatca_compliance_last_response' => $response_body,
                ]);

                return [
                    'success' => false,
                    'status_code' => $status_code,
                    'message' => 'Compliance CSID request failed with status code ' . $status_code,
                    'response_body' => $response_body,
                ];
            }

            self::persistComplianceCredentials([
                'zatca_compliance_last_response' => $lastResponseBody,
            ]);

            return [
                'success' => false,
                'status_code' => $lastStatusCode,
                'message' => 'Compliance CSID request failed with status code ' . $lastStatusCode,
                'response_body' => $lastResponseBody,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Compliance CSID request failed: ' . $e->getMessage(),
                'response_body' => '',
            ];
        }
    }

    public static function runComplianceInvoices($config)
    {
        $api_base = self::resolveApiBase($config);

        $compliance_token = isset($config['zatca_binary_security_token'])
            ? trim($config['zatca_binary_security_token'])
            : '';
        $compliance_secret = isset($config['zatca_secret'])
            ? trim($config['zatca_secret'])
            : '';

        if ($compliance_token === '' || $compliance_secret === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Compliance CSID credentials are required.',
                'response_body' => '',
            ];
        }

        // Prefer a real invoice payload for compliance check. This mirrors production data
        // and avoids false negatives caused by minimal synthetic XML.
        $realInvoiceCheck = self::runComplianceInvoicesUsingRecentInvoice(
            $config,
            $api_base,
            $compliance_token,
            $compliance_secret
        );

        if (!empty($realInvoiceCheck['attempted'])) {
            self::persistComplianceCredentials([
                'zatca_compliance_invoices_last_response' => isset($realInvoiceCheck['response_body'])
                    ? (string) $realInvoiceCheck['response_body']
                    : '',
            ]);

            return [
                'success' => !empty($realInvoiceCheck['success']),
                'status_code' => isset($realInvoiceCheck['status_code']) ? (int) $realInvoiceCheck['status_code'] : 0,
                'message' => isset($realInvoiceCheck['message']) ? (string) $realInvoiceCheck['message'] : 'Compliance invoice check failed.',
                'response_body' => isset($realInvoiceCheck['response_body']) ? (string) $realInvoiceCheck['response_body'] : '',
            ];
        }

        $seller_vat = isset($config['zatca_vat_number']) && $config['zatca_vat_number'] !== ''
            ? $config['zatca_vat_number']
            : (isset($config['CompanyVat']) ? $config['CompanyVat'] : '312345678900003');

        $company = isset($config['CompanyName']) && $config['CompanyName'] !== ''
            ? $config['CompanyName']
            : 'Test Company';

        $uuid = Zatca::generateUuidV4();
        $now  = date('Y-m-d\TH:i:s') . 'Z';
        $date = date('Y-m-d');
        $time = date('H:i:s');

        // Minimal ZATCA-compliant simplified invoice XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"'
            . ' xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"'
            . ' xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">'
            . '<cbc:ProfileID>reporting:1.0</cbc:ProfileID>'
            . '<cbc:ID>Test-001</cbc:ID>'
            . '<cbc:UUID>' . $uuid . '</cbc:UUID>'
            . '<cbc:IssueDate>' . $date . '</cbc:IssueDate>'
            . '<cbc:IssueTime>' . $time . '</cbc:IssueTime>'
            . '<cbc:InvoiceTypeCode name="0200000">388</cbc:InvoiceTypeCode>'
            . '<cbc:DocumentCurrencyCode>SAR</cbc:DocumentCurrencyCode>'
            . '<cbc:TaxCurrencyCode>SAR</cbc:TaxCurrencyCode>'
            . '<cac:AdditionalDocumentReference>'
            . '<cbc:ID>ICV</cbc:ID><cbc:UUID>1</cbc:UUID>'
            . '</cac:AdditionalDocumentReference>'
            . '<cac:AdditionalDocumentReference>'
            . '<cbc:ID>PIH</cbc:ID>'
            . '<cac:Attachment><cbc:EmbeddedDocumentBinaryObject mimeCode="text/plain">'
            . 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjYmUzODYzNzYzMjBhNjc='
            . '</cbc:EmbeddedDocumentBinaryObject></cac:Attachment>'
            . '</cac:AdditionalDocumentReference>'
            . '<cac:AccountingSupplierParty><cac:Party>'
            . '<cac:PartyIdentification><cbc:ID schemeID="VAT">' . htmlspecialchars($seller_vat, ENT_XML1) . '</cbc:ID></cac:PartyIdentification>'
            . '<cac:PostalAddress>'
            . '<cbc:StreetName>Olaya</cbc:StreetName>'
            . '<cbc:CityName>Riyadh</cbc:CityName>'
            . '<cbc:PostalZone>12345</cbc:PostalZone>'
            . '<cac:Country><cbc:IdentificationCode>SA</cbc:IdentificationCode></cac:Country>'
            . '</cac:PostalAddress>'
            . '<cac:PartyTaxScheme><cbc:CompanyID>' . htmlspecialchars($seller_vat, ENT_XML1) . '</cbc:CompanyID>'
            . '<cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme>'
            . '<cac:PartyLegalEntity><cbc:RegistrationName>' . htmlspecialchars($company, ENT_XML1) . '</cbc:RegistrationName></cac:PartyLegalEntity>'
            . '</cac:Party></cac:AccountingSupplierParty>'
            . '<cac:AccountingCustomerParty><cac:Party>'
            . '<cac:PartyLegalEntity><cbc:RegistrationName>Test Customer</cbc:RegistrationName></cac:PartyLegalEntity>'
            . '</cac:Party></cac:AccountingCustomerParty>'
            . '<cac:TaxTotal>'
            . '<cbc:TaxAmount currencyID="SAR">15.00</cbc:TaxAmount>'
            . '<cac:TaxSubtotal>'
            . '<cbc:TaxableAmount currencyID="SAR">100.00</cbc:TaxableAmount>'
            . '<cbc:TaxAmount currencyID="SAR">15.00</cbc:TaxAmount>'
            . '<cac:TaxCategory>'
            . '<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">S</cbc:ID>'
            . '<cbc:Percent>15</cbc:Percent>'
            . '<cac:TaxScheme><cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID></cac:TaxScheme>'
            . '</cac:TaxCategory>'
            . '</cac:TaxSubtotal>'
            . '</cac:TaxTotal>'
            . '<cac:LegalMonetaryTotal>'
            . '<cbc:LineExtensionAmount currencyID="SAR">100.00</cbc:LineExtensionAmount>'
            . '<cbc:TaxExclusiveAmount currencyID="SAR">100.00</cbc:TaxExclusiveAmount>'
            . '<cbc:TaxInclusiveAmount currencyID="SAR">115.00</cbc:TaxInclusiveAmount>'
            . '<cbc:PayableAmount currencyID="SAR">115.00</cbc:PayableAmount>'
            . '</cac:LegalMonetaryTotal>'
            . '<cac:InvoiceLine>'
            . '<cbc:ID>1</cbc:ID>'
            . '<cbc:InvoicedQuantity unitCode="PCE">1</cbc:InvoicedQuantity>'
            . '<cbc:LineExtensionAmount currencyID="SAR">100.00</cbc:LineExtensionAmount>'
            . '<cac:TaxTotal>'
            . '<cbc:TaxAmount currencyID="SAR">15.00</cbc:TaxAmount>'
            . '<cbc:RoundingAmount currencyID="SAR">115.00</cbc:RoundingAmount>'
            . '</cac:TaxTotal>'
            . '<cac:Item>'
            . '<cbc:Name>Test Service</cbc:Name>'
            . '<cac:ClassifiedTaxCategory>'
            . '<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">S</cbc:ID>'
            . '<cbc:Percent>15</cbc:Percent>'
            . '<cac:TaxScheme><cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID></cac:TaxScheme>'
            . '</cac:ClassifiedTaxCategory>'
            . '</cac:Item>'
            . '<cac:Price><cbc:PriceAmount currencyID="SAR">100.00</cbc:PriceAmount></cac:Price>'
            . '</cac:InvoiceLine>'
            . '</Invoice>';

        $invoice_hash = base64_encode(hash('sha256', $xml, true));

        $client = new Client([
            'base_uri' => $api_base,
            'timeout'  => 60,
            'http_errors' => false,
        ]);

        try {
            $response = $client->post('compliance/invoices', [
                'headers' => [
                    'Accept'          => 'application/json',
                    'Accept-Language' => 'en',
                    'Accept-Version'  => 'V2',
                    'Authorization'   => 'Basic ' . base64_encode($compliance_token . ':' . $compliance_secret),
                    'Content-Type'    => 'application/json',
                ],
                'json' => [
                    'invoiceHash' => $invoice_hash,
                    'uuid'        => $uuid,
                    'invoice'     => base64_encode($xml),
                ],
            ]);

            $status_code   = $response->getStatusCode();
            $response_body = (string) $response->getBody();

            self::persistComplianceCredentials([
                'zatca_compliance_invoices_last_response' => $response_body,
            ]);

            if ($status_code === 401) {
                return [
                    'success'       => false,
                    'status_code'   => $status_code,
                    'message'       => 'Compliance invoice check failed with status code 401. Compliance CSID credentials were rejected. Request a fresh Compliance CSID (new OTP) and try again.',
                    'response_body' => $response_body,
                ];
            }

            if ($status_code >= 200 && $status_code < 300) {
                return [
                    'success'       => true,
                    'status_code'   => $status_code,
                    'message'       => 'Compliance invoice check passed (status ' . $status_code . ').',
                    'response_body' => $response_body,
                ];
            }

            return [
                'success'       => false,
                'status_code'   => $status_code,
                'message'       => 'Compliance invoice check failed with status code ' . $status_code,
                'response_body' => $response_body,
            ];
        } catch (Throwable $e) {
            return [
                'success'       => false,
                'status_code'   => 0,
                'message'       => 'Compliance invoice check failed: ' . $e->getMessage(),
                'response_body' => '',
            ];
        }
    }

    protected static function runComplianceInvoicesUsingRecentInvoice($config, $api_base, $compliance_token, $compliance_secret)
    {
        try {
            $recentInvoices = ORM::for_table('sys_invoices')
                ->order_by_desc('id')
                ->limit(20)
                ->find_many();
        } catch (Throwable $e) {
            return [
                'attempted' => false,
                'success' => false,
                'status_code' => 0,
                'message' => 'Unable to load invoices for compliance check: ' . $e->getMessage(),
                'response_body' => '',
            ];
        }

        if (empty($recentInvoices)) {
            return [
                'attempted' => false,
                'success' => false,
                'status_code' => 0,
                'message' => 'No invoices found for compliance check.',
                'response_body' => '',
            ];
        }

        $client = new Client([
            'base_uri' => $api_base,
            'timeout'  => 60,
            'http_errors' => false,
        ]);

        $active_binary_token = trim((string) $compliance_token);
        $vat_from_cert = self::extractVatFromBinaryToken($active_binary_token);
        $sellerVatNumber = Zatca::resolveSellerVatNumber($config);
        if ($vat_from_cert !== '') {
            $sellerVatNumber = $vat_from_cert;
        }

        // Normalize binary token into a valid PEM certificate before local signing.
        $compliance_cert_pem = self::binaryTokenToPemCertificate($compliance_token);

        $requiredScenarios = [
            [
                'key' => 'standard-compliant',
                'label' => 'STANDARD_INVOICE',
                'context' => ['force_resubmit' => true, 'zatca_invoice_type' => 'standard', 'document_type' => 'invoice', 'compliance_mode' => true, 'compliance_certificate' => $compliance_cert_pem],
            ],
            [
                'key' => 'standard-credit-note-compliant',
                'label' => 'STANDARD_CREDIT_NOTE',
                'context' => ['force_resubmit' => true, 'zatca_invoice_type' => 'standard', 'document_type' => 'credit_note', 'compliance_mode' => true, 'compliance_certificate' => $compliance_cert_pem],
            ],
            [
                'key' => 'standard-debit-note-compliant',
                'label' => 'STANDARD_DEBIT_NOTE',
                'context' => ['force_resubmit' => true, 'zatca_invoice_type' => 'standard', 'document_type' => 'debit_note', 'compliance_mode' => true, 'compliance_certificate' => $compliance_cert_pem],
            ],
            [
                'key' => 'simplified-compliant',
                'label' => 'SIMPLIFIED_INVOICE',
                'context' => ['force_resubmit' => true, 'zatca_invoice_type' => 'simplified', 'document_type' => 'invoice', 'compliance_mode' => true, 'compliance_certificate' => $compliance_cert_pem],
            ],
            [
                'key' => 'simplified-credit-note-compliant',
                'label' => 'SIMPLIFIED_CREDIT_NOTE',
                'context' => ['force_resubmit' => true, 'zatca_invoice_type' => 'simplified', 'document_type' => 'credit_note', 'compliance_mode' => true, 'compliance_certificate' => $compliance_cert_pem],
            ],
            [
                'key' => 'simplified-debit-note-compliant',
                'label' => 'SIMPLIFIED_DEBIT_NOTE',
                'context' => ['force_resubmit' => true, 'zatca_invoice_type' => 'simplified', 'document_type' => 'debit_note', 'compliance_mode' => true, 'compliance_certificate' => $compliance_cert_pem],
            ],
        ];

        $coveredScenarios = [];
        $submissionCount = 0;
        $scenarioFailures = [];

        foreach ($requiredScenarios as $scenario) {
            $scenarioKey = $scenario['key'];
            $scenarioLabel = $scenario['label'];
            $scenarioCovered = false;

            foreach ($recentInvoices as $invoice) {
                $invoiceId = isset($invoice['id']) ? (int) $invoice['id'] : 0;
                if ($invoiceId <= 0) {
                    continue;
                }

                $items = ORM::for_table('sys_invoiceitems')
                    ->where('invoiceid', $invoiceId)
                    ->order_by_asc('id')
                    ->find_array();

                if (empty($items)) {
                    continue;
                }

                $customer = ORM::for_table('crm_accounts')->find_one($invoice['userid']);
                $context = $scenario['context'];
                $context['compliance_mode'] = true;
                $context['compliance_certificate'] = $compliance_cert_pem;
                $context['original_invoice'] = $invoice;

                $validation = self::validateInvoiceForSubmission(
                    $invoice,
                    $items,
                    $customer,
                    $config,
                    $sellerVatNumber,
                    $context
                );

                if (empty($validation['success'])) {
                    self::logZatcaDiagnostic(
                        'Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId
                        . ' validation failed: '
                        . (isset($validation['message']) ? (string) $validation['message'] : 'unknown error')
                    );
                    continue;
                }

                self::logZatcaDiagnostic('Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId . ' passed validation');

                $package = self::buildSignedPackage(
                    $invoice,
                    $items,
                    $customer,
                    $config,
                    $sellerVatNumber,
                    $context
                );

                if (empty($package['success'])) {
                    $scenarioFailures[$scenarioKey] = 'Package build failed for invoice #' . $invoiceId . ': '
                        . (isset($package['message']) ? (string) $package['message'] : 'unknown error');
                    self::logZatcaDiagnostic(
                        'Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId
                        . ' package build failed: '
                        . (isset($package['message']) ? (string) $package['message'] : 'unknown error')
                    );
                    continue;
                }

                self::logZatcaDiagnostic(
                    'Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId
                    . ' built signed package. Hash: '
                    . substr((string) $package['invoice_hash'], 0, 32)
                    . '..., UUID: '
                    . (string) $package['uuid']
                );

                $response = $client->post('compliance/invoices', [
                    'headers' => [
                        'Accept'          => 'application/json',
                        'Accept-Language' => 'en',
                        'Accept-Version'  => 'V2',
                        'Authorization'   => 'Basic ' . base64_encode($compliance_token . ':' . $compliance_secret),
                        'Content-Type'    => 'application/json',
                    ],
                    'json' => [
                        'invoiceHash' => $package['invoice_hash'],
                        'uuid'        => $package['uuid'],
                        'invoice'     => $package['invoice_b64'],
                    ],
                ]);

                // The 6 required scenarios x up to 4 invoices each can fire 20+ requests at
                // ZATCA in a few seconds with no spacing, which risks tripping their gateway's
                // rate limiting (seen as a bare 401 with no response body, on every request in
                // the batch at once). A short pause between submissions keeps this well under
                // any reasonable per-second limit without meaningfully slowing the overall check.
                usleep(400000);

                $status_code = (int) $response->getStatusCode();
                $response_body = (string) $response->getBody();

                // A 401 with a completely empty body (no JSON, no message) doesn't match a real
                // credential rejection from ZATCA's app layer, which always includes a JSON
                // error - it matches their gateway throttling a burst of requests. Back off hard
                // and retry this one submission once before treating it as a real failure.
                if ($status_code === 401 && $response_body === '') {
                    self::logZatcaDiagnostic('Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId . ' got empty-body 401 (likely rate limit), retrying once after backoff.');
                    sleep(3);
                    $response = $client->post('compliance/invoices', [
                        'headers' => [
                            'Accept'          => 'application/json',
                            'Accept-Language' => 'en',
                            'Accept-Version'  => 'V2',
                            'Authorization'   => 'Basic ' . base64_encode($compliance_token . ':' . $compliance_secret),
                            'Content-Type'    => 'application/json',
                        ],
                        'json' => [
                            'invoiceHash' => $package['invoice_hash'],
                            'uuid'        => $package['uuid'],
                            'invoice'     => $package['invoice_b64'],
                        ],
                    ]);
                    $status_code = (int) $response->getStatusCode();
                    $response_body = (string) $response->getBody();
                }

                self::logZatcaDiagnostic('Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId . ' submission response status: ' . $status_code);

                if ($status_code >= 200 && $status_code < 300) {
                    $submissionCount++;
                    $coveredScenarios[$scenarioKey] = true;
                    $scenarioCovered = true;
                    self::logZatcaDiagnostic(
                        'Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId
                        . ' submitted successfully (status ' . $status_code . '). Submission #' . $submissionCount . '.'
                    );
                    break;
                }

                $errorSummary = self::summarizeApiValidationError($response_body);
                $scenarioFailures[$scenarioKey] = 'Invoice #' . $invoiceId . ' returned ' . $status_code . ': ' . strip_tags((string) $errorSummary);
                self::logZatcaDiagnostic('Compliance check [' . $scenarioLabel . '] - invoice #' . $invoiceId . ' failed with ' . $status_code . '. Error: ' . $errorSummary);

                // 406 "Submitted before" only counts if response matches this exact scenario.
                if ($status_code === 406 && stripos($errorSummary, 'submitted before') !== false) {
                    $summaryText = strtolower(strip_tags((string) $errorSummary));
                    $matchesScenario = false;

                    switch ($scenarioKey) {
                        case 'standard-compliant':
                            $matchesScenario = strpos($summaryText, 'standard') !== false
                                && strpos($summaryText, 'credit') === false
                                && strpos($summaryText, 'debit') === false;
                            break;

                        case 'standard-credit-note-compliant':
                            $matchesScenario = strpos($summaryText, 'standard') !== false
                                && strpos($summaryText, 'credit') !== false;
                            break;

                        case 'standard-debit-note-compliant':
                            $matchesScenario = strpos($summaryText, 'standard') !== false
                                && strpos($summaryText, 'debit') !== false;
                            break;

                        case 'simplified-compliant':
                            $matchesScenario = strpos($summaryText, 'simplified') !== false
                                && strpos($summaryText, 'credit') === false
                                && strpos($summaryText, 'debit') === false;
                            break;

                        case 'simplified-credit-note-compliant':
                            $matchesScenario = strpos($summaryText, 'simplified') !== false
                                && strpos($summaryText, 'credit') !== false;
                            break;

                        case 'simplified-debit-note-compliant':
                            $matchesScenario = strpos($summaryText, 'simplified') !== false
                                && strpos($summaryText, 'debit') !== false;
                            break;
                    }

                    if ($matchesScenario) {
                        $coveredScenarios[$scenarioKey] = true;
                        $scenarioCovered = true;
                        self::logZatcaDiagnostic('Compliance check [' . $scenarioLabel . '] marked as covered from ZATCA 406 submitted-before response.');
                        break;
                    }
                }
            }

            if (!$scenarioCovered) {
                self::logZatcaDiagnostic('Compliance check [' . $scenarioLabel . '] not covered after scanning available invoices.');
            }
        }

        $missingScenarios = [];
        foreach ($requiredScenarios as $scenario) {
            if (empty($coveredScenarios[$scenario['key']])) {
                $missingScenarios[] = $scenario['key'];
            }
        }

        $complianceEnvironment = isset($config['zatca_environment']) ? $config['zatca_environment'] : 'sandbox';
        foreach ($requiredScenarios as $scenario) {
            $scenarioKey = $scenario['key'];
            $scenarioPassed = !empty($coveredScenarios[$scenarioKey]);
            self::persistComplianceResult(
                $complianceEnvironment,
                $scenarioKey,
                $scenarioPassed ? 'PASS' : 'FAIL',
                null,
                $scenarioPassed ? null : (isset($scenarioFailures[$scenarioKey]) ? $scenarioFailures[$scenarioKey] : 'Not covered by any available invoice.')
            );
        }

        if (empty($missingScenarios)) {
            return [
                'attempted' => true,
                'success' => true,
                'status_code' => 202,
                'message' => 'Compliance invoice check completed - all required compliance scenarios are covered.',
                'response_body' => '',
            ];
        }

        return [
            'attempted' => true,
            'success' => false,
            'status_code' => 400,
            'message' => 'Compliance invoice check incomplete. Missing required scenarios: [' . implode(',', $missingScenarios) . '].',
            'response_body' => json_encode([
                'missing_scenarios' => $missingScenarios,
                'scenario_failures' => $scenarioFailures,
            ]),
        ];
    }

    public static function requestProductionCsid($config)
    {
        $api_base = self::resolveApiBase($config);
        $environment = isset($config['zatca_environment'])
            ? strtolower(trim((string) $config['zatca_environment']))
            : 'sandbox';

        self::logZatcaDiagnostic(
            'requestProductionCsid context - environment: '
            . $environment
            . ', api_base: ' . $api_base
        );

        if ($environment === 'production' && strpos(strtolower((string) $api_base), '/e-invoicing/core/') === false) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Production CSID request blocked: Production environment must use /e-invoicing/core endpoint. Please correct ZATCA API base URL.',
                'response_body' => '',
            ];
        }

        // Use compliance CSID credentials for auth
        $compliance_token = isset($config['zatca_binary_security_token'])
            ? trim($config['zatca_binary_security_token'])
            : '';
        $compliance_secret = isset($config['zatca_secret'])
            ? trim($config['zatca_secret'])
            : '';

        // Normalize accidental whitespace/newlines from copied credentials.
        $compliance_token = preg_replace('/\s+/', '', (string) $compliance_token);
        $compliance_secret = preg_replace('/\s+/', '', (string) $compliance_secret);

        $compliance_request_id = isset($config['zatca_compliance_request_id'])
            ? trim((string) $config['zatca_compliance_request_id'])
            : '';
        if ($compliance_request_id === '' && isset($config['zatca_request_id'])) {
            $compliance_request_id = trim((string) $config['zatca_request_id']);
        }

        if ($compliance_token === '' || $compliance_secret === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Compliance CSID credentials are required before requesting Production CSID.',
                'response_body' => '',
            ];
        }

        if ($compliance_request_id === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Compliance request ID is missing. Please re-run Compliance CSID first.',
                'response_body' => '',
            ];
        }

        $client = new Client([
            'base_uri' => $api_base,
            'timeout' => 45,
            'http_errors' => false,
        ]);

        self::logZatcaDiagnostic(
            'requestProductionCsid REQUEST - endpoint: ' . rtrim($api_base, '/') . '/production/csids'
            . ', compliance_request_id: ' . $compliance_request_id
            . ', compliance_token_len: ' . strlen($compliance_token)
            . ', compliance_token_preview: ' . substr($compliance_token, 0, 24) . '...'
            . ', compliance_secret_len: ' . strlen($compliance_secret)
        );

        try {
            $response = $client->post('production/csids', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en',
                    'Accept-Version' => 'V2',
                    'Authorization' => 'Basic ' . base64_encode($compliance_token . ':' . $compliance_secret),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'compliance_request_id' => $compliance_request_id,
                ],
            ]);

            $status_code = $response->getStatusCode();
            $response_body = (string) $response->getBody();
            $response_headers = [];
            foreach ($response->getHeaders() as $headerName => $headerValues) {
                $response_headers[$headerName] = implode(', ', $headerValues);
            }
            self::logZatcaDiagnostic(
                'requestProductionCsid RESPONSE - status: ' . $status_code
                . ', headers: ' . json_encode($response_headers)
                . ', body: ' . $response_body
            );
            $decoded = json_decode($response_body, true);

            if (!is_array($decoded)) {
                $decoded = [];
            }

            $prod_token = self::extractFirstScalarValue($decoded, [
                'binarySecurityToken',
                'binary_security_token',
                'productionBinarySecurityToken',
                'production_binary_security_token',
                'csid',
                'productionCsid',
                'production_csid',
            ]);
            $prod_secret = self::extractFirstScalarValue($decoded, [
                'secret',
                'productionSecret',
                'production_secret',
            ]);
            $prod_request_id = self::extractFirstScalarValue($decoded, [
                'requestID',
                'requestId',
                'request_id',
                'productionRequestID',
                'production_request_id',
            ]);

            self::persistComplianceCredentials([
                'zatca_production_last_response' => $response_body,
            ]);

            $response_summary = self::summarizeApiValidationError($response_body);

            if ($status_code === 401) {
                return [
                    'success' => false,
                    'status_code' => $status_code,
                    'message' => 'Production CSID request failed with status code 401. Compliance CSID credentials are invalid or expired. Request a new Compliance CSID first, then run Compliance Invoice Check, then request Production CSID. ' . $response_summary,
                    'response_body' => $response_body,
                ];
            }

            if ($status_code >= 200 && $status_code < 300 && $prod_token !== '' && $prod_secret !== '') {
                $prod_certificate_pem = self::binaryTokenToPemCertificate($prod_token);

                self::persistComplianceCredentials([
                    'zatca_production_binary_security_token' => $prod_token,
                    'zatca_production_secret' => $prod_secret,
                    'zatca_production_csid' => $prod_token,
                    'zatca_production_certificate' => $prod_certificate_pem,
                    'zatca_production_request_id' => $prod_request_id,
                    'zatca_production_last_response' => $response_body,
                ]);

                return [
                    'success' => true,
                    'status_code' => $status_code,
                    'message' => 'Production CSID retrieved successfully.',
                    'response_body' => $response_body,
                    'binary_security_token' => $prod_token,
                    'secret' => $prod_secret,
                    'certificate' => $prod_certificate_pem,
                    'request_id' => $prod_request_id,
                ];
            }

            return [
                'success' => false,
                'status_code' => $status_code,
                'message' => 'Production CSID request failed with status code ' . $status_code . ': ' . $response_summary,
                'response_body' => $response_body,
            ];
        } catch (Throwable $e) {
            self::logZatcaDiagnostic('requestProductionCsid EXCEPTION: ' . $e->getMessage());
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Production CSID request failed: ' . $e->getMessage(),
                'response_body' => '',
            ];
        }
    }

    public static function submitInvoiceById($invoiceId, $config, array $context = [])
    {
        $invoice = ORM::for_table('sys_invoices')->find_one($invoiceId);
        if (!$invoice) {
            return [
                'success' => false,
                'message' => 'Invoice not found.',
            ];
        }

        $force_resubmit = !empty($context['force_resubmit']);

        if (!$force_resubmit && self::isInvoiceAlreadyRegistered($invoice)) {
            return [
                'success' => true,
                'message' => 'Invoice is already registered in ZATCA. No re-submission was performed.',
                'status_code' => (int) (isset($invoice['zatca_submission_http_code']) ? $invoice['zatca_submission_http_code'] : 200),
                'response' => isset($invoice['zatca_submission_response']) ? (string) $invoice['zatca_submission_response'] : '',
            ];
        }

        $items = ORM::for_table('sys_invoiceitems')
            ->where('invoiceid', $invoiceId)
            ->order_by_asc('id')
            ->find_array();

        $customer = ORM::for_table('crm_accounts')->find_one($invoice['userid']);

        $active_binary_token = isset($config['zatca_production_binary_security_token']) && trim((string) $config['zatca_production_binary_security_token']) !== ''
            ? trim((string) $config['zatca_production_binary_security_token'])
            : (isset($config['zatca_binary_security_token']) ? trim((string) $config['zatca_binary_security_token']) : '');

        $vat_from_cert = self::extractVatFromBinaryToken($active_binary_token);

        // Keep QR, XML, and submission VAT lookup consistent across the app.
        $sellerVatNumber = Zatca::resolveSellerVatNumber($config);

        if ($vat_from_cert !== '') {
            // Always use the VAT bound to the active certificate for XML, QR, and submission.
            $sellerVatNumber = $vat_from_cert;
        }

        $validation = self::validateInvoiceForSubmission(
            $invoice,
            $items,
            $customer,
            $config,
            $sellerVatNumber,
            $context
        );

        if (!$validation['success']) {
            return $validation;
        }

        $package = self::buildSignedPackage(
            $invoice,
            $items,
            $customer,
            $config,
            $sellerVatNumber,
            $context
        );

        if (!$package['success']) {
            return $package;
        }

        self::persistInvoiceZatcaData($invoiceId, $package['zatca_data']);

        $submission = self::submitToApi($package, $config);

        self::persistSubmissionResult($invoiceId, $package, $submission);

        if ($submission['success']) {
            return [
                'success' => true,
                'message' => 'ZATCA submission completed successfully.',
                'status_code' => $submission['status_code'],
                'response' => $submission['response_body'],
            ];
        }

        return [
            'success' => false,
            'message' => $submission['message'],
            'status_code' => $submission['status_code'],
            'response' => $submission['response_body'],
        ];
    }

    public static function submitInvoice($invoice, $config, array $context = [])
    {
        if (!$invoice) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Invoice is required for ZATCA submission.',
                'response_body' => '',
            ];
        }

        $invoiceId = 0;
        if (is_object($invoice) && isset($invoice->id)) {
            $invoiceId = (int) $invoice->id;
        } elseif (is_array($invoice) && isset($invoice['id'])) {
            $invoiceId = (int) $invoice['id'];
        }

        if ($invoiceId <= 0) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Invalid invoice id for ZATCA submission.',
                'response_body' => '',
            ];
        }

        $result = self::submitInvoiceById($invoiceId, $config, $context);

        return [
            'success' => !empty($result['success']),
            'status_code' => isset($result['status_code']) ? (int) $result['status_code'] : 0,
            'message' => isset($result['message']) ? (string) $result['message'] : 'ZATCA submission failed.',
            'response_body' => isset($result['response']) ? (string) $result['response'] : '',
        ];
    }

    public static function ensureInvoiceState($invoice)
    {
        if (!$invoice || !is_object($invoice)) {
            return false;
        }

        $dirty = false;

        if (Zatca::hasInvoiceColumn('zatca_uuid') && empty($invoice->zatca_uuid)) {
            $invoice->zatca_uuid = Zatca::generateUuidV4();
            $dirty = true;
        }

        if (Zatca::hasInvoiceColumn('zatca_status') && empty($invoice->zatca_status)) {
            $invoice->zatca_status = 'not_submitted';
            $dirty = true;
        }

        if (Zatca::hasInvoiceColumn('zatca_invoice_type') && empty($invoice->zatca_invoice_type)) {
            $invoice->zatca_invoice_type = self::detectInvoiceType($invoice);
            $dirty = true;
        }

        if ($dirty && method_exists($invoice, 'save')) {
            $invoice->save();
        }

        return true;
    }

    public static function markSubmitted($invoiceId, $responsePayload = '', $status = 'submitted')
    {
        $invoice = Invoice::find((int) $invoiceId);

        if (!$invoice) {
            return false;
        }

        if (Zatca::hasInvoiceColumn('zatca_status')) {
            $invoice->zatca_status = (string) $status;
        }

        if (Zatca::hasInvoiceColumn('zatca_last_submit_at')) {
            $invoice->zatca_last_submit_at = date('Y-m-d H:i:s');
        }

        if (Zatca::hasInvoiceColumn('zatca_last_response') && $responsePayload !== '') {
            $invoice->zatca_last_response = (string) $responsePayload;
        }

        $invoice->save();

        return true;
    }

    protected static function detectInvoiceType($invoice)
    {
        $isB2B = false;

        if ($invoice && isset($invoice->userid) && Zatca::hasTableColumn('crm_accounts', 'company')) {
            $contact = ORM::for_table('crm_accounts')->find_one((int) $invoice->userid);
            if ($contact) {
                $company = isset($contact['company']) ? trim((string) $contact['company']) : '';
                $isB2B = $company !== '';
            }
        }

        return $isB2B ? 'standard' : 'simplified';
    }

    public static function submitReturnInvoiceById($returnInvoiceId, $originalInvoiceId, $config)
    {
        $original_invoice = ORM::for_table('sys_invoices')->find_one((int) $originalInvoiceId);
        if (!$original_invoice) {
            return [
                'success' => false,
                'message' => 'Original invoice not found for return submission.',
                'status_code' => 0,
                'response' => '',
            ];
        }

        $original_report = self::buildInvoiceRegistrationReport($original_invoice);
        if (empty($original_report['is_registered'])) {
            return [
                'success' => false,
                'message' => 'Original invoice is not registered in ZATCA. Submit it before creating a return.',
                'status_code' => 0,
                'response' => '',
            ];
        }

        $context = [
            'document_type' => 'credit_note',
            'original_invoice' => $original_invoice,
            'force_resubmit' => false,
            'zatca_invoice_type' => isset($original_invoice['zatca_invoice_type']) && in_array($original_invoice['zatca_invoice_type'], ['standard', 'simplified'], true)
                ? $original_invoice['zatca_invoice_type']
                : 'simplified',
        ];

        return self::submitInvoiceById($returnInvoiceId, $config, $context);
    }

    protected static function isInvoiceAlreadyRegistered($invoice)
    {
        if (!$invoice) {
            return false;
        }

        $report = self::buildInvoiceRegistrationReport($invoice);

        return !empty($report['is_registered']);
    }

    protected static function summarizeApiValidationError($responseBody)
    {
        $responseBody = (string) $responseBody;
        if ($responseBody === '') {
            return 'No response body returned by ZATCA.';
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            $plain = trim(strip_tags($responseBody));
            return $plain !== '' ? $plain : 'Unable to parse ZATCA error response.';
        }

        $messages = [];

        foreach (['message', 'error', 'errorMessage'] as $key) {
            if (isset($decoded[$key]) && trim((string) $decoded[$key]) !== '') {
                $messages[] = (string) $decoded[$key];
            }
        }

        if (isset($decoded['validationResults']) && is_array($decoded['validationResults'])) {
            // Bucket label preserved on the front of every line ([ERROR]/[WARNING]/[INFO]) -
            // ZATCA's own severity classification was previously discarded here, making a
            // genuinely blocking error indistinguishable from a harmless warning (e.g. BR-KSA-80
            // is documented elsewhere in this file as often just a warning on invoices with no
            // PrepaidAmount - collectValidationMessages()/hasOnlyIgnorablePrepaymentWarning()
            // already know this, but this function - used by the compliance-check path - didn't).
            $bucketTags = [
                'errorMessages' => '[ERROR]',
                'warningMessages' => '[WARNING]',
                'infoMessages' => '[INFO]',
            ];
            foreach (['errorMessages', 'warningMessages', 'infoMessages'] as $bucket) {
                if (!isset($decoded['validationResults'][$bucket]) || !is_array($decoded['validationResults'][$bucket])) {
                    continue;
                }

                foreach ($decoded['validationResults'][$bucket] as $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }

                    $code = isset($entry['code']) ? trim((string) $entry['code']) : '';
                    $message = isset($entry['message']) ? trim((string) $entry['message']) : '';
                    $tag = $bucketTags[$bucket];

                    if ($code !== '' && $message !== '') {
                        $messages[] = $tag . ' ' . $code . ': ' . $message;
                    } elseif ($message !== '') {
                        $messages[] = $tag . ' ' . $message;
                    } elseif ($code !== '') {
                        $messages[] = $tag . ' ' . $code;
                    }
                }
            }
        }

        if (empty($messages)) {
            $plain = trim(strip_tags($responseBody));
            return $plain !== '' ? $plain : 'Unable to parse ZATCA validation details.';
        }

        $normalized = [];
        foreach ($messages as $raw_message) {
            $line = trim((string) $raw_message);
            if ($line === '') {
                continue;
            }

            $line = html_entity_decode($line, ENT_QUOTES, 'UTF-8');
            $line = preg_replace('/\s+/', ' ', $line);
            if ($line !== '') {
                $normalized[] = $line;
            }
        }

        $normalized = array_values(array_unique($normalized));
        $normalized = array_slice($normalized, 0, 10);

        $friendly_messages = [];
        $technical_lines = [];

        foreach ($normalized as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $upper_line = strtoupper($line);
            if (strpos($upper_line, 'XSD_ZATCA_VALID') !== false) {
                // This line indicates XML schema compliance and is not an error.
                continue;
            }

            $technical_lines[] = $line;

            $code = '';
            if (preg_match('/^(?:\[(?:ERROR|WARNING|INFO)\]\s*)?([A-Z0-9\-]+)\s*:/', $upper_line, $m)) {
                $code = trim((string) $m[1]);
            } elseif (strpos($upper_line, 'CERTIFICATE-PERMISSIONS') !== false) {
                $code = 'CERTIFICATE-PERMISSIONS';
            }

            // Carry the [ERROR]/[WARNING]/[INFO] severity tag through to the friendly message too -
            // a warning-only BR-KSA-80 (documented elsewhere as a known ZATCA false positive on
            // invoices with no PrepaidAmount) reads very differently from an actual blocking error.
            $severityPrefix = '';
            if (preg_match('/^\[(ERROR|WARNING|INFO)\]\s*/', $line, $sm)) {
                $severityPrefix = '[' . $sm[1] . '] ';
            }
            $lineWithoutSeverity = preg_replace('/^\[(?:ERROR|WARNING|INFO)\]\s*/', '', $line);

            switch ($code) {
                case 'BR-CUSTOM-VALIDATION-01':
                    $friendly_messages[] = $severityPrefix . 'Seller VAT and Buyer VAT cannot be the same. Use the customer\'s VAT number, not your company VAT.';
                    break;
                case 'CERTIFICATE-PERMISSIONS':
                    $friendly_messages[] = $severityPrefix . 'Seller VAT must match the VAT number inside the active ZATCA certificate.';
                    break;
                case 'BR-KSA-80':
                    $friendly_messages[] = $severityPrefix . 'Prepaid amount is inconsistent. Remove prepayment from this invoice or make prepaid taxable amount + prepaid tax equal prepaid total.';
                    break;
                case 'BR-KSA-F-06-C23':
                    $friendly_messages[] = $severityPrefix . 'Buyer street address is required (1 to 127 characters).';
                    break;
                case 'BR-KSA-F-06-C25':
                    $friendly_messages[] = $severityPrefix . 'Buyer city is required (1 to 127 characters).';
                    break;
                case 'BR-KSA-10':
                    $friendly_messages[] = $severityPrefix . 'For non-Saudi buyers, address, city, and country code are required.';
                    break;
                case 'BR-KSA-15':
                    $friendly_messages[] = $severityPrefix . 'Supply date is required for standard tax invoices.';
                    break;
                default:
                    // Keep unknown errors concise and readable.
                    $friendly_messages[] = $severityPrefix . preg_replace('/^([A-Z0-9\-]+)\s*:\s*/', '', $lineWithoutSeverity);
                    break;
            }
        }

        $friendly_messages = array_values(array_unique(array_filter($friendly_messages)));
        $friendly_messages = array_slice($friendly_messages, 0, 6);

        if (empty($friendly_messages) && !empty($technical_lines)) {
            $friendly_messages[] = 'Please review invoice VAT, buyer details, and supply date, then submit again.';
        }

        $friendly_html = '';
        foreach ($friendly_messages as $msg) {
            $friendly_html .= '<li>' . htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        $technical_html = '';
        foreach (array_slice($technical_lines, 0, 4) as $line) {
            $technical_html .= '<li>' . htmlspecialchars((string) $line, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        $output = '<strong>ZATCA submission could not be completed.</strong>';
        $output .= '<div style="margin-top:6px;">Please fix the following and submit again:</div>';
        $output .= '<ul style="margin:6px 0 0 18px; padding:0;">' . $friendly_html . '</ul>';

        if ($technical_html !== '') {
            $output .= '<div style="margin-top:8px; font-size:12px; color:#666;"><strong>Technical reference:</strong></div>';
            $output .= '<ul style="margin:4px 0 0 18px; padding:0; font-size:12px; color:#666;">' . $technical_html . '</ul>';
        }

        return $output;
    }

    protected static function extractFirstScalarValue(array $decoded, array $preferredKeys)
    {
        foreach ($preferredKeys as $key) {
            if (isset($decoded[$key]) && is_scalar($decoded[$key])) {
                $value = trim((string) $decoded[$key]);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        $stack = [$decoded];
        while (!empty($stack)) {
            $current = array_pop($stack);
            if (!is_array($current)) {
                continue;
            }

            foreach ($current as $k => $v) {
                if (is_array($v)) {
                    $stack[] = $v;
                    continue;
                }

                if (!is_scalar($v)) {
                    continue;
                }

                foreach ($preferredKeys as $preferred) {
                    if (strcasecmp((string) $k, (string) $preferred) === 0) {
                        $value = trim((string) $v);
                        if ($value !== '') {
                            return $value;
                        }
                    }
                }
            }
        }

        return '';
    }

    protected static function buildSignedPackage(
        $invoice,
        array $items,
        $customer,
        $config,
        $sellerVatNumber,
        array $context = []
    ) {
        $zatca_invoice_type = isset($context['zatca_invoice_type']) && in_array($context['zatca_invoice_type'], ['standard', 'simplified'], true)
            ? $context['zatca_invoice_type']
            : (isset($invoice['zatca_invoice_type']) && in_array($invoice['zatca_invoice_type'], ['standard', 'simplified'], true)
                ? $invoice['zatca_invoice_type']
                : (isset($config['zatca_invoice_type']) && in_array($config['zatca_invoice_type'], ['standard', 'simplified'], true)
                    ? $config['zatca_invoice_type']
                    : 'simplified'));

        $zatca_data = self::prepareInvoiceZatcaData(
            $invoice,
            $config['CompanyName'],
            $sellerVatNumber,
            $invoice['total'],
            $invoice['tax']
        );

        $zatca_environment = isset($config['zatca_environment']) ? $config['zatca_environment'] : 'sandbox';
        $document_type = isset($context['document_type']) ? strtolower(trim((string) $context['document_type'])) : 'invoice';

        // Real clearance/reporting submissions need the actual per-environment ICV/PIH chain.
        // Compliance-mode checks replay historical invoices out of chronological order against
        // the sandbox compliance/invoices endpoint - they are isolated conformance tests, not
        // part of the live chain, so they keep ICV=1 + the genesis PIH (buildInvoiceXml()'s
        // defaults) instead of consuming/perturbing the real sequence.
        if (empty($context['compliance_mode'])) {
            $icv_result = self::acquireNextIcv($zatca_environment);
            if (empty($icv_result['success'])) {
                return [
                    'success' => false,
                    'message' => $icv_result['message'],
                ];
            }
            $context['icv'] = $icv_result['icv'];
            $context['pih'] = self::getPreviousInvoiceHash($zatca_environment);
        }

        // Compliance checks reuse real historical invoices (often weeks/months old) to exercise
        // all 6 required scenarios. ZATCA's production endpoint enforces BR-KSA-98 for simplified
        // invoices ("must be submitted within 24 hours of issuing"), which real production/sandbox
        // testing environments are lenient about but production genuinely checks. Backdate the
        // whole signing timestamp to "now" for compliance-mode submissions only, so IssueDate/
        // IssueTime/SigningTime/QR-tag3/main-hash are all internally consistent AND fresh, instead
        // of tripping timing-based rules that have nothing to do with actual signature correctness.
        if (!empty($context['compliance_mode'])) {
            $zatca_data['timestamp'] = date('c');
        }

        $xml = self::buildInvoiceXml(
            $invoice,
            $items,
            $customer,
            $config,
            $sellerVatNumber,
            $zatca_data['uuid'],
            $zatca_data['timestamp'],
            $context,
            $zatca_invoice_type
        );

        $xml = self::stripPrepaymentNodes($xml);

        // API-only mode for shared hosting: do not depend on local Java/SDK runtime.
        $sdk_package = self::buildApiSignedPackage($xml, $zatca_data['uuid'], $config, $context);
        if (!$sdk_package['success']) {
            return [
                'success' => false,
                'message' => $sdk_package['message'],
            ];
        }

        $invoice_hash = $sdk_package['invoice_hash'];
        $xml = $sdk_package['invoice_xml'];

        $signature = '';
        $public_key = '';
        $ca_signature = '';

        $pem_private = self::resolvePemValue(
            isset($config['zatca_private_key']) ? $config['zatca_private_key'] : ''
        , false);
        $is_compliance_mode = !empty($context['compliance_mode']);
        if ($is_compliance_mode && !empty($context['compliance_certificate'])) {
            // For compliance invoice checks, certificate must match compliance CSID context.
            $pem_certificate = self::resolvePemValue($context['compliance_certificate']);
        } else {
            // Prefer production certificate for production/reporting submission.
            $pem_certificate = self::resolvePemValue(
                isset($config['zatca_production_certificate']) && trim((string) $config['zatca_production_certificate']) !== ''
                    ? $config['zatca_production_certificate']
                    : (isset($config['zatca_certificate']) ? $config['zatca_certificate'] : '')
            );
        }
        $passphrase = isset($config['zatca_private_key_passphrase'])
            ? $config['zatca_private_key_passphrase']
            : '';

        if ($pem_private !== '') {
            $signature_result = self::signHash($invoice_hash, $pem_private, $passphrase);

            if (!$signature_result['success']) {
                return [
                    'success' => false,
                    'message' => $signature_result['message'],
                ];
            }

            $signature = $signature_result['signature'];
        }

        if ($pem_certificate !== '') {
            // QR tag 8 expects the full X.509 SubjectPublicKeyInfo DER (same bytes Java's
            // PublicKey.getEncoded() returns) - not the bare EC point. Tag 9 is the raw
            // CA signature bytes out of the certificate's own ASN.1 structure.
            $public_key_handle = openssl_pkey_get_public($pem_certificate);
            $public_key_details = $public_key_handle ? openssl_pkey_get_details($public_key_handle) : false;
            if (is_resource($public_key_handle) || $public_key_handle instanceof \OpenSSLAsymmetricKey) {
                @openssl_pkey_free($public_key_handle);
            }
            if ($public_key_details && isset($public_key_details['key'])) {
                $public_key = (string) base64_decode(
                    preg_replace('/-----BEGIN PUBLIC KEY-----|-----END PUBLIC KEY-----|\s+/', '', (string) $public_key_details['key']),
                    true
                );
            }

            $cert_b64_for_qr = preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/', '', $pem_certificate);
            $cert_der_for_qr = base64_decode($cert_b64_for_qr, true);
            if ($cert_der_for_qr !== false) {
                $ca_signature = self::extractCertificateSignatureBytes($cert_der_for_qr);
            }
        }

        // DEBUG: Log the condition values
        self::logZatcaDiagnostic('buildSignedPackage DEBUG: is_compliance_mode=' . ($is_compliance_mode ? 'true' : 'false') . ', signature_len=' . strlen((string) $signature) . ', pem_cert_len=' . strlen((string) $pem_certificate));

        // Inject full XML-DSig cryptographic stamp into UBLExtensions. This must happen for
        // EVERY submission (compliance checks AND real clearance/reporting) - ZATCA requires
        // the KSA-15 stamp on the actual invoice, not just on compliance test scenarios. It was
        // previously gated on $is_compliance_mode, which meant real submissions never got a
        // ds:Signature/cac:Signature block at all; that went unnoticed for standard invoices
        // (BR-KSA-28/29/30/60 are simplified-only rules) but broke every simplified submission.
        if (!empty($signature) && !empty($pem_certificate)) {
            self::logZatcaDiagnostic('buildSignedPackage: INJECTING signature into XML');
            // $signature is already base64 text (signHash() encodes it); injectXmlDsigSignature
            // expects raw bytes and base64-encodes internally, so decode here to avoid
            // double-encoding ds:SignatureValue into cryptographic garbage.
            $xml = self::injectXmlDsigSignature($xml, base64_decode($signature), $pem_certificate, $zatca_data['timestamp'], $invoice_hash);
            // Also ensure cac:Signature metadata node exists
            $xml = self::injectComplianceSignatureMetadata($xml);
        } else {
            self::logZatcaDiagnostic('buildSignedPackage: SKIPPING signature injection - condition failed');
        }

        $zatca_data['hash'] = $invoice_hash;
        $zatca_data['signature'] = $signature;
        $zatca_data['public_key'] = $public_key;
        $zatca_data['ca_signature'] = $ca_signature;

        // KSA-25 requires the QR's timestamp to match the invoice's issue date/time EXACTLY -
        // confirmed against a ZATCA-accepted reference sample, tag 3 is built by reading back
        // cbc:IssueDate + "T" + cbc:IssueTime verbatim (no 'Z', no timezone conversion), not by
        // independently reformatting some other timestamp value. buildInvoiceXml() now builds
        // BOTH elements from $zatca_data['timestamp'] alone (not $invoice['date'], which can
        // silently diverge when an invoice's date/datepaid columns differ) - mirror that exact
        // single-source construction here so the strings can never drift apart.
        $qr_timestamp = date('Y-m-d', strtotime((string) $zatca_data['timestamp']))
            . 'T' . date('H:i:s', strtotime((string) $zatca_data['timestamp']));

        $zatca_data['tlv'] = self::buildBase64TlvCompat([
            1 => (string) $config['CompanyName'],
            2 => (string) $sellerVatNumber,
            3 => $qr_timestamp,
            4 => Zatca::formatAmount($invoice['total']),
            5 => Zatca::formatAmount($invoice['tax']),
            6 => $invoice_hash,
            7 => $signature,
            8 => $public_key,
            9 => $ca_signature,
        ]);

        self::logZatcaDiagnostic(
            'buildSignedPackage QR TLV DEBUG: tlv_len=' . strlen((string) $zatca_data['tlv'])
            . ', hash_len=' . strlen((string) $invoice_hash)
            . ', signature_len=' . strlen((string) $signature)
            . ', public_key_len=' . strlen((string) $public_key)
            . ', ca_signature_len=' . strlen((string) $ca_signature)
        );

        // buildInvoiceXml() only had tags 1-5 available (no signature/cert yet existed
        // at that point), so the QR node it wrote is a stub. ZATCA requires the full
        // cryptographic stamp (tags 6-9) in the QR, so swap the stub payload for the
        // complete TLV now that signing is done.
        $qr_replacement_count = 0;
        if ($zatca_data['tlv'] !== '') {
            $xml = preg_replace_callback(
                '/(<cbc:ID>QR<\/cbc:ID><cac:Attachment><cbc:EmbeddedDocumentBinaryObject mimeCode="text\/plain">)[^<]*(<\/cbc:EmbeddedDocumentBinaryObject>)/',
                function ($m) use ($zatca_data) {
                    return $m[1] . $zatca_data['tlv'] . $m[2];
                },
                $xml,
                1,
                $qr_replacement_count
            );
        }
        self::logZatcaDiagnostic('buildSignedPackage QR replacement count: ' . $qr_replacement_count);

        // TEMPORARY: full-XML dump (post-QR-swap, i.e. the TRUE final submitted XML) for
        // simplified-invoice diffing against the passing standard invoice XML. Revert to the
        // 300-char preview once the remaining signed-properties-hashing bundle on simplified
        // invoices is root-caused.
        $xml_preview = (!empty($context['zatca_invoice_type']) && $context['zatca_invoice_type'] === 'simplified')
            ? $xml
            : substr($xml, 0, 300);
        self::logZatcaDiagnostic('buildSignedPackage FINAL XML PREVIEW: ' . str_replace(["\n", "\r"], " ", $xml_preview));

        return [
            'success' => true,
            'invoice_id' => $invoice['id'],
            'uuid' => $zatca_data['uuid'],
            'invoice_hash' => $invoice_hash,
            'invoice_xml' => $xml,
            'invoice_b64' => base64_encode($xml),
            'zatca_data' => $zatca_data,
            'zatca_invoice_type' => $zatca_invoice_type,
            'zatca_environment' => $zatca_environment,
            'document_type' => $document_type,
            'icv' => isset($context['icv']) ? (int) $context['icv'] : null,
            'is_compliance_mode' => !empty($context['compliance_mode']),
        ];
    }

    protected static function injectXmlDsigSignature($xml, $signatureBytes, $certificate, $timestamp, $invoiceHash)
    {
        $xml = (string) $xml;
        self::logZatcaDiagnostic('injectXmlDsigSignature START: XML len=' . strlen($xml));
        
        if ($xml === '' || $signatureBytes === '' || $certificate === '' || $timestamp === '' || $invoiceHash === '') {
            self::logZatcaDiagnostic('injectXmlDsigSignature: EARLY RETURN - missing parameter (xml=' . strlen((string) $xml) . ', sig=' . strlen((string) $signatureBytes) . ', cert=' . strlen((string) $certificate) . ')');
            return $xml;
        }
        if (empty($xml) || empty($signatureBytes) || empty($certificate)) {
            return $xml;
        }

        // Build the complete XML-DSig signature structure
        $signatureValue = base64_encode($signatureBytes);
        
        // Extract pure base64 from PEM certificate - remove all headers and whitespace
        $certificateB64 = preg_replace('#-----BEGIN CERTIFICATE-----(.+)-----END CERTIFICATE-----#s', '$1', $certificate);
        $certificateB64 = preg_replace('/\s+/', '', $certificateB64);
        
        // Parse certificate to extract issuer and serial number.
        // openssl_x509_parse() expects a PEM certificate/resource here; passing DER bytes
        // causes this method to return the original XML without injecting the signature.
        $certDerBinary = base64_decode($certificateB64);
        $certificateResource = @openssl_x509_read($certificate);
        $certData = $certificateResource ? @openssl_x509_parse($certificateResource) : false;

        if (is_resource($certificateResource) || $certificateResource instanceof \OpenSSLCertificate) {
            @openssl_x509_free($certificateResource);
        }

        if ($certData === false || !is_array($certData)) {
            self::logZatcaDiagnostic('injectXmlDsigSignature: certificate parse failed');
            return $xml;
        }
        
        // Get issuer DN (use the issuer array to build the DN string)
        $issuerDn = '';
        if (isset($certData['issuer']) && is_array($certData['issuer'])) {
            $parts = [];
            foreach ($certData['issuer'] as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $parts[] = $key . '=' . $v;
                    }
                } else {
                    $parts[] = $key . '=' . $value;
                }
            }
            // Reference (ZATCA-accepted) implementations build this as an RFC4514 DN then
            // respace it as ", " between RDNs - ZATCA recomputes the SignedProperties digest
            // itself from the certificate and expects that exact spacing, not bare commas.
            $issuerDn = implode(', ', array_reverse($parts));
        }
        
        // X509SerialNumber is typed as integer in XML-DSig, so prefer the decimal
        // serial number and do not emit the hexadecimal representation.
        $serialNumber = isset($certData['serialNumber']) ? preg_replace('/\D+/', '', (string) $certData['serialNumber']) : '';
        
        // Digest of the main invoice content - invoice hash is already base64-encoded from calculateInvoiceHash
        // Use it directly as the digest value
        $mainDocumentDigest = (string) $invoiceHash;
        
        // Certificate digest - ZATCA's actual (non-standard) expectation, confirmed against a
        // ZATCA-accepted reference sample: base64(hex(sha256(...))) - i.e. hash the certificate's
        // base64 TEXT (the same ASCII string placed in ds:X509Certificate), take the hex digest,
        // then base64-encode that hex STRING. Not base64(raw sha256 bytes) and not over DER bytes.
        $certificateDigest = base64_encode(hash('sha256', $certificateB64));

        // Format timestamp for XAdES - per ZATCA samples, NO Z suffix or timezone
        // Just ISO 8601 datetime format: YYYY-MM-DDTHH:mm:ss
        if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/', $timestamp)) {
            // Remove timezone info and milliseconds, keep only datetime
            $xadesTimestamp = preg_replace('/([T ]\d{2}:\d{2}:\d{2}).*$/', '$1', $timestamp);
            $xadesTimestamp = str_replace(' ', 'T', $xadesTimestamp);
        } else {
            // Parse and reformat without timezone
            $ts = strtotime($timestamp);
            $xadesTimestamp = date('Y-m-d\TH:i:s', $ts);
        }
        
        // ZATCA's own validator (decompiled from the official zatca-einvoicing-sdk jar,
        // com.zatca.sdk.service.validation.signature.SignatureValidator) extracts this element
        // with dom4j XPath and calls Node.asXML() on it, then hashes with
        // base64(hex(sha256(...))) - dom4j's asXML() uses its compact output format: no
        // indentation whitespace, self-closing empty elements, and a namespace declaration
        // re-emitted on every element that introduces a prefix not already in scope on an
        // ANCESTOR WITHIN THIS EXTRACTED SUBTREE (siblings don't inherit from each other) -
        // hence xmlns:ds is repeated on each of the four ds: leaves below rather than declared
        // once. This must reproduce that exact dom4j serialization, not just be self-consistent
        // with whatever we happen to embed.
        $xadesPropertiesXml = '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">'
            . '<xades:SignedSignatureProperties>'
            . '<xades:SigningTime>' . $xadesTimestamp . '</xades:SigningTime>'
            . '<xades:SigningCertificate>'
            . '<xades:Cert>'
            . '<xades:CertDigest>'
            . '<ds:DigestMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></ds:DigestMethod>'
            . '<ds:DigestValue xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . $certificateDigest . '</ds:DigestValue>'
            . '</xades:CertDigest>'
            . '<xades:IssuerSerial>'
            . '<ds:X509IssuerName xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . htmlspecialchars($issuerDn, ENT_XML1, 'UTF-8') . '</ds:X509IssuerName>'
            . '<ds:X509SerialNumber xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . htmlspecialchars($serialNumber, ENT_XML1, 'UTF-8') . '</ds:X509SerialNumber>'
            . '</xades:IssuerSerial>'
            . '</xades:Cert>'
            . '</xades:SigningCertificate>'
            . '</xades:SignedSignatureProperties>'
            . '</xades:SignedProperties>';

        // Confirmed via bytecode: EcryptionUtils.hashString() returns hex(sha256(x)), and the
        // validator does Base64.getEncoder().encodeToString(hashString(nodeXml).getBytes(UTF_8))
        // - i.e. base64 of the HEX STRING, not base64 of the raw digest bytes.
        $xadesSignedPropertiesDigest = base64_encode(hash('sha256', $xadesPropertiesXml));

        $ds_signature = <<<XMLSIG
<ext:UBLExtension>
    <ext:ExtensionURI>urn:oasis:names:specification:ubl:dsig:enveloped:xades</ext:ExtensionURI>
    <ext:ExtensionContent>
        <sig:UBLDocumentSignatures xmlns:sig="urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2" xmlns:sac="urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2" xmlns:sbc="urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2">
            <sac:SignatureInformation>
                <cbc:ID>urn:oasis:names:specification:ubl:signature:1</cbc:ID>
                <sbc:ReferencedSignatureID>urn:oasis:names:specification:ubl:signature:Invoice</sbc:ReferencedSignatureID>
                <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="signature">
                    <ds:SignedInfo>
                        <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>
                        <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#ecdsa-sha256"/>
                        <ds:Reference Id="invoiceSignedData" URI="">
                            <ds:Transforms>
                                <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
                                    <ds:XPath>not(//ancestor-or-self::ext:UBLExtensions)</ds:XPath>
                                </ds:Transform>
                                <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
                                    <ds:XPath>not(//ancestor-or-self::cac:Signature)</ds:XPath>
                                </ds:Transform>
                                <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
                                    <ds:XPath>not(//ancestor-or-self::cac:AdditionalDocumentReference[cbc:ID='QR'])</ds:XPath>
                                </ds:Transform>
                                <ds:Transform Algorithm="http://www.w3.org/2006/12/xml-c14n11"/>
                            </ds:Transforms>
                            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                            <ds:DigestValue>{$mainDocumentDigest}</ds:DigestValue>
                        </ds:Reference>
                        <ds:Reference URI="#xadesSignedProperties" Type="http://www.w3.org/2000/09/xmldsig#SignatureProperties">
                            <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                            <ds:DigestValue>{$xadesSignedPropertiesDigest}</ds:DigestValue>
                        </ds:Reference>
                    </ds:SignedInfo>
                    <ds:SignatureValue>{$signatureValue}</ds:SignatureValue>
                    <ds:KeyInfo>
                        <ds:X509Data>
                            <ds:X509Certificate>{$certificateB64}</ds:X509Certificate>
                        </ds:X509Data>
                    </ds:KeyInfo>
                    <ds:Object>
                        <xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Target="signature">
{$xadesPropertiesXml}
                        </xades:QualifyingProperties>
                    </ds:Object>
                </ds:Signature>
            </sac:SignatureInformation>
        </sig:UBLDocumentSignatures>
    </ext:ExtensionContent>
</ext:UBLExtension>
XMLSIG;

        // Substitute template placeholders with actual values
        $ds_signature_before = $ds_signature;
        $ds_signature = str_replace(
            ['{$mainDocumentDigest}', '{$xadesSignedPropertiesDigest}', '{$signatureValue}', '{$certificateB64}', '{$xadesPropertiesXml}'],
            [$mainDocumentDigest, $xadesSignedPropertiesDigest, $signatureValue, $certificateB64, $xadesPropertiesXml],
            $ds_signature
        );
        self::logZatcaDiagnostic('injectXmlDsigSignature: template substitution - before=' . strlen($ds_signature_before) . ', after=' . strlen($ds_signature) . ', has placeholders after=' . (strpos($ds_signature, '{$') !== false ? 'yes' : 'no'));

        // If UBLExtensions doesn't exist, create it
        if (stripos($xml, '<ext:UBLExtensions>') === false) {
            $xml = preg_replace(
                '/<Invoice[^>]*>/i',
                '$0<ext:UBLExtensions>' . $ds_signature . '</ext:UBLExtensions>',
                $xml,
                1
            );
        } else {
            // Insert into existing UBLExtensions before closing tag
            $xml = preg_replace(
                '/<\/ext:UBLExtensions>/i',
                $ds_signature . '</ext:UBLExtensions>',
                $xml,
                1
            );
        }

        self::logZatcaDiagnostic('injectXmlDsigSignature DONE: XML len=' . strlen($xml) . ', has UBLExtensions=' . (stripos($xml, '<ext:UBLExtensions>') !== false ? 'yes' : 'no') . ', has ds:Signature=' . (stripos($xml, '<ds:Signature') !== false ? 'yes' : 'no'));
        return $xml;
    }

    protected static function injectComplianceSignatureMetadata($xml)
    {
        $xml = (string) $xml;
        if ($xml === '' || stripos($xml, '<cac:Signature>') !== false) {
            return $xml;
        }

        // Inject cac:Signature metadata node (before cac:AccountingSupplierParty)
        // Per ZATCA samples, metadata ID should be "Invoice" not :1
        $signatureMetadata = '<cac:Signature>'
            . '<cbc:ID>urn:oasis:names:specification:ubl:signature:Invoice</cbc:ID>'
            . '<cbc:SignatureMethod>urn:oasis:names:specification:ubl:dsig:enveloped:xades</cbc:SignatureMethod>'
            . '</cac:Signature>';

        $inserted = 0;
        $xml = preg_replace(
            '/<cac:AccountingSupplierParty>/i',
            $signatureMetadata . '<cac:AccountingSupplierParty>',
            $xml,
            1,
            $inserted
        );

        if ((int) $inserted === 0) {
            // If AccountingSupplierParty not found, append before closing Invoice tag
            $xml = preg_replace(
                '/<\/Invoice>/i',
                $signatureMetadata . '</Invoice>',
                $xml,
                1
            );
        }

        return $xml;
    }

    const ZATCA_GENESIS_PIH = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjYmUzODYzNzYzMjBhNjc=';

    const REQUIRED_COMPLIANCE_SCENARIOS = [
        'standard-compliant',
        'standard-credit-note-compliant',
        'standard-debit-note-compliant',
        'simplified-compliant',
        'simplified-credit-note-compliant',
        'simplified-debit-note-compliant',
    ];

    protected static function buildInvoiceXml(
        $invoice,
        array $items,
        $customer,
        $config,
        $sellerVatNumber,
        $uuid,
        $timestamp,
        array $context = [],
        $zatcaInvoiceType = 'simplified'
    ) {
        $invoice_number = self::buildInvoiceNumberCompat($invoice);
        // ICV/PIH must come from the real per-environment chain for live clearance/reporting
        // submissions (see buildSignedPackage(), which populates $context['icv']/['pih'] via
        // acquireNextIcv()/getPreviousInvoiceHash()). Compliance-mode checks (isolated conformance
        // tests replaying historical invoices out of order) intentionally keep ICV=1 and the
        // genesis PIH - falling back to that here covers both that case and any other caller.
        $icv = isset($context['icv']) ? (string) ((int) $context['icv']) : (string) ((int) $invoice['id']);
        $pih = isset($context['pih']) && trim((string) $context['pih']) !== ''
            ? trim((string) $context['pih'])
            : self::ZATCA_GENESIS_PIH;

        $document_type = isset($context['document_type'])
            ? strtolower(trim((string) $context['document_type']))
            : 'invoice';

        $is_standard_invoice = strtolower(trim((string) $zatcaInvoiceType)) === 'standard';
        $invoice_type_name = $is_standard_invoice ? '0100000' : '0200000';
        $invoice_type_code = '388';
        if ($document_type === 'credit_note') {
            $invoice_type_code = '381';
        } elseif ($document_type === 'debit_note') {
            $invoice_type_code = '383';
        }

        $currency_code = 'SAR';
        if (!empty($invoice['currency_symbol']) && strlen($invoice['currency_symbol']) === 3) {
            $currency_code = strtoupper($invoice['currency_symbol']);
        }

        $subtotal_amount = round((float) $invoice['subtotal'], 2);
        $invoice_tax_total = round((float) $invoice['tax'], 2);
        $invoice_total = round((float) $invoice['total'], 2);
        $prepaid_amount = 0.0;
        $emit_prepayment = false;

        $taxable_subtotal = 0.0;
        $has_taxable_items = false;
        foreach ($items as $item) {
            $item_is_taxable = !isset($item['taxed']) || (string) $item['taxed'] !== '0';
            if ($item_is_taxable) {
                $has_taxable_items = true;
                $taxable_subtotal += round((float) $item['total'], 2);
            }
        }

        $tax_rate = round((float) $invoice['taxrate'], 2);
        if ($subtotal_amount > 0 && $invoice_tax_total > 0 && $tax_rate <= 0) {
            $tax_rate = round(($invoice_tax_total / $subtotal_amount) * 100, 2);
        }

        if ($tax_rate <= 0 && $has_taxable_items) {
            $default_tax = ORM::for_table('sys_tax')->find_one();
            if ($default_tax && isset($default_tax['rate']) && (float) $default_tax['rate'] > 0) {
                $tax_rate = round((float) $default_tax['rate'], 2);
            }
        }

        if ($invoice_tax_total <= 0 && $tax_rate > 0 && $taxable_subtotal > 0) {
            $invoice_tax_total = round($taxable_subtotal * ($tax_rate / 100), 2);
        }

        $expected_invoice_total = round($subtotal_amount + $invoice_tax_total, 2);
        if ($invoice_total <= 0 || ($has_taxable_items && $tax_rate > 0 && abs($invoice_total - $expected_invoice_total) > 0.01)) {
            $invoice_total = $expected_invoice_total;
        }

        $payable_amount = round(max($invoice_total - $prepaid_amount, 0), 2);

        $tax_category_code = $invoice_tax_total > 0 ? 'S' : 'O';
        $tax_exemption_xml = '';

        if ($tax_category_code !== 'S') {
            $tax_exemption_xml = '<cbc:TaxExemptionReasonCode>VATEX-SA-OOS</cbc:TaxExemptionReasonCode>'
                . '<cbc:TaxExemptionReason>Not subject to VAT</cbc:TaxExemptionReason>';
        }

        $buyer_name = '';
        $buyer_vat = '';
        $buyer_id_value = '';
        $buyer_id_scheme = 'CRN';
        $buyer_street = '';
        $buyer_building = '';
        $buyer_district = '';
        $buyer_city = '';
        $buyer_postal = '';
        $buyer_country = 'SA';

        $seller_profile = self::resolveSellerProfileFromConfig($config, $sellerVatNumber);
        $seller_crn = $seller_profile['crn'];
        $seller_street = $seller_profile['street'];
        $seller_building = $seller_profile['building_number'];
        $seller_district = $seller_profile['district'];
        $seller_city = $seller_profile['city'];
        $seller_postal = $seller_profile['postal_zone'];
        $seller_country = $seller_profile['country_code'];

        if ($customer) {
            $buyer_name = $customer['account'];
            $buyer_street = isset($customer['address']) ? trim((string) $customer['address']) : '';
            $buyer_district = isset($customer['state']) ? trim((string) $customer['state']) : '';
            $buyer_city = isset($customer['city']) ? trim((string) $customer['city']) : '';
            $buyer_postal = isset($customer['zip']) ? trim((string) $customer['zip']) : '';
            $buyer_country = self::normalizeCountryCode(isset($customer['country']) ? $customer['country'] : '');

            if (!empty($customer['tax_number'])) {
                $buyer_vat = $customer['tax_number'];
            }

            if (!empty($customer['entity_number'])) {
                $buyer_id_value = $customer['entity_number'];
                $buyer_id_scheme = 'CRN';
            } elseif (!empty($customer['id_iqama'])) {
                $buyer_id_value = $customer['id_iqama'];
                $buyer_id_scheme = 'IQA';
            }

            if (!$buyer_vat || !$buyer_id_value || $buyer_building === '') {
                $linked_company = self::resolveLinkedCompanyForCustomer($customer);
                if ($linked_company) {
                    if (!$buyer_vat && Zatca::hasTableColumn('sys_companies', 'vat_number') && isset($linked_company['vat_number'])) {
                        $buyer_vat = (string) $linked_company['vat_number'];
                    }

                    if (!$buyer_id_value && Zatca::hasTableColumn('sys_companies', 'crn_number') && isset($linked_company['crn_number'])) {
                        $buyer_id_value = (string) $linked_company['crn_number'];
                        $buyer_id_scheme = 'CRN';
                    }

                    if ($buyer_street === '' && isset($linked_company['address1'])) {
                        $buyer_street = trim((string) $linked_company['address1']);
                    }

                    if ($buyer_building === '' && Zatca::hasTableColumn('sys_companies', 'building_number') && isset($linked_company['building_number'])) {
                        $buyer_building = preg_replace('/\D+/', '', self::normalizeArabicDigits((string) $linked_company['building_number']));
                    }

                    if ($buyer_building === '' && isset($linked_company['address1'])) {
                        $buyer_building = self::extractBuildingNumber((string) $linked_company['address1']);
                        if ($buyer_building === '0000') {
                            $buyer_building = '';
                        }
                    }

                    if ($buyer_district === '' && isset($linked_company['state'])) {
                        $buyer_district = trim((string) $linked_company['state']);
                    }

                    if ($buyer_city === '' && isset($linked_company['city'])) {
                        $buyer_city = trim((string) $linked_company['city']);
                    }

                    if ($buyer_postal === '' && isset($linked_company['zip'])) {
                        $buyer_postal = trim((string) $linked_company['zip']);
                    }

                    if ((string) $buyer_country === 'SA' && isset($linked_company['country'])) {
                        $buyer_country = self::normalizeCountryCode((string) $linked_company['country']);
                    }
                }
            }
        }

        $buyer_vat = preg_replace('/\D+/', '', (string) $buyer_vat);
        $buyer_id_value = preg_replace('/[^A-Za-z0-9]+/', '', (string) $buyer_id_value);

        $buyer_vat_is_valid = (bool) preg_match('/^3\d{13}3$/', (string) $buyer_vat);

        if ($is_standard_invoice) {
            // Keep BT-46 valid even when legacy contact data is incomplete.
            if ($buyer_id_scheme === 'CRN' && !preg_match('/^\d{10}$/', (string) $buyer_id_value)) {
                $buyer_id_value = '';
            }

            if ($buyer_id_scheme === 'IQA' && !preg_match('/^\d{10}$/', (string) $buyer_id_value)) {
                $buyer_id_value = '';
            }

            if ($buyer_id_value === '') {
                $buyer_id_scheme = 'OTH';
                $buyer_id_value = 'CUST' . (string) (isset($customer['id']) ? (int) $customer['id'] : (int) $invoice['userid']);
            }

            // Standard invoices require buyer address details.
            if ($buyer_street === '') {
                $buyer_street = 'N/A';
            }

            if ($buyer_city === '') {
                $buyer_city = 'N/A';
            }
        } else {
            // For simplified invoices, avoid invalid schemeID="VAT" usage in BT-46.
            // If buyer VAT is not valid and no ID exists, provide a valid OTH identifier.
            if (!$buyer_vat_is_valid && $buyer_id_value === '') {
                $buyer_id_scheme = 'OTH';
                $buyer_id_value = 'CUST' . (string) (isset($customer['id']) ? (int) $customer['id'] : (int) $invoice['userid']);
            }
        }

        if ($buyer_building === '') {
            $buyer_building = self::extractBuildingNumber($buyer_street);
        }
        if ($buyer_district === '') {
            $buyer_district = $buyer_city !== '' ? $buyer_city : 'N/A';
        }

        if ($buyer_postal === '') {
            $buyer_postal = '00000';
        }

        if ($buyer_country === '') {
            $buyer_country = 'SA';
        }

        $lines = '';
        $line_id = 1;
        foreach ($items as $item) {
            $qty = Zatca::formatAmount($item['qty']);
            $line_total = Zatca::formatAmount($item['total']);
            $line_amount = Zatca::formatAmount($item['amount']);
            $line_is_taxable = $tax_rate > 0 && (!isset($item['taxed']) || (string) $item['taxed'] !== '0');
            $line_tax_category_code = $line_is_taxable ? 'S' : 'O';
            $line_tax_exemption_xml = '';
            if ($line_tax_category_code !== 'S') {
                $line_tax_exemption_xml = '<cbc:TaxExemptionReasonCode>VATEX-SA-OOS</cbc:TaxExemptionReasonCode>'
                    . '<cbc:TaxExemptionReason>Not subject to VAT</cbc:TaxExemptionReason>';
            }
            $tax_percent = Zatca::formatAmount($line_is_taxable ? $tax_rate : 0);
            $line_tax_raw = $line_is_taxable
                ? round(((float) $item['total']) * ($tax_rate / 100), 2)
                : 0.0;
            $line_tax = Zatca::formatAmount($line_tax_raw);
            $line_with_vat = Zatca::formatAmount(((float) $item['total']) + $line_tax_raw);

            $lines .= '<cac:InvoiceLine>';
            $lines .= '<cbc:ID>' . $line_id . '</cbc:ID>';
            $lines .= '<cbc:InvoicedQuantity unitCode="PCE">' . $qty . '</cbc:InvoicedQuantity>';
            $lines .= '<cbc:LineExtensionAmount currencyID="' . $currency_code . '">' . $line_total . '</cbc:LineExtensionAmount>';
            $lines .= '<cac:TaxTotal>';
            $lines .= '<cbc:TaxAmount currencyID="' . $currency_code . '">' . $line_tax . '</cbc:TaxAmount>';
            $lines .= '<cbc:RoundingAmount currencyID="' . $currency_code . '">' . $line_with_vat . '</cbc:RoundingAmount>';
            $lines .= '<cac:TaxSubtotal>';
            $lines .= '<cbc:TaxableAmount currencyID="' . $currency_code . '">' . $line_total . '</cbc:TaxableAmount>';
            $lines .= '<cbc:TaxAmount currencyID="' . $currency_code . '">' . $line_tax . '</cbc:TaxAmount>';
            $lines .= '<cac:TaxCategory>';
            $lines .= '<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">' . $line_tax_category_code . '</cbc:ID>';
            $lines .= '<cbc:Percent>' . $tax_percent . '</cbc:Percent>';
            $lines .= $line_tax_exemption_xml;
            $lines .= '<cac:TaxScheme><cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID></cac:TaxScheme>';
            $lines .= '</cac:TaxCategory>';
            $lines .= '</cac:TaxSubtotal>';
            $lines .= '</cac:TaxTotal>';
            $lines .= '<cac:Item><cbc:Name>' . htmlspecialchars($item['description'], ENT_XML1) . '</cbc:Name>';
            $lines .= '<cac:ClassifiedTaxCategory>';
            $lines .= '<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">' . $line_tax_category_code . '</cbc:ID>';
            $lines .= '<cbc:Percent>' . $tax_percent . '</cbc:Percent>';
            $lines .= $line_tax_exemption_xml;
            $lines .= '<cac:TaxScheme><cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID></cac:TaxScheme>';
            $lines .= '</cac:ClassifiedTaxCategory>';
            $lines .= '</cac:Item>';
            $lines .= '<cac:Price><cbc:PriceAmount currencyID="' . $currency_code . '">' . $line_amount . '</cbc:PriceAmount></cac:Price>';
            $lines .= '</cac:InvoiceLine>';

            $line_id++;
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sig="urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2" xmlns:sac="urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2" xmlns:sbc="urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2">';
        $xml .= '<cbc:ProfileID>reporting:1.0</cbc:ProfileID>';
        $xml .= '<cbc:ID>' . htmlspecialchars($invoice_number, ENT_XML1) . '</cbc:ID>';
        $xml .= '<cbc:UUID>' . htmlspecialchars($uuid, ENT_XML1) . '</cbc:UUID>';
        // IssueDate MUST come from the same source as IssueTime/QR-tag3/XAdES SigningTime
        // ($timestamp, resolved via Zatca::resolveTimestamp() which can prefer datepaid over
        // date) - using raw $invoice['date'] here let the two silently diverge onto different
        // calendar dates whenever an invoice's date and datepaid columns differ.
        $xml .= '<cbc:IssueDate>' . date('Y-m-d', strtotime($timestamp)) . '</cbc:IssueDate>';
        $xml .= '<cbc:IssueTime>' . date('H:i:s', strtotime($timestamp)) . '</cbc:IssueTime>';
        $xml .= '<cbc:InvoiceTypeCode name="' . $invoice_type_name . '">' . $invoice_type_code . '</cbc:InvoiceTypeCode>';

        if ($document_type === 'credit_note' || $document_type === 'debit_note') {
            // KSA-10 reason for issuing debit/credit note.
            $xml .= $document_type === 'credit_note'
                ? '<cbc:Note>Return of goods/services</cbc:Note>'
                : '<cbc:Note>Additional charge or correction</cbc:Note>';
        }

        $xml .= '<cbc:DocumentCurrencyCode>' . $currency_code . '</cbc:DocumentCurrencyCode>';
        $xml .= '<cbc:TaxCurrencyCode>' . $currency_code . '</cbc:TaxCurrencyCode>';

        if (($document_type === 'credit_note' || $document_type === 'debit_note') && isset($context['original_invoice'])) {
            $original_invoice = $context['original_invoice'];
            $original_invoice_number = self::buildInvoiceNumberCompat($original_invoice);
            $original_uuid = self::resolveInvoiceUuidForReference($original_invoice);

            $xml .= '<cac:BillingReference><cac:InvoiceDocumentReference>';
            $xml .= '<cbc:ID>' . htmlspecialchars($original_invoice_number, ENT_XML1) . '</cbc:ID>';
            if ($original_uuid !== '') {
                $xml .= '<cbc:UUID>' . htmlspecialchars($original_uuid, ENT_XML1) . '</cbc:UUID>';
            }
            $xml .= '</cac:InvoiceDocumentReference></cac:BillingReference>';
        }

        // Additional references must appear before AccountingSupplierParty in UBL order.
        $xml .= '<cac:AdditionalDocumentReference>';
        $xml .= '<cbc:ID>ICV</cbc:ID>';
        $xml .= '<cbc:UUID>' . $icv . '</cbc:UUID>';
        $xml .= '</cac:AdditionalDocumentReference>';

        $xml .= '<cac:AdditionalDocumentReference>';
        $xml .= '<cbc:ID>PIH</cbc:ID>';
        $xml .= '<cac:Attachment><cbc:EmbeddedDocumentBinaryObject mimeCode="text/plain">' . htmlspecialchars($pih, ENT_XML1) . '</cbc:EmbeddedDocumentBinaryObject></cac:Attachment>';
        $xml .= '</cac:AdditionalDocumentReference>';

        $qr_payload = self::buildBase64TlvCompat([
            1 => (string) $config['CompanyName'],
            2 => (string) $sellerVatNumber,
            3 => date('Y-m-d\TH:i:s\Z', strtotime($timestamp)),
            4 => Zatca::formatAmount($invoice_total),
            5 => Zatca::formatAmount($invoice_tax_total),
        ]);
        $xml .= '<cac:AdditionalDocumentReference>';
        $xml .= '<cbc:ID>QR</cbc:ID>';
        $xml .= '<cac:Attachment><cbc:EmbeddedDocumentBinaryObject mimeCode="text/plain">' . $qr_payload . '</cbc:EmbeddedDocumentBinaryObject></cac:Attachment>';
        $xml .= '</cac:AdditionalDocumentReference>';

        $xml .= '<cac:AccountingSupplierParty><cac:Party>';
        $xml .= '<cac:PartyIdentification><cbc:ID schemeID="CRN">' . htmlspecialchars($seller_crn, ENT_XML1) . '</cbc:ID></cac:PartyIdentification>';
        $xml .= '<cac:PostalAddress>';
        $xml .= '<cbc:StreetName>' . htmlspecialchars($seller_street, ENT_XML1) . '</cbc:StreetName>';
        $xml .= '<cbc:BuildingNumber>' . htmlspecialchars($seller_building, ENT_XML1) . '</cbc:BuildingNumber>';
        $xml .= '<cbc:CitySubdivisionName>' . htmlspecialchars($seller_district, ENT_XML1) . '</cbc:CitySubdivisionName>';
        $xml .= '<cbc:CityName>' . htmlspecialchars($seller_city, ENT_XML1) . '</cbc:CityName>';
        $xml .= '<cbc:PostalZone>' . htmlspecialchars($seller_postal, ENT_XML1) . '</cbc:PostalZone>';
        $xml .= '<cac:Country><cbc:IdentificationCode>' . htmlspecialchars($seller_country, ENT_XML1) . '</cbc:IdentificationCode></cac:Country>';
        $xml .= '</cac:PostalAddress>';
        $xml .= '<cac:PartyTaxScheme><cbc:CompanyID>' . htmlspecialchars($sellerVatNumber, ENT_XML1) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme>';
        $xml .= '<cac:PartyLegalEntity><cbc:RegistrationName>' . htmlspecialchars($config['CompanyName'], ENT_XML1) . '</cbc:RegistrationName></cac:PartyLegalEntity>';
        $xml .= '</cac:Party></cac:AccountingSupplierParty>';

        if ($buyer_name !== '') {
            $xml .= '<cac:AccountingCustomerParty><cac:Party>';

            // B2B (standard) mapping:
            // - BT-46 Buyer Identification with allowed scheme ID (CRN/IQA/etc.)
            // - Buyer VAT in PartyTaxScheme/CompanyID
            if ($is_standard_invoice) {
                if ($buyer_id_value !== '') {
                    $xml .= '<cac:PartyIdentification><cbc:ID schemeID="' . htmlspecialchars($buyer_id_scheme, ENT_XML1) . '">' . htmlspecialchars($buyer_id_value, ENT_XML1) . '</cbc:ID></cac:PartyIdentification>';
                }

                if ($buyer_street !== '' || $buyer_city !== '' || $buyer_country !== '') {
                    $xml .= '<cac:PostalAddress>';
                    if ($buyer_street !== '') {
                        $xml .= '<cbc:StreetName>' . htmlspecialchars($buyer_street, ENT_XML1) . '</cbc:StreetName>';
                    }
                    if ($buyer_building !== '') {
                        $xml .= '<cbc:BuildingNumber>' . htmlspecialchars($buyer_building, ENT_XML1) . '</cbc:BuildingNumber>';
                    }
                    if ($buyer_district !== '') {
                        $xml .= '<cbc:CitySubdivisionName>' . htmlspecialchars($buyer_district, ENT_XML1) . '</cbc:CitySubdivisionName>';
                    }
                    if ($buyer_city !== '') {
                        $xml .= '<cbc:CityName>' . htmlspecialchars($buyer_city, ENT_XML1) . '</cbc:CityName>';
                    }
                    if ($buyer_postal !== '') {
                        $xml .= '<cbc:PostalZone>' . htmlspecialchars($buyer_postal, ENT_XML1) . '</cbc:PostalZone>';
                    }
                    $xml .= '<cac:Country><cbc:IdentificationCode>' . htmlspecialchars($buyer_country, ENT_XML1) . '</cbc:IdentificationCode></cac:Country>';
                    $xml .= '</cac:PostalAddress>';
                }

                if ($buyer_vat_is_valid) {
                    $xml .= '<cac:PartyTaxScheme><cbc:CompanyID>' . htmlspecialchars($buyer_vat, ENT_XML1) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme>';
                }
            } else {
                // Keep B2C behavior unchanged.
                if ($buyer_id_value !== '') {
                    $xml .= '<cac:PartyIdentification><cbc:ID schemeID="' . htmlspecialchars($buyer_id_scheme, ENT_XML1) . '">' . htmlspecialchars($buyer_id_value, ENT_XML1) . '</cbc:ID></cac:PartyIdentification>';
                }

                if ($buyer_street !== '' || $buyer_city !== '' || $buyer_country !== '') {
                    $xml .= '<cac:PostalAddress>';
                    if ($buyer_street !== '') {
                        $xml .= '<cbc:StreetName>' . htmlspecialchars($buyer_street, ENT_XML1) . '</cbc:StreetName>';
                    }
                    if ($buyer_building !== '') {
                        $xml .= '<cbc:BuildingNumber>' . htmlspecialchars($buyer_building, ENT_XML1) . '</cbc:BuildingNumber>';
                    }
                    if ($buyer_district !== '') {
                        $xml .= '<cbc:CitySubdivisionName>' . htmlspecialchars($buyer_district, ENT_XML1) . '</cbc:CitySubdivisionName>';
                    }
                    if ($buyer_city !== '') {
                        $xml .= '<cbc:CityName>' . htmlspecialchars($buyer_city, ENT_XML1) . '</cbc:CityName>';
                    }
                    if ($buyer_postal !== '') {
                        $xml .= '<cbc:PostalZone>' . htmlspecialchars($buyer_postal, ENT_XML1) . '</cbc:PostalZone>';
                    }
                    $xml .= '<cac:Country><cbc:IdentificationCode>' . htmlspecialchars($buyer_country, ENT_XML1) . '</cbc:IdentificationCode></cac:Country>';
                    $xml .= '</cac:PostalAddress>';
                }

                if ($buyer_vat_is_valid) {
                    $xml .= '<cac:PartyTaxScheme><cbc:CompanyID>' . htmlspecialchars($buyer_vat, ENT_XML1) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme>';
                }
            }

            $xml .= '<cac:PartyLegalEntity><cbc:RegistrationName>' . htmlspecialchars($buyer_name, ENT_XML1) . '</cbc:RegistrationName></cac:PartyLegalEntity>';

            $xml .= '</cac:Party></cac:AccountingCustomerParty>';
        }

        if ($is_standard_invoice) {
            $supply_date = isset($invoice['date']) && trim((string) $invoice['date']) !== ''
                ? trim((string) $invoice['date'])
                : date('Y-m-d');
            $xml .= '<cac:Delivery><cbc:ActualDeliveryDate>' . htmlspecialchars($supply_date, ENT_XML1) . '</cbc:ActualDeliveryDate></cac:Delivery>';
        }

        if ($document_type === 'credit_note' || $document_type === 'debit_note') {
            $xml .= '<cac:PaymentMeans>';
            $xml .= '<cbc:PaymentMeansCode>10</cbc:PaymentMeansCode>';
            $xml .= $document_type === 'credit_note'
                ? '<cbc:InstructionNote>Return of goods/services</cbc:InstructionNote>'
                : '<cbc:InstructionNote>Additional charge or correction</cbc:InstructionNote>';
            $xml .= '</cac:PaymentMeans>';
        }

        $xml .= '<cac:TaxTotal>';
        $xml .= '<cbc:TaxAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($invoice_tax_total) . '</cbc:TaxAmount>';
        $xml .= '</cac:TaxTotal>';

        $xml .= '<cac:TaxTotal>';
        $xml .= '<cbc:TaxAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($invoice_tax_total) . '</cbc:TaxAmount>';
        $xml .= '<cac:TaxSubtotal>';
        $xml .= '<cbc:TaxableAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($invoice_tax_total > 0 ? $taxable_subtotal : $subtotal_amount) . '</cbc:TaxableAmount>';
        $xml .= '<cbc:TaxAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($invoice_tax_total) . '</cbc:TaxAmount>';
        $xml .= '<cac:TaxCategory>';
        $xml .= '<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">' . $tax_category_code . '</cbc:ID>';
        $xml .= '<cbc:Percent>' . Zatca::formatAmount($tax_rate) . '</cbc:Percent>';
        $xml .= $tax_exemption_xml;
        $xml .= '<cac:TaxScheme><cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">VAT</cbc:ID></cac:TaxScheme>';
        $xml .= '</cac:TaxCategory>';
        $xml .= '</cac:TaxSubtotal>';
        $xml .= '</cac:TaxTotal>';

        $xml .= '<cac:LegalMonetaryTotal>';
        $xml .= '<cbc:LineExtensionAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($subtotal_amount) . '</cbc:LineExtensionAmount>';
        $xml .= '<cbc:TaxExclusiveAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($subtotal_amount) . '</cbc:TaxExclusiveAmount>';
        $xml .= '<cbc:TaxInclusiveAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($invoice_total) . '</cbc:TaxInclusiveAmount>';
        // Confirmed against a ZATCA-accepted reference sample: AllowanceTotalAmount is always
        // present in cac:LegalMonetaryTotal, even as 0.00 when there's no discount - it is not
        // optional in the KSA UBL profile the way plain UBL treats it. Its absence here was the
        // likely real cause behind the persistent BR-KSA-80/signed-properties-hashing bundle.
        $xml .= '<cbc:AllowanceTotalAmount currencyID="' . $currency_code . '">0.00</cbc:AllowanceTotalAmount>';
        if ($prepaid_amount > 0 && $emit_prepayment) {
            $xml .= '<cbc:PrepaidAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($prepaid_amount) . '</cbc:PrepaidAmount>';
        }
        $xml .= '<cbc:PayableAmount currencyID="' . $currency_code . '">' . Zatca::formatAmount($payable_amount) . '</cbc:PayableAmount>';
        $xml .= '</cac:LegalMonetaryTotal>';

        $xml .= $lines;
        $xml .= '</Invoice>';

        return $xml;
    }

    protected static function stripPrepaymentNodes($xml)
    {
        $xml = (string) $xml;
        if ($xml === '') {
            return '';
        }

        return preg_replace('/<cbc:PrepaidAmount\b[^>]*>.*?<\/cbc:PrepaidAmount>/s', '', $xml);
    }

    protected static function validateInvoiceForSubmission($invoice, array $items, $customer, $config, $sellerVatNumber, array $context = [])
    {
        $errors = [];

        $zatca_invoice_type = isset($context['zatca_invoice_type']) && in_array($context['zatca_invoice_type'], ['standard', 'simplified'], true)
            ? $context['zatca_invoice_type']
            : (isset($invoice['zatca_invoice_type']) && in_array($invoice['zatca_invoice_type'], ['standard', 'simplified'], true)
                ? $invoice['zatca_invoice_type']
                : (isset($config['zatca_invoice_type']) && in_array($config['zatca_invoice_type'], ['standard', 'simplified'], true)
                    ? $config['zatca_invoice_type']
                    : 'simplified'));

        $is_standard_invoice = $zatca_invoice_type === 'standard';
        $seller_profile = self::resolveSellerProfileFromConfig($config, $sellerVatNumber);
        $buyer_profile = self::resolveBuyerProfile($invoice, $customer, $is_standard_invoice);

        if (empty($items)) {
            $errors[] = 'Add at least one invoice item before submitting to ZATCA.';
        }

        if (!isset($invoice['date']) || trim((string) $invoice['date']) === '') {
            $errors[] = 'Invoice date is required before ZATCA submission.';
        }

        if (!preg_match('/^3\d{13}3$/', (string) $sellerVatNumber)) {
            $errors[] = 'Seller VAT number is missing or invalid. It must be a 15-digit Saudi VAT number.';
        }

        if (!preg_match('/^\d{10}$/', (string) $seller_profile['crn'])) {
            $errors[] = 'Seller CRN is missing or invalid. It must be exactly 10 digits.';
        }

        if ($is_standard_invoice) {
            $buyer_type = $customer && isset($customer['buyer_type'])
                ? strtolower(trim((string) $customer['buyer_type']))
                : '';

            if ($buyer_type === 'individual') {
                $errors[] = 'B2B (Standard Tax Invoice) is not allowed for Individual buyer. Change invoice type to Simplified or update buyer as Company.';
            }

            if (!preg_match('/^3\d{13}3$/', (string) $buyer_profile['vat'])) {
                $errors[] = 'Buyer VAT number is required for a standard invoice and must be a valid 15-digit Saudi VAT number.';
            }

            if (strtoupper((string) $buyer_profile['id_scheme']) !== 'CRN' || !preg_match('/^\d{10}$/', (string) $buyer_profile['id_value'])) {
                $errors[] = 'Buyer CRN is required for a standard invoice and must be exactly 10 digits.';
            }

            if ($buyer_profile['street'] === '' || strtoupper($buyer_profile['street']) === 'N/A') {
                $errors[] = 'Buyer street address is required for a standard invoice.';
            }

            if ($buyer_profile['building_number'] === '' || $buyer_profile['building_number'] === '0000') {
                $errors[] = 'Buyer building number is required for ZATCA. Add a real building number to the customer or linked company address.';
            }

            if ($buyer_profile['city'] === '' || strtoupper($buyer_profile['city']) === 'N/A') {
                $errors[] = 'Buyer city is required for a standard invoice.';
            }

            if ($buyer_profile['district'] === '' || strtoupper($buyer_profile['district']) === 'N/A') {
                $errors[] = 'Buyer district is required for a standard invoice.';
            }

            if (!preg_match('/^\d{5}$/', (string) $buyer_profile['postal'])) {
                $errors[] = 'Buyer postal code is required for a standard invoice and must be 5 digits.';
            }

            if (trim((string) $buyer_profile['country']) === '') {
                $errors[] = 'Buyer country is required for a standard invoice.';
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'status_code' => 0,
                'response' => '',
                'message' => self::formatLocalValidationErrors($errors),
            ];
        }

        return [
            'success' => true,
        ];
    }

    protected static function formatLocalValidationErrors(array $errors)
    {
        $items = '';
        foreach ($errors as $error) {
            $items .= '<li>' . htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        return '<strong>ZATCA submission was blocked locally.</strong>'
            . '<div style="margin-top:6px;">Please fix the following and submit again:</div>'
            . '<ul style="margin:6px 0 0 18px; padding:0;">' . $items . '</ul>';
    }

    protected static function resolveBuyerProfile($invoice, $customer, $is_standard_invoice)
    {
        $buyer_name = '';
        $buyer_vat = '';
        $buyer_id_value = '';
        $buyer_id_scheme = 'CRN';
        $buyer_street = '';
        $buyer_building = '';
        $buyer_district = '';
        $buyer_city = '';
        $buyer_postal = '';
        $buyer_country = 'SA';

        if ($customer) {
            $buyer_name = isset($customer['account']) ? (string) $customer['account'] : '';
            $buyer_street = isset($customer['address']) ? trim((string) $customer['address']) : '';
            $buyer_building = isset($customer['building_number']) ? preg_replace('/\D+/', '', self::normalizeArabicDigits((string) $customer['building_number'])) : '';
            $buyer_district = isset($customer['state']) ? trim((string) $customer['state']) : '';
            $buyer_city = isset($customer['city']) ? trim((string) $customer['city']) : '';
            $buyer_postal = isset($customer['zip']) ? trim((string) $customer['zip']) : '';
            $buyer_country = self::normalizeCountryCode(isset($customer['country']) ? $customer['country'] : '');

            if (!empty($customer['tax_number'])) {
                $buyer_vat = (string) $customer['tax_number'];
            }

            if (!empty($customer['entity_number'])) {
                $buyer_id_value = (string) $customer['entity_number'];
                $buyer_id_scheme = 'CRN';
            } elseif (!empty($customer['id_iqama'])) {
                $buyer_id_value = (string) $customer['id_iqama'];
                $buyer_id_scheme = 'IQA';
            }

            if (!$buyer_vat || !$buyer_id_value || $buyer_street === '' || $buyer_city === '' || $buyer_postal === '' || $buyer_district === '' || $buyer_building === '') {
                $linked_company = self::resolveLinkedCompanyForCustomer($customer);
                if ($linked_company) {
                    if (!$buyer_vat && Zatca::hasTableColumn('sys_companies', 'vat_number') && isset($linked_company['vat_number'])) {
                        $buyer_vat = (string) $linked_company['vat_number'];
                    }

                    if (!$buyer_id_value && Zatca::hasTableColumn('sys_companies', 'crn_number') && isset($linked_company['crn_number'])) {
                        $buyer_id_value = (string) $linked_company['crn_number'];
                        $buyer_id_scheme = 'CRN';
                    }

                    if ($buyer_street === '' && isset($linked_company['address1'])) {
                        $buyer_street = trim((string) $linked_company['address1']);
                    }

                    if ($buyer_building === '' && Zatca::hasTableColumn('sys_companies', 'building_number') && isset($linked_company['building_number'])) {
                        $buyer_building = preg_replace('/\D+/', '', self::normalizeArabicDigits((string) $linked_company['building_number']));
                    }

                    if ($buyer_building === '' && isset($linked_company['address1'])) {
                        $buyer_building = self::extractBuildingNumber((string) $linked_company['address1']);
                        if ($buyer_building === '0000') {
                            $buyer_building = '';
                        }
                    }

                    if ($buyer_district === '' && isset($linked_company['state'])) {
                        $buyer_district = trim((string) $linked_company['state']);
                    }

                    if ($buyer_city === '' && isset($linked_company['city'])) {
                        $buyer_city = trim((string) $linked_company['city']);
                    }

                    if ($buyer_postal === '' && isset($linked_company['zip'])) {
                        $buyer_postal = trim((string) $linked_company['zip']);
                    }

                    if ((string) $buyer_country === 'SA' && isset($linked_company['country'])) {
                        $buyer_country = self::normalizeCountryCode((string) $linked_company['country']);
                    }
                }
            }
        }

        $buyer_vat = preg_replace('/\D+/', '', (string) $buyer_vat);
        $buyer_id_value = preg_replace('/[^A-Za-z0-9]+/', '', (string) $buyer_id_value);

        if ($is_standard_invoice) {
            if ($buyer_id_scheme === 'CRN' && !preg_match('/^\d{10}$/', (string) $buyer_id_value)) {
                $buyer_id_value = '';
            }

            if ($buyer_id_scheme === 'IQA' && !preg_match('/^\d{10}$/', (string) $buyer_id_value)) {
                $buyer_id_value = '';
            }

            if ($buyer_id_value === '') {
                $buyer_id_scheme = 'OTH';
                $buyer_id_value = 'CUST' . (string) (isset($customer['id']) ? (int) $customer['id'] : (int) $invoice['userid']);
            }

            if ($buyer_street === '') {
                $buyer_street = 'N/A';
            }

            if ($buyer_city === '') {
                $buyer_city = 'N/A';
            }
        }

        if ($buyer_building === '') {
            $buyer_building = self::extractBuildingNumber($buyer_street);
        }
        if ($buyer_district === '') {
            $buyer_district = $buyer_city !== '' ? $buyer_city : 'N/A';
        }

        if ($buyer_postal === '') {
            $buyer_postal = '00000';
        }

        if ($buyer_country === '') {
            $buyer_country = 'SA';
        }

        return [
            'name' => $buyer_name,
            'vat' => $buyer_vat,
            'id_value' => $buyer_id_value,
            'id_scheme' => $buyer_id_scheme,
            'street' => $buyer_street,
            'building_number' => $buyer_building,
            'district' => $buyer_district,
            'city' => $buyer_city,
            'postal' => $buyer_postal,
            'country' => $buyer_country,
        ];
    }

    protected static function resolveLinkedCompanyForCustomer($customer)
    {
        if (!$customer) {
            return null;
        }

        $company_id = isset($customer['cid']) ? (int) $customer['cid'] : 0;
        if ($company_id > 0) {
            $company = ORM::for_table('sys_companies')->find_one($company_id);
            if ($company) {
                return $company;
            }
        }

        $company_name = isset($customer['company']) ? trim((string) $customer['company']) : '';
        if ($company_name !== '') {
            $company = ORM::for_table('sys_companies')
                ->where('company_name', $company_name)
                ->find_one();
            if ($company) {
                return $company;
            }
        }

        $account_name = isset($customer['account']) ? trim((string) $customer['account']) : '';
        if ($account_name !== '') {
            $company = ORM::for_table('sys_companies')
                ->where('company_name', $account_name)
                ->find_one();
            if ($company) {
                return $company;
            }
        }

        return null;
    }

    protected static function resolveSellerProfileFromConfig($config, $sellerVatNumber)
    {
        $company_name = isset($config['CompanyName']) ? trim((string) $config['CompanyName']) : '';

        $company_model = null;
        if (Zatca::hasTableColumn('sys_companies', 'crn_number') || Zatca::hasTableColumn('sys_companies', 'vat_number')) {
            if ($company_name !== '') {
                $company_model = ORM::for_table('sys_companies')
                    ->where('company_name', $company_name)
                    ->find_one();
            }

            if (!$company_model) {
                $company_model = ORM::for_table('sys_companies')
                    ->where_raw("COALESCE(crn_number, '') <> '' OR COALESCE(vat_number, '') <> ''")
                    ->order_by_desc('id')
                    ->find_one();
            }
        }

        $crn_candidates = [
            ($company_model && isset($company_model['crn_number'])) ? (string) $company_model['crn_number'] : '',
            isset($config['zatca_seller_crn']) ? (string) $config['zatca_seller_crn'] : '',
            isset($config['zatca_crn']) ? (string) $config['zatca_crn'] : '',
            isset($config['CompanyCRN']) ? (string) $config['CompanyCRN'] : '',
            isset($config['company_crn']) ? (string) $config['company_crn'] : '',
        ];

        $seller_crn = '';
        foreach ($crn_candidates as $crn_candidate) {
            $crn_candidate = trim((string) $crn_candidate);
            if ($crn_candidate !== '') {
                $seller_crn = $crn_candidate;
                break;
            }
        }

        if ($seller_crn === '') {
            $seller_crn = '1010000000';
        }

        $seller_crn = preg_replace('/[^A-Za-z0-9]+/', '', (string) $seller_crn);
        if (!preg_match('/^\d{10}$/', (string) $seller_crn)) {
            $seller_crn = '1010000000';
        }

        $address_blob = isset($config['caddress']) ? trim((string) $config['caddress']) : '';
        $address_lines = preg_split('/\r\n|\r|\n/', $address_blob);
        $address_lines = array_values(array_filter(array_map('trim', (array) $address_lines), function ($line) {
            return $line !== '';
        }));

        $street = isset($config['zatca_street_name']) && trim((string) $config['zatca_street_name']) !== ''
            ? trim((string) $config['zatca_street_name'])
            : (isset($address_lines[0]) ? $address_lines[0] : 'Main Street');

        $building_number = isset($config['zatca_building_number']) && trim((string) $config['zatca_building_number']) !== ''
            ? trim((string) $config['zatca_building_number'])
            : (isset($config['zatca_seller_building_number']) && trim((string) $config['zatca_seller_building_number']) !== ''
                ? trim((string) $config['zatca_seller_building_number'])
                : '0000');

        $district = isset($config['zatca_city_subdivision']) && trim((string) $config['zatca_city_subdivision']) !== ''
            ? trim((string) $config['zatca_city_subdivision'])
            : (isset($config['zatca_seller_district']) && trim((string) $config['zatca_seller_district']) !== ''
                ? trim((string) $config['zatca_seller_district'])
                : (isset($address_lines[1]) ? $address_lines[1] : $company_name));

        $city = isset($config['zatca_city_name']) && trim((string) $config['zatca_city_name']) !== ''
            ? trim((string) $config['zatca_city_name'])
            : (isset($config['zatca_seller_city']) && trim((string) $config['zatca_seller_city']) !== ''
                ? trim((string) $config['zatca_seller_city'])
                : (isset($address_lines[2]) ? $address_lines[2] : 'Jeddah'));

        $postal_zone = isset($config['zatca_postal_zone']) && trim((string) $config['zatca_postal_zone']) !== ''
            ? trim((string) $config['zatca_postal_zone'])
            : (isset($config['zatca_seller_postal_zone']) && trim((string) $config['zatca_seller_postal_zone']) !== ''
                ? trim((string) $config['zatca_seller_postal_zone'])
                : (isset($config['zip']) && trim((string) $config['zip']) !== '' ? trim((string) $config['zip']) : '21577'));

        $country_code = isset($config['country_code']) && trim((string) $config['country_code']) !== ''
            ? strtoupper(trim((string) $config['country_code']))
            : (isset($config['zatca_country_code']) && trim((string) $config['zatca_country_code']) !== ''
                ? strtoupper(trim((string) $config['zatca_country_code']))
                : 'SA');

        return [
            'crn' => $seller_crn,
            'street' => $street,
            'building_number' => $building_number,
            'district' => $district,
            'city' => $city,
            'postal_zone' => $postal_zone,
            'country_code' => $country_code,
            'vat' => (string) $sellerVatNumber,
        ];
    }

    protected static function normalizeCountryCode($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return 'SA';
        }

        $map = [
            'SAUDI ARABIA' => 'SA',
            'KSA' => 'SA',
            'KINGDOM OF SAUDI ARABIA' => 'SA',
            'UAE' => 'AE',
            'UNITED ARAB EMIRATES' => 'AE',
            'QATAR' => 'QA',
            'KUWAIT' => 'KW',
            'BAHRAIN' => 'BH',
            'OMAN' => 'OM',
        ];

        if (isset($map[$value])) {
            return $map[$value];
        }

        // Already an ISO-2 country code.
        if (preg_match('/^[A-Z]{2}$/', $value)) {
            return $value;
        }

        return 'SA';
    }

    protected static function buildInvoiceNumberCompat($invoice)
    {
        if (is_callable(['Zatca', 'buildInvoiceNumber'])) {
            return (string) call_user_func(['Zatca', 'buildInvoiceNumber'], $invoice);
        }

        $invoicenum = is_array($invoice)
            ? (isset($invoice['invoicenum']) ? (string) $invoice['invoicenum'] : '')
            : (isset($invoice->invoicenum) ? (string) $invoice->invoicenum : '');
        $cn = is_array($invoice)
            ? (isset($invoice['cn']) ? (string) $invoice['cn'] : '')
            : (isset($invoice->cn) ? (string) $invoice->cn : '');
        $id = is_array($invoice)
            ? (isset($invoice['id']) ? (string) $invoice['id'] : '')
            : (isset($invoice->id) ? (string) $invoice->id : '');

        return $cn !== '' ? ($invoicenum . $cn) : ($invoicenum . $id);
    }

    protected static function resolveInvoiceUuidForReference($invoice)
    {
        if (!$invoice) {
            return '';
        }

        $read = static function ($key) use ($invoice) {
            if (is_array($invoice)) {
                return isset($invoice[$key]) ? trim((string) $invoice[$key]) : '';
            }

            return isset($invoice->$key) ? trim((string) $invoice->$key) : '';
        };

        foreach (['zatca_uuid', 'zatca_invoice_uuid', 'uuid'] as $key) {
            $value = $read($key);
            if ($value !== '') {
                return $value;
            }
        }

        // Some installs persist UUID only inside the stored submission response payload.
        foreach (['zatca_submission_response', 'zatca_last_response', 'zatca_last_submit_response'] as $payload_key) {
            $payload = $read($payload_key);
            if ($payload === '') {
                continue;
            }

            $decoded = json_decode($payload, true);
            if (!is_array($decoded)) {
                continue;
            }

            $uuid = self::extractFirstScalarValue($decoded, ['uuid', 'invoiceUUID', 'invoiceUuid']);
            if ($uuid !== '') {
                return $uuid;
            }
        }

        return '';
    }

    protected static function buildBase64TlvCompat(array $tags)
    {
        if (is_callable(['Zatca', 'buildBase64Tlv'])) {
            return (string) call_user_func(['Zatca', 'buildBase64Tlv'], $tags);
        }

        $binary = '';
        foreach ($tags as $tag => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $value = (string) $value;
            if (strlen($value) > 255) {
                $value = substr($value, 0, 255);
            }

            $binary .= chr((int) $tag) . chr(strlen($value)) . $value;
        }

        return base64_encode($binary);
    }

    protected static function prepareInvoiceZatcaData($invoice, $sellerName, $sellerVatNumber, $invoiceTotal, $invoiceVatTotal)
    {
        if (is_callable(['Zatca', 'prepareInvoiceData'])) {
            return (array) call_user_func(
                ['Zatca', 'prepareInvoiceData'],
                $invoice,
                $sellerName,
                $sellerVatNumber,
                $invoiceTotal,
                $invoiceVatTotal
            );
        }

        return [
            'uuid' => Zatca::generateUuidV4(),
            'hash' => base64_encode(hash('sha256', (string) $invoiceTotal . '|' . (string) $invoiceVatTotal, true)),
            'signature' => '',
            'public_key' => '',
            'ca_signature' => '',
            'seller_name' => (string) $sellerName,
            'seller_vat' => (string) $sellerVatNumber,
            'timestamp' => date('c'),
            'invoice_number' => self::buildInvoiceNumberCompat($invoice),
            'tlv' => '',
        ];
    }

    protected static function persistInvoiceZatcaData($invoiceId, array $data)
    {
        if (is_callable(['Zatca', 'persistInvoiceData'])) {
            call_user_func(['Zatca', 'persistInvoiceData'], $invoiceId, $data);
        }
    }

    protected static function extractBuildingNumber($street)
    {
        $street = self::normalizeArabicDigits(trim((string) $street));
        if ($street === '') {
            return '0000';
        }

        if (preg_match('/\b(\d{1,10})\b/', $street, $m)) {
            return trim((string) $m[1]);
        }

        return '0000';
    }

    protected static function normalizeArabicDigits($value)
    {
        return strtr((string) $value, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        ]);
    }

    protected static function submitToApi(array $package, $config)
    {
        $api_base = self::resolveApiBase($config);

        $environment = isset($config['zatca_environment'])
            ? strtolower(trim((string) $config['zatca_environment']))
            : 'sandbox';

        // Select credentials based on environment first, then fallback.
        $prod_token = isset($config['zatca_production_binary_security_token'])
            ? trim($config['zatca_production_binary_security_token'])
            : '';
        $prod_secret = isset($config['zatca_production_secret'])
            ? trim($config['zatca_production_secret'])
            : '';
        $compliance_token = isset($config['zatca_binary_security_token'])
            ? trim($config['zatca_binary_security_token'])
            : '';
        $compliance_secret = isset($config['zatca_secret'])
            ? trim($config['zatca_secret'])
            : '';

        // Credentials are occasionally pasted with line breaks/spaces; normalize before auth header usage.
        $prod_token = preg_replace('/\s+/', '', (string) $prod_token);
        $prod_secret = preg_replace('/\s+/', '', (string) $prod_secret);
        $compliance_token = preg_replace('/\s+/', '', (string) $compliance_token);
        $compliance_secret = preg_replace('/\s+/', '', (string) $compliance_secret);

        if ($environment === 'production' && ($prod_token === '' || $prod_secret === '')) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Production ZATCA credentials are missing. Configure Production Binary Security Token and Production Secret first (Portal Generated Keys), then submit invoice again.',
                'response_body' => '',
            ];
        }

        $use_production_credentials = $environment === 'production';

        if ($use_production_credentials) {
            $binary_token = $prod_token !== '' ? $prod_token : $compliance_token;
            $secret = $prod_secret !== '' ? $prod_secret : $compliance_secret;
            $alternate_token = $compliance_token;
            $alternate_secret = $compliance_secret;
        } else {
            $binary_token = $compliance_token !== '' ? $compliance_token : $prod_token;
            $secret = $compliance_secret !== '' ? $compliance_secret : $prod_secret;
            $alternate_token = $prod_token;
            $alternate_secret = $prod_secret;
        }

        $binary_token = preg_replace('/\s+/', '', (string) $binary_token);
        $secret = preg_replace('/\s+/', '', (string) $secret);
        $alternate_token = preg_replace('/\s+/', '', (string) $alternate_token);
        $alternate_secret = preg_replace('/\s+/', '', (string) $alternate_secret);

        if ($binary_token === '' || $secret === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'ZATCA credentials are missing. Please configure Binary Security Token and Secret.',
                'response_body' => '',
            ];
        }

        $invoice_type = isset($package['zatca_invoice_type'])
            ? strtolower(trim((string) $package['zatca_invoice_type']))
            : (isset($config['zatca_invoice_type'])
                ? strtolower(trim((string) $config['zatca_invoice_type']))
                : 'simplified');

        $endpoint = 'invoices/reporting/single';
        if ($invoice_type === 'standard') {
            $endpoint = 'invoices/clearance/single';
        }

        $client = new Client([
            'base_uri' => $api_base,
            'timeout' => 45,
            'http_errors' => false,
        ]);

        try {
            $submit = function ($token, $tokenSecret) use ($client, $endpoint, $package) {
                return $client->post($endpoint, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Accept-Language' => 'en',
                        'Accept-Version' => 'V2',
                        'Authorization' => 'Basic ' . base64_encode($token . ':' . $tokenSecret),
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'invoiceHash' => $package['invoice_hash'],
                        'uuid' => $package['uuid'],
                        'invoice' => $package['invoice_b64'],
                    ],
                ]);
            };

            $response = $submit($binary_token, $secret);

            $status_code = $response->getStatusCode();
            $response_body = (string) $response->getBody();

            $should_retry_alternate =
                (($status_code === 401) || ($status_code >= 400 && stripos($response_body, 'invalid-certificate') !== false))
                && $alternate_token !== ''
                && $alternate_secret !== ''
                && ($alternate_token !== $binary_token || $alternate_secret !== $secret);

            if ($should_retry_alternate) {
                $response = $submit($alternate_token, $alternate_secret);
                $status_code = $response->getStatusCode();
                $response_body = (string) $response->getBody();
            }

            if ($status_code >= 200 && $status_code < 300) {
                return [
                    'success' => true,
                    'status_code' => $status_code,
                    'message' => 'Submission successful',
                    'response_body' => $response_body,
                ];
            }

            if ($status_code === 401 && trim((string) $response_body) === '') {
                return [
                    'success' => false,
                    'status_code' => $status_code,
                    'message' => 'Submission failed with status code 401. ZATCA rejected authorization. Verify Production Binary Security Token and Production Secret are correct and belong to the currently onboarded production device.',
                    'response_body' => $response_body,
                ];
            }

            return [
                'success' => false,
                'status_code' => $status_code,
                'message' => 'Submission failed with status code ' . $status_code . ': ' . self::summarizeApiValidationError($response_body),
                'response_body' => $response_body,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Submission failed: ' . $e->getMessage(),
                'response_body' => '',
            ];
        }
    }

    protected static function persistSubmissionResult($invoiceId, array $package, array $submission)
    {
        // Audit log entry (and, implicitly, the PIH chain source for the next submission)
        // is recorded regardless of downstream DB-column handling below, and independent of
        // whether this ends up being treated as a no-op re-submission - the ICV was already
        // consumed by acquireNextIcv() in buildSignedPackage() so the attempt must be logged.
        if (empty($package['is_compliance_mode'])) {
            $responseBodyForLog = isset($submission['response_body']) ? (string) $submission['response_body'] : '';
            $decodedForLog = json_decode($responseBodyForLog, true);
            $decodedForLog = is_array($decodedForLog) ? $decodedForLog : [];

            $logStatus = 'ERROR';
            if (!empty($submission['success'])) {
                $logStatus = 'PASS';
                $validationStatus = '';
                if (isset($decodedForLog['validationResults']['status'])) {
                    $validationStatus = strtoupper(trim((string) $decodedForLog['validationResults']['status']));
                } elseif (isset($decodedForLog['validation_results']['status'])) {
                    $validationStatus = strtoupper(trim((string) $decodedForLog['validation_results']['status']));
                }
                if ($validationStatus === 'WARNING') {
                    $logStatus = 'WARNING';
                } elseif ($validationStatus === 'ERROR') {
                    $logStatus = 'ERROR';
                }
            }

            self::logInvoiceSubmission(
                $invoiceId,
                isset($package['document_type']) ? $package['document_type'] : 'invoice',
                isset($package['zatca_environment']) ? $package['zatca_environment'] : 'sandbox',
                isset($package['icv']) ? $package['icv'] : 0,
                isset($package['uuid']) ? $package['uuid'] : null,
                isset($package['invoice_hash']) ? $package['invoice_hash'] : null,
                $logStatus,
                isset($submission['status_code']) ? $submission['status_code'] : null,
                isset($submission['message']) ? $submission['message'] : null
            );
        }

        $invoice = ORM::for_table('sys_invoices')->find_one($invoiceId);
        if (!$invoice) {
            return;
        }

        // If invoice is already registered, do not downgrade its registration
        // state because of a later failed re-submission attempt.
        $existing_report = self::buildInvoiceRegistrationReport($invoice);
        if (!empty($existing_report['is_registered']) && empty($submission['success'])) {
            return;
        }

        $status = $submission['success'] ? 'submitted' : 'failed';
        $response_body = isset($submission['response_body']) ? $submission['response_body'] : '';

        $column_map = [
            'zatca_submission_status' => $status,
            'zatca_submission_date' => date('Y-m-d H:i:s'),
            'zatca_submission_response' => $response_body,
            'zatca_invoice_xml' => $package['invoice_xml'],
            'zatca_invoice_encoded' => $package['invoice_b64'],
        ];

        foreach ($column_map as $column => $value) {
            if (Zatca::hasInvoiceColumn($column)) {
                $invoice->$column = $value;
            }
        }

        // Backward compatibility for installations that still use legacy ZATCA columns.
        if (Zatca::hasInvoiceColumn('zatca_status')) {
            $invoice->zatca_status = $status;
        }
        if (Zatca::hasInvoiceColumn('zatca_last_submit_at')) {
            $invoice->zatca_last_submit_at = date('Y-m-d H:i:s');
        }

        if (Zatca::hasInvoiceColumn('zatca_submission_http_code')) {
            $invoice->zatca_submission_http_code = (int) $submission['status_code'];
        }

        $responseForStorage = [];
        if (!empty($response_body)) {
            $decoded = json_decode($response_body, true);
            if (is_array($decoded)) {
                $responseForStorage = $decoded;
            }
        }

        $signedInvoiceBase64 = '';
        if (!empty($responseForStorage)) {
            $signedInvoiceBase64 = isset($responseForStorage['clearedInvoice']) && !empty($responseForStorage['clearedInvoice'])
                ? (string) $responseForStorage['clearedInvoice']
                : (isset($responseForStorage['invoice']) ? (string) $responseForStorage['invoice'] : '');

            if ($signedInvoiceBase64 === '' && isset($responseForStorage['signedInvoice'])) {
                $signedInvoiceBase64 = (string) $responseForStorage['signedInvoice'];
            }
        }

        if ($signedInvoiceBase64 === '' && !empty($package['invoice_b64'])) {
            $signedInvoiceBase64 = (string) $package['invoice_b64'];
        }

        $cryptoData = [];
        if ($signedInvoiceBase64 !== '' && class_exists('Zatca') && is_callable(['Zatca', 'extractPhase2CryptoData'])) {
            $cryptoData = Zatca::extractPhase2CryptoData($signedInvoiceBase64);
            if (!is_array($cryptoData)) {
                $cryptoData = [];
            }
        }

        if (empty($cryptoData['hash']) && !empty($responseForStorage['invoiceHash']) && is_callable(['Zatca', 'normalizeBase64ValuePublic'])) {
            $normalizedHash = Zatca::normalizeBase64ValuePublic((string) $responseForStorage['invoiceHash'], true);
            if ($normalizedHash !== '') {
                $cryptoData['hash'] = $normalizedHash;
            }
        }

        if (!empty($cryptoData['hash'])) {
            if (Zatca::hasInvoiceColumn('zatca_invoice_hash')) {
                $invoice->zatca_invoice_hash = $cryptoData['hash'];
            }
            if (Zatca::hasInvoiceColumn('zatca_hash')) {
                $invoice->zatca_hash = $cryptoData['hash'];
            }
        }
        if (!empty($cryptoData['signature']) && Zatca::hasInvoiceColumn('zatca_signature')) {
            $invoice->zatca_signature = $cryptoData['signature'];
        }
        if (!empty($cryptoData['public_key']) && Zatca::hasInvoiceColumn('zatca_public_key')) {
            $invoice->zatca_public_key = $cryptoData['public_key'];
        }
        if (!empty($cryptoData['qr_signature']) && Zatca::hasInvoiceColumn('zatca_qr_signature')) {
            $invoice->zatca_qr_signature = $cryptoData['qr_signature'];
        }

        // Store extracted cryptographic data in response payload for both modern and legacy schemas.
        if (empty($responseForStorage) && !empty($cryptoData)) {
            $responseForStorage = [];
        }

        if (is_array($responseForStorage)) {
            if (!empty($cryptoData['hash'])) {
                $responseForStorage['phase2Hash'] = $cryptoData['hash'];
            }
            if (!empty($cryptoData['signature'])) {
                $responseForStorage['phase2Signature'] = $cryptoData['signature'];
            }
            if (!empty($cryptoData['public_key'])) {
                $responseForStorage['phase2PublicKey'] = $cryptoData['public_key'];
            }
            if (!empty($cryptoData['qr_signature'])) {
                $responseForStorage['phase2QrSignature'] = $cryptoData['qr_signature'];
            }
            $response_body = json_encode($responseForStorage);
        }

        if (Zatca::hasInvoiceColumn('zatca_submission_response')) {
            $invoice->zatca_submission_response = $response_body;
        }

        if (Zatca::hasInvoiceColumn('zatca_last_response')) {
            $invoice->zatca_last_response = $response_body;
        }

        $invoice->save();
    }

    public static function buildInvoiceRegistrationReport($invoice)
    {
        $status = isset($invoice['zatca_submission_status'])
            ? trim((string) $invoice['zatca_submission_status'])
            : '';
        if ($status === '' && isset($invoice['zatca_status'])) {
            $status = trim((string) $invoice['zatca_status']);
        }
        $http_code = isset($invoice['zatca_submission_http_code'])
            ? (int) $invoice['zatca_submission_http_code']
            : 0;
        $submitted_at = isset($invoice['zatca_submission_date'])
            ? trim((string) $invoice['zatca_submission_date'])
            : '';
        if ($submitted_at === '' && isset($invoice['zatca_last_submit_at'])) {
            $submitted_at = trim((string) $invoice['zatca_last_submit_at']);
        }
        $response_raw = isset($invoice['zatca_submission_response'])
            ? (string) $invoice['zatca_submission_response']
            : '';
        if ($response_raw === '' && isset($invoice['zatca_last_response'])) {
            $response_raw = (string) $invoice['zatca_last_response'];
        }

        $response = json_decode($response_raw, true);
        if (!is_array($response)) {
            $response = [];
        }

        $invoice_xml = isset($invoice['zatca_invoice_xml'])
            ? (string) $invoice['zatca_invoice_xml']
            : '';

        $has_ignored_prepayment_warning = self::hasOnlyIgnorablePrepaymentWarning($response, $invoice_xml);

        $reporting_status = isset($response['reportingStatus'])
            ? strtoupper(trim((string) $response['reportingStatus']))
            : '';
        if ($reporting_status === '' && isset($response['reporting_status'])) {
            $reporting_status = strtoupper(trim((string) $response['reporting_status']));
        }
        if ($reporting_status === '' && isset($response['clearanceStatus'])) {
            $reporting_status = strtoupper(trim((string) $response['clearanceStatus']));
        }

        $validation_status = isset($response['validationResults']['status'])
            ? strtoupper(trim((string) $response['validationResults']['status']))
            : '';
        if ($validation_status === '' && isset($response['validation_results']['status'])) {
            $validation_status = strtoupper(trim((string) $response['validation_results']['status']));
        }
        if ($validation_status === '' && isset($response['validation_status'])) {
            $validation_status = strtoupper(trim((string) $response['validation_status']));
        }

        $validation = isset($response['validationResults']) && is_array($response['validationResults'])
            ? $response['validationResults']
            : [];
        if (empty($validation) && isset($response['validation_results']) && is_array($response['validation_results'])) {
            $validation = $response['validation_results'];
        }

        $errors = isset($validation['errorMessages']) && is_array($validation['errorMessages'])
            ? $validation['errorMessages']
            : [];
        $warnings = isset($validation['warningMessages']) && is_array($validation['warningMessages'])
            ? $validation['warningMessages']
            : [];
        $infos = isset($validation['infoMessages']) && is_array($validation['infoMessages'])
            ? $validation['infoMessages']
            : [];

        $validation_messages = self::collectValidationMessages(
            $response,
            $has_ignored_prepayment_warning
        );

        if ($has_ignored_prepayment_warning) {
            $validation_status = 'PASS';
        }

        $is_registered = false;
        if (in_array($reporting_status, ['REPORTED', 'CLEARED'], true)) {
            $is_registered = true;
        } elseif ($status === 'submitted' && $http_code >= 200 && $http_code < 300 && $validation_status !== 'ERROR') {
            $is_registered = true;
        }

        $status_label = 'Not submitted';
        $status_class = 'label-default';

        if ($is_registered) {
            $status_label = 'Registered in ZATCA';
            $status_class = 'label-success';
        } elseif ($status === 'failed') {
            $status_label = 'Not registered (failed)';
            $status_class = 'label-danger';
        } elseif ($status === 'submitted') {
            $status_label = 'Submitted (pending/needs review)';
            $status_class = 'label-warning';
        }

        $zatca_request_id = '';
        foreach (['requestID', 'requestId', 'request_id', 'clearanceRequestID', 'reportingRequestID'] as $request_key) {
            if (isset($response[$request_key]) && trim((string) $response[$request_key]) !== '') {
                $zatca_request_id = trim((string) $response[$request_key]);
                break;
            }
        }

        $zatca_uuid = '';
        foreach (['uuid', 'UUID', 'invoiceUuid', 'invoiceUUID'] as $uuid_key) {
            if (isset($response[$uuid_key]) && trim((string) $response[$uuid_key]) !== '') {
                $zatca_uuid = trim((string) $response[$uuid_key]);
                break;
            }
        }
        if ($zatca_uuid === '' && isset($invoice['zatca_uuid']) && trim((string) $invoice['zatca_uuid']) !== '') {
            $zatca_uuid = trim((string) $invoice['zatca_uuid']);
        }

        $zatca_invoice_hash = '';
        foreach (['invoiceHash', 'invoice_hash', 'hash'] as $hash_key) {
            if (isset($response[$hash_key]) && trim((string) $response[$hash_key]) !== '') {
                $zatca_invoice_hash = trim((string) $response[$hash_key]);
                break;
            }
        }
        if ($zatca_invoice_hash === '' && isset($invoice['zatca_invoice_hash']) && trim((string) $invoice['zatca_invoice_hash']) !== '') {
            $zatca_invoice_hash = trim((string) $invoice['zatca_invoice_hash']);
        }

        return [
            'has_submission' => ($status !== '' || $http_code > 0 || $response_raw !== ''),
            'is_registered' => $is_registered,
            'status_label' => $status_label,
            'status_class' => $status_class,
            'submission_status' => $status,
            'http_code' => $http_code,
            'reporting_status' => $reporting_status,
            'validation_status' => $validation_status,
            'errors' => $errors,
            'warnings' => $warnings,
            'infos' => $infos,
            'counts' => [
                'errors' => count($errors),
                'warnings' => count($warnings),
                'infos' => count($infos),
            ],
            'validation_messages' => $validation_messages,
            'ignored_prepayment_warning' => $has_ignored_prepayment_warning,
            'submitted_at' => $submitted_at,
            'zatca_request_id' => $zatca_request_id,
            'zatca_uuid' => $zatca_uuid,
            'zatca_invoice_hash' => $zatca_invoice_hash,
            'raw_response' => $response_raw,
        ];
    }

    protected static function collectValidationMessages(array $response, $ignoreSinglePrepaymentWarning = false)
    {
        $messages = [];

        $validation = isset($response['validationResults']) && is_array($response['validationResults'])
            ? $response['validationResults']
            : [];

        foreach (['errorMessages', 'warningMessages', 'infoMessages'] as $bucket) {
            if (!isset($validation[$bucket]) || !is_array($validation[$bucket])) {
                continue;
            }

            foreach ($validation[$bucket] as $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $code = isset($entry['code']) ? strtoupper(trim((string) $entry['code'])) : '';
                $message = isset($entry['message']) ? trim((string) $entry['message']) : '';

                if ($code === '' && $message === '') {
                    continue;
                }

                if ($ignoreSinglePrepaymentWarning && $bucket === 'warningMessages' && $code === 'BR-KSA-80') {
                    continue;
                }

                $messages[] = self::toFriendlyValidationMessage($bucket, $code, $message);
            }
        }

        return $messages;
    }

    protected static function toFriendlyValidationMessage($bucket, $code, $rawMessage)
    {
        $bucketLabel = 'Note';
        if ($bucket === 'errorMessages') {
            $bucketLabel = 'Error';
        } elseif ($bucket === 'warningMessages') {
            $bucketLabel = 'Warning';
        } elseif ($bucket === 'infoMessages') {
            $bucketLabel = 'Info';
        }

        $code = strtoupper(trim((string) $code));
        $rawMessage = trim((string) $rawMessage);

        switch ($code) {
            case 'BR-KSA-80':
                return $bucketLabel . ': Advance payment values do not match. If this invoice has no advance payment, remove advance-payment fields. If it has advance payment, ensure advance total = taxable amount + VAT. (Code: BR-KSA-80)';

            case 'BR-KSA-F-13':
                return $bucketLabel . ': Buyer/Seller ID format is invalid. Check the ID type and number (for example VAT, CRN, national ID) and re-save customer details. (Code: BR-KSA-F-13)';

            case 'BR-KSA-F-06-C23':
                return $bucketLabel . ': Buyer street address is missing or invalid. Add a valid street in customer address. (Code: BR-KSA-F-06-C23)';

            case 'BR-KSA-F-06-C25':
                return $bucketLabel . ': Buyer city is missing or invalid. Add a valid city in customer address. (Code: BR-KSA-F-06-C25)';

            case 'BR-KSA-10':
                return $bucketLabel . ': Buyer address is incomplete. For non-SA buyer country, street, city, and country code are required. (Code: BR-KSA-10)';

            case 'BR-KSA-15':
                return $bucketLabel . ': Supply date is missing for this tax invoice type. Add supply date and submit again. (Code: BR-KSA-15)';

            case 'XSD_ZATCA_VALID':
                return 'Info: XML format is valid according to ZATCA/UBL rules. This line is good and does not need fixing.';
        }

        if ($code !== '' && $rawMessage !== '') {
            return $bucketLabel . ': ' . $rawMessage . ' (Code: ' . $code . ')';
        }

        if ($code !== '') {
            return $bucketLabel . ': Validation issue found. (Code: ' . $code . ')';
        }

        if ($rawMessage !== '') {
            return $bucketLabel . ': ' . $rawMessage;
        }

        return $bucketLabel . ': Validation feedback received.';
    }

    protected static function hasOnlyIgnorablePrepaymentWarning(array $response, $invoiceXml)
    {
        $warnings = isset($response['validationResults']['warningMessages']) && is_array($response['validationResults']['warningMessages'])
            ? $response['validationResults']['warningMessages']
            : [];
        $errors = isset($response['validationResults']['errorMessages']) && is_array($response['validationResults']['errorMessages'])
            ? $response['validationResults']['errorMessages']
            : [];

        if (!empty($errors) || count($warnings) !== 1) {
            return false;
        }

        $warning = $warnings[0];
        $code = isset($warning['code']) ? strtoupper(trim((string) $warning['code'])) : '';
        if ($code !== 'BR-KSA-80') {
            return false;
        }

        $invoiceXml = (string) $invoiceXml;

        if ($invoiceXml === '') {
            return false;
        }

        if (strpos($invoiceXml, '<cbc:PrepaidAmount') !== false) {
            return false;
        }

        if (strpos($invoiceXml, '<cbc:DocumentTypeCode>386</cbc:DocumentTypeCode>') !== false) {
            return false;
        }

        return true;
    }

    protected static function resolvePemValue($rawValue, $allowFilePath = true)
    {
        $rawValue = trim((string) $rawValue);

        if ($rawValue === '') {
            return '';
        }

        if (strpos($rawValue, '-----BEGIN') !== false) {
            return str_replace('\\n', "\n", $rawValue);
        }

        if ($allowFilePath && file_exists($rawValue)) {
            return file_get_contents($rawValue);
        }

        return '';
    }

    protected static function describePemSourceIssue($rawValue, $label)
    {
        $rawValue = trim((string) $rawValue);
        $label = trim((string) $label) !== '' ? trim((string) $label) : 'value';

        if ($rawValue === '') {
            return 'Configured ' . $label . ' value is empty.';
        }

        if (strpos($rawValue, '-----BEGIN') !== false) {
            return 'Configured ' . $label . ' PEM content is empty or invalid.';
        }

        if (!file_exists($rawValue)) {
            return 'Configured ' . $label . ' file was not found: ' . $rawValue;
        }

        if (!is_readable($rawValue)) {
            return 'Configured ' . $label . ' file is not readable: ' . $rawValue;
        }

        if (@filesize($rawValue) === 0) {
            return 'Configured ' . $label . ' file is empty: ' . $rawValue;
        }

        return 'Configured ' . $label . ' content is invalid or could not be parsed.';
    }

    protected static function resolveTextValue($rawValue)
    {
        $rawValue = trim((string) $rawValue);

        if ($rawValue === '') {
            return '';
        }

        // DB-only mode: do not resolve server file paths for CSR/text values.
        return $rawValue;
    }

    protected static function logZatcaDiagnostic($message)
    {
        $log_file = defined('APP_STORAGE_PATH') 
            ? APP_STORAGE_PATH . '/logs/zatca_diagnostic.log'
            : sys_get_temp_dir() . '/zatca_diagnostic.log';
        
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] " . $message . "\n";
        @error_log($log_entry, 3, $log_file);
    }

    protected static function detectCsrEllipticCurve($csrPemContent)
    {
        $csrPemContent = trim((string) $csrPemContent);
        
        if ($csrPemContent === '' || strpos($csrPemContent, 'BEGIN CERTIFICATE REQUEST') === false) {
            return null;
        }
        
        // Extract base64 from PEM
        $csr_b64 = preg_replace('/-----BEGIN CERTIFICATE REQUEST-----/', '', $csrPemContent);
        $csr_b64 = preg_replace('/-----END CERTIFICATE REQUEST-----/', '', $csr_b64);
        $csr_b64 = preg_replace('/\s+/', '', $csr_b64);
        
        // Decode to DER binary
        $der = base64_decode($csr_b64, true);
        if ($der === false || strlen($der) < 20) {
            return null;
        }
        
        // Look for elliptic curve OID in DER structure:
        // prime256v1 (P-256): 1.2.840.10045.3.1.7 → hex: 06 08 2A 86 48 CE 3D 03 01 07
        // secp256k1: 1.3.132.0.10 → hex: 06 05 2B 81 04 00 0A
        
        $prime256v1_oid = "\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";
        $secp256k1_oid = "\x06\x05\x2B\x81\x04\x00\x0A";
        
        if (strpos($der, $prime256v1_oid) !== false) {
            return 'prime256v1';
        }
        if (strpos($der, $secp256k1_oid) !== false) {
            return 'secp256k1';
        }
        
        return 'unknown';
    }

    protected static function normalizeApiBase($apiBase)
    {
        $apiBase = trim((string) $apiBase);

        if ($apiBase === '') {
            $apiBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal';
        }

        return rtrim($apiBase, '/') . '/';
    }

    /**
     * Lightweight ZATCA connectivity probe.
     * Returns true when the ZATCA API responds with any HTTP code > 0 and != 401/403.
     * Result is cached in the PHP session for 5 minutes to avoid probing on every page load.
     */
    public static function checkConnectivity($config)
    {
        $cache_key  = 'zatca_connectivity_ok';
        $cache_time = 'zatca_connectivity_ts';
        $ttl        = 300; // 5 minutes

        if (session_status() === PHP_SESSION_ACTIVE) {
            if (
                isset($_SESSION[$cache_key], $_SESSION[$cache_time]) &&
                (time() - (int) $_SESSION[$cache_time]) < $ttl
            ) {
                return (bool) $_SESSION[$cache_key];
            }
        }

        $api_base = self::resolveApiBase($config);

        if ($api_base === '') {
            return false;
        }

        // Resolve active credentials (production first, fall back to compliance).
        $binary_token = '';
        $secret       = '';
        foreach (['zatca_production_binary_security_token', 'zatca_binary_security_token'] as $k) {
            if (!empty($config[$k]) && trim((string) $config[$k]) !== '') {
                $binary_token = trim((string) $config[$k]);
                break;
            }
        }
        foreach (['zatca_production_secret', 'zatca_secret'] as $k) {
            if (!empty($config[$k]) && trim((string) $config[$k]) !== '') {
                $secret = trim((string) $config[$k]);
                break;
            }
        }

        if ($binary_token === '' || $secret === '') {
            // No credentials — mark as not connected.
            self::_cacheConnectivity(false);
            return false;
        }

        // Phase-2 requires a valid private key for signing — treat missing/invalid key as not connected.
        $phase2_enabled = !isset($config['zatca_phase2_enabled']) || (string) $config['zatca_phase2_enabled'] === '1';
        if ($phase2_enabled) {
            $private_key_source = '';
            foreach (['zatca_production_private_key', 'zatca_private_key'] as $k) {
                if (!empty($config[$k]) && trim((string) $config[$k]) !== '') {
                    $private_key_source = trim((string) $config[$k]);
                    break;
                }
            }

            $private_pem = self::resolvePemValue($private_key_source, false);
            if ($private_pem === '') {
                self::_cacheConnectivity(false);
                return false;
            }
        }

        try {
            $invoice_type = isset($config['zatca_invoice_type'])
                ? strtolower(trim((string) $config['zatca_invoice_type']))
                : 'simplified';

            $probe_uuid  = function_exists('random_bytes')
                ? bin2hex(random_bytes(16))
                : md5(uniqid('zatca-probe', true));
            $probe_hash  = base64_encode(hash('sha256', 'zatca-probe-' . $probe_uuid, true));
            $probe_b64   = base64_encode('ZATCA_PROBE_' . $probe_uuid);
            $endpoint    = $invoice_type === 'standard'
                ? 'invoices/clearance/single'
                : 'invoices/reporting/single';

            $client = new \GuzzleHttp\Client([
                'base_uri'    => $api_base,
                'timeout'     => 8,
                'http_errors' => false,
            ]);

            $response = $client->post($endpoint, [
                'headers' => [
                    'Accept'          => 'application/json',
                    'Accept-Language' => 'en',
                    'Accept-Version'  => 'V2',
                    'Authorization'   => 'Basic ' . base64_encode($binary_token . ':' . $secret),
                    'Content-Type'    => 'application/json',
                ],
                'json' => [
                    'invoiceHash' => $probe_hash,
                    'uuid'        => $probe_uuid,
                    'invoice'     => $probe_b64,
                ],
            ]);

            $code      = (int) $response->getStatusCode();
            // A 200 or a ZATCA validation-error (4xx other than auth) both confirm reachability.
            $connected = $code > 0 && !in_array($code, [0, 401, 403], true);

            self::_cacheConnectivity($connected);
            return $connected;
        } catch (\Throwable $e) {
            self::_cacheConnectivity(false);
            return false;
        }
    }

    private static function _cacheConnectivity($value)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['zatca_connectivity_ok'] = (bool) $value;
            $_SESSION['zatca_connectivity_ts'] = time();
        }
    }

    protected static function resolveApiBase($config)
    {
        $environment = isset($config['zatca_environment'])
            ? strtolower(trim((string) $config['zatca_environment']))
            : 'sandbox';

        if ($environment !== 'sandbox' && $environment !== 'simulation' && $environment !== 'production') {
            $environment = 'sandbox';
        }

        $defaultBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal';
        if ($environment === 'simulation') {
            $defaultBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation';
        } elseif ($environment === 'production') {
            $defaultBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core';
        }

        $apiBase = isset($config['zatca_api_base_url']) && trim((string) $config['zatca_api_base_url']) !== ''
            ? trim((string) $config['zatca_api_base_url'])
            : (isset($config['zatca_api_base']) && trim((string) $config['zatca_api_base']) !== ''
                ? trim((string) $config['zatca_api_base'])
                : $defaultBase
            );

        $normalizedInputBase = strtolower(rtrim((string) $apiBase, '/'));
        if ($environment === 'production' && strpos($normalizedInputBase, '/e-invoicing/core') === false) {
            self::logZatcaDiagnostic('resolveApiBase override: forcing production core endpoint because configured URL was ' . (string) $apiBase);
            $apiBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core';
        } elseif ($environment === 'simulation' && strpos($normalizedInputBase, '/e-invoicing/simulation') === false) {
            self::logZatcaDiagnostic('resolveApiBase override: forcing simulation endpoint because configured URL was ' . (string) $apiBase);
            $apiBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation';
        } elseif ($environment === 'sandbox' && strpos($normalizedInputBase, '/e-invoicing/developer-portal') === false) {
            self::logZatcaDiagnostic('resolveApiBase override: forcing sandbox endpoint because configured URL was ' . (string) $apiBase);
            $apiBase = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal';
        }

        return self::normalizeApiBase($apiBase);
    }

    protected static function normalizeEnvironmentKey($environment)
    {
        $environment = strtolower(trim((string) $environment));
        if (!in_array($environment, ['sandbox', 'simulation', 'production'], true)) {
            $environment = 'sandbox';
        }
        return $environment;
    }

    /**
     * Atomically allocates the next ICV (Invoice Counter Value) for an environment.
     *
     * Uses a short-lived transaction with SELECT ... FOR UPDATE so concurrent submissions
     * never receive the same ICV. The counter is committed immediately (not held open across
     * the ZATCA HTTP call), so a failed submission does not roll the value back - the ICV
     * sequence can have gaps on failure, but this avoids holding a DB transaction open across
     * network I/O on the app's shared connection, which would risk blocking every other query
     * in the request/connection if a submission ever hung.
     */
    protected static function acquireNextIcv($environment)
    {
        $environment = self::normalizeEnvironmentKey($environment);
        $setting = 'zatca_icv_counter__' . $environment;
        $pdo = ORM::get_db();

        // Ensure the row exists before opening the locking transaction, so the FOR UPDATE
        // below always has a concrete row to lock (avoids gap-lock/insert-race edge cases).
        $exists = $pdo->prepare('SELECT COUNT(*) AS cnt FROM sys_appconfig WHERE setting = :setting');
        $exists->execute(['setting' => $setting]);
        if ((int) $exists->fetch(PDO::FETCH_ASSOC)['cnt'] === 0) {
            try {
                $insert = $pdo->prepare('INSERT INTO sys_appconfig (setting, value) VALUES (:setting, :value)');
                $insert->execute(['setting' => $setting, 'value' => '0']);
            } catch (Throwable $e) {
                // Another request created it concurrently - fine, continue to the locked read.
            }
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT value FROM sys_appconfig WHERE setting = :setting FOR UPDATE');
            $stmt->execute(['setting' => $setting]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current = $row ? (int) $row['value'] : 0;
            $next = $current + 1;

            $update = $pdo->prepare('UPDATE sys_appconfig SET value = :value WHERE setting = :setting');
            $update->execute(['value' => (string) $next, 'setting' => $setting]);

            $pdo->commit();

            return ['success' => true, 'icv' => $next];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Failed to allocate ICV: ' . $e->getMessage()];
        }
    }

    /**
     * Returns the invoice_hash of the most recent successfully-logged submission for this
     * environment (PASS/WARNING), or the ZATCA-mandated genesis PIH if none exists yet -
     * i.e. this is truly the first invoice ever submitted in this environment's chain.
     */
    protected static function getPreviousInvoiceHash($environment)
    {
        $environment = self::normalizeEnvironmentKey($environment);
        $pdo = ORM::get_db();

        try {
            $stmt = $pdo->prepare(
                "SELECT invoice_hash FROM sys_zatca_invoice_log
                 WHERE environment = :environment AND status IN ('PASS', 'WARNING') AND invoice_hash IS NOT NULL AND invoice_hash != ''
                 ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute(['environment' => $environment]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && trim((string) $row['invoice_hash']) !== '') {
                return trim((string) $row['invoice_hash']);
            }
        } catch (Throwable $e) {
            self::logZatcaDiagnostic('getPreviousInvoiceHash lookup failed, falling back to genesis PIH: ' . $e->getMessage());
        }

        return self::ZATCA_GENESIS_PIH;
    }

    /**
     * Appends one row to the append-only ZATCA submission audit log. Never updated/deleted
     * by application code - status history for an invoice is a sequence of new rows, not a
     * mutated single row.
     */
    protected static function logInvoiceSubmission(
        $invoiceId,
        $documentType,
        $environment,
        $icv,
        $invoiceUuid,
        $invoiceHash,
        $status,
        $httpCode,
        $message
    ) {
        try {
            $pdo = ORM::get_db();
            $stmt = $pdo->prepare(
                'INSERT INTO sys_zatca_invoice_log
                    (invoice_id, document_type, environment, icv, invoice_uuid, invoice_hash, status, http_code, message, created_at)
                 VALUES
                    (:invoice_id, :document_type, :environment, :icv, :invoice_uuid, :invoice_hash, :status, :http_code, :message, NOW())'
            );
            $stmt->execute([
                'invoice_id' => (int) $invoiceId,
                'document_type' => (string) $documentType,
                'environment' => self::normalizeEnvironmentKey($environment),
                'icv' => (int) $icv,
                'invoice_uuid' => $invoiceUuid !== null ? (string) $invoiceUuid : null,
                'invoice_hash' => $invoiceHash !== null ? (string) $invoiceHash : null,
                'status' => (string) $status,
                'http_code' => $httpCode !== null ? (int) $httpCode : null,
                'message' => $message !== null ? (string) $message : null,
            ]);
        } catch (Throwable $e) {
            self::logZatcaDiagnostic('logInvoiceSubmission failed to write audit row: ' . $e->getMessage());
        }
    }

    /**
     * Upserts one row per (environment, scenario) so the 6 compliance-check results survive
     * page refreshes and can gate the Production CSID step, instead of only keeping the last
     * raw HTTP response body.
     */
    protected static function persistComplianceResult($environment, $scenarioKey, $status, $httpCode = null, $message = null)
    {
        try {
            $pdo = ORM::get_db();
            $stmt = $pdo->prepare(
                'INSERT INTO sys_zatca_compliance_results (environment, scenario_key, status, http_code, message, updated_at)
                 VALUES (:environment, :scenario_key, :status, :http_code, :message, NOW())
                 ON DUPLICATE KEY UPDATE status = VALUES(status), http_code = VALUES(http_code), message = VALUES(message), updated_at = VALUES(updated_at)'
            );
            $stmt->execute([
                'environment' => self::normalizeEnvironmentKey($environment),
                'scenario_key' => (string) $scenarioKey,
                'status' => (string) $status,
                'http_code' => $httpCode !== null ? (int) $httpCode : null,
                'message' => $message !== null ? (string) $message : null,
            ]);
        } catch (Throwable $e) {
            self::logZatcaDiagnostic('persistComplianceResult failed to write row: ' . $e->getMessage());
        }
    }

    /**
     * Returns true only if all 6 required compliance scenarios have a persisted PASS for
     * this environment - used to gate the Production CSID step.
     */
    public static function complianceChecksPassed($environment, array $requiredScenarioKeys)
    {
        try {
            $pdo = ORM::get_db();
            $stmt = $pdo->prepare(
                "SELECT scenario_key FROM sys_zatca_compliance_results WHERE environment = :environment AND status = 'PASS'"
            );
            $stmt->execute(['environment' => self::normalizeEnvironmentKey($environment)]);
            $passed = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'scenario_key');
            $missing = array_diff($requiredScenarioKeys, $passed);
            return empty($missing) ? [] : array_values($missing);
        } catch (Throwable $e) {
            self::logZatcaDiagnostic('complianceChecksPassed lookup failed: ' . $e->getMessage());
            return $requiredScenarioKeys;
        }
    }

    protected static function canonicalizeBase64($value)
    {
        $value = preg_replace('/\s+/', '', (string) $value);

        if ($value === '') {
            return '';
        }

        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return '';
        }

        return base64_encode($decoded);
    }

    protected static function normalizeOtpValue($otp)
    {
        $otp = trim((string) $otp);
        if ($otp === '') {
            return '';
        }

        // Normalize Arabic-Indic digits to ASCII digits.
        $otp = strtr($otp, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        return preg_replace('/\D+/', '', $otp);
    }

    protected static function buildComplianceCsrCandidates($resolvedCsrValue, $normalizedCsr)
    {
        $candidates = [];
        $seen = [];

        $resolvedCsrValue = trim((string) $resolvedCsrValue);
        if (stripos($resolvedCsrValue, 'BEGIN CERTIFICATE REQUEST') !== false) {
            $pem = str_replace(["\r\n", "\r"], "\n", $resolvedCsrValue);
            $pem = trim($pem) . "\n";
            $pemBase64 = base64_encode($pem);

            if ($pemBase64 !== '' && !isset($seen[$pemBase64])) {
                $candidates[] = [
                    'label' => 'pem_base64',
                    'value' => $pemBase64,
                ];
                $seen[$pemBase64] = true;
            }
        }

        // Fallback to DER base64 only when PEM text was not provided.
        $normalizedCsr = self::canonicalizeBase64($normalizedCsr);
        if ($normalizedCsr !== '' && !isset($seen[$normalizedCsr])) {
            $candidates[] = [
                'label' => 'der_base64',
                'value' => $normalizedCsr,
            ];
            $seen[$normalizedCsr] = true;
        }

        return $candidates;
    }

    protected static function normalizeCsr($rawCsr)
    {
        $rawCsr = trim((string) $rawCsr);
        $debug_log = [];

        $debug_log[] = "normalizeCsr input length: " . strlen($rawCsr);
        $debug_log[] = "Input preview: " . substr($rawCsr, 0, 100);

        if ($rawCsr === '') {
            $debug_log[] = "normalizeCsr: Input is empty";
            self::logZatcaDiagnostic(implode("\n", $debug_log));
            return '';
        }

        // Some users paste a base64-encoded full PEM block (starts with LS0tLS...).
        // Decode once when it looks like wrapped PEM text, then continue normalization.
        $decoded_candidate = base64_decode($rawCsr, true);
        if (
            $decoded_candidate !== false &&
            stripos($decoded_candidate, 'BEGIN CERTIFICATE REQUEST') !== false
        ) {
            $debug_log[] = "normalizeCsr: Decoded base64-wrapped PEM";
            $rawCsr = $decoded_candidate;
        }

        $debug_log[] = "Before regex strips, length: " . strlen($rawCsr);
        $rawCsr = preg_replace('/-----BEGIN CERTIFICATE REQUEST-----/i', '', $rawCsr);
        $rawCsr = preg_replace('/-----END CERTIFICATE REQUEST-----/i', '', $rawCsr);
        $rawCsr = preg_replace('/\s+/', '', $rawCsr);
        $debug_log[] = "After regex strips, length: " . strlen($rawCsr);

        if ($rawCsr === '') {
            $debug_log[] = "normalizeCsr: Empty after stripping PEM markers/whitespace";
            self::logZatcaDiagnostic(implode("\n", $debug_log));
            return '';
        }

        // Ensure resulting value is a compact base64 string.
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $rawCsr)) {
            $debug_log[] = "normalizeCsr: Contains non-base64 characters after normalization";
            self::logZatcaDiagnostic(implode("\n", $debug_log));
            return '';
        }

        $der = base64_decode($rawCsr, true);
        if ($der === false || strlen($der) < 32) {
            $debug_log[] = "normalizeCsr: base64_decode failed or DER too short";
            self::logZatcaDiagnostic(implode("\n", $debug_log));
            return '';
        }

        // CSR ASN.1 DER should start with a SEQUENCE (0x30).
        if (ord($der[0]) !== 0x30) {
            $debug_log[] = "normalizeCsr: DER does not start with ASN.1 SEQUENCE";
            self::logZatcaDiagnostic(implode("\n", $debug_log));
            return '';
        }

        $debug_log[] = "normalizeCsr: DER validation passed";
        $debug_log[] = "Final output preview: " . substr($rawCsr, 0, 100);

        self::logZatcaDiagnostic(implode("\n", $debug_log));
        return trim((string) $rawCsr);
    }

    protected static function binaryTokenToPemCertificate($binaryToken)
    {
        $rawToken = trim((string) $binaryToken);
        if ($rawToken === '') {
            return '';
        }

        if (strpos($rawToken, '-----BEGIN CERTIFICATE-----') !== false) {
            return self::normalizePemForSdk($rawToken, 'CERTIFICATE');
        }

        $binaryToken = preg_replace('/\s+/', '', $rawToken);
        if ($binaryToken === '') {
            return '';
        }

        // Some gateways return token as base64(base64(der-certificate)); normalize to base64(der).
        $decoded = base64_decode($binaryToken, true);
        if ($decoded !== false && preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $decoded)) {
            $candidate = preg_replace('/\s+/', '', (string) $decoded);
            if (strpos($candidate, 'MI') === 0) {
                $binaryToken = $candidate;
            }
        }

        // If token was base64(der), re-encode DER bytes into PEM body.
        $der = base64_decode($binaryToken, true);
        if ($der !== false && strlen($der) > 0 && ord($der[0]) === 0x30) {
            $binaryToken = base64_encode($der);
        }

        return self::normalizePemForSdk($binaryToken, 'CERTIFICATE');
    }

    protected static function normalizePemForSdk($pemValue, $label)
    {
        $pemValue = trim((string) $pemValue);
        $label = trim((string) $label);

        if ($pemValue === '' || $label === '') {
            return '';
        }

        if (strpos($pemValue, '-----BEGIN') !== false) {
            $pemValue = str_replace(["\r\n", "\r"], "\n", $pemValue);

            if (substr($pemValue, -1) !== "\n") {
                $pemValue .= "\n";
            }

            return $pemValue;
        }

        $raw = preg_replace('/\s+/', '', $pemValue);
        if ($raw === '') {
            return '';
        }

        return '-----BEGIN ' . $label . "-----\n"
            . chunk_split($raw, 64, "\n")
            . '-----END ' . $label . "-----\n";
    }

    protected static function normalizePrivateKeyForSdk($privatePem, $workingDir)
    {
        $privatePem = self::normalizePemForSdk($privatePem, 'PRIVATE KEY');
        if ($privatePem === '') {
            return '';
        }

        if (stripos($privatePem, 'BEGIN EC PRIVATE KEY') !== false) {
            return $privatePem;
        }

        // Some SDK builds reject PKCS#8 (BEGIN PRIVATE KEY) and only accept
        // SEC1 EC key blocks (BEGIN EC PRIVATE KEY).
        $openssl_bin = self::resolveOpenSslBinary();
        if ($openssl_bin !== '') {
            $input_file = rtrim($workingDir, '\/') . DIRECTORY_SEPARATOR . 'sdk_private_pkcs8.pem';
            $output_file = rtrim($workingDir, '\/') . DIRECTORY_SEPARATOR . 'sdk_private_ec.pem';

            @file_put_contents($input_file, $privatePem);

            $convert_command = escapeshellarg($openssl_bin)
                . ' pkey -in '
                . escapeshellarg($input_file)
                . ' -out '
                . escapeshellarg($output_file)
                . ' -traditional';

            self::executeCommand($convert_command, $workingDir);

            if (is_file($output_file)) {
                $converted = (string) @file_get_contents($output_file);
                if (stripos($converted, 'BEGIN EC PRIVATE KEY') !== false) {
                    return self::normalizePemForSdk($converted, 'EC PRIVATE KEY');
                }
            }
        }

        $convertedByPhp = self::convertPkcs8ToEcPrivateKeyPem($privatePem);
        if ($convertedByPhp !== '') {
            return $convertedByPhp;
        }

        return $privatePem;
    }

    protected static function convertPkcs8ToEcPrivateKeyPem($privatePem)
    {
        if (!function_exists('openssl_pkey_get_private') || !function_exists('openssl_pkey_get_details')) {
            return '';
        }

        $resource = @openssl_pkey_get_private($privatePem);
        if ($resource === false) {
            return '';
        }

        $details = @openssl_pkey_get_details($resource);
        if (!is_array($details) || !isset($details['ec']) || !is_array($details['ec'])) {
            return '';
        }

        $ec = $details['ec'];
        if (!isset($ec['d']) || $ec['d'] === '') {
            return '';
        }

        $privateScalar = (string) $ec['d'];
        $curveOid = isset($ec['curve_oid']) && trim((string) $ec['curve_oid']) !== ''
            ? trim((string) $ec['curve_oid'])
            : '1.3.132.0.10';

        $version = "\x02\x01\x01";
        $privateOctet = "\x04" . self::asn1EncodeLength(strlen($privateScalar)) . $privateScalar;
        $curveOidDer = self::asn1EncodeOid($curveOid);
        if ($curveOidDer === '') {
            return '';
        }

        $parameters = "\xA0" . self::asn1EncodeLength(strlen($curveOidDer)) . $curveOidDer;
        $publicPoint = '';
        if (isset($ec['x'], $ec['y']) && $ec['x'] !== '' && $ec['y'] !== '') {
            $publicPointRaw = "\x04" . (string) $ec['x'] . (string) $ec['y'];
            $bitString = "\x03"
                . self::asn1EncodeLength(strlen($publicPointRaw) + 1)
                . "\x00"
                . $publicPointRaw;
            $publicPoint = "\xA1" . self::asn1EncodeLength(strlen($bitString)) . $bitString;
        }

        $ecPrivateSequence = $version . $privateOctet . $parameters . $publicPoint;
        $der = "\x30" . self::asn1EncodeLength(strlen($ecPrivateSequence)) . $ecPrivateSequence;

        return "-----BEGIN EC PRIVATE KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END EC PRIVATE KEY-----\n";
    }

    protected static function asn1EncodeLength($length)
    {
        $length = (int) $length;
        if ($length < 0) {
            return '';
        }

        if ($length < 128) {
            return chr($length);
        }

        $bytes = '';
        while ($length > 0) {
            $bytes = chr($length & 0xFF) . $bytes;
            $length >>= 8;
        }

        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    protected static function asn1EncodeOid($oid)
    {
        $parts = explode('.', trim((string) $oid));
        if (count($parts) < 2) {
            return '';
        }

        $first = (int) $parts[0];
        $second = (int) $parts[1];
        if ($first < 0 || $first > 2 || $second < 0) {
            return '';
        }

        $encoded = chr(($first * 40) + $second);

        for ($i = 2; $i < count($parts); $i++) {
            $value = (int) $parts[$i];
            if ($value < 0) {
                return '';
            }

            $segment = chr($value & 0x7F);
            $value >>= 7;
            while ($value > 0) {
                $segment = chr(($value & 0x7F) | 0x80) . $segment;
                $value >>= 7;
            }

            $encoded .= $segment;
        }

        return "\x06" . self::asn1EncodeLength(strlen($encoded)) . $encoded;
    }

    protected static function resolveOpenSslBinary()
    {
        $candidates = [
            'openssl',
            'C:\\xampp\\apache\\bin\\openssl.exe',
            'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.exe',
            'C:\\Program Files\\OpenSSL-Win32\\bin\\openssl.exe',
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }

            if (strpos($candidate, DIRECTORY_SEPARATOR) !== false || strpos($candidate, ':') !== false) {
                if (is_file($candidate)) {
                    return $candidate;
                }
                continue;
            }

            $result = self::executeCommand(escapeshellarg($candidate) . ' version', null);
            if ((int) $result['exit_code'] === 0) {
                return $candidate;
            }
        }

        return '';
    }

    protected static function buildSdkSignedPackage($invoiceXml, $fallbackUuid, $config, $context = [])
    {
        $sdk_jar_source = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . 'zatca-envoice-sdk-203'
            . DIRECTORY_SEPARATOR
            . 'Apps'
            . DIRECTORY_SEPARATOR
            . 'cli-3.0.8-jar-with-dependencies.jar';
        $sdk_root_source = dirname(dirname($sdk_jar_source));

        if (!is_file($sdk_jar_source)) {
            return [
                'success' => false,
                'message' => 'ZATCA SDK jar was not found at: ' . $sdk_jar_source,
            ];
        }

        $java_bin = self::resolveJavaBinary();
        
        // Check if Java is actually available before trying to use it
        $java_check = self::executeCommand(escapeshellarg($java_bin) . ' -version', null, []);
        if ($java_check['exit_code'] !== 0) {
            // Java not available - try API client fallback for shared hosting
            return self::buildApiSignedPackage($invoiceXml, $fallbackUuid, $config, $context);
        }

        $temp_dir = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'ibilling_zatca_' . uniqid('', true);
        if (!@mkdir($temp_dir, 0777, true) && !is_dir($temp_dir)) {
            return [
                'success' => false,
                'message' => 'Unable to create temporary directory for ZATCA SDK execution.',
            ];
        }

        $unsigned_file = $temp_dir . DIRECTORY_SEPARATOR . 'invoice.xml';
        $signed_file = $temp_dir . DIRECTORY_SEPARATOR . 'signed-invoice.xml';
        $request_file = $temp_dir . DIRECTORY_SEPARATOR . 'api-request.json';

        // Use isolated runtime files per submission so web and CLI runs do not
        // depend on write access to the shared SDK installation directory.
        $sdk_root = $temp_dir
            . DIRECTORY_SEPARATOR
            . 'sdk-runtime';
        $sdk_jar = $sdk_root
            . DIRECTORY_SEPARATOR
            . 'Apps'
            . DIRECTORY_SEPARATOR
            . basename($sdk_jar_source);
        $sdk_working_dir = $sdk_root;
        $cert_dir = $sdk_root
            . DIRECTORY_SEPARATOR
            . 'Data'
            . DIRECTORY_SEPARATOR
            . 'Certificates';

        if (!self::copyDirectory($sdk_root_source, $sdk_root)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'Unable to prepare temporary SDK runtime directory for signing.',
            ];
        }

        if (!is_file($sdk_jar)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'Unable to locate ZATCA SDK jar inside temporary runtime.',
            ];
        }

        if (!@mkdir($cert_dir, 0777, true) && !is_dir($cert_dir)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'Unable to create SDK certificate directory for signing.',
            ];
        }

        $private_key_source = isset($config['zatca_private_key']) ? $config['zatca_private_key'] : '';
        $certificate_source = isset($config['zatca_production_certificate']) && trim((string) $config['zatca_production_certificate']) !== ''
            ? $config['zatca_production_certificate']
            : (isset($config['zatca_certificate']) ? $config['zatca_certificate'] : '');

        // Check if we're in compliance mode - use compliance certificate instead
        if (isset($context['compliance_mode']) && $context['compliance_mode'] === true && isset($context['compliance_certificate'])) {
            $certificate_source = $context['compliance_certificate'];
        }

        $private_pem = self::resolvePemValue($private_key_source, false);
        $certificate_pem = self::resolvePemValue($certificate_source);

        $private_raw = preg_replace('/-----BEGIN[^-]+-----|-----END[^-]+-----|\s+/', '', (string) $private_pem);
        $certificate_raw = preg_replace('/-----BEGIN[^-]+-----|-----END[^-]+-----|\s+/', '', (string) $certificate_pem);

        // Some responses store certificate as base64(base64(der)); normalize to base64(der).
        $decoded_certificate = base64_decode($certificate_raw, true);
        if (
            $decoded_certificate !== false
            && preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $decoded_certificate)
        ) {
            $decoded_candidate = preg_replace('/\s+/', '', (string) $decoded_certificate);
            if (strpos($decoded_candidate, 'MI') === 0) {
                $certificate_raw = $decoded_candidate;
            }
        }

        if ($private_raw === '' || $certificate_raw === '') {
            $detail_messages = [];

            if ($private_raw === '') {
                $detail_messages[] = self::describePemSourceIssue($private_key_source, 'private key');
            }

            if ($certificate_raw === '') {
                $detail_messages[] = self::describePemSourceIssue($certificate_source, 'certificate');
            }

            $detail_text = implode(' ', array_filter($detail_messages));
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => trim('Missing ZATCA private key or production certificate for SDK signing. ' . $detail_text),
            ];
        }

        $private_pem_for_sdk = self::normalizePrivateKeyForSdk($private_pem, $temp_dir);
        $certificate_pem_for_sdk = self::normalizePemForSdk($certificate_pem, 'CERTIFICATE');

        if ($private_pem_for_sdk === '' || $certificate_pem_for_sdk === '') {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'Missing or invalid PEM-formatted ZATCA private key or production certificate for SDK signing.',
            ];
        }

        $private_key_raw_for_sdk = preg_replace(
            '/-----BEGIN[^-]+-----|-----END[^-]+-----|\s+/',
            '',
            (string) $private_pem_for_sdk
        );
        if ($private_key_raw_for_sdk === '') {
            $private_key_raw_for_sdk = preg_replace(
                '/-----BEGIN[^-]+-----|-----END[^-]+-----|\s+/',
                '',
                (string) $private_pem
            );
        }

        $certificate_raw_for_sdk = preg_replace(
            '/-----BEGIN[^-]+-----|-----END[^-]+-----|\s+/',
            '',
            (string) $certificate_pem_for_sdk
        );
        if ($certificate_raw_for_sdk === '') {
            $certificate_raw_for_sdk = preg_replace(
                '/-----BEGIN[^-]+-----|-----END[^-]+-----|\s+/',
                '',
                (string) $certificate_pem
            );
        }

        // Different SDK builds can resolve different key/cert filenames.
        // Write all common variants to avoid null-file lookup failures.
        if (file_put_contents($cert_dir . DIRECTORY_SEPARATOR . 'ec-secp256k1-priv-key.pem', $private_key_raw_for_sdk) === false
            || file_put_contents($cert_dir . DIRECTORY_SEPARATOR . 'my-zatca-private-key.pem', $private_key_raw_for_sdk) === false
            || file_put_contents($cert_dir . DIRECTORY_SEPARATOR . 'PrivateKey.pem', $private_key_raw_for_sdk) === false
            || file_put_contents($cert_dir . DIRECTORY_SEPARATOR . 'privatekey.pem', $private_key_raw_for_sdk) === false
            || file_put_contents($cert_dir . DIRECTORY_SEPARATOR . 'private-key.pem', $private_key_raw_for_sdk) === false
            || file_put_contents($cert_dir . DIRECTORY_SEPARATOR . 'cert.pem', $certificate_raw_for_sdk) === false
            || file_put_contents($cert_dir . DIRECTORY_SEPARATOR . 'certificate.pem', $certificate_raw_for_sdk) === false
            || file_put_contents($sdk_root . DIRECTORY_SEPARATOR . 'PrivateKey.pem', $private_key_raw_for_sdk) === false
            || file_put_contents($sdk_root . DIRECTORY_SEPARATOR . 'privatekey.pem', $private_key_raw_for_sdk) === false
            || file_put_contents($sdk_root . DIRECTORY_SEPARATOR . 'cert.pem', $certificate_raw_for_sdk) === false) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'Unable to write temporary private key/certificate files for ZATCA SDK signing.',
            ];
        }

        $sdk_config_dir = $sdk_root . DIRECTORY_SEPARATOR . 'Configuration';
        $sdk_config_file = $sdk_config_dir . DIRECTORY_SEPARATOR . 'config.json';
        $sdk_defaults_file = $sdk_config_dir . DIRECTORY_SEPARATOR . 'defaults.json';
        $usage_file = $sdk_config_dir . DIRECTORY_SEPARATOR . 'usage.txt';
        $sdk_cert_password = isset($config['zatca_private_key_passphrase'])
            ? trim((string) $config['zatca_private_key_passphrase'])
            : '';
        if ($sdk_cert_password === '') {
            $sdk_cert_password = '123456789';
        }

        $config_payload = [
            'xsdPath' => $sdk_root . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Schemas' . DIRECTORY_SEPARATOR . 'xsds' . DIRECTORY_SEPARATOR . 'UBL2.1' . DIRECTORY_SEPARATOR . 'xsd' . DIRECTORY_SEPARATOR . 'maindoc' . DIRECTORY_SEPARATOR . 'UBL-Invoice-2.1.xsd',
            'enSchematron' => $sdk_root . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Rules' . DIRECTORY_SEPARATOR . 'schematrons' . DIRECTORY_SEPARATOR . 'CEN-EN16931-UBL.xsl',
            'zatcaSchematron' => $sdk_root . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Rules' . DIRECTORY_SEPARATOR . 'schematrons' . DIRECTORY_SEPARATOR . '20210819_ZATCA_E-invoice_Validation_Rules.xsl',
            'certPath' => $cert_dir . DIRECTORY_SEPARATOR . 'cert.pem',
            'privateKeyPath' => $cert_dir . DIRECTORY_SEPARATOR . 'ec-secp256k1-priv-key.pem',
            'pihPath' => $sdk_root . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'PIH' . DIRECTORY_SEPARATOR . 'pih.txt',
            'certPassword' => $sdk_cert_password,
            'inputPath' => $sdk_root . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'Input',
            'usagePathFile' => $usage_file,
        ];

        if (!is_dir($sdk_config_dir) && !@mkdir($sdk_config_dir, 0777, true) && !is_dir($sdk_config_dir)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'Unable to prepare SDK configuration directory for signing.',
            ];
        }

        $config_json = json_encode($config_payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($config_json === false
            || @file_put_contents($sdk_config_file, $config_json) === false
            || @file_put_contents($sdk_defaults_file, $config_json) === false) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'Unable to write SDK configuration/defaults files for signing runtime.',
            ];
        }

        $unsigned_xml = self::ensureUblSignatureNamespaces((string) $invoiceXml);
        file_put_contents($unsigned_file, $unsigned_xml);

        $sign_command = escapeshellarg($java_bin)
            . ' -jar '
            . escapeshellarg($sdk_jar)
            . ' -sign -invoice '
            . escapeshellarg($unsigned_file)
            . ' -privateKey '
            . escapeshellarg($cert_dir . DIRECTORY_SEPARATOR . 'ec-secp256k1-priv-key.pem')
            . ' -signedInvoice '
            . escapeshellarg($signed_file);

        $sdk_env = [
            'SDK_CONFIG' => $sdk_config_file,
        ];

        $sign_result = self::executeCommand($sign_command, $sdk_working_dir, $sdk_env);
        if ($sign_result['exit_code'] !== 0 || !is_file($signed_file)) {
            $sign_debug = [
                'sdk_working_dir' => $sdk_working_dir,
                'sdk_config_env' => $sdk_config_file,
                'jar_exists' => is_file($sdk_jar) ? 'yes' : 'no',
                'private_key_exists' => is_file($cert_dir . DIRECTORY_SEPARATOR . 'ec-secp256k1-priv-key.pem') ? 'yes' : 'no',
                'private_key_size' => is_file($cert_dir . DIRECTORY_SEPARATOR . 'ec-secp256k1-priv-key.pem') ? (string) filesize($cert_dir . DIRECTORY_SEPARATOR . 'ec-secp256k1-priv-key.pem') : '0',
                'cert_exists' => is_file($cert_dir . DIRECTORY_SEPARATOR . 'cert.pem') ? 'yes' : 'no',
                'config_exists' => is_file($sdk_config_file) ? 'yes' : 'no',
                'defaults_exists' => is_file($sdk_defaults_file) ? 'yes' : 'no',
            ];
            $sign_debug_text = ' Runtime debug: ' . json_encode($sign_debug);
            $raw_output = trim((string) $sign_result['output']);
            if ($raw_output !== '') {
                $sign_debug_text .= ' Raw SDK output: ' . $raw_output;
            }
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'ZATCA SDK invoice signing failed. ' . self::summarizeCommandOutput($sign_result['output']) . $sign_debug_text,
            ];
        }

        $request_command = escapeshellarg($java_bin)
            . ' -jar '
            . escapeshellarg($sdk_jar)
            . ' -invoice '
            . escapeshellarg($signed_file)
            . ' -invoiceRequest -apiRequest '
            . escapeshellarg($request_file);

        $request_result = self::executeCommand($request_command, $sdk_working_dir, $sdk_env);
        if ($request_result['exit_code'] !== 0 || !is_file($request_file)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'ZATCA SDK invoiceRequest generation failed. ' . self::summarizeCommandOutput($request_result['output']),
            ];
        }

        $request_json = json_decode((string) file_get_contents($request_file), true);
        if (!is_array($request_json)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'ZATCA SDK produced an invalid invoiceRequest JSON payload.',
            ];
        }

        $invoice_hash = isset($request_json['invoiceHash'])
            ? trim((string) $request_json['invoiceHash'])
            : '';

        $strict_qr_result = self::injectSignedQrIntoInvoice($signed_file, $config, $invoice_hash);
        if (!$strict_qr_result['success']) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => $strict_qr_result['message'],
            ];
        }

        $request_result = self::executeCommand($request_command, $sdk_working_dir, $sdk_env);
        if ($request_result['exit_code'] !== 0 || !is_file($request_file)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'ZATCA SDK invoiceRequest regeneration failed. ' . self::summarizeCommandOutput($request_result['output']),
            ];
        }

        $request_json = json_decode((string) file_get_contents($request_file), true);
        if (!is_array($request_json)) {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'ZATCA SDK produced an invalid invoiceRequest JSON payload after strict QR update.',
            ];
        }

        $invoice_hash = isset($request_json['invoiceHash'])
            ? trim((string) $request_json['invoiceHash'])
            : '';
        $uuid = isset($request_json['uuid'])
            ? trim((string) $request_json['uuid'])
            : trim((string) $fallbackUuid);
        $invoice_b64 = isset($request_json['invoice'])
            ? trim((string) $request_json['invoice'])
            : '';

        if ($invoice_hash === '' || $uuid === '' || $invoice_b64 === '') {
            self::cleanupDirectory($temp_dir);

            return [
                'success' => false,
                'message' => 'ZATCA SDK invoiceRequest is missing invoiceHash, uuid, or invoice.',
            ];
        }

        $signed_xml = (string) file_get_contents($signed_file);
        self::cleanupDirectory($temp_dir);

        return [
            'success' => true,
            'invoice_hash' => $invoice_hash,
            'uuid' => $uuid,
            'invoice_b64' => $invoice_b64,
            'invoice_xml' => $signed_xml,
        ];
    }

    protected static function injectSignedQrIntoInvoice($signedFile, $config, $invoiceHash)
    {
        // Handle both file paths and XML strings
        if (is_file($signedFile)) {
            $xml_raw = @file_get_contents($signedFile);
        } else {
            // Assume it's XML content directly
            $xml_raw = $signedFile;
        }
        
        if ($xml_raw === false || trim($xml_raw) === '') {
            return [
                'success' => false,
                'message' => 'Unable to read signed invoice XML for QR update.',
            ];
        }

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = true;

        if (!$doc->loadXML($xml_raw, LIBXML_NOBLANKS)) {
            return [
                'success' => false,
                'message' => 'Unable to load signed invoice XML for QR update.',
            ];
        }

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        $qr_node = $xpath->query("//cac:AdditionalDocumentReference[cbc:ID='QR']/cac:Attachment/cbc:EmbeddedDocumentBinaryObject")->item(0);
        $sig_node = $xpath->query('//ds:SignatureValue')->item(0);
        $cert_node = $xpath->query('//ds:X509Certificate')->item(0);
        $issue_date_node = $xpath->query('//cbc:IssueDate')->item(0);
        $issue_time_node = $xpath->query('//cbc:IssueTime')->item(0);
        $total_node = $xpath->query('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')->item(0);
        $tax_node = $xpath->query('//cac:TaxTotal/cbc:TaxAmount')->item(0);
        $seller_name_node = $xpath->query('//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')->item(0);
        $vat_node = $xpath->query('//cac:AccountingSupplierParty//cac:PartyTaxScheme/cbc:CompanyID')->item(0);

        if (!$qr_node || !$sig_node || !$cert_node || !$issue_date_node || !$issue_time_node || !$total_node || !$tax_node || !$seller_name_node || !$vat_node) {
            return [
                'success' => false,
                'message' => 'Required invoice nodes for QR update are missing.',
            ];
        }

        $signature_b64 = preg_replace('/\s+/', '', (string) $sig_node->textContent);
        $certificate_b64 = preg_replace('/\s+/', '', (string) $cert_node->textContent);
        $certificate_der = base64_decode($certificate_b64, true);

        if ($certificate_der === false) {
            return [
                'success' => false,
                'message' => 'Unable to decode certificate while building QR.',
            ];
        }

        if ($signature_b64 === '') {
            return [
                'success' => false,
                'message' => 'Unable to read XML signature value while building QR.',
            ];
        }

        $cert_pem = "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($certificate_b64, 64, "\n")
            . "-----END CERTIFICATE-----\n";

        $public_key = openssl_pkey_get_public($cert_pem);
        if (!$public_key) {
            return [
                'success' => false,
                'message' => 'Unable to extract certificate public key for QR.',
            ];
        }

        $public_details = openssl_pkey_get_details($public_key);
        if (is_resource($public_key)) {
            openssl_free_key($public_key);
        }

        if (!$public_details || !isset($public_details['key'])) {
            return [
                'success' => false,
                'message' => 'Unable to read certificate public key details for QR.',
            ];
        }

        $public_key_spki_der = base64_decode(
            preg_replace('/-----BEGIN PUBLIC KEY-----|-----END PUBLIC KEY-----|\s+/', '', (string) $public_details['key']),
            true
        );

        $certificate_signature = self::extractCertificateSignatureBytes($certificate_der);
        if ($public_key_spki_der === false || $public_key_spki_der === '' || $certificate_signature === '') {
            return [
                'success' => false,
                'message' => 'Unable to extract certificate public key/signature bytes for QR.',
            ];
        }

        $issue_date = trim((string) $issue_date_node->textContent);
        $issue_time = trim((string) $issue_time_node->textContent);
        $issue_timestamp = $issue_date . 'T' . preg_replace('/Z$/', '', $issue_time);

        $seller_name = trim((string) $seller_name_node->textContent);
        $vat_number = trim((string) $vat_node->textContent);
        $total_amount = trim((string) $total_node->textContent);
        $tax_amount = trim((string) $tax_node->textContent);

        $tlv = '';
        $tlv .= self::encodeTlvValue(1, $seller_name);
        $tlv .= self::encodeTlvValue(2, $vat_number);
        $tlv .= self::encodeTlvValue(3, $issue_timestamp);
        $tlv .= self::encodeTlvValue(4, $total_amount);
        $tlv .= self::encodeTlvValue(5, $tax_amount);
        $tlv .= self::encodeTlvValue(6, (string) $invoiceHash);
        $tlv .= self::encodeTlvValue(7, $signature_b64);
        $tlv .= self::encodeTlvValue(8, $public_key_spki_der, true);
        $tlv .= self::encodeTlvValue(9, $certificate_signature, true);

        $new_qr_b64 = base64_encode($tlv);
        $qr_old_b64 = trim((string) $qr_node->textContent);

        if ($qr_old_b64 === '') {
            return [
                'success' => false,
                'message' => 'Unable to locate existing QR value for strict update.',
            ];
        }

        $updated_xml = preg_replace('/' . preg_quote($qr_old_b64, '/') . '/', $new_qr_b64, $xml_raw, 1);
        if (!is_string($updated_xml) || $updated_xml === $xml_raw) {
            return [
                'success' => false,
                'message' => 'Failed to update QR value while preserving signed XML.',
            ];
        }

        // If input is a file path, write to file; if it's a string, return the updated XML
        if (is_file($signedFile) || strpos($signedFile, DIRECTORY_SEPARATOR) !== false || strpos($signedFile, '/') !== false) {
            // It's a file path
            if (@file_put_contents($signedFile, $updated_xml) === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to save signed invoice after strict QR update.',
                ];
            }
            return ['success' => true];
        } else {
            // It's a string, return the updated XML
            return [
                'success' => true,
                'signed_xml' => $updated_xml
            ];
        }
    }

    protected static function encodeTlvValue($tag, $value, $binary = false)
    {
        $raw_value = $binary ? (string) $value : (string) $value;
        $length = strlen($raw_value);

        if ($length < 128) {
            $len_encoded = chr($length);
        } elseif ($length < 256) {
            $len_encoded = chr(0x81) . chr($length);
        } else {
            $len_encoded = chr(0x82) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        }

        return chr((int) $tag) . $len_encoded . $raw_value;
    }

    protected static function extractCertificateSignatureBytes($certificateDer)
    {
        $offset = 0;

        $outer = self::readAsn1Node($certificateDer, $offset);
        if ($outer === null || $outer['tag'] !== 0x30) {
            return '';
        }

        $inner_offset = 0;
        $payload = $outer['value'];

        $tbs = self::readAsn1Node($payload, $inner_offset);
        $algo = self::readAsn1Node($payload, $inner_offset);
        $sig = self::readAsn1Node($payload, $inner_offset);

        if ($tbs === null || $algo === null || $sig === null || $sig['tag'] !== 0x03) {
            return '';
        }

        $sig_value = $sig['value'];
        if ($sig_value === '') {
            return '';
        }

        // First byte in BIT STRING is unused bits count.
        return substr($sig_value, 1);
    }

    protected static function extractCertificateSubjectPublicKeyBytes($certificateDer)
    {
        $offset = 0;
        $outer = self::readAsn1Node($certificateDer, $offset);
        if ($outer === null || $outer['tag'] !== 0x30) {
            return '';
        }

        $payload_offset = 0;
        $payload = $outer['value'];
        $tbs = self::readAsn1Node($payload, $payload_offset);
        if ($tbs === null || $tbs['tag'] !== 0x30) {
            return '';
        }

        $tbs_offset = 0;
        $tbs_data = $tbs['value'];

        // Optional version field [0] EXPLICIT.
        $first = self::readAsn1Node($tbs_data, $tbs_offset);
        if ($first === null) {
            return '';
        }

        if ($first['tag'] !== 0xA0) {
            // version missing, rewind and continue from serial number.
            $tbs_offset = 0;
        }

        $serial = self::readAsn1Node($tbs_data, $tbs_offset);
        $algo = self::readAsn1Node($tbs_data, $tbs_offset);
        $issuer = self::readAsn1Node($tbs_data, $tbs_offset);
        $validity = self::readAsn1Node($tbs_data, $tbs_offset);
        $subject = self::readAsn1Node($tbs_data, $tbs_offset);
        $spki = self::readAsn1Node($tbs_data, $tbs_offset);

        if (
            $serial === null || $algo === null || $issuer === null ||
            $validity === null || $subject === null || $spki === null || $spki['tag'] !== 0x30
        ) {
            return '';
        }

        $spki_offset = 0;
        $spki_algo = self::readAsn1Node($spki['value'], $spki_offset);
        $spki_key = self::readAsn1Node($spki['value'], $spki_offset);

        if ($spki_algo === null || $spki_key === null || $spki_key['tag'] !== 0x03 || $spki_key['value'] === '') {
            return '';
        }

        // BIT STRING first byte is unused bits count.
        return substr($spki_key['value'], 1);
    }

    protected static function ecdsaDerSignatureToRs($signatureDer)
    {
        $offset = 0;
        $seq = self::readAsn1Node($signatureDer, $offset);
        if ($seq === null || $seq['tag'] !== 0x30) {
            return '';
        }

        $inner_offset = 0;
        $inner = $seq['value'];
        $r = self::readAsn1Node($inner, $inner_offset);
        $s = self::readAsn1Node($inner, $inner_offset);

        if ($r === null || $s === null || $r['tag'] !== 0x02 || $s['tag'] !== 0x02) {
            return '';
        }

        $r_value = ltrim($r['value'], "\x00");
        $s_value = ltrim($s['value'], "\x00");

        if (strlen($r_value) > 32 || strlen($s_value) > 32) {
            return '';
        }

        return str_pad($r_value, 32, "\x00", STR_PAD_LEFT)
            . str_pad($s_value, 32, "\x00", STR_PAD_LEFT);
    }

    protected static function readAsn1Node($data, &$offset)
    {
        $data_len = strlen($data);
        if ($offset + 2 > $data_len) {
            return null;
        }

        $tag = ord($data[$offset++]);
        $len_octet = ord($data[$offset++]);

        if (($len_octet & 0x80) === 0) {
            $length = $len_octet;
        } else {
            $num_octets = $len_octet & 0x7F;
            if ($num_octets < 1 || $num_octets > 4 || $offset + $num_octets > $data_len) {
                return null;
            }

            $length = 0;
            for ($i = 0; $i < $num_octets; $i++) {
                $length = ($length << 8) | ord($data[$offset++]);
            }
        }

        if ($offset + $length > $data_len) {
            return null;
        }

        $value = substr($data, $offset, $length);
        $offset += $length;

        return [
            'tag' => $tag,
            'value' => $value,
        ];
    }

    protected static function ensureUblSignatureNamespaces($xml)
    {
        $xml = (string) $xml;

        if (strpos($xml, 'xmlns:ext=') === false) {
            $xml = preg_replace(
                '/<Invoice\s+([^>]*?)xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"/i',
                '<Invoice $1xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sig="urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2" xmlns:sac="urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2" xmlns:sbc="urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2"',
                $xml,
                1
            );
        }

        return $xml;
    }

    protected static function resolveJavaBinary()
    {
        $java_home = getenv('JAVA_HOME');
        if ($java_home) {
            $candidate = rtrim($java_home, '\\/') . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'java.exe';
            if (is_file($candidate)) {
                return $candidate;
            }

            $candidate = rtrim($java_home, '\\/') . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'java';
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        // Discover Java in common install locations when PATH/JAVA_HOME are not set
        // (common for web-server service accounts on Windows).
        $commonCandidates = [];

        foreach (['ProgramFiles', 'ProgramW6432', 'ProgramFiles(x86)'] as $envVar) {
            $base = getenv($envVar);
            if (!$base) {
                continue;
            }

            $base = rtrim((string) $base, '\\/');
            $commonCandidates[] = $base . DIRECTORY_SEPARATOR . 'Java' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'java.exe';

            foreach (glob($base . DIRECTORY_SEPARATOR . 'Java' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'java.exe') ?: [] as $path) {
                $commonCandidates[] = $path;
            }

            foreach (glob($base . DIRECTORY_SEPARATOR . 'Eclipse Adoptium' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'java.exe') ?: [] as $path) {
                $commonCandidates[] = $path;
            }

            foreach (glob($base . DIRECTORY_SEPARATOR . 'Zulu' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'java.exe') ?: [] as $path) {
                $commonCandidates[] = $path;
            }
        }

        foreach ($commonCandidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        // Fallback: explicit Windows installation roots even when ProgramFiles env vars
        // are not available to the web/PHP process token.
        $fixedRoots = [
            'C:\\Program Files\\Eclipse Adoptium',
            'C:\\Program Files\\Java',
            'C:\\Program Files\\Zulu',
            'C:\\Program Files\\Microsoft',
            'C:\\Program Files (x86)\\Java',
            'C:\\Program Files (x86)\\Zulu',
            'C:\\ProgramData\\Oracle\\Java\\javapath',
        ];

        foreach ($fixedRoots as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $direct = rtrim($root, '\\/') . DIRECTORY_SEPARATOR . 'java.exe';
            if (is_file($direct)) {
                return $direct;
            }

            $globbed = glob(rtrim($root, '\\/') . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'java.exe') ?: [];
            rsort($globbed);
            foreach ($globbed as $candidate) {
                if (is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        // Last-resort symlink used by some Oracle installs.
        $oracleShim = 'C:\\ProgramData\\Oracle\\Java\\javapath\\java.exe';
        if (is_file($oracleShim)) {
            return $oracleShim;
        }

        return 'java';
    }

    protected static function executeCommand($command, $workingDirectory = null, array $environment = null)
    {
        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = null;
        if (is_array($environment) && !empty($environment)) {
            $env = $_ENV;
            if (!is_array($env)) {
                $env = [];
            }

            foreach ($environment as $k => $v) {
                $env[(string) $k] = (string) $v;
            }
        }

        $process = @proc_open($command, $descriptors, $pipes, $workingDirectory ?: null, $env);
        if (!is_resource($process)) {
            return [
                'exit_code' => 1,
                'output' => 'Unable to start process: ' . $command,
            ];
        }

        $stdout = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exit_code = proc_close($process);

        return [
            'exit_code' => (int) $exit_code,
            'output' => trim($stdout . "\n" . $stderr),
        ];
    }

    protected static function summarizeCommandOutput($output)
    {
        $output = trim((string) $output);
        if ($output === '') {
            return 'See server log for details.';
        }

        $lines = preg_split('/\r\n|\r|\n/', $output);
        $candidates = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (stripos($line, 'Welcome to ZATCA E-Invoice Java SDK') !== false) {
                continue;
            }

            if (preg_match('/\bat\s+[a-z0-9_.$]+\([^)]+\)$/i', $line)) {
                continue;
            }

            if (stripos($line, 'Exception in thread') !== false) {
                $line = preg_replace('/^.*?Exception in thread\s+"main"\s+/i', '', $line);
            }

            $candidates[] = $line;
        }

        if (empty($candidates)) {
            return 'See server log for details.';
        }

        $summary = $candidates[0];

        foreach ($candidates as $candidate) {
            if (
                stripos($candidate, '[error]') !== false
                || stripos($candidate, 'failed to sign invoice') !== false
                || stripos($candidate, 'cannot invoke') !== false
                || stripos($candidate, 'invalid private key') !== false
            ) {
                $summary = $candidate;
                break;
            }
        }

        $summary = preg_replace('/\s+/', ' ', $summary);

        if (strlen($summary) > 220) {
            $summary = substr($summary, 0, 217) . '...';
        }

        return self::humanizeSdkError($summary);
    }

    protected static function humanizeSdkError($summary)
    {
        $summary = trim((string) $summary);
        $normalized = strtolower($summary);

        if (
            strpos($normalized, 'cannot invoke "java.io.file.isinvalid()" because "file" is null') !== false ||
            strpos($normalized, 'privatekey') !== false ||
            strpos($normalized, 'fileinputstream') !== false
        ) {
            return 'Private key file is missing, unreadable, or not in the format expected by the ZATCA SDK. SDK detail: ' . $summary;
        }

        if (strpos($normalized, 'unable to create sdk certificate directory') !== false) {
            return 'The ZATCA SDK certificate directory could not be created on the server.';
        }

        if (strpos($normalized, 'java') !== false && strpos($normalized, 'not found') !== false) {
            return 'Java runtime was not found on the server.';
        }

        return $summary;
    }

    /**
     * Build signed invoice using ZATCA API (fallback when Java not available)
     * 
     * This method provides an alternative to Java SDK for shared hosting
     * by using direct ZATCA REST API calls with PHP's OpenSSL functions.
     * 
     * @param string $invoiceXml UBL invoice XML
     * @param string $fallbackUuid UUID for fallback purposes
     * @param array $config System configuration
     * @return array ['success' => bool, 'invoice_hash' => string, 'invoice_xml' => string]
     */
    protected static function buildApiSignedPackage($invoiceXml, $fallbackUuid, $config, $context = [])
    {
        try {
            // Check if API client can be used
            $requirements = ZatcaApiClient::validateRequirements();
            if (!$requirements['available']) {
                return [
                    'success' => false,
                    'message' => 'ZATCA SDK jar not found and API fallback unavailable. Missing: ' . implode(', ', $requirements['missing']) . '. Install OpenSSL and cURL PHP extensions or Java runtime.',
                ];
            }

            // Validate required credentials
            $certificate = isset($config['zatca_production_certificate']) && trim((string) $config['zatca_production_certificate']) !== ''
                ? $config['zatca_production_certificate']
                : (isset($config['zatca_certificate']) ? $config['zatca_certificate'] : '');
            
            // Check if we're in compliance mode - use compliance certificate instead
            if (isset($context['compliance_mode']) && $context['compliance_mode'] === true && isset($context['compliance_certificate'])) {
                $certificate = $context['compliance_certificate'];
            }
            
            $private_key = self::resolvePemValue(isset($config['zatca_private_key']) ? $config['zatca_private_key'] : '', false);
            
            if (!$certificate || !$private_key) {
                return [
                    'success' => false,
                    'message' => 'Missing ZATCA certificate or private key required for API submission.',
                ];
            }

            // Start with the original invoice XML and apply the same prepayment cleanup
            // used by the SDK path to avoid BR-KSA-80 inconsistencies.
            $working_xml = self::stripPrepaymentNodes($invoiceXml);

            // Prefer UUID already present in XML, then fallback to generated UUID.
            if (preg_match('/<cbc:UUID>([^<]+)<\/cbc:UUID>/', $working_xml, $matches)) {
                $fallbackUuid = trim((string) $matches[1]);
            } elseif (empty($fallbackUuid)) {
                $fallbackUuid = Zatca::generateUuidV4();
            }

            // Build invoice hash using ZATCA XML transform rules.
            // This matches SDK behavior (exclude UBLExtensions, Signature, and QR ADR).
            $invoice_hash = self::calculateInvoiceHashWithC14N($working_xml);

            // Sign the invoice (this creates the signature for ZATCA)
            $private_key_passphrase = isset($config['zatca_private_key_passphrase'])
                ? (string) $config['zatca_private_key_passphrase']
                : '';

            $signing_result = ZatcaApiClient::signInvoiceLocally(
                $working_xml,
                $private_key,
                $certificate,
                $private_key_passphrase
            );
            if (!$signing_result['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to sign invoice: ' . $signing_result['error'],
                ];
            }

            // Base64 encode for API submission
            $invoice_b64 = base64_encode($working_xml);

            return [
                'success' => true,
                'invoice_hash' => $invoice_hash,
                'uuid' => $fallbackUuid,
                'invoice_b64' => $invoice_b64,
                'invoice_xml' => $working_xml,
                'method' => 'api_fallback',
                'note' => 'Signed using PHP API client (Java not available). Hash mode: transformed-c14n-base64.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'API fallback signing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate invoice hash using ZATCA XML transform + canonicalization.
     *
     * ZATCA hash must be computed after removing these nodes from the XML digest input:
     * - ext:UBLExtensions
     * - cac:Signature
     * - cac:AdditionalDocumentReference where cbc:ID = QR
     *
     * The remaining XML is canonicalized and SHA-256 hashed, then base64-encoded.
     * 
     * @param string $invoiceXml UBL invoice XML
     * @return string Base64-encoded SHA-256 digest
     */
    protected static function calculateInvoiceHashWithC14N($invoiceXml)
    {
        try {
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = true;
            
            if (!$doc->loadXML($invoiceXml)) {
                // Fallback to raw XML hash if parsing fails.
                return base64_encode(hash('sha256', (string) $invoiceXml, true));
            }

            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
            $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

            $nodesToRemove = $xpath->query(
                '//ext:UBLExtensions | //cac:Signature | //cac:AdditionalDocumentReference[cbc:ID="QR"]'
            );

            if ($nodesToRemove instanceof DOMNodeList && $nodesToRemove->length > 0) {
                for ($i = $nodesToRemove->length - 1; $i >= 0; $i--) {
                    $node = $nodesToRemove->item($i);
                    if ($node && $node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }

            // Canonicalize transformed XML before hashing.
            $canonicalXml = $doc->C14N(false, false);
            
            if ($canonicalXml === false) {
                return base64_encode(hash('sha256', (string) $invoiceXml, true));
            }
            
            return base64_encode(hash('sha256', (string) $canonicalXml, true));
        } catch (Exception $e) {
            return base64_encode(hash('sha256', (string) $invoiceXml, true));
        }
    }

    protected static function cleanupDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = @scandir($dir);
        if (!is_array($files)) {
            @rmdir($dir);
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                self::cleanupDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    protected static function copyDirectory($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }

        if (!@mkdir($destination, 0777, true) && !is_dir($destination)) {
            return false;
        }

        $entries = @scandir($source);
        if (!is_array($entries)) {
            return false;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $src = $source . DIRECTORY_SEPARATOR . $entry;
            $dst = $destination . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($src)) {
                if (!self::copyDirectory($src, $dst)) {
                    return false;
                }
                continue;
            }

            if (!@copy($src, $dst)) {
                return false;
            }
        }

        return true;
    }

    protected static function extractVatFromBinaryToken($binaryToken)
    {
        $binaryToken = trim((string) $binaryToken);
        if ($binaryToken === '') {
            return '';
        }

        foreach (self::expandCertificateSearchPool($binaryToken) as $blob) {
            if (preg_match('/\b3\d{13}3\b/', (string) $blob, $m)) {
                return $m[0];
            }
        }

        $certificate_pem = self::binaryTokenToPemCertificate($binaryToken);
        if ($certificate_pem === '') {
            return '';
        }

        $certificate = openssl_x509_read($certificate_pem);
        if (!$certificate) {
            return '';
        }

        $parsed = openssl_x509_parse($certificate);

        if (is_resource($certificate)) {
            openssl_x509_free($certificate);
        }

        if (!is_array($parsed) || !isset($parsed['extensions']['subjectAltName'])) {
            return '';
        }

        $san = (string) $parsed['extensions']['subjectAltName'];
        if (preg_match('/UID=(3\d{13}3)/', $san, $m)) {
            return $m[1];
        }

        foreach (['name', 'subject'] as $subjectKey) {
            if (!isset($parsed[$subjectKey])) {
                continue;
            }

            $subjectBlob = is_array($parsed[$subjectKey]) ? json_encode($parsed[$subjectKey]) : (string) $parsed[$subjectKey];
            if (preg_match('/\b3\d{13}3\b/', (string) $subjectBlob, $m)) {
                return $m[0];
            }
        }

        return '';
    }

    protected static function expandCertificateSearchPool($raw)
    {
        $pool = [$raw];

        $decodedRaw = base64_decode(preg_replace('/\s+/', '', (string) $raw), true);
        if ($decodedRaw !== false) {
            $pool[] = $decodedRaw;
        }

        if (substr_count((string) $raw, '.') >= 2) {
            $parts = explode('.', (string) $raw);
            foreach ($parts as $part) {
                $decodedPart = base64_decode(strtr($part, '-_', '+/') . str_repeat('=', (4 - strlen($part) % 4) % 4), true);
                if ($decodedPart !== false) {
                    $pool[] = $decodedPart;
                }
            }
        }

        return $pool;
    }

    protected static function persistComplianceCredentials(array $settings)
    {
        // Try WordPress option functions first (for compatibility)
        $wordpress_success = true;
        foreach ($settings as $setting_key => $setting_value) {
            $result = update_option($setting_key, $setting_value);
            if (!$result && !add_option($setting_key, $setting_value)) {
                $wordpress_success = false;
                self::logZatcaDiagnostic("persistComplianceCredentials: update_option/add_option failed for $setting_key");
            }
        }

        // Fallback to AppConfig PDO if WordPress functions failed or are unavailable
        if (!$wordpress_success) {
            try {
                if (class_exists('AppConfig')) {
                    $pdo_result = AppConfig::saveZatcaOptionsUsingPdo($settings);
                    if ($pdo_result) {
                        self::logZatcaDiagnostic("persistComplianceCredentials: Fallback to AppConfig PDO succeeded");
                    } else {
                        self::logZatcaDiagnostic("persistComplianceCredentials: AppConfig PDO fallback returned false");
                    }
                }
            } catch (Exception $e) {
                self::logZatcaDiagnostic("persistComplianceCredentials: AppConfig PDO fallback threw exception: " . $e->getMessage());
            }
        }
    }

    protected static function loadOptionsFromAppConfig()
    {
        $result = [];

        if (!class_exists('ORM')) {
            self::logZatcaDiagnostic('loadOptionsFromAppConfig: ORM class not available');
            return $result;
        }

        // List of keys we want to retrieve
        $keys = [
            'zatca_binary_security_token',
            'zatca_secret',
            'zatca_compliance_request_id',
            'zatca_compliance_csid',
            'zatca_compliance_secret',
            'zatca_production_binary_security_token',
            'zatca_production_secret',
            'zatca_production_csid',
            'zatca_certificate',
            'zatca_private_key',
        ];

        try {
            // Initialize all keys with empty values
            foreach ($keys as $k) {
                $result[$k] = '';
            }

            self::logZatcaDiagnostic('loadOptionsFromAppConfig: Attempting ORM where_in query for ' . count($keys) . ' keys');

            // Query using where_in for explicit keys (like settings.php does)
            $rows = ORM::for_table('sys_appconfig')
                ->select_many('setting', 'value')
                ->where_in('setting', $keys)
                ->find_many();

            $found_count = count($rows);
            self::logZatcaDiagnostic('loadOptionsFromAppConfig: ORM query returned ' . $found_count . ' rows');

            foreach ($rows as $row) {
                $setting = (string) $row->setting;
                // Handle NULL values explicitly - convert to empty string
                $rawValue = $row->value;
                $value = ($rawValue === null) ? '' : trim((string) $rawValue);
                if (isset($result[$setting])) {
                    $result[$setting] = $value;
                    $len = strlen($value);
                    self::logZatcaDiagnostic('loadOptionsFromAppConfig: Loaded ' . $setting . ' (' . $len . ' bytes)');
                }
            }

            // If we got results, return them
            if ($found_count > 0) {
                return $result;
            }

            self::logZatcaDiagnostic('loadOptionsFromAppConfig: ORM returned 0 rows, trying PDO fallback');
        } catch (Throwable $e) {
            self::logZatcaDiagnostic('loadOptionsFromAppConfig: EXCEPTION in ORM - ' . get_class($e) . ': ' . $e->getMessage());
        }

        // Fallback to PDO if ORM failed or returned nothing
        try {
            self::logZatcaDiagnostic('loadOptionsFromAppConfig: Using PDO fallback');

            // Re-initialize result
            foreach ($keys as $k) {
                $result[$k] = '';
            }

            // Use PDO directly via ORM
            $pdo = ORM::get_db();
            if (!$pdo) {
                self::logZatcaDiagnostic('loadOptionsFromAppConfig: Could not get PDO connection from ORM');
                return $result;
            }

            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $sql = "SELECT `setting`, `value` FROM sys_appconfig WHERE `setting` IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($keys);

            $pdo_found = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $setting = $row['setting'];
                // Handle NULL values explicitly - convert to empty string
                $rawValue = $row['value'];
                $value = ($rawValue === null) ? '' : trim($rawValue);
                if (isset($result[$setting])) {
                    $result[$setting] = $value;
                    $pdo_found++;
                    $len = strlen($value);
                    self::logZatcaDiagnostic('loadOptionsFromAppConfig (PDO): Loaded ' . $setting . ' (' . $len . ' bytes)');
                }
            }

            self::logZatcaDiagnostic('loadOptionsFromAppConfig (PDO): Retrieved ' . $pdo_found . ' keys');
            return $result;
        } catch (Throwable $e) {
            self::logZatcaDiagnostic('loadOptionsFromAppConfig: EXCEPTION in PDO fallback - ' . get_class($e) . ': ' . $e->getMessage());
        }

        return $result;
    }

    protected static function signHash($hash_base64, $private_key_pem, $passphrase = '')
    {
        $private_key = openssl_pkey_get_private($private_key_pem, $passphrase);

        if (!$private_key) {
            return [
                'success' => false,
                'message' => 'Unable to parse ZATCA private key.',
            ];
        }

        $signature = '';
        $hash_raw = base64_decode($hash_base64);

        $ok = openssl_sign($hash_raw, $signature, $private_key, OPENSSL_ALGO_SHA256);

        if (is_resource($private_key)) {
            openssl_pkey_free($private_key);
        }

        if (!$ok) {
            return [
                'success' => false,
                'message' => 'Unable to sign invoice hash with the configured private key.',
            ];
        }

        return [
            'success' => true,
            'signature' => base64_encode($signature),
        ];
    }

    protected static function extractPublicKey($certificate_pem)
    {
        $public_key = openssl_pkey_get_public($certificate_pem);
        if (!$public_key) {
            return '';
        }

        $details = openssl_pkey_get_details($public_key);

        if (is_resource($public_key)) {
            openssl_free_key($public_key);
        }

        if (!$details || !isset($details['key'])) {
            return '';
        }

        return base64_encode($details['key']);
    }
}
