<?php

/**
 * ============================================================
 * ACCOUNTING SYSTEM - QUICK START INITIALIZATION SCRIPT
 * ============================================================
 * Run this once to initialize the accounting system
 * 
 * Usage: php system/lib/AccountingInitializer.php
 * 
 * Creates:
 * - Chart of Accounts (COA)
 * - Default GL accounts for all transaction types
 * - Accounting periods
 * - Tax codes (ZATCA compliant)
 * - Default GL-to-subsystem linkages
 * ============================================================
 */

class AccountingInitializer
{
    protected $db;

    public function __construct()
    {
        $this->db = DB::connection();
    }

    /**
     * Run complete initialization
     */
    public function initialize()
    {
        echo "🔧 Initializing Accounting System...\n\n";

        try {
            $this->createChartOfAccounts();
            echo "✓ Chart of Accounts created\n";

            $this->createAccountingPeriods();
            echo "✓ Accounting Periods created\n";

            $this->createTaxCodes();
            echo "✓ Tax Codes created\n";

            $this->linkCustomersToAR();
            echo "✓ Customers linked to AR\n";

            $this->linkVendorsToAP();
            echo "✓ Vendors linked to AP\n";

            echo "\n✅ Accounting System initialized successfully!\n";
            return true;

        } catch (\Exception $e) {
            echo "\n❌ Initialization failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Create Chart of Accounts
     */
    protected function createChartOfAccounts()
    {
        $accounts = [
            // ASSETS
            ['code' => '1000', 'name' => 'Cash - General', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1010', 'name' => 'Cash - Petty', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'is_control_account' => 1],
            ['code' => '1150', 'name' => 'Allowance for Doubtful Accounts', 'type' => 'asset', 'normal_balance' => 'credit'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1250', 'name' => 'VAT Recoverable (Input Tax)', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1500', 'name' => 'Fixed Assets', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1510', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'normal_balance' => 'credit'],

            // LIABILITIES
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'is_control_account' => 1],
            ['code' => '2100', 'name' => 'VAT Payable (Output Tax)', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2200', 'name' => 'Accrued Expenses', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2500', 'name' => 'Short-term Debt', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2600', 'name' => 'Long-term Debt', 'type' => 'liability', 'normal_balance' => 'credit'],

            // EQUITY
            ['code' => '3000', 'name' => 'Capital Stock', 'type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '3200', 'name' => 'Drawings', 'type' => 'equity', 'normal_balance' => 'debit'],

            // REVENUE
            ['code' => '4000', 'name' => 'Sales Revenue - Domestic', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4010', 'name' => 'Sales Revenue - Export', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4100', 'name' => 'Service Revenue', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4200', 'name' => 'Interest Income', 'type' => 'revenue', 'normal_balance' => 'credit'],

            // EXPENSES
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5100', 'name' => 'Bad Debt Expense', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5200', 'name' => 'Purchase Discount', 'type' => 'expense', 'normal_balance' => 'credit'],
            ['code' => '5300', 'name' => 'Salaries & Wages', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5400', 'name' => 'Rent Expense', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5500', 'name' => 'Utilities', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5600', 'name' => 'Office Supplies', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5700', 'name' => 'Depreciation Expense', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5800', 'name' => 'Interest Expense', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $acc) {
            $exists = DB::table('sys_gl_accounts')
                ->where('code', $acc['code'])
                ->exists();

            if (!$exists) {
                DB::table('sys_gl_accounts')->insert(array_merge($acc, [
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    /**
     * Create Accounting Periods (12 months of 2026)
     */
    protected function createAccountingPeriods()
    {
        $year = 2026;
        $months = [
            ['month' => 1, 'name' => 'January'],
            ['month' => 2, 'name' => 'February'],
            ['month' => 3, 'name' => 'March'],
            ['month' => 4, 'name' => 'April'],
            ['month' => 5, 'name' => 'May'],
            ['month' => 6, 'name' => 'June'],
            ['month' => 7, 'name' => 'July'],
            ['month' => 8, 'name' => 'August'],
            ['month' => 9, 'name' => 'September'],
            ['month' => 10, 'name' => 'October'],
            ['month' => 11, 'name' => 'November'],
            ['month' => 12, 'name' => 'December'],
        ];

        foreach ($months as $m) {
            $start = date('Y-m-d', strtotime("$year-{$m['month']}-01"));
            $end = date('Y-m-d', strtotime("last day of $start"));

            $exists = DB::table('sys_accounting_periods')
                ->where('start_date', $start)
                ->exists();

            if (!$exists) {
                DB::table('sys_accounting_periods')->insert([
                    'period_name' => "{$year}-{$m['name']}",
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => date('Y-m') === date('Y-m', strtotime($start)) ? 'open' : 'open',
                    'created_at' => now()
                ]);
            }
        }
    }

    /**
     * Create Tax Codes (ZATCA Compliant)
     */
    protected function createTaxCodes()
    {
        // Get GL accounts
        $vat_payable = DB::table('sys_gl_accounts')
            ->where('code', '2100')
            ->value('id');

        $vat_recoverable = DB::table('sys_gl_accounts')
            ->where('code', '1250')
            ->value('id');

        $tax_codes = [
            [
                'code' => 'VAT-15',
                'name' => 'Standard VAT (15%)',
                'rate' => 15.00,
                'tax_type' => 'both',
                'gl_payable_account_id' => $vat_payable,
                'gl_recoverable_account_id' => $vat_recoverable,
                'is_zatca_compliant' => 1
            ],
            [
                'code' => 'VAT-0',
                'name' => 'Zero-Rated (Exports)',
                'rate' => 0.00,
                'tax_type' => 'sales',
                'gl_payable_account_id' => $vat_payable,
                'gl_recoverable_account_id' => null,
                'is_zatca_compliant' => 1
            ],
            [
                'code' => 'VAT-EXEMPT',
                'name' => 'Tax Exempt',
                'rate' => 0.00,
                'tax_type' => 'both',
                'gl_payable_account_id' => null,
                'gl_recoverable_account_id' => null,
                'is_zatca_compliant' => 1
            ],
        ];

        foreach ($tax_codes as $tc) {
            $exists = DB::table('sys_tax_codes')
                ->where('code', $tc['code'])
                ->exists();

            if (!$exists) {
                DB::table('sys_tax_codes')->insert(array_merge($tc, [
                    'is_active' => 1,
                    'created_at' => now()
                ]));
            }
        }
    }

    /**
     * Link Customers to AR Account
     */
    protected function linkCustomersToAR()
    {
        $ar_account_id = DB::table('sys_gl_accounts')
            ->where('code', '1100')
            ->value('id');

        if ($ar_account_id) {
            DB::table('sys_customers')
                ->whereNull('ar_account_id')
                ->update(['ar_account_id' => $ar_account_id]);
        }
    }

    /**
     * Link Vendors to AP Account
     */
    protected function linkVendorsToAP()
    {
        $ap_account_id = DB::table('sys_gl_accounts')
            ->where('code', '2000')
            ->value('id');

        if ($ap_account_id) {
            DB::table('sys_vendors')
                ->whereNull('ap_account_id')
                ->update(['ap_account_id' => $ap_account_id]);
        }
    }

    /**
     * Create default bank account
     */
    public function createDefaultBankAccount()
    {
        $cash_account_id = DB::table('sys_gl_accounts')
            ->where('code', '1000')
            ->value('id');

        if (!$cash_account_id) {
            throw new \Exception('Cash GL account not found');
        }

        $exists = DB::table('sys_bank_accounts')
            ->where('name', 'Main Bank Account')
            ->exists();

        if (!$exists) {
            DB::table('sys_bank_accounts')->insert([
                'name' => 'Main Bank Account',
                'account_number' => '****',
                'bank_name' => 'Primary Bank',
                'gl_cash_account_id' => $cash_account_id,
                'balance' => 0,
                'is_active' => 1,
                'created_at' => now()
            ]);
        }
    }
}

// Run initialization if executed directly
if (php_sapi_name() === 'cli') {
    $init = new AccountingInitializer();
    $init->initialize();
    $init->createDefaultBankAccount();
}
