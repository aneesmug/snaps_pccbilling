<?php

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $table = 'sys_journal_entries';
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_entry_id');
    }

    public function period()
    {
        return $this->belongsTo(AccountingPeriod::class, 'period_id');
    }

    public function scopePosted($query)
    {
        return $query->where('post_status', 'posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('post_status', 'draft');
    }

    public function getTotals()
    {
        $debit = $this->items()->sum('debit_amount');
        $credit = $this->items()->sum('credit_amount');
        return ['debit' => $debit, 'credit' => $credit, 'balanced' => abs($debit - $credit) < 0.01];
    }
}
