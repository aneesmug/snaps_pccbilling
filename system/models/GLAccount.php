<?php

use Illuminate\Database\Eloquent\Model;

class GLAccount extends Model
{
    protected $table = 'sys_gl_accounts';
    protected $guarded = [];

    /**
     * Relationships
     */
    public function parent()
    {
        return $this->belongsTo(GLAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(GLAccount::class, 'parent_id');
    }

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }

    public function balance()
    {
        return $this->hasOne(GLAccountBalance::class, 'account_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Get account balance
     */
    public function getBalance($currency_id = 1)
    {
        $balance = $this->balance()
            ->where('currency_id', $currency_id)
            ->first();

        return $balance ? $balance->net_balance : 0;
    }

    /**
     * Get account type display
     */
    public function getTypeLabel()
    {
        $types = [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expense'
        ];

        return $types[$this->type] ?? $this->type;
    }
}

class JournalEntry extends Model
{
    protected $table = 'sys_journal_entries';
    protected $guarded = [];
    protected $casts = [
        'entry_date' => 'date',
        'post_date' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_entry_id');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }

    public function reversal()
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_of_je_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(JournalEntry::class, 'reversing_je_id');
    }

    /**
     * Scopes
     */
    public function scopePosted($query)
    {
        return $query->where('post_status', 'posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('post_status', 'draft');
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('source_module', $module);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('entry_date', [$from, $to]);
    }

    /**
     * Get totals
     */
    public function getTotals()
    {
        $items = $this->items;
        $debit = $items->sum('debit');
        $credit = $items->sum('credit');

        return [
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'difference' => abs(round($debit - $credit, 2)),
            'balanced' => abs($debit - $credit) < 0.01
        ];
    }

    /**
     * Get status display
     */
    public function getStatusLabel()
    {
        $statuses = [
            'draft' => 'Draft',
            'posted' => 'Posted',
            'reversed' => 'Reversed'
        ];

        return $statuses[$this->post_status] ?? $this->post_status;
    }
}

class JournalItem extends Model
{
    protected $table = 'sys_journal_items';
    protected $guarded = [];

    /**
     * Relationships
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(GLAccount::class, 'account_id');
    }

    /**
     * Get amount (debit or credit)
     */
    public function getAmount()
    {
        return $this->debit > 0 ? $this->debit : -$this->credit;
    }

    /**
     * Get direction
     */
    public function getDirection()
    {
        return $this->debit > 0 ? 'debit' : 'credit';
    }
}

class GLAccountBalance extends Model
{
    protected $table = 'sys_gl_account_balances';
    protected $guarded = [];

    /**
     * Relationships
     */
    public function account()
    {
        return $this->belongsTo(GLAccount::class, 'account_id');
    }

    /**
     * Get balance (considering account normal balance)
     */
    public function getBalance()
    {
        $account = $this->account;

        if ($account->normal_balance === 'debit') {
            return $this->debit_balance - $this->credit_balance;
        } else {
            return $this->credit_balance - $this->debit_balance;
        }
    }
}

class ARLedger extends Model
{
    protected $table = 'sys_ar_ledger';
    protected $guarded = [];
    protected $casts = [
        'posting_date' => 'date',
        'due_date' => 'date'
    ];

    /**
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo('Customer', 'customer_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function invoice()
    {
        return $this->belongsTo('Invoice', 'invoice_id');
    }

    /**
     * Scopes
     */
    public function scopeInvoices($query)
    {
        return $query->where('transaction_type', 'invoice');
    }

    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    public function scopeOutstanding($query)
    {
        return $query->where('is_allocated', 0);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue()
    {
        if (!$this->due_date) {
            return 0;
        }

        $days = now()->diffInDays($this->due_date);
        return $days > 0 ? $days : 0;
    }
}

class APLedger extends Model
{
    protected $table = 'sys_ap_ledger';
    protected $guarded = [];
    protected $casts = [
        'posting_date' => 'date',
        'due_date' => 'date'
    ];

    /**
     * Relationships
     */
    public function vendor()
    {
        return $this->belongsTo('Vendor', 'vendor_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function bill()
    {
        return $this->belongsTo('Bill', 'bill_id');
    }

    /**
     * Scopes
     */
    public function scopeBills($query)
    {
        return $query->where('transaction_type', 'bill');
    }

    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    public function scopeOutstanding($query)
    {
        return $query->where('is_allocated', 0);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue()
    {
        if (!$this->due_date) {
            return 0;
        }

        $days = now()->diffInDays($this->due_date);
        return $days > 0 ? $days : 0;
    }
}

class BankAccount extends Model
{
    protected $table = 'sys_bank_accounts';
    protected $guarded = [];

    /**
     * Relationships
     */
    public function glAccount()
    {
        return $this->belongsTo(GLAccount::class, 'gl_cash_account_id');
    }

    public function reconciliations()
    {
        return $this->hasMany(BankReconciliation::class, 'bank_account_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Get balance
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Get reconciled balance
     */
    public function getReconciledBalance()
    {
        return $this->reconciled_balance ?? 0;
    }

    /**
     * Get difference
     */
    public function getBalanceDifference()
    {
        return $this->balance - $this->reconciled_balance;
    }
}

class BankReconciliation extends Model
{
    protected $table = 'sys_bank_reconciliations';
    protected $guarded = [];
    protected $casts = [
        'statement_date' => 'date',
        'reconciled_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function matches()
    {
        return $this->hasMany(BankReconciliationMatch::class, 'reconciliation_id');
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeReconciled($query)
    {
        return $query->where('status', 'reconciled');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}

class BankReconciliationMatch extends Model
{
    protected $table = 'sys_bank_reconciliation_matches';
    protected $guarded = [];

    /**
     * Relationships
     */
    public function reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class, 'reconciliation_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}

class TaxCode extends Model
{
    protected $table = 'sys_tax_codes';
    protected $guarded = [];

    /**
     * Relationships
     */
    public function payableAccount()
    {
        return $this->belongsTo(GLAccount::class, 'gl_payable_account_id');
    }

    public function recoverableAccount()
    {
        return $this->belongsTo(GLAccount::class, 'gl_recoverable_account_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeZATCACompliant($query)
    {
        return $query->where('is_zatca_compliant', 1);
    }

    /**
     * Get rate percentage
     */
    public function getRatePercentage()
    {
        return (float)$this->rate;
    }
}

class AuditLog extends Model
{
    protected $table = 'sys_audit_logs';
    protected $guarded = [];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeByUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    public function scopeByTable($query, $table)
    {
        return $query->where('table_name', $table);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Get old value
     */
    public function getOldValueDecoded()
    {
        return json_decode($this->old_value, true);
    }

    /**
     * Get new value
     */
    public function getNewValueDecoded()
    {
        return json_decode($this->new_value, true);
    }
}

class AccountingPeriod extends Model
{
    protected $table = 'sys_accounting_periods';
    protected $guarded = [];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'locked_at' => 'datetime'
    ];

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Check if period is open
     */
    public function isOpen()
    {
        return $this->status === 'open';
    }

    /**
     * Check if date is in period
     */
    public function containsDate($date)
    {
        return $date >= $this->start_date && $date <= $this->end_date;
    }
}
