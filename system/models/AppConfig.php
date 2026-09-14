<?php
use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table = 'sys_appconfig';
    public $timestamps = false;

    public static function migrateZatcaOptionsUsingPdo()
    {
        $pdo = self::pdo();

        if (!$pdo || !self::tableExists($pdo, 'sys_appconfig')) {
            return;
        }

        self::ensureConfigStorageColumns($pdo);

        $options = [
            'zatca_enabled' => '0',
            'zatca_phase2_enabled' => '0',
            'zatca_environment' => 'sandbox',
            'zatca_api_base_url' => '',
            'zatca_invoice_type' => 'simplified',
            'zatca_seller_name' => '',
            'zatca_vat_number' => '',
            'zatca_seller_crn' => '',
            'zatca_building_no' => '',
            'zatca_street_name' => '',
            'zatca_district' => '',
            'zatca_city' => '',
            'zatca_postal_code' => '',
            'zatca_country_code' => 'SA',
            'zatca_csr_content' => '',
            'zatca_otp' => '',
            'zatca_binary_security_token' => '',
            'zatca_secret' => '',
            'zatca_compliance_request_id' => '',
            'zatca_private_key_passphrase' => '',
            'zatca_private_key' => '',
            'zatca_certificate' => '',
            'zatca_compliance_csid' => '',
            'zatca_compliance_secret' => '',
            'zatca_production_csid' => '',
            'zatca_production_binary_security_token' => '',
            'zatca_production_secret' => '',
        ];

        foreach ($options as $setting => $value) {
            self::ensureOption($pdo, $setting, $value);
        }
    }

    public static function saveZatcaOptionsUsingPdo(array $options)
    {
        $pdo = self::pdo();

        if (!$pdo || !self::tableExists($pdo, 'sys_appconfig')) {
            return false;
        }

        self::ensureConfigStorageColumns($pdo);

        foreach ($options as $setting => $value) {
            self::upsertOption($pdo, (string) $setting, (string) $value);
        }

        return true;
    }

    private static function ensureConfigStorageColumns(PDO $pdo)
    {
        // Ensure setting column can hold long option keys like
        // zatca_production_binary_security_token.
        $settingColumn = self::getColumnMeta($pdo, 'sys_appconfig', 'setting');
        if (!empty($settingColumn)) {
            $dataType = strtolower((string) ($settingColumn['DATA_TYPE'] ?? ''));
            $maxLength = (int) ($settingColumn['CHARACTER_MAXIMUM_LENGTH'] ?? 0);

            if ($dataType !== 'varchar' || $maxLength < 191) {
                $pdo->exec(
                    'ALTER TABLE `sys_appconfig` MODIFY COLUMN `setting` VARCHAR(191) NOT NULL'
                );
            }
        }

        // Ensure value column supports PEM/certificate payloads.
        // ZATCA FIX: Make NOT NULL with empty string default to prevent recovery failures
        $valueColumn = self::getColumnMeta($pdo, 'sys_appconfig', 'value');
        if (!empty($valueColumn)) {
            $dataType = strtolower((string) ($valueColumn['DATA_TYPE'] ?? ''));
            $isNullable = (string) ($valueColumn['IS_NULLABLE'] ?? 'YES');
            
            $needsAlter = false;
            if (!in_array($dataType, ['text', 'mediumtext', 'longtext'])) {
                $needsAlter = true;
            }
            if ($isNullable === 'YES') {
                $needsAlter = true;
            }
            
            if ($needsAlter) {
                $pdo->exec(
                    "ALTER TABLE `sys_appconfig` MODIFY COLUMN `value` MEDIUMTEXT NOT NULL"
                );
            }
        }
    }

    private static function pdo()
    {
        try {
            return ORM::get_db();
        } catch (Exception $e) {
            return null;
        }
    }

    private static function tableExists(PDO $pdo, $table)
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
        );
        $stmt->execute(['table' => $table]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function ensureOption(PDO $pdo, $setting, $value)
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM sys_appconfig WHERE setting = :setting'
        );
        $stmt->execute(['setting' => $setting]);

        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO sys_appconfig (setting, value) VALUES (:setting, :value)'
        );
        $stmt->execute([
            'setting' => $setting,
            'value' => $value,
        ]);
    }

    private static function upsertOption(PDO $pdo, $setting, $value)
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM sys_appconfig WHERE setting = :setting'
        );
        $stmt->execute(['setting' => $setting]);

        if ((int) $stmt->fetchColumn() > 0) {
            $update = $pdo->prepare(
                'UPDATE sys_appconfig SET value = :value WHERE setting = :setting'
            );
            $update->execute([
                'setting' => $setting,
                'value' => $value,
            ]);
            return;
        }

        $insert = $pdo->prepare(
            'INSERT INTO sys_appconfig (setting, value) VALUES (:setting, :value)'
        );
        $insert->execute([
            'setting' => $setting,
            'value' => $value,
        ]);
    }

    private static function getColumnMeta(PDO $pdo, $table, $column)
    {
        $stmt = $pdo->prepare(
            'SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
               AND COLUMN_NAME = :column_name
             LIMIT 1'
        );
        $stmt->execute([
            'table' => $table,
            'column_name' => $column,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: [];
    }

}