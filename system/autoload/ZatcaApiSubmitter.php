<?php
/**
 * ZatcaApiSubmitter - Integration layer for ZATCA submissions
 * 
 * This class bridges ZatcaPhase2 with ZatcaApiClient to provide:
 * - Automatic fallback to PHP API when Java is not available
 * - Compatibility with existing invoice submission workflow
 * - Error handling and logging
 * 
 * Replaces Java SDK calls with direct ZATCA REST API calls
 */

class ZatcaApiSubmitter
{
    /**
     * Check if Java SDK is available
     * 
     * @return bool
     */
    public static function isJavaAvailable()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $result = shell_exec('which java 2>/dev/null');
            return !empty($result);
        }
        
        // Windows
        $result = shell_exec('where java 2>nul');
        return !empty($result);
    }

    /**
     * Determine which method to use for ZATCA submission
     * 
     * @return string 'java' or 'api'
     */
    public static function getPreferredSubmissionMethod()
    {
        // Check requirements for API method
        $apiRequirements = ZatcaApiClient::validateRequirements();
        
        // Check if Java is available
        $javaAvailable = self::isJavaAvailable();
        
        // Prefer Java if available (faster, official method)
        if ($javaAvailable) {
            return 'java';
        }
        
        // Fall back to API if Java not available
        if ($apiRequirements['available']) {
            return 'api';
        }
        
        // Neither available
        return null;
    }

    /**
     * Submit invoice using appropriate method
     * 
     * @param array $invoiceData Invoice data array
     * @param array $config System configuration
     * @param string $forceMethod Force specific method: 'java', 'api', or auto
     * @return array ['success' => bool, 'data' => array, 'method' => string, 'error' => string]
     */
    public static function submitInvoice($invoiceData, $config, $forceMethod = 'auto')
    {
        $method = $forceMethod === 'auto' ? self::getPreferredSubmissionMethod() : $forceMethod;
        
        if (!$method) {
            return [
                'success' => false,
                'method' => null,
                'error' => 'ZATCA submission not available. Java SDK not installed and API requirements not met (cURL/OpenSSL required).',
                'requirements_missing' => ZatcaApiClient::validateRequirements()['missing']
            ];
        }
        
        try {
            if ($method === 'java') {
                // Use existing Java SDK method (ZatcaPhase2)
                return self::submitViaJavaSdk($invoiceData, $config);
            } else {
                // Use new API method
                return self::submitViaApi($invoiceData, $config);
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'method' => $method,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Submit invoice via Java SDK (existing method)
     * 
     * @param array $invoiceData Invoice data
     * @param array $config System config
     * @return array Response
     */
    private static function submitViaJavaSdk($invoiceData, $config)
    {
        // Delegate to existing ZatcaPhase2 implementation via public method
        $invoiceId = $invoiceData['id'] ?? 0;
        
        if (!$invoiceId) {
            return [
                'success' => false,
                'method' => 'java',
                'error' => 'Invoice ID is required for Java SDK submission'
            ];
        }
        
        // Call public method on ZatcaPhase2
        $result = ZatcaPhase2::submitInvoiceById($invoiceId, $config);
        
        return [
            'success' => isset($result['success']) ? $result['success'] : false,
            'method' => 'java',
            'data' => $result
        ];
    }

    /**
     * Submit invoice via ZATCA REST API (new method)
     * 
     * Works on shared hosting without Java requirement
     * 
     * @param array $invoiceData Invoice data
     * @param array $config System config
     * @return array Response
     */
    private static function submitViaApi($invoiceData, $config)
    {
        $client = new ZatcaApiClient($config);
        
        // Get invoice XML and hash
        $invoiceXml = $invoiceData['xml'] ?? '';
        $invoiceHash = $invoiceData['hash'] ?? base64_encode(hash('sha256', (string) $invoiceXml, true));
        
        if (!$invoiceXml) {
            throw new Exception('Invoice XML is required for API submission');
        }
        
        // Determine which endpoint to use
        $submissionType = isset($invoiceData['type']) ? $invoiceData['type'] : 'compliance';
        
        $response = null;
        $endpoint = '';
        
        try {
            if ($submissionType === 'clearance') {
                $response = $client->submitClearanceInvoice($invoiceXml, $invoiceHash);
                $endpoint = 'clearance';
            } elseif ($submissionType === 'reporting') {
                $response = $client->reportInvoice($invoiceXml, $invoiceHash);
                $endpoint = 'reporting';
            } else {
                // Default to compliance
                $response = $client->submitComplianceInvoice($invoiceXml, $invoiceHash);
                $endpoint = 'compliance';
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'method' => 'api',
                'error' => 'API Request Failed: ' . $e->getMessage(),
                'endpoint' => $endpoint,
                'debug' => $client->getLastResponse()
            ];
        }
        
        // Process ZATCA response
        $success = false;
        $message = '';
        $data = [];
        
        if (isset($response['validationResults'])) {
            // Compliance response
            $validationResults = $response['validationResults'];
            $errorCount = isset($validationResults['errorMessages']) 
                ? count($validationResults['errorMessages']) 
                : 0;
            
            $success = $errorCount === 0;
            $message = $success ? 'Validation passed' : 'Validation failed';
            $data = $validationResults;
        } elseif (isset($response['clearedInvoice'])) {
            // Clearance response
            $success = true;
            $message = 'Invoice cleared successfully';
            $data = $response;
        } elseif (isset($response['success'])) {
            // Generic success response
            $success = $response['success'];
            $message = $response['message'] ?? 'Submission completed';
            $data = $response;
        } else {
            // Check HTTP status code
            $httpCode = $response['httpCode'] ?? 0;
            $success = ($httpCode >= 200 && $httpCode < 300);
            $message = $success ? 'Submission successful' : 'Submission failed';
            $data = $response;
        }
        
        return [
            'success' => $success,
            'method' => 'api',
            'message' => $message,
            'endpoint' => $endpoint,
            'data' => $data,
            'debug' => $client->getLastResponse()
        ];
    }

    /**
     * Get diagnostic information about submission methods
     * 
     * @return array Diagnostic data
     */
    public static function getDiagnostics()
    {
        $apiRequirements = ZatcaApiClient::validateRequirements();
        
        return [
            'java_available' => self::isJavaAvailable(),
            'preferred_method' => self::getPreferredSubmissionMethod(),
            'api_available' => $apiRequirements['available'],
            'api_missing_requirements' => $apiRequirements['missing'],
            'curl_available' => ZatcaApiClient::isCurlAvailable(),
            'openssl_available' => ZatcaApiClient::isOpenSSLAvailable(),
            'php_version' => PHP_VERSION,
            'php_os' => PHP_OS_FAMILY
        ];
    }
}
?>
