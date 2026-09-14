<?php

use Illuminate\Database\Eloquent\Model;

class APLedger extends Model
{
    protected $table = 'sys_ap_ledger';
    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(Contact::class, 'vendor_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
