<?php

use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    protected $table = 'sys_bank_reconciliations';
    protected $guarded = [];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function matches()
    {
        return $this->hasMany(BankReconciliationMatch::class, 'reconciliation_id');
    }
}
