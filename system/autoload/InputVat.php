<?php

/**
 * Input VAT (purchases & expenses) rules for KSA VAT / ZATCA.
 *
 * Purchases and expenses are never reported to ZATCA by this system (Phase 2 e-invoicing
 * covers invoices WE issue), but the input VAT recorded here feeds the VAT return, and
 * input VAT is only deductible with a valid tax invoice carrying the supplier's VAT number
 * (VAT Implementing Regulations, Art. 49 & 53). Each purchase / expense therefore keeps:
 *  - vat_treatment: which VAT return box it belongs to
 *  - supplier_vat_number: snapshot of the supplier VAT No. printed on their tax invoice
 *  - supplier_invoice_number / supplier_invoice_date: the supplier's own invoice reference
 *    (customs declaration No. for imports)
 *  - reverse_charge_vat: VAT self-assessed on imported services (output and input VAT)
 */
class InputVat
{
    const SCHEMA_VERSION = '1';

    const STANDARD_RATE = 15.0;

    public static function treatments()
    {
        return [
            'standard' => 'Standard rated (domestic 15%)',
            'zero' => 'Zero-rated',
            'exempt' => 'Exempt / out of scope',
            'import' => 'Import (VAT paid at customs)',
            'reverse_charge' => 'Reverse charge (services from non-resident)',
        ];
    }

    public static function normalizeTreatment($treatment)
    {
        $treatment = strtolower(trim((string) $treatment));

        return array_key_exists($treatment, self::treatments()) ? $treatment : 'standard';
    }

    public static function normalizeDigits($value)
    {
        $value = str_replace(
            ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            (string) $value
        );

        return preg_replace('/\D+/', '', $value);
    }

    public static function isValidVatNumber($vat_number)
    {
        return (bool) preg_match('/^3\d{13}3$/', (string) $vat_number);
    }

    public static function reverseChargeVat($treatment, $net)
    {
        if ($treatment !== 'reverse_charge') {
            return 0.0;
        }

        return round(max(0.0, (float) $net) * self::STANDARD_RATE / 100, 2);
    }

    /**
     * @return string error messages joined with <br>, empty when valid
     */
    public static function validate($treatment, $vat_amount, $supplier_vat_number, $supplier_invoice_number)
    {
        $msg = '';
        $vat_amount = round((float) $vat_amount, 2);
        $labels = self::treatments();

        if ($vat_amount < 0) {
            $msg .= 'VAT amount cannot be negative <br>';
        }

        if ($treatment === 'standard' && $vat_amount > 0) {
            if (!self::isValidVatNumber($supplier_vat_number)) {
                $msg .= 'Supplier VAT Number (15 digits, starts and ends with 3) is required to claim input VAT <br>';
            }
            if (trim((string) $supplier_invoice_number) === '') {
                $msg .= 'Supplier Invoice Number is required to claim input VAT <br>';
            }
        } elseif (in_array($treatment, ['zero', 'exempt', 'reverse_charge'], true) && $vat_amount > 0) {
            $msg .= 'VAT must be 0 for "' . $labels[$treatment] . '"'
                . ($treatment === 'reverse_charge' ? ' (VAT is self-assessed automatically)' : '')
                . ' <br>';
        } elseif ($treatment === 'import' && $vat_amount > 0 && trim((string) $supplier_invoice_number) === '') {
            $msg .= 'Customs Declaration Number is required to claim import VAT <br>';
        }

        // Domestic supplier VAT numbers must be well-formed when entered; foreign suppliers
        // (import / reverse charge) have no Saudi VAT number format.
        if (
            $supplier_vat_number !== ''
            && !in_array($treatment, ['import', 'reverse_charge'], true)
            && !self::isValidVatNumber($supplier_vat_number)
        ) {
            if (strpos($msg, 'Supplier VAT Number') === false) {
                $msg .= 'Supplier VAT Number must be 15 digits and start/end with 3 <br>';
            }
        }

        return $msg;
    }

    /**
     * Supplier VAT number stored on the contact (or its registered company).
     */
    public static function contactVatNumber($contact_id)
    {
        $contact = ORM::for_table('crm_accounts')->find($contact_id);

        if (!$contact) {
            return '';
        }

        $vat = self::normalizeDigits($contact->tax_number ?? '');

        if ($vat === '' && !empty($contact->cid)) {
            $company = ORM::for_table('sys_companies')->find($contact->cid);
            if ($company) {
                $vat = self::normalizeDigits($company->vat_number ?? '');
            }
        }

        return $vat;
    }

    public static function migrate()
    {
        try {
            $pdo = ORM::get_db();
        } catch (Throwable $e) {
            return;
        }

        try {
            $stmt = $pdo->prepare('SELECT `value` FROM `sys_appconfig` WHERE `setting` = ?');
            $stmt->execute(['input_vat_schema_version']);
            if ((string) $stmt->fetchColumn() === self::SCHEMA_VERSION) {
                return;
            }

            $columns = [
                'supplier_invoice_number' => 'VARCHAR(100) NULL',
                'supplier_invoice_date' => 'DATE NULL',
                'supplier_vat_number' => 'VARCHAR(20) NULL',
                'vat_treatment' => "VARCHAR(20) NOT NULL DEFAULT 'standard'",
                'reverse_charge_vat' => 'DECIMAL(18,2) NOT NULL DEFAULT 0.00',
            ];

            foreach (['sys_purchases', 'sys_transactions'] as $table) {
                if (!self::tableExists($pdo, $table)) {
                    return;
                }

                foreach ($columns as $column => $definition) {
                    if (!self::columnExists($pdo, $table, $column)) {
                        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                    }
                }
            }

            update_option('input_vat_schema_version', self::SCHEMA_VERSION);
        } catch (Throwable $e) {
            // Leave the flag unset so the migration is retried on the next request.
        }
    }

    private static function tableExists(PDO $pdo, $table)
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $stmt->execute([$table]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private static function columnExists(PDO $pdo, $table, $column)
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
