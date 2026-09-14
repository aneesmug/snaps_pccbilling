<?php

use Illuminate\Database\Eloquent\Model;

class CustomerDue extends Model
{
    protected $table = 'customer_dues';

    public static function getSummaryForCustomer($customer_id)
    {
        $dues = CustomerDue::where('customer_id', $customer_id)
            ->orderBy('id', 'desc')
            ->get();

        $total_due_amount = 0.0;
        $total_paid_amount = 0.0;
        $total_unpaid_amount = 0.0;

        foreach ($dues as $due) {
            $amount = (float) $due->amount;
            $paid = (float) $due->paid_amount;

            $total_due_amount += $amount;
            $total_paid_amount += $paid;
            $total_unpaid_amount += ($amount - $paid);
        }

        return [
            'dues' => $dues,
            'total_due_amount' => $total_due_amount,
            'total_paid_amount' => $total_paid_amount,
            'total_unpaid_amount' => $total_unpaid_amount,
        ];
    }
}
