<?php
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{

    public static function typeNames()
    {
        return [
            'daily' => __('Daily'),
            'week1' => __('Weekly'),
            'weeks2' => __('Weeks_2'),
            'weeks3' => __('Weeks_3'),
            'weeks4' => __('Weeks_4'),
            'month1' => __('Monthly'),
            'months2' => __('Months_2'),
            'months3' => __('Months_3'),
            'months6' => __('Months_6'),
            'year1' => __('Yearly'),
            'years2' => __('Years_2'),
            'years3' => __('Years_3'),
        ];
    }

}
