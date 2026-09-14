<?php

declare(strict_types=1);

use GuzzleHttp\Client;

/**
 * Standalone utility to fetch Compliance and Production CSIDs from ZATCA.
 *
 * This file is intentionally independent from existing application classes.
 */
class ZatcaCsidAutoFetcher
{
    private const DEFAULT_API_BASE = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation/';

    /** @var Client */
    private $httpClient;

    /** @var string */
    private $apiBase;

    public function __construct(string $apiBase = self::DEFAULT_API_BASE, int $timeoutSeconds = 45)
    {
        $this->apiBase = self::normalizeApiBase($apiBase);
        $this->httpClient = new Client([
            'base_uri' => $this->apiBase,
            'timeout' => $timeoutSeconds,
            'http_errors' => false,
        ]);
    }

    /**
     * Fetch Compliance CSID, run compliance invoice check, then fetch Production CSID.
     *
     * ZATCA requires all 3 steps in order:
     *   1. POST /compliance          (OTP + CSR)  → compliance CSID
     *   2. POST /compliance/invoices (Basic auth) → compliance invoice check
     *   3. POST /production/csids   (Basic auth) → production CSID
     *
     * @param string $vatNumber  Seller VAT number (15 digits). Used in compliance invoice check.
     * @return array<string,mixed>
     */
    public function fetchAll(string $otp, string $csrInput, string $vatNumber = ''): array
    {
        // Step 1 — Compliance CSID
        $compliance = $this->requestComplianceCsid($otp, $csrInput);

        if (empty($compliance['success'])) {
            return [
                'success'            => false,
                'stage'              => 'compliance',
                'api_base'           => $this->apiBase,
                'compliance'         => $compliance,
                'compliance_invoices' => null,
                'production'         => null,
            ];
        }

        $token  = (string) ($compliance['binary_security_token'] ?? '');
        $secret = (string) ($compliance['secret'] ?? '');

        // Step 2 — Compliance Invoice Check (REQUIRED before production CSID)
        $complianceInvoices = $this->runComplianceInvoiceCheck($token, $secret, $vatNumber);

        if (empty($complianceInvoices['success'])) {
            return [
                'success'            => false,
                'stage'              => 'compliance_invoices',
                'api_base'           => $this->apiBase,
                'compliance'         => $compliance,
                'compliance_invoices' => $complianceInvoices,
                'production'         => null,
            ];
        }

        // Step 3 — Production CSID
        $production = $this->requestProductionCsid(
            $token,
            $secret,
            (string) ($compliance['request_id'] ?? '')
        );

        if (empty($production['success'])) {
            return [
                'success'            => false,
                'stage'              => 'production',
                'api_base'           => $this->apiBase,
                'compliance'         => $compliance,
                'compliance_invoices' => $complianceInvoices,
                'production'         => $production,
            ];
        }

        return [
            'success'            => true,
            'stage'              => 'completed',
            'api_base'           => $this->apiBase,
            'compliance'         => $compliance,
            'compliance_invoices' => $complianceInvoices,
            'production'         => $production,
            'keys' => [
                'zatca_binary_security_token'            => $token,
                'zatca_secret'                           => $secret,
                'zatca_compliance_request_id'            => (string) ($compliance['request_id'] ?? ''),
                'zatca_production_binary_security_token' => (string) ($production['binary_security_token'] ?? ''),
                'zatca_production_secret'                => (string) ($production['secret'] ?? ''),
                'zatca_production_request_id'            => (string) ($production['request_id'] ?? ''),
            ],
        ];
    }

    /**
     * Step 2: Run compliance invoice check using the compliance CSID credentials.
     * ZATCA requires this before issuing a Production CSID.
     *
     * @return array<string,mixed>
     */
    public function runComplianceInvoiceCheck(
        string $complianceToken,
        string $complianceSecret,
        string $vatNumber = ''
    ): array {
        $vatNumber = trim($vatNumber);
        if ($vatNumber === '') {
            $vatNumber = '312345678900003'; // ZATCA test VAT fallback
        }

        $uuid = self::generateUuid();
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
            . '<cac:PartyIdentification><cbc:ID schemeID="VAT">' . htmlspecialchars($vatNumber, ENT_XML1) . '</cbc:ID></cac:PartyIdentification>'
            . '<cac:PostalAddress>'
            . '<cbc:StreetName>Olaya</cbc:StreetName>'
            . '<cbc:CityName>Riyadh</cbc:CityName>'
            . '<cbc:PostalZone>12345</cbc:PostalZone>'
            . '<cac:Country><cbc:IdentificationCode>SA</cbc:IdentificationCode></cac:Country>'
            . '</cac:PostalAddress>'
            . '<cac:PartyTaxScheme><cbc:CompanyID>' . htmlspecialchars($vatNumber, ENT_XML1) . '</cbc:CompanyID>'
            . '<cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme>'
            . '<cac:PartyLegalEntity><cbc:RegistrationName>Test Company</cbc:RegistrationName></cac:PartyLegalEntity>'
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

        $invoiceHash = base64_encode(hash('sha256', $xml, true));

        try {
            $response = $this->httpClient->post('compliance/invoices', [
                'headers' => [
                    'Accept'          => 'application/json',
                    'Accept-Language' => 'en',
                    'Accept-Version'  => 'V2',
                    'Authorization'   => 'Basic ' . base64_encode($complianceToken . ':' . $complianceSecret),
                    'Content-Type'    => 'application/json',
                ],
                'json' => [
                    'invoiceHash' => $invoiceHash,
                    'uuid'        => $uuid,
                    'invoice'     => base64_encode($xml),
                ],
            ]);

            $statusCode   = $response->getStatusCode();
            $responseBody = (string) $response->getBody();

            if ($statusCode === 401) {
                return [
                    'success'       => false,
                    'status_code'   => $statusCode,
                    'message'       => 'Compliance invoice check failed (401). Compliance CSID credentials were rejected.',
                    'response_body' => $responseBody,
                ];
            }

            if ($statusCode >= 200 && $statusCode < 300) {
                return [
                    'success'       => true,
                    'status_code'   => $statusCode,
                    'message'       => 'Compliance invoice check passed.',
                    'response_body' => $responseBody,
                ];
            }

            return [
                'success'       => false,
                'status_code'   => $statusCode,
                'message'       => 'Compliance invoice check failed with status code ' . $statusCode . '.',
                'response_body' => $responseBody,
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

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @return array<string,mixed>
     */
    public function requestComplianceCsid(string $otp, string $csrInput): array
    {
        $otp = trim($otp);
        $csr = self::normalizeCsr($csrInput);

        if ($otp === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'OTP is required.',
                'response_body' => '',
            ];
        }

        if ($csr === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'CSR is missing or invalid.',
                'response_body' => '',
            ];
        }

        try {
            $response = $this->httpClient->post('compliance', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en',
                    'Accept-Version' => 'V2',
                    'Content-Type' => 'application/json',
                    'OTP' => $otp,
                ],
                'json' => [
                    'csr' => $csr,
                ],
            ]);

            return $this->mapComplianceResponse($response->getStatusCode(), (string) $response->getBody());
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Compliance request failed: ' . $e->getMessage(),
                'response_body' => '',
            ];
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function requestProductionCsid(
        string $complianceToken,
        string $complianceSecret,
        string $complianceRequestId
    ): array {
        $complianceToken = preg_replace('/\s+/', '', trim($complianceToken));
        $complianceSecret = trim($complianceSecret);
        $complianceRequestId = trim($complianceRequestId);

        if ($complianceToken === '' || $complianceSecret === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Compliance token and secret are required for production CSID request.',
                'response_body' => '',
            ];
        }

        if ($complianceRequestId === '') {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Compliance request ID is required for production CSID request.',
                'response_body' => '',
            ];
        }

        try {
            $response = $this->httpClient->post('production/csids', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en',
                    'Accept-Version' => 'V2',
                    'Authorization' => 'Basic ' . base64_encode($complianceToken . ':' . $complianceSecret),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'compliance_request_id' => $complianceRequestId,
                ],
            ]);

            return $this->mapProductionResponse($response->getStatusCode(), (string) $response->getBody());
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Production request failed: ' . $e->getMessage(),
                'response_body' => '',
            ];
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function mapComplianceResponse(int $statusCode, string $responseBody): array
    {
        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $token = isset($decoded['binarySecurityToken']) ? trim((string) $decoded['binarySecurityToken']) : '';
        $secret = isset($decoded['secret']) ? trim((string) $decoded['secret']) : '';
        $requestId = isset($decoded['requestID']) ? trim((string) $decoded['requestID']) : '';

        if ($statusCode >= 200 && $statusCode < 300 && $token !== '' && $secret !== '') {
            return [
                'success' => true,
                'status_code' => $statusCode,
                'message' => 'Compliance CSID fetched successfully.',
                'response_body' => $responseBody,
                'binary_security_token' => $token,
                'secret' => $secret,
                'request_id' => $requestId,
            ];
        }

        $message = 'Compliance CSID request failed with status code ' . $statusCode . '.';
        // Try to parse ZATCA JSON error body (simulation gives structured errors)
        $jsonError = json_decode($responseBody, true);
        if (is_array($jsonError) && isset($jsonError['errorCategory'])) {
            $cat    = (string) ($jsonError['errorCategory'] ?? '');
            $errMsg = (string) ($jsonError['errorMessage'] ?? '');
            if ($cat === 'Invalid-CSR') {
                $message = 'ZATCA rejected the CSR: ' . $errMsg
                    . ' — The CSR must be generated by the ZATCA SDK using the same EGS serial number'
                    . ' and VAT number that was registered on the portal when the OTP was issued.'
                    . ' You cannot use a pre-generated or mismatched CSR.';
            } elseif ($errMsg !== '') {
                $message = 'ZATCA error [' . $cat . ']: ' . $errMsg;
            } else {
                $message .= ' ZATCA error category: ' . $cat;
            }
        } elseif ($statusCode === 400) {
            $bodyLower = strtolower(trim($responseBody));
            if ($bodyLower === 'invalid request' || $bodyLower === '"invalid request"' || $bodyLower === '') {
                $message .= ' The Developer Portal returned a generic error. Switch API environment to'
                    . ' "Simulation" for a detailed error message.'
                    . ' Common causes: OTP expired/used, or CSR does not match the registered EGS device.';
            } else {
                $message .= ' Check the ZATCA response for details.';
            }
        } elseif ($statusCode === 401) {
            $message .= ' OTP was rejected (401). The OTP must match the CSR device registration.';
        } elseif ($statusCode === 404) {
            $message .= ' API endpoint not found. Check the API Environment URL.';
        }

        return [
            'success' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'response_body' => $responseBody,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function mapProductionResponse(int $statusCode, string $responseBody): array
    {
        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $token = isset($decoded['binarySecurityToken']) ? trim((string) $decoded['binarySecurityToken']) : '';
        $secret = isset($decoded['secret']) ? trim((string) $decoded['secret']) : '';
        $requestId = isset($decoded['requestID']) ? trim((string) $decoded['requestID']) : '';

        if ($statusCode >= 200 && $statusCode < 300 && $token !== '' && $secret !== '') {
            return [
                'success' => true,
                'status_code' => $statusCode,
                'message' => 'Production CSID fetched successfully.',
                'response_body' => $responseBody,
                'binary_security_token' => $token,
                'secret' => $secret,
                'request_id' => $requestId,
            ];
        }

        return [
            'success' => false,
            'status_code' => $statusCode,
            'message' => 'Production CSID request failed with status code ' . $statusCode,
            'response_body' => $responseBody,
        ];
    }

    private static function normalizeApiBase(string $apiBase): string
    {
        $apiBase = trim($apiBase);
        if ($apiBase === '') {
            $apiBase = self::DEFAULT_API_BASE;
        }

        return rtrim($apiBase, '/') . '/';
    }

    private static function normalizeCsr(string $csrInput): string
    {
        $raw = self::resolveTextValue($csrInput);
        $raw = trim($raw);

        if ($raw === '') {
            return '';
        }

        $decodedCandidate = base64_decode($raw, true);
        if ($decodedCandidate !== false && stripos($decodedCandidate, 'BEGIN CERTIFICATE REQUEST') !== false) {
            $raw = $decodedCandidate;
        }

        $raw = preg_replace('/-----BEGIN CERTIFICATE REQUEST-----/i', '', $raw);
        $raw = preg_replace('/-----END CERTIFICATE REQUEST-----/i', '', $raw);
        $raw = preg_replace('/\s+/', '', $raw);

        return trim((string) $raw);
    }

    private static function resolveTextValue(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (is_file($raw) && is_readable($raw)) {
            $content = file_get_contents($raw);
            return $content === false ? '' : (string) $content;
        }

        return str_replace(["\\r\\n", "\\n"], ["\n", "\n"], $raw);
    }
}
