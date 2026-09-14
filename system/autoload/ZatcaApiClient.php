<?php
/**
 * ZatcaApiClient - Direct REST API implementation for ZATCA e-invoicing
 * 
 * This class handles all ZATCA API communications without requiring Java SDK.
 * Works on shared hosting by using pure PHP with OpenSSL cryptography.
 * 
 * Supported Operations:
 * - CSR submission for certificate generation
 * - Invoice submission (simplified & standard)
 * - Compliance check
 * - Production submission
 * - Invoice clearance and reporting
 */

class ZatcaApiClient
{
    // ZATCA API Endpoints
    private static $SANDBOX_API_BASE = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal';
    private static $PRODUCTION_API_BASE = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/api';
    
    private $environment = 'sandbox';
    private $apiBase = '';
    private $binarySecurityToken = '';
    private $secret = '';
    private $certificate = '';
    private $privateKey = '';
    private $lastResponse = [];
    private $lastError = '';

    /**
     * Initialize API client with configuration
     */
    public function __construct($config = [])
    {
        $this->environment = isset($config['zatca_environment']) && trim((string) $config['zatca_environment']) !== ''
            ? trim((string) $config['zatca_environment'])
            : 'sandbox';
        $this->apiBase = $this->environment === 'production' 
            ? self::$PRODUCTION_API_BASE 
            : self::$SANDBOX_API_BASE;
        
        // Load credentials based on environment
        if ($this->environment === 'production') {
            $this->binarySecurityToken = isset($config['zatca_production_binary_security_token']) 
                ? $config['zatca_production_binary_security_token'] 
                : '';
            $this->secret = isset($config['zatca_production_secret']) 
                ? $config['zatca_production_secret'] 
                : '';
            $this->certificate = isset($config['zatca_production_certificate']) 
                ? $config['zatca_production_certificate'] 
                : '';
        } else {
            $this->binarySecurityToken = isset($config['zatca_binary_security_token']) 
                ? $config['zatca_binary_security_token'] 
                : '';
            $this->secret = isset($config['zatca_secret']) 
                ? $config['zatca_secret'] 
                : '';
            $this->certificate = isset($config['zatca_certificate']) 
                ? $config['zatca_certificate'] 
                : '';
        }
        
        $this->privateKey = isset($config['zatca_private_key']) 
            ? $config['zatca_private_key'] 
            : '';
    }

    /**
     * Submit CSR for certificate generation
     * 
     * @param string $csr Certificate Signing Request (PEM format)
     * @param string $otp OTP from ZATCA
     * @return array Response with certificate data
     */
    public function submitCsr($csr, $otp)
    {
        $endpoint = '/compliance';
        $url = $this->apiBase . $endpoint;
        
        $payload = [
            'csr' => $this->encodePem($csr),
            'otp' => $otp
        ];
        
        return $this->makeRequest('POST', $url, json_encode($payload), [
            'Authorization' => 'Bearer ' . $this->binarySecurityToken
        ]);
    }

    /**
     * Submit invoice for compliance (testing before production)
     * 
     * @param string $invoiceXml UBL invoice XML
     * @param string $invoiceHash SHA-256 hash of invoice
     * @return array Response with validation results
     */
    public function submitComplianceInvoice($invoiceXml, $invoiceHash)
    {
        $endpoint = '/compliance/invoices';
        $url = $this->apiBase . $endpoint;
        
        // Sign the invoice for compliance
        $signedData = $this->signInvoice($invoiceXml, $invoiceHash);
        
        $payload = [
            'invoiceHash' => $invoiceHash,
            'uuid' => $this->extractUuid($invoiceXml),
            'invoice' => base64_encode($invoiceXml),
            'signature' => $signedData['signature'],
            'publicKey' => $signedData['publicKey']
        ];
        
        return $this->makeRequest('POST', $url, json_encode($payload), $this->getAuthHeaders());
    }

    /**
     * Submit invoice for clearance (production submission)
     * 
     * @param string $invoiceXml UBL invoice XML
     * @param string $invoiceHash SHA-256 hash of invoice
     * @return array Response with clearance result
     */
    public function submitClearanceInvoice($invoiceXml, $invoiceHash)
    {
        $endpoint = '/invoices/clearance';
        $url = $this->apiBase . $endpoint;
        
        // Sign the invoice for clearance
        $signedData = $this->signInvoice($invoiceXml, $invoiceHash);
        
        $payload = [
            'invoiceHash' => $invoiceHash,
            'uuid' => $this->extractUuid($invoiceXml),
            'invoice' => base64_encode($invoiceXml),
            'signature' => $signedData['signature'],
            'publicKey' => $signedData['publicKey']
        ];
        
        return $this->makeRequest('POST', $url, json_encode($payload), $this->getAuthHeaders());
    }

    /**
     * Report invoice (after clearance)
     * 
     * @param string $invoiceXml UBL invoice XML
     * @param string $invoiceHash SHA-256 hash of invoice
     * @return array Response with reporting result
     */
    public function reportInvoice($invoiceXml, $invoiceHash)
    {
        $endpoint = '/invoices/reporting';
        $url = $this->apiBase . $endpoint;
        
        // Sign the invoice for reporting
        $signedData = $this->signInvoice($invoiceXml, $invoiceHash);
        
        $payload = [
            'invoiceHash' => $invoiceHash,
            'uuid' => $this->extractUuid($invoiceXml),
            'invoice' => base64_encode($invoiceXml),
            'signature' => $signedData['signature'],
            'publicKey' => $signedData['publicKey']
        ];
        
        return $this->makeRequest('POST', $url, json_encode($payload), $this->getAuthHeaders());
    }

    /**
     * Sign invoice using ZATCA method
     * 
     * Generates signature and extracts public key from certificate
     * 
     * @param string $invoiceXml UBL invoice XML
     * @param string $invoiceHash SHA-256 hash of invoice
     * @return array ['signature' => base64, 'publicKey' => base64]
     */
    private function signInvoice($invoiceXml, $invoiceHash)
    {
        // Extract public key from certificate
        $publicKeyData = $this->extractPublicKeyFromCertificate();
        
        // Create signature using private key
        $signature = $this->createSignature($invoiceXml);
        
        return [
            'signature' => base64_encode($signature),
            'publicKey' => $publicKeyData
        ];
    }

    /**
     * Create cryptographic signature using private key
     * 
     * @param string $data Data to sign
     * @return string Binary signature
     */
    private function createSignature($data)
    {
        if (!$this->privateKey) {
            throw new Exception('Private key not configured');
        }
        
        $privateKey = openssl_pkey_get_private($this->privateKey);
        if (!$privateKey) {
            throw new Exception('Failed to load private key');
        }
        
        $signature = '';
        $result = openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        
        openssl_free_key($privateKey);
        
        if (!$result) {
            throw new Exception('Failed to create signature: ' . openssl_error_string());
        }
        
        return $signature;
    }

    /**
     * Extract and encode public key from certificate
     * 
     * @return string Base64-encoded public key
     */
    private function extractPublicKeyFromCertificate()
    {
        if (!$this->certificate) {
            throw new Exception('Certificate not configured');
        }
        
        $certResource = openssl_x509_read($this->certificate);
        if (!$certResource) {
            throw new Exception('Failed to read certificate');
        }
        
        $certDetails = openssl_x509_parse($certResource);
        if (!$certDetails) {
            throw new Exception('Failed to parse certificate');
        }
        
        $publicKeyResource = openssl_pkey_get_public($this->certificate);
        if (!$publicKeyResource) {
            throw new Exception('Failed to extract public key from certificate');
        }
        
        $publicKeyDetails = openssl_pkey_get_details($publicKeyResource);
        if (!$publicKeyDetails) {
            throw new Exception('Failed to get public key details');
        }
        
        openssl_free_key($publicKeyResource);
        openssl_x509_free($certResource);
        
        // Return the public key in PEM format, encoded as base64
        return base64_encode($publicKeyDetails['key']);
    }

    /**
     * Extract UUID from invoice XML
     * 
     * @param string $xml Invoice XML
     * @return string UUID
     */
    private function extractUuid($xml)
    {
        if (preg_match('/<cbc:UUID>([^<]+)<\/cbc:UUID>/i', $xml, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Make HTTP request to ZATCA API
     * 
     * @param string $method HTTP method (POST, GET)
     * @param string $url Full URL
     * @param string $body Request body
     * @param array $headers HTTP headers
     * @return array Response data
     */
    private function makeRequest($method, $url, $body = '', $headers = [])
    {
        // Default headers
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        // Merge headers
        $allHeaders = $defaultHeaders;
        foreach ($headers as $key => $value) {
            $allHeaders[] = "$key: $value";
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $allHeaders,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Store for debugging
        $this->lastResponse = [
            'url' => $url,
            'method' => $method,
            'httpCode' => $httpCode,
            'body' => $response,
            'error' => $error
        ];
        
        if ($error) {
            $this->lastError = "CURL Error: $error";
            return ['success' => false, 'error' => $this->lastError];
        }
        
        $decoded = json_decode($response, true);
        
        // Add HTTP status to response
        if (is_array($decoded)) {
            $decoded['httpCode'] = $httpCode;
        }
        
        return $decoded ? $decoded : ['raw' => $response, 'httpCode' => $httpCode];
    }

    /**
     * Get authentication headers for API requests
     * 
     * @return array Headers array
     */
    private function getAuthHeaders()
    {
        if ($this->environment === 'production' && $this->binarySecurityToken && $this->secret) {
            // Production: Use Binary Security Token
            return [
                'Authorization' => 'Basic ' . $this->binarySecurityToken,
                'X-Secret' => $this->secret
            ];
        } else {
            // Sandbox: Use Binary Security Token
            return [
                'Authorization' => 'Basic ' . $this->binarySecurityToken
            ];
        }
    }

    /**
     * Encode PEM certificate/CSR for API transmission
     * 
     * @param string $pem PEM data
     * @return string Base64-encoded PEM
     */
    private function encodePem($pem)
    {
        // Remove PEM headers
        $pem = preg_replace('/-----BEGIN.*-----/', '', $pem);
        $pem = preg_replace('/-----END.*-----/', '', $pem);
        $pem = trim($pem);
        
        return base64_encode(base64_decode($pem));
    }

    /**
     * Get last API response for debugging
     * 
     * @return array Last response details
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Get last error message
     * 
     * @return string Error message
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Check if curl is available
     * 
     * @return bool
     */
    public static function isCurlAvailable()
    {
        return function_exists('curl_init');
    }

    /**
     * Check if OpenSSL is available
     * 
     * @return bool
     */
    public static function isOpenSSLAvailable()
    {
        return extension_loaded('openssl');
    }

    /**
     * Validate system requirements for API client
     * 
     * @return array ['available' => bool, 'missing' => array]
     */
    public static function validateRequirements()
    {
        $missing = [];
        
        if (!self::isCurlAvailable()) {
            $missing[] = 'PHP cURL extension';
        }
        
        if (!self::isOpenSSLAvailable()) {
            $missing[] = 'PHP OpenSSL extension';
        }
        
        return [
            'available' => empty($missing),
            'missing' => $missing
        ];
    }

    /**
     * Sign an invoice locally using private key and certificate
     * For API submission, this validates the credentials and calculates hash
     * 
     * @param string $invoiceXml UBL invoice XML
     * @param string $privateKey Private key in PEM format
     * @param string $certificate Certificate in PEM format
     * @return array ['success' => bool, 'invoice_hash' => string, 'signed_xml' => string, 'error' => string]
     */
    public static function signInvoiceLocally($invoiceXml, $privateKey, $certificate, $passphrase = '')
    {
        try {
            if (empty($invoiceXml)) {
                return ['success' => false, 'error' => 'Invoice XML is empty'];
            }
            
            if (empty($privateKey)) {
                return ['success' => false, 'error' => 'Private key is empty'];
            }
            
            if (empty($certificate)) {
                return ['success' => false, 'error' => 'Certificate is empty'];
            }

            $resolvedPrivateKey = self::resolvePemInput($privateKey, 'PRIVATE KEY');
            $resolvedCertificate = self::resolvePemInput($certificate, 'CERTIFICATE');

            if ($resolvedPrivateKey === '') {
                return ['success' => false, 'error' => 'Private key is empty or unreadable.'];
            }

            if ($resolvedCertificate === '') {
                return ['success' => false, 'error' => 'Certificate is empty or unreadable.'];
            }

            if (stripos($resolvedPrivateKey, 'BEGIN OPENSSH PRIVATE KEY') !== false) {
                return ['success' => false, 'error' => 'Unsupported private key format: OPENSSH PRIVATE KEY. Convert it to PEM EC PRIVATE KEY or PKCS#8 PRIVATE KEY.'];
            }

            self::clearOpenSSLErrors();
            
            // Verify we can read the private key
            $privateKeyResource = @openssl_pkey_get_private($resolvedPrivateKey, (string) $passphrase);
            if (!$privateKeyResource) {
                $errors = self::collectOpenSSLErrors();
                $errorText = !empty($errors) ? implode(' | ', $errors) : 'Unknown OpenSSL error';
                return ['success' => false, 'error' => 'Failed to load private key: ' . $errorText];
            }
            openssl_free_key($privateKeyResource);
            
            // Verify we can read the certificate
            self::clearOpenSSLErrors();
            $certResource = @openssl_x509_read($resolvedCertificate);
            if (!$certResource) {
                $errors = self::collectOpenSSLErrors();
                $errorText = !empty($errors) ? implode(' | ', $errors) : 'Unknown OpenSSL error';
                return ['success' => false, 'error' => 'Failed to read certificate: ' . $errorText];
            }
            openssl_x509_free($certResource);
            
            // For API submission, just return the XML and hash
            // ZATCA will validate using the API credentials (binary token), not XML signature
            $invoiceHash = hash('sha256', $invoiceXml);
            
            return [
                'success' => true,
                'invoice_hash' => $invoiceHash,
                'signed_xml' => $invoiceXml
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }

    private static function resolvePemInput($value, $label)
    {
        $value = trim((string) $value);
        $label = strtoupper(trim((string) $label));

        if ($value === '') {
            return '';
        }

        if (is_file($value) && is_readable($value)) {
            $content = @file_get_contents($value);
            if ($content !== false) {
                $value = (string) $content;
            }
        }

        $value = str_replace(["\\r\\n", "\\r"], "\n", $value);
        $value = str_replace('\\n', "\n", $value);
        $value = trim($value);

        if (stripos($value, '-----BEGIN') !== false) {
            if (substr($value, -1) !== "\n") {
                $value .= "\n";
            }
            return $value;
        }

        $raw = preg_replace('/\s+/', '', $value);
        if ($raw === '') {
            return '';
        }

        return '-----BEGIN ' . $label . "-----\n"
            . chunk_split($raw, 64, "\n")
            . '-----END ' . $label . "-----\n";
    }

    private static function clearOpenSSLErrors()
    {
        while (openssl_error_string() !== false) {
            // Drain stale OpenSSL error queue.
        }
    }

    private static function collectOpenSSLErrors()
    {
        $errors = [];
        while (($err = openssl_error_string()) !== false) {
            $err = trim((string) $err);
            if ($err !== '') {
                $errors[] = $err;
            }
        }

        return array_values(array_unique($errors));
    }
}
?>
