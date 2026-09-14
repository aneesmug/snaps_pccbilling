<?php

use Illuminate\Database\Eloquent\Model;

class BankReconciliationMatch extends Model
{
    protected $table = 'sys_bank_reconciliation_matches';
    protected $guarded = [];

    public function reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class, 'reconciliation_id');
    }
}
