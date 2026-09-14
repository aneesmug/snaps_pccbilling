<?php

use Illuminate\Database\Eloquent\Model;

class TaxCode extends Model
{
    protected $table = 'sys_tax_codes';
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
