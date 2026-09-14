<?php

/**
 * ============================================================
 * ACCOUNTING API CONTROLLER
 * ============================================================
 * REST API endpoints for complete accounting system
 * 
 * Base URL: /api/accounting/
 * 
 * Endpoints:
 * - Journal Entry management
 * - AR/AP operations
 * - Bank reconciliation
 * - Tax/VAT reporting
 * - Financial reports
 * ============================================================
 */

class AccountingAPIController extends Controller
{
    protected $je_service;
    protected $ar_service;
    protected $ap_service;
    protected $cash_service;
    protected $tax_service;
    protected $report_service;

    public function __construct()
    {
        // Initialize services
        $this->je_service = new JournalEntryService();
        $this->ar_service = new AccountsReceivableService(null, $this->je_service);
        $this->ap_service = new AccountsPayableService(null, $this->je_service);
        $this->cash_service = new CashBankService(null, $this->je_service);
        $this->tax_service = new TaxVATService(null, $this->je_service);
        $this->report_service = new FinancialReportService(null, $this->je_service);
    }

    protected function denyResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Permission Denied'
        ], 403);
    }

    protected function hasAccountingAccess($permissionShortname, $action = 'view')
    {
        if (!function_exists('authenticate_admin') || !function_exists('has_access')) {
            return false;
        }

        $user = authenticate_admin();

        if (!$user || !isset($user->roleid)) {
            return false;
        }

        return has_access($user->roleid, $permissionShortname, $action);
    }

    /**
     * ============================================================
     * JOURNAL ENTRY ENDPOINTS
     * ============================================================
     */

    /**
     * POST /api/accounting/journal-entries
     * Create and post a journal entry
     */
    public function createJournalEntry()
    {
        if (!$this->hasAccountingAccess('accounting_journal_entries', 'create')) {
            return $this->denyResponse();
        }

        $data = request()->all();

        try {
            $result = $this->je_service->createAndPostJournalEntry($data);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/journal-entries
     * List journal entries with filters
     */
    public function listJournalEntries()
    {
        if (!$this->hasAccountingAccess('accounting_journal_entries', 'view')) {
            return $this->denyResponse();
        }

        $filters = request()->all();

        try {
            $results = $this->je_service->listJournalEntries($filters);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/journal-entries/{id}
     * Get journal entry details
     */
    public function getJournalEntry($id)
    {
        if (!$this->hasAccountingAccess('accounting_journal_entries', 'view')) {
            return $this->denyResponse();
        }

        try {
            $je = $this->je_service->getJournalEntry($id);
            $items = $this->je_service->getJournalItems($id);

            if (!$je) {
                return response()->json([
                    'success' => false,
                    'message' => 'Journal entry not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'entry' => $je,
                    'items' => $items
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounting/journal-entries/{id}/reverse
     * Reverse a journal entry
     */
    public function reverseJournalEntry($id)
    {
        if (!$this->hasAccountingAccess('accounting_journal_entries', 'edit')) {
            return $this->denyResponse();
        }

        try {
            $result = $this->je_service->reverseJournalEntry($id);

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * ACCOUNTS RECEIVABLE (AR) ENDPOINTS
     * ============================================================
     */

    /**
     * POST /api/accounting/ar/post-invoice
     * Post invoice to AR and GL
     */
    public function postInvoiceToAR()
    {
        if (!$this->hasAccountingAccess('accounting_ar', 'create')) {
            return $this->denyResponse();
        }

        $invoice_id = request()->input('invoice_id');

        try {
            $result = $this->ar_service->postInvoiceToAR($invoice_id);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounting/ar/record-payment
     * Record customer payment
     */
    public function recordCustomerPayment()
    {
        if (!$this->hasAccountingAccess('accounting_ar', 'create')) {
            return $this->denyResponse();
        }

        $data = request()->all();

        try {
            $result = $this->ar_service->recordCustomerPayment($data);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/ar/aging-report
     * Get AR aging report
     */
    public function getARAgingReport()
    {
        if (!$this->hasAccountingAccess('accounting_ar', 'view')) {
            return $this->denyResponse();
        }

        try {
            $aging = $this->ar_service->getARAgingReport();

            return response()->json([
                'success' => true,
                'data' => $aging
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/ar/summary
     * Get AR summary
     */
    public function getARSummary()
    {
        if (!$this->hasAccountingAccess('accounting_ar', 'view')) {
            return $this->denyResponse();
        }

        try {
            $summary = $this->ar_service->getARSummary();

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/ar/customer/{id}/statement
     * Get customer statement
     */
    public function getCustomerStatement($customer_id)
    {
        if (!$this->hasAccountingAccess('accounting_ar', 'view')) {
            return $this->denyResponse();
        }

        $from = request()->input('from_date');
        $to = request()->input('to_date');

        try {
            $statement = $this->ar_service->getCustomerStatement($customer_id, $from, $to);

            return response()->json([
                'success' => true,
                'data' => $statement
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * ACCOUNTS PAYABLE (AP) ENDPOINTS
     * ============================================================
     */

    /**
     * POST /api/accounting/ap/post-bill
     * Post bill to AP and GL
     */
    public function postBillToAP()
    {
        if (!$this->hasAccountingAccess('accounting_ap', 'create')) {
            return $this->denyResponse();
        }

        $bill_id = request()->input('bill_id');

        try {
            $result = $this->ap_service->postBillToAP($bill_id);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounting/ap/record-payment
     * Record vendor payment
     */
    public function recordVendorPayment()
    {
        if (!$this->hasAccountingAccess('accounting_ap', 'create')) {
            return $this->denyResponse();
        }

        $data = request()->all();

        try {
            $result = $this->ap_service->recordVendorPayment($data);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/ap/aging-report
     * Get AP aging report
     */
    public function getAPAgingReport()
    {
        if (!$this->hasAccountingAccess('accounting_ap', 'view')) {
            return $this->denyResponse();
        }

        try {
            $aging = $this->ap_service->getAPAgingReport();

            return response()->json([
                'success' => true,
                'data' => $aging
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/ap/summary
     * Get AP summary
     */
    public function getAPSummary()
    {
        if (!$this->hasAccountingAccess('accounting_ap', 'view')) {
            return $this->denyResponse();
        }

        try {
            $summary = $this->ap_service->getAPSummary();

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * CASH & BANK ENDPOINTS
     * ============================================================
     */

    /**
     * POST /api/accounting/bank/transfer
     * Record bank transfer
     */
    public function recordBankTransfer()
    {
        if (!$this->hasAccountingAccess('accounting_bank_reconciliation', 'create')) {
            return $this->denyResponse();
        }

        $data = request()->all();

        try {
            $result = $this->cash_service->recordBankTransfer($data);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounting/bank/reconciliation
     * Create bank reconciliation
     */
    public function createReconciliation()
    {
        if (!$this->hasAccountingAccess('accounting_bank_reconciliation', 'create')) {
            return $this->denyResponse();
        }

        $data = request()->all();

        try {
            $result = $this->cash_service->createReconciliation($data);

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounting/bank/reconciliation/{id}/match
     * Match transaction to reconciliation
     */
    public function matchTransaction($reconciliation_id)
    {
        if (!$this->hasAccountingAccess('accounting_bank_reconciliation', 'edit')) {
            return $this->denyResponse();
        }

        $data = request()->all();

        try {
            $result = $this->cash_service->matchTransaction(
                $reconciliation_id,
                $data['journal_entry_id'],
                $data['statement_line'] ?? null,
                $data['match_type'] ?? 'cleared'
            );

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounting/bank/reconciliation/{id}/finalize
     * Finalize reconciliation
     */
    public function finalizeReconciliation($reconciliation_id)
    {
        if (!$this->hasAccountingAccess('accounting_bank_reconciliation', 'edit')) {
            return $this->denyResponse();
        }

        try {
            $result = $this->cash_service->finalizeReconciliation($reconciliation_id);

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/bank/reconciliation/{id}
     * Get reconciliation summary
     */
    public function getReconciliationSummary($reconciliation_id)
    {
        if (!$this->hasAccountingAccess('accounting_bank_reconciliation', 'view')) {
            return $this->denyResponse();
        }

        try {
            $summary = $this->cash_service->getReconciliationSummary($reconciliation_id);

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * TAX & VAT ENDPOINTS
     * ============================================================
     */

    /**
     * POST /api/accounting/tax/calculate
     * Calculate VAT on transaction
     */
    public function calculateVAT()
    {
        if (!$this->hasAccountingAccess('accounting_tax_vat', 'view')) {
            return $this->denyResponse();
        }

        $data = request()->all();

        try {
            $result = $this->tax_service->calculateInvoiceVAT(
                $data['invoice_data'],
                $data['tax_code'] ?? 'VAT-15'
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/tax/vat-report
     * Generate VAT report
     */
    public function getVATReport()
    {
        if (!$this->hasAccountingAccess('accounting_tax_vat', 'view')) {
            return $this->denyResponse();
        }

        $from = request()->input('from_date');
        $to = request()->input('to_date');

        try {
            $report = $this->tax_service->getVATReport($from, $to);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/tax/zatca-status
     * Get ZATCA submission status
     */
    public function getZATCAStatus()
    {
        if (!$this->hasAccountingAccess('accounting_tax_vat', 'view')) {
            return $this->denyResponse();
        }

        $status = request()->input('status');

        try {
            $results = $this->tax_service->getZATCAStatus($status);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * FINANCIAL REPORTS ENDPOINTS
     * ============================================================
     */

    /**
     * GET /api/accounting/reports/trial-balance
     * Get trial balance
     */
    public function getTrialBalance()
    {
        if (!$this->hasAccountingAccess('accounting_reports', 'view')) {
            return $this->denyResponse();
        }

        $date = request()->input('as_of_date');

        try {
            $report = $this->report_service->getTrialBalance($date);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/reports/profit-loss
     * Get P&L statement
     */
    public function getProfitLoss()
    {
        if (!$this->hasAccountingAccess('accounting_reports', 'view')) {
            return $this->denyResponse();
        }

        $from = request()->input('from_date');
        $to = request()->input('to_date');

        try {
            $report = $this->report_service->getProfitLoss($from, $to);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/reports/balance-sheet
     * Get balance sheet
     */
    public function getBalanceSheet()
    {
        if (!$this->hasAccountingAccess('accounting_reports', 'view')) {
            return $this->denyResponse();
        }

        $date = request()->input('as_of_date');

        try {
            $report = $this->report_service->getBalanceSheet($date);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/reports/cash-flow
     * Get cash flow statement
     */
    public function getCashFlow()
    {
        $from = request()->input('from_date');
        $to = request()->input('to_date');

        try {
            $report = $this->report_service->getCashFlowStatement($from, $to);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/reports/account-ledger/{account_id}
     * Get account ledger
     */
    public function getAccountLedger($account_id)
    {
        $from = request()->input('from_date');
        $to = request()->input('to_date');

        try {
            $report = $this->report_service->getAccountLedger($account_id, $from, $to);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/reports/general-ledger
     * Get general ledger
     */
    public function getGeneralLedger()
    {
        $from = request()->input('from_date');
        $to = request()->input('to_date');

        try {
            $report = $this->report_service->getGeneralLedger($from, $to);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounting/reports/gl-integrity
     * Validate GL integrity
     */
    public function validateGLIntegrity()
    {
        try {
            $result = $this->je_service->validateGLIntegrity();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
