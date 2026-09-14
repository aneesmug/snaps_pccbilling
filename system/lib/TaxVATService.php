<?php

/**
 * ============================================================
 * TAX & VAT COMPLIANCE SERVICE
 * ============================================================
 * Manages VAT/Tax compliance and ZATCA Phase 2 integration
 * - Track VAT by type (sales/purchase)
 * - Generate VAT reports
 * - Link ZATCA invoice data to GL entries
 * - Tax period reconciliation
 * - Compliance validation
 * 
 * GL Accounts Used:
 * - VAT Payable (Output Tax)
 * - VAT Recoverable (Input Tax)
 * ============================================================
 */

class TaxVATService
{
    protected $db;
    protected $je_service;
    protected $user_id;
    protected $company_id;
    protected $table_exists_cache = [];

    public function __construct($db = null, $je_service = null, $user_id = null, $company_id = null)
    {
        $this->db = $db ?: \Illuminate\Database\Capsule\Manager::connection();
        $this->je_service = $je_service ?: new JournalEntryService($db, $user_id, $company_id);
        $this->user_id = $user_id ?: (isset($GLOBALS['user']) && $GLOBALS['user'] ? $GLOBALS['user']->id : null);
        $this->company_id = $company_id;
    }

    /**
     * ============================================================
     * CALCULATE VAT ON INVOICE
     * ============================================================
     * Determines taxable amount, VAT rate, and VAT amount
     * Based on tax code and item configuration
     * 
     * @param array $invoice_data
     * @param string $tax_code
     * @return array
     */
    public function calculateInvoiceVAT($invoice_data, $tax_code = null)
    {
        try {
            if (!$tax_code) {
                $tax_code = 'VAT-15';  // Default Saudi VAT rate (15%)
            }

            $tax = $this->db->table('sys_tax_codes')
                ->where('code', $tax_code)
                ->first();

            if (!$tax) {
                return [
                    'success' => false,
                    'message' => 'Tax code not found: ' . $tax_code
                ];
            }

            // Calculate taxable amount (exclude non-taxable items)
            $taxable_amount = $this->calculateTaxableAmount($invoice_data);

            // Calculate VAT
            $vat_amount = $taxable_amount * ($tax->rate / 100);

            return [
                'success' => true,
                'tax_code' => $tax_code,
                'tax_rate' => (float)$tax->rate,
                'taxable_amount' => (float)$taxable_amount,
                'vat_amount' => round($vat_amount, 2),
                'total_with_vat' => round($taxable_amount + $vat_amount, 2),
                'gl_payable_account_id' => $tax->gl_payable_account_id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error calculating VAT: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * CALCULATE TAXABLE AMOUNT
     * ============================================================
     * Filter items based on tax_exempt flag
     */
    protected function calculateTaxableAmount($invoice_data)
    {
        $taxable = 0;

        if (!empty($invoice_data['lines'])) {
            foreach ($invoice_data['lines'] as $line) {
                // Check if item is tax exempt
                $is_exempt = $line['tax_exempt'] ?? false;

                if (!$is_exempt) {
                    $line_total = $line['quantity'] * $line['unit_price'];
                    $taxable += $line_total;
                }
            }
        }

        return $taxable;
    }

    /**
     * ============================================================
     * RECORD SALES VAT (PAYABLE)
     * ============================================================
     * Called when invoice is posted
     * Increases VAT Payable account
     * 
     * @param int $invoice_id
     * @param string $tax_code
     * @return array
     */
    public function recordSalesVAT($invoice_id, $tax_code = 'VAT-15')
    {
        try {
            $invoice = $this->db->table('sys_invoices')
                ->where('id', $invoice_id)
                ->first();

            if (!$invoice || !$invoice->vat_amount || $invoice->vat_amount <= 0) {
                return ['success' => true, 'message' => 'No VAT to record'];
            }

            $tax = $this->db->table('sys_tax_codes')
                ->where('code', $tax_code)
                ->first();

            if (!$tax || !$tax->gl_payable_account_id) {
                return ['success' => false, 'message' => 'VAT Payable account not configured'];
            }

            // VAT Payable is already posted as part of invoice posting
            // This is for tracking/validation only
            
            $this->db->table('sys_invoices')
                ->where('id', $invoice_id)
                ->update(['vat_code' => $tax_code]);

            return [
                'success' => true,
                'message' => 'VAT recorded',
                'vat_amount' => (float)$invoice->vat_amount,
                'tax_code' => $tax_code
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error recording sales VAT: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * RECORD PURCHASE VAT (RECOVERABLE)
     * ============================================================
     * Called when bill is posted
     * Creates VAT Recoverable (Input Tax Credit)
     * 
     * @param int $bill_id
     * @param string $tax_code
     * @return array
     */
    public function recordPurchaseVAT($bill_id, $tax_code = 'VAT-15')
    {
        try {
            $bill_table = $this->getPurchaseTableName();
            if (!$bill_table) {
                return ['success' => false, 'message' => 'No purchase/bill table found in current schema'];
            }

            $bill = $this->db->table($bill_table)
                ->where('id', $bill_id)
                ->first();

            $vat_amount = $this->extractPurchaseVATAmount($bill);
            if (!$bill || $vat_amount <= 0) {
                return ['success' => true, 'message' => 'No VAT to recover'];
            }

            $tax = $this->db->table('sys_tax_codes')
                ->where('code', $tax_code)
                ->first();

            if (!$tax || !$tax->gl_recoverable_account_id) {
                return ['success' => false, 'message' => 'VAT Recoverable account not configured'];
            }

            if ($this->tableHasColumn($bill_table, 'vat_code')) {
                $this->db->table($bill_table)
                    ->where('id', $bill_id)
                    ->update(['vat_code' => $tax_code]);
            } elseif ($this->tableHasColumn($bill_table, 'tax_code')) {
                $this->db->table($bill_table)
                    ->where('id', $bill_id)
                    ->update(['tax_code' => $tax_code]);
            }

            return [
                'success' => true,
                'message' => 'Input tax recorded',
                'vat_amount' => $vat_amount,
                'tax_code' => $tax_code
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error recording purchase VAT: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * GENERATE VAT REPORT (Period-based)
     * ============================================================
     * Calculate VAT payable/recoverable for a period
     * 
     * @param string $from_date YYYY-MM-DD
     * @param string $to_date YYYY-MM-DD
     * @return array
     */
    public function getVATReport($from_date, $to_date)
    {
        try {
            // Get VAT Payable (Sales Tax)
            $vat_payable = $this->db->selectOne("
                SELECT SUM(credit) as total
                FROM sys_journal_items
                WHERE account_id IN (
                    SELECT id FROM sys_gl_accounts 
                    WHERE type = 'liability' AND name LIKE '%VAT%Payable%'
                )
                AND journal_entry_id IN (
                    SELECT id FROM sys_journal_entries
                    WHERE entry_date BETWEEN ? AND ? AND post_status = 'posted'
                )
            ", [$from_date, $to_date]);

            // Get VAT Recoverable (Purchase VAT / Input Tax)
            $vat_recoverable = $this->db->selectOne("
                SELECT SUM(debit) as total
                FROM sys_journal_items
                WHERE account_id IN (
                    SELECT id FROM sys_gl_accounts 
                    WHERE type = 'asset' AND name LIKE '%VAT%Recoverable%'
                )
                AND journal_entry_id IN (
                    SELECT id FROM sys_journal_entries
                    WHERE entry_date BETWEEN ? AND ? AND post_status = 'posted'
                )
            ", [$from_date, $to_date]);

            $payable = (float)($vat_payable->total ?? 0);
            $recoverable = (float)($vat_recoverable->total ?? 0);
            $net_vat = $payable - $recoverable;

            return [
                'success' => true,
                'period' => [
                    'from_date' => $from_date,
                    'to_date' => $to_date
                ],
                'vat_payable' => round($payable, 2),
                'vat_recoverable' => round($recoverable, 2),
                'net_vat_due' => round($net_vat, 2),
                'vat_status' => $net_vat > 0 ? 'Due' : ($net_vat < 0 ? 'Refund' : 'Settled')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating VAT report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * LINK ZATCA INVOICE TO GL
     * ============================================================
     * Associate ZATCA submission data with GL journal entry
     * Maintains ZATCA audit trail
     * 
     * @param int $invoice_id
     * @param array $zatca_data
     * @return array
     */
    public function linkZATCAInvoiceToGL($invoice_id, $zatca_data)
    {
        try {
            // Get invoice JE
            $invoice = $this->db->table('sys_invoices')
                ->where('id', $invoice_id)
                ->first();

            if (!$invoice || !$invoice->journal_entry_id) {
                return ['success' => false, 'message' => 'Invoice or JE not found'];
            }

            // Update JE with ZATCA reference
            $this->db->table('sys_journal_entries')
                ->where('id', $invoice->journal_entry_id)
                ->update([
                    'memo' => json_encode([
                        'zatca_uuid' => $zatca_data['uuid'] ?? null,
                        'zatca_status' => $zatca_data['status'] ?? 'submitted',
                        'zatca_timestamp' => $zatca_data['timestamp'] ?? date('Y-m-d H:i:s')
                    ])
                ]);

            // Update invoice ZATCA columns
            $this->db->table('sys_invoices')
                ->where('id', $invoice_id)
                ->update([
                    'zatca_uuid' => $zatca_data['uuid'] ?? null,
                    'zatca_status' => $zatca_data['status'] ?? 'submitted',
                    'zatca_invoice_type' => $zatca_data['type'] ?? 'STANDARD',
                    'zatca_last_submit_at' => date('Y-m-d H:i:s'),
                    'zatca_submission_response' => json_encode($zatca_data['response'] ?? []),
                    'zatca_hash' => $zatca_data['hash'] ?? null,
                    'zatca_signature' => $zatca_data['signature'] ?? null
                ]);

            return [
                'success' => true,
                'message' => 'ZATCA data linked to GL',
                'journal_entry_id' => $invoice->journal_entry_id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error linking ZATCA data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * VALIDATE ZATCA COMPLIANCE
     * ============================================================
     * Check if invoices meet ZATCA Phase 2 requirements
     * 
     * @param int $invoice_id
     * @return array
     */
    public function validateZATCACompliance($invoice_id)
    {
        try {
            $invoice = $this->db->table('sys_invoices')
                ->where('id', $invoice_id)
                ->first();

            if (!$invoice) {
                return ['success' => false, 'message' => 'Invoice not found'];
            }

            $issues = [];

            // Check required ZATCA fields
            if (!$invoice->zatca_uuid) $issues[] = 'Missing ZATCA UUID';
            if (!$invoice->zatca_hash) $issues[] = 'Missing invoice hash';
            if (!$invoice->zatca_signature) $issues[] = 'Missing digital signature';
            if (!$invoice->vat_amount) $issues[] = 'Invoice must include VAT for ZATCA';

            // Check invoice date
            if (strtotime($invoice->created_at) < strtotime('-30 days')) {
                // ZATCA requires submission within 30 days
                $issues[] = 'Invoice exceeds 30-day submission deadline';
            }

            $is_compliant = count($issues) === 0;

            return [
                'success' => true,
                'invoice_id' => $invoice_id,
                'is_compliant' => $is_compliant,
                'zatca_status' => $invoice->zatca_status ?? 'not_submitted',
                'issues' => $issues,
                'check_date' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error validating ZATCA compliance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * GET TAX SUMMARY BY CODE
     * ============================================================
     * Show transaction count and totals per tax code
     */
    public function getTaxSummary($from_date, $to_date)
    {
        $sales = $this->db->table('sys_invoices')
            ->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59'])
            ->select(
                'vat_code',
                $this->db->raw('COUNT(*) as count'),
                $this->db->raw('SUM(total) as total_amount'),
                $this->db->raw('SUM(vat_amount) as total_vat')
            )
            ->groupBy('vat_code')
            ->get();

        $purchase_table = $this->getPurchaseTableName();
        if ($purchase_table) {
            $purchases = $this->db->table($purchase_table)
            ->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59'])
            ->select(
                $this->db->raw("COALESCE(vat_code, tax_code, taxname, 'UNSPECIFIED') as vat_code"),
                $this->db->raw('COUNT(*) as count'),
                $this->db->raw('SUM(total) as total_amount'),
                $this->db->raw('SUM(COALESCE(vat_amount, tax_total, tax, 0)) as total_vat')
            )
            ->groupBy($this->db->raw("COALESCE(vat_code, tax_code, taxname, 'UNSPECIFIED')"))
            ->get();
        } else {
            $purchases = [];
        }

        return [
            'sales' => $sales,
            'purchases' => $purchases,
            'period' => ['from' => $from_date, 'to' => $to_date]
        ];
    }

    /**
     * ============================================================
     * GET ZATCA SUBMISSION STATUS
     * ============================================================
     * List invoices by ZATCA submission status
     */
    public function getZATCAStatus($status = null)
    {
        $query = $this->db->table('sys_invoices');

        if ($status) {
            $query->where('zatca_status', $status);
        }

        return $query->select(
            'id',
            'invoicenum',
            'zatca_uuid',
            'zatca_status',
            'zatca_last_submit_at',
            'created_at'
        )->get();
    }

    /**
     * ============================================================
     * RECONCILE VAT ACCOUNTS
     * ============================================================
     * Validate VAT GL accounts are balanced
     */
    public function reconcileVATAccounts()
    {
        $accounts = $this->db->table('sys_gl_accounts')
            ->where('name', 'LIKE', '%VAT%')
            ->select('id', 'code', 'name', 'type')
            ->get();

        $results = [];

        foreach ($accounts as $account) {
            $balance = $this->je_service->getAccountBalance($account->id);

            $results[] = [
                'account' => $account->name,
                'code' => $account->code,
                'type' => $account->type,
                'debit_balance' => $balance['debit_balance'],
                'credit_balance' => $balance['credit_balance'],
                'net_balance' => $balance['net_balance']
            ];
        }

        return [
            'vat_accounts' => $results,
            'reconciliation_date' => date('Y-m-d H:i:s')
        ];
    }

    protected function tableExists($table)
    {
        if (!isset($this->table_exists_cache[$table])) {
            $this->table_exists_cache[$table] = $this->db->table('information_schema.tables')
                ->whereRaw('table_schema = DATABASE()')
                ->where('table_name', $table)
                ->exists();
        }

        return $this->table_exists_cache[$table];
    }

    protected function tableHasColumn($table, $column)
    {
        return $this->db->table('information_schema.columns')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->exists();
    }

    protected function getPurchaseTableName()
    {
        if ($this->tableExists('sys_bills')) {
            return 'sys_bills';
        }

        if ($this->tableExists('sys_purchases')) {
            return 'sys_purchases';
        }

        return null;
    }

    protected function extractPurchaseVATAmount($purchase)
    {
        if (!$purchase) {
            return 0.0;
        }

        if (isset($purchase->vat_amount)) {
            return (float) $purchase->vat_amount;
        }

        if (isset($purchase->tax_total)) {
            return (float) $purchase->tax_total;
        }

        $tax_1 = isset($purchase->tax) ? (float) $purchase->tax : 0.0;
        $tax_2 = isset($purchase->tax2) ? (float) $purchase->tax2 : 0.0;

        return $tax_1 + $tax_2;
    }
}
