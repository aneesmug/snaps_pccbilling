<?php
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;

global $ui, $_L, $config, $routes, $file_build, $request, $plugin, $d;

_auth();
$ui->assign('_title', $_L['Settings'] . '- ' . $config['CompanyName']);
$ui->assign('selected_navigation', 'settings');

$action = $routes['1'];
$user = authenticate_admin();
$data = request()->all();

if (!function_exists('zatca_normalize_otp_input')) {
    function zatca_normalize_otp_input($otp)
    {
        $otp = trim((string) $otp);
        if ($otp === '') {
            return '';
        }

        $otp = strtr($otp, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        return preg_replace('/[^0-9]+/', '', $otp);
    }
}

if (!function_exists('build_zatca_options_from_request')) {
    function build_zatca_options_from_request($data)
    {
        return [
            'zatca_enabled' => !empty($data['zatca_enabled']) ? '1' : '0',
            'zatca_phase2_enabled' => !empty($data['zatca_phase2_enabled']) ? '1' : '0',
            'zatca_environment' => isset($data['zatca_environment'])
                ? trim((string) $data['zatca_environment'])
                : '',
            'zatca_setup_mode' => isset($data['zatca_setup_mode'])
                ? trim((string) $data['zatca_setup_mode'])
                : 'csr_otp',
            'zatca_api_base_url' => isset($data['zatca_api_base_url'])
                ? trim((string) $data['zatca_api_base_url'])
                : '',
            'zatca_invoice_type' => isset($data['zatca_invoice_type'])
                ? trim((string) $data['zatca_invoice_type'])
                : 'simplified',
            'zatca_seller_name' => isset($data['zatca_seller_name'])
                ? trim((string) $data['zatca_seller_name'])
                : '',
            'zatca_vat_number' => isset($data['zatca_vat_number'])
                ? preg_replace('/\D+/', '', (string) $data['zatca_vat_number'])
                : '',
            'zatca_seller_crn' => isset($data['zatca_seller_crn'])
                ? preg_replace('/\D+/', '', (string) $data['zatca_seller_crn'])
                : '',
            'zatca_building_no' => isset($data['zatca_building_no'])
                ? trim((string) $data['zatca_building_no'])
                : '',
            'zatca_street_name' => isset($data['zatca_street_name'])
                ? trim((string) $data['zatca_street_name'])
                : '',
            'zatca_district' => isset($data['zatca_district'])
                ? trim((string) $data['zatca_district'])
                : '',
            'zatca_city' => isset($data['zatca_city'])
                ? trim((string) $data['zatca_city'])
                : '',
            'zatca_postal_code' => isset($data['zatca_postal_code'])
                ? trim((string) $data['zatca_postal_code'])
                : '',
            'zatca_country_code' => isset($data['zatca_country_code'])
                ? strtoupper(trim((string) $data['zatca_country_code']))
                : 'SA',
            'zatca_csr_content' => isset($data['zatca_csr_content'])
                ? trim((string) $data['zatca_csr_content'])
                : '',
            'zatca_otp' => isset($data['zatca_otp'])
                ? zatca_normalize_otp_input($data['zatca_otp'])
                : '',
            'zatca_binary_security_token' => isset($data['zatca_binary_security_token'])
                ? trim((string) $data['zatca_binary_security_token'])
                : '',
            'zatca_secret' => isset($data['zatca_secret'])
                ? trim((string) $data['zatca_secret'])
                : '',
            'zatca_compliance_request_id' => isset($data['zatca_compliance_request_id'])
                ? trim((string) $data['zatca_compliance_request_id'])
                : '',
            'zatca_private_key_passphrase' => isset($data['zatca_private_key_passphrase'])
                ? trim((string) $data['zatca_private_key_passphrase'])
                : '',
            'zatca_private_key' => isset($data['zatca_private_key'])
                ? trim((string) $data['zatca_private_key'])
                : '',
            'zatca_certificate' => isset($data['zatca_certificate'])
                ? trim((string) $data['zatca_certificate'])
                : '',
            'zatca_compliance_csid' => isset($data['zatca_compliance_csid'])
                ? trim((string) $data['zatca_compliance_csid'])
                : '',
            'zatca_compliance_secret' => isset($data['zatca_compliance_secret'])
                ? trim((string) $data['zatca_compliance_secret'])
                : '',
            'zatca_production_csid' => isset($data['zatca_production_csid'])
                ? trim((string) $data['zatca_production_csid'])
                : '',
            'zatca_production_binary_security_token' => isset($data['zatca_production_binary_security_token'])
                ? trim((string) $data['zatca_production_binary_security_token'])
                : '',
            'zatca_production_secret' => isset($data['zatca_production_secret'])
                ? trim((string) $data['zatca_production_secret'])
                : '',
        ];
    }
}

if (!function_exists('zatca_check_pem_or_file')) {
    function zatca_check_pem_or_file($value, $label, $allowRawBase64 = false, $allowFilePath = true)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $label . ' is required.';
        }

        if (stripos($value, '-----BEGIN') !== false) {
            return '';
        }

        if ($allowFilePath && is_file($value)) {
            return '';
        }

        if ($allowRawBase64) {
            $compact = preg_replace('/\s+/', '', $value);

            if (
                $compact !== '' &&
                preg_match('/^[A-Za-z0-9+\/=]+$/', $compact) &&
                base64_decode($compact, true) !== false
            ) {
                return '';
            }
        }

        if ($allowFilePath) {
            return $label . ' must be PEM text or an existing server file path.';
        }

        return $label . ' must be inline PEM text.';
    }
}

if (!function_exists('zatca_detect_csr_curve')) {
    function zatca_detect_csr_curve($csrValue)
    {
        $csrValue = trim((string) $csrValue);
        $debug = [];

        $csrValue = trim((string) $csrValue);
        if ($csrValue === '' || strpos($csrValue, 'BEGIN CERTIFICATE REQUEST') === false) {
            $debug[] = "Curve detection: Invalid CSR format";
            if (!empty($debug) && function_exists('error_log')) {
                error_log(implode("\n", $debug));
            }
            return null;
        }
        
        // Extract base64 content from PEM
        $csr_b64 = preg_replace('/-----BEGIN CERTIFICATE REQUEST-----/', '', $csrValue);
        $csr_b64 = preg_replace('/-----END CERTIFICATE REQUEST-----/', '', $csr_b64);
        $csr_b64 = preg_replace('/\s+/', '', $csr_b64);
        
        // Decode to DER
        $der = base64_decode($csr_b64, true);
        if ($der === false || strlen($der) < 20) {
            $debug[] = "Curve detection: Failed to decode base64 or DER too short";
            if (!empty($debug) && function_exists('error_log')) {
                error_log(implode("\n", $debug));
            }
            return null;
        }
        
        // Look for curve OID patterns in the DER:
        // prime256v1 (P-256): 1.2.840.10045.3.1.7  → hex: 06 08 2A 86 48 CE 3D 03 01 07
        // secp256k1: 1.3.132.0.10  → hex: 06 05 2B 81 04 00 0A
        
        $prime256v1_oid = "\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";
        $secp256k1_oid = "\x06\x05\x2B\x81\x04\x00\x0A";
        
        if (strpos($der, $prime256v1_oid) !== false) {
            $debug[] = "Curve detection: ✗ DETECTED PRIME256V1 (P-256) - NOT ZATCA COMPATIBLE";
            if (!empty($debug) && function_exists('error_log')) {
                error_log(implode("\n", $debug));
            }
            return 'prime256v1';
        }
        if (strpos($der, $secp256k1_oid) !== false) {
            $debug[] = "Curve detection: ✓ DETECTED SECP256K1 - ZATCA COMPATIBLE";
            if (!empty($debug) && function_exists('error_log')) {
                error_log(implode("\n", $debug));
            }
            return 'secp256k1';
        }
        
        $debug[] = "Curve detection: Unknown/undetected curve";
        if (!empty($debug) && function_exists('error_log')) {
            error_log(implode("\n", $debug));
        }
        return 'unknown';
    }
}

if (!function_exists('validate_zatca_options')) {
    function validate_zatca_options($options, $strict = true)
    {
        $errors = [];

        $validEnvironments = ['sandbox', 'simulation', 'production'];
        if (!in_array($options['zatca_environment'], $validEnvironments)) {
            $errors[] = 'Invalid ZATCA environment.';
        }

        $validSetupModes = ['csr_otp', 'portal_keys'];
        if (!isset($options['zatca_setup_mode']) || !in_array($options['zatca_setup_mode'], $validSetupModes)) {
            $options['zatca_setup_mode'] = 'csr_otp';
        }

        $validInvoiceTypes = ['simplified', 'standard'];
        if (!in_array($options['zatca_invoice_type'], $validInvoiceTypes)) {
            $errors[] = 'Invalid ZATCA invoice type.';
        }

        if ($options['zatca_api_base_url'] !== '' && !filter_var($options['zatca_api_base_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid ZATCA API base URL.';
        }

        if ($options['zatca_api_base_url'] !== '') {
            $apiBase = strtolower(rtrim((string) $options['zatca_api_base_url'], '/'));
            $environment = strtolower((string) $options['zatca_environment']);

            if ($environment === 'production' && strpos($apiBase, '/e-invoicing/core') === false) {
                $errors[] = 'Production environment requires ZATCA API base URL ending with /e-invoicing/core.';
            }

            if ($environment === 'simulation' && strpos($apiBase, '/e-invoicing/simulation') === false) {
                $errors[] = 'Simulation environment requires ZATCA API base URL ending with /e-invoicing/simulation.';
            }

            if ($environment === 'sandbox' && strpos($apiBase, '/e-invoicing/developer-portal') === false) {
                $errors[] = 'Sandbox environment requires ZATCA API base URL ending with /e-invoicing/developer-portal.';
            }
        }

        if ($options['zatca_enabled'] === '1' && $strict) {
            if (strlen($options['zatca_vat_number']) !== 15) {
                $errors[] = 'ZATCA VAT Number must be 15 digits.';
            }

            if ($options['zatca_country_code'] === '' || strlen($options['zatca_country_code']) !== 2) {
                $errors[] = 'Country Code must be 2 letters (ISO-2).';
            }

            if ($options['zatca_postal_code'] !== '' && !preg_match('/^[A-Za-z0-9\- ]+$/', $options['zatca_postal_code'])) {
                $errors[] = 'Postal Zone contains invalid characters.';
            }

            if ($options['zatca_otp'] !== '' && !preg_match('/^[0-9]{6}$/', $options['zatca_otp'])) {
                $errors[] = 'Compliance OTP must be exactly 6 digits.';
            }

            foreach (['zatca_seller_name', 'zatca_seller_crn', 'zatca_street_name', 'zatca_building_no', 'zatca_city', 'zatca_district'] as $requiredField) {
                if (trim((string) $options[$requiredField]) === '') {
                    $errors[] = str_replace('_', ' ', strtoupper($requiredField)) . ' is required.';
                }
            }

            $pemChecks = [
                'zatca_private_key' => ['Private Key', false],
            ];

            if ($options['zatca_setup_mode'] === 'csr_otp') {
                $pemChecks['zatca_csr_content'] = ['CSR', true];

                if (trim((string) $options['zatca_otp']) === '') {
                    $errors[] = 'Compliance OTP is required when using CSR + OTP flow.';
                }
            } else {
                $pemChecks['zatca_certificate'] = ['Certificate', false];

                if (trim((string) $options['zatca_binary_security_token']) === '') {
                    $errors[] = 'Binary Security Token is required when using Portal Keys flow.';
                }

                if (trim((string) $options['zatca_secret']) === '') {
                    $errors[] = 'Secret is required when using Portal Keys flow.';
                }
            }

            foreach ($pemChecks as $field => $rule) {
                $label = $rule[0];
                $allowRawBase64 = $rule[1];
                $allowFilePath = !in_array($field, ['zatca_csr_content', 'zatca_private_key'], true);

                $err = zatca_check_pem_or_file($options[$field], $label, $allowRawBase64, $allowFilePath);
                if ($err !== '') {
                    $errors[] = $err;
                }
            }
        }

        return $errors;
    }
}

if (!function_exists('zatca_has_active_credentials')) {
    function zatca_has_active_credentials($config)
    {
        $config = is_array($config) ? $config : [];

        $keys = [
            'zatca_binary_security_token',
            'zatca_secret',
            'zatca_compliance_request_id',
            'zatca_production_binary_security_token',
            'zatca_production_secret',
            'zatca_production_csid',
        ];

        foreach ($keys as $key) {
            if (isset($config[$key]) && trim((string) $config[$key]) !== '') {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('zatca_key_material_changed')) {
    function zatca_key_material_changed($newOptions, $existingConfig)
    {
        $newOptions = is_array($newOptions) ? $newOptions : [];
        $existingConfig = is_array($existingConfig) ? $existingConfig : [];

        $materialFields = [
            'zatca_csr_content',
            'zatca_private_key',
            'zatca_certificate',
            'zatca_private_key_passphrase',
        ];

        foreach ($materialFields as $field) {
            $newValue = isset($newOptions[$field]) ? trim((string) $newOptions[$field]) : '';
            $oldValue = isset($existingConfig[$field]) ? trim((string) $existingConfig[$field]) : '';
            if ($newValue !== '' && $oldValue !== '' && $newValue !== $oldValue) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('zatca_extract_compliance_credentials_from_response')) {
    function zatca_extract_compliance_credentials_from_response($responseBody)
    {
        $responseBody = trim((string) $responseBody);
        if ($responseBody === '') {
            return [
                'binary_security_token' => '',
                'secret' => '',
                'request_id' => '',
            ];
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            return [
                'binary_security_token' => '',
                'secret' => '',
                'request_id' => '',
            ];
        }

        $pick = function ($keys) use ($decoded) {
            foreach ($keys as $key) {
                if (isset($decoded[$key]) && is_scalar($decoded[$key])) {
                    $value = trim((string) $decoded[$key]);
                    if ($value !== '') {
                        return $value;
                    }
                }
            }

            $stack = [$decoded];
            while (!empty($stack)) {
                $node = array_pop($stack);
                if (!is_array($node)) {
                    continue;
                }

                foreach ($node as $k => $v) {
                    if (is_array($v)) {
                        $stack[] = $v;
                        continue;
                    }

                    if (!is_scalar($v)) {
                        continue;
                    }

                    foreach ($keys as $wanted) {
                        if (strcasecmp((string) $k, (string) $wanted) === 0) {
                            $value = trim((string) $v);
                            if ($value !== '') {
                                return $value;
                            }
                        }
                    }
                }
            }

            return '';
        };

        return [
            'binary_security_token' => $pick(['binarySecurityToken', 'binary_security_token', 'complianceCsid', 'compliance_csid']),
            'secret' => $pick(['secret', 'complianceSecret', 'compliance_secret']),
            'request_id' => $pick(['requestID', 'requestId', 'request_id', 'complianceRequestID', 'compliance_request_id']),
        ];
    }
}

if (!function_exists('zatca_merge_runtime_config_preserve_non_empty')) {
    function zatca_merge_runtime_config_preserve_non_empty($baseConfig, $requestOptions)
    {
        $baseConfig = is_array($baseConfig) ? $baseConfig : [];
        $requestOptions = is_array($requestOptions) ? $requestOptions : [];

        $merged = $baseConfig;
        foreach ($requestOptions as $key => $value) {
            if (is_string($value)) {
                if (trim($value) !== '') {
                    $merged[$key] = $value;
                } elseif (!isset($merged[$key])) {
                    $merged[$key] = '';
                }
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }
}

if (!function_exists('zatca_preserve_saved_credential_values_on_empty')) {
    function zatca_preserve_saved_credential_values_on_empty($options, $existingConfig)
    {
        $options = is_array($options) ? $options : [];
        $existingConfig = is_array($existingConfig) ? $existingConfig : [];

        $protectedKeys = [
            'zatca_binary_security_token',
            'zatca_secret',
            'zatca_compliance_request_id',
            'zatca_compliance_csid',
            'zatca_compliance_secret',
            'zatca_certificate',
            'zatca_production_binary_security_token',
            'zatca_production_secret',
            'zatca_production_csid',
            'zatca_production_request_id',
            'zatca_production_certificate',
        ];

        foreach ($protectedKeys as $key) {
            $newValue = isset($options[$key]) ? trim((string) $options[$key]) : '';
            $oldValue = isset($existingConfig[$key]) ? trim((string) $existingConfig[$key]) : '';

            if ($newValue === '' && $oldValue !== '') {
                $options[$key] = $oldValue;
            }
        }

        return $options;
    }
}

if (!function_exists('zatca_is_protected_credential_key')) {
    function zatca_is_protected_credential_key($key)
    {
        $key = (string) $key;
        $protectedKeys = [
            'zatca_binary_security_token',
            'zatca_secret',
            'zatca_compliance_request_id',
            'zatca_compliance_csid',
            'zatca_compliance_secret',
            'zatca_certificate',
            'zatca_production_binary_security_token',
            'zatca_production_secret',
            'zatca_production_csid',
            'zatca_production_request_id',
            'zatca_production_certificate',
        ];

        return in_array($key, $protectedKeys, true);
    }
}

if (!function_exists('zatca_load_options_from_appconfig')) {
    function zatca_load_options_from_appconfig(array $keys)
    {
        $result = [];
        foreach ($keys as $k) {
            $result[(string) $k] = '';
        }

        if (empty($keys)) {
            return $result;
        }

        $loaded = false;

        if (class_exists('ORM')) {
            try {
                $rows = ORM::for_table('sys_appconfig')
                    ->select_many('setting', 'value')
                    ->where_in('setting', $keys)
                    ->find_many();

                foreach ($rows as $row) {
                    $setting = (string) $row->setting;
                    if (array_key_exists($setting, $result)) {
                        $result[$setting] = trim((string) $row->value);
                    }
                }

                $loaded = count($rows) > 0;
            } catch (Throwable $e) {
                if (function_exists('error_log')) {
                    error_log('[ZATCA] Failed to load settings from sys_appconfig via ORM: ' . $e->getMessage());
                }
            }
        }

        // Fallback for contexts where ORM is unavailable or not initialized.
        if (!$loaded && class_exists('PDO') && defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            try {
                $dbPort = defined('DB_PORT') ? trim((string) DB_PORT) : '';
                $dsn = 'mysql:host=' . DB_HOST
                    . ($dbPort !== '' ? ';port=' . $dbPort : '')
                    . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                $pdo = new PDO($dsn, DB_USER, defined('DB_PASSWORD') ? DB_PASSWORD : '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $placeholders = implode(',', array_fill(0, count($keys), '?'));
                $stmt = $pdo->prepare('SELECT `setting`, `value` FROM `sys_appconfig` WHERE `setting` IN (' . $placeholders . ')');
                $stmt->execute(array_values($keys));

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $setting = isset($row['setting']) ? (string) $row['setting'] : '';
                    if (array_key_exists($setting, $result)) {
                        $result[$setting] = trim((string) $row['value']);
                    }
                }
            } catch (Throwable $e) {
                if (function_exists('error_log')) {
                    error_log('[ZATCA] Failed to load settings from sys_appconfig via PDO: ' . $e->getMessage());
                }
            }
        }

        return $result;
    }
}

if (!function_exists('zatca_persist_option_best_effort')) {
    function zatca_persist_option_best_effort($key, $value)
    {
        $key = (string) $key;
        $value = (string) $value;

        try {
            if (function_exists('update_option') && update_option($key, $value)) {
                return true;
            }

            if (function_exists('add_option') && add_option($key, $value)) {
                return true;
            }
        } catch (Throwable $e) {
            if (function_exists('error_log')) {
                error_log('[ZATCA] option helper update/add failed for ' . $key . ': ' . $e->getMessage());
            }
        }

        try {
            return AppConfig::saveZatcaOptionsUsingPdo([$key => $value]);
        } catch (Throwable $e) {
            if (function_exists('error_log')) {
                error_log('[ZATCA] AppConfig save failed for ' . $key . ': ' . $e->getMessage());
            }
        }

        return false;
    }
}

if (!function_exists('zatca_persist_appconfig_best_effort')) {
    function zatca_persist_appconfig_best_effort(array $options)
    {
        $options = is_array($options) ? $options : [];
        if (empty($options)) {
            return false;
        }

        try {
            if (class_exists('AppConfig')) {
                $saved = AppConfig::saveZatcaOptionsUsingPdo($options);
                if ($saved) {
                    return true;
                }
            }
        } catch (Throwable $e) {
            if (function_exists('error_log')) {
                error_log('[ZATCA] AppConfig direct save failed: ' . $e->getMessage());
            }
        }

        // Final PDO fallback when AppConfig is unavailable in this runtime context.
        if (class_exists('PDO') && defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            try {
                $dbPort = defined('DB_PORT') ? trim((string) DB_PORT) : '';
                $dsn = 'mysql:host=' . DB_HOST
                    . ($dbPort !== '' ? ';port=' . $dbPort : '')
                    . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                $pdo = new PDO($dsn, DB_USER, defined('DB_PASSWORD') ? DB_PASSWORD : '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                foreach ($options as $key => $value) {
                    $key = (string) $key;
                    $value = (string) $value;

                    $update = $pdo->prepare('UPDATE `sys_appconfig` SET `value` = :value WHERE `setting` = :setting');
                    $update->execute([
                        ':value' => $value,
                        ':setting' => $key,
                    ]);

                    if ((int) $update->rowCount() === 0) {
                        $insert = $pdo->prepare('INSERT INTO `sys_appconfig` (`setting`, `value`) VALUES (:setting, :value)');
                        $insert->execute([
                            ':setting' => $key,
                            ':value' => $value,
                        ]);
                    }
                }

                return true;
            } catch (Throwable $e) {
                if (function_exists('error_log')) {
                    error_log('[ZATCA] PDO direct appconfig save failed: ' . $e->getMessage());
                }
            }
        }

        return false;
    }
}

switch ($action) {

    case 'quick-links':

        $selected_quick_link = null;

        $id = route(2);

        $quick_links = [];
        if(!empty($user->preferences['quick_links'])){
            $quick_links = json_decode($user->preferences['quick_links']);
        }

        if($id)
        {
            foreach ($quick_links as $quick_link)
            {
                if($quick_link->id == $id)
                {
                    $selected_quick_link = $quick_link;
                    break;
                }
            }
        }

        view('quick-links',[
            'selected_quick_link' => $selected_quick_link,
        ]);


        break;

        case  'save-quick-link':

            $quick_links = [];
            if(!empty($user->preferences['quick_links'])){
                $quick_links = json_decode($user->preferences['quick_links'],true);
            }

            if(!empty($data['name']) && !empty($data['url'])){

                $open_new_tab = $data['open_new_tab'] ?? false;

                if(!empty($data['id']))
                {
                    $updated_quick_links = [];
                    foreach($quick_links as $quick_link){
                        if($quick_link['id'] == $data['id']){
                            $updated_quick_links[] = [
                                'id' => $quick_link['id'],
                                'name' => $data['name'],
                                'url' => $data['url'],
                                'open_new_tab' => $open_new_tab,
                            ];
                        }else{
                            $updated_quick_links[] = $quick_link;
                        }
                    }

                    $quick_links = $updated_quick_links;

                }
                else{
                    $next_id = count($quick_links) + 1;
                    $quick_links[] = [
                        'id' => $next_id,
                        'name' => $data['name'],
                        'url' => $data['url'],
                        'open_new_tab' => $open_new_tab,
                    ];
                }
            }

            UserPreference::setPreference($user->id, 'quick_links', json_encode($quick_links));

            appFlashMessage(__('Data Updated'));
            redirect_to('settings/quick-links');

            break;

            case 'delete-quick-link':
                $id = route(2);
                if(!empty($user->preferences['quick_links'])){
                    $quick_links = json_decode($user->preferences['quick_links'],true);
                    $updated_quick_links = [];
                    foreach($quick_links as $quick_link){
                        if($quick_link['id'] != $id){
                            $updated_quick_links[] = $quick_link;
                        }
                    }

                    UserPreference::setPreference($user->id, 'quick_links',json_encode($updated_quick_links));

                }

                appFlashMessage(__('delete_successful'));
                redirect_to('settings/quick-links');

                break;

                case 'reorder-quick-links':

                    $updateRecordsArray = $data['recordsArray'];


                    if(!empty($user->preferences['quick_links'])) {
                        $quick_links = json_decode($user->preferences['quick_links'], true);
                        $updated_quick_links = [];


                        foreach ($updateRecordsArray as $id) {
                            //Find the quick link in the array
                            $item = appArrayFindById($quick_links, $id);
                            if(!empty($item)) {
                                $updated_quick_links[] = $item;
                            }
                        }

                        UserPreference::setPreference($user->id, 'quick_links',json_encode($updated_quick_links));

                    }

                    echo create_alert_message($_L['Updated']);


                    break;

    case 'expense-categories':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $d = ORM::for_table('sys_cats')
            ->where('type', 'Expense')
            ->order_by_asc('sorder')
            ->find_many();

        $ui->assign('d', $d);

        view('expense-categories');

        break;

    case 'expense-categories-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $name = _post('name');
        if ($name == '') {
            r2(U . "settings/expense-categories", 'e', $_L['name_error']);
        }
        $c = ORM::for_table('sys_cats')
            ->where('name', $name)
            ->where('type', 'Expense')
            ->first();
        if ($c) {
            r2(U . "settings/expense-categories", 'e', $_L['name_exist_error']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/expense-categories',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $d = ORM::for_table('sys_cats')->create();

        $d->name = $name;
        $d->type = 'Expense';
        $d->save();
        r2(U . "settings/expense-categories", 's', $_L['added_successful']);
        break;

    case 'income-categories':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $d = ORM::for_table('sys_cats')
            ->where('type', 'Income')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('d', $d);

        view('income-categories');

        break;

    case 'income-categories-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $name = _post('name');
        if ($name == '') {
            r2(U . "settings/income-categories", 'e', $_L['name_error']);
        }
        $c = ORM::for_table('sys_cats')
            ->where('name', $name)
            ->where('type', 'Income')
            ->first();
        if ($c) {
            r2(U . "settings/income-categories", 'e', $_L['name_exist_error']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/income-categories',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $d = ORM::for_table('sys_cats')->create();

        $d->name = $name;
        $d->type = 'Income';
        $d->save();
        r2(U . "settings/income-categories", 's', $_L['added_successful']);
        break;

    case 'categories-manage':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $id = $routes[2];
        $d = ORM::for_table('sys_cats')->find($id);
        if ($d) {
            $ui->assign('c', $d);
            view('categories-edit');
        }

        break;

    case 'categories-edit-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $id = _post('id');
        $d = ORM::for_table('sys_cats')->find($id);
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/expense-categories',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        if ($d) {
            $otype = $d['type'];
            $rd = strtolower($otype);
            $name = _post('name');
            $c = ORM::for_table('sys_cats')
                ->where('name', $name)
                ->where('type', $otype)
                ->first();
            if ($c) {
                r2(U . "settings/$rd-categories", 'e', $_L['name_exist_error']);
            }
            $oname = $d['name'];
            $type = $d['type'];
            if ($name == '') {
                r2(
                    U . "settings/categories-manage/$id",
                    'e',
                    $_L['name_error']
                );
            } else {
                $d->name = $name;
                $d->save();
                ORM::for_table('sys_transactions')->raw_execute(
                    "update sys_transactions set category='$name' where (category='$oname' AND type='$type')"
                );
                r2(
                    U . "settings/categories-manage/$id",
                    's',
                    $_L['edit_successful']
                );
            }
        }
        break;

    case 'categories-delete':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $id = $routes[2];
        $d = ORM::for_table('sys_cats')->find($id);
        if ($d) {
            if (APP_STAGE == 'Demo') {
                r2(
                    U . 'settings/expense-categories',
                    'e',
                    'Sorry! This option is disabled in the demo mode.'
                );
            }
            $name = $d['name'];
            $type = $d['type'];

            ORM::for_table('sys_transactions')->raw_query(
                "update sys_transactions set category=:cat where category='$name' AND type='$type'",
                ['cat' => 'Uncategorized']
            );
            $d->delete();
            if ($type == 'Income') {
                r2(
                    U . "settings/income-categories",
                    's',
                    $_L['delete_successful']
                );
            } else {
                r2(
                    U . "settings/expense-categories",
                    's',
                    $_L['delete_successful']
                );
            }
        }
        break;

    case 'payee':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $d = ORM::for_table('sys_payee')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('d', $d);

        view('payee');

        break;

    case 'payee-manage':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $id = $routes[2];
        $d = ORM::for_table('sys_payee')->find($id);
        if ($d) {
            $ui->assign('c', $d);
            view('payee-manage');
        }

        break;

    case 'payee-edit-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/payee',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $id = _post('id');
        $d = ORM::for_table('sys_payee')->find($id);
        if ($d) {
            $name = _post('name');
            $c = ORM::for_table('sys_payee')
                ->where('name', $name)
                ->first();
            if ($c) {
                r2(U . "settings/payee", 'e', $_L['name_exist_error']);
            }

            $oname = $d['name'];

            if ($name == '') {
                r2(U . "settings/payee-manage/$id", 'e', $_L['name_error']);
            } else {
                $d->name = $name;
                $d->save();
                ORM::for_table('sys_transactions')->raw_query(
                    "update sys_transactions set payee=:payee where payee='$oname'",
                    ['payee' => $name]
                );
                r2(
                    U . "settings/payee-manage/$id",
                    's',
                    $_L['edit_successful']
                );
            }
        }

        break;

    case 'payee-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $name = _post('name');
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/payee',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        if ($name == '') {
            r2(U . "settings/payee", 'e', $_L['name_error']);
        }

        $c = ORM::for_table('sys_payee')
            ->where('name', $name)
            ->first();
        if ($c) {
            r2(U . "settings/payee", 'e', $_L['name_exist_error']);
        }

        $d = ORM::for_table('sys_payee')->create();

        $d->name = $name;

        $d->save();
        r2(U . "settings/payee", 's', $_L['added_successful']);
        break;

    case 'payee-delete':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/payee',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $id = $routes[2];
        $d = ORM::for_table('sys_payee')->find($id);
        if ($d) {
            $d->delete();

            r2(U . "settings/payee", 's', $_L['delete_successful']);
        }
        break;

    case 'payer':
        $ui->assign('content_inner', inner_contents($config['c_cache']));

        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $d = ORM::for_table('sys_payers')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('d', $d);

        view('payer');

        break;

    case 'payer-manage':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $id = $routes[2];
        $d = ORM::for_table('sys_payers')->find($id);
        if ($d) {
            $ui->assign('c', $d);
            view('payer-manage');
        }

        break;

    case 'payer-edit-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/payer',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $id = _post('id');
        $d = ORM::for_table('sys_payers')->find($id);
        if ($d) {
            $name = _post('name');
            $c = ORM::for_table('sys_payers')
                ->where('name', $name)
                ->first();
            if ($c) {
                r2(U . "settings/payer", 'e', $_L['name_exist_error']);
            }

            $oname = $d['name'];

            if ($name == '') {
                r2(U . "settings/payer-manage/$id", 'e', $_L['name_error']);
            } else {
                $d->name = $name;
                $d->save();

                ORM::for_table('sys_transactions')->raw_query(
                    "update sys_transactions set payer=:payer where payer='$oname'",
                    ['payer' => $name]
                );
                r2(
                    U . "settings/payer-manage/$id",
                    's',
                    $_L['edit_successful']
                );
            }
        }

        break;

    case 'payer-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/payer',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $name = _post('name');
        if ($name == '') {
            r2(U . "settings/payer", 'e', $_L['name_error']);
        }

        $c = ORM::for_table('sys_payers')
            ->where('name', $name)
            ->first();
        if ($c) {
            r2(U . "settings/payer", 'e', $_L['name_exist_error']);
        }

        $d = ORM::for_table('sys_payers')->create();

        $d->name = $name;

        $d->save();
        r2(U . "settings/payer", 's', $_L['added_successful']);
        break;

    case 'payer-delete':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/payer',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $id = $routes[2];
        $d = ORM::for_table('sys_payers')->find($id);
        if ($d) {
            $d->delete();

            r2(U . "settings/payer", 's', $_L['delete_successful']);
        }
        break;
    case 'pmethods':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $d = ORM::for_table('sys_pmethods')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('d', $d);

        view('pmethods');

        break;

    case 'pmethods-manage':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $id = $routes[2];
        $d = ORM::for_table('sys_pmethods')->find($id);
        if ($d) {
            $ui->assign('c', $d);
            view('pmethods-manage');
        }

        break;

    case 'pmethods-edit-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/pmethods',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $id = _post('id');
        $d = ORM::for_table('sys_pmethods')->find($id);
        if ($d) {
            $name = _post('name');
            $c = ORM::for_table('sys_pmethods')
                ->where('name', $name)
                ->first();
            if ($c) {
                r2(U . "settings/pmethods", 'e', $_L['name_exist_error']);
            }

            $oname = $d['name'];

            if ($name == '') {
                r2(U . "settings/pmethods-manage/$id", 'e', $_L['name_error']);
            } else {
                $d->name = $name;
                $d->save();

                ORM::for_table('sys_transactions')->raw_query(
                    "update sys_transactions set pmethod=:pmethod where pmethod='$oname'",
                    ['pmethod' => $name]
                );
                r2(
                    U . "settings/pmethods-manage/$id",
                    's',
                    $_L['edit_successful']
                );
            }
        }

        break;

    case 'pmethods-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/pmethods',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $name = _post('name');
        if ($name == '') {
            r2(U . "settings/pmethods", 'e', $_L['name_error']);
        }

        $c = ORM::for_table('sys_pmethods')
            ->where('name', $name)
            ->first();
        if ($c) {
            r2(U . "settings/pmethods", 'e', $_L['name_exist_error']);
        }

        $d = ORM::for_table('sys_pmethods')->create();

        $d->name = $name;

        $d->save();
        r2(U . "settings/pmethods", 's', $_L['added_successful']);
        break;

    case 'pmethods-delete':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/pmethods',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $id = $routes[2];
        $d = ORM::for_table('sys_pmethods')->find($id);
        if ($d) {
            $d->delete();

            r2(U . "settings/pmethods", 's', $_L['delete_successful']);
        }
        break;

    case 'app':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        $tblsts = ORM::for_table('sys_invoices')
            ->raw_query("show table status like 'sys_invoices'")
            ->first();
        $ai = $tblsts['Auto_increment'];
        $ui->assign('ai', $ai);

        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $timezonelist = Timezone::timezoneList();
        $ui->assign('tlist', $timezonelist);

        $version_number = '1.0.0';

        if(!empty($file_build))
        {
            $v_arr = str_split($file_build);
            $version_number = implode('.', $v_arr);
        }



        $e = ORM::for_table('sys_emailconfig')->find('1');
        $ui->assign('e', $e);

        view('app-settings', [
            'update_check' => '',
            'file_build' => $file_build,
            'version_number' => $version_number,
        ]);

        break;

    case 'zatca':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $ui->assign('content_inner', inner_contents($config['c_cache']));

        $ui->assign('zatca_endpoint_options', [
            'sandbox' => 'Sandbox',
            'simulation' => 'Simulation',
            'production' => 'Production',
        ]);

        $ui->assign('zatca_setup_mode_options', [
            'csr_otp' => 'CSR + OTP (Auto-Fetch Compliance/Production Keys)',
            'portal_keys' => 'Portal Generated Keys (Manual Paste)',
        ]);

        $ui->assign('zatca_invoice_type_options', [
            'simplified' => 'Simplified (Reporting)',
            'standard' => 'Standard (Clearance)',
        ]);

        $zatcaComplianceEnv = isset($config['zatca_environment']) ? strtolower(trim((string) $config['zatca_environment'])) : 'sandbox';
        $zatcaMissingScenarios = ZatcaPhase2::complianceChecksPassed($zatcaComplianceEnv, ZatcaPhase2::REQUIRED_COMPLIANCE_SCENARIOS);
        $ui->assign('zatca_compliance_scenarios_total', count(ZatcaPhase2::REQUIRED_COMPLIANCE_SCENARIOS));
        $ui->assign('zatca_compliance_scenarios_passed', count(ZatcaPhase2::REQUIRED_COMPLIANCE_SCENARIOS) - count($zatcaMissingScenarios));
        $ui->assign('zatca_compliance_all_passed', empty($zatcaMissingScenarios));

        view('settings-zatca');

        break;

    case 'features':
        $ui->assign('content_inner', inner_contents($config['c_cache']));

        $status_purchase_invoice = Status::where(
            'type',
            'Purchase Invoice'
        )->get();

        view('feature-settings', [
            'status_purchase_invoice' => $status_purchase_invoice,
        ]);

        break;

    case 'users':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $d = ORM::for_table('sys_users')->find_many();

        $relations = Relation::staffDepartmentsAll();

        $departments = TicketDepartment::all()
            ->keyBy('id')
            ->all();

        $ui->assign('d', $d);
        view('users', [
            'departments' => $departments,
            'relations' => $relations,
        ]);

        break;

    case 'users-add':

        $ui->assign('content_inner', inner_contents($config['c_cache']));

        $departments = TicketDepartment::orderBy('sorder', 'asc')->get();

        $roles = Role::all();
        $ui->assign('roles', $roles);

        view('users-add', [
            'departments' => $departments,
            'employee' => false,
        ]);

        break;

    case 'users-edit':
        $ui->assign('selected_navigation', 'dashboard');

        $ui->assign('languages', Localization::getLanguages());

        $id = $routes['2'];
        $d = ORM::for_table('sys_users')->find($id);
        if ($d) {
            if ($user->id != $d->id && !has_access($user->roleid, 'settings', 'edit')) {
                permissionDenied();
            }

            $ui->assign('d', $d);

            $selected_language = $d->language == '' ? $config['language'] : $d->language;

            $departments = TicketDepartment::all();

            $roles = Role::all();
            $ui->assign('roles', $roles);

            $assigned_departments = Relation::where('type', 'staff_departments')
                ->where('source_id', $id)
                ->get()
                ->keyBy('target_id')
                ->all();

            view('users-edit', [
                'selected_language' => $selected_language,
                'departments' => $departments,
                'assigned_departments' => $assigned_departments,
            ]);
        } else {
            r2(U . 'settings/users', 'e', $_L['Account_Not_Found']);
        }

        break;

    case 'users-delete':
        $id = $routes['2'];
        if ($user->id == $id) {
            r2(U . 'settings/users', 'e', 'Sorry You can\'t delete yourself');
        }
        $d = ORM::for_table('sys_users')->find($id);
        if ($d) {
            if ($user->id != $d->id && !has_access($user->roleid, 'settings', 'delete')) {
                permissionDenied();
            }

            $d->delete();
            r2(U . 'settings/users', 's', 'User deleted Successfully');
        } else {
            r2(U . 'settings/users', 'e', $_L['Account_Not_Found']);
        }

        break;

    case 'users-post':
        $username = _post('username');
        $fullname = _post('fullname');
        $password = _post('password');
        $cpassword = _post('cpassword');
        $user_type = _post('user_type');

        if (!has_access($user->roleid, 'settings', 'create')) {
            permissionDenied();
        }

        $r = Role::find($user_type);

        if ($r) {
            $role = $r->rname;
            $roleid = $user_type;
            $user_type = $r->rname;
        } else {
            $role = '';
            $roleid = 0;
            $user_type = 'Admin';
        }

        $msg = '';
        if (filter_var($username, FILTER_VALIDATE_EMAIL) == false) {
            $msg .= $_L['notice_email_as_username'] . '<br>';
        }

        if ($password !== $cpassword) {
            $msg .= 'Passwords does not match<br>';
        }
        $d = ORM::for_table('sys_users')
            ->where('username', $username)
            ->first();
        if ($d) {
            $msg .= $_L['account_already_exist'] . '<br>';
        }

        if ($msg == '') {
            $password = Password::_crypt($password);
            $d = ORM::for_table('sys_users')->create();
            $d->username = $username;
            $d->password = $password;
            $d->fullname = $fullname;
            $d->user_type = $user_type;

            $d->phonenumber = '';
            $d->last_login = date('Y-m-d H:i:s');
            $d->email = '';
            $d->creationdate = date('Y-m-d H:i:s');
            $d->pin = '';
            $d->img = '';
            $d->otp = 'No';
            $d->pin_enabled = 'No';
            $d->api = 'No';
            $d->pwresetkey = '';
            $d->keyexpire = '';
            $d->status = 'Active';
            $d->role = $role;
            $d->roleid = $roleid;

            $d->save();
            r2(U . 'settings/users', 's', $_L['account_created_successfully']);
        } else {
            r2(U . 'settings/users-add', 'e', $msg);
        }

        break;

    case 'users-edit-post':
        // verify_csrf_token(); // instead of here, we can do it globally.

        $data = $request->all();
        $data = sp_purify_data($data);

        $validation = Validation::init();
        $validator = $validation->make($data, [
            'username' => 'required|string|max:100',
            'fullname' => 'required|string|max:150',
            'phonenumber' => 'required|string|max:100',
        ]);

        $msg = '';

        if ($validator->fails()) {
            $msg = response_with_error_message($validator->errors());
        }

        $username = $data['username'];
        $fullname = $data['fullname'];
        $phonenumber = $data['phonenumber'];
        $img = _post('picture');

        $img = str_replace(APP_URL . '/', '', $img);
        $password = _post('password');
        $cpassword = _post('cpassword');

        $language = _post('user_language');

        $_SESSION['language'] = $language;

        if (filter_var($username, FILTER_VALIDATE_EMAIL) == false) {
            $msg .= 'Please use a valid Email address as Username<br>';
        }

        if ($password != '' && $password !== $cpassword) {
            $msg .= 'Passwords does not match<br>';
        }
        $id = _post('id');

        $employee = User::find($id);

        if ($employee) {
            if ($user->id != $employee->id && !has_access($user->roleid, 'settings', 'edit')) {
                permissionDenied();
            }
        } else {
            $msg .= 'Username Not Found<br>';
        }

        if ($employee->username != $username) {
            $c = ORM::for_table('sys_users')
                ->where('username', $username)
                ->first();
            if ($c) {
                $msg .= $_L['account_already_exist'] . '<br>';
            }
        }

        if (APP_STAGE == 'Demo') {
            $msg .= 'Editing User is disabled in the Demo Mode!<br>';
        }

        $user_type = _post('user_type');

        $r = Role::find($user_type);

        if ($r) {
            $role = $r->rname;
            $roleid = $user_type;
            $user_type = $r->rname;
        } else {
            $role = '';
            $roleid = 0;
            $user_type = 'Admin';
        }

        if ($msg == '') {
            $employee->username = $username;

            $employee->language = $language;
            if ($password != '') {
                $password = Password::_crypt($password);
                $employee->password = $password;
            }

            $employee->fullname = $fullname;

            $employee->fullname = $fullname;

            $employee->phonenumber = $phonenumber;

            if ($user->id != $id) {
                $employee->user_type = $user_type;
                $employee->role = $role;
                $employee->roleid = $roleid;
            }

            $employee->img = $img;

            if ($user->roleid == '0') {
                $employee->job_title = $data['job_title'];

                if (isset($data['file_link']) && $data['file_link'] != '') {
                    $employee->image = $data['file_link'];
                }

                $employee->pay_frequency = $data['pay_frequency'];

                $employee->currency = $config['home_currency'];

                $amount = 0.0;

                if (isset($data['amount']) && $data['amount'] != '') {
                    $amount = $data['amount'];
                    $amount = Finance::amount_fix($amount);
                    $employee->amount = $amount;
                }

                if (isset($data['address']) && $data['address'] != '') {
                    $employee->address_line_1 = $data['address'];
                }

                if (isset($data['email'])) {
                    $employee->email = $data['email'];
                }

                if (isset($data['phone'])) {
                    $employee->phone = $data['phone'];
                }

                if (isset($data['city'])) {
                    $employee->city = $data['city'];
                }

                if (isset($data['state'])) {
                    $employee->state = $data['state'];
                }
                if (isset($data['zip'])) {
                    $employee->zip = $data['zip'];
                }

                if (isset($data['country'])) {
                    $employee->country = $data['country'];
                }

                if (isset($data['summary'])) {
                    $employee->summary = $data['summary'];
                }
                if (isset($data['facebook'])) {
                    $employee->facebook = $data['facebook'];
                }

                if (isset($data['linkedin'])) {
                    $employee->linkedin = $data['linkedin'];
                }
                if (isset($data['twitter'])) {
                    $employee->twitter = $data['twitter'];
                }
                if (isset($data['date_hired']) && $data['date_hired'] != '') {
                    $employee->date_hired = $data['date_hired'];
                }

                if (isset($data['departments'])) {
                    $relations = Relation::where('type', 'staff_departments')
                        ->where('source_id', $employee->id)
                        ->delete();

                    $departments = $data['departments'];

                    foreach ($departments as $department) {
                        if (
                            is_numeric($department) &&
                            TicketDepartment::find($department)
                        ) {
                            $relation = new Relation();
                            $relation->type = 'staff_departments';
                            $relation->source_id = $id;
                            $relation->target_id = $department;
                            $relation->save();
                        }
                    }
                } else {
                    $relations = Relation::where('type', 'staff_departments')
                        ->where('source_id', $employee->id)
                        ->delete();
                }
            }

            $employee->save();

            r2(
                U . 'settings/users-edit/' . $id,
                's',
                'User Updated Successfully'
            );
        } else {
            r2(U . 'settings/users-edit/' . $id, 'e', $msg);
        }

        break;

    case 'app-post':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/app',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }
        $company = _post('company');

        $pdf_font = _post('pdf_font');
        if ($company == '') {
            r2(U . 'settings/app', 'e', $_L['All Fields are Required']);
        }

        if (APP_STAGE == 'Demo') {
            r2(U . 'settings/app', 'e', $_L['disabled_in_demo']);
        } else {
            $data = request()->all();

            $caddress = $data['caddress'] ?? null;

            update_option('CompanyName', $company);
            update_option('pdf_font', $pdf_font);
            update_option('caddress', $caddress);

            if (!empty($data['show_quantity_as'])) {
                update_option('show_quantity_as', $data['show_quantity_as']);
            }

            if (!empty($data['invoice_terms'])) {
                update_option('invoice_terms', $data['invoice_terms']);
            }

            if(isset($data['invoice_terms']))
            {
                update_option('invoice_terms', $data['invoice_terms']);
            }

            if (isset($data['invoice_footer_html'])) {
                update_option('invoice_footer_html', $data['invoice_footer_html']);
            }

            if (!empty($data['i_driver'])) {
                update_option('i_driver', $data['i_driver']);
            }

            if (!empty($data['default_landing_page'])) {
                update_option(
                    'default_landing_page',
                    $data['default_landing_page']
                );
            }

            if (!empty($data['dashboard'])) {
                update_option('dashboard', $data['dashboard']);
            }

            if (!empty($data['tax_system'])) {
                $tax_system = $data['tax_system'];
                update_option('tax_system', $tax_system);
                switch ($tax_system) {
                    case 'ca_quebec':
                        DB::unprepared(
                            "ALTER TABLE `sys_tax` CHANGE `rate` `rate` DECIMAL(10,3) NULL DEFAULT NULL"
                        );
                        break;
                    default:
                        DB::unprepared(
                            "ALTER TABLE `sys_tax` CHANGE `rate` `rate` DECIMAL(10,2) NULL DEFAULT NULL"
                        );
                        break;
                }
            }

            $address_format = _post('address_format');

            updateOption('address_format', $address_format, true);

            $business_location = _post('business_location');

            update_option('business_location', $business_location);

            $vat_number = _post('vat_number');

            update_option('vat_number', $vat_number);

            $invoice_default_date = _post('invoice_default_date');

            updateOption('invoice_default_date', $invoice_default_date, true);
            updateOption('label_tax_number', _post('label_tax_number'), true);

            updateOption('invoice_logo_height', _post('invoice_logo_height'), true);

            r2(U . 'settings/app', 's', $_L['Settings Saved Successfully']);
        }

        break;

    case 'zatca-post':
        if ($user['user_type'] != 'Admin') {
            r2(U . 'dashboard', 'e', $_L['You do not have permission']);
        }

        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/zatca',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }

        $data = request()->all();

        try {
            Company::migrateTableUsingPdo();
            Contact::migrateTableUsingPdo();
            Invoice::migrateTableUsingPdo();
            AppConfig::migrateZatcaOptionsUsingPdo();
        } catch (Throwable $e) {
            r2(U . 'settings/zatca', 'e', $e->getMessage());
        }

        $options = build_zatca_options_from_request($data);

        $existingConfig = (isset($config) && is_array($config)) ? $config : [];
        $options = zatca_preserve_saved_credential_values_on_empty($options, $existingConfig);

        if (zatca_has_active_credentials($existingConfig) && zatca_key_material_changed($options, $existingConfig)) {
            r2(
                U . 'settings/zatca',
                'e',
                'Key rotation safeguard: CSR/private key material was changed while active ZATCA credentials exist. To rotate keys intentionally, first clear existing compliance/production credentials, then save new CSR/private key and re-onboard with a fresh OTP.'
            );
        }

        // OTP must be one-time; do not persist it in configuration storage.
        if (isset($options['zatca_otp'])) {
            $options['zatca_otp'] = '';
        }

        // Save mode: validate only basic format constraints.
        $errors = validate_zatca_options($options, false);
        if (!empty($errors)) {
            r2(U . 'settings/zatca', 'e', implode('<br>', $errors));
        }

        $validEnvironments = ['sandbox', 'simulation', 'production'];
        if (!in_array($options['zatca_environment'], $validEnvironments)) {
            $options['zatca_environment'] = 'sandbox';
        }

        $validSetupModes = ['csr_otp', 'portal_keys'];
        if (!in_array($options['zatca_setup_mode'], $validSetupModes)) {
            $options['zatca_setup_mode'] = 'csr_otp';
        }

        $remainingOptions = [];

        foreach ($options as $setting => $value) {
            // Never overwrite saved credentials with empty form values.
            if (zatca_is_protected_credential_key($setting) && trim((string) $value) === '') {
                continue;
            }

            $saved = false;

            if (function_exists('update_option')) {
                $saved = (bool) update_option((string) $setting, (string) $value);
            }

            if (!$saved && function_exists('add_option')) {
                $saved = (bool) add_option((string) $setting, (string) $value);
            }

            if (!$saved) {
                $remainingOptions[$setting] = (string) $value;
            }
        }

        if (!empty($remainingOptions)) {
            try {
                $saved = AppConfig::saveZatcaOptionsUsingPdo($remainingOptions);
                if (!$saved) {
                    r2(U . 'settings/zatca', 'e', 'ZATCA save failed: unable to persist settings into sys_appconfig.');
                }
            } catch (Throwable $e) {
                r2(U . 'settings/zatca', 'e', 'ZATCA save failed: ' . $e->getMessage());
            }
        }

        r2(U . 'settings/zatca', 's', $_L['Settings Saved Successfully']);

        break;

    case 'zatca-verify':
        if ($user['user_type'] != 'Admin') {
            r2(U . 'dashboard', 'e', $_L['You do not have permission']);
        }

        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/zatca',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }

        $data = request()->all();
        $options = build_zatca_options_from_request($data);
        $otpValue = trim((string) $options['zatca_otp']);

        $existingConfig = (isset($config) && is_array($config)) ? $config : [];
        $options = zatca_preserve_saved_credential_values_on_empty($options, $existingConfig);
        $preRotationConfig = $existingConfig;
        $keyMaterialChanged = zatca_has_active_credentials($existingConfig) && zatca_key_material_changed($options, $existingConfig);
        $verifySetupMode = isset($options['zatca_setup_mode']) ? trim((string) $options['zatca_setup_mode']) : 'csr_otp';
        $allowKeyRotationOnVerify = ($keyMaterialChanged && $verifySetupMode === 'csr_otp' && $otpValue !== '');

        if ($keyMaterialChanged && !$allowKeyRotationOnVerify) {
            r2(
                U . 'settings/zatca',
                'e',
                'Key rotation safeguard: CSR/private key material was changed while active ZATCA credentials exist. To rotate keys intentionally, first clear existing compliance/production credentials, then run Verify with a fresh OTP.'
            );
        }

        // Keep existing credentials intact during key-rotation verify attempts.
        // They are needed for 409 recovery paths and should only be replaced after
        // successful onboarding responses are received and persisted.

        $credentialReuseConfig = $allowKeyRotationOnVerify ? $preRotationConfig : $existingConfig;

        
        // Log OTP submission for diagnostics
        $otpDebugMarker = (strlen($otpValue) > 0)
            ? (substr($otpValue, 0, 1) . '*' . substr($otpValue, -1) . ' (len=' . strlen($otpValue) . ')')
            : '(empty)';
        if (function_exists('error_log')) {
            error_log('[ZATCA Verify] OTP received from form: ' . $otpDebugMarker . ', environment: ' . (isset($options['zatca_environment']) ? $options['zatca_environment'] : 'unknown'));
        }
        
        // Verify mode: strict checks including file/PEM and required compliance fields.
        $errors = validate_zatca_options($options, true);

        if (!empty($errors)) {
            r2(U . 'settings/zatca', 'e', implode('<br>', $errors));
        }

        $remainingOptions = [];
        $optionsToPersist = $options;

        // OTP must be one-time; do not persist it in configuration storage.
        if (isset($optionsToPersist['zatca_otp'])) {
            $optionsToPersist['zatca_otp'] = '';
        }

        foreach ($optionsToPersist as $setting => $value) {
            // Never overwrite saved credentials with empty form values unless key-rotation explicitly requested.
            if (
                zatca_is_protected_credential_key($setting)
                && trim((string) $value) === ''
                && !$allowKeyRotationOnVerify
            ) {
                continue;
            }

            $saved = false;

            if (function_exists('update_option')) {
                $saved = (bool) update_option((string) $setting, (string) $value);
            }

            if (!$saved && function_exists('add_option')) {
                $saved = (bool) add_option((string) $setting, (string) $value);
            }

            if (!$saved) {
                $remainingOptions[$setting] = (string) $value;
            }
        }

        if (!empty($remainingOptions)) {
            try {
                $saved = AppConfig::saveZatcaOptionsUsingPdo($remainingOptions);
                if (!$saved) {
                    r2(U . 'settings/zatca', 'e', 'ZATCA verify save failed: unable to persist settings into sys_appconfig.');
                }
            } catch (Throwable $e) {
                r2(U . 'settings/zatca', 'e', 'ZATCA verify save failed: ' . $e->getMessage());
            }
        }

        $successMessages = ['ZATCA configuration verified successfully and keys were saved.'];
        if ($allowKeyRotationOnVerify) {
            $successMessages[] = 'Key rotation mode enabled: existing credentials are preserved until new onboarding succeeds.';
        }
        $baseConfig = $existingConfig;

        $csrValue = trim((string) $options['zatca_csr_content']);
        $existingComplianceToken = trim((string) $options['zatca_binary_security_token']);
        $existingComplianceSecret = trim((string) $options['zatca_secret']);
        $existingComplianceRequestId = trim((string) $options['zatca_compliance_request_id']);

        if ($existingComplianceToken === '' && isset($credentialReuseConfig['zatca_binary_security_token'])) {
            $existingComplianceToken = trim((string) $credentialReuseConfig['zatca_binary_security_token']);
        }
        if ($existingComplianceToken === '' && isset($credentialReuseConfig['zatca_compliance_csid'])) {
            $existingComplianceToken = trim((string) $credentialReuseConfig['zatca_compliance_csid']);
        }
        if ($existingComplianceSecret === '' && isset($credentialReuseConfig['zatca_secret'])) {
            $existingComplianceSecret = trim((string) $credentialReuseConfig['zatca_secret']);
        }
        if ($existingComplianceSecret === '' && isset($credentialReuseConfig['zatca_compliance_secret'])) {
            $existingComplianceSecret = trim((string) $credentialReuseConfig['zatca_compliance_secret']);
        }
        if ($existingComplianceRequestId === '' && isset($credentialReuseConfig['zatca_compliance_request_id'])) {
            $existingComplianceRequestId = trim((string) $credentialReuseConfig['zatca_compliance_request_id']);
        }
        if ($existingComplianceRequestId === '' && isset($credentialReuseConfig['zatca_request_id'])) {
            $existingComplianceRequestId = trim((string) $credentialReuseConfig['zatca_request_id']);
        }

        if (($existingComplianceToken === '' || $existingComplianceSecret === '') && isset($credentialReuseConfig['zatca_compliance_last_response'])) {
            $restoredFromResponse = zatca_extract_compliance_credentials_from_response((string) $credentialReuseConfig['zatca_compliance_last_response']);
            if ($existingComplianceToken === '') {
                $existingComplianceToken = trim((string) $restoredFromResponse['binary_security_token']);
            }
            if ($existingComplianceSecret === '') {
                $existingComplianceSecret = trim((string) $restoredFromResponse['secret']);
            }
            if ($existingComplianceRequestId === '') {
                $existingComplianceRequestId = trim((string) $restoredFromResponse['request_id']);
            }
        }
        $setupMode = isset($options['zatca_setup_mode']) ? trim((string) $options['zatca_setup_mode']) : 'csr_otp';
        if ($setupMode !== 'csr_otp' && $setupMode !== 'portal_keys') {
            $setupMode = 'csr_otp';
        }

        $hasExistingComplianceCredentials = ($existingComplianceToken !== '' && $existingComplianceSecret !== '');
        $existingProductionToken = isset($options['zatca_production_binary_security_token'])
            ? trim((string) $options['zatca_production_binary_security_token'])
            : '';
        $existingProductionSecret = isset($options['zatca_production_secret'])
            ? trim((string) $options['zatca_production_secret'])
            : '';
        if ($existingProductionToken === '' && isset($credentialReuseConfig['zatca_production_binary_security_token'])) {
            $existingProductionToken = trim((string) $credentialReuseConfig['zatca_production_binary_security_token']);
        }
        if ($existingProductionSecret === '' && isset($credentialReuseConfig['zatca_production_secret'])) {
            $existingProductionSecret = trim((string) $credentialReuseConfig['zatca_production_secret']);
        }
        $hasExistingProductionCredentials = ($existingProductionToken !== '' && $existingProductionSecret !== '');
        $shouldFetchKeys = ($otpValue !== '' && $csrValue !== '');

        if ($setupMode === 'portal_keys') {
            $successMessages[] = 'Portal Keys flow selected: auto-fetch was skipped. Manual credentials were validated and saved.';
            r2(U . 'settings/zatca', 's', implode('<br>', $successMessages));
            break;
        }

        if ($shouldFetchKeys) {
            // STEP 1: Fetch/refresh Compliance CSID only.
            $runtimeConfig = zatca_merge_runtime_config_preserve_non_empty($baseConfig, $options);

            if ($hasExistingComplianceCredentials) {
                $successMessages[] = 'Existing compliance credentials detected, but a fresh OTP and CSR were provided, so compliance fetch will be refreshed.';
            }

            // Check CSR elliptic curve compatibility before submitting to ZATCA.
            // ZATCA requires secp256k1 (confirmed against ZATCA's own bundled e-invoicing SDK,
            // which hardcodes ec-secp256k1-priv-key.pem as the required key) - NOT prime256v1/P-256.
            $csrCurve = zatca_detect_csr_curve($csrValue);
            if ($csrCurve === 'prime256v1') {
                $errorMessage = '<strong>❌ CSR Elliptic Curve Mismatch:</strong> Your CSR was generated with the <strong>prime256v1 (P-256)</strong> curve, which is NOT supported by ZATCA.<br><br>'
                    . '<strong>ZATCA requires the secp256k1 curve.</strong><br><br>'
                    . '<strong>How to fix:</strong><br>'
                    . '1. Run the CSR generator script: <code>tools/generate-zatca-csr-openssl.bat</code><br>'
                    . '2. When asked for Elliptic Curve, select: <strong>secp256k1</strong><br>'
                    . '3. Replace your current CSR and private key files with the newly generated ones<br>'
                    . '4. Return here and try again with a fresh OTP<br><br>'
                    . '<strong>Why this happened:</strong> Your CSR was manually generated or copied with the wrong curve. The app now detects this and prevents submission to avoid a 400 error from ZATCA.';
                r2(U . 'settings/zatca', 'e', $errorMessage);
                break;
            } elseif ($csrCurve === 'unknown') {
                $successMessages[] = '⚠ Warning: CSR elliptic curve could not be detected. ZATCA may reject it if it does not use secp256k1.';
            } elseif ($csrCurve === 'secp256k1') {
                $successMessages[] = '✓ CSR uses ZATCA-compatible secp256k1 elliptic curve.';
            }

            $compliance = ZatcaPhase2::requestComplianceCsid($runtimeConfig, [
                'otp' => $otpValue,
                'csr' => $csrValue,
            ]);

            if (!$compliance['success']) {
                $statusCode = isset($compliance['status_code']) ? (int) $compliance['status_code'] : 0;
                $responseBody = isset($compliance['response_body']) ? (string) $compliance['response_body'] : '';
                $responseBodyLower = strtolower($responseBody);
                $alreadyGenerated = (
                    ($statusCode === 409 || $statusCode === 400)
                    && (
                        strpos($responseBodyLower, 'submitted/generated before') !== false
                        || strpos($responseBodyLower, 'already generated') !== false
                        || strpos($responseBodyLower, 'already submitted') !== false
                        || strpos($responseBodyLower, 'already exists') !== false
                        || strpos($responseBodyLower, 'already onboarded') !== false
                    )
                );

                if ($alreadyGenerated && $hasExistingComplianceCredentials) {
                    $successMessages[] = 'Compliance CSID already exists at ZATCA (409). Reusing previously saved compliance credentials.';
                    $compliance = [
                        'success' => true,
                        'status_code' => 200,
                        'binary_security_token' => $existingComplianceToken,
                        'secret' => $existingComplianceSecret,
                        'request_id' => $existingComplianceRequestId,
                        'response_body' => $responseBody,
                    ];
                } elseif ($alreadyGenerated && !$hasExistingComplianceCredentials) {
                    if ($hasExistingProductionCredentials) {
                        $successMessages[] = 'Compliance returned 409 and local compliance credentials are missing, but production credentials are already saved. You can continue with invoicing.';
                        r2(U . 'settings/zatca', 's', implode('<br>', $successMessages));
                        break;
                    }

                    $errorMessage = 'ZATCA returned 409: Compliance transaction was already generated before, but no saved compliance credentials were found locally.'
                        . '<br><br>Do not keep regenerating CSR/PEM for this same onboarding device.'
                        . '<br><br><strong>Important:</strong> If your portal account can only generate OTP and cannot manage devices, OTP alone cannot recover old compliance credentials after a 409.'
                        . '<br><br><strong>Recovery options:</strong>'
                        . '<br>1. Restore previously saved compliance credentials (token/secret/request ID) from backup or another environment.'
                        . '<br>2. If production credentials are already saved, skip Step 1 and continue using invoicing directly.'
                        . '<br>3. Ask a portal admin account (with Device Management access) to reset/revoke the onboarding device, then onboard fresh.';
                    if ($responseBody !== '') {
                        $errorMessage .= '<br><pre>' . htmlentities($responseBody) . '</pre>';
                    }
                    r2(U . 'settings/zatca', 'e', $errorMessage);
                    break;
                }
            }

            if (!$compliance['success']) {
                $errorMessage = 'ZATCA verify failed during Compliance CSID fetch: ' . $compliance['message'];
                if (!empty($compliance['response_body'])) {
                    $errorMessage .= '<br><pre>' . htmlentities((string) $compliance['response_body']) . '</pre>';
                }
                r2(U . 'settings/zatca', 'e', $errorMessage);
                break;
            }

            $successMessages[] = 'Compliance CSID fetched and saved.';
            $successMessages[] = 'Step 1 completed. Next: run Step 2 (Compliance Invoice Check), then Step 3 (Production CSID).';

            $runtimeConfig = array_merge($runtimeConfig, [
                'zatca_binary_security_token' => isset($compliance['binary_security_token']) ? (string) $compliance['binary_security_token'] : '',
                'zatca_secret' => isset($compliance['secret']) ? (string) $compliance['secret'] : '',
                'zatca_compliance_request_id' => isset($compliance['request_id']) ? (string) $compliance['request_id'] : '',
            ]);

            $keysToDatabase = [
                'zatca_binary_security_token' => (string) $runtimeConfig['zatca_binary_security_token'],
                'zatca_secret' => (string) $runtimeConfig['zatca_secret'],
                'zatca_compliance_request_id' => (string) $runtimeConfig['zatca_compliance_request_id'],
            ];

            try {
                $saved = AppConfig::saveZatcaOptionsUsingPdo($keysToDatabase);
                if (!$saved) {
                    $successMessages[] = '⚠ Warning: Compliance credentials were fetched but could not be saved to database.';
                }
            } catch (Throwable $e) {
                $successMessages[] = '⚠ Warning: Compliance save failed: ' . $e->getMessage();
            }
        } else {
            // STEP 2: Fetch Production CSID using already saved Compliance credentials.
            $runtimeConfig = zatca_merge_runtime_config_preserve_non_empty($baseConfig, $options);

            $runtimeConfig = array_merge($runtimeConfig, [
                'zatca_binary_security_token' => $existingComplianceToken,
                'zatca_secret' => $existingComplianceSecret,
                'zatca_compliance_request_id' => $existingComplianceRequestId,
            ]);

            $activeEnvironment = isset($runtimeConfig['zatca_environment'])
                ? strtolower(trim((string) $runtimeConfig['zatca_environment']))
                : 'sandbox';

            // Step 2 onboarding is valid in sandbox/simulation (developer-portal/simulation endpoints)
            // and in production (core endpoint). Do not gate by environment here.
            // Recover missing compliance credentials from persisted storage before deciding Step 2 is blocked.
            $fallback = zatca_load_options_from_appconfig([
                'zatca_binary_security_token',
                'zatca_compliance_csid',
                'zatca_secret',
                'zatca_compliance_secret',
                'zatca_compliance_request_id',
                'zatca_request_id',
                'zatca_production_binary_security_token',
                'zatca_production_secret',
            ]);

            if ($existingComplianceToken === '') {
                $existingComplianceToken = trim((string) $fallback['zatca_binary_security_token']);
                if ($existingComplianceToken === '') {
                    $existingComplianceToken = trim((string) $fallback['zatca_compliance_csid']);
                }
            }

            if ($existingComplianceSecret === '') {
                $existingComplianceSecret = trim((string) $fallback['zatca_secret']);
                if ($existingComplianceSecret === '') {
                    $existingComplianceSecret = trim((string) $fallback['zatca_compliance_secret']);
                }
            }

            if ($existingComplianceRequestId === '') {
                $existingComplianceRequestId = trim((string) $fallback['zatca_compliance_request_id']);
                if ($existingComplianceRequestId === '') {
                    $existingComplianceRequestId = trim((string) $fallback['zatca_request_id']);
                }
            }

            if (function_exists('get_option')) {
                if ($existingComplianceToken === '') {
                    $existingComplianceToken = trim((string) get_option('zatca_binary_security_token'));
                    if ($existingComplianceToken === '') {
                        $existingComplianceToken = trim((string) get_option('zatca_compliance_csid'));
                    }
                }

                if ($existingComplianceSecret === '') {
                    $existingComplianceSecret = trim((string) get_option('zatca_secret'));
                    if ($existingComplianceSecret === '') {
                        $existingComplianceSecret = trim((string) get_option('zatca_compliance_secret'));
                    }
                }

                if ($existingComplianceRequestId === '') {
                    $existingComplianceRequestId = trim((string) get_option('zatca_compliance_request_id'));
                    if ($existingComplianceRequestId === '') {
                        $existingComplianceRequestId = trim((string) get_option('zatca_request_id'));
                    }
                }
            }

            // Keep runtime config in sync with recovered values.
            if ($existingComplianceToken !== '') {
                $runtimeConfig['zatca_binary_security_token'] = $existingComplianceToken;
            }
            if ($existingComplianceSecret !== '') {
                $runtimeConfig['zatca_secret'] = $existingComplianceSecret;
            }
            if ($existingComplianceRequestId !== '') {
                $runtimeConfig['zatca_compliance_request_id'] = $existingComplianceRequestId;
            }

            $existingProductionToken = isset($runtimeConfig['zatca_production_binary_security_token'])
                ? trim((string) $runtimeConfig['zatca_production_binary_security_token'])
                : '';
            $existingProductionSecret = isset($runtimeConfig['zatca_production_secret'])
                ? trim((string) $runtimeConfig['zatca_production_secret'])
                : '';
            if ($existingProductionToken === '' && isset($credentialReuseConfig['zatca_production_binary_security_token'])) {
                $existingProductionToken = trim((string) $credentialReuseConfig['zatca_production_binary_security_token']);
            }
            if ($existingProductionSecret === '' && isset($credentialReuseConfig['zatca_production_secret'])) {
                $existingProductionSecret = trim((string) $credentialReuseConfig['zatca_production_secret']);
            }

            if ($existingProductionToken === '') {
                $existingProductionToken = trim((string) $fallback['zatca_production_binary_security_token']);
            }
            if ($existingProductionSecret === '') {
                $existingProductionSecret = trim((string) $fallback['zatca_production_secret']);
            }

            if (function_exists('get_option')) {
                if ($existingProductionToken === '') {
                    $existingProductionToken = trim((string) get_option('zatca_production_binary_security_token'));
                }
                if ($existingProductionSecret === '') {
                    $existingProductionSecret = trim((string) get_option('zatca_production_secret'));
                }
            }

            $autoStep2DiagPayload = json_encode([
                'step' => 'verify_auto_step2_diagnostics',
                'time' => date('c'),
                'has_compliance_token' => $existingComplianceToken !== '',
                'has_compliance_secret' => $existingComplianceSecret !== '',
                'has_compliance_request_id' => $existingComplianceRequestId !== '',
                'has_production_token' => $existingProductionToken !== '',
                'has_production_secret' => $existingProductionSecret !== '',
                'environment' => $activeEnvironment,
            ]);
            if ($autoStep2DiagPayload !== false) {
                zatca_persist_option_best_effort('zatca_step2_last_diagnostics', (string) $autoStep2DiagPayload);
                zatca_persist_appconfig_best_effort(['zatca_step2_last_diagnostics' => (string) $autoStep2DiagPayload]);
            }

            $autoMissingComplianceScenarios = ZatcaPhase2::complianceChecksPassed(
                $activeEnvironment,
                ZatcaPhase2::REQUIRED_COMPLIANCE_SCENARIOS
            );

            if ($existingProductionToken !== '' && $existingProductionSecret !== '') {
                $successMessages[] = 'Step 3 skipped: Production credentials are already saved.';
            } elseif ($existingComplianceToken === '' || $existingComplianceSecret === '' || $existingComplianceRequestId === '') {
                $successMessages[] = 'Step 3 blocked: Compliance credentials/request ID are missing. Restore saved compliance credentials or run Step 1 with a recoverable device.';
            } elseif (!empty($autoMissingComplianceScenarios)) {
                $successMessages[] = 'Step 3 blocked: complete all 6 compliance checks (Step 2) first. Missing: ' . implode(', ', $autoMissingComplianceScenarios) . '.';
            } else {
                $production = ZatcaPhase2::requestProductionCsid($runtimeConfig);

                if (!empty($production['success'])) {
                    $successMessages[] = 'Production CSID fetched and saved.';
                    $runtimeConfig = array_merge($runtimeConfig, [
                        'zatca_production_binary_security_token' => isset($production['binary_security_token']) ? (string) $production['binary_security_token'] : '',
                        'zatca_production_secret' => isset($production['secret']) ? (string) $production['secret'] : '',
                        'zatca_production_csid' => isset($production['binary_security_token']) ? (string) $production['binary_security_token'] : '',
                    ]);

                    $prodKeys = [
                        'zatca_production_binary_security_token' => (string) $runtimeConfig['zatca_production_binary_security_token'],
                        'zatca_production_secret' => (string) $runtimeConfig['zatca_production_secret'],
                        'zatca_production_csid' => (string) $runtimeConfig['zatca_production_csid'],
                    ];

                    try {
                        $saved = AppConfig::saveZatcaOptionsUsingPdo($prodKeys);
                        if (!$saved) {
                            $successMessages[] = '⚠ Warning: Production credentials were fetched but could not be saved to database.';
                        }
                    } catch (Throwable $e) {
                        $successMessages[] = '⚠ Warning: Production save failed: ' . $e->getMessage();
                    }
                } else {
                    $productionMessage = isset($production['message']) ? (string) $production['message'] : 'Production CSID request failed.';
                    $successMessages[] = 'Step 3 failed: ' . $productionMessage;
                }
            }
        }

        r2(U . 'settings/zatca', 's', implode('<br>', $successMessages));

        break;

    case 'zatca-compliance-check':
        if ($user['user_type'] != 'Admin') {
            r2(U . 'dashboard', 'e', $_L['You do not have permission']);
        }

        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/zatca',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }

        $data = request()->all();
        $options = build_zatca_options_from_request($data);
        $runtimeConfig = zatca_merge_runtime_config_preserve_non_empty(
            (isset($config) && is_array($config)) ? $config : [],
            $options
        );

        $complianceToken = isset($runtimeConfig['zatca_binary_security_token'])
            ? trim((string) $runtimeConfig['zatca_binary_security_token'])
            : '';
        $complianceSecret = isset($runtimeConfig['zatca_secret'])
            ? trim((string) $runtimeConfig['zatca_secret'])
            : '';
        $complianceRequestId = isset($runtimeConfig['zatca_compliance_request_id'])
            ? trim((string) $runtimeConfig['zatca_compliance_request_id'])
            : '';

        if ($complianceToken === '' && isset($runtimeConfig['zatca_compliance_csid'])) {
            $complianceToken = trim((string) $runtimeConfig['zatca_compliance_csid']);
            $runtimeConfig['zatca_binary_security_token'] = $complianceToken;
        }

        if ($complianceSecret === '' && isset($runtimeConfig['zatca_compliance_secret'])) {
            $complianceSecret = trim((string) $runtimeConfig['zatca_compliance_secret']);
            $runtimeConfig['zatca_secret'] = $complianceSecret;
        }

        if ($complianceRequestId === '' && isset($runtimeConfig['zatca_request_id'])) {
            $complianceRequestId = trim((string) $runtimeConfig['zatca_request_id']);
            $runtimeConfig['zatca_compliance_request_id'] = $complianceRequestId;
        }

        if (
            ($complianceToken === '' || $complianceSecret === '' || $complianceRequestId === '')
            && isset($runtimeConfig['zatca_compliance_last_response'])
            && trim((string) $runtimeConfig['zatca_compliance_last_response']) !== ''
        ) {
            $restored = zatca_extract_compliance_credentials_from_response((string) $runtimeConfig['zatca_compliance_last_response']);

            if ($complianceToken === '' && !empty($restored['binary_security_token'])) {
                $complianceToken = trim((string) $restored['binary_security_token']);
                $runtimeConfig['zatca_binary_security_token'] = $complianceToken;
            }

            if ($complianceSecret === '' && !empty($restored['secret'])) {
                $complianceSecret = trim((string) $restored['secret']);
                $runtimeConfig['zatca_secret'] = $complianceSecret;
            }

            if ($complianceRequestId === '' && !empty($restored['request_id'])) {
                $complianceRequestId = trim((string) $restored['request_id']);
                $runtimeConfig['zatca_compliance_request_id'] = $complianceRequestId;
            }
        }

        if ($complianceToken === '' || $complianceSecret === '' || $complianceRequestId === '') {
            $dbFallback = zatca_load_options_from_appconfig([
                'zatca_binary_security_token',
                'zatca_compliance_csid',
                'zatca_secret',
                'zatca_compliance_secret',
                'zatca_compliance_request_id',
                'zatca_request_id',
                'zatca_compliance_last_response',
            ]);

            if ($complianceToken === '') {
                $complianceToken = trim((string) $dbFallback['zatca_binary_security_token']);
                if ($complianceToken === '') {
                    $complianceToken = trim((string) $dbFallback['zatca_compliance_csid']);
                }
                if ($complianceToken !== '') {
                    $runtimeConfig['zatca_binary_security_token'] = $complianceToken;
                }
            }

            if ($complianceSecret === '') {
                $complianceSecret = trim((string) $dbFallback['zatca_secret']);
                if ($complianceSecret === '') {
                    $complianceSecret = trim((string) $dbFallback['zatca_compliance_secret']);
                }
                if ($complianceSecret !== '') {
                    $runtimeConfig['zatca_secret'] = $complianceSecret;
                }
            }

            if ($complianceRequestId === '') {
                $complianceRequestId = trim((string) $dbFallback['zatca_compliance_request_id']);
                if ($complianceRequestId === '') {
                    $complianceRequestId = trim((string) $dbFallback['zatca_request_id']);
                }
                if ($complianceRequestId !== '') {
                    $runtimeConfig['zatca_compliance_request_id'] = $complianceRequestId;
                }
            }

            if ((!isset($runtimeConfig['zatca_compliance_last_response']) || trim((string) $runtimeConfig['zatca_compliance_last_response']) === '')
                && trim((string) $dbFallback['zatca_compliance_last_response']) !== '') {
                $runtimeConfig['zatca_compliance_last_response'] = (string) $dbFallback['zatca_compliance_last_response'];
            }
        }

        if ($complianceToken === '' || $complianceSecret === '') {
            r2(
                U . 'settings/zatca',
                'e',
                'Compliance invoice check is blocked: missing Compliance CSID credentials. Run Step 1 (Verify with OTP + CSR) first.'
            );
        }

        $result = ZatcaPhase2::runComplianceInvoices($runtimeConfig);

        if (!empty($result['success'])) {
            $message = 'Compliance invoice check completed successfully.';
            if (!empty($result['message'])) {
                $message .= '<br>' . htmlentities((string) $result['message']);
            }
            r2(U . 'settings/zatca', 's', $message);
        }

        $errorMessage = 'Compliance invoice check failed.';
        if (!empty($result['message'])) {
            $errorMessage .= '<br>' . htmlentities((string) $result['message']);
        }
        if (!empty($result['response_body'])) {
            $errorMessage .= '<br><pre>' . htmlentities((string) $result['response_body']) . '</pre>';
        }

        r2(U . 'settings/zatca', 'e', $errorMessage);

        break;

    case 'zatca-production-step2':
        if ($user['user_type'] != 'Admin') {
            r2(U . 'dashboard', 'e', $_L['You do not have permission']);
        }

        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/zatca',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }

        $data = request()->all();
        $options = build_zatca_options_from_request($data);
        $baseConfig = (isset($config) && is_array($config)) ? $config : [];
        $runtimeConfig = zatca_merge_runtime_config_preserve_non_empty($baseConfig, $options);

        $existingProductionToken = isset($runtimeConfig['zatca_production_binary_security_token'])
            ? trim((string) $runtimeConfig['zatca_production_binary_security_token'])
            : '';
        $existingProductionSecret = isset($runtimeConfig['zatca_production_secret'])
            ? trim((string) $runtimeConfig['zatca_production_secret'])
            : '';

        $activeEnvironment = isset($runtimeConfig['zatca_environment'])
            ? strtolower(trim((string) $runtimeConfig['zatca_environment']))
            : 'sandbox';

        $complianceToken = isset($runtimeConfig['zatca_binary_security_token'])
            ? trim((string) $runtimeConfig['zatca_binary_security_token'])
            : '';
        $complianceSecret = isset($runtimeConfig['zatca_secret'])
            ? trim((string) $runtimeConfig['zatca_secret'])
            : '';
        $complianceRequestId = isset($runtimeConfig['zatca_compliance_request_id'])
            ? trim((string) $runtimeConfig['zatca_compliance_request_id'])
            : '';

        if ($complianceToken === '' && isset($runtimeConfig['zatca_compliance_csid'])) {
            $complianceToken = trim((string) $runtimeConfig['zatca_compliance_csid']);
            $runtimeConfig['zatca_binary_security_token'] = $complianceToken;
        }

        if ($complianceSecret === '' && isset($runtimeConfig['zatca_compliance_secret'])) {
            $complianceSecret = trim((string) $runtimeConfig['zatca_compliance_secret']);
            $runtimeConfig['zatca_secret'] = $complianceSecret;
        }

        if ($complianceRequestId === '' && isset($runtimeConfig['zatca_request_id'])) {
            $complianceRequestId = trim((string) $runtimeConfig['zatca_request_id']);
            $runtimeConfig['zatca_compliance_request_id'] = $complianceRequestId;
        }

        // Recover from persisted options if runtime payload is incomplete.
        if (function_exists('get_option')) {
            if ($complianceToken === '') {
                $complianceToken = trim((string) get_option('zatca_binary_security_token'));
                if ($complianceToken === '') {
                    $complianceToken = trim((string) get_option('zatca_compliance_csid'));
                }
                if ($complianceToken !== '') {
                    $runtimeConfig['zatca_binary_security_token'] = $complianceToken;
                }
            }

            if ($complianceSecret === '') {
                $complianceSecret = trim((string) get_option('zatca_secret'));
                if ($complianceSecret === '') {
                    $complianceSecret = trim((string) get_option('zatca_compliance_secret'));
                }
                if ($complianceSecret !== '') {
                    $runtimeConfig['zatca_secret'] = $complianceSecret;
                }
            }

            if ($complianceRequestId === '') {
                $complianceRequestId = trim((string) get_option('zatca_compliance_request_id'));
                if ($complianceRequestId === '') {
                    $complianceRequestId = trim((string) get_option('zatca_request_id'));
                }
                if ($complianceRequestId !== '') {
                    $runtimeConfig['zatca_compliance_request_id'] = $complianceRequestId;
                }
            }

            if (!isset($runtimeConfig['zatca_compliance_last_response']) || trim((string) $runtimeConfig['zatca_compliance_last_response']) === '') {
                $persistedComplianceResponse = trim((string) get_option('zatca_compliance_last_response'));
                if ($persistedComplianceResponse !== '') {
                    $runtimeConfig['zatca_compliance_last_response'] = $persistedComplianceResponse;
                }
            }
        }

        if (
            ($complianceToken === '' || $complianceSecret === '' || $complianceRequestId === '')
            && isset($runtimeConfig['zatca_compliance_last_response'])
            && trim((string) $runtimeConfig['zatca_compliance_last_response']) !== ''
        ) {
            $restored = zatca_extract_compliance_credentials_from_response((string) $runtimeConfig['zatca_compliance_last_response']);

            if ($complianceToken === '' && !empty($restored['binary_security_token'])) {
                $complianceToken = trim((string) $restored['binary_security_token']);
                $runtimeConfig['zatca_binary_security_token'] = $complianceToken;
            }

            if ($complianceSecret === '' && !empty($restored['secret'])) {
                $complianceSecret = trim((string) $restored['secret']);
                $runtimeConfig['zatca_secret'] = $complianceSecret;
            }

            if ($complianceRequestId === '' && !empty($restored['request_id'])) {
                $complianceRequestId = trim((string) $restored['request_id']);
                $runtimeConfig['zatca_compliance_request_id'] = $complianceRequestId;
            }
        }

        if ($complianceToken === '' || $complianceSecret === '' || $complianceRequestId === '') {
            $dbFallback = zatca_load_options_from_appconfig([
                'zatca_binary_security_token',
                'zatca_compliance_csid',
                'zatca_secret',
                'zatca_compliance_secret',
                'zatca_compliance_request_id',
                'zatca_request_id',
                'zatca_compliance_last_response',
                'zatca_production_binary_security_token',
                'zatca_production_secret',
            ]);

            if ($complianceToken === '') {
                $complianceToken = trim((string) $dbFallback['zatca_binary_security_token']);
                if ($complianceToken === '') {
                    $complianceToken = trim((string) $dbFallback['zatca_compliance_csid']);
                }
                if ($complianceToken !== '') {
                    $runtimeConfig['zatca_binary_security_token'] = $complianceToken;
                }
            }

            if ($complianceSecret === '') {
                $complianceSecret = trim((string) $dbFallback['zatca_secret']);
                if ($complianceSecret === '') {
                    $complianceSecret = trim((string) $dbFallback['zatca_compliance_secret']);
                }
                if ($complianceSecret !== '') {
                    $runtimeConfig['zatca_secret'] = $complianceSecret;
                }
            }

            if ($complianceRequestId === '') {
                $complianceRequestId = trim((string) $dbFallback['zatca_compliance_request_id']);
                if ($complianceRequestId === '') {
                    $complianceRequestId = trim((string) $dbFallback['zatca_request_id']);
                }
                if ($complianceRequestId !== '') {
                    $runtimeConfig['zatca_compliance_request_id'] = $complianceRequestId;
                }
            }

            if ((!isset($runtimeConfig['zatca_compliance_last_response']) || trim((string) $runtimeConfig['zatca_compliance_last_response']) === '')
                && trim((string) $dbFallback['zatca_compliance_last_response']) !== '') {
                $runtimeConfig['zatca_compliance_last_response'] = (string) $dbFallback['zatca_compliance_last_response'];
            }

            if ($existingProductionToken === '') {
                $existingProductionToken = trim((string) $dbFallback['zatca_production_binary_security_token']);
            }
            if ($existingProductionSecret === '') {
                $existingProductionSecret = trim((string) $dbFallback['zatca_production_secret']);
            }
        }

        if (function_exists('get_option')) {
            if ($existingProductionToken === '') {
                $existingProductionToken = trim((string) get_option('zatca_production_binary_security_token'));
            }
            if ($existingProductionSecret === '') {
                $existingProductionSecret = trim((string) get_option('zatca_production_secret'));
            }
        }

        $step2DiagPayload = json_encode([
            'step' => 'step2_diagnostics',
            'time' => date('c'),
            'has_token' => $complianceToken !== '',
            'has_secret' => $complianceSecret !== '',
            'has_request_id' => $complianceRequestId !== '',
            'has_production_token' => $existingProductionToken !== '',
            'has_production_secret' => $existingProductionSecret !== '',
            'environment' => $activeEnvironment,
        ]);
        if ($step2DiagPayload !== false) {
            zatca_persist_option_best_effort('zatca_step2_last_diagnostics', (string) $step2DiagPayload);
            zatca_persist_appconfig_best_effort(['zatca_step2_last_diagnostics' => (string) $step2DiagPayload]);
        }

        if ($existingProductionToken !== '' && $existingProductionSecret !== '') {
            r2(
                U . 'settings/zatca',
                's',
                'Step 3 skipped: Production credentials are already saved. You can continue with invoicing.'
            );
            break;
        }

        if ($complianceToken === '' || $complianceSecret === '' || $complianceRequestId === '') {
            $blockedPayload = json_encode([
                'step' => 'step2_blocked',
                'reason' => 'missing_compliance_credentials_or_request_id',
                'has_token' => $complianceToken !== '',
                'has_secret' => $complianceSecret !== '',
                'has_request_id' => $complianceRequestId !== '',
                'base_has_token' => isset($baseConfig['zatca_binary_security_token']) && trim((string) $baseConfig['zatca_binary_security_token']) !== '',
                'base_has_secret' => isset($baseConfig['zatca_secret']) && trim((string) $baseConfig['zatca_secret']) !== '',
                'base_has_request_id' => isset($baseConfig['zatca_compliance_request_id']) && trim((string) $baseConfig['zatca_compliance_request_id']) !== '',
                'time' => date('c'),
            ]);

            $persisted = zatca_persist_option_best_effort('zatca_production_last_response', (string) $blockedPayload);
            $persistedDb = zatca_persist_appconfig_best_effort([
                'zatca_production_last_response' => (string) $blockedPayload,
                'zatca_step2_last_diagnostics' => (string) $blockedPayload,
            ]);
            if (!$persisted && function_exists('error_log')) {
                error_log('[ZATCA Step2] Failed to persist blocked reason via all known storage paths.');
            }
            if (!$persistedDb && function_exists('error_log')) {
                error_log('[ZATCA Step2] Failed to persist blocked reason into sys_appconfig.');
            }

            if (function_exists('error_log')) {
                error_log('[ZATCA Step2] blocked - token=' . ($complianceToken !== '' ? 'yes' : 'no')
                    . ', secret=' . ($complianceSecret !== '' ? 'yes' : 'no')
                    . ', request_id=' . ($complianceRequestId !== '' ? 'yes' : 'no'));
            }

            r2(
                U . 'settings/zatca',
                'e',
                'Step 3 blocked: Compliance CSID credentials/request ID are missing. Restore saved compliance credentials or run Step 1 with a recoverable device.'
            );
            break;
        }

        // Gate Production CSID on all 6 required compliance scenarios having passed - ZATCA's
        // real sequence is Compliance CSID -> 6 compliance checks -> Production CSID, and
        // requesting Production CSID before that leaves the device unverified.
        $missingComplianceScenarios = ZatcaPhase2::complianceChecksPassed(
            $activeEnvironment,
            ZatcaPhase2::REQUIRED_COMPLIANCE_SCENARIOS
        );
        if (!empty($missingComplianceScenarios)) {
            r2(
                U . 'settings/zatca',
                'e',
                'Step 3 blocked: complete all 6 compliance checks (Step 2) first. Missing: ' . implode(', ', $missingComplianceScenarios) . '.'
            );
            break;
        }

        $production = ZatcaPhase2::requestProductionCsid($runtimeConfig);
        if (!empty($production['success'])) {
            $prodToken = isset($production['binary_security_token'])
                ? (string) $production['binary_security_token']
                : '';

            $prodKeys = [
                'zatca_production_binary_security_token' => $prodToken,
                'zatca_production_secret' => isset($production['secret']) ? (string) $production['secret'] : '',
                'zatca_production_csid' => $prodToken,
            ];

            try {
                $saved = AppConfig::saveZatcaOptionsUsingPdo($prodKeys);
                if (!$saved) {
                    r2(U . 'settings/zatca', 'e', 'Production CSID fetched but failed to save credentials.');
                    break;
                }
            } catch (Throwable $e) {
                r2(U . 'settings/zatca', 'e', 'Production CSID fetched but save failed: ' . $e->getMessage());
                break;
            }

            r2(U . 'settings/zatca', 's', 'Step 3 completed: Production CSID fetched and saved.');
            break;
        }

        $productionMessage = isset($production['message']) ? (string) $production['message'] : 'Production CSID request failed.';
        if (!empty($production['response_body'])) {
            $productionMessage .= '<br><pre>' . htmlentities((string) $production['response_body']) . '</pre>';
        }

        r2(U . 'settings/zatca', 'e', 'Step 3 failed: ' . $productionMessage);
        break;

    case 'zatca-clear-and-reset':
        if ($user['user_type'] != 'Admin') {
            r2(U . 'dashboard', 'e', $_L['You do not have permission']);
        }

        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/zatca',
                'e',
                'Sorry! This option is disabled in the demo mode.'
            );
        }

        $clearKeys = [
            'zatca_csr_content',
            'zatca_otp',
            'zatca_binary_security_token',
            'zatca_secret',
            'zatca_compliance_request_id',
            'zatca_compliance_csid',
            'zatca_compliance_secret',
            'zatca_compliance_last_response',
            'zatca_production_binary_security_token',
            'zatca_production_secret',
            'zatca_production_csid',
            'zatca_production_request_id',
            'zatca_production_certificate',
            'zatca_production_last_response',
            'zatca_certificate',
            'zatca_private_key',
        ];

        foreach ($clearKeys as $key) {
            try {
                delete_option($key);
            } catch (Throwable $e) {
                // Continue even if delete fails for some keys.
            }
        }

        r2(
            U . 'settings/zatca',
            's',
            'All local ZATCA credentials and certificates have been cleared. '
            . 'After you reset/revoke the device in ZATCA portal and obtain a fresh OTP, '
            . 'return here and run Verify again to complete fresh onboarding.'
        );

        break;

    case 'eml-post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'settings/emls/', 'e', $_L['disabled_in_demo']);
        }

        $sysemail = _post('sysemail');
        if (filter_var($sysemail, FILTER_VALIDATE_EMAIL) == false) {
            r2(U . 'settings/emls/', 'e', $_L['Invalid System Email']);
        }

        $d = AppConfig::where('setting', 'sysEmail')
            ->first();
        $d->value = $sysemail;
        $d->save();
        $email_method = _post('email_method');
        $e = EmailConfig::first();
        if ($email_method == 'smtp') {
            $smtp_user = _post('smtp_user');
            $smtp_host = _post('smtp_host');
            $smtp_password = $_POST['smtp_password'];
            $smtp_port = _post('smtp_port');
            $smtp_secure = _post('smtp_secure');
            if (
                $smtp_port == '' || $smtp_host == ''
            ) {
                r2(U . 'settings/emls/', 'e', $_L['smtp_fields_error']);
            } else {

                $e->update([
                    'method' => $email_method,
                    'host' => $smtp_host,
                    'username' => $smtp_user,
                    'password' => $smtp_password,
                    'port' => $smtp_port,
                    'secure' => $smtp_secure,
                ]);
            }
        } else {
            $e->update([
                'method' => $email_method,
            ]);
        }
        $e->save();

        update_option('mailgun_api_key', _post('mailgun_api_key'));
        update_option('mailgun_domain', _post('mailgun_domain'));
        update_option('sparkpost_api_key', _post('sparkpost_api_key'));

        r2(U . 'settings/emls/', 's', $_L['Settings Saved Successfully']);

        break;

    case 'lc-post':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'settings/localisation/',
                'e',
                'Sorry! This option is disabled in the demo mode!'
            );
        }

        $tzone = _post('tzone');
        $d = AppConfig::where('setting', 'timezone')
            ->first();
        $d->value = $tzone;
        $d->save();

        $country = _post('country');
        $d = AppConfig::where('setting', 'country')
            ->first();
        $d->value = $country;
        $d->save();

        $country_flag_code = strtolower(Countries::full2short($country));

        update_option('country_flag_code', $country_flag_code);


        $currency_code = _post('home_currency', 'USD');


        update_option('home_currency', $currency_code);

        $currencies = Currency::getAllCurrencies();

        if (isset($currencies[$currency_code])) {
            update_option(
                'currency_code',
                $currencies[$currency_code]['symbol']
            );
            update_option(
                'dec_point',
                $currencies[$currency_code]['decimal_mark']
            );
            update_option(
                'thousands_sep',
                $currencies[$currency_code]['thousands_separator']
            );

            if ($currencies[$currency_code]['symbol_first'] == true) {
                update_option('currency_symbol_position', 'p');
            } else {
                update_option('currency_symbol_position', 's');
            }
        }

        $lan = _post('lan');
        $d = AppConfig::where('setting', 'language')
            ->first();
        $d->value = $lan;
        $d->save();

        if ($lan == 'ar' || $lan == 'he') {
            updateOption('rtl', 1);
        }

        $df = _post('df');

        update_option('df', $df);


        $all_currencies = Currency::all();

        foreach($all_currencies as $currency)
        {
            $currency->isdefault = 0;
            $currency->save();
        }


        $currency_exist = Currency::where('iso_code', $currency_code)->first();

        if (!$currency_exist) {
            $c_create = new Currency();
            $c_create->cname = $currency_code;
            $c_create->iso_code = $currency_code;
            $c_create->symbol = $currency_code;
            $c_create->rate = 1.0;
            $c_create->isdefault = 1;
            $c_create->save();
        }
        else{
            $currency_exist->isdefault = 1;
            $currency_exist->save();
        }

        $currency_decimal_digits = _post('currency_decimal_digits');
        $d = AppConfig::where('setting', 'currency_decimal_digits')
            ->first();
        $d->value = $currency_decimal_digits;
        $d->save();

        $currency_symbol_position = _post('currency_symbol_position');
        $d = AppConfig::where('setting', 'currency_symbol_position')
            ->first();
        $d->value = $currency_symbol_position;
        $d->save();

        $thousand_separator_placement = _post('thousand_separator_placement');
        $d = AppConfig::where('setting', 'thousand_separator_placement')
            ->first();
        $d->value = $thousand_separator_placement;
        $d->save();

        $data = $request->all();

        if (isset($data['decimal_places_products_and_services'])) {
            if ($data['decimal_places_products_and_services'] === 'default') {
                removeOption('decimal_places_products_and_services');
                ORM::execute(
                    'ALTER TABLE `sys_items` CHANGE `sales_price` `sales_price` DECIMAL(16,2) NOT NULL DEFAULT \'0.00\''
                );
                ORM::execute(
                    'ALTER TABLE `sys_items` CHANGE `cost_price` `cost_price` DECIMAL(16,2) NOT NULL DEFAULT \'0.00\''
                );
                ORM::execute(
                    'ALTER TABLE `sys_invoiceitems` CHANGE `amount` `amount` DECIMAL(16,2) NOT NULL DEFAULT \'0.00\''
                );
            } else {
                $decimal_places_products_and_services =
                    (int) $data['decimal_places_products_and_services'];
                update_option(
                    'decimal_places_products_and_services',
                    $decimal_places_products_and_services
                );

                ORM::execute(
                    'ALTER TABLE `sys_items` CHANGE `sales_price` `sales_price` DECIMAL(16,' .
                    $decimal_places_products_and_services .
                    ') NOT NULL DEFAULT \'0.00\''
                );
                ORM::execute(
                    'ALTER TABLE `sys_items` CHANGE `cost_price` `cost_price` DECIMAL(16,' .
                    $decimal_places_products_and_services .
                    ') NOT NULL DEFAULT \'0.00\''
                );
                ORM::execute(
                    'ALTER TABLE `sys_invoiceitems` CHANGE `amount` `amount` DECIMAL(16,' .
                    $decimal_places_products_and_services .
                    ') NOT NULL DEFAULT \'0.00\''
                );
            }
        }

        r2(U . 'settings/localisation/');

        break;

    case 'lc-charset-post':
        $coll = _post('coll');
        $chars = explode('_', $coll);
        $chars_name = $chars[0];

        $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($mysqli->error === '' || $mysqli->error === '0') {
            $sql = "SHOW TABLES";
            $show = $mysqli->query($sql);
            while ($r = $show->fetch_array()) {
                $tables[] = $r[0];
            }

            if (!empty($tables)) {
                foreach ($tables as $table) {
                    $result = $mysqli->query(
                        'ALTER TABLE ' .
                        $table .
                        " CONVERT TO CHARACTER SET $chars_name COLLATE $coll"
                    );
                }
            }
        }

        r2(
            U . 'settings/localisation/',
            's',
            $_L['Charset Saved Successfully']
        );
        break;

    case 'change-password':
        $ui->assign('selected_navigation', 'dashboard');
        view('change-password');

        break;

    case 'change-password-post':
        $password = _post('password');
        if ($password != '') {
            $d = ORM::for_table('sys_users')
                ->where('username', $user['username'])
                ->first();
            if ($d) {
                $d_pass = $d['password'];
                if (Password::_verify($password, $d_pass) == true) {
                    $npass = _post('npass');
                    $cnpass = _post('cnpass');

                    if ($npass !== $cnpass) {
                        r2(
                            U . 'settings/change-password',
                            'e',
                            $_L['Both Password should be same']
                        );
                    }

                    if (APP_STAGE == 'Demo') {
                        r2(
                            U . 'settings/change-password',
                            'e',
                            $_L['disabled_in_demo']
                        );
                    }
                    $npass = Password::_crypt($npass);
                    $d->password = $npass;
                    $d->save();
                    _msglog('s', $_L['Password changed successfully']);

                    r2(U . 'login');
                } else {
                    r2(
                        U . 'settings/change-password',
                        'e',
                        $_L['Incorrect Current Password']
                    );
                }
            } else {
                r2(
                    U . 'settings/change-password',
                    'e',
                    $_L['Incorrect Current Password']
                );
            }
        } else {
            r2(
                U . 'settings/change-password',
                'e',
                $_L['Incorrect Current Password']
            );
        }

        break;

    case 'networth_goal':
        $goal = _post('goal');

        $goal = Finance::amount_fix($goal);

        if (is_numeric($goal) && $goal != '') {
            $d = AppConfig::where('setting', 'networth_goal')
                ->first();
            $d->value = $goal;
            $d->save();
            _msglog('s', $_L['New Goal has been set']);
        } else {
            _msglog('e', $_L['Invalid Number']);
        }

        break;

    case 'email-templates':
        $d = ORM::for_table('sys_email_templates')
            ->order_by_desc('id')
            ->find_array();
        $ui->assign('d', $d);

        view('email-templates');
        break;

    case 'email-templates-view':
        $sid = route(2, '');

        $create = true;

        if ($sid != '') {
            $d = ORM::for_table('sys_email_templates')->find($sid);
            $create = !(bool) $d;
        }

        if ($create) {
            $core = 'No';
            $tplname = '';
            $subject = '';
            $message = '';
            $modal_header = $_L['Add New Template'];
            $s_yes = 'selected="selected"';
            $s_no = '';
            $id = '';
        } else {
            $s_yes = '';
            $s_no = '';
            if ($d['send'] == 'No') {
                $s_no = 'selected="selected"';
            }

            if ($d['send'] == 'Yes') {
                $s_yes = 'selected="selected"';
            }

            $core = $d->core;
            $tplname = $d->tplname;
            $subject = $d->subject;
            $message = $d->message;
            $modal_header = ib_lan_get_line($d['tplname']);
            $id = $d->id;
        }

        echo '
<div class="mx-auto" style="max-width: 800px;">
<div class="panel mb-0 rounded-0">
<div class="panel-hdr">
<h2>' .
            $modal_header .
            '</h2>
</div>
<div class="panel-container">
<div class="panel-content">
<form class="form-horizontal" role="form" id="edit_form" method="post">

<div class="mb-3">
    
     ' .
            ($core == 'Yes'
                ? '<span class="badge badge-warning"> ' .
                $_L['System'] .
                ' </span>'
                : '<span class="label badge-info"> ' .
                $_L['Custom'] .
                ' </span>') .
            '
  </div>

<div class="mb-3">
    <label for="tplname">' .
            $_L['Name'] .
            '</label>
     <input type="text" id="tplname" name="tplname" class="form-control" value="' .
            $tplname .
            '" ' .
            ($core == 'Yes' ? 'disabled' : '') .
            '>
  </div>
  
<div class="mb-3">
    <label for="subject">' .
            $_L['Subject'] .
            '</label>
    <input type="text" id="subject" name="subject" class="form-control" value="' .
            $subject .
            '">
  </div>


   <div class="mb-3">
    <label for="message">' .
            $_L['Message Body'] .
            '</label>
    <textarea id="message" name="message" class="form-control sysedit" rows="10">' .
            $message .
            '</textarea>
      <input type="hidden" id="sid" name="id" value="' .
            $id .
            '">
  </div>
  <div class="mb-3">
    <label for="name">' .
            $_L['Send'] .
            '</label>
    <select name="send" id="send" class="form-select">
                              <option value="Yes" ' .
            $s_yes .
            '>' .
            $_L['Yes'] .
            '</option>
                              <option value="No" ' .
            $s_no .
            '>' .
            $_L['No'] .
            '</option>

                          </select>
  </div>
  <div class="mb-3">
		<button id="update" class="btn btn-primary">' .
            $_L['Save'] .
            '</button>
</div>
</form>
</div>
</div>
</div>
</div>
';

        break;

    case 'update-email-template':
        $id = _post('id');
        $message = $data['message'];
        $subject = $data['subject'];
        $tplname = $data['tplname'];
        $send = _post('send');

        if ($id == '') {
            if ($message == '' || $subject == '' || $tplname == '') {
                echo $_L['All Fields are Required'];
                exit();
            }

            $d = ORM::for_table('sys_email_templates')->create();

            $d->tplname = $tplname;
            $d->subject = $subject;
            $d->send = $send;
            $d->message = $message;
            $d->core = 'No';

            $d->save();

            echo $_L['added_successful'];

            exit();
        }
        $d = ORM::for_table('sys_email_templates')->find($id);
        if (APP_STAGE == 'Demo') {
            echo 'Sorry! This option is disabled in the demo mode!';
            exit();
        }
        if ($d) {
            if ($d->core == 'Yes') {
                $tplname = $d->tplname;
            }

            if ($message == '' || $subject == '' || $tplname == '') {
                echo 'Invalid Data';
            } else {
                $d->tplname = $tplname;
                $d->subject = $subject;
                $d->send = $send;
                $d->message = $message;

                $d->save();
                echo $_L['edit_successful'];
            }
        } else {
            echo 'Sorry Data not Found';
        }

        break;

    case 'tags':
        $ui->assign('content_inner', inner_contents($config['c_cache']));

        $d = ORM::for_table('sys_tags')->find_many();
        $ui->assign('d', $d);

        view('tags');

        break;

    case 'logo-post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'appearance/customize/', 'e', $_L['disabled_in_demo']);
        }

        $validextentions = ["jpeg", "jpg", "png"];
        $temporary = explode(".", $_FILES["file"]["name"]);
        $file_extension = end($temporary);
        $file_name = '';
        if ($_FILES["file"]["type"] == "image/png") {
            $file_name = 'logo_' . _raid(10) . '.png';
        } elseif ($_FILES["file"]["type"] == "image/jpg") {
            $file_name = 'logo_' . _raid(10) . '.jpg';
        } elseif ($_FILES["file"]["type"] == "image/jpeg") {
            $file_name = 'logo_' . _raid(10) . '.jpeg';
        } elseif ($_FILES["file"]["type"] == "image/gif") {
            $file_name = 'logo_' . _raid(10) . '.gif';
        } else {
        }
        if (
            ($_FILES["file"]["type"] == "image/png" ||
                $_FILES["file"]["type"] == "image/jpg" ||
                $_FILES["file"]["type"] == "image/jpeg" ||
                $_FILES["file"]["type"] == "image/gif") &&
            $_FILES["file"]["size"] < 1000000 && //approx. 100kb files can be uploaded
            in_array($file_extension, $validextentions)
        ) {
            move_uploaded_file(
                $_FILES["file"]["tmp_name"],
                'storage/system/' . $file_name
            );

            update_option('logo_default', $file_name);

            r2(
                U . 'appearance/customize/',
                's',
                $_L['Settings Saved Successfully']
            );
        } else {
            r2(U . 'appearance/customize/', 'e', $_L['Invalid Logo File']);
        }

        break;

    case 'logo-inverse-post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'appearance/customize/', 'e', $_L['disabled_in_demo']);
        }

        $validextentions = ["jpeg", "jpg", "png"];
        $temporary = explode(".", $_FILES["file"]["name"]);
        $file_extension = end($temporary);
        $file_name = '';
        if ($_FILES["file"]["type"] == "image/png") {
            $file_name = 'logo_inverse_' . _raid(10) . '.png';
        } elseif ($_FILES["file"]["type"] == "image/jpg") {
            $file_name = 'logo_inverse_' . _raid(10) . '.jpg';
        } elseif ($_FILES["file"]["type"] == "image/jpeg") {
            $file_name = 'logo_inverse_' . _raid(10) . '.jpeg';
        } elseif ($_FILES["file"]["type"] == "image/gif") {
            $file_name = 'logo_inverse_' . _raid(10) . '.gif';
        } else {
        }
        if (
            ($_FILES["file"]["type"] == "image/png" ||
                $_FILES["file"]["type"] == "image/jpg" ||
                $_FILES["file"]["type"] == "image/jpeg" ||
                $_FILES["file"]["type"] == "image/gif") &&
            $_FILES["file"]["size"] < 10000000 && //approx. 100kb files can be uploaded
            in_array($file_extension, $validextentions)
        ) {
            move_uploaded_file(
                $_FILES["file"]["tmp_name"],
                'storage/system/' . $file_name
            );

            update_option('logo_inverse', $file_name);

            r2(
                U . 'appearance/customize/',
                's',
                $_L['Settings Saved Successfully']
            );
        } else {
            r2(U . 'appearance/customize/', 'e', $_L['Invalid Logo File']);
        }

        break;

    case 'logo-square-post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'appearance/customize/', 'e', $_L['disabled_in_demo']);
        }


        if(!empty($_FILES['file']['type']))
        {
            #Check file type is png
            if($_FILES['file']['type'] !== 'image/png') {
                r2(U . 'appearance/customize/', 'e', __('Invalid Logo File'));
            }

            #Check file size is less than 1MB
            if($_FILES['file']['size'] > 1000000) {
                r2(U . 'appearance/customize/', 'e', __('Invalid Logo File'));
            }

            #Check file is not corrupted

            $file_name = 'logo-512x512-' . _raid(10) . '.png';
            move_uploaded_file(
                $_FILES["file"]["tmp_name"],
                'storage/system/' . $file_name
            );
            update_option('logo_square', $file_name);

            // Save favicon
            $img = Image::make('storage/system/' . $file_name);

            $icon_270 = 'icon-270x270-' . time() . '.png';
            $img->resize(270, 270);
            $img->save('storage/system/' . $icon_270);
            update_option('icon-270', $icon_270);

            $icon_192 = 'icon-192x192-' . time() . '.png';
            $img->resize(192, 192);
            $img->save('storage/system/' . $icon_192);
            update_option('icon-192', $icon_192);

            $icon_180 = 'icon-180x180-' . time() . '.png';
            $img->resize(180, 180);
            $img->save('storage/system/' . $icon_180);
            update_option('icon-180', $icon_180);

            $icon_32 = 'icon-32x32-' . time() . '.png';
            $img->resize(32, 32);
            $img->save('storage/system/' . $icon_32);
            update_option('icon-32', $icon_32);
        }



        r2(
            U . 'appearance/customize/',
            's',
            $_L['Settings Saved Successfully']
        );

       // list($width, $height) = getimagesize($_FILES["file"]["tmp_name"]);

//        if ($width == 512 && $height == 512) {
//
//        } else {
//            r2(
//                U . 'appearance/customize/',
//                'e',
//                $_L['Invalid Logo File'] .
//                ' ' .
//                $_L['Required'] .
//                '-512x512'
//            );
//        }

        break;

    case 'localisation':
        $ui->assign('content_inner', inner_contents($config['c_cache']));

        $tblsts = ORM::for_table('crm_accounts')
            ->raw_query("show table status like 'crm_accounts'")
            ->first();
        $col = $tblsts['Collation'];
        $ui->assign('col', $col);

        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }
        $ui->assign('countries', Countries::all($config['country']));
        $timezonelist = Timezone::timezoneList();
        $ui->assign('tlist', $timezonelist);

        $ui->assign('currencies', Currency::getAllCurrencies());

        $ui->assign('languages', Localization::getLanguages());

        view('localisation');

        break;

    case 'emls':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $e = ORM::for_table('sys_emailconfig')->find('1');
        $ui->assign('e', $e);

        if (isset($config['mailgun_api_key'])) {
            $mailgun_api_key = $config['mailgun_api_key'];
        } else {
            add_option('mailgun_api_key', '');
            $mailgun_api_key = '';
        }

        if (isset($config['mailgun_domain'])) {
            $mailgun_domain = $config['mailgun_domain'];
        } else {
            add_option('mailgun_domain', '');
            $mailgun_domain = '';
        }

        if (isset($config['sparkpost_api_key'])) {
            $sparkpost_api_key = $config['sparkpost_api_key'];
        } else {
            add_option('sparkpost_api_key', '');
            $sparkpost_api_key = '';
        }

        view('emls', [
            'mailgun_api_key' => $mailgun_api_key,
            'mailgun_domain' => $mailgun_domain,
            'sparkpost_api_key' => $sparkpost_api_key,
        ]);

        break;

    case 'automation':
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        view('automation');

        break;

    case 'pg':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        if ($user['user_type'] != 'Admin') {
            r2(U . "dashboard", 'e', $_L['You do not have permission']);
        }

        $d = ORM::for_table('sys_pg')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('d', $d);

        view('pg');

        break;

    case 'pg-conf':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        $pg = $routes['2'];
        $d = ORM::for_table('sys_pg')->find($pg);
        if ($d) {
            $script_append = '';

            $label = [];

            $label['value'] = 'Value';
            $label['c1'] = '';
            $label['c2'] = '';
            $label['c3'] = '';
            $label['c4'] = '';
            $label['c5'] = '';
            $label['mode'] = false;

            $input = [];

            $input['value'] =
                '<input type="text" class="form-control" id="value" name="value" value="' .
                $d['value'] .
                '">';
            $input['c1'] =
                '<input type="text" class="form-control" id="c1" name="c1" value="' .
                $d['c1'] .
                '">';
            $input['c2'] =
                '<input type="text" class="form-control" id="c2" name="c2" value="' .
                $d['c2'] .
                '">';
            $input['c3'] =
                '<input type="text" class="form-control" id="c3" name="c3" value="' .
                $d['c3'] .
                '">';
            $input['c4'] =
                '<input type="text" class="form-control" id="c4" name="c4" value="' .
                $d['c4'] .
                '">';
            $input['c5'] =
                '<input type="text" class="form-control" id="c5" name="c5" value="' .
                $d['c5'] .
                '">';

            $help_txt = [];

            $help_txt['value'] = '';
            $help_txt['c1'] = '';
            $help_txt['c2'] = '';
            $help_txt['c3'] = '';
            $help_txt['c4'] = '';
            $help_txt['c5'] = '';
            $help_txt['mode'] = '';

            $extra_panel = '';

            $processor = $d->processor;

            switch ($processor) {
                case 'paypal':
                    $label['value'] = 'Paypal Email';
                    $label['c1'] = $_L['Currency Code'];
                    $label['c2'] = 'Conversion Rate';

                    break;

                case 'stripe':
                    $label['value'] = 'Publishable key';
                    $label['c1'] = 'Secret key';
                    $label['c2'] = $_L['Currency Code'];

                    break;

                case 'authorize_net':
                    $label['value'] = 'API Login ID';
                    $label['c1'] = 'Transaction Key';

                    break;

                case 'manualpayment':
                    $input['value'] =
                        '<textarea id="value" class="form-control" rows="3">' .
                        $d['value'] .
                        '</textarea>';

                    $label['value'] = 'Payment Instructions';

                    break;

                case 'braintree':
                    $label['value'] = 'Your Merchant ID';
                    $label['c1'] = $_L['Public Key'];
                    $label['c2'] = $_L['Private Key'];
                    $label['c3'] = $_L['Default Account'];
                    $label['c4'] = $_L['live or sandbox'];

                    break;

                case 'ccavenue':
                    $label['value'] = 'Merchant ID';
                    $label['c1'] = 'Working Key';
                    $label['c2'] = 'Currency ISO Code';
                    $label['c3'] = 'Access Code';

                    break;

                default:
                    $label['value'] = 'Value';
            }

            $ui->assign('label', $label);
            $ui->assign('input', $input);
            $ui->assign('help_txt', $help_txt);
            $ui->assign('extra_panel', $extra_panel);

            $icon_url = '';

            if (file_exists('apps/' . $processor . '/views/img/icon.png')) {
                $icon_url =
                    APP_URL . '/apps/' . $processor . '/views/img/icon.png';
            }

            $ui->assign('icon_url', $icon_url);

            Event::trigger('settings/pg_conf/label', [$processor]);

            $ui->assign('d', $d);
            view('pg-conf');
        } else {
            echo 'PG Not Found';
        }

        break;

    case 'pg-post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'settings/app', 'e', $_L['disabled_in_demo']);
        }
        $pg = _post('pgid');

        $d = ORM::for_table('sys_pg')->find($pg);
        if ($d) {
            $name = _post('name');
            if ($name == '') {
                _msglog('e', $_L['name_error']);
                echo $pg;
                exit();
            }
            $d->name = $name;
            //  $d->settings = _post('settings');
            $d->value = _post('value');
            $d->status = _post('status');
            $d->c1 = _post('c1');
            $d->c2 = _post('c2');
            $d->c3 = _post('c3');
            $d->c4 = _post('c4');
            $d->c5 = _post('c5');
            $d->mode = _post('mode');
            $d->save();
            _msglog('s', $_L['Data Updated']);
            echo $pg;
        } else {
            echo 'PG Not Found';
        }

        break;

    case 'add-tax':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        view('add-tax');
        break;

    case 'add-tax-post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'settings/app', 'e', $_L['disabled_in_demo']);
        }
        $taxname = _post('taxname');
        $taxrate = _post('taxrate');
        $taxrate = Finance::amount_fix($taxrate);
        if ($taxname == '' || $taxrate == '') {
            $taxrate = 0.0;
        }

        if (!is_numeric($taxrate)) {
            $taxrate = 0.0;
        }

        $d = ORM::for_table('sys_tax')->create();
        $d->name = $taxname;
        $d->rate = $taxrate;
        $d->is_inclusive = _post('is_inclusive') == '1' ? 1 : 0;
        $d->save();
        r2(U . 'tax/list/', 's', $_L['New TAX Added']);
        break;

    case 'edit-tax':
        $tid = $routes['2'];
        $d = ORM::for_table('sys_tax')->find($tid);
        if ($d) {
            $ui->assign('d', $d);

            $ui->assign('ib_money_format_apply', true);

            Event::trigger('settings/edit-tax/');

            view('edit-tax');
        } else {
            r2(U . 'tax/list/', 'e', $_L['TAX Not Found']);
        }

        break;

    case 'edit-tax-post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'settings/app', 'e', $_L['disabled_in_demo']);
        }
        $tid = _post('tid');
        $d = ORM::for_table('sys_tax')->find($tid);
        if ($d) {
            $taxname = _post('taxname');
            $taxrate = _post('taxrate');
            $taxrate = Finance::amount_fix($taxrate);
            if ($taxname == '' || $taxrate == '') {
                r2(
                    U . 'settings/edit-tax/' . $tid . '/',
                    'e',
                    'All Fields is Required.'
                );
            }
            if (!is_numeric($taxrate)) {
                r2(
                    U . 'settings/edit-tax/' . $tid . '/',
                    'e',
                    'Invalid TAX Rate.'
                );
            }

            $d->name = $taxname;
            $d->rate = $taxrate;
            $d->is_inclusive = _post('is_inclusive') == '1' ? 1 : 0;
            $d->save();
            r2(U . 'settings/edit-tax/' . $tid . '/', 's', 'TAX Saved.');
        } else {
            r2(U . 'tax/list/', 'e', $_L['TAX Not Found']);
        }

        break;

    case 'consolekey_regen':
        $nkey = _raid('10');

        $d = AppConfig::where('setting', 'ckey')
            ->first();
        $d->value = $nkey;
        $d->save();
        r2(U . 'settings/automation/', 's', $_L['cron_new_key']);
        break;

    case 'automation-post':
        $accounting_snapshot = _post('accounting_snapshot');
        $d = ORM::for_table('sys_schedule')
            ->where('cname', 'accounting_snapshot')
            ->first();
        $d->val = $accounting_snapshot == 'on' ? 'Active' : 'Inactive';
        $d->save();

        $recurring_invoice = _post('recurring_invoice');
        $d = ORM::for_table('sys_schedule')
            ->where('cname', 'recurring_invoice')
            ->first();
        $d->val = $recurring_invoice == 'on' ? 'Active' : 'Inactive';
        $d->save();

        $notify = _post('notify');
        $notifyemail = _post('notifyemail');
        //need valid notify email
        if ($notify == 'on' && filter_var($notifyemail, FILTER_VALIDATE_EMAIL) == false) {
            r2(U . 'settings/automation/', 'e', $_L['cron_notification']);
        }
        $d = ORM::for_table('sys_schedule')
            ->where('cname', 'notify')
            ->first();
        $d->val = $notify == 'on' ? 'Active' : 'Inactive';
        $d->save();

        $d = ORM::for_table('sys_schedule')
            ->where('cname', 'notifyemail')
            ->first();
        $d->val = $notifyemail;
        $d->save();

        r2(U . 'settings/automation/', 's', $_L['Settings Saved Successfully']);
        break;

    case 'plugins':
        $Parsedown = new Parsedown();

        $ui->assign('selected_navigation', 'plugins');

        $pls = array_diff(scandir('apps'), ['..', '.', 'index.html']);
        $pl_html = '';

        $plugins = [];

        foreach ($pls as $pl) {
            $pl_path = 'apps/' . $pl . '/';
            $i = 0;
            if (file_exists($pl_path . '/manifest.php')) {
                $i++;

                $plugin = require $pl_path . '/manifest.php';

                if (empty($plugin)) {
                    continue;
                }

                $plugin['icon_url'] = APP_URL . '/storage/system/plug.png';
                $plugin['status'] = 'Not Installed';

                $d = ORM::for_table('sys_pl')
                    ->where('c', $pl)
                    ->first();
                $btn = '';
                if ($d) {
                    //plugin was installed & active
                    $status = $d['status'];
                    if ($status == '1') {
                        $plugin['status'] = 'Active';

                    } else {
                        $plugin['status'] = 'Inactive';
                    }

                    $db_build = $d->build;

                    $plugin['installed_build'] = (int) $d->build;


                    if (file_exists($pl_path . '/views/img/icon.png')) {
                        $plugin['icon_url'] =
                            APP_URL . '/' . $pl_path . '/views/img/icon.png';
                    }

                    $plugins[$pl] = $plugin;
                }

                $plugins[$pl] = $plugin;
            }
        }

        $marketplace_plugins = [];

//        try{
//
//            $marketplace_plugins = (new Http())->withOptions([
//                'verify' => false,
//            ])->get('https://www.cloudonex.com/public-api/post.json?slug=business-suite-plugins')
//                ->json();
//        }
//        catch(Exception $e){
//            $marketplace_plugins = [];
//        }

        view('pl-list', [
            'plugins' => $plugins,
            'marketplace_plugins' => $marketplace_plugins,
        ]);

        break;

    case 'plugin_upload':
        $uploader = new Uploader();
        $uploader->setDir('apps/');
        $uploader->sameName(true);
        $uploader->setExtensions(['zip']); //allowed extensions list//
        if ($uploader->uploadFile('file')) {
            $uploaded = $uploader->getUploadName(); //get uploaded file name, renames on upload//
        } else {
            _msglog('e', $uploader->getMessage()); //get upload error message
        }

        break;

    case 'plugin_unzip':
        $msg = '';
        $name = _post('name');
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();

            $res = $zip->open('apps/' . $name);
            if ($res === true) {
                if (APP_STAGE == 'Demo') {
                    $msg .=
                        $name .
                        ' - Plugin Unzipping is Disabled in the Demo Mode! <br>';
                } else {
                    $zip->extractTo('apps/');
                }

                if ($zip->close()) {
                    unlink('apps/' . $name);
                }
                //
            } else {
                $msg .=
                    $name .
                    ' - Invalid Plugin Package Or An error occured while unzipping the file! <br>';
            }
        } else {
            $msg .= 'PHP ZipArchive Class is not Available! <br>';
        }

        if ($msg != '') {
            _msglog('e', $msg);
        } else {
            _msglog('s', $_L['Plugin Added']);
        }

        break;

    case 'plugin_activate':
        define('IB_INTERNAL', true);

        if (isset($routes['2']) && $routes['2'] != '') {
            $pl = $routes['2'];
            $pl_path = 'apps/' . $pl . '/';

            $msg = '';
            $msg .= 'Activating Plugin...
';

            $plugin = require $pl_path . '/manifest.php';

            if (APP_STAGE == 'Demo') {
                $msg .= 'Sorry, Activating Plugin is disabled in the demo mode...
';
            } else {
                if (file_exists($pl_path . '/activate.php')) {
                    require $pl_path . '/activate.php';
                }

                $d = ORM::for_table('sys_pl')
                    ->where('c', $pl)
                    ->first();
                if ($d) {
                    $d->status = '1';

                    if (isset($plugin['build'])) {
                        $d->build = $plugin['build'];
                    }

                    $d->save();
                    $msg .= 'Plugin Activated...
';
                }
            }

            $ui->assign('plugin', $plugin);
            $ui->assign('plugin_activity', $_L['Activating Plugin']);
            $ui->assign('msg', $msg);

            view('plugin-activity');
        } else {
            echo 'Plugin not Found';
        }

        break;

    case 'plugin_deactivate':
        define('IB_INTERNAL', true);

        if (isset($routes['2']) && $routes['2'] != '') {
            $pl = $routes['2'];
            $pl_path = 'apps/' . $pl . '/';

            $msg = '';
            $msg .= 'Deactivating Plugin...
';

            $plugin = require $pl_path . '/manifest.php';

            if (APP_STAGE == 'Demo') {
                $msg .= 'Sorry, Deactivating Plugin is disabled in the demo mode...
';
            } else {
                if (file_exists($pl_path . '/deactivate.php')) {
                    require $pl_path . '/deactivate.php';
                }

                $d = ORM::for_table('sys_pl')
                    ->where('c', $pl)
                    ->first();
                if ($d) {
                    $d->status = '0';
                    $d->save();
                    $msg .= 'Plugin Deactivated...
';
                }
            }

            $ui->assign('plugin', $plugin);
            $ui->assign('plugin_activity', $_L['Deactivating Plugin']);
            $ui->assign('msg', $msg);
            view('plugin-activity');
        } else {
            echo 'Plugin not Found';
        }

        break;

    case 'plugin_install':
        define('IB_INTERNAL', true);

        if (isset($routes['2']) && $routes['2'] != '') {
            $pl = $routes['2'];

            $pl_path = 'apps/' . $pl . '/';
            $msg = '';
            $msg .= 'Installing Plugin...
';
            $plugin = require $pl_path . '/manifest.php';

            if (APP_STAGE == 'Demo') {
                $msg .= 'Sorry, Installing Plugin is disabled in the demo mode...
';
            } else {
                if (file_exists($pl_path . '/install.php')) {
                    require $pl_path . '/install.php';
                }

                $msg .= 'Adding Plugin to the Plugin Database
';

                $c = ORM::for_table('sys_pl')->create();
                $c->c = $pl;
                $c->status = 1;

                if (isset($plugin['priority'])) {
                    $c->sorder = $plugin['priority'];
                }

                $c->build = isset($plugin['build']) ? $plugin['build'] : 1;

                $c->c1 = '';
                $c->c2 = '';

                $c->save();

                $msg .= 'Plugin Added
';
            }

            $ui->assign('plugin', $plugin);
            $ui->assign('plugin_activity', $_L['Installing Plugin']);
            $ui->assign('msg', $msg);
            view('plugin-activity');
        } else {
            echo 'Install Script not Found';
        }

        break;

    case 'plugin_uninstall':
        define('IB_INTERNAL', true);

        if (isset($routes['2']) && $routes['2'] != '') {
            $pl = $routes['2'];
            $pl_path = 'apps/' . $pl . '/';

            $msg = '';
            $msg .= 'Uninstalling Plugin...
';

            $plugin = require $pl_path . '/manifest.php';

            if (APP_STAGE == 'Demo') {
                $msg .= 'Sorry, Uninstalling Plugin is disabled in the demo mode...
';
            } else {
                if (file_exists($pl_path . '/uninstall.php')) {
                    require $pl_path . '/uninstall.php';
                }

                $msg .= 'Removing Plugin from Plugin Database...
';

                $d = ORM::for_table('sys_pl')
                    ->where('c', $pl)
                    ->first();
                if ($d) {
                    $d->delete();
                    $msg .= 'Plugin Uninstalled...
';
                }
            }

            $ui->assign('plugin', $plugin);
            $ui->assign('plugin_activity', $_L['Uninstalling Plugin']);
            $ui->assign('msg', $msg);
            view('plugin-activity');
        } else {
            echo 'Uninstall script not found';
        }

        break;

    case 'plugin_delete':
        define('IB_INTERNAL', true);

        if (APP_STAGE !== 'Live') {
            exit('delete works on live mode only');
        }

        if (isset($routes['2']) && $routes['2'] != '') {
            $pl = $routes['2'];
            $pl_path = 'apps/' . $pl . '/';

            $msg = '';
            $msg .= 'Deleting Plugin...
';

            require $pl_path . '/manifest.php';

            if (APP_STAGE == 'Demo') {
                $msg .= 'Sorry, Deleting Plugin is disabled in the demo mode...
';
            } elseif (Sysfile::deleteDir($pl_path)) {
                $msg .= 'Plugin Directory Deleted Successfully
';
            } else {
                $msg .=
                    'An Error Occurred while Deleting Plugin Directory. You may Delete this Plugin Manually - ' .
                    $pl_path .
                    '
';
            }

            $ui->assign('plugin', $plugin);
            $ui->assign('plugin_activity', 'Delete Plugin');
            $ui->assign('msg', $msg);
            view('plugin-activity');
        } else {
            echo 'Plugin not found';
        }

        break;

    case 'plugin_update':
        define('IB_INTERNAL', true);

        if (isset($routes[2]) && $routes[2] != '') {
            $pl = $routes['2'];

            $pl_path = 'apps/' . $pl . '/';
            $msg = '';
            $msg .= 'Updating Plugin...
';
            require $pl_path . '/manifest.php';

            if (APP_STAGE == 'Demo') {
                $msg .= 'Sorry, Updating Plugin is disabled in the demo mode...
';
            } else {
                if (file_exists($pl_path . '/update.php')) {
                    $msg .= require $pl_path . '/update.php';
                    $msg .= PHP_EOL;
                }

                $msg .= 'Checking Build...
';

                $d = ORM::for_table('sys_pl')
                    ->where('c', $pl)
                    ->first();
                if ($d && isset($plugin['build'])) {
                    $d->build = $plugin['build'];
                    $d->save();
                    $msg .=
                        'Build Updated to ' .
                        $plugin['build'] .
                        '
';
                }

                $msg .= 'done...
';
            }

            $ui->assign('plugin', $plugin);
            $ui->assign('plugin_activity', $_L['Installing Plugin']);
            $ui->assign('msg', $msg);
            view('plugin-activity');
        } else {
            echo 'Install Script not Found';
        }

        break;

    case 'customfields':
        $cf = ORM::for_table('crm_customfields')
            ->where('ctype', 'crm')
            ->order_by_asc('id')
            ->find_many();

        $ui->assign('cf', $cf);

        view('customfields');

        break;

    case 'customfields-post':
        $fieldname = _post('fieldname');
        $fieldtype = _post('fieldtype');
        $description = _post('description');
        $validation = _post('validation');
        $options = _post('options');
        $showinvoice = _post('showinvoice');
        if ($showinvoice != 'Yes') {
            $showinvoice = 'No';
        }
        if ($fieldname != '') {
            $d = ORM::for_table('crm_customfields')->create();
            $d->fieldname = $fieldname;
            $d->fieldtype = $fieldtype;
            $d->description = $description;
            $d->regexpr = $validation;
            $d->fieldoptions = $options;
            $d->ctype = 'crm';
            $d->relid = 0;
            $d->adminonly = '';
            $d->required = '';
            $d->showorder = '';
            $d->showinvoice = $showinvoice;
            $d->sorder = '0';
            $d->save();

            echo $d->id();
        } else {
            echo 'Name is Required';
        }

        break;

    case 'customfields-ajax-add':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        view('ajax-add-custom-field');

        break;

    case 'customfields-ajax-edit':
        $id = $routes[2];
        $id = str_replace('f', '', $id);

        $d = ORM::for_table('crm_customfields')->find($id);
        if ($d) {
            $ui->assign('d', $d);
            view('ajax-edit-custom-field');
        } else {
            echo 'Not Found';
        }

        break;

    case 'customfield-edit-post':
        $id = _post('id');

        $fieldname = _post('fieldname');

        if ($fieldname == '') {
            ib_die('Name is Required');
        }

        $d = ORM::for_table('crm_customfields')->find($id);
        if ($d) {
            $fieldtype = _post('fieldtype');
            $description = _post('description');
            $validation = _post('validation');
            $options = _post('options');
            $showinvoice = _post('showinvoice');
            if ($showinvoice != 'Yes') {
                $showinvoice = 'No';
            }
            $d->fieldname = $fieldname;
            $d->fieldtype = $fieldtype;
            $d->description = $description;
            $d->regexpr = $validation;
            $d->fieldoptions = $options;
            $d->ctype = 'crm';
            $d->relid = 0;
            $d->adminonly = '';
            $d->required = '';
            $d->showorder = '';
            $d->showinvoice = $showinvoice;
            $d->sorder = '0';
            $d->save();
            echo $id;
        } else {
            echo 'Not Found';
        }

        break;

    case 'invoice-groups':

        $selected_group_id = (int) route(2, null);

        $selected_group = null;

        if($selected_group_id){
            $selected_group = InvoiceGroup::find($selected_group_id);
        }

        $groups = InvoiceGroup::all();

        \view('invoice-groups', [
            'groups' => $groups,
            'selected_group' => $selected_group,
        ]);

        break;

    case 'update_option':
        if (APP_STAGE == 'Demo') {
            _msglog('e', 'Sorry, this option is disabled in the demo mode.');
            ib_close();
        }

        $opt = _post('opt');
        $val = _post('val');

        $m = route(2);

        switch ($opt) {
            case 'add_fund_minimum_deposit':
            case 'add_fund_maximum_deposit':
                if (is_numeric($val)) {
                    update_option($opt, $val);
                    echo '1';
                } else {
                    i_close('Invalid Amount');
                }

                break;

            case 'tickets_assigned_sms_notification':
                if ($val == 1) {

                    $tpl_name = 'Ticket Assigned: Admin Notification';

                    $sms_template = SMSTemplate::where(
                        'tpl',
                        $tpl_name
                    )->first();

                    if (!$sms_template) {
                        $sms_template = new SMSTemplate();
                        $sms_template->tpl = $tpl_name;
                        $sms_template->sms =
                            'Ticket - {{ticket_id}} has been assigned to you.';
                        $sms_template->save();
                    }
                }

                update_option($opt, $val);
                echo '1';

                break;

            case 'invoicing_allow_staff_selection_for_each_item':

                if (!db_column_exist('sys_invoiceitems', 'staff_id')) {
                    DB::unprepared('ALTER TABLE `sys_invoiceitems` ADD `staff_id` INT(10) NOT NULL DEFAULT \'0\' AFTER `userid`');
                }

                update_option($opt, $val);

                break;

            case 'invoice_items_purchasing':

                if (!db_column_exist('sys_invoices', 'purchase_id')) {
                    DB::unprepared('ALTER TABLE `sys_invoices` ADD `purchase_status` VARCHAR(255) NULL DEFAULT NULL, ADD `purchase_id` VARCHAR(255) NULL DEFAULT NULL, ADD `purchase_date` DATE NULL DEFAULT NULL, ADD `purchase_staff_id` INT(11) UNSIGNED NOT NULL DEFAULT \'0\', ADD `purchase_cost` DECIMAL(16,2) NULL DEFAULT NULL, ADD `purchase_notes` TEXT NULL DEFAULT NULL, ADD `purchase_attachment` VARCHAR(255) NULL DEFAULT NULL;');
                }

                update_option($opt, $val);

                break;

            case 'invoice_items_shipping':

                if (!db_column_exist('sys_invoices', 'shipping_date')) {
                    DB::unprepared('ALTER TABLE `sys_invoices` ADD `shipping_status` VARCHAR(255) NULL DEFAULT NULL, ADD `shipping_date` DATE NULL DEFAULT NULL, ADD `shipping_tracking_number` VARCHAR(255) NULL DEFAULT NULL, ADD `shipping_weight` VARCHAR(255) NULL DEFAULT NULL, ADD `shipping_cost` DECIMAL(16,2) NULL DEFAULT NULL, ADD `shipping_notes` TEXT NULL DEFAULT NULL;');
                }
                update_option($opt, $val);

                break;

            case 'invoice_group':

                if (!db_column_exist('sys_invoices', 'group_id')) {
                    DB::unprepared('ALTER TABLE `sys_invoices` ADD `group_id` INT(11) UNSIGNED NOT NULL DEFAULT \'0\' AFTER `id`');
                }

                if(!db_table_exist('invoice_groups'))
                {
                    DB::schema()->create('invoice_groups', function ($table) {
                        $table->increments('id');
                        $table->string('name',255)->nullable();
                        $table->text('description')->nullable();
                        $table->unsignedInteger('created_by')->default(0);
                        $table->unsignedInteger('owner_id')->default(0);
                        $table->timestamps();
                    });
                }

                update_option($opt, $val);

                break;

            case 'invoice_single_service':

                if (!db_column_exist('sys_invoices', 'service_id')) {
                    DB::unprepared('ALTER TABLE `sys_invoices` ADD `service_id` INT(11) UNSIGNED NOT NULL DEFAULT \'0\' AFTER `id`');
                }

                update_option($opt, $val);

                break;

            default:
                if ($m != 'silent') {
                    _msglog('s', $_L['Settings Saved Successfully']);
                }

                if ($opt == 'maxmind_installed' && $val == '1' && !file_exists('storage/mmdb/GeoLite2-City.mmdb')) {
                    _msglog(
                        'e',
                        'Maxmind database- GeoLite2-City.mmdb was not found in storage/mmdb/'
                    );
                    echo 'failed';
                    exit();
                }

                if (update_option($opt, $val)) {
                    echo 'ok';
                } else {
                    echo 'failed';
                }
        }

        break;

    case 'api':
        $ui->assign('content_inner', inner_contents($config['c_cache']));
        $d = ORM::for_table('sys_api')->find_many();

        $ui->assign('d', $d);
        $ui->assign('api_url', APP_URL);

        $invoice = Invoice::first(); # Get a sample invoice

        view('api',[
            'invoice' => $invoice,
        ]);

        break;

    case 'api_post':
        $label = _post('label');
        if ($label == '') {
            r2(U . 'settings/api/', 'e', 'Label is Required');
        } else {
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $string = '';
            $random_string_length = '40';
            for ($i = 0; $i < $random_string_length; $i++) {
                $string .= $characters[rand(0, strlen($characters) - 1)];
            }

            $d = ORM::for_table('sys_api')->create();
            $d->label = $label;
            $d->ip = '';
            $d->apikey = $string;
            $d->save();
            r2(U . 'settings/api/', 's', $_L['API Access Added']);
        }

        break;

    case 'api_delete':
        $id = $routes[2];
        $d = ORM::for_table('sys_api')->find($id);
        if ($d) {
            $d->delete();

            r2(U . "settings/api/", 's', $_L['delete_successful']);
        }

        break;

    case 'api_regen':
        $id = $routes[2];
        $d = ORM::for_table('sys_api')->find($id);
        if ($d) {
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $string = '';
            $random_string_length = '40';
            for ($i = 0; $i < $random_string_length; $i++) {
                $string .= $characters[rand(0, strlen($characters) - 1)];
            }

            $d->apikey = $string;
            $d->save();

            r2(U . "settings/api/", 's', 'API Key Updated');
        }

        break;

    case 'plugin_force_remove':
        $pl = $routes[2];

        $d = ORM::for_table('sys_pl')
            ->where('c', $pl)
            ->first();
        if ($d) {
            $d->delete();
            r2(U . "dashboard/", 's', 'Plugin Successfully Removed.');
        }

        r2(U . "dashboard/", 's', 'Plugin Not Found.');

        break;

    case 'activate_license':
        view('settings_activate_license');

        break;

    case 'activate_license_post':
        $purchase_key = _post('purchase_key');

        updateOption('purchase_key', $purchase_key, true);

        r2(U . 'settings/about/');

        break;

    case 'logo-text':
        updateOption('logo_text', _post('logo_text'), true);

        $header_show_logo_as = _post('header_show_logo_as');

        if ($header_show_logo_as === '' || $header_show_logo_as === '0') {
            remove_option('header_show_logo_as');
        } else {
            update_option('header_show_logo_as', $header_show_logo_as);
        }

        r2(U . 'appearance/customize');

        break;

    case 'add_purchase_key':
        $purchase_key = $data['purchase_key'];

        update_option('purchase_key', $purchase_key);

        echo 'Purchase Key Saved.' . PHP_EOL;

        break;

    case 'check_update_post':
        $purchase_key = $data['purchase_key'];

        update_option('purchase_key', $purchase_key);

        $res = updateCheck($purchase_key);

        api_response($res);

        break;

    case 'backup_logo':
        header('Content-Type: application/json');

        if (APP_STAGE == 'Demo') {
            $a = [
                'continue' => 'No',
                'message' => 'This option is disabled in the demo mode.',
            ];

            echo json_encode($a);

            ib_close();
        }

        $file = 'storage/system/logo.png';
        $newfile = './logo.png';

        $message = '';
        $continue = 'No';

        if (!copy($file, $newfile)) {
            $message = "failed to copy $file";
        } else {
            $message = "File Copied: $file ...";
            $continue = 'Yes';
        }

        $a = [
            'continue' => $continue,
            'message' => $message,
        ];

        echo json_encode($a);

        ib_close();

        break;

    case 'backup_app':
        if (APP_STAGE == 'Demo') {
            $a = [
                'continue' => 'No',
                'message' => 'This option is disabled in the demo mode.',
            ];

            echo json_encode($a);

            ib_close();
        }

        $backup = new Backup();

        $backupDB = $backup->backupDB();

        $message = '';
        $continue = 'No';

        if ($backupDB['success']) {
            $continue = 'Yes';
            $message = $backupDB['message'];
        } else {
            $continue = 'No';
            $message = $backupDB['message'];
        }

        $a = [
            'continue' => $continue,
            'message' => $message,
        ];

        api_response($a);

        break;

    case 'get_latest':
        $message = '';
        $continue = 'No';

        $purchase_key = $config['purchase_key'];

        if ($purchase_key == '') {
            $a = [
                'continue' => 'No',
                'message' =>
                    'Purchase Code Not Found. Please save Purchase code before update...',
            ];

            api_response($a);
        }

        $arr = [
            'app_url' => APP_URL,
            'item_id' => 1,
            'purchase_key' => $purchase_key,
        ];

        $raw = ib_http_request(
            $update_server . '/create-download-link',
            'POST',
            $arr
        );

        $resp = json_decode($raw);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (property_exists($resp, 'success') && $resp->success !== null) {
                $success = $resp->success;
                if ($success == true) {
                    $a = [
                        'continue' => 'Yes',
                        'message' => $resp->message,
                        'link' => $resp->link,
                    ];

                    api_response($a);
                } else {
                    $a = [
                        'continue' => 'No',
                        'message' => $resp->message,
                    ];

                    api_response($a);
                }
            } else {
                $a = [
                    'continue' => 'No',
                    'message' => 'Unable to communicate download server.',
                ];

                api_response($a);
            }
        } else {
            $a = [
                'continue' => 'No',
                'message' => $raw,
            ];

            api_response($a);
        }

        break;

    case 'get_plugin':
        $msg = '';

        $pl_url = _post('pl_url');

        // check URL is correct

        if (filter_var($pl_url, FILTER_VALIDATE_URL) === false) {
            $msg .= 'Invalid URL.';
        }

        if ($msg == '') {
            r2(U . 'settings/plugins', 's', 'No valid plugin header found.');
        } else {
            r2(U . 'settings/plugins', 'e', $msg);
        }

        break;

    case 'url_rewrite':
        if (APP_STAGE == 'Demo') {
            r2(U . 'dashboard/', 'e', $_L['disabled_in_demo']);
        }

        $set = route(2);

        if ($set == 'yes') {

            $ui->assign('msg', 'Please wait...');

            view('activity');
        } else {
            $fs = new Filesystem();

            try {
                $fs->delete('.htaccess');
                update_option('url_rewrite', 0);
                r2(
                    APP_URL . '/?ng=settings/app/',
                    's',
                    $_L['Settings Saved Successfully']
                );
            } catch (Exception $e) {
                update_option('url_rewrite', 0);
                r2(
                    APP_URL . '/?ng=settings/app/',
                    's',
                    'An Error Occurred while removing .htaccess file. Error: ' .
                    $e->getMessage()
                );
            }
        }

        break;

    case 'url_rewrite_enable':
        update_option('url_rewrite', 1);

        echo 'URL rewrite enabled... <br> ';

        break;

    case 'url_rewrite_check':
        $resp = ib_http_request(U . 'settings/url_rewrite_is_ok/');

        if ($resp == 'ok') {
            // it's working

            echo 'ok';
        } else {
            // remove

            echo 'failed ' . U . 'settings/url_rewrite_is_ok/';
        }

        break;

    case 'url_rewrite_is_ok':
        echo 'ok';

        break;

    case 'set_color':

        if (APP_STAGE == 'Demo') {
            appFlashMessage('This feature is not available in Demo Mode.', 'error');
            exit();
        }

        $available_color = [
            'light_blue',
            'indigo_blue',
            'blue_extra',
            'purple',
            'dark_mode',
            'light_mode',
            'dark',
            'california',
            'nordic',
            'tokyo',
            'sydney',
            'brazil',
            'mumbai',
            'istanbul',
            'vancouver',
            'bali',
            'singapore',
            'barcelona',
            'london',
            'dubai',
        ];

        $color = route(2);

        if (in_array($color, $available_color)) {
            update_option('nstyle', $color);
        }

        switch ($color) {
            case 'light_blue':
                update_option('graph_primary_color', '2196f3');
                update_option('graph_secondary_color', 'eb3c00');

                break;

            case 'purple':
                update_option('graph_primary_color', '7CB5EC');
                update_option('graph_secondary_color', '434348');

                break;

            case 'indigo_blue':
                update_option('graph_primary_color', '002868');
                update_option('graph_secondary_color', 'dc171d');

                break;

            default:
                update_option('graph_primary_color', '2196f3');
                update_option('graph_secondary_color', 'eb3c00');
        }

        $logo_inverse_for = ['light_blue', 'purple', 'indigo_blue'];

        if (in_array($color, $logo_inverse_for)) {
            update_option('top_bar_is_dark', 1);
        } else {
            update_option('top_bar_is_dark', 0);
        }

        break;

    case 'recaptcha_post':
        if (APP_STAGE == 'Demo') {
            r2(U . 'settings/app/', 'e', "This option is disabled in Demo.");
        }
        $data = sp_purify_data($request->all());

        update_option('recaptcha', $data['recaptcha']);
        update_option('recaptcha_sitekey', $data['recaptcha_sitekey']);
        update_option('recaptcha_secretkey', $data['recaptcha_secretkey']);

        r2(U . 'settings/app', 's', $_L['Settings Saved Successfully']);

        break;

    case 'custom_scripts':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'appearance/customize/',
                'e',
                "This option is disabled in Demo."
            );
        }

        update_option('header_scripts', $data['header_scripts']);
        update_option('footer_scripts', $data['footer_scripts']);

        r2(
            U . 'appearance/customize/',
            's',
            $_L['Settings Saved Successfully']
        );

        break;

    case 'update_admin_note':
        $notes = $data['notes'];

        $user->notes = $notes;
        $user->save();

        echo $_L['Data Updated'];

        break;

    case 'roles':
        $roles = Role::all();

        $ui->assign('roles', $roles);

        view('settings_roles');

        break;

    case 'add_role':
        $accounting_permission_map = [
            'Accounting Dashboard' => 'accounting_dashboard',
            'Accounting GL Accounts' => 'accounting_gl_accounts',
            'Accounting Journal Entries' => 'accounting_journal_entries',
            'Accounting Accounts Receivable' => 'accounting_ar',
            'Accounting Accounts Payable' => 'accounting_ap',
            'Accounting Bank Reconciliation' => 'accounting_bank_reconciliation',
            'Accounting Financial Reports' => 'accounting_reports',
            'Accounting Tax & VAT' => 'accounting_tax_vat',
            'Accounting Settings' => 'accounting_settings',
        ];

        foreach ($accounting_permission_map as $permission_name => $permission_shortname) {
            addPermission($permission_name, $permission_shortname);
        }

        $permissions = Permission::all();
        $roles = Role::all();

        $ui->assign('permissions', $permissions);
        $ui->assign('roles', $roles);

        view('settings_add_role');

        break;

    case 'add_role_post':
        $msg = '';

        $data = ib_posted_data();

        $rname = _post('rname');

        if ($rname == 'Admin') {
            $msg .= 'Role name "Admin" is not allowed. <br>';
        }

        if ($rname == '') {
            $msg .= 'Role name is required. <br>';
        }

        if (Role::where('rname', $rname)->first()) {
            $msg .= 'Role already exist. Use Different Role Name. <br>';
        }

        if ($msg == '') {
            $role = new Role();
            $role->rname = $rname;
            $role->save();

            $rid = $role->id;

            $accounting_permission_map = [
                'Accounting Dashboard' => 'accounting_dashboard',
                'Accounting GL Accounts' => 'accounting_gl_accounts',
                'Accounting Journal Entries' => 'accounting_journal_entries',
                'Accounting Accounts Receivable' => 'accounting_ar',
                'Accounting Accounts Payable' => 'accounting_ap',
                'Accounting Bank Reconciliation' => 'accounting_bank_reconciliation',
                'Accounting Financial Reports' => 'accounting_reports',
                'Accounting Tax & VAT' => 'accounting_tax_vat',
                'Accounting Settings' => 'accounting_settings',
            ];

            foreach ($accounting_permission_map as $permission_name => $permission_shortname) {
                addPermission($permission_name, $permission_shortname);
            }

            $permissions = Permission::all();

            foreach ($permissions as $p) {
                $d = ORM::for_table('sys_staffpermissions')->create();

                $shortname = $p['shortname'];

                $d->rid = $rid;
                $d->pid = $p['id'];
                $d->shortname = $shortname;

                $view = $shortname . '_view';
                $edit = $shortname . '_edit';
                $create = $shortname . '_create';
                $delete = $shortname . '_delete';

                $all_data = $shortname . '_all_data';

                $d->can_view = isset($data[$view]) ? 1 : 0;

                $d->can_edit = isset($data[$edit]) ? 1 : 0;

                $d->can_create = isset($data[$create]) ? 1 : 0;

                $d->can_delete = isset($data[$delete]) ? 1 : 0;

                $d->all_data = isset($data[$all_data]) ? 1 : 0;

                $d->save();
            }

            r2(U . 'settings/roles/', 's', $_L['added_successful']);
        } else {
            r2(U . 'settings/add_role/', 'e', $msg);
        }

        break;

    case 'edit_role':
        $id = route(2);

        $role = Role::find($id);

        if ($role) {
            $accounting_permission_map = [
                'Accounting Dashboard' => 'accounting_dashboard',
                'Accounting GL Accounts' => 'accounting_gl_accounts',
                'Accounting Journal Entries' => 'accounting_journal_entries',
                'Accounting Accounts Receivable' => 'accounting_ar',
                'Accounting Accounts Payable' => 'accounting_ap',
                'Accounting Bank Reconciliation' => 'accounting_bank_reconciliation',
                'Accounting Financial Reports' => 'accounting_reports',
                'Accounting Tax & VAT' => 'accounting_tax_vat',
                'Accounting Settings' => 'accounting_settings',
            ];

            foreach ($accounting_permission_map as $permission_name => $permission_shortname) {
                addPermission($permission_name, $permission_shortname);
            }

            $permissions = Permission::all();
            $ui->assign('permissions', $permissions);
            $ui->assign('role', $role);

            $sp = ORM::for_table('sys_staffpermissions')
                ->where('rid', $id)
                ->find_array();

            view('settings_edit_role');
        } else {
            echo 'Role Not Found.';
        }

        break;

    case 'edit_role_post':
        $id = _post('rid');

        $msg = '';

        $data = ib_posted_data();

        $role = Role::find($id);

        $c_rname = $role->rname;

        if ($role) {
            $rid = $id;

            $rname = _post('rname');

            if ($rname == 'Admin') {
                $msg .= 'Role name "Admin" is not allowed. <br>';
            }

            if ($rname == '') {
                $msg .= 'Role name is required. <br>';
            }

            if ($c_rname != $rname && Role::where('rname', $rname)->first()) {
                $msg .= 'Role already exist. Use Different Role Name. <br>';
            }

            if ($msg == '') {
                $role->rname = $rname;

                $role->save();

                $p = ORM::for_table('sys_staffpermissions')
                    ->where('rid', $id)
                    ->delete_many();

                $accounting_permission_map = [
                    'Accounting Dashboard' => 'accounting_dashboard',
                    'Accounting GL Accounts' => 'accounting_gl_accounts',
                    'Accounting Journal Entries' => 'accounting_journal_entries',
                    'Accounting Accounts Receivable' => 'accounting_ar',
                    'Accounting Accounts Payable' => 'accounting_ap',
                    'Accounting Bank Reconciliation' => 'accounting_bank_reconciliation',
                    'Accounting Financial Reports' => 'accounting_reports',
                    'Accounting Tax & VAT' => 'accounting_tax_vat',
                    'Accounting Settings' => 'accounting_settings',
                ];

                foreach ($accounting_permission_map as $permission_name => $permission_shortname) {
                    addPermission($permission_name, $permission_shortname);
                }

                $permissions = Permission::all();

                foreach ($permissions as $p) {
                    $d = ORM::for_table('sys_staffpermissions')->create();

                    $shortname = $p['shortname'];

                    $d->rid = $rid;
                    $d->pid = $p['id'];
                    $d->shortname = $shortname;

                    $view = $shortname . '_view';
                    $edit = $shortname . '_edit';
                    $create = $shortname . '_create';
                    $delete = $shortname . '_delete';

                    $all_data = $shortname . '_all_data';

                    $d->can_view = isset($data[$view]) ? 1 : 0;

                    $d->can_edit = isset($data[$edit]) ? 1 : 0;

                    $d->can_create = isset($data[$create]) ? 1 : 0;

                    $d->can_delete = isset($data[$delete]) ? 1 : 0;

                    $d->all_data = isset($data[$all_data]) ? 1 : 0;

                    $d->save();
                }

                r2(
                    U . 'settings/edit_role/' . $id,
                    's',
                    $_L['edit_successful']
                );
            } else {
                r2(U . 'settings/edit_role/' . $id, 'e', $msg);
            }
        } else {
            echo 'Role Not Found.';
        }

        break;

    case 'currencies':
        $currency = Currency::first();
        $currencies = Currency::all();

        if (!$currency) {
            $n = new Currency();

            $n->iso_code = $config['home_currency'];
            $n->cname = $config['home_currency'];
            $n->symbol = $config['currency_code'];
            $n->save();
        }

        $ui->assign('currencies', $currencies);

        view('settings_currencies', []);

        break;

    case 'modal_add_currency':
        $home_currency = homeCurrency();

        $id = route(2);

        $currency = false;

        if ($id != '') {
            $id = str_replace('ae', '', $id);
            $id = str_replace('be', '', $id);

            $currency = Currency::find($id);
        }

        $val = [];

        if ($currency) {
            $f_type = 'edit';
            $val['code'] = $currency->cname;
            $val['symbol'] = $currency->symbol;
            $val['rate'] = $currency->rate;
            $val['cid'] = $currency->id;
        } else {
            $f_type = 'create';
            $val['code'] = '';
            $val['symbol'] = '';
            $val['rate'] = '1.0000';
            $val['cid'] = '0';
        }

        $ui->assign('f_type', $f_type);
        $ui->assign('val', $val);

        view('modal_add_currency', [
            'home_currency' => $home_currency,
        ]);

        break;

    case 'add_currency_post':
        $msg = '';

        $iso_code = _post('iso_code');
        $cname = _post('iso_code');

        $symbol = _post('symbol');

        $rate = _post('rate');

        if (strlen($iso_code) != 3) {
            $msg .=
                'Invalid Currency Code. Please use 3 digit ISO code for your currency. <br>';
        }

        if (!is_numeric($rate)) {
            $msg .= 'Invalid Rate';
        }

        $f_type = _post('f_type');

        if ($f_type == 'edit') {
            if ($msg == '') {
                $cid = _post('cid');
                $currency = Currency::find($cid);

                if ($currency) {
                    $currency->cname = $iso_code;
                    $currency->iso_code = $iso_code;
                    $currency->symbol = '';
                    $currency->rate = $rate;

                    $currencies_total = Currency::all()->count();

                    if ($currencies_total == 1) {
                        update_option('home_currency', $iso_code);

                        $currencies = Currency::getAllCurrencies();

                        if (isset($currencies[$iso_code])) {
                            update_option(
                                'currency_code',
                                $currencies[$iso_code]['symbol']
                            );
                            update_option(
                                'dec_point',
                                $currencies[$iso_code]['decimal_mark']
                            );
                            update_option(
                                'thousands_sep',
                                $currencies[$iso_code]['thousands_separator']
                            );

                            if (
                                $currencies[$iso_code]['symbol_first'] == true
                            ) {
                                update_option('currency_symbol_position', 'p');
                            } else {
                                update_option('currency_symbol_position', 's');
                            }
                        }
                    }

                    //

                    $currency->save();

                    $id = $currency->id;

                    echo $id;
                } else {
                    echo 'An Error Occurred';
                }
            } else {
                echo $msg;
            }
        } else {
            $check = Currency::where('cname', $cname)->first();

            if ($check) {
                $msg .= 'Currency already exist <br>';
            }

            if ($msg == '') {
                $currency = new Currency();

                $currency->cname = $iso_code;
                $currency->iso_code = $iso_code;
                $currency->symbol = $symbol;
                $currency->rate = $rate;

                $currency->save();

                $id = $currency->id;

                echo $id;
            } else {
                echo $msg;
            }
        }

        break;

    case 'make_base_currency':
        $id = route(2);
        $id = str_replace('b', '', $id);

        $c = Currency::find($id);

        if ($c) {
            update_option('home_currency', $c->cname);

            $currencies = Currency::getAllCurrencies();

            if (isset($currencies[$c->cname])) {
                update_option(
                    'currency_code',
                    $currencies[$c->cname]['symbol']
                );
                update_option(
                    'dec_point',
                    $currencies[$c->cname]['decimal_mark']
                );
                update_option(
                    'thousands_sep',
                    $currencies[$c->cname]['thousands_separator']
                );

                if ($currencies[$c->cname]['symbol_first'] == true) {
                    update_option('currency_symbol_position', 'p');
                } else {
                    update_option('currency_symbol_position', 's');
                }
            }
        }

        r2(U . 'settings/currencies/', 's', 'Currency Updated Successfully.');

        break;

    case 'other-settings-post':
        $gmap_api_key = _post('gmap_api_key');
        $slack_webhook_url = _post('slack_webhook_url');

        update_option('gmap_api_key', $gmap_api_key);
        update_option('slack_webhook_url', $slack_webhook_url);

        update_option('customer_code_prefix', _post('customer_code_prefix'));
        update_option('income_code_prefix', _post('income_code_prefix'));
        update_option(
            'customer_code_current_number',
            _post('customer_code_current_number')
        );
        update_option(
            'income_code_current_number',
            _post('income_code_current_number')
        );
        update_option('expense_code_prefix', _post('expense_code_prefix'));
        update_option(
            'expense_code_current_number',
            _post('expense_code_current_number')
        );
        update_option('contact_extra_field', _post('contact_extra_field'));

        update_option('invoice_code_prefix', _post('invoice_code_prefix'));
        update_option(
            'invoice_code_current_number',
            _post('invoice_code_current_number')
        );
        update_option('purchase_code_prefix', _post('purchase_code_prefix'));
        update_option(
            'purchase_code_current_number',
            _post('purchase_code_current_number')
        );
        update_option('quotation_code_prefix', _post('quotation_code_prefix'));
        update_option(
            'quotation_code_current_number',
            _post('quotation_code_current_number')
        );

        update_option('ticket_code_prefix', _post('ticket_code_prefix'));
        update_option(
            'ticket_code_current_number',
            _post('ticket_code_current_number')
        );

        r2(U . 'settings/app/', 's', $_L['Data Updated']);

        break;

    case 'units':
        $units = ORM::for_table('sys_units')
            ->order_by_asc('sorder')
            ->find_array();
        $ui->assign('units', $units);
        view('settings_units');

        break;

    case 'modal_unit':
        $id = route(2);

        $unit = false;

        if ($id != '') {
            $id = str_replace('ae', '', $id);
            $id = str_replace('be', '', $id);

            $unit = ORM::for_table('sys_units')->find($id);
        }

        $val = [];

        if ($unit) {
            $f_type = 'edit';
            $val['uid'] = $unit->id;
            $val['type'] = $unit->type;
            $val['name'] = $unit->name;
            $val['reference'] = $unit->reference;
            $val['conversion_factor'] = $unit->conversion_factor;
        } else {
            $f_type = 'create';
            $val['uid'] = '';
            $val['type'] = '';
            $val['name'] = '';
            $val['reference'] = '';
            $val['conversion_factor'] = '';
        }

        $ui->assign('f_type', $f_type);
        $ui->assign('val', $val);

        view('modal_units');

        break;

    case 'add_unit':
        $name = _post('name');
        $type = _post('type');
        $uid = _post('uid');

        if ($name == '' || $type == '') {
            echo $_L['All Fields are Required'];
            exit();
        }

        $f_type = _post('f_type');

        if ($f_type == 'edit') {
            $d = ORM::for_table('sys_units')->find($uid);
            $d->name = $name;
            $d->type = $type;
            $d->save();
            echo $d->id();
        } else {
            $d = ORM::for_table('sys_units')->create();
            $d->name = $name;
            $d->type = $type;
            $d->save();
            echo $d->id();
        }

        break;

    case 'clone_email_template':
        $id = route(2);

        $d = ORM::for_table('sys_email_templates')->find($id);
        if ($d) {
            $c = ORM::for_table('sys_email_templates')->create();

            $tplname = 'Custom';

            for ($x = 2; $x <= 30; $x++) {
                $e = ORM::for_table('sys_email_templates')
                    ->where('tplname', $d->tplname . ': ' . $x)
                    ->first();
                if ($e) {
                    continue;
                } else {
                    $tplname = $d->tplname . ' ' . $x;
                    break;
                }
            }

            $c->tplname = $tplname;
            $c->subject = $d->subject;
            $c->message = $d->message;
            $c->send = $d->send;
            $c->core = 'No';
            $c->hidden = $d->hidden;
            $c->save();
        }

        r2(U . 'settings/email-templates', 's', $_L['added_successful']);

        break;

    case 'expense-types':
        $e = ORM::for_table('expense_types')
            ->order_by_asc('sorder')
            ->find_array();

        $ui->assign('e', $e);

        view('settings_expense_types');

        break;

    case 'add_expense_type':
        $expense_type = _post('expense_type');

        if ($expense_type == '') {
            echo 'Exepnese Type is required';
        } else {
            $e = new ExpenseType();
            $e->name = $expense_type;
            $e->save();

            echo $e->id;
        }

        break;

    case 'e_expense_type_edit':
        $id = _post('id');
        $id = str_replace('e', '', $id);
        $e_expense_type = _post('e_expense_type');

        $d = ORM::for_table('expense_types')->find($id);

        if ($d) {
            $o_name = $d->name;

            ORM::execute(
                "update sys_transactions set sub_type='$e_expense_type' where sub_type='$o_name'"
            );

            $d->name = $e_expense_type;

            $d->save();

            echo $d->id;
        }

        break;

    case 'tax-make-default':
        $id = route(2);

        $tax = Tax::find($id);

        if ($tax) {
            $prev_tax = Tax::where('is_default', '1')->first();

            if ($prev_tax) {
                $prev_tax->is_default = 0;
                $prev_tax->save();
            }

            $tax->is_default = 1;

            $tax->save();
        }

        r2(U . 'tax/list', 's', $_L['Data Updated']);

        break;

    case 'set_build':
        $build = route(2);

        update_option('build', $build);

        echo 'ok';

        break;

    case 'set_notify':
        $opt = _post('opt');

        $val = _post('val');

        if ($opt == 'email_notify') {
            $user->email_notify = $val;
        } elseif ($opt == 'sms_notify') {
            $user->sms_notify = $val;
        } else {
        }

        $user->save();

        echo 1;

        break;

    case 'email-test':
        $validator = new Validator();
        $data = $request->all();
        $validation = $validator->validate($data, [
            'email' => 'required|email',
        ]);

        if ($validation->fails()) {
            $message = '';
            foreach ($validation->errors()->all() as $key => $value) {
                $message .= $value . ' <br> ';
            }
            responseWithError($message);
        } else {

            $msg = 'Sending and email with Test Invoice Attachment....' . PHP_EOL;

            $email = $data['email'];

            $invoice = Invoice::first();

            if (!$invoice) {
                $msg .= 'You don\'t have an Invoice to test.... ' . PHP_EOL;
                $msg .= 'Please create an Invoice and try again.... ' . PHP_EOL;
                echo '<textarea class="form-control" rows="15">'.$msg.'</textarea>';
            }

            if ($invoice) {

                $dispid = $invoice->cn != '' ? $invoice->cn : $invoice->id;
                $in = $invoice->invoicenum . $dispid;

                $attach_pdf = _post('attach_pdf');
                $attachment_path = '';
                $attachment_file = '';

                try {
                    Invoice::pdf($invoice->id, 'store');
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $attachment_path = 'storage/temp/' . __('Invoice') . '_' . $in . '.pdf';
                $attachment_file = 'Invoice_' . $in . '.pdf';

                $subject = 'A Test email!';
                $messageBody = 'Test body with an attachment';

                $email_config = EmailConfig::first();

                if ($email_config) {
                    if ($email_config->method === 'smtp') {
                        $dsn = sprintf(
                            'smtp://%s:%s@%s:%d',
                            urlencode($email_config->username),
                            urlencode($email_config->password),
                            $email_config->host,
                            $email_config->port
                        );

                        if ($email_config->secure === 'tls') {
                            $dsn .= '?encryption=tls';
                        } elseif ($email_config->secure === 'ssl') {
                            $dsn .= '?encryption=ssl';
                        }
                    } else {
                        $dsn = 'sendmail://default';
                    }

                    ray($dsn);

                    $transport = Transport::fromDsn($dsn);

                    $mailer = new Mailer($transport);

                    $email = (new Email())
                        ->from(new Address($config['sysEmail'], $config['CompanyName']))
                        ->to(new Address($email, 'Test'))
                        ->subject($subject)
                        ->html($messageBody);

                    if ($attach_pdf && file_exists($attachment_path)) {
                        $email->attachFromPath($attachment_path, $attachment_file, 'application/pdf');
                    }

                    try {
                        $mailer->send($email);
                        echo "Email sent successfully.";
                    } catch (\Exception $e) {
                        echo '<textarea class="form-control" rows="15">'.$e->getMessage().'</textarea>';
                    }
                }


            }
        }

        break;

    case 'client-auth-page-widget':
        \view('settings_client_auth_page_widget', []);

        break;

    case 'client-auth-page-widget-save':
        $data = $request->all();

        $widget = Widget::where('type', 'client-auth-page-widget')->first();

        if (!$widget) {
            $widget = new Widget();
            $widget->name = 'Client auth page widget';
            $widget->type = 'client-auth-page-widget';
        }

        if ($widget) {
            $widget->content = $data['content'] ?? '';
            $widget->save();
        }

        echo 1;

        break;

    case 'save-font-size':
        $size = _post('size');

        if (
            in_array($size, [
                'root-text',
                'root-text-lg',
                'root-text-xl',
                'root-text-sm',
            ])
        ) {
            update_option('font_size', $size);
        }

        break;

    case 'set-global-notification-email':
        $email = _post('email');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            update_option('global_notifications_email', $email);
        }
        break;

    case 'set-user-ticket-department':
        if (!$user->roleid) {
            $department_id = _post('department_id');
            $department_id = str_replace('department_', '', $department_id);
            $department_id = (int) $department_id;
            $selected_user = User::find(_post('user_id'));
            $status = (int) _post('status');
            if ($selected_user) {
                $department = TicketDepartment::find($department_id);
                if ($department) {
                    $exist = Relation::where('type', 'staff_departments')
                        ->where('type', 'staff_departments')
                        ->where('source_id', $selected_user->id)
                        ->where('target_id', $department_id)
                        ->first();
                    if ($status === 0 && $exist) {
                        $exist->delete();
                        return;
                    }
                    if (!$exist) {
                        $relation = new Relation();
                        $relation->type = 'staff_departments';
                        $relation->source_id = $selected_user->id;
                        $relation->target_id = $department_id;
                        $relation->save();
                    }
                }
            }
        }

        break;

    case 'remove-purchase-key':
        removeOption('purchase_key');
        redirect_to('settings/about');
        break;

    case 'clear-update-log':
        removeOption('update_log');
        redirect_to('settings/about');
        break;

    case 'about':
        if (!defined('DISABLE_ABOUT_PAGE')) {
            $ui->assign('app_stage', APP_STAGE);

            $latest_version = null;
            $manifest = require APP_SYSTEM_PATH . '/manifest.php';
            try {
                $response = (new Http())
                    ->withOptions([
                        'verify' => false,
                    ])
                    ->get(
                        $manifest['system']['update_url'] .
                        '/version-check/' .
                        $manifest['system']['item_api_name'],
                    );
                $result = $response->json();
                if (!empty($result['version'])) {
                    $latest_version = $result['version'];
                }
            } catch (\Exception $exception) {
                ray($exception->getMessage());
            }

            $installed_version = $config['version'] ?? '1.0.0'; // default version


            $update_available = false;

            if ($latest_version) {
                $update_available = SemverComparator::greaterThan(
                    $latest_version,
                    $installed_version
                );
            }

            $update_step = $config['update_step'] ?? 0;

            \view('about', [
                'latest_version' => $latest_version,
                'installed_version' => $installed_version,
                'update_available' => $update_available,
                'update_step' => $update_step,
            ]);
        }
        else{
            abort(404);
        }



        break;

    case 'update':
        $manifest = require APP_SYSTEM_PATH . '/manifest.php';
        $update_step = $config['update_step'] ?? 0;
        switch ($update_step) {
            case 0:
                $result = Update::downloadTheLatestVersion(
                    $config,
                    $manifest,
                    $user
                );
                if ($result['success']) {
                    $update_step = 1;
                    update_option('update_step', $update_step);
                }
                if (!empty($result['message'])) {
                    $current_update_log = $config['update_log'] ?? '';
                    $message =
                        $current_update_log . PHP_EOL . $result['message'];
                    update_option('update_log', $message);
                }

                break;
            case 1:
                if (!empty($config['latest_version_file_name'])) {
                    $result = Update::extractTheLatestVersion($config);
                    if ($result['success']) {
                        $update_step = 2;
                        update_option('update_step', $update_step);
                    }
                    if (!empty($result['message'])) {
                        $current_update_log = $config['update_log'] ?? '';
                        $message =
                            $current_update_log . PHP_EOL . $result['message'];
                        update_option('update_log', $message);
                    }
                } else {
                    $update_step = 2;
                    update_option('update_step', $update_step);
                }
                break;
            case 2:
                Update::databaseSchema($config);
                $update_step = 3;
                update_option('update_step', $update_step);

                break;
            case 3:
                Update::cleanup($config);
                update_option('version', $manifest['system']['version']);
                break;
        }
        redirect_to('settings/about');
        break;

    case 'update-cancel':
        Update::cleanup($config);
        redirect_to('settings/about');
        break;

    case 'activate':

        \view('activate');

        break;

    case 'make-activation':

        $fullname = $user->fullname;
        $fullname_array = explode(' ', $fullname);
        $firstname = $fullname_array[0] ?? 'N';
        $lastname = $fullname_array[1] ?? 'A';
        $activate = (new Http())->post('https://app.stackpie.com/v3/item-register',[
            'api_public_key' => '1d6d4c99-ef54-4124-9c43-519ba161fc8c',
            'item_id' => '3',
            'purchase_type' => 'direct',
            'purchase_key' => _post('purchase_key'),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $user->username,
            'url' => APP_URL,
            'ip' => get_client_ip(),
        ]);

        $activate = $activate->json();

        ray($activate);

        if(!empty($activate))
        {

        }

        break;


    case 'save-invoice-group':

        $data = request()->all();

        if(!empty($data['id'])){
            $group = InvoiceGroup::find($data['id']);
        }
        else{
            $group = new InvoiceGroup();
        }

        $group->name = $data['name'];
        $group->save();

        redirect_to('settings/invoice-groups');


        break;

    case 'delete-invoice-group':
        $id = (int) route(2);
        $group = InvoiceGroup::find($id);
        if($group){
            $group->delete();
        }
        redirect_to('settings/invoice-groups');
        break;

    case 'landing-page':


        \view('settings_landingpage', [

        ]);

        break;

    case 'privacy-policy-page':

        $privacy= PrivacyPolicy::first();

        \view('settings_privacy_policy', [

            'privacy' =>  $privacy,

        ]);

        break;

    case 'privacy-policy-page-save':

        $data = request()->all();

        if(!empty($data['id'])){
            $privacy = PrivacyPolicy::find($data['id']);
        }
        else{
            $privacy = new PrivacyPolicy();
        }

        $privacy->title = $data['title'];
        $privacy->description = $data['description'];
        $privacy->save();

        redirect_to('settings/privacy-policy-page');

        break;

    case 'terms-page':

        $terms= Terms::first();

        \view('settings_terms', [

            'terms' =>  $terms,

        ]);

        break;

    case 'terms-page-save':

        $data = request()->all();

        if(!empty($data['id'])){
            $privacy = Terms::find($data['id']);
        }
        else{
            $privacy = new Terms();
        }

        $privacy->title = $data['title'];
        $privacy->description = $data['description'];
        $privacy->save();

        redirect_to('settings/terms-page');

        break;

    case 'cookie-page':

        $terms= CookiePolicy::first();

        \view('settings_cookie', [

            'terms' =>  $terms,

        ]);

        break;

    case 'cookie-page-save':

        $data = request()->all();

        if(!empty($data['id'])){
            $privacy = CookiePolicy::find($data['id']);
        }
        else{
            $privacy = new CookiePolicy();
        }

        $privacy->title = $data['title'];
        $privacy->description = $data['description'];
        $privacy->save();

        redirect_to('settings/cookie-page');

        break;

    case 'contact-page':

        $terms= ContactSection::first();

        \view('settings_contact_section', [

            'privacy' =>  $terms,

        ]);

        break;


    case 'contact-page-save':

        $data = request()->all();

        if(!empty($data['id'])){
            $privacy = ContactSection::find($data['id']);
        }
        else{
            $privacy = new ContactSection();
        }

        $privacy->title = $data['title'];
        $privacy->address_1 = $data['address_1'];
        $privacy->phone_number = $data['phone_number'];
        $privacy->email = $data['email'];
        $privacy->youtube = $data['youtube'];
        $privacy->facebook = $data['facebook'];
        $privacy->twitter = $data['twitter'];
        $privacy->save();

        redirect_to('settings/contact-page');

        break;


    case 'home-page':

        $terms= ContactSection::first();
        $hero= LandingPage::first();

        \view('settings_homepage', [

            'terms' =>  $terms,
            'hero' =>  $hero,
        ]);

        break;

    case 'home-page-hero-save':


        $hero = LandingPage::first();
        if(!$hero){
            $hero = new LandingPage();
        }

        $hero->hero_title = $data['hero_title'];
        $hero->hero_subtitle = $data['hero_subtitle'];
        $hero->hero_paragraph = $data['hero_paragraph'];


        $hero->save();

        redirect_to('settings/home-page');

        break;

    case 'home-page-feature-one-save':


        $hero = LandingPage::first();
        if(!$hero){
            $hero = new LandingPage();
        }

        $hero->feature1_title = $data['feature1_title'];
        $hero->feature1_subtitle = $data['feature1_subtitle'];
        $hero->feature1_one = $data['feature1_one'];
        $hero->feature1_one_paragraph = $data['feature1_one_paragraph'];
        $hero->feature1_two = $data['feature1_two'];
        $hero->feature1_two_paragraph = $data['feature1_two_paragraph'];
        $hero->feature1_three = $data['feature1_three'];
        $hero->feature1_three_paragraph = $data['feature1_three_paragraph'];
        $hero->feature1_four = $data['feature1_four'];
        $hero->feature1_four_paragraph = $data['feature1_four_paragraph'];
        $hero->feature1_five = $data['feature1_five'];
        $hero->feature1_five_paragraph = $data['feature1_five_paragraph'];
        $hero->feature1_six = $data['feature1_six'];
        $hero->feature1_six_paragraph = $data['feature1_six_paragraph'];
        $hero->save();

        redirect_to('settings/home-page');

        break;



    case 'home-page-story-one-save':

//        $data = request()->all();

        $hero = LandingPage::first();
        if(!$hero){
            $hero = new LandingPage();
        }


        $hero->story1_title = $data['story1_title'];
        $hero->story1_paragrapgh = $data['story1_paragrapgh'];

        $hero->save();

        redirect_to('settings/home-page');

        break;


    case 'home-page-story-two-save':


        $hero = LandingPage::first();
        if(!$hero){
            $hero = new LandingPage();
        }

        $hero->story2_title = $data['story2_title'];
        $hero->story2_paragrapgh = $data['story2_paragrapgh'];

        $hero->save();

        redirect_to('settings/home-page');

        break;


    case 'home-page-calltoaction-save':


        $hero = LandingPage::first();
        if(!$hero){
            $hero = new LandingPage();
        }

        $hero->calltoaction_title = $data['calltoaction_title'];
        $hero->calltoaction_subtitle = $data['calltoaction_subtitle'];

        $hero->save();

        redirect_to('settings/home-page');

        break;

    case 'save-settings':

        $data = request()->all();

        if(isset($data['openai_api_key']))
        {
            updateOption('openai_api_key', ($data['openai_api_key'] ?? ''), true);
        }

        if(isset($data['openai_api_base_url']))
        {
            updateOption('openai_api_base_url', ($data['openai_api_base_url'] ?? ''), true);
        }

        if(isset($data['openai_api_model']))
        {
            updateOption('openai_api_model', ($data['openai_api_model'] ?? ''), true);
        }

        if(isset($data['anthropic_api_key']))
        {
            updateOption('anthropic_api_key', ($data['anthropic_api_key'] ?? ''), true);
        }

        if (isset($data['google_maps_api_key'])) {
            updateOption('google_maps_api_key', $data['google_maps_api_key'], true);
        }

        redirect_back();

        break;

    default:
        echo 'action not defined';
        break;
}
