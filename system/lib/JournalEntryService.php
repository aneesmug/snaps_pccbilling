<?php

/**
 * ============================================================
 * JOURNAL ENTRY SERVICE
 * ============================================================
 * Core double-entry accounting engine
 * - Validates all entries must be balanced (Debit = Credit)
 * - Uses database transactions with rollback on error
 * - Updates GL account balances atomically
 * - Logs all operations for audit trail
 * - Prevents direct balance updates (only via JE posting)
 * 
 * PRINCIPLES:
 * - ACID compliance mandatory
 * - No deletion allowed (reversal entries only)
 * - GL is single source of truth
 * - Every transaction must post to GL
 * ============================================================
 */

class JournalEntryService
{
    protected $db;
    protected $user_id;
    protected $company_id;
    
    public function __construct($db = null, $user_id = null, $company_id = null)
    {
        $this->db = $db ?: \Illuminate\Database\Capsule\Manager::connection();
        $this->user_id = $user_id ?: (isset($GLOBALS['user']) && $GLOBALS['user'] ? $GLOBALS['user']->id : null);
        $this->company_id = $company_id;
    }

    /**
     * ============================================================
     * CREATE & POST JOURNAL ENTRY
     * ============================================================
     * Main entry point for all GL transactions
     * 
     * @param array $data {
     *     'entry_date'     => 'Y-m-d' (posting date),
     *     'reference'      => 'INV-001' (optional, for traceability),
     *     'description'    => 'Invoice from Customer ABC',
     *     'source_module'  => 'invoice|bill|payment|adjustment',
     *     'source_id'      => 123 (record ID from source module),
     *     'lines' => [
     *         ['account_id' => 1000, 'debit' => 1000.00, 'credit' => 0, 'description' => 'Debit line'],
     *         ['account_id' => 2000, 'debit' => 0, 'credit' => 1000.00, 'description' => 'Credit line'],
     *     ]
     * }
     * @return array ['success' => bool, 'journal_entry_id' => int, 'message' => string]
     */
    public function createAndPostJournalEntry($data)
    {
        try {
            // START TRANSACTION
            $this->db->beginTransaction();

            // VALIDATE INPUT
            $validation = $this->validateJournalEntryInput($data);
            if (!$validation['success']) {
                $this->db->rollBack();
                return $validation;
            }

            // VALIDATE BALANCED (CRITICAL)
            $balance_check = $this->validateBalance($data['lines']);
            if (!$balance_check['balanced']) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Journal entry is NOT balanced. Debit: ' . $balance_check['total_debit'] . 
                                 ', Credit: ' . $balance_check['total_credit']
                ];
            }

            // CHECK ACCOUNTING PERIOD
            $period_check = $this->validateAccountingPeriod($data['entry_date']);
            if (!$period_check['success']) {
                $this->db->rollBack();
                return $period_check;
            }

            // CREATE JOURNAL ENTRY HEADER
            $journal_entry_id = $this->insertJournalEntryHeader($data);

            // CREATE JOURNAL ITEMS (Lines)
            $this->insertJournalItems($journal_entry_id, $data['lines']);

            // POST TO GL (Update account balances)
            $this->postToGeneralLedger($journal_entry_id, $data['lines']);

            // AUDIT LOG
            $this->auditLog(
                'create',
                'sys_journal_entries',
                $journal_entry_id,
                null,
                json_encode($data)
            );

            // COMMIT TRANSACTION
            $this->db->commit();

            return [
                'success' => true,
                'journal_entry_id' => $journal_entry_id,
                'message' => 'Journal entry #' . $journal_entry_id . ' posted successfully',
                'total_debit' => $balance_check['total_debit'],
                'total_credit' => $balance_check['total_credit']
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error posting journal entry: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * ============================================================
     * VALIDATE JOURNAL ENTRY INPUT
     * ============================================================
     */
    protected function validateJournalEntryInput($data)
    {
        // Required fields
        if (empty($data['entry_date'])) {
            return ['success' => false, 'message' => 'entry_date is required'];
        }

        if (empty($data['source_module'])) {
            return ['success' => false, 'message' => 'source_module is required'];
        }

        if (empty($data['lines']) || count($data['lines']) < 2) {
            return ['success' => false, 'message' => 'Journal entry must have at least 2 lines (debit + credit)'];
        }

        // Validate date
        if (!strtotime($data['entry_date'])) {
            return ['success' => false, 'message' => 'Invalid entry_date format (use YYYY-MM-DD)'];
        }

        // Validate each line has account_id and debit/credit amounts
        foreach ($data['lines'] as $i => $line) {
            if (empty($line['account_id'])) {
                return ['success' => false, 'message' => 'Line ' . ($i + 1) . ' missing account_id'];
            }

            if (!isset($line['debit']) || !isset($line['credit'])) {
                return ['success' => false, 'message' => 'Line ' . ($i + 1) . ' missing debit/credit amounts'];
            }

            // Validate account exists and is active
            $account = $this->getAccount($line['account_id']);
            if (!$account) {
                return ['success' => false, 'message' => 'Line ' . ($i + 1) . ' references non-existent account'];
            }

            if (!$account->is_active) {
                return ['success' => false, 'message' => 'Line ' . ($i + 1) . ' references inactive account'];
            }
        }

        return ['success' => true];
    }

    /**
     * ============================================================
     * VALIDATE BALANCED (DEBIT = CREDIT)
     * ============================================================
     * CRITICAL: Must be EXACTLY balanced or transaction REJECTED
     * Precision: 2 decimal places (cents)
     */
    protected function validateBalance($lines)
    {
        $total_debit = 0;
        $total_credit = 0;

        foreach ($lines as $line) {
            $total_debit += (float)$line['debit'];
            $total_credit += (float)$line['credit'];
        }

        // Round to 2 decimals to handle floating point precision
        $total_debit = round($total_debit, 2);
        $total_credit = round($total_credit, 2);

        return [
            'balanced' => ($total_debit === $total_credit),
            'total_debit' => $total_debit,
            'total_credit' => $total_credit,
            'difference' => abs($total_debit - $total_credit)
        ];
    }

    /**
     * ============================================================
     * CHECK ACCOUNTING PERIOD STATUS
     * ============================================================
     * Prevent posting to locked or closed periods
     */
    protected function validateAccountingPeriod($entry_date)
    {
        $period = $this->db->table('sys_accounting_periods')
            ->where('start_date', '<=', $entry_date)
            ->where('end_date', '>=', $entry_date)
            ->first();

        if (!$period) {
            return ['success' => false, 'message' => 'No open accounting period for date: ' . $entry_date];
        }

        if ($period->status !== 'open') {
            return ['success' => false, 'message' => 'Accounting period is ' . $period->status . ' (cannot post)'];
        }

        return ['success' => true, 'period_id' => $period->id];
    }

    /**
     * ============================================================
     * INSERT JOURNAL ENTRY HEADER
     * ============================================================
     */
    protected function insertJournalEntryHeader($data)
    {
        $je_data = [
            'entry_date' => $data['entry_date'],
            'reference' => $data['reference'] ?? null,
            'description' => $data['description'] ?? null,
            'source_module' => $data['source_module'],
            'source_id' => $data['source_id'] ?? null,
            'company_id' => $this->company_id,
            'entered_by_user_id' => $this->user_id,
            'posted_by_user_id' => $this->user_id,
            'post_status' => 'posted',
            'post_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('sys_journal_entries')->insertGetId($je_data);
    }

    /**
     * ============================================================
     * INSERT JOURNAL ITEMS (Detail Lines)
     * ============================================================
     */
    protected function insertJournalItems($journal_entry_id, $lines)
    {
        $line_no = 1;

        foreach ($lines as $line) {
            $item_data = [
                'journal_entry_id' => $journal_entry_id,
                'account_id' => $line['account_id'],
                'debit' => (float)$line['debit'],
                'credit' => (float)$line['credit'],
                'description' => $line['description'] ?? null,
                'line_no' => $line_no++,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('sys_journal_items')->insert($item_data);
        }
    }

    /**
     * ============================================================
     * POST TO GENERAL LEDGER
     * ============================================================
     * Update GL account balances based on posted journal items
     * This is the ONLY way to update GL account balances
     */
    protected function postToGeneralLedger($journal_entry_id, $lines)
    {
        foreach ($lines as $line) {
            $account_id = $line['account_id'];
            $debit = (float)$line['debit'];
            $credit = (float)$line['credit'];

            // Get or create balance record
            $balance = $this->db->table('sys_gl_account_balances')
                ->where('account_id', $account_id)
                ->where('currency_id', 1)  // Default currency, adjust as needed
                ->first();

            if (!$balance) {
                $this->db->table('sys_gl_account_balances')->insert([
                    'account_id' => $account_id,
                    'currency_id' => 1,
                    'debit_balance' => $debit,
                    'credit_balance' => $credit,
                    'net_balance' => $debit - $credit
                ]);
            } else {
                // Update balance
                $new_debit = $balance->debit_balance + $debit;
                $new_credit = $balance->credit_balance + $credit;
                $new_net = $new_debit - $new_credit;

                $this->db->table('sys_gl_account_balances')
                    ->where('id', $balance->id)
                    ->update([
                        'debit_balance' => $new_debit,
                        'credit_balance' => $new_credit,
                        'net_balance' => $new_net,
                        'last_updated' => date('Y-m-d H:i:s')
                    ]);
            }
        }
    }

    /**
     * ============================================================
     * REVERSE A JOURNAL ENTRY (No Deletion)
     * ============================================================
     * Create a reversing entry instead of deleting
     * Maintains complete audit trail
     * 
     * @param int $journal_entry_id Original JE to reverse
     * @return array
     */
    public function reverseJournalEntry($journal_entry_id)
    {
        try {
            $this->db->beginTransaction();

            // Get original JE
            $original_je = $this->db->table('sys_journal_entries')
                ->where('id', $journal_entry_id)
                ->first();

            if (!$original_je) {
                return ['success' => false, 'message' => 'Journal entry not found'];
            }

            if ($original_je->post_status === 'reversed') {
                return ['success' => false, 'message' => 'Journal entry is already reversed'];
            }

            // Get original items
            $original_items = $this->db->table('sys_journal_items')
                ->where('journal_entry_id', $journal_entry_id)
                ->get();

            // Create reversing entry with opposite debit/credit
            $reversal_lines = [];
            foreach ($original_items as $item) {
                $reversal_lines[] = [
                    'account_id' => $item->account_id,
                    'debit' => $item->credit,  // Flip debit and credit
                    'credit' => $item->debit,
                    'description' => 'Reversal of ' . $original_je->reference
                ];
            }

            // Create reversal JE
            $reversal_data = [
                'entry_date' => date('Y-m-d'),
                'reference' => 'REV-' . $original_je->reference,
                'description' => 'Reversal of ' . $original_je->description,
                'source_module' => $original_je->source_module,
                'source_id' => $original_je->source_id,
                'lines' => $reversal_lines
            ];

            $result = $this->createAndPostJournalEntry($reversal_data);

            if ($result['success']) {
                // Mark original as reversed
                $this->db->table('sys_journal_entries')
                    ->where('id', $journal_entry_id)
                    ->update([
                        'post_status' => 'reversed',
                        'reversing_je_id' => $result['journal_entry_id']
                    ]);

                // Link reversal back to original
                $this->db->table('sys_journal_entries')
                    ->where('id', $result['journal_entry_id'])
                    ->update([
                        'reversal_of_je_id' => $journal_entry_id
                    ]);

                $this->auditLog(
                    'reverse',
                    'sys_journal_entries',
                    $journal_entry_id,
                    $original_je,
                    'Reversed by JE #' . $result['journal_entry_id']
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error reversing journal entry: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * GET JOURNAL ENTRY DETAILS
     * ============================================================
     */
    public function getJournalEntry($journal_entry_id)
    {
        return $this->db->table('sys_journal_entries')
            ->where('id', $journal_entry_id)
            ->first();
    }

    public function getJournalItems($journal_entry_id)
    {
        return $this->db->table('sys_journal_items')
            ->join('sys_gl_accounts', 'sys_journal_items.account_id', '=', 'sys_gl_accounts.id')
            ->where('journal_entry_id', $journal_entry_id)
            ->select(
                'sys_journal_items.*',
                'sys_gl_accounts.code',
                'sys_gl_accounts.name'
            )
            ->orderBy('line_no')
            ->get();
    }

    /**
     * ============================================================
     * AUDIT LOGGING
     * ============================================================
     */
    protected function auditLog($action, $table_name, $record_id, $old_value = null, $new_value = null)
    {
        $this->db->table('sys_audit_logs')->insert([
            'user_id' => $this->user_id,
            'action' => $action,
            'table_name' => $table_name,
            'record_id' => $record_id,
            'old_value' => is_array($old_value) ? json_encode($old_value) : $old_value,
            'new_value' => is_array($new_value) ? json_encode($new_value) : $new_value,
            'ip_address' => request()->ip() ?? '0.0.0.0',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * ============================================================
     * HELPER: GET ACCOUNT
     * ============================================================
     */
    protected function getAccount($account_id)
    {
        return $this->db->table('sys_gl_accounts')
            ->where('id', $account_id)
            ->first();
    }

    /**
     * ============================================================
     * LIST JOURNAL ENTRIES (with filters)
     * ============================================================
     */
    public function listJournalEntries($filters = [])
    {
        $query = $this->db->table('sys_journal_entries');

        if (!empty($filters['source_module'])) {
            $query->where('source_module', $filters['source_module']);
        }

        if (!empty($filters['status'])) {
            $query->where('post_status', $filters['status']);
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('entry_date', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * ============================================================
     * GL ACCOUNT BALANCE QUERY
     * ============================================================
     */
    public function getAccountBalance($account_id, $currency_id = 1)
    {
        $balance = $this->db->table('sys_gl_account_balances')
            ->where('account_id', $account_id)
            ->where('currency_id', $currency_id)
            ->first();

        return $balance ? [
            'debit_balance' => (float)$balance->debit_balance,
            'credit_balance' => (float)$balance->credit_balance,
            'net_balance' => (float)$balance->net_balance
        ] : [
            'debit_balance' => 0,
            'credit_balance' => 0,
            'net_balance' => 0
        ];
    }

    /**
     * ============================================================
     * VALIDATE GL INTEGRITY (Audit)
     * ============================================================
     * Run integrity checks to ensure GL is balanced
     */
    public function validateGLIntegrity()
    {
        $result = $this->db->selectOne("
            SELECT 
                SUM(debit) as total_debit,
                SUM(credit) as total_credit
            FROM sys_journal_items
            WHERE journal_entry_id IN (
                SELECT id FROM sys_journal_entries 
                WHERE post_status = 'posted'
            )
        ");

        $balanced = abs($result->total_debit - $result->total_credit) < 0.01;

        return [
            'integrity_check' => $balanced ? 'PASS' : 'FAIL',
            'total_debit' => (float)$result->total_debit,
            'total_credit' => (float)$result->total_credit,
            'difference' => abs($result->total_debit - $result->total_credit)
        ];
    }
}
