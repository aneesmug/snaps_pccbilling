<?php

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    protected $table = 'sys_journal_items';
    protected $guarded = [];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(GLAccount::class, 'account_id');
    }
}
