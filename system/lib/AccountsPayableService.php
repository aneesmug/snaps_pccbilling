<?php

/**
 * ============================================================
 * ACCOUNTS PAYABLE SERVICE
 * ============================================================
 * Manages vendor bills and payables
 * - Posts bills to AP & GL
 * - Records vendor payments
 * - Generates AP aging reports
 * - Handles recurring bills
 * - Manages payment terms and schedules
 * 
 * GL Accounts Used:
 * - Accounts Payable (Control account)
 * - Expense (various)
 * - VAT Recoverable (Input Tax)
 * - Bank/Cash
 * ============================================================
 */

class AccountsPayableService
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
     * POST BILL TO AP & GL
     * ============================================================
     * When bill is approved/posted:
     * 
     *   Dr Expense (by category)
     *   Dr VAT Recoverable (Input Tax)
     *   Cr Accounts Payable (AP control account)
     * 
     * @param int $bill_id Bill from sys_bills
     * @return array
     */
    public function postBillToAP($bill_id)
    {
        try {
            $bill_table = $this->getBillTableName();
            if (!$bill_table) {
                return ['success' => false, 'message' => 'No bill/purchase table found in current schema'];
            }

            $bill = $this->db->table($bill_table)
                ->where('id', $bill_id)
                ->first();

            if (!$bill) {
                return ['success' => false, 'message' => 'Bill not found'];
            }

            if (!empty($bill->journal_entry_id)) {
                return ['success' => false, 'message' => 'Bill already posted to GL'];
            }

            $vendor_id = $this->extractBillVendorId($bill);
            if (!$vendor_id) {
                return ['success' => false, 'message' => 'Bill is missing vendor reference'];
            }

            $vendor = $this->getContactRecord($vendor_id, 'Vendor');
            if (!$vendor) {
                return ['success' => false, 'message' => 'Vendor not found'];
            }

            $ap_account_id = $this->resolveAPAccountId($vendor);
            if (!$ap_account_id) {
                return ['success' => false, 'message' => 'AP account not configured'];
            }

            $vendor_name = $this->extractContactName($vendor);

            $bill_items = $this->getBillItemsWithAccounts($bill_id);
            if (empty($bill_items)) {
                return ['success' => false, 'message' => 'Bill items not found or missing account mapping'];
            }

            // Aggregate expense lines
            $expense_lines = [];
            $total_expense = 0;

            foreach ($bill_items as $item) {
                $account_id = (int) ($item->account_id ?? 0);
                if ($account_id <= 0) {
                    continue;
                }

                $item_amount = (float) ($item->line_total ?? 0);
                if ($item_amount <= 0) {
                    continue;
                }

                $total_expense += $item_amount;

                if (!isset($expense_lines[$account_id])) {
                    $expense_lines[$account_id] = 0;
                }
                $expense_lines[$account_id] += $item_amount;
            }

            if (empty($expense_lines)) {
                return ['success' => false, 'message' => 'No valid expense lines found'];
            }

            $bill_number = $bill->bill_number ?? $bill->invoicenum ?? $bill->code ?? ('BILL-' . $bill_id);
            $vat_amount = $this->extractBillVATAmount($bill);

            // Build JE lines
            $je_lines = [];

            // Lines 1+: Dr Expense accounts
            foreach ($expense_lines as $account_id => $amount) {
                $je_lines[] = [
                    'account_id' => $account_id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'Bill ' . $bill_number . ' expense'
                ];
            }

            // Line N: Dr VAT Recoverable (if applicable)
            if ($vat_amount > 0) {
                $vat_recoverable_account = $this->getTaxVATRecoverableAccount($bill->id);
                if ($vat_recoverable_account) {
                    $je_lines[] = [
                        'account_id' => $vat_recoverable_account,
                        'debit' => $vat_amount,
                        'credit' => 0,
                        'description' => 'VAT Recoverable on Bill ' . $bill_number
                    ];
                }
            }

            // Final line: Cr Accounts Payable (control account)
            $je_lines[] = [
                'account_id' => $ap_account_id,
                'debit' => 0,
                'credit' => $total_expense + $vat_amount,
                'description' => 'Bill ' . $bill_number . ' from ' . $vendor_name
            ];

            // Create JE using JournalEntryService
            $je_result = $this->je_service->createAndPostJournalEntry([
                'entry_date' => date('Y-m-d'),
                'reference' => 'BILL-' . $bill_number,
                'description' => 'Bill posting: ' . $bill_number,
                'source_module' => 'bill',
                'source_id' => $bill_id,
                'lines' => $je_lines
            ]);

            if ($je_result['success']) {
                // Update bill with JE reference
                $bill_updates = [];
                if ($this->columnExists($bill_table, 'journal_entry_id')) {
                    $bill_updates['journal_entry_id'] = $je_result['journal_entry_id'];
                }
                if ($this->columnExists($bill_table, 'status')) {
                    $bill_updates['status'] = 'approved';
                }
                if (!empty($bill_updates)) {
                    $this->db->table($bill_table)
                        ->where('id', $bill_id)
                        ->update($bill_updates);
                }

                // Create AP ledger entry for aging reports
                $this->db->table('sys_ap_ledger')->insert([
                    'vendor_id' => $vendor_id,
                    'journal_entry_id' => $je_result['journal_entry_id'],
                    'bill_id' => $bill_id,
                    'amount' => $total_expense + $vat_amount,
                    'transaction_type' => 'bill',
                    'posting_date' => date('Y-m-d'),
                    'due_date' => ($bill->due_date ?? $bill->duedate ?? date('Y-m-d', strtotime('+30 days')))
                ]);
            }

            return $je_result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error posting bill to AP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * RECORD VENDOR PAYMENT
     * ============================================================
     * When payment is made to vendor:
     * 
     *   Dr Accounts Payable
     *   Cr Bank/Cash Account
     * 
     * @param array $data {
     *     'vendor_id' => 123,
     *     'bank_account_id' => 1,
     *     'amount' => 1000.00,
     *     'payment_date' => '2026-04-27',
     *     'reference' => 'CHQ-001',
     *     'allocations' => [
     *         ['bill_id' => 100, 'amount' => 500.00],
     *         ['bill_id' => 101, 'amount' => 500.00],
     *     ]
     * }
     * @return array
     */
    public function recordVendorPayment($data)
    {
        try {
            $this->db->beginTransaction();

            // Validate
            if (empty($data['vendor_id']) || empty($data['bank_account_id']) || empty($data['amount'])) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            // Get vendor AP account
            $vendor = $this->getContactRecord($data['vendor_id'], 'Vendor');
            $ap_account_id = $this->resolveAPAccountId($vendor);

            if (!$vendor || !$ap_account_id) {
                return ['success' => false, 'message' => 'Vendor or AP account not found'];
            }

            // Get bank account GL account
            $bank_account = $this->db->table('sys_bank_accounts')
                ->where('id', $data['bank_account_id'])
                ->first();

            if (!$bank_account) {
                return ['success' => false, 'message' => 'Bank account not found'];
            }

            // Create JE: Dr AP Cr Bank
            $je_result = $this->je_service->createAndPostJournalEntry([
                'entry_date' => $data['payment_date'] ?? date('Y-m-d'),
                'reference' => 'PAY-' . ($data['reference'] ?? 'DISBURSEMENT'),
                'description' => 'Payment to Vendor ' . $this->extractContactName($vendor),
                'source_module' => 'payment',
                'source_id' => null,
                'lines' => [
                    [
                        'account_id' => $ap_account_id,
                        'debit' => $data['amount'],
                        'credit' => 0,
                        'description' => 'AP reduction'
                    ],
                    [
                        'account_id' => $bank_account->gl_cash_account_id,
                        'debit' => 0,
                        'credit' => $data['amount'],
                        'description' => 'Cash payment'
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
                ->decrement('balance', $data['amount']);

            // Record in AP ledger
            $this->db->table('sys_ap_ledger')->insert([
                'vendor_id' => $data['vendor_id'],
                'journal_entry_id' => $je_result['journal_entry_id'],
                'bill_id' => null,
                'amount' => -$data['amount'],  // Negative for payment
                'transaction_type' => 'payment',
                'posting_date' => $data['payment_date'] ?? date('Y-m-d'),
                'is_allocated' => !empty($data['allocations']) ? 1 : 0
            ]);

            // Allocate to bills if specified
            if (!empty($data['allocations'])) {
                $this->allocatePaymentToBills(
                    $je_result['journal_entry_id'],
                    $data['vendor_id'],
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
     * ALLOCATE PAYMENT TO BILLS
     * ============================================================
     */
    protected function allocatePaymentToBills($je_id, $vendor_id, $allocations)
    {
        foreach ($allocations as $alloc) {
            $this->db->table('sys_ap_ledger')
                ->where('vendor_id', $vendor_id)
                ->where('bill_id', $alloc['bill_id'])
                ->update(['is_allocated' => 1]);
        }
    }

    /**
     * ============================================================
     * AP AGING REPORT
     * ============================================================
     * Generates AP aging by vendor
     * Buckets: Current, 30, 60, 90+ days
     * 
     * @return array
     */
    public function getAPAgingReport()
    {
        $today = date('Y-m-d');
        
        $results = $this->db->table('sys_ap_ledger')
            ->leftJoin('crm_accounts', 'sys_ap_ledger.vendor_id', '=', 'crm_accounts.id')
            ->where('sys_ap_ledger.transaction_type', 'bill')
            ->where('sys_ap_ledger.is_allocated', 0)  // Outstanding only
            ->select(
                'sys_ap_ledger.vendor_id',
                $this->db->raw("COALESCE(crm_accounts.display_name, crm_accounts.company, crm_accounts.account, TRIM(CONCAT(COALESCE(crm_accounts.fname,''), ' ', COALESCE(crm_accounts.lname,''))), 'Unknown') as name"),
                'sys_ap_ledger.amount',
                'sys_ap_ledger.due_date'
            )
            ->get();

        $aging = [];

        foreach ($results as $row) {
            $due_date = strtotime($row->due_date);
            $today_ts = strtotime($today);
            $days_overdue = floor(($today_ts - $due_date) / 86400);

            if (!isset($aging[$row->vendor_id])) {
                $aging[$row->vendor_id] = [
                    'vendor_name' => $row->name,
                    'current' => 0,
                    '30' => 0,
                    '60' => 0,
                    '90' => 0
                ];
            }

            if ($days_overdue <= 0) {
                $aging[$row->vendor_id]['current'] += $row->amount;
            } elseif ($days_overdue <= 30) {
                $aging[$row->vendor_id]['30'] += $row->amount;
            } elseif ($days_overdue <= 60) {
                $aging[$row->vendor_id]['60'] += $row->amount;
            } else {
                $aging[$row->vendor_id]['90'] += $row->amount;
            }
        }

        return $aging;
    }

    /**
     * ============================================================
     * VENDOR STATEMENT
     * ============================================================
     */
    public function getVendorStatement($vendor_id, $from_date = null, $to_date = null)
    {
        $query = $this->db->table('sys_ap_ledger')
            ->where('vendor_id', $vendor_id);

        if ($from_date && $to_date) {
            $query->whereBetween('posting_date', [$from_date, $to_date]);
        }

        return $query->orderBy('posting_date')
            ->get();
    }

    /**
     * ============================================================
     * RECORD BILL DISCOUNT / CREDIT MEMO
     * ============================================================
     * Handle vendor credits or early payment discounts
     * 
     *   Dr Accounts Payable
     *   Cr Discount Income / Expense Reduction
     */
    public function recordBillCredit($vendor_id, $amount, $reason = 'Credit memo')
    {
        try {
            $vendor = $this->getContactRecord($vendor_id, 'Vendor');
            $ap_account_id = $this->resolveAPAccountId($vendor);

            if (!$vendor || !$ap_account_id) {
                return ['success' => false, 'message' => 'Vendor not found'];
            }

            $discount_account = $this->getPurchaseDiscountAccount();
            if (!$discount_account) {
                return ['success' => false, 'message' => 'Purchase discount account not configured'];
            }

            $je_result = $this->je_service->createAndPostJournalEntry([
                'entry_date' => date('Y-m-d'),
                'reference' => 'CREDIT-' . $vendor_id,
                'description' => $reason . ': ' . $this->extractContactName($vendor),
                'source_module' => 'ap_credit',
                'source_id' => $vendor_id,
                'lines' => [
                    [
                        'account_id' => $ap_account_id,
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => 'AP credit'
                    ],
                    [
                        'account_id' => $discount_account,
                        'debit' => 0,
                        'credit' => $amount,
                        'description' => $reason
                    ]
                ]
            ]);

            return $je_result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error recording bill credit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * GET AP SUMMARY
     * ============================================================
     */
    public function getAPSummary()
    {
        $outstanding = $this->db->selectOne("
            SELECT 
                COUNT(DISTINCT vendor_id) as vendor_count,
                SUM(CASE WHEN transaction_type = 'bill' THEN amount ELSE 0 END) as total_billed,
                SUM(CASE WHEN transaction_type = 'payment' THEN amount ELSE 0 END) as total_paid,
                SUM(amount) as net_ap
            FROM sys_ap_ledger
            WHERE posting_date <= DATE(date('Y-m-d H:i:s'))
        ");

        return [
            'total_vendors' => $outstanding->vendor_count ?? 0,
            'total_billed' => (float)($outstanding->total_billed ?? 0),
            'total_paid' => (float)($outstanding->total_paid ?? 0),
            'net_ap_outstanding' => (float)($outstanding->net_ap ?? 0)
        ];
    }

    /**
     * ============================================================
     * HELPER: Get VAT Recoverable Account
     * ============================================================
     */
    protected function getTaxVATRecoverableAccount($bill_id)
    {
        $bill_table = $this->getBillTableName();
        if (!$bill_table) {
            return null;
        }

        $bill = $this->db->table($bill_table)->where('id', $bill_id)->first();
        if (!$bill) {
            return null;
        }

        $vat_code = $bill->vat_code ?? $bill->tax_code ?? null;
        if (!$vat_code && !empty($bill->taxname)) {
            $vat_code = 'VAT-15';
        }

        if (!$vat_code) {
            return null;
        }

        return $this->db->table('sys_tax_codes')
            ->where('code', $vat_code)
            ->value('gl_recoverable_account_id');
    }

    /**
     * ============================================================
     * HELPER: Get Purchase Discount Account
     * ============================================================
     */
    protected function getPurchaseDiscountAccount()
    {
        return $this->db->table('sys_gl_accounts')
            ->where('code', '5200')  // Adjust to your chart of accounts
            ->where('type', 'expense')
            ->value('id');
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

    protected function getBillTableName()
    {
        if ($this->tableExists('sys_bills')) {
            return 'sys_bills';
        }

        if ($this->tableExists('sys_purchases')) {
            return 'sys_purchases';
        }

        return null;
    }

    protected function extractBillVendorId($bill)
    {
        if (!empty($bill->vendor_id)) {
            return (int) $bill->vendor_id;
        }

        if (!empty($bill->supplier_id)) {
            return (int) $bill->supplier_id;
        }

        return null;
    }

    protected function getContactRecord($id, $type = 'Vendor')
    {
        if ($this->tableExists('sys_vendors') && $type === 'Vendor') {
            return $this->db->table('sys_vendors')->where('id', $id)->first();
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

    protected function resolveAPAccountId($vendor)
    {
        if (!$vendor) {
            return null;
        }

        if (isset($vendor->ap_account_id) && !empty($vendor->ap_account_id)) {
            return (int) $vendor->ap_account_id;
        }

        return (int) $this->db->table('sys_gl_accounts')->where('code', '2000')->value('id');
    }

    protected function getBillItemsWithAccounts($bill_id)
    {
        if ($this->tableExists('sys_bill_items')) {
            $rows = $this->db->table('sys_bill_items')
                ->join('sys_items', 'sys_bill_items.item_id', '=', 'sys_items.id')
                ->where('sys_bill_items.bill_id', $bill_id)
                ->select(
                    $this->db->raw('COALESCE(sys_items.gl_expense_account_id, sys_items.purchase_account) as account_id'),
                    $this->db->raw('(sys_bill_items.quantity * sys_bill_items.unit_price) as line_total')
                )
                ->get();

            return $rows ?: [];
        }

        if ($this->tableExists('sys_purchaseitems')) {
            $rows = $this->db->table('sys_purchaseitems')
                ->leftJoin('sys_items', 'sys_purchaseitems.relid', '=', 'sys_items.id')
                ->where('sys_purchaseitems.invoiceid', $bill_id)
                ->select(
                    $this->db->raw('COALESCE(sys_items.purchase_account, 0) as account_id'),
                    $this->db->raw('COALESCE(sys_purchaseitems.total, (CAST(sys_purchaseitems.qty AS DECIMAL(16,4)) * sys_purchaseitems.amount), 0) as line_total')
                )
                ->get();

            return $rows ?: [];
        }

        return [];
    }

    protected function extractBillVATAmount($bill)
    {
        if (isset($bill->vat_amount)) {
            return (float) $bill->vat_amount;
        }

        if (isset($bill->tax_total)) {
            return (float) $bill->tax_total;
        }

        $tax_1 = isset($bill->tax) ? (float) $bill->tax : 0.0;
        $tax_2 = isset($bill->tax2) ? (float) $bill->tax2 : 0.0;

        return $tax_1 + $tax_2;
    }
}
