<?php
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'sys_companies';

    public static function migrateTableUsingPdo()
    {
        $pdo = self::pdo();

        if (!$pdo) {
            return;
        }

        if (!self::tableExists($pdo, 'sys_companies')) {
            return;
        }

        self::ensureColumn(
            $pdo,
            'sys_companies',
            'vat_number',
            "VARCHAR(32) NULL AFTER `company_name`"
        );

        self::ensureColumn(
            $pdo,
            'sys_companies',
            'crn_number',
            "VARCHAR(32) NULL AFTER `vat_number`"
        );

        self::ensureColumn(
            $pdo,
            'sys_companies',
            'building_number',
            "VARCHAR(20) NULL AFTER `crn_number`"
        );
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