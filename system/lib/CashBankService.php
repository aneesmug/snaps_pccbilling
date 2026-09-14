<?php

/**
 * ============================================================
 * CASH & BANK MANAGEMENT SERVICE
 * ============================================================
 * Manages bank accounts, transfers, and reconciliation
 * - Record bank transfers between accounts
 * - Bank reconciliation process
 * - Match cleared vs pending transactions
 * - Cash flow analysis
 * 
 * GL Accounts Used:
 * - Bank/Cash (multiple accounts per entity)
 * ============================================================
 */

class CashBankService
{
    protected $db;
    protected $je_service;
    protected $user_id;
    protected $company_id;

    public function __construct($db = null, $je_service = null, $user_id = null, $company_id = null)
    {
        $this->db = $db ?: \Illuminate\Database\Capsule\Manager::connection();
        $this->je_service = $je_service ?: new JournalEntryService($db, $user_id, $company_id);
        $this->user_id = $user_id ?: (isset($GLOBALS['user']) && $GLOBALS['user'] ? $GLOBALS['user']->id : null);
        $this->company_id = $company_id;
    }

    /**
     * ============================================================
     * TRANSFER BETWEEN BANK ACCOUNTS
     * ============================================================
     * Record transfer from one bank account to another
     * 
     *   Dr Bank Account B
     *   Cr Bank Account A
     * 
     * @param array $data {
     *     'from_bank_account_id' => 1,
     *     'to_bank_account_id' => 2,
     *     'amount' => 5000.00,
     *     'transfer_date' => '2026-04-27',
     *     'reference' => 'TRANSFER-001'
     * }
     * @return array
     */
    public function recordBankTransfer($data)
    {
        try {
            $this->db->beginTransaction();

            // Validate
            if (empty($data['from_bank_account_id']) || empty($data['to_bank_account_id']) || empty($data['amount'])) {
                return ['success' => false, 'message' => 'Missing required fields'];
            }

            if ($data['from_bank_account_id'] === $data['to_bank_account_id']) {
                return ['success' => false, 'message' => 'Cannot transfer to same account'];
            }

            // Get bank accounts
            $from_bank = $this->db->table('sys_bank_accounts')
                ->where('id', $data['from_bank_account_id'])
                ->first();

            $to_bank = $this->db->table('sys_bank_accounts')
                ->where('id', $data['to_bank_account_id'])
                ->first();

            if (!$from_bank || !$to_bank) {
                return ['success' => false, 'message' => 'Bank account(s) not found'];
            }

            // Check sufficient balance
            if ($from_bank->balance < $data['amount']) {
                return ['success' => false, 'message' => 'Insufficient funds'];
            }

            // Create JE: Dr To_Bank Cr From_Bank
            $je_result = $this->je_service->createAndPostJournalEntry([
                'entry_date' => $data['transfer_date'] ?? date('Y-m-d'),
                'reference' => $data['reference'] ?? 'TRANSFER',
                'description' => 'Transfer from ' . $from_bank->name . ' to ' . $to_bank->name,
                'source_module' => 'bank_transfer',
                'source_id' => null,
                'lines' => [
                    [
                        'account_id' => $to_bank->gl_cash_account_id,
                        'debit' => $data['amount'],
                        'credit' => 0,
                        'description' => 'Transfer to ' . $to_bank->name
                    ],
                    [
                        'account_id' => $from_bank->gl_cash_account_id,
                        'debit' => 0,
                        'credit' => $data['amount'],
                        'description' => 'Transfer from ' . $from_bank->name
                    ]
                ]
            ]);

            if (!$je_result['success']) {
                $this->db->rollBack();
                return $je_result;
            }

            // Update bank balances
            $this->db->table('sys_bank_accounts')
                ->where('id', $data['from_bank_account_id'])
                ->decrement('balance', $data['amount']);

            $this->db->table('sys_bank_accounts')
                ->where('id', $data['to_bank_account_id'])
                ->increment('balance', $data['amount']);

            $this->db->commit();

            return [
                'success' => true,
                'journal_entry_id' => $je_result['journal_entry_id'],
                'message' => 'Transfer recorded successfully'
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error recording transfer: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * CREATE BANK RECONCILIATION
     * ============================================================
     * Start reconciliation process for a bank account
     * 
     * @param array $data {
     *     'bank_account_id' => 1,
     *     'statement_date' => '2026-04-30',
     *     'opening_balance' => 10000.00,
     *     'closing_balance' => 12000.00
     * }
     * @return array
     */
    public function createReconciliation($data)
    {
        try {
            $bank_account = $this->db->table('sys_bank_accounts')
                ->where('id', $data['bank_account_id'])
                ->first();

            if (!$bank_account) {
                return ['success' => false, 'message' => 'Bank account not found'];
            }

            // Create reconciliation record
            $recon_id = $this->db->table('sys_bank_reconciliations')->insertGetId([
                'bank_account_id' => $data['bank_account_id'],
                'statement_date' => $data['statement_date'],
                'opening_balance' => $data['opening_balance'],
                'closing_balance' => $data['closing_balance'],
                'book_balance' => $bank_account->balance,
                'status' => 'draft',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'reconciliation_id' => $recon_id,
                'message' => 'Reconciliation created'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating reconciliation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * MATCH TRANSACTION TO STATEMENT LINE
     * ============================================================
     * Mark a GL transaction as matched/cleared
     * 
     * @param int $reconciliation_id
     * @param int $journal_entry_id
     * @param string $statement_line (optional description from statement)
     * @param string $match_type 'cleared' or 'pending'
     * @return array
     */
    public function matchTransaction($reconciliation_id, $journal_entry_id, $statement_line = null, $match_type = 'cleared')
    {
        try {
            $recon = $this->db->table('sys_bank_reconciliations')
                ->where('id', $reconciliation_id)
                ->first();

            if (!$recon || $recon->status !== 'draft') {
                return ['success' => false, 'message' => 'Reconciliation not in draft status'];
            }

            // Get JE for amount
            $je = $this->je_service->getJournalEntry($journal_entry_id);
            if (!$je) {
                return ['success' => false, 'message' => 'Journal entry not found'];
            }

            // Get JE items to find cash account line
            $je_items = $this->je_service->getJournalItems($journal_entry_id);
            $matched_amount = 0;

            foreach ($je_items as $item) {
                if ($item->debit > 0) $matched_amount = $item->debit;
                else if ($item->credit > 0) $matched_amount = $item->credit;
            }

            // Create match record
            $this->db->table('sys_bank_reconciliation_matches')->insert([
                'reconciliation_id' => $reconciliation_id,
                'journal_entry_id' => $journal_entry_id,
                'statement_line_text' => $statement_line,
                'matched_amount' => $matched_amount,
                'match_type' => $match_type,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Transaction matched'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error matching transaction: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * GET RECONCILIATION SUMMARY
     * ============================================================
     * Calculate reconciliation differences
     */
    public function getReconciliationSummary($reconciliation_id)
    {
        $recon = $this->db->table('sys_bank_reconciliations')
            ->where('id', $reconciliation_id)
            ->first();

        if (!$recon) {
            return ['success' => false, 'message' => 'Reconciliation not found'];
        }

        $matches = $this->db->table('sys_bank_reconciliation_matches')
            ->where('reconciliation_id', $reconciliation_id)
            ->get();

        $cleared_amount = 0;
        $pending_amount = 0;

        foreach ($matches as $match) {
            if ($match->match_type === 'cleared') {
                $cleared_amount += $match->matched_amount;
            } else {
                $pending_amount += $match->matched_amount;
            }
        }

        $reconciled_balance = $recon->opening_balance + $cleared_amount;
        $difference = abs($reconciled_balance - $recon->book_balance);

        return [
            'success' => true,
            'statement_date' => $recon->statement_date,
            'opening_balance' => (float)$recon->opening_balance,
            'statement_closing' => (float)$recon->closing_balance,
            'book_balance' => (float)$recon->book_balance,
            'cleared_amount' => (float)$cleared_amount,
            'pending_amount' => (float)$pending_amount,
            'reconciled_balance' => (float)$reconciled_balance,
            'difference' => (float)$difference,
            'is_balanced' => $difference < 0.01,
            'match_count' => count($matches)
        ];
    }

    /**
     * ============================================================
     * FINALIZE RECONCILIATION
     * ============================================================
     * Mark reconciliation as complete
     */
    public function finalizeReconciliation($reconciliation_id)
    {
        try {
            $summary = $this->getReconciliationSummary($reconciliation_id);

            if (!$summary['success']) {
                return $summary;
            }

            if (!$summary['is_balanced']) {
                return [
                    'success' => false,
                    'message' => 'Reconciliation not balanced. Difference: ' . $summary['difference']
                ];
            }

            // Update reconciliation status
            $this->db->table('sys_bank_reconciliations')
                ->where('id', $reconciliation_id)
                ->update([
                    'status' => 'reconciled',
                    'reconciled_by_user_id' => $this->user_id,
                    'reconciled_at' => date('Y-m-d H:i:s')
                ]);

            // Update bank account reconciled balance
            $recon = $this->db->table('sys_bank_reconciliations')
                ->where('id', $reconciliation_id)
                ->first();

            $this->db->table('sys_bank_accounts')
                ->where('id', $recon->bank_account_id)
                ->update([
                    'reconciled_balance' => $summary['reconciled_balance']
                ]);

            return [
                'success' => true,
                'message' => 'Reconciliation finalized'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error finalizing reconciliation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * GET UNMATCHED TRANSACTIONS
     * ============================================================
     * List GL transactions not yet reconciled
     */
    public function getUnmatchedTransactions($bank_account_id, $statement_date)
    {
        $bank_account = $this->db->table('sys_bank_accounts')
            ->where('id', $bank_account_id)
            ->first();

        if (!$bank_account) {
            return [];
        }

        // Get all JEs for this bank account that haven't been matched
        return $this->db->table('sys_journal_items')
            ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
            ->where('sys_journal_items.account_id', $bank_account->gl_cash_account_id)
            ->where('sys_journal_entries.entry_date', '<=', $statement_date)
            ->whereNotIn(
                'sys_journal_entries.id',
                $this->db->table('sys_bank_reconciliation_matches')->select('journal_entry_id')
            )
            ->select(
                'sys_journal_entries.id',
                'sys_journal_entries.entry_date',
                'sys_journal_entries.reference',
                'sys_journal_entries.description',
                'sys_journal_items.debit',
                'sys_journal_items.credit'
            )
            ->orderBy('sys_journal_entries.entry_date')
            ->get();
    }

    /**
     * ============================================================
     * GET BANK ACCOUNT STATEMENT
     * ============================================================
     */
    public function getBankAccountStatement($bank_account_id, $from_date = null, $to_date = null)
    {
        $query = $this->db->table('sys_journal_items')
            ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
            ->join('sys_bank_accounts', 'sys_journal_items.account_id', '=', 'sys_bank_accounts.gl_cash_account_id');

        $query->where('sys_bank_accounts.id', $bank_account_id);

        if ($from_date && $to_date) {
            $query->whereBetween('sys_journal_entries.entry_date', [$from_date, $to_date]);
        }

        return $query->select(
            'sys_journal_entries.id',
            'sys_journal_entries.entry_date',
            'sys_journal_entries.reference',
            'sys_journal_entries.description',
            'sys_journal_items.debit',
            'sys_journal_items.credit'
        )
        ->orderBy('sys_journal_entries.entry_date')
        ->get();
    }

    /**
     * ============================================================
     * GET CASH FLOW BY ACCOUNT
     * ============================================================
     * Summarize cash in/out by account
     */
    public function getCashFlowSummary($from_date, $to_date)
    {
        $results = $this->db->table('sys_journal_items')
            ->join('sys_journal_entries', 'sys_journal_items.journal_entry_id', '=', 'sys_journal_entries.id')
            ->join('sys_bank_accounts', 'sys_journal_items.account_id', '=', 'sys_bank_accounts.gl_cash_account_id')
            ->whereBetween('sys_journal_entries.entry_date', [$from_date, $to_date])
            ->select(
                'sys_bank_accounts.name',
                'sys_bank_accounts.id',
                $this->db->raw('SUM(sys_journal_items.debit) as total_in'),
                $this->db->raw('SUM(sys_journal_items.credit) as total_out')
            )
            ->groupBy('sys_bank_accounts.id', 'sys_bank_accounts.name')
            ->get();

        $summary = [];
        foreach ($results as $row) {
            $summary[] = [
                'bank_account' => $row->name,
                'cash_in' => (float)($row->total_in ?? 0),
                'cash_out' => (float)($row->total_out ?? 0),
                'net' => (float)($row->total_in ?? 0) - (float)($row->total_out ?? 0)
            ];
        }

        return $summary;
    }

    /**
     * ============================================================
     * GET ALL BANK ACCOUNTS
     * ============================================================
     */
    public function getAllBankAccounts()
    {
        return $this->db->table('sys_bank_accounts')
            ->where('is_active', 1)
            ->get();
    }

    /**
     * ============================================================
     * GET BANK ACCOUNT DETAILS
     * ============================================================
     */
    public function getBankAccountDetails($bank_account_id)
    {
        return $this->db->table('sys_bank_accounts')
            ->where('id', $bank_account_id)
            ->first();
    }
}
