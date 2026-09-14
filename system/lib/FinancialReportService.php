<?php

/**
 * ============================================================
 * FINANCIAL REPORTS SERVICE
 * ============================================================
 * Generates financial reports from General Ledger
 * - Trial Balance
 * - Profit & Loss (Income Statement)
 * - Balance Sheet
 * - Cash Flow Statement
 * - Account Details
 * 
 * PRINCIPLE: All reports derived ONLY from GL (sys_journal_items)
 * ============================================================
 */

class FinancialReportService
{
    protected $db;
    protected $je_service;

    public function __construct($db = null, $je_service = null)
    {
        $this->db = $db ?: \Illuminate\Database\Capsule\Manager::connection();
        $this->je_service = $je_service ?: new JournalEntryService($db);
    }

    /**
     * ============================================================
     * TRIAL BALANCE
     * ============================================================
     * Lists all GL accounts with their debit/credit balances
     * Total Debits MUST equal Total Credits (validation)
     * 
     * @param string $as_of_date YYYY-MM-DD or null for current
     * @return array
     */
    public function getTrialBalance($as_of_date = null)
    {
        if (!$as_of_date) {
            $as_of_date = date('Y-m-d');
        }

        try {
            // Get all posted journal items up to date
            $items = $this->db->table('sys_journal_items')
                ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                ->join('sys_gl_accounts', 'sys_journal_items.account_id', '=', 'sys_gl_accounts.id')
                ->where('sys_journal_entries.post_status', 'posted')
                ->where('sys_journal_entries.entry_date', '<=', $as_of_date)
                ->select(
                    'sys_journal_items.account_id',
                    'sys_gl_accounts.code',
                    'sys_gl_accounts.name',
                    'sys_gl_accounts.type',
                    'sys_journal_items.debit',
                    'sys_journal_items.credit'
                )
                ->get();

            // Aggregate by account
            $accounts = [];
            $total_debit = 0;
            $total_credit = 0;

            foreach ($items as $item) {
                $acc_key = $item->account_id;

                if (!isset($accounts[$acc_key])) {
                    $accounts[$acc_key] = [
                        'account_id' => $item->account_id,
                        'code' => $item->code,
                        'name' => $item->name,
                        'type' => $item->type,
                        'debit' => 0,
                        'credit' => 0
                    ];
                }

                $accounts[$acc_key]['debit'] += $item->debit;
                $accounts[$acc_key]['credit'] += $item->credit;
                $total_debit += $item->debit;
                $total_credit += $item->credit;
            }

            // Sort by code
            uasort($accounts, function ($a, $b) {
                return strcmp($a['code'], $b['code']);
            });

            // Validate balance
            $difference = abs(round($total_debit, 2) - round($total_credit, 2));
            $is_balanced = $difference < 0.01;

            return [
                'success' => true,
                'as_of_date' => $as_of_date,
                'accounts' => array_values($accounts),
                'totals' => [
                    'debit' => round($total_debit, 2),
                    'credit' => round($total_credit, 2),
                    'difference' => round($difference, 2)
                ],
                'is_balanced' => $is_balanced,
                'integrity_status' => $is_balanced ? 'PASS' : 'FAIL'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating trial balance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * PROFIT & LOSS STATEMENT (Income Statement)
     * ============================================================
     * Revenue - Expenses = Net Income
     * 
     * @param string $from_date YYYY-MM-DD
     * @param string $to_date YYYY-MM-DD
     * @return array
     */
    public function getProfitLoss($from_date, $to_date)
    {
        try {
            // Get revenue accounts
            $revenue = $this->db->table('sys_journal_items')
                ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                ->join('sys_gl_accounts', 'sys_journal_items.account_id', '=', 'sys_gl_accounts.id')
                ->where('sys_gl_accounts.type', 'revenue')
                ->where('sys_journal_entries.post_status', 'posted')
                ->whereBetween('sys_journal_entries.entry_date', [$from_date, $to_date])
                ->select(
                    'sys_journal_items.account_id',
                    'sys_gl_accounts.code',
                    'sys_gl_accounts.name',
                    $this->db->raw('SUM(sys_journal_items.credit) - SUM(sys_journal_items.debit) as amount')
                )
                ->groupBy('sys_journal_items.account_id', 'sys_gl_accounts.code', 'sys_gl_accounts.name')
                ->get();

            // Get expense accounts
            $expenses = $this->db->table('sys_journal_items')
                ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                ->join('sys_gl_accounts', 'sys_journal_items.account_id', '=', 'sys_gl_accounts.id')
                ->where('sys_gl_accounts.type', 'expense')
                ->where('sys_journal_entries.post_status', 'posted')
                ->whereBetween('sys_journal_entries.entry_date', [$from_date, $to_date])
                ->select(
                    'sys_journal_items.account_id',
                    'sys_gl_accounts.code',
                    'sys_gl_accounts.name',
                    $this->db->raw('SUM(sys_journal_items.debit) - SUM(sys_journal_items.credit) as amount')
                )
                ->groupBy('sys_journal_items.account_id', 'sys_gl_accounts.code', 'sys_gl_accounts.name')
                ->get();

            // Aggregate
            $total_revenue = 0;
            $total_expense = 0;

            foreach ($revenue as $row) {
                $total_revenue += $row->amount;
            }

            foreach ($expenses as $row) {
                $total_expense += $row->amount;
            }

            $net_income = $total_revenue - $total_expense;

            return [
                'success' => true,
                'period' => ['from' => $from_date, 'to' => $to_date],
                'revenue' => [
                    'items' => $revenue->toArray(),
                    'total' => round($total_revenue, 2)
                ],
                'expenses' => [
                    'items' => $expenses->toArray(),
                    'total' => round($total_expense, 2)
                ],
                'net_income' => round($net_income, 2)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating P&L: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * BALANCE SHEET
     * ============================================================
     * Assets = Liabilities + Equity
     * 
     * @param string $as_of_date YYYY-MM-DD
     * @return array
     */
    public function getBalanceSheet($as_of_date = null)
    {
        if (!$as_of_date) {
            $as_of_date = date('Y-m-d');
        }

        try {
            // Get accounts by type
            $items = $this->db->table('sys_journal_items')
                ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                ->join('sys_gl_accounts', 'sys_journal_items.account_id', '=', 'sys_gl_accounts.id')
                ->where('sys_journal_entries.post_status', 'posted')
                ->where('sys_journal_entries.entry_date', '<=', $as_of_date)
                ->whereIn('sys_gl_accounts.type', ['asset', 'liability', 'equity'])
                ->select(
                    'sys_journal_items.account_id',
                    'sys_gl_accounts.code',
                    'sys_gl_accounts.name',
                    'sys_gl_accounts.type',
                    'sys_journal_items.debit',
                    'sys_journal_items.credit'
                )
                ->get();

            // Aggregate by type
            $assets = [];
            $liabilities = [];
            $equity = [];

            $total_assets = 0;
            $total_liabilities = 0;
            $total_equity = 0;

            foreach ($items as $item) {
                $acc_key = $item->account_id;
                $balance = $item->debit - $item->credit;

                if ($item->type === 'asset') {
                    if (!isset($assets[$acc_key])) {
                        $assets[$acc_key] = [
                            'code' => $item->code,
                            'name' => $item->name,
                            'balance' => 0
                        ];
                    }
                    $assets[$acc_key]['balance'] += $balance;
                    $total_assets += $balance;

                } elseif ($item->type === 'liability') {
                    if (!isset($liabilities[$acc_key])) {
                        $liabilities[$acc_key] = [
                            'code' => $item->code,
                            'name' => $item->name,
                            'balance' => 0
                        ];
                    }
                    $liabilities[$acc_key]['balance'] += $balance;
                    $total_liabilities += $balance;

                } elseif ($item->type === 'equity') {
                    if (!isset($equity[$acc_key])) {
                        $equity[$acc_key] = [
                            'code' => $item->code,
                            'name' => $item->name,
                            'balance' => 0
                        ];
                    }
                    $equity[$acc_key]['balance'] += $balance;
                    $total_equity += $balance;
                }
            }

            // Add net income from current period
            $pl = $this->getProfitLoss($as_of_date, $as_of_date);
            if ($pl['success'] && $pl['net_income'] != 0) {
                $total_equity += $pl['net_income'];
            }

            // Validate balance sheet equation
            $equation_balanced = abs(round($total_assets, 2) - (round($total_liabilities, 2) + round($total_equity, 2))) < 0.01;

            return [
                'success' => true,
                'as_of_date' => $as_of_date,
                'assets' => [
                    'items' => array_values($assets),
                    'total' => round($total_assets, 2)
                ],
                'liabilities' => [
                    'items' => array_values($liabilities),
                    'total' => round($total_liabilities, 2)
                ],
                'equity' => [
                    'items' => array_values($equity),
                    'total' => round($total_equity, 2)
                ],
                'totals' => [
                    'assets' => round($total_assets, 2),
                    'liabilities_plus_equity' => round($total_liabilities + $total_equity, 2)
                ],
                'equation_balanced' => $equation_balanced
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating balance sheet: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * CASH FLOW STATEMENT
     * ============================================================
     * Indirect method: Net Income → Operating / Investing / Financing
     * 
     * @param string $from_date
     * @param string $to_date
     * @return array
     */
    public function getCashFlowStatement($from_date, $to_date)
    {
        try {
            // Get net income
            $pl = $this->getProfitLoss($from_date, $to_date);
            $net_income = $pl['net_income'] ?? 0;

            // Get cash movements from bank accounts
            $cash_flows = $this->db->table('sys_journal_items')
                ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                ->join('sys_gl_accounts', 'sys_journal_items.account_id', '=', 'sys_gl_accounts.id')
                ->where('sys_gl_accounts.type', 'asset')
                ->where('sys_gl_accounts.name', 'LIKE', '%Bank%')
                ->where('sys_journal_entries.post_status', 'posted')
                ->whereBetween('sys_journal_entries.entry_date', [$from_date, $to_date])
                ->select(
                    'sys_journal_entries.source_module',
                    $this->db->raw('SUM(sys_journal_items.debit) - SUM(sys_journal_items.credit) as flow')
                )
                ->groupBy('sys_journal_entries.source_module')
                ->get();

            // Categorize cash flows
            $operating = 0;
            $investing = 0;
            $financing = 0;

            foreach ($cash_flows as $flow) {
                if ($flow->source_module === 'payment') {
                    $operating += $flow->flow;
                } elseif ($flow->source_module === 'bank_transfer') {
                    $investing += $flow->flow;
                }
            }

            return [
                'success' => true,
                'period' => ['from' => $from_date, 'to' => $to_date],
                'operating_activities' => round($operating, 2),
                'investing_activities' => round($investing, 2),
                'financing_activities' => round($financing, 2),
                'net_change_in_cash' => round($operating + $investing + $financing, 2),
                'net_income_source' => round($net_income, 2)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating cash flow: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * ACCOUNT DETAILS (Ledger)
     * ============================================================
     * Show all transactions for a specific GL account
     * 
     * @param int $account_id
     * @param string $from_date
     * @param string $to_date
     * @return array
     */
    public function getAccountLedger($account_id, $from_date = null, $to_date = null)
    {
        try {
            $account = $this->db->table('sys_gl_accounts')
                ->where('id', $account_id)
                ->first();

            if (!$account) {
                return ['success' => false, 'message' => 'Account not found'];
            }

            $query = $this->db->table('sys_journal_items')
                ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                ->where('sys_journal_items.account_id', $account_id)
                ->where('sys_journal_entries.post_status', 'posted');

            if ($from_date && $to_date) {
                $query->whereBetween('sys_journal_entries.entry_date', [$from_date, $to_date]);
            }

            $items = $query->select(
                'sys_journal_entries.id',
                'sys_journal_entries.entry_date',
                'sys_journal_entries.reference',
                'sys_journal_entries.description',
                'sys_journal_items.debit',
                'sys_journal_items.credit'
            )
            ->orderBy('sys_journal_entries.entry_date')
            ->get();

            // Calculate running balance
            $running_balance = 0;
            $ledger = [];

            foreach ($items as $item) {
                $running_balance += $item->debit - $item->credit;

                $ledger[] = [
                    'date' => $item->entry_date,
                    'reference' => $item->reference,
                    'description' => $item->description,
                    'debit' => (float)$item->debit,
                    'credit' => (float)$item->credit,
                    'balance' => round($running_balance, 2)
                ];
            }

            return [
                'success' => true,
                'account' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type
                ],
                'transactions' => $ledger,
                'period' => ['from' => $from_date, 'to' => $to_date],
                'transaction_count' => count($ledger)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting account ledger: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * GENERAL LEDGER (All Accounts)
     * ============================================================
     * Full GL listing with all transactions
     * 
     * @param string $from_date
     * @param string $to_date
     * @return array
     */
    public function getGeneralLedger($from_date, $to_date)
    {
        try {
            $items = $this->db->table('sys_journal_items')
                ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                ->join('sys_gl_accounts', 'sys_journal_items.account_id', '=', 'sys_gl_accounts.id')
                ->where('sys_journal_entries.post_status', 'posted')
                ->whereBetween('sys_journal_entries.entry_date', [$from_date, $to_date])
                ->select(
                    'sys_gl_accounts.code',
                    'sys_gl_accounts.name',
                    'sys_gl_accounts.type',
                    'sys_journal_entries.entry_date',
                    'sys_journal_entries.reference',
                    'sys_journal_entries.description',
                    'sys_journal_items.debit',
                    'sys_journal_items.credit'
                )
                ->orderBy('sys_journal_entries.entry_date')
                ->orderBy('sys_gl_accounts.code')
                ->get();

            return [
                'success' => true,
                'period' => ['from' => $from_date, 'to' => $to_date],
                'transactions' => $items->toArray(),
                'transaction_count' => count($items)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting general ledger: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * ACCOUNT BALANCES SNAPSHOT
     * ============================================================
     * Current balance of all accounts
     */
    public function getAccountBalancesSnapshot($as_of_date = null)
    {
        if (!$as_of_date) {
            $as_of_date = date('Y-m-d');
        }

        try {
            $balances = $this->db->table('sys_gl_account_balances')
                ->join('sys_gl_accounts', 'sys_gl_account_balances.account_id', '=', 'sys_gl_accounts.id')
                ->select(
                    'sys_gl_accounts.code',
                    'sys_gl_accounts.name',
                    'sys_gl_accounts.type',
                    'sys_gl_account_balances.debit_balance',
                    'sys_gl_account_balances.credit_balance',
                    'sys_gl_account_balances.net_balance'
                )
                ->orderBy('sys_gl_accounts.code')
                ->get();

            return [
                'success' => true,
                'as_of_date' => $as_of_date,
                'accounts' => $balances->toArray(),
                'snapshot_time' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting account balances: ' . $e->getMessage()
            ];
        }
    }
}
