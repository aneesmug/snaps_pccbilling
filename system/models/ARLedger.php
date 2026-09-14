<?php

use Illuminate\Database\Eloquent\Model;

class ARLedger extends Model
{
    protected $table = 'sys_ar_ledger';
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
