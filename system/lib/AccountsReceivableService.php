<?php

/**
 * ============================================================
 * ACCOUNTS RECEIVABLE SERVICE
 * ============================================================
 * Manages customer invoices and receivables
 * - Posts invoices to AR & GL
 * - Records customer payments
 * - Generates aging reports
 * - Handles bad debt writeoffs
 * - Allocates payments to invoices
 * 
 * GL Accounts Used:
 * - Accounts Receivable (Control account)
 * - Revenue (various)
 * - VAT Payable (or Sales Tax)
 * - Bank/Cash
 * ============================================================
 */

class AccountsReceivableService
{
    protected $db;
    protected $je_service;
    protected $user_id;
    protected $company_id;
    protected $table_exists_cache = [];
    protected $column_exists_cache = [];

    public function __construct($db = null, $je_service = null, $user_id = null, $company_id = null)
    {
        $this->db = $db ?: \Illuminate\Database\Capsule\Manager::connection();
        $this->je_service = $je_service ?: new JournalEntryService($db, $user_id, $company_id);
        $this->user_id = $user_id ?: (isset($GLOBALS['user']) && $GLOBALS['user'] ? $GLOBALS['user']->id : null);
        $this->company_id = $company_id;
    }

    /**
     * ============================================================
     * POST INVOICE TO AR & GL
     * ============================================================
     * When invoice is finalized/posted:
     * 
     *   Dr Accounts Receivable (AR control account)
     *   Cr Revenue (by item category)
     *   Cr VAT Payable (if taxable)
     * 
     * @param int $invoice_id Invoice from sys_invoices
     * @return array
     */
    public function postInvoiceToAR($invoice_id)
    {
        try {
            $invoice = $this->db->table('sys_invoices')
                ->where('id', $invoice_id)
                ->first();

            if (!$invoice) {
                return ['success' => false, 'message' => 'Invoice not found'];
            }

            if (!empty($invoice->journal_entry_id)) {
                return ['success' => false, 'message' => 'Invoice already posted to GL'];
            }

            $customer_id = $this->extractInvoiceCustomerId($invoice);
            if (!$customer_id) {
                return ['success' => false, 'message' => 'Invoice is missing customer reference'];
            }

            $customer = $this->getContactRecord($customer_id, 'Customer');
            if (!$customer) {
                return ['success' => false, 'message' => 'Customer not found'];
            }

            $ar_account_id = $this->resolveARAccountId($customer);
            if (!$ar_account_id) {
                return ['success' => false, 'message' => 'AR account not configured'];
            }

            $customer_name = $this->extractContactName($customer);

            $invoice_items = $this->getInvoiceItemsWithAccounts($invoice_id);
            if (empty($invoice_items)) {
                return ['success' => false, 'message' => 'Invoice items not found or missing account mapping'];
            }

            // Aggregate lines by revenue account
            $revenue_lines = [];
            $total_revenue = 0;

            foreach ($invoice_items as $item) {
                $account_id = (int) ($item->account_id ?? 0);
                if ($account_id <= 0) {
                    continue;
                }

                $item_amount = (float) ($item->line_total ?? 0);
                if ($item_amount <= 0) {
                    continue;
                }

                $total_revenue += $item_amount;

                if (!isset($revenue_lines[$account_id])) {
                    $revenue_lines[$account_id] = 0;
                }
                $revenue_lines[$account_id] += $item_amount;
            }

            if (empty($revenue_lines)) {
                return ['success' => false, 'message' => 'No valid revenue lines found'];
            }

            $invoice_number = $invoice->invoicenum ?? ('INV-' . $invoice_id);
            $vat_amount = $this->extractInvoiceVATAmount($invoice);

            // Build JE lines
            $je_lines = [];

            // Line 1: Dr Accounts Receivable (control account)
            $je_lines[] = [
                'account_id' => $ar_account_id,
                'debit' => $total_revenue + $vat_amount,
                'credit' => 0,
                'description' => 'Invoice ' . $invoice_number . ' from ' . $customer_name
            ];

            // Lines 2+: Cr Revenue accounts
            foreach ($revenue_lines as $account_id => $amount) {
                $je_lines[] = [
                    'account_id' => $account_id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'Invoice ' . $invoice_number . ' revenue'
                ];
            }

            // Line N: Cr VAT Payable (if applicable)
            if ($vat_amount > 0) {
                $vat_payable_account = $this->getTaxVATPayableAccount($invoice->id);
                if ($vat_payable_account) {
                    $je_lines[] = [
                        'account_id' => $vat_payable_account,
                        'debit' => 0,
                        'credit' => $vat_amount,
                        'description' => 'VAT on Invoice ' . $invoice_number
                    ];
                }
            }

            // Create JE using JournalEntryService
            $je_result = $this->je_service->createAndPostJournalEntry([
                'entry_date' => date('Y-m-d'),
                'reference' => 'INV-' . $invoice_number,
                'description' => 'Invoice posting: ' . $invoice_number,
                'source_module' => 'invoice',
                'source_id' => $invoice_id,
                'lines' => $je_lines
            ]);

            if ($je_result['success']) {
                // Update invoice with JE reference
                $this->db->table('sys_invoices')
                    ->where('id', $invoice_id)
                    ->update([
                        'journal_entry_id' => $je_result['journal_entry_id'],
                        'status' => 'posted'
                    ]);

                // Create AR ledger entry for aging reports
                $this->db->table('sys_ar_ledger')->insert([
                    'customer_id' => $customer_id,
                    'journal_entry_id' => $je_result['journal_entry_id'],
                    'invoice_id' => $invoice_id,
                    'amount' => $total_revenue + $vat_amount,
                    'transaction_type' => 'invoice',
                    'posting_date' => date('Y-m-d'),
                    'due_date' => ($invoice->due_date ?? $invoice->duedate ?? date('Y-m-d', strtotime('+30 days')))
                ]);
            }

            return $je_result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error posting invoice to AR: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * RECORD CUSTOMER PAYMENT (RECEIPT)
     * ============================================================
     * When customer payment is received:
     * 
     *   Dr Bank/Cash Account
     *   Cr Accounts Receivable
     * 
     * Optionally allocates to specific invoices
     * 
     * @param array $data {
     *     'customer_id' => 123,
     *     'bank_account_id' => 1,
     *     'amount' => 1000.00,
     *     'payment_date' => '2026-04-27',
     *     'reference' => 'CHQ-001',
     *     'allocations' => [
     *         ['invoice_id' => 100, 'amount' => 500.00],
     *         ['invoice_id' => 101, 'amount' => 500.00],
     *     ]
     * }
     * @return array
     */
    public function recordCustomerPayment($data)
    {
        try {
            $this->db->beginTransaction();

            // Validate
            if (empty($data['customer_id']) || empty($data['bank_account_id']) || empty($data['amount'])) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            // Get customer AR account
            $customer = $this->getContactRecord($data['customer_id'], 'Customer');
            $ar_account_id = $this->resolveARAccountId($customer);

            if (!$customer || !$ar_account_id) {
                return ['success' => false, 'message' => 'Customer or AR account not found'];
            }

            // Get bank account GL account
            $bank_account = $this->db->table('sys_bank_accounts')
                ->where('id', $data['bank_account_id'])
                ->first();

            if (!$bank_account) {
                return ['success' => false, 'message' => 'Bank account not found'];
            }

            // Create JE: Dr Bank Cr AR
            $je_result = $this->je_service->createAndPostJournalEntry([
                'entry_date' => $data['payment_date'] ?? date('Y-m-d'),
                'reference' => 'PAY-' . ($data['reference'] ?? 'RECEIPT'),
                'description' => 'Payment from Customer ' . $customer->name,
                'source_module' => 'payment',
                'source_id' => null,
                'lines' => [
                    [
                        'account_id' => $bank_account->gl_cash_account_id,
                        'debit' => $data['amount'],
                        'credit' => 0,
                        'description' => 'Payment received'
                    ],
                    [
                        'account_id' => $ar_account_id,
                        'debit' => 0,
                        'credit' => $data['amount'],
                        'description' => 'AR reduction'
                    ]
                ]
            ]);

            if (!$je_result['success']) {
                $this->db->rollBack();
                return $je_result;
            }

            // Update bank account balance
            $this->db->table('sys_bank_accounts')
                ->where('id', $data['bank_account_id'])
                ->increment('balance', $data['amount']);

            // Record in AR ledger
            $this->db->table('sys_ar_ledger')->insert([
                'customer_id' => $data['customer_id'],
                'journal_entry_id' => $je_result['journal_entry_id'],
                'invoice_id' => null,
                'amount' => -$data['amount'],  // Negative for payment
                'transaction_type' => 'payment',
                'posting_date' => $data['payment_date'] ?? date('Y-m-d'),
                'is_allocated' => !empty($data['allocations']) ? 1 : 0
            ]);

            // Allocate to invoices if specified
            if (!empty($data['allocations'])) {
                $this->allocatePaymentToInvoices(
                    $je_result['journal_entry_id'],
                    $data['customer_id'],
                    $data['allocations']
                );
            }

            $this->db->commit();

            return [
                'success' => true,
                'journal_entry_id' => $je_result['journal_entry_id'],
                'message' => 'Payment recorded successfully'
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * ALLOCATE PAYMENT TO INVOICES
     * ============================================================
     */
    protected function allocatePaymentToInvoices($je_id, $customer_id, $allocations)
    {
        foreach ($allocations as $alloc) {
            $this->db->table('sys_ar_ledger')
                ->where('customer_id', $customer_id)
                ->where('invoice_id', $alloc['invoice_id'])
                ->update(['is_allocated' => 1]);
        }
    }

    /**
     * ============================================================
     * AGING REPORT
     * ============================================================
     * Generates AR aging by customer
     * Buckets: Current, 30, 60, 90+ days
     * 
     * @return array [customer_id => [current => 0, 30 => 0, 60 => 0, 90 => 0]]
     */
    public function getARAgingReport()
    {
        $today = date('Y-m-d');
        
        $results = $this->db->table('sys_ar_ledger')
            ->leftJoin('crm_accounts', 'sys_ar_ledger.customer_id', '=', 'crm_accounts.id')
            ->where('sys_ar_ledger.transaction_type', 'invoice')
            ->where('sys_ar_ledger.is_allocated', 0)  // Outstanding only
            ->select(
                'sys_ar_ledger.customer_id',
                $this->db->raw("COALESCE(crm_accounts.display_name, crm_accounts.company, crm_accounts.account, TRIM(CONCAT(COALESCE(crm_accounts.fname,''), ' ', COALESCE(crm_accounts.lname,''))), 'Unknown') as name"),
                'sys_ar_ledger.amount',
                'sys_ar_ledger.due_date'
            )
            ->get();

        $aging = [];

        foreach ($results as $row) {
            $due_date = strtotime($row->due_date);
            $today_ts = strtotime($today);
            $days_overdue = floor(($today_ts - $due_date) / 86400);

            if (!isset($aging[$row->customer_id])) {
                $aging[$row->customer_id] = [
                    'customer_name' => $row->name,
                    'current' => 0,
                    '30' => 0,
                    '60' => 0,
                    '90' => 0
                ];
            }

            if ($days_overdue <= 0) {
                $aging[$row->customer_id]['current'] += $row->amount;
            } elseif ($days_overdue <= 30) {
                $aging[$row->customer_id]['30'] += $row->amount;
            } elseif ($days_overdue <= 60) {
                $aging[$row->customer_id]['60'] += $row->amount;
            } else {
                $aging[$row->customer_id]['90'] += $row->amount;
            }
        }

        return $aging;
    }

    /**
     * ============================================================
     * BAD DEBT WRITEOFF
     * ============================================================
     * Record bad debt as expense (with reversal of AR)
     * 
     *   Dr Bad Debt Expense
     *   Cr Accounts Receivable
     * 
     * @param int $customer_id
     * @param float $amount
     * @return array
     */
    public function writeOffBadDebt($customer_id, $amount)
    {
        try {
            $customer = $this->getContactRecord($customer_id, 'Customer');
            $ar_account_id = $this->resolveARAccountId($customer);

            if (!$customer || !$ar_account_id) {
                return ['success' => false, 'message' => 'Customer not found'];
            }

            // Get Bad Debt Expense account (should be configured)
            $bad_debt_expense_account = $this->getBadDebtExpenseAccount();
            if (!$bad_debt_expense_account) {
                return ['success' => false, 'message' => 'Bad Debt Expense account not configured'];
            }

            $je_result = $this->je_service->createAndPostJournalEntry([
                'entry_date' => date('Y-m-d'),
                'reference' => 'WRITEOFF-' . $customer_id,
                'description' => 'Bad debt writeoff: ' . $this->extractContactName($customer),
                'source_module' => 'ar_writeoff',
                'source_id' => $customer_id,
                'lines' => [
                    [
                        'account_id' => $bad_debt_expense_account,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'Bad debt expense'
                    ],
                    [
                        'account_id' => $ar_account_id,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => 'AR reduction'
                    ]
                ]
            ]);

            return $je_result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error writing off bad debt: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * CUSTOMER STATEMENT
     * ============================================================
     * Generate customer statement showing AR transactions
     */
    public function getCustomerStatement($customer_id, $from_date = null, $to_date = null)
    {
        $query = $this->db->table('sys_ar_ledger')
            ->where('customer_id', $customer_id);

        if ($from_date && $to_date) {
            $query->whereBetween('posting_date', [$from_date, $to_date]);
        }

        return $query->orderBy('posting_date')
            ->get();
    }

    /**
     * ============================================================
     * HELPER: Get VAT Payable Account
     * ============================================================
     */
    protected function getTaxVATPayableAccount($invoice_id)
    {
        $invoice = $this->db->table('sys_invoices')
            ->where('id', $invoice_id)
            ->first();

        if (!$invoice) {
            return null;
        }

        $vat_code = $invoice->vat_code ?? $invoice->tax_code ?? null;
        if (!$vat_code && !empty($invoice->taxname)) {
            $vat_code = 'VAT-15';
        }

        if (!$vat_code) {
            return null;
        }

        return $this->db->table('sys_tax_codes')
            ->where('code', $vat_code)
            ->value('gl_payable_account_id');
    }

    /**
     * ============================================================
     * HELPER: Get Bad Debt Expense Account
     * ============================================================
     */
    protected function getBadDebtExpenseAccount()
    {
        return $this->db->table('sys_gl_accounts')
            ->where('code', '5100')  // Adjust to your chart of accounts
            ->where('type', 'expense')
            ->value('id');
    }

    /**
     * ============================================================
     * GET AR SUMMARY
     * ============================================================
     */
    public function getARSummary()
    {
        $outstanding = $this->db->selectOne("
            SELECT 
                COUNT(DISTINCT customer_id) as customer_count,
                SUM(CASE WHEN transaction_type = 'invoice' THEN amount ELSE 0 END) as total_invoiced,
                SUM(CASE WHEN transaction_type = 'payment' THEN amount ELSE 0 END) as total_paid,
                SUM(amount) as net_ar
            FROM sys_ar_ledger
            WHERE posting_date <= DATE(date('Y-m-d H:i:s'))
        ");

        return [
            'total_customers' => $outstanding->customer_count ?? 0,
            'total_invoiced' => (float)($outstanding->total_invoiced ?? 0),
            'total_paid' => (float)($outstanding->total_paid ?? 0),
            'net_ar_outstanding' => (float)($outstanding->net_ar ?? 0)
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

    protected function columnExists($table, $column)
    {
        $key = $table . '.' . $column;
        if (!isset($this->column_exists_cache[$key])) {
            $this->column_exists_cache[$key] = $this->db->table('information_schema.columns')
                ->whereRaw('table_schema = DATABASE()')
                ->where('table_name', $table)
                ->where('column_name', $column)
                ->exists();
        }

        return $this->column_exists_cache[$key];
    }

    protected function extractInvoiceCustomerId($invoice)
    {
        if (!empty($invoice->customer_id)) {
            return (int) $invoice->customer_id;
        }

        if (!empty($invoice->userid)) {
            return (int) $invoice->userid;
        }

        return null;
    }

    protected function getContactRecord($id, $type = 'Customer')
    {
        if ($this->tableExists('sys_customers') && $type === 'Customer') {
            return $this->db->table('sys_customers')->where('id', $id)->first();
        }

        if ($this->tableExists('crm_accounts')) {
            $query = $this->db->table('crm_accounts')->where('id', $id);
            if ($this->columnExists('crm_accounts', 'type') && $type) {
                $query->where('type', $type);
            }
            return $query->first();
        }

        return null;
    }

    protected function extractContactName($contact)
    {
        if (!$contact) {
            return 'Unknown';
        }

        $name = $contact->name ?? $contact->display_name ?? $contact->company ?? $contact->account ?? null;
        if ($name) {
            return $name;
        }

        $first = trim((string) ($contact->fname ?? ''));
        $last = trim((string) ($contact->lname ?? ''));
        $full = trim($first . ' ' . $last);

        return $full !== '' ? $full : 'Unknown';
    }

    protected function resolveARAccountId($customer)
    {
        if (!$customer) {
            return null;
        }

        if (isset($customer->ar_account_id) && !empty($customer->ar_account_id)) {
            return (int) $customer->ar_account_id;
        }

        return (int) $this->db->table('sys_gl_accounts')->where('code', '1100')->value('id');
    }

    protected function getInvoiceItemsWithAccounts($invoice_id)
    {
        if ($this->tableExists('sys_invoice_items')) {
            $rows = $this->db->table('sys_invoice_items')
                ->join('sys_items', 'sys_invoice_items.item_id', '=', 'sys_items.id')
                ->where('sys_invoice_items.invoice_id', $invoice_id)
                ->select(
                    $this->db->raw('COALESCE(sys_items.gl_revenue_account_id, sys_items.sell_account) as account_id'),
                    $this->db->raw('(sys_invoice_items.quantity * sys_invoice_items.unit_price) as line_total')
                )
                ->get();

            return $rows ?: [];
        }

        if ($this->tableExists('sys_invoiceitems')) {
            $rows = $this->db->table('sys_invoiceitems')
                ->leftJoin('sys_items', 'sys_invoiceitems.relid', '=', 'sys_items.id')
                ->where('sys_invoiceitems.invoiceid', $invoice_id)
                ->select(
                    $this->db->raw('COALESCE(sys_items.sell_account, 0) as account_id'),
                    $this->db->raw('COALESCE(sys_invoiceitems.total, (CAST(sys_invoiceitems.qty AS DECIMAL(16,4)) * sys_invoiceitems.amount), 0) as line_total')
                )
                ->get();

            return $rows ?: [];
        }

        return [];
    }

    protected function extractInvoiceVATAmount($invoice)
    {
        if (isset($invoice->vat_amount)) {
            return (float) $invoice->vat_amount;
        }

        if (isset($invoice->tax_total)) {
            return (float) $invoice->tax_total;
        }

        $tax_1 = isset($invoice->tax) ? (float) $invoice->tax : 0.0;
        $tax_2 = isset($invoice->tax2) ? (float) $invoice->tax2 : 0.0;

        return $tax_1 + $tax_2;
    }
}
