<?php

use Illuminate\Database\Eloquent\Model;

class AccountingPeriod extends Model
{
    protected $table = 'sys_accounting_periods';
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('is_closed', 0);
    }

    public function scopeCurrent($query)
    {
        $today = date('Y-m-d');
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }
}
