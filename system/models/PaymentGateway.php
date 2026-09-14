<?php
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table = 'sys_pg';

    public static function getByApiName(string $api_name)
    {
        return self::where('processor', $api_name)
            ->where('status', 'Active')
            ->first();
    }
}
