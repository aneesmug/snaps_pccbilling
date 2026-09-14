<?php

/**
 * ============================================================
 * ACCOUNTING AUDIT & CONTROLS SERVICE
 * ============================================================
 * Manages accounting controls and compliance
 * - Period lock/close management
 * - Role-based permissions
 * - Complete audit trail
 * - Data integrity validation
 * - Compliance monitoring
 * ============================================================
 */

class AccountingControlsService
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
     * ACCOUNTING PERIOD MANAGEMENT
     * ============================================================
     */

    /**
     * CREATE NEW ACCOUNTING PERIOD
     */
    public function createPeriod($period_name, $start_date, $end_date)
    {
        try {
            // Check for overlap
            $overlap = $this->db->table('sys_accounting_periods')
                ->where(function ($query) use ($start_date, $end_date) {
                    $query->whereBetween('start_date', [$start_date, $end_date])
                        ->orWhereBetween('end_date', [$start_date, $end_date]);
                })
                ->first();

            if ($overlap) {
                return [
                    'success' => false,
                    'message' => 'Accounting period overlaps with existing period'
                ];
            }

            $period_id = $this->db->table('sys_accounting_periods')->insertGetId([
                'period_name' => $period_name,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => 'open',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('create', 'sys_accounting_periods', $period_id, null, [
                'period_name' => $period_name,
                'dates' => "$start_date to $end_date"
            ]);

            return [
                'success' => true,
                'period_id' => $period_id,
                'message' => 'Accounting period created'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating period: ' . $e->getMessage()
            ];
        }
    }

    /**
     * LOCK ACCOUNTING PERIOD
     * Prevents further postings to period
     */
    public function lockPeriod($period_id)
    {
        try {
            $period = $this->db->table('sys_accounting_periods')
                ->where('id', $period_id)
                ->first();

            if (!$period) {
                return ['success' => false, 'message' => 'Period not found'];
            }

            if ($period->status === 'locked') {
                return ['success' => false, 'message' => 'Period already locked'];
            }

            // Lock the period
            $this->db->table('sys_accounting_periods')
                ->where('id', $period_id)
                ->update([
                    'status' => 'locked',
                    'locked_by_user_id' => $this->user_id,
                    'locked_at' => date('Y-m-d H:i:s')
                ]);

            $this->logAudit('lock', 'sys_accounting_periods', $period_id, $period, [
                'status' => 'locked'
            ]);

            return [
                'success' => true,
                'message' => 'Accounting period locked'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error locking period: ' . $e->getMessage()
            ];
        }
    }

    /**
     * CLOSE ACCOUNTING PERIOD
     * Final close - prevents any modifications
     */
    public function closePeriod($period_id)
    {
        try {
            $period = $this->db->table('sys_accounting_periods')
                ->where('id', $period_id)
                ->first();

            if (!$period) {
                return ['success' => false, 'message' => 'Period not found'];
            }

            if ($period->status === 'closed') {
                return ['success' => false, 'message' => 'Period already closed'];
            }

            // Close the period
            $this->db->table('sys_accounting_periods')
                ->where('id', $period_id)
                ->update([
                    'status' => 'closed',
                    'locked_by_user_id' => $this->user_id,
                    'locked_at' => date('Y-m-d H:i:s')
                ]);

            $this->logAudit('close', 'sys_accounting_periods', $period_id, $period, [
                'status' => 'closed'
            ]);

            return [
                'success' => true,
                'message' => 'Accounting period closed'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error closing period: ' . $e->getMessage()
            ];
        }
    }

    /**
     * CHECK PERIOD STATUS
     */
    public function getPeriodStatus($period_id)
    {
        $period = $this->db->table('sys_accounting_periods')
            ->where('id', $period_id)
            ->first();

        if (!$period) {
            return ['success' => false, 'message' => 'Period not found'];
        }

        return [
            'success' => true,
            'period_id' => $period->id,
            'period_name' => $period->period_name,
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
            'status' => $period->status,
            'locked_at' => $period->locked_at,
            'can_post' => $period->status === 'open'
        ];
    }

    /**
     * ============================================================
     * ROLE-BASED ACCESS CONTROL (RBAC)
     * ============================================================
     */

    /**
     * CHECK PERMISSION
     */
    public function hasPermission($user_id, $permission)
    {
        $user = $this->db->table('sys_users')
            ->where('id', $user_id)
            ->first();

        if (!$user) {
            return false;
        }

        // Get user role
        $role = $this->db->table('sys_roles')
            ->where('id', $user->role_id)
            ->first();

        if (!$role) {
            return false;
        }

        // Check permission for role
        $has_perm = $this->db->table('sys_permissions')
            ->where('role_id', $role->id)
            ->where('permission', $permission)
            ->exists();

        return $has_perm;
    }

    /**
     * REQUIRE PERMISSION
     */
    public function requirePermission($permission)
    {
        if (!$this->hasPermission($this->user_id, $permission)) {
            throw new \Exception("User does not have permission: $permission");
        }

        return true;
    }

    /**
     * GET ACCOUNTING PERMISSIONS
     */
    public function getAccountingPermissions()
    {
        return [
            'create_journal_entry' => 'Create journal entries',
            'post_journal_entry' => 'Post journal entries',
            'reverse_journal_entry' => 'Reverse journal entries',
            'post_invoice' => 'Post invoices to AR',
            'post_bill' => 'Post bills to AP',
            'record_payment' => 'Record payments',
            'bank_reconciliation' => 'Bank reconciliation',
            'close_period' => 'Close accounting periods',
            'view_reports' => 'View financial reports',
            'export_reports' => 'Export financial reports',
            'manage_gl_accounts' => 'Manage GL accounts',
            'audit_log_access' => 'Access audit logs'
        ];
    }

    /**
     * ============================================================
     * AUDIT LOGGING
     * ============================================================
     */

    /**
     * LOG AUDIT EVENT
     */
    public function logAudit($action, $table_name, $record_id, $old_value = null, $new_value = null)
    {
        try {
            $this->db->table('sys_audit_logs')->insert([
                'user_id' => $this->user_id,
                'action' => $action,
                'table_name' => $table_name,
                'record_id' => $record_id,
                'old_value' => is_array($old_value) ? json_encode($old_value) : $old_value,
                'new_value' => is_array($new_value) ? json_encode($new_value) : $new_value,
                'ip_address' => request()->ip() ?? '0.0.0.0',
                'user_agent' => request()->userAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return true;

        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            return false;
        }
    }

    /**
     * GET AUDIT LOG
     */
    public function getAuditLog($filters = [])
    {
        $query = $this->db->table('sys_audit_logs')
            ->join('sys_users', 'sys_audit_logs.user_id', '=', 'sys_users.id', 'left');

        if (!empty($filters['user_id'])) {
            $query->where('sys_audit_logs.user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('sys_audit_logs.action', $filters['action']);
        }

        if (!empty($filters['table_name'])) {
            $query->where('sys_audit_logs.table_name', $filters['table_name']);
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('sys_audit_logs.created_at', [
                $filters['from_date'] . ' 00:00:00',
                $filters['to_date'] . ' 23:59:59'
            ]);
        }

        return $query->select(
            'sys_audit_logs.*',
            'sys_users.name as user_name',
            'sys_users.email'
        )
        ->orderBy('sys_audit_logs.created_at', 'desc')
        ->paginate($filters['per_page'] ?? 50);
    }

    /**
     * GET RECORD HISTORY
     */
    public function getRecordHistory($table_name, $record_id)
    {
        return $this->db->table('sys_audit_logs')
            ->where('table_name', $table_name)
            ->where('record_id', $record_id)
            ->join('sys_users', 'sys_audit_logs.user_id', '=', 'sys_users.id', 'left')
            ->select(
                'sys_audit_logs.*',
                'sys_users.name as user_name'
            )
            ->orderBy('sys_audit_logs.created_at')
            ->get();
    }

    /**
     * ============================================================
     * DATA INTEGRITY VALIDATION
     * ============================================================
     */

    /**
     * VALIDATE GENERAL LEDGER INTEGRITY
     */
    public function validateGLIntegrity()
    {
        try {
            $result = $this->db->selectOne("
                SELECT 
                    COUNT(*) as entry_count,
                    SUM(total_debit) as total_debit,
                    SUM(total_credit) as total_credit
                FROM (
                    SELECT 
                        je.id,
                        SUM(ji.debit) as total_debit,
                        SUM(ji.credit) as total_credit
                    FROM sys_journal_entries je
                    LEFT JOIN sys_journal_items ji ON je.id = ji.journal_entry_id
                    WHERE je.post_status = 'posted'
                    GROUP BY je.id
                ) as entries
            ");

            $debit = (float)($result->total_debit ?? 0);
            $credit = (float)($result->total_credit ?? 0);
            $difference = abs($debit - $credit);
            $is_valid = $difference < 0.01;

            return [
                'success' => true,
                'is_valid' => $is_valid,
                'entry_count' => (int)$result->entry_count,
                'total_debit' => round($debit, 2),
                'total_credit' => round($credit, 2),
                'difference' => round($difference, 2),
                'validation_date' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error validating GL: ' . $e->getMessage()
            ];
        }
    }

    /**
     * VALIDATE AR LEDGER
     */
    public function validateARLedger()
    {
        try {
            $unallocated = $this->db->selectOne("
                SELECT 
                    COUNT(*) as count,
                    SUM(amount) as total
                FROM sys_ar_ledger
                WHERE is_allocated = 0 AND posting_date <= DATE(date('Y-m-d H:i:s'))
            ");

            return [
                'success' => true,
                'unallocated_count' => (int)($unallocated->count ?? 0),
                'unallocated_amount' => (float)($unallocated->total ?? 0)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error validating AR: ' . $e->getMessage()
            ];
        }
    }

    /**
     * VALIDATE AP LEDGER
     */
    public function validateAPLedger()
    {
        try {
            $unallocated = $this->db->selectOne("
                SELECT 
                    COUNT(*) as count,
                    SUM(amount) as total
                FROM sys_ap_ledger
                WHERE is_allocated = 0 AND posting_date <= DATE(date('Y-m-d H:i:s'))
            ");

            return [
                'success' => true,
                'unallocated_count' => (int)($unallocated->count ?? 0),
                'unallocated_amount' => (float)($unallocated->total ?? 0)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error validating AP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * COMPLIANCE CHECKS
     * ============================================================
     */

    /**
     * COMPLIANCE REPORT
     */
    public function getComplianceReport($as_of_date = null)
    {
        if (!$as_of_date) {
            $as_of_date = date('Y-m-d');
        }

        $checks = [];

        // Check 1: GL Integrity
        $gl_check = $this->validateGLIntegrity();
        $checks[] = [
            'name' => 'GL Integrity (Debit = Credit)',
            'status' => $gl_check['is_valid'] ? 'PASS' : 'FAIL',
            'details' => $gl_check
        ];

        // Check 2: Unbalanced Entries
        $unbalanced = $this->db->table('sys_journal_entries')
            ->where('post_status', 'draft')
            ->where('entry_date', '<=', $as_of_date)
            ->count();

        $checks[] = [
            'name' => 'Pending Draft Entries',
            'status' => $unbalanced === 0 ? 'PASS' : 'WARNING',
            'count' => $unbalanced
        ];

        // Check 3: AR Aging
        $ar_check = $this->validateARLedger();
        $checks[] = [
            'name' => 'Accounts Receivable',
            'status' => 'INFO',
            'details' => $ar_check
        ];

        // Check 4: AP Aging
        $ap_check = $this->validateAPLedger();
        $checks[] = [
            'name' => 'Accounts Payable',
            'status' => 'INFO',
            'details' => $ap_check
        ];

        // Check 5: Period Status
        $locked_periods = $this->db->table('sys_accounting_periods')
            ->where('status', 'locked')
            ->where('end_date', '>=', $as_of_date)
            ->count();

        $checks[] = [
            'name' => 'Locked Periods',
            'status' => 'INFO',
            'count' => $locked_periods
        ];

        // Summary
        $passed = count(array_filter($checks, fn($c) => $c['status'] === 'PASS'));
        $failed = count(array_filter($checks, fn($c) => $c['status'] === 'FAIL'));
        $warnings = count(array_filter($checks, fn($c) => $c['status'] === 'WARNING'));

        return [
            'success' => true,
            'as_of_date' => $as_of_date,
            'checks' => $checks,
            'summary' => [
                'total_checks' => count($checks),
                'passed' => $passed,
                'failed' => $failed,
                'warnings' => $warnings,
                'overall_status' => $failed > 0 ? 'FAIL' : ($warnings > 0 ? 'WARNING' : 'PASS')
            ]
        ];
    }

    /**
     * ============================================================
     * HELPER: Get All Periods
     * ============================================================
     */
    public function getAllPeriods($status = null)
    {
        $query = $this->db->table('sys_accounting_periods');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }
}
