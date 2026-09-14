<?php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'crm_accounts';

    public static function migrateTableUsingPdo()
    {
        $pdo = self::pdo();

        if (!$pdo) {
            return;
        }

        if (!self::tableExists($pdo, 'crm_accounts')) {
            return;
        }

        self::ensureColumn(
            $pdo,
            'crm_accounts',
            'id_type',
            "VARCHAR(20) NULL AFTER `company`"
        );

        self::ensureColumn(
            $pdo,
            'crm_accounts',
            'id_number',
            "VARCHAR(32) NULL AFTER `id_type`"
        );

        self::ensureColumn(
            $pdo,
            'crm_accounts',
            'buyer_type',
            "VARCHAR(20) NULL AFTER `entity_number`"
        );

        self::ensureColumn(
            $pdo,
            'crm_accounts',
            'building_number',
            "VARCHAR(20) NULL AFTER `buyer_type`"
        );
    }

    /**
     * @return array
     */
    public static function asArray()
    {
        return Contact::all()
            ->keyBy('id')
            ->toArray();
    }

    public static function hasLoginToken()
    {
        if (isset($_COOKIE['cloudonex_client_token'])) {
            return true;
        }
        return isset($_SESSION['cloudonex_client_token']);
    }

    /**
     * @return bool
     */
    public static function isLoggedIn()
    {
        if (isset($_COOKIE['cloudonex_client_token'])) {
            $cloudonex_client_token = $_COOKIE['cloudonex_client_token'];
        } elseif (isset($_SESSION['cloudonex_client_token'])) {
            $cloudonex_client_token = $_SESSION['cloudonex_client_token'];
        } else {
            return false;
        }

        return self::where('token', $cloudonex_client_token)->first();
    }

    /**
     * @return mixed
     */
    public static function getAllContacts()
    {
        return Contact::select(['id', 'account', 'email', 'phone', 'company'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public static function customers()
    {
        return self::select(['id', 'account', 'email', 'phone', 'company'])
            ->orderBy('id', 'desc')
            ->limit(2000)
            ->get();
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

        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
        $pdo->exec($sql);
    }
}
