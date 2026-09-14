<?php

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'sys_bank_accounts';
    protected $guarded = [];

    public function reconciliations()
    {
        return $this->hasMany(BankReconciliation::class, 'bank_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
