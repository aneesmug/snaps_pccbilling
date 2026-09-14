<?php

/**
 * ============================================================
 * ACCOUNTING SYSTEM - FRONTEND CONTROLLER
 * ============================================================
 * Add this to your accounts.php controller to handle accounting
 * module requests
 * ============================================================
 */

/*
|--------------------------------------------------------------------------
| Accounting Controller Routes
|--------------------------------------------------------------------------
|
| These routes handle all accounting system frontend pages
|
*/

// Check if this is an accounting request
$accounting_actions = [
    'gl-accounts',
    'journal-entries',
    'ar-dashboard',
    'ap-dashboard',
    'bank-reconciliation',
    'financial-reports',
    'tax-vat',
        'accounting-settings',
        'dashboard'
];

if (in_array($action, $accounting_actions)) {
    // Mark as accounting module
    $ui->assign('selected_navigation', 'accounting');
    
    switch ($action) {
        /**
         * ============================================================
         * ACCOUNTING DASHBOARD
         * ============================================================
         */
        case 'dashboard':
            if (!has_access($user->roleid, 'accounting_dashboard', 'view')) {
                access_denied();
            }

            $ui->assign('_title', $_L['Accounting System']);
            view('accounting/dashboard');
            break;
        
        /**
         * ============================================================
         * GENERAL LEDGER - CHART OF ACCOUNTS
         * ============================================================
         */
        case 'gl-accounts':
            $gl_service = new JournalEntryService();
            $controls_service = new AccountingControlsService();
            
            // Check permission
            if (!has_access($user->roleid, 'accounting_gl_accounts', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            
            switch ($sub_action) {
                case 'list':
                default:
                    if (!has_access($user->roleid, 'accounting_gl_accounts', 'view')) {
                        access_denied();
                    }

                    // Get all GL accounts
                    $accounts = GLAccount::where('is_active', 1)
                        ->orderBy('code')
                        ->get();
                    
                    // Get balances
                    $balances = GLAccountBalance::all()
                        ->keyBy('account_id');
                    
                    $ui->assign('_title', $_L['General Ledger'] . ' - ' . $_L['Chart of Accounts']);
                    $ui->assign('accounts', $accounts);
                    $ui->assign('balances', $balances);
                    
                    view('accounting/gl-accounts-list');
                    break;
                
                case 'view':
                    if (!has_access($user->roleid, 'accounting_gl_accounts', 'view')) {
                        access_denied();
                    }

                    $account_id = route(3);
                    $account = GLAccount::find($account_id);
                    
                    if (!$account) {
                        error_404();
                    }
                    
                    // Get account balance
                    $balance = GLAccountBalance::where('account_id', $account_id)
                        ->first();
                    
                    // Get account transactions (from journal items)
                    $transactions = JournalItem::where('account_id', $account_id)
                        ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
                        ->orderBy('sys_journal_entries.entry_date', 'desc')
                        ->limit(100)
                        ->get();
                    
                    $ui->assign('_title', $_L['Account Ledger'] . ' - ' . $account->name);
                    $ui->assign('account', $account);
                    $ui->assign('balance', $balance);
                    $ui->assign('transactions', $transactions);
                    
                    view('accounting/gl-account-detail');
                    break;
            }
            break;
        
        /**
         * ============================================================
         * JOURNAL ENTRIES
         * ============================================================
         */
        case 'journal-entries':
            $je_service = new JournalEntryService();
            
            if (!has_access($user->roleid, 'accounting_journal_entries', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            
            switch ($sub_action) {
                case 'create':
                    if (!has_access($user->roleid, 'accounting_journal_entries', 'create')) {
                        access_denied();
                    }

                    // Get GL accounts for dropdown
                    $accounts = GLAccount::where('is_active', 1)
                        ->orderBy('name')
                        ->get();
                    
                    $ui->assign('_title', $_L['Create Journal Entry']);
                    $ui->assign('accounts', $accounts);
                    
                    view('accounting/journal-entry-create');
                    break;
                
                case 'list':
                default:
                    if (!has_access($user->roleid, 'accounting_journal_entries', 'view')) {
                        access_denied();
                    }

                    // Get journal entries
                    $filter_status = $_GET['status'] ?? (route(3) ?? 'posted');
                    $filter_date_from = $_GET['from_date'] ?? null;
                    $filter_date_to = $_GET['to_date'] ?? null;

                    $entries = [];

                    try {
                        $db_conn = \Illuminate\Database\Capsule\Manager::connection();
                        $table_check = $db_conn->selectOne("SHOW TABLES LIKE 'sys_journal_entries'");
                        $has_journal_table = !empty((array) $table_check);

                        // If migration is not applied yet, show empty page instead of 500.
                        if ($has_journal_table) {
                            $query = JournalEntry::query();

                            $post_status_check = $db_conn->selectOne("SHOW COLUMNS FROM `sys_journal_entries` LIKE 'post_status'");
                            $entry_date_check = $db_conn->selectOne("SHOW COLUMNS FROM `sys_journal_entries` LIKE 'entry_date'");
                            $has_post_status = !empty((array) $post_status_check);
                            $has_entry_date = !empty((array) $entry_date_check);

                            if ($has_post_status && $filter_status) {
                                $query->where('post_status', $filter_status);
                            }

                            if ($has_entry_date && $filter_date_from) {
                                $query->where('entry_date', '>=', $filter_date_from);
                            }

                            if ($has_entry_date && $filter_date_to) {
                                $query->where('entry_date', '<=', $filter_date_to);
                            }

                            $order_col = $has_entry_date ? 'entry_date' : 'id';
                            $entries = $query->orderBy($order_col, 'desc')
                                ->limit(500)
                                ->get();
                        }
                    } catch (\Throwable $e) {
                        $entries = [];
                    }
                    
                    $ui->assign('_title', $_L['Journal Entries']);
                    $ui->assign('entries', $entries);
                    $ui->assign('filter_status', $filter_status);
                    
                    view('accounting/journal-entries-list');
                    break;
                
                case 'view':
                    if (!has_access($user->roleid, 'accounting_journal_entries', 'view')) {
                        access_denied();
                    }

                    $je_id = route(3);
                    $entry = JournalEntry::with('items')
                        ->find($je_id);
                    
                    if (!$entry) {
                        error_404();
                    }
                    
                    $ui->assign('_title', $_L['Journal Entry'] . ' #' . $je_id);
                    $ui->assign('entry', $entry);
                    $ui->assign('items', $entry->items);
                    
                    view('accounting/journal-entry-detail');
                    break;
            }
            break;
        
        /**
         * ============================================================
         * ACCOUNTS RECEIVABLE DASHBOARD
         * ============================================================
         */
        case 'ar-dashboard':
            $ar_service = new AccountsReceivableService();
            
            if (!has_access($user->roleid, 'accounting_ar', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            
            switch ($sub_action) {
                case 'aging':
                    if (!has_access($user->roleid, 'accounting_ar', 'view')) {
                        access_denied();
                    }

                    // Get AR aging report
                    $aging = $ar_service->getARAgingReport();
                    $summary = $ar_service->getARSummary();
                    
                    $ui->assign('_title', $_L['Accounts Receivable'] . ' - ' . $_L['Aging Report']);
                    $ui->assign('aging', $aging);
                    $ui->assign('summary', $summary);
                    
                    view('accounting/ar-aging-report');
                    break;
                
                case 'dashboard':
                default:
                    if (!has_access($user->roleid, 'accounting_ar', 'view')) {
                        access_denied();
                    }

                    // Get AR summary and key metrics
                    $summary = $ar_service->getARSummary();
                    $aging = $ar_service->getARAgingReport();
                    
                    $ui->assign('_title', $_L['Accounts Receivable Dashboard']);
                    $ui->assign('summary', $summary);
                    $ui->assign('aging', $aging);
                    
                    view('accounting/ar-dashboard');
                    break;
                
                case 'customer-statement':
                    if (!has_access($user->roleid, 'accounting_ar', 'view')) {
                        access_denied();
                    }

                    $customer_id = route(3);
                    
                    $statement = ARLedger::where('customer_id', $customer_id)
                        ->orderBy('posting_date', 'desc')
                        ->get();

                    // Use Contact model as customer master in this codebase.
                    $customer = Contact::find($customer_id);
                    if (!$customer) {
                        error_404();
                    }

                    $customer_name = $customer->account ?? $customer->company ?? ('Customer #' . $customer_id);

                    $ui->assign('_title', $_L['AR Statement'] . ' - ' . $customer_name);
                    $ui->assign('statement', $statement);
                    $ui->assign('customer', $customer);
                    
                    view('accounting/ar-customer-statement');
                    break;
            }
            break;
        
        /**
         * ============================================================
         * ACCOUNTS PAYABLE DASHBOARD
         * ============================================================
         */
        case 'ap-dashboard':
            $ap_service = new AccountsPayableService();
            
            if (!has_access($user->roleid, 'accounting_ap', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            
            switch ($sub_action) {
                case 'aging':
                    if (!has_access($user->roleid, 'accounting_ap', 'view')) {
                        access_denied();
                    }

                    // Get AP aging report
                    $aging = $ap_service->getAPAgingReport();
                    $summary = $ap_service->getAPSummary();
                    
                    $ui->assign('_title', $_L['Accounts Payable'] . ' - ' . $_L['Aging Report']);
                    $ui->assign('aging', $aging);
                    $ui->assign('summary', $summary);
                    
                    view('accounting/ap-aging-report');
                    break;
                
                case 'dashboard':
                default:
                    if (!has_access($user->roleid, 'accounting_ap', 'view')) {
                        access_denied();
                    }

                    // Get AP summary and key metrics
                    $summary = $ap_service->getAPSummary();
                    $aging = $ap_service->getAPAgingReport();
                    
                    $ui->assign('_title', $_L['Accounts Payable Dashboard']);
                    $ui->assign('summary', $summary);
                    $ui->assign('aging', $aging);
                    
                    view('accounting/ap-dashboard');
                    break;
                
                case 'vendor-statement':
                    if (!has_access($user->roleid, 'accounting_ap', 'view')) {
                        access_denied();
                    }

                    $vendor_id = route(3);
                    
                    $statement = APLedger::where('vendor_id', $vendor_id)
                        ->orderBy('posting_date', 'desc')
                        ->get();

                    // Use Contact model as vendor master in this codebase.
                    $vendor = Contact::find($vendor_id);
                    if (!$vendor) {
                        error_404();
                    }

                    $vendor_name = $vendor->account ?? $vendor->company ?? ('Vendor #' . $vendor_id);

                    $ui->assign('_title', $_L['AP Statement'] . ' - ' . $vendor_name);
                    $ui->assign('statement', $statement);
                    $ui->assign('vendor', $vendor);
                    
                    view('accounting/ap-vendor-statement');
                    break;
            }
            break;
        
        /**
         * ============================================================
         * BANK RECONCILIATION
         * ============================================================
         */
        case 'bank-reconciliation':
            $bank_service = new CashBankService();
            
            if (!has_access($user->roleid, 'accounting_bank_reconciliation', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            
            switch ($sub_action) {
                case 'new':
                    if (!has_access($user->roleid, 'accounting_bank_reconciliation', 'create')) {
                        access_denied();
                    }

                    // Get bank accounts
                    $bank_accounts = BankAccount::where('is_active', 1)
                        ->get();
                    
                    $ui->assign('_title', $_L['Bank Reconciliation'] . ' - ' . $_L['New']);
                    $ui->assign('bank_accounts', $bank_accounts);
                    
                    view('accounting/bank-reconciliation-create');
                    break;
                
                case 'list':
                default:
                    if (!has_access($user->roleid, 'accounting_bank_reconciliation', 'view')) {
                        access_denied();
                    }

                    // Get reconciliations
                    $reconciliations = BankReconciliation::with('bankAccount')
                        ->orderBy('statement_date', 'desc')
                        ->paginate(50);
                    
                    $ui->assign('_title', $_L['Bank Reconciliations']);
                    $ui->assign('reconciliations', $reconciliations);
                    
                    view('accounting/bank-reconciliation-list');
                    break;
                
                case 'detail':
                    if (!has_access($user->roleid, 'accounting_bank_reconciliation', 'view')) {
                        access_denied();
                    }

                    $recon_id = route(3);
                    
                    $recon = BankReconciliation::with('matches')
                        ->find($recon_id);
                    
                    $summary = $bank_service->getReconciliationSummary($recon_id);
                    
                    $ui->assign('_title', $_L['Bank Reconciliation'] . ' - ' . $recon->statement_date);
                    $ui->assign('reconciliation', $recon);
                    $ui->assign('summary', $summary);
                    
                    view('accounting/bank-reconciliation-detail');
                    break;
            }
            break;
        
        /**
         * ============================================================
         * FINANCIAL REPORTS
         * ============================================================
         */
        case 'financial-reports':
            $report_service = new FinancialReportService();
            
            if (!has_access($user->roleid, 'accounting_reports', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            $report_date = $_GET['as_of_date'] ?? date('Y-m-d');
            $report_from = $_GET['from_date'] ?? date('Y-m-01');
            $report_to = $_GET['to_date'] ?? date('Y-m-d');
            
            switch ($sub_action) {
                case 'trial-balance':
                    if (!has_access($user->roleid, 'accounting_reports', 'view')) {
                        access_denied();
                    }

                    $data = $report_service->getTrialBalance($report_date);
                    
                    $ui->assign('_title', $_L['Trial Balance']);
                    $ui->assign('data', $data);
                    $ui->assign('as_of_date', $report_date);
                    
                    view('accounting/report-trial-balance');
                    break;
                
                case 'profit-loss':
                    if (!has_access($user->roleid, 'accounting_reports', 'view')) {
                        access_denied();
                    }

                    $data = $report_service->getProfitLoss($report_from, $report_to);
                    
                    $ui->assign('_title', $_L['Profit & Loss']);
                    $ui->assign('data', $data);
                    $ui->assign('from_date', $report_from);
                    $ui->assign('to_date', $report_to);
                    
                    view('accounting/report-profit-loss');
                    break;
                
                case 'balance-sheet':
                    if (!has_access($user->roleid, 'accounting_reports', 'view')) {
                        access_denied();
                    }

                    $data = $report_service->getBalanceSheet($report_date);
                    
                    $ui->assign('_title', $_L['Balance Sheet']);
                    $ui->assign('data', $data);
                    $ui->assign('as_of_date', $report_date);
                    
                    view('accounting/report-balance-sheet');
                    break;
                
                case 'cash-flow':
                    if (!has_access($user->roleid, 'accounting_reports', 'view')) {
                        access_denied();
                    }

                    $data = $report_service->getCashFlowStatement($report_from, $report_to);
                    
                    $ui->assign('_title', $_L['Cash Flow Statement']);
                    $ui->assign('data', $data);
                    $ui->assign('from_date', $report_from);
                    $ui->assign('to_date', $report_to);
                    
                    view('accounting/report-cash-flow');
                    break;
                
                case 'list':
                default:
                    if (!has_access($user->roleid, 'accounting_reports', 'view')) {
                        access_denied();
                    }

                    $ui->assign('_title', $_L['Financial Reports']);
                    
                    view('accounting/reports-list');
                    break;
            }
            break;
        
        /**
         * ============================================================
         * TAX & VAT
         * ============================================================
         */
        case 'tax-vat':
            $tax_service = new TaxVATService();
            
            if (!has_access($user->roleid, 'accounting_tax_vat', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            $report_from = $_GET['from_date'] ?? date('Y-m-01');
            $report_to = $_GET['to_date'] ?? date('Y-m-d');
            
            switch ($sub_action) {
                case 'vat-report':
                    if (!has_access($user->roleid, 'accounting_tax_vat', 'view')) {
                        access_denied();
                    }

                    $vat_data = $tax_service->getVATReport($report_from, $report_to);

                    $summary = is_array($vat_data) ? ((object) ($vat_data['summary'] ?? [])) : (object) [];
                    $vat_details = is_array($vat_data) ? ($vat_data['details'] ?? $vat_data['vat_details'] ?? []) : [];
                    
                    $ui->assign('_title', $_L['VAT Report']);
                    $ui->assign('vat_data', $vat_data);
                    $ui->assign('summary', $summary);
                    $ui->assign('vat_details', $vat_details);
                    $ui->assign('from_date', $report_from);
                    $ui->assign('to_date', $report_to);
                    
                    view('accounting/tax-vat-report');
                    break;
                
                case 'zatca-status':
                    if (!has_access($user->roleid, 'accounting_tax_vat', 'view')) {
                        access_denied();
                    }

                    // Get ZATCA invoice status
                    $invoices = [];
                    $invoice_count = 0;
                    $latest_submission_date = '';
                    $show_sensitive = isset($_GET['show_sensitive']) && (string) $_GET['show_sensitive'] === '1';

                    $get_zatca_option = function ($key, $default = '') use ($config) {
                        if (isset($config[$key]) && trim((string) $config[$key]) !== '') {
                            return (string) $config[$key];
                        }

                        if (function_exists('get_option')) {
                            $v = get_option($key);
                            if ($v !== false && trim((string) $v) !== '') {
                                return (string) $v;
                            }
                        }

                        return $default;
                    };

                    try {
                        $has_zatca_status = false;
                        $has_created_at = false;
                        $has_zatca_uuid = false;
                        $has_zatca_last_response = false;

                        $database_row = \DB::selectOne('SELECT DATABASE() AS db_name');
                        $database_name = is_object($database_row) ? ($database_row->db_name ?? null) : null;

                        if (!empty($database_name)) {
                            $status_col = \DB::selectOne(
                                'SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                                [$database_name, 'sys_invoices', 'zatca_status']
                            );

                            $created_col = \DB::selectOne(
                                'SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                                [$database_name, 'sys_invoices', 'created_at']
                            );

                            $uuid_col = \DB::selectOne(
                                'SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                                [$database_name, 'sys_invoices', 'zatca_uuid']
                            );

                            $last_response_col = \DB::selectOne(
                                'SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                                [$database_name, 'sys_invoices', 'zatca_last_response']
                            );

                            $has_zatca_status = ((int) ($status_col->cnt ?? 0)) > 0;
                            $has_created_at = ((int) ($created_col->cnt ?? 0)) > 0;
                            $has_zatca_uuid = ((int) ($uuid_col->cnt ?? 0)) > 0;
                            $has_zatca_last_response = ((int) ($last_response_col->cnt ?? 0)) > 0;
                        }

                        $query = \DB::table('sys_invoices');

                        if ($has_zatca_status) {
                            $query->whereNotNull('zatca_status');
                        }

                        if ($has_created_at) {
                            $query->orderBy('created_at', 'desc');
                        } else {
                            $query->orderBy('id', 'desc');
                        }

                        $invoices = $query->paginate(50);

                        $total_invoice_records = (int) \DB::table('sys_invoices')->count();
                        $invoice_count = 0;

                        $submitted_query = \DB::table('sys_invoices');
                        $has_submitted_filter = false;

                        if ($has_zatca_status) {
                            $submitted_query->whereIn(\DB::raw('LOWER(zatca_status)'), ['submitted', 'reported', 'cleared', 'accepted', 'success']);
                            $has_submitted_filter = true;
                        }

                        if ($has_zatca_uuid) {
                            if ($has_submitted_filter) {
                                $submitted_query->orWhere(function ($q) {
                                    $q->whereNotNull('zatca_uuid')->where('zatca_uuid', '!=', '');
                                });
                            } else {
                                $submitted_query->whereNotNull('zatca_uuid')->where('zatca_uuid', '!=', '');
                                $has_submitted_filter = true;
                            }
                        }

                        if ($has_zatca_last_response) {
                            $response_matcher = function ($q) {
                                $q->where('zatca_last_response', 'like', '%"reportingStatus":"REPORTED"%')
                                    ->orWhere('zatca_last_response', 'like', '%"clearanceStatus":"CLEARED"%')
                                    ->orWhere('zatca_last_response', 'like', '%"dispositionMessage":"ISSUED"%')
                                    ->orWhere('zatca_last_response', 'like', '%"status":"submitted"%')
                                    ->orWhere('zatca_last_response', 'like', '%"success":true%');
                            };

                            if ($has_submitted_filter) {
                                $submitted_query->orWhere($response_matcher);
                            } else {
                                $submitted_query->where($response_matcher);
                                $has_submitted_filter = true;
                            }
                        }

                        if ($has_submitted_filter) {
                            $invoice_count = (int) $submitted_query->count();
                        }

                        if (is_object($invoices) && method_exists($invoices, 'firstItem')) {
                            $firstRow = null;
                            try {
                                $collection = method_exists($invoices, 'items') ? $invoices->items() : [];
                                if (is_array($collection) && !empty($collection)) {
                                    $firstRow = $collection[0];
                                }
                            } catch (\Throwable $e) {
                                $firstRow = null;
                            }

                            if (is_object($firstRow) && !empty($firstRow->created_at)) {
                                $latest_submission_date = (string) $firstRow->created_at;
                            }
                        }

                        if ($latest_submission_date === '' && $has_zatca_status) {
                            try {
                                $latest_row = \DB::table('sys_invoices')
                                    ->whereNotNull('zatca_status')
                                    ->orderBy('id', 'desc')
                                    ->first();
                                if (is_object($latest_row) && !empty($latest_row->created_at)) {
                                    $latest_submission_date = (string) $latest_row->created_at;
                                }
                            } catch (\Throwable $e) {
                                // Leave latest submission date empty.
                            }
                        }

                        if ($invoice_count <= 0) {
                            if (is_object($invoices) && method_exists($invoices, 'total')) {
                                $invoice_count = (int) $invoices->total();
                            } elseif ($invoices instanceof \Countable) {
                                $invoice_count = count($invoices);
                            } elseif (is_array($invoices)) {
                                $invoice_count = count($invoices);
                            }
                        }

                        if (!isset($total_invoice_records)) {
                            $total_invoice_records = $invoice_count;
                        }
                    } catch (\Throwable $e) {
                        // Keep the page functional even if invoice query fails on legacy DBs.
                        $invoices = [];
                        $invoice_count = 0;
                        $total_invoice_records = 0;
                    }

                    $zatca_enabled = $get_zatca_option('zatca_enabled', '0') === '1';
                    $phase2_enabled = $get_zatca_option('zatca_phase2_enabled', '0') === '1';
                    $api_base_url = $get_zatca_option('zatca_api_base_url', '');
                    $environment = $get_zatca_option('zatca_environment', 'sandbox');
                    $environment_normalized = strtolower(trim((string) $environment));
                    if (!in_array($environment_normalized, ['sandbox', 'simulation', 'production'], true)) {
                        $environment_normalized = 'sandbox';
                    }

                    $api_base_fallback = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal';
                    if ($environment_normalized === 'simulation') {
                        $api_base_fallback = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation';
                    } elseif ($environment_normalized === 'production') {
                        $api_base_fallback = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core';
                    }

                    $api_base_candidate = trim((string) $api_base_url);
                    if ($api_base_candidate === '') {
                        $api_base_candidate = trim((string) $get_zatca_option('zatca_api_base', ''));
                    }
                    if ($api_base_candidate === '') {
                        $api_base_candidate = $api_base_fallback;
                    }

                    $parsed_api_base = @parse_url($api_base_candidate);
                    $api_path = '';
                    if (is_array($parsed_api_base) && isset($parsed_api_base['path'])) {
                        $api_path = trim((string) $parsed_api_base['path']);
                    }

                    if ($api_path === '' || $api_path === '/') {
                        $api_base_candidate = $api_base_fallback;
                    }

                    $resolved_api_base_url = rtrim($api_base_candidate, '/') . '/';
                    $invoice_type = $get_zatca_option('zatca_invoice_type', 'simplified');
                    $seller_name = $get_zatca_option('zatca_seller_name', '');
                    $crn = $get_zatca_option('zatca_seller_crn', '');
                    $vat_number = $get_zatca_option('zatca_vat_number', '');
                    $building_no = $get_zatca_option('zatca_building_no', '');
                    $street_name = $get_zatca_option('zatca_street_name', '');
                    $district = $get_zatca_option('zatca_district', '');
                    $city = $get_zatca_option('zatca_city', '');
                    $postal_code = $get_zatca_option('zatca_postal_code', '');
                    $country_code = $get_zatca_option('zatca_country_code', 'SA');
                    $csr_value = $get_zatca_option('zatca_csr_content', '');
                    $otp_value = $get_zatca_option('zatca_otp', '');
                    $binary_security_token = $get_zatca_option('zatca_binary_security_token', '');
                    $secret_value = $get_zatca_option('zatca_secret', '');
                    $compliance_request_id = $get_zatca_option('zatca_compliance_request_id', '');
                    $certificate_value = $get_zatca_option('zatca_certificate', '');
                    $private_key_value = $get_zatca_option('zatca_private_key', '');
                    $private_key_passphrase = $get_zatca_option('zatca_private_key_passphrase', '');
                    if ($private_key_passphrase === '') {
                        $legacy_passphrase_keys = [
                            'zatca_private_key_password',
                            'zatca_passphrase',
                            'zatca_cert_password',
                            'zatca_sdk_cert_password',
                        ];

                        foreach ($legacy_passphrase_keys as $legacy_passphrase_key) {
                            $legacy_passphrase_value = $get_zatca_option($legacy_passphrase_key, '');
                            if ($legacy_passphrase_value !== '') {
                                $private_key_passphrase = $legacy_passphrase_value;
                                break;
                            }
                        }
                    }
                    $compliance_csid = $get_zatca_option('zatca_compliance_csid', '');
                    $compliance_secret = $get_zatca_option('zatca_compliance_secret', '');
                    if ($compliance_secret === '') {
                        $compliance_secret = $secret_value;
                    }
                    $production_csid = $get_zatca_option('zatca_production_csid', '');
                    $production_binary_security_token = $get_zatca_option('zatca_production_binary_security_token', '');
                    $production_secret = $get_zatca_option('zatca_production_secret', '');

                    $summarize_live_response = function ($response_body, $status_code) {
                        $status_code = (int) $status_code;
                        $response_body = trim((string) $response_body);
                        if ($response_body === '') {
                            return 'HTTP ' . $status_code;
                        }

                        $decoded = json_decode($response_body, true);
                        if (is_array($decoded)) {
                            $message_fields = [
                                'message',
                                'error',
                                'dispositionMessage',
                                'reportingStatus',
                                'clearanceStatus',
                            ];

                            foreach ($message_fields as $field) {
                                if (!empty($decoded[$field]) && is_scalar($decoded[$field])) {
                                    return 'HTTP ' . $status_code . ' - ' . (string) $decoded[$field];
                                }
                            }
                        }

                        $plain = preg_replace('/\s+/', ' ', strip_tags($response_body));
                        $plain = trim((string) $plain);
                        if ($plain === '') {
                            return 'HTTP ' . $status_code;
                        }

                        if (strlen($plain) > 160) {
                            $plain = substr($plain, 0, 160) . '...';
                        }

                        return 'HTTP ' . $status_code . ' - ' . $plain;
                    };

                    $live_status_checked_at = date('Y-m-d H:i:s');
                    $live_status_code = 0;
                    $live_status_message = 'Not checked';
                    $live_status_checked = false;
                    $live_status_reachable = false;
                    $live_status_auth_ok = false;

                    if ($resolved_api_base_url !== '') {
                        $active_binary_token = $environment_normalized === 'production'
                            ? ($production_binary_security_token !== '' ? $production_binary_security_token : $binary_security_token)
                            : ($binary_security_token !== '' ? $binary_security_token : $production_binary_security_token);

                        $active_secret = $environment_normalized === 'production'
                            ? ($production_secret !== '' ? $production_secret : $compliance_secret)
                            : ($compliance_secret !== '' ? $compliance_secret : $production_secret);

                        if ($active_binary_token !== '' && $active_secret !== '') {
                            try {
                                $live_status_checked = true;

                                $probe_uuid = function_exists('random_bytes')
                                    ? bin2hex(random_bytes(16))
                                    : md5(uniqid('zatca', true));
                                $probe_hash = base64_encode(hash('sha256', 'zatca-live-probe-' . $probe_uuid, true));
                                $probe_invoice = base64_encode('ZATCA_LIVE_PROBE_' . $probe_uuid);
                                $probe_endpoint = strtolower(trim((string) $invoice_type)) === 'standard'
                                    ? 'invoices/clearance/single'
                                    : 'invoices/reporting/single';

                                $client = new \GuzzleHttp\Client([
                                    'base_uri' => $resolved_api_base_url,
                                    'timeout' => 20,
                                    'http_errors' => false,
                                ]);

                                $live_response = $client->post($probe_endpoint, [
                                    'headers' => [
                                        'Accept' => 'application/json',
                                        'Accept-Language' => 'en',
                                        'Accept-Version' => 'V2',
                                        'Authorization' => 'Basic ' . base64_encode($active_binary_token . ':' . $active_secret),
                                        'Content-Type' => 'application/json',
                                    ],
                                    'json' => [
                                        'invoiceHash' => $probe_hash,
                                        'uuid' => $probe_uuid,
                                        'invoice' => $probe_invoice,
                                    ],
                                ]);

                                $live_status_code = (int) $live_response->getStatusCode();
                                $live_status_reachable = $live_status_code > 0;
                                $live_status_auth_ok = !in_array($live_status_code, [401, 403], true);
                                $live_status_message = $summarize_live_response((string) $live_response->getBody(), $live_status_code);
                            } catch (\Throwable $e) {
                                $live_status_checked = true;
                                $live_status_reachable = false;
                                $live_status_auth_ok = false;
                                $live_status_code = 0;
                                $live_status_message = 'Live connection failed: ' . $e->getMessage();
                            }
                        } else {
                            $live_status_message = 'Live check skipped: missing active ZATCA token/secret.';
                        }
                    } else {
                        $live_status_message = 'Live check skipped: missing ZATCA API Base URL.';
                    }

                    // Registration and certificate status reflect local config only.
                    // A failed/unreachable live probe must NOT override these — it only
                    // means the local server could not reach the ZATCA API at this moment.
                    $live_registered = $zatca_enabled;
                    $live_certificates_valid = ($certificate_value !== '');

                    // Portal connection is the only field driven by the live probe result.
                    $portal_connected = $live_status_checked ? $live_status_reachable : ($resolved_api_base_url !== '');

                    $mask_secret = function ($value) {
                        $value = trim((string) $value);
                        if ($value === '') {
                            return '';
                        }

                        $len = strlen($value);
                        if ($len <= 8) {
                            return str_repeat('*', $len);
                        }

                        return substr($value, 0, 4) . str_repeat('*', max(4, $len - 8)) . substr($value, -4);
                    };

                    $binary_security_token_masked = $mask_secret($binary_security_token);
                    $secret_value_masked = $mask_secret($secret_value);
                    $compliance_secret_masked = $mask_secret($compliance_secret);
                    $production_binary_security_token_masked = $mask_secret($production_binary_security_token);
                    $production_secret_masked = $mask_secret($production_secret);

                    $certificate_display = '';
                    if ($certificate_value !== '') {
                        if (stripos($certificate_value, '-----BEGIN') !== false) {
                            $certificate_display = 'PEM Content Saved';
                        } else {
                            $certificate_display = $certificate_value;
                        }
                    }

                    $certificate_expiry = '';
                    if ($certificate_value !== '') {
                        try {
                            $cert_raw = '';
                            if (stripos($certificate_value, '-----BEGIN') !== false) {
                                $cert_raw = $certificate_value;
                            } elseif (is_file($certificate_value)) {
                                $cert_raw = (string) @file_get_contents($certificate_value);
                            }

                            if ($cert_raw !== '' && function_exists('openssl_x509_parse')) {
                                $cert_info = @openssl_x509_parse($cert_raw);
                                if (is_array($cert_info) && !empty($cert_info['validTo_time_t'])) {
                                    $certificate_expiry = date('Y-m-d H:i:s', (int) $cert_info['validTo_time_t']);
                                }
                            }
                        } catch (\Throwable $e) {
                            $certificate_expiry = '';
                        }
                    }

                    $criteria_total = 5;
                    $criteria_ok = 0;
                    $criteria_ok += $zatca_enabled ? 1 : 0;
                    $criteria_ok += ($crn !== '' && $vat_number !== '') ? 1 : 0;
                    $criteria_ok += ($certificate_value !== '') ? 1 : 0;
                    $criteria_ok += ($phase2_enabled && $private_key_value !== '') ? 1 : 0;
                    $criteria_ok += ($resolved_api_base_url !== '') ? 1 : 0;
                    $compliance_score = (int) round(($criteria_ok / $criteria_total) * 100);

                    $status = (object) [
                        'zatca_registered' => $live_registered,
                        'certificates_valid' => $live_certificates_valid,
                        'invoices_submitted' => $invoice_count,
                        'total_invoices' => isset($total_invoice_records) ? (int) $total_invoice_records : $invoice_count,
                        'compliance_score' => $compliance_score,
                        'signing_enabled' => ($phase2_enabled && $private_key_value !== ''),
                        'qr_generation' => $zatca_enabled,
                        'portal_connected' => $portal_connected,
                        'last_submission_date' => ($latest_submission_date !== '' ? $latest_submission_date : date('Y-m-d H:i:s')),
                        'live_status_code' => $live_status_code,
                        'live_status_message' => $live_status_message,
                        'live_status_checked_at' => $live_status_checked_at,
                        'invoice_count_source' => 'ZATCA-linked invoices',
                        'crn' => $crn,
                        'vat_reg_number' => $vat_number,
                        'certificate_file' => $certificate_display,
                        'certificate_expiry' => $certificate_expiry,
                        'environment' => $environment,
                        'api_base_url' => $resolved_api_base_url,
                        'invoice_type' => $invoice_type,
                        'seller_name' => $seller_name,
                        'building_no' => $building_no,
                        'street_name' => $street_name,
                        'district' => $district,
                        'city' => $city,
                        'postal_code' => $postal_code,
                        'country_code' => $country_code,
                        'csr_saved' => ($csr_value !== ''),
                        'otp_saved' => ($otp_value !== ''),
                        'binary_security_token' => $binary_security_token_masked,
                        'secret' => $secret_value_masked,
                        'compliance_request_id' => $compliance_request_id,
                        'private_key_saved' => ($private_key_value !== ''),
                        'private_key_passphrase_saved' => ($private_key_passphrase !== ''),
                        'compliance_csid' => $compliance_csid,
                        'compliance_secret' => $compliance_secret_masked,
                        'production_csid' => $production_csid,
                        'production_binary_security_token' => $production_binary_security_token_masked,
                        'production_secret' => $production_secret_masked,
                    ];

                    $issues = [];
                    if (!$zatca_enabled) {
                        $issues[] = 'ZATCA integration is disabled in settings.';
                    }
                    if ($vat_number === '' || $crn === '') {
                        $issues[] = 'VAT Number and/or CRN is missing.';
                    }
                    if ($certificate_value === '') {
                        $issues[] = 'Certificate is not configured.';
                    }
                    if ($private_key_value === '') {
                        $issues[] = 'Private key is not configured.';
                    }
                    if ($resolved_api_base_url === '') {
                        $issues[] = 'ZATCA API Base URL is not configured.';
                    }
                    if ($live_status_checked && !$live_status_reachable) {
                        $issues[] = 'Live ZATCA response check failed: ' . $live_status_message;
                    }
                    
                    $ui->assign('_title', $_L['ZATCA Compliance']);
                    $ui->assign('invoices', $invoices);
                    $ui->assign('status', $status);
                    $ui->assign('issues', $issues);
                    $ui->assign('show_sensitive', $show_sensitive);
                    
                    view('accounting/tax-zatca-status');
                    break;
                
                case 'list':
                default:
                    if (!has_access($user->roleid, 'accounting_tax_vat', 'view')) {
                        access_denied();
                    }

                    $ui->assign('_title', $_L['Tax & VAT']);
                    
                    view('accounting/tax-list');
                    break;
            }
            break;
        
        /**
         * ============================================================
         * ACCOUNTING SETTINGS
         * ============================================================
         */
        case 'accounting-settings':
            $controls_service = new AccountingControlsService();
            
            if (!has_access($user->roleid, 'accounting_settings', 'view')) {
                access_denied();
            }
            
            $sub_action = route(2);
            
            switch ($sub_action) {
                case 'periods':
                    if (!has_access($user->roleid, 'accounting_settings', 'view')) {
                        access_denied();
                    }

                    // Get accounting periods
                    $periods = AccountingPeriod::orderBy('start_date', 'desc')
                        ->get();
                    
                    $ui->assign('_title', $_L['Accounting Periods']);
                    $ui->assign('periods', $periods);
                    
                    view('accounting/settings-periods');
                    break;
                
                case 'tax-codes':
                    if (!has_access($user->roleid, 'accounting_settings', 'view')) {
                        access_denied();
                    }

                    // Get tax codes
                    $tax_codes = TaxCode::all();
                    
                    $ui->assign('_title', $_L['Tax Codes']);
                    $ui->assign('tax_codes', $tax_codes);
                    
                    view('accounting/settings-tax-codes');
                    break;
                
                case 'audit-log':
                    if (!has_access($user->roleid, 'accounting_settings', 'view')) {
                        access_denied();
                    }

                    // Get audit log
                    $audit_logs = AuditLog::orderBy('created_at', 'desc')
                        ->paginate(50);
                    
                    $ui->assign('_title', $_L['Audit Log']);
                    $ui->assign('audit_logs', $audit_logs);
                    $ui->assign('logs', $audit_logs);
                    
                    view('accounting/settings-audit-log');
                    break;
                
                case 'compliance':
                    if (!has_access($user->roleid, 'accounting_settings', 'view')) {
                        access_denied();
                    }

                    // Get compliance report
                    $compliance = $controls_service->getComplianceReport(date('Y-m-d'));
                    
                    $ui->assign('_title', $_L['Compliance Report']);
                    $ui->assign('compliance', $compliance);
                    
                    view('accounting/settings-compliance');
                    break;
            }
            break;
    }
}

