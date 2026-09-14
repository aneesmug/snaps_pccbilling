<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HrAttendance extends Model
{
    public static function getToday($employee_id)
    {
        return self::query()
            ->where('employee_id', $employee_id)
            ->where('date', date('Y-m-d'))
            ->first();
    }

    public static function getTimesheet($employee_id)
    {

        $last_three_months = Carbon::now()->subMonths(3)->format('Y-m-d');
        $this_month = Carbon::now()->format('Y-m-d');

        return self::query()
            ->where('employee_id',$employee_id)
            ->whereBetween('date',[$last_three_months,$this_month])
            ->orderBy('date','desc')
            ->get()
            ->groupBy(function($item){
                return Carbon::parse($item->date)->format('F Y');
            })
            ->toArray();
    }
}