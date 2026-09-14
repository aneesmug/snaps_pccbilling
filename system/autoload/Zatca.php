<?php

class Zatca
{
    protected static $tableColumns = [];

    protected static function invoiceValue($invoice, $key, $default = '')
    {
        if (is_array($invoice) && array_key_exists($key, $invoice)) {
            return $invoice[$key];
        }

        if (is_object($invoice)) {
            if (isset($invoice->$key)) {
                return $invoice->$key;
            }

            if ($invoice instanceof ArrayAccess && isset($invoice[$key])) {
                return $invoice[$key];
            }
        }

        return $default;
    }

    protected static function encodeTlvLength($length)
    {
        $length = (int) $length;

        if ($length <= 127) {
            return chr($length);
        }

        if ($length <= 255) {
            return chr(0x81) . chr($length);
        }

        return chr(0x82) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
    }

    protected static function encodeTlvTag($tag, $value)
    {
        $value = (string) $value;

        return chr((int) $tag) . self::encodeTlvLength(strlen($value)) . $value;
    }

    protected static function normalizeBase64Value($value, $allowHexHash = false)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (stripos($value, 'data:') === 0 && strpos($value, ',') !== false) {
            $parts = explode(',', $value, 2);
            $value = trim((string) $parts[1]);
        }

        if (stripos($value, '-----BEGIN') !== false) {
            $value = preg_replace('/-----BEGIN[^-]+-----/i', '', $value);
            $value = preg_replace('/-----END[^-]+-----/i', '', $value);
        }

        $value = str_replace(['\r', '\n'], ['', ''], $value);
        $value = preg_replace('/\s+/', '', $value);

        $value = strtr($value, '-_', '+/');

        if ($allowHexHash && preg_match('/^[a-fA-F0-9]{64}$/', $value)) {
            $bin = hex2bin($value);
            if ($bin !== false) {
                return base64_encode($bin);
            }
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return '';
        }

        return base64_encode($decoded);
    }

    protected static function findFirstByKeysRecursive($data, $keys)
    {
        if (!is_array($data) || empty($keys)) {
            return '';
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && trim((string) $data[$key]) !== '') {
                return (string) $data[$key];
            }
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $found = self::findFirstByKeysRecursive($value, $keys);
                if ($found !== '') {
                    return $found;
                }
            }
        }

        return '';
    }

    protected static function resolveInvoiceResponsePayload($invoice)
    {
        $raw = trim((string) self::invoiceValue($invoice, 'zatca_submission_response', ''));

        if ($raw === '') {
            $raw = trim((string) self::invoiceValue($invoice, 'zatca_last_response', ''));
        }

        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected static function isInvoiceSubmittedToZatca($invoice)
    {
        $status = strtolower(trim((string) self::invoiceValue($invoice, 'zatca_status', '')));
        if (in_array($status, ['submitted', 'reported', 'cleared'], true)) {
            return true;
        }

        $response = self::resolveInvoiceResponsePayload($invoice);
        if (empty($response)) {
            return false;
        }

        $reportingStatus = strtoupper(trim((string) self::findFirstByKeysRecursive(
            $response,
            ['reportingStatus', 'reporting_status', 'clearanceStatus', 'clearance_status']
        )));

        if (in_array($reportingStatus, ['REPORTED', 'CLEARED'], true)) {
            return true;
        }

        $httpCode = (int) self::invoiceValue($invoice, 'zatca_submission_http_code', 0);
        if ($status === 'submitted' && $httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        return false;
    }

    protected static function resolvePublicKeyFromCertificate($config = [])
    {
        $certCandidates = [
            isset($config['zatca_production_certificate']) ? (string) $config['zatca_production_certificate'] : '',
            isset($config['zatca_certificate']) ? (string) $config['zatca_certificate'] : '',
        ];

        foreach ($certCandidates as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate === '') {
                continue;
            }

            if (strpos($candidate, '-----BEGIN CERTIFICATE-----') === false) {
                $candidate = "-----BEGIN CERTIFICATE-----\n"
                    . chunk_split(preg_replace('/\s+/', '', $candidate), 64, "\n")
                    . "-----END CERTIFICATE-----\n";
            }

            $pubKey = @openssl_pkey_get_public($candidate);
            if (!$pubKey) {
                continue;
            }

            $details = @openssl_pkey_get_details($pubKey);
            if (!is_array($details) || empty($details['key'])) {
                continue;
            }

            $pem = (string) $details['key'];
            $pem = preg_replace('/-----BEGIN PUBLIC KEY-----/i', '', $pem);
            $pem = preg_replace('/-----END PUBLIC KEY-----/i', '', $pem);
            $pem = preg_replace('/\s+/', '', $pem);

            $decoded = base64_decode($pem, true);
            if ($decoded !== false) {
                return base64_encode($decoded);
            }
        }

        return '';
    }

    protected static function resolvePhase2FromSignedInvoiceXml($xmlBase64)
    {
        $xmlBase64 = preg_replace('/\s+/', '', (string) $xmlBase64);

        if ($xmlBase64 === '') {
            return [];
        }

        $xml = base64_decode($xmlBase64, true);

        if ($xml === false || trim($xml) === '') {
            return [];
        }

        $result = [
            'hash' => base64_encode(hash('sha256', $xml, true)),
            'signature' => '',
            'public_key' => '',
            'qr_signature' => '',
        ];

        if (!class_exists('DOMDocument')) {
            return $result;
        }

        $dom = new DOMDocument();

        if (!@$dom->loadXML($xml)) {
            return $result;
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        $sigNode = $xpath->query('//*[local-name()="SignatureValue"]');
        if ($sigNode && $sigNode->length > 0) {
            $signatureValue = preg_replace('/\s+/', '', (string) $sigNode->item(0)->textContent);
            if ($signatureValue !== '') {
                $result['signature'] = self::normalizeBase64Value($signatureValue);
            }
        }

        $certNode = $xpath->query('//*[local-name()="X509Certificate"]');
        if ($certNode && $certNode->length > 0) {
            $certB64 = preg_replace('/\s+/', '', (string) $certNode->item(0)->textContent);
            if ($certB64 !== '') {
                $pem = "-----BEGIN CERTIFICATE-----\n"
                    . chunk_split($certB64, 64, "\n")
                    . "-----END CERTIFICATE-----\n";

                $pubKey = @openssl_pkey_get_public($pem);
                if ($pubKey) {
                    $details = @openssl_pkey_get_details($pubKey);
                    if (is_array($details) && !empty($details['key'])) {
                        $pubPem = (string) $details['key'];
                        $pubPem = preg_replace('/-----BEGIN PUBLIC KEY-----/i', '', $pubPem);
                        $pubPem = preg_replace('/-----END PUBLIC KEY-----/i', '', $pubPem);
                        $pubPem = preg_replace('/\s+/', '', $pubPem);
                        $decoded = base64_decode($pubPem, true);
                        if ($decoded !== false) {
                            $result['public_key'] = base64_encode($decoded);
                        }
                    }
                }
            }
        }

        if ($result['signature'] !== '') {
            $result['qr_signature'] = $result['signature'];
        }

        return $result;
    }

    protected static function resolvePhase2QrData($invoice, $config = [])
    {
        $response = self::resolveInvoiceResponsePayload($invoice);

        $signedInvoiceBase64 = self::findFirstByKeysRecursive(
            $response,
            ['clearedInvoice', 'invoice', 'signedInvoice', 'invoiceBase64', 'invoice_base64']
        );
        $phase2XmlData = self::resolvePhase2FromSignedInvoiceXml($signedInvoiceBase64);

        $hashCandidates = [
            self::invoiceValue($invoice, 'zatca_invoice_hash', ''),
            self::invoiceValue($invoice, 'zatca_hash', ''),
            self::invoiceValue($invoice, 'invoice_hash', ''),
            self::invoiceValue($invoice, 'pih', ''),
            self::findFirstByKeysRecursive($response, ['invoiceHash', 'invoice_hash', 'hash', 'pih', 'previousInvoiceHash', 'phase2Hash']),
            isset($phase2XmlData['hash']) ? $phase2XmlData['hash'] : '',
        ];

        $signatureCandidates = [
            self::invoiceValue($invoice, 'zatca_signature', ''),
            self::findFirstByKeysRecursive($response, ['signature', 'digitalSignature', 'cryptographicStamp', 'cryptographic_stamp', 'ecdsaSignature', 'ecdsa_signature', 'invoiceSignature', 'phase2Signature']),
            isset($phase2XmlData['signature']) ? $phase2XmlData['signature'] : '',
        ];

        $publicKeyCandidates = [
            self::invoiceValue($invoice, 'zatca_public_key', ''),
            self::findFirstByKeysRecursive($response, ['publicKey', 'public_key', 'ecdsaPublicKey', 'ecdsa_public_key', 'certificatePublicKey', 'phase2PublicKey']),
            isset($phase2XmlData['public_key']) ? $phase2XmlData['public_key'] : '',
            self::resolvePublicKeyFromCertificate($config),
        ];

        $qrSigCandidates = [
            self::invoiceValue($invoice, 'zatca_qr_signature', ''),
            self::findFirstByKeysRecursive($response, ['qrSignature', 'qrCodeSignature', 'ecdsaQrSignature', 'ecdsa_qr_signature', 'phase2QrSignature']),
            isset($phase2XmlData['qr_signature']) ? $phase2XmlData['qr_signature'] : '',
        ];

        $invoiceHash = '';
        foreach ($hashCandidates as $candidate) {
            $normalized = self::normalizeBase64Value($candidate, true);
            if ($normalized !== '') {
                $invoiceHash = $normalized;
                break;
            }
        }

        $invoiceSignature = '';
        foreach ($signatureCandidates as $candidate) {
            $candidate = (string) $candidate;
            if (stripos($candidate, 'data:image/') === 0) {
                continue;
            }

            $normalized = self::normalizeBase64Value($candidate);
            if ($normalized !== '') {
                $invoiceSignature = $normalized;
                break;
            }
        }

        $publicKey = '';
        foreach ($publicKeyCandidates as $candidate) {
            $normalized = self::normalizeBase64Value($candidate);
            if ($normalized !== '') {
                $publicKey = $normalized;
                break;
            }
        }

        $qrSignature = '';
        foreach ($qrSigCandidates as $candidate) {
            $normalized = self::normalizeBase64Value($candidate);
            if ($normalized !== '') {
                $qrSignature = $normalized;
                break;
            }
        }

        if ($qrSignature === '' && $invoiceSignature !== '') {
            $qrSignature = $invoiceSignature;
        }

        return [
            'tag6_hash' => $invoiceHash,
            'tag7_signature' => $invoiceSignature,
            'tag8_public_key' => $publicKey,
            'tag9_qr_signature' => $qrSignature,
        ];
    }

    public static function buildInvoiceQrBase64($invoice, $config = [])
    {
        if (!$invoice) {
            return '';
        }

        $sellerName = isset($config['zatca_seller_name']) && trim((string) $config['zatca_seller_name']) !== ''
            ? trim((string) $config['zatca_seller_name'])
            : (isset($config['CompanyName']) ? trim((string) $config['CompanyName']) : '');

        $sellerVat = self::resolveSellerVatNumber($config);
        $issueDate = trim((string) self::invoiceValue($invoice, 'date', ''));

        if ($issueDate === '') {
            $issueDate = date('Y-m-d H:i:s');
        }

        $timestamp = date('c', strtotime($issueDate));
        $invoiceTotal = self::formatAmount(self::invoiceValue($invoice, 'total', 0));
        $vatTotal = self::formatAmount(self::invoiceValue($invoice, 'tax', 0));

        if ($sellerName === '' || $sellerVat === '') {
            return '';
        }

        $tlv = '';
        $tlv .= self::encodeTlvTag(1, $sellerName);
        $tlv .= self::encodeTlvTag(2, $sellerVat);
        $tlv .= self::encodeTlvTag(3, $timestamp);
        $tlv .= self::encodeTlvTag(4, $invoiceTotal);
        $tlv .= self::encodeTlvTag(5, $vatTotal);

        $phase2Enabled =
            !isset($config['zatca_phase2_enabled']) ||
            (string) $config['zatca_phase2_enabled'] === '1';

        // Business rule: use Phase-1 TLV QR until invoice is actually submitted to ZATCA.
        if ($phase2Enabled && self::isInvoiceSubmittedToZatca($invoice)) {
            $phase2 = self::resolvePhase2QrData($invoice, $config);

            // Include all available Phase-2 cryptographic tags (6-9) from ZATCA response
            if (!empty($phase2['tag6_hash'])) {
                $tlv .= self::encodeTlvTag(6, $phase2['tag6_hash']);
            }

            if (!empty($phase2['tag7_signature'])) {
                $tlv .= self::encodeTlvTag(7, $phase2['tag7_signature']);
            }

            if (!empty($phase2['tag8_public_key'])) {
                $tlv .= self::encodeTlvTag(8, $phase2['tag8_public_key']);
            }

            if (!empty($phase2['tag9_qr_signature'])) {
                $tlv .= self::encodeTlvTag(9, $phase2['tag9_qr_signature']);
            }
        }

        return base64_encode($tlv);
    }

    public static function getInvoiceQrPhaseLabel($invoice, $config = [])
    {
        $phase2Enabled =
            !isset($config['zatca_phase2_enabled']) ||
            (string) $config['zatca_phase2_enabled'] === '1';

        if ($phase2Enabled && self::isInvoiceSubmittedToZatca($invoice)) {
            return 'Phase 2';
        }

        return 'TLV Phase 1';
    }

    protected static function loadTableColumns($table)
    {
        if (!is_string($table) || $table === '') {
            return [];
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return [];
        }

        if (isset(self::$tableColumns[$table]) && is_array(self::$tableColumns[$table])) {
            return self::$tableColumns[$table];
        }

        self::$tableColumns[$table] = [];

        try {
            $db = ORM::get_db();
            $stmt = $db->query('SHOW COLUMNS FROM `' . $table . '`');
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($columns as $column) {
                if (isset($column['Field'])) {
                    self::$tableColumns[$table][$column['Field']] = true;
                }
            }
        } catch (Throwable $e) {
            self::$tableColumns[$table] = [];
        }

        return self::$tableColumns[$table];
    }

    public static function hasTableColumn($table, $column)
    {
        if (!is_string($column) || $column === '') {
            return false;
        }

        $columns = self::loadTableColumns($table);

        return isset($columns[$column]);
    }

    public static function hasInvoiceColumn($column)
    {
        return self::hasTableColumn('sys_invoices', $column);
    }

    public static function formatAmount($amount)
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * Public wrapper to normalize base64 values.
     * Handles data URIs, PEM formats, and hex hashes.
     */
    public static function normalizeBase64ValuePublic($value, $allowHexHash = false)
    {
        return self::normalizeBase64Value($value, $allowHexHash);
    }

    /**
     * Public wrapper to extract Phase-2 cryptographic data from signed invoice XML.
     * Returns array with hash, signature, public_key, qr_signature.
     */
    public static function extractPhase2CryptoData($signedInvoiceBase64)
    {
        return self::resolvePhase2FromSignedInvoiceXml($signedInvoiceBase64);
    }

    public static function resolveSellerVatNumber($config = [])
    {
        // PRIORITY 1: Use zatca_vat_number from config (this is the ZATCA-specific VAT)
        if (isset($config['zatca_vat_number']) && trim((string) $config['zatca_vat_number']) !== '') {
            $vat = preg_replace('/\D+/', '', trim((string) $config['zatca_vat_number']));
            if ($vat !== '') {
                return $vat;
            }
        }

        // PRIORITY 2: Fallback to vat_number config
        if (isset($config['vat_number']) && trim((string) $config['vat_number']) !== '') {
            $value = preg_replace('/\D+/', '', trim((string) $config['vat_number']));
            if ($value !== '') {
                return $value;
            }
        }

        // PRIORITY 3: Fallback to CompanyVat config
        if (isset($config['CompanyVat']) && trim((string) $config['CompanyVat']) !== '') {
            $value = preg_replace('/\D+/', '', trim((string) $config['CompanyVat']));
            if ($value !== '') {
                return $value;
            }
        }

        // PRIORITY 4: Only if all config options fail, try sys_companies lookup (for backward compatibility)
        // But ONLY look up by exact company name match, not by picking random company
        if (self::hasTableColumn('sys_companies', 'vat_number')) {
            $companyName = isset($config['CompanyName']) ? trim((string) $config['CompanyName']) : '';
            
            if ($companyName !== '') {
                $company = ORM::for_table('sys_companies')
                    ->where('company_name', $companyName)
                    ->find_one();
                    
                if ($company && isset($company['vat_number'])) {
                    $vat = preg_replace('/\D+/', '', (string) $company['vat_number']);
                    if ($vat !== '') {
                        return $vat;
                    }
                }
            }
        }

        return '';
    }

    public static function resolveSellerCrn($config = [])
    {
        // PRIORITY 1: Use zatca_seller_crn from config (this is the ZATCA-specific CRN)
        if (isset($config['zatca_seller_crn']) && trim((string) $config['zatca_seller_crn']) !== '') {
            $crn = preg_replace('/\D+/', '', trim((string) $config['zatca_seller_crn']));
            if ($crn !== '') {
                return $crn;
            }
        }

        // PRIORITY 2: Fallback to company_crn config
        if (isset($config['company_crn']) && trim((string) $config['company_crn']) !== '') {
            $value = preg_replace('/\D+/', '', trim((string) $config['company_crn']));
            if ($value !== '') {
                return $value;
            }
        }

        // PRIORITY 3: Fallback to CompanyCRN config
        if (isset($config['CompanyCRN']) && trim((string) $config['CompanyCRN']) !== '') {
            $value = preg_replace('/\D+/', '', trim((string) $config['CompanyCRN']));
            if ($value !== '') {
                return $value;
            }
        }

        // PRIORITY 4: Only if all config options fail, try sys_companies lookup (for backward compatibility)
        // But ONLY look up by exact company name match, not by picking random company
        if (self::hasTableColumn('sys_companies', 'crn_number')) {
            $companyName = isset($config['CompanyName']) ? trim((string) $config['CompanyName']) : '';
            
            if ($companyName !== '') {
                $company = ORM::for_table('sys_companies')
                    ->where('company_name', $companyName)
                    ->find_one();
                    
                if ($company && isset($company['crn_number'])) {
                    $crn = preg_replace('/\D+/', '', (string) $company['crn_number']);
                    if ($crn !== '') {
                        return $crn;
                    }
                }
            }
        }

        return '';
    }

    public static function buildInvoiceNumber($invoice)
    {
        $baseNumber = (string) self::invoiceValue($invoice, 'invoicenum', '');

        if ($baseNumber !== '' && preg_match('/-\d{8}-\d+$/', $baseNumber)) {
            return $baseNumber;
        }

        $cn = (string) self::invoiceValue($invoice, 'cn', '');
        if ($cn !== '') {
            return $baseNumber . $cn;
        }

        return $baseNumber . (string) self::invoiceValue($invoice, 'id', '');
    }

    public static function resolveTimestamp($invoice)
    {
        $source = '';

        $datePaid = (string) self::invoiceValue($invoice, 'datepaid', '');
        $dateOnly = (string) self::invoiceValue($invoice, 'date', '');

        if ($datePaid !== '' && $datePaid !== '0000-00-00 00:00:00') {
            $source = $datePaid;
        } elseif ($dateOnly !== '' && $dateOnly !== '0000-00-00') {
            $source = $dateOnly . ' 00:00:00';
        } else {
            $source = date('Y-m-d H:i:s');
        }

        $timestamp = strtotime($source);
        if ($timestamp === false) {
            $timestamp = time();
        }

        return date('c', $timestamp);
    }

    public static function buildInvoiceHash(
        $invoice,
        $sellerVatNumber,
        $invoiceNumber,
        $invoiceTimestamp,
        $invoiceTotal,
        $invoiceVatTotal
    ) {
        $payload = [
            'invoice_id' => (int) self::invoiceValue($invoice, 'id', 0),
            'invoice_number' => $invoiceNumber,
            'invoice_date' => (string) self::invoiceValue($invoice, 'date', ''),
            'timestamp' => $invoiceTimestamp,
            'seller_vat_number' => (string) $sellerVatNumber,
            'invoice_total' => self::formatAmount($invoiceTotal),
            'vat_total' => self::formatAmount($invoiceVatTotal),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return base64_encode(hash('sha256', (string) $json, true));
    }

    public static function buildBase64Tlv(array $tags)
    {
        $binary = '';

        foreach ($tags as $tag => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $value = (string) $value;

            if (strlen($value) > 255) {
                $value = substr($value, 0, 255);
            }

            // Use proper TLV encoding with correct length encoding
            $binary .= self::encodeTlvTag($tag, $value);
        }

        return base64_encode($binary);
    }

    public static function prepareInvoiceData(
        $invoice,
        $sellerName,
        $sellerVatNumber,
        $invoiceTotal,
        $invoiceVatTotal
    ) {
        $invoiceNumber = self::buildInvoiceNumber($invoice);
        $timestamp = self::resolveTimestamp($invoice);

        // invoiceValue() returns the raw column value, which is PHP null (not '') for a
        // NULL/cleared column - comparing that against '' with !== is true for null too,
        // so casting to string must happen BEFORE the emptiness check, not after, or a
        // cleared column is mistaken for "has a stored value" and silently resolves to ''.
        $uuid = '';
        $storedUuid = trim((string) self::invoiceValue($invoice, 'zatca_uuid', ''));
        if (self::hasInvoiceColumn('zatca_uuid') && $storedUuid !== '') {
            $uuid = $storedUuid;
        } else {
            $uuid = self::generateUuidV4();
        }

        $storedHash = trim((string) self::invoiceValue($invoice, 'zatca_hash', ''));
        if (self::hasInvoiceColumn('zatca_hash') && $storedHash !== '') {
            $hash = $storedHash;
        } else {
            $hash = self::buildInvoiceHash(
                $invoice,
                $sellerVatNumber,
                $invoiceNumber,
                $timestamp,
                $invoiceTotal,
                $invoiceVatTotal
            );
        }

        $signature = '';
        $publicKey = '';
        $caSignature = '';

        $storedSignature = trim((string) self::invoiceValue($invoice, 'zatca_signature', ''));
        if (self::hasInvoiceColumn('zatca_signature') && $storedSignature !== '') {
            $signature = $storedSignature;
        }

        $storedPublicKey = trim((string) self::invoiceValue($invoice, 'zatca_public_key', ''));
        if (self::hasInvoiceColumn('zatca_public_key') && $storedPublicKey !== '') {
            $publicKey = $storedPublicKey;
        }

        $storedCaSignature = trim((string) self::invoiceValue($invoice, 'zatca_ca_signature', ''));
        if (self::hasInvoiceColumn('zatca_ca_signature') && $storedCaSignature !== '') {
            $caSignature = $storedCaSignature;
        }

        $tlv = self::buildBase64Tlv([
            1 => (string) $sellerName,
            2 => (string) $sellerVatNumber,
            3 => $timestamp,
            4 => self::formatAmount($invoiceTotal),
            5 => self::formatAmount($invoiceVatTotal),
            6 => $hash,
            7 => $signature,
            8 => $publicKey,
            9 => $caSignature,
        ]);

        return [
            'uuid' => $uuid,
            'hash' => $hash,
            'signature' => $signature,
            'public_key' => $publicKey,
            'ca_signature' => $caSignature,
            'seller_name' => (string) $sellerName,
            'seller_vat' => (string) $sellerVatNumber,
            'timestamp' => $timestamp,
            'invoice_number' => $invoiceNumber,
            'tlv' => $tlv,
        ];
    }

    public static function persistInvoiceData($invoiceId, array $data)
    {
        $updatableColumns = [
            'zatca_uuid' => 'uuid',
            'zatca_hash' => 'hash',
            'zatca_signature' => 'signature',
            'zatca_public_key' => 'public_key',
            'zatca_ca_signature' => 'ca_signature',
        ];

        $hasAnyColumn = false;
        foreach (array_keys($updatableColumns) as $column) {
            if (self::hasInvoiceColumn($column)) {
                $hasAnyColumn = true;
                break;
            }
        }

        if (!$hasAnyColumn) {
            return false;
        }

        $invoice = ORM::for_table('sys_invoices')->find_one((int) $invoiceId);
        if (!$invoice) {
            return false;
        }

        $hasChanges = false;

        foreach ($updatableColumns as $column => $key) {
            if (!self::hasInvoiceColumn($column) || !isset($data[$key])) {
                continue;
            }

            if ($column === 'zatca_uuid' && !empty($invoice->$column)) {
                continue;
            }

            if (
                in_array($column, ['zatca_signature', 'zatca_public_key', 'zatca_ca_signature'], true) &&
                empty($data[$key])
            ) {
                continue;
            }

            if ((string) $invoice->$column !== (string) $data[$key]) {
                $invoice->$column = $data[$key];
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $invoice->save();
        }

        return $hasChanges;
    }

    public static function generateUuidV4()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
