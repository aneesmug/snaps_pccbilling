<?php

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $table = 'sys_licenses';

    const GRACE_DAYS = 3;
    const RECHECK_SECONDS = 60;

    public static function migrateTableUsingPdo()
    {
        $pdo = self::pdo();

        if (!$pdo) {
            return;
        }

        if (!self::tableExists($pdo, 'sys_licenses')) {
            $pdo->exec('CREATE TABLE `sys_licenses` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `serial_key` varchar(255) DEFAULT NULL,
                `token` varchar(255) DEFAULT NULL,
                `api_url` varchar(255) DEFAULT NULL,
                `verify_url` varchar(500) DEFAULT NULL,
                `cache_valid` tinyint(1) NOT NULL DEFAULT 0,
                `status` varchar(60) NOT NULL DEFAULT \'not_configured\',
                `message` text,
                `expires_at` date DEFAULT NULL,
                `last_attempt_at` datetime DEFAULT NULL,
                `last_verified_at` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        } else {
            self::ensureColumn($pdo, 'sys_licenses', 'verify_url', "VARCHAR(500) NULL AFTER `api_url`");
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

    private static function columnExists(PDO $pdo, $table, $column)
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column_name'
        );
        $stmt->execute([
            'table' => $table,
            'column_name' => $column,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function ensureColumn(PDO $pdo, $table, $column, $definition)
    {
        if (self::columnExists($pdo, $table, $column)) {
            return;
        }

        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    }

    public static function record()
    {
        $record = self::query()->first();

        if (!$record) {
            $record = new self();
            $record->status = 'not_configured';
            $record->cache_valid = 0;
            $record->save();
        }

        return $record;
    }

    public static function saveAndVerify($serialKey, $verifyUrl, $domain)
    {
        $serialKey = trim($serialKey);
        $verifyUrl = trim($verifyUrl);

        $parts = parse_url($verifyUrl);

        if (!$parts || empty($parts['host'])) {
            return null;
        }

        $token = '';
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $q);
            $token = $q['token'] ?? '';
        }

        $apiUrl = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $apiUrl .= ':' . $parts['port'];
        }
        $apiUrl .= $parts['path'] ?? '';

        $record = self::record();
        $record->serial_key = $serialKey;
        $record->token = $token;
        $record->api_url = $apiUrl;
        $record->verify_url = $verifyUrl;
        $record->save();

        return self::forceCheck($domain);
    }

    public static function forceCheck($domain)
    {
        $record = self::record();

        if (empty($record->serial_key) || empty($record->token) || empty($record->api_url)) {
            return null;
        }

        $data = null;

        try {
            $raw = ib_http_request($record->api_url, 'POST', [
                'serial_key' => $record->serial_key,
                'token' => $record->token,
                'domain' => $domain,
            ]);

            $parsed = json_decode($raw, true);

            if (is_array($parsed) && array_key_exists('valid', $parsed)) {
                $data = $parsed;
            }
        } catch (Exception $e) {
            $data = null;
        }

        $record->last_attempt_at = date('Y-m-d H:i:s');

        if ($data !== null) {
            $record->cache_valid = !empty($data['valid']) ? 1 : 0;
            $record->status = $data['status'] ?? '';
            $record->message = $data['message'] ?? '';
            $record->expires_at = $data['expires_at'] ?? null;

            if (!empty($data['valid'])) {
                $record->last_verified_at = date('Y-m-d H:i:s');
            }
        }

        $record->save();

        return $data;
    }

    public static function enforce($domain)
    {
        $record = self::record();

        if (empty($record->serial_key) || empty($record->token)) {
            return false;
        }

        $needsCheck = empty($record->last_attempt_at) ||
            (strtotime($record->last_attempt_at) <= time() - self::RECHECK_SECONDS);

        if ($needsCheck) {
            self::forceCheck($domain);
            $record = self::record();
        }

        if (empty($record->cache_valid) || empty($record->last_verified_at)) {
            return false;
        }

        $graceCutoff = time() - (self::GRACE_DAYS * 86400);

        return strtotime($record->last_verified_at) >= $graceCutoff;
    }

    public static function isCachedValid()
    {
        $record = self::record();

        if (empty($record->cache_valid) || empty($record->last_verified_at)) {
            return false;
        }

        $graceCutoff = time() - (self::GRACE_DAYS * 86400);

        return strtotime($record->last_verified_at) >= $graceCutoff;
    }
}
