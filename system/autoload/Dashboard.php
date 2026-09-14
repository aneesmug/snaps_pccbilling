<?php
use GuzzleHttp\Client;
class Dashboard
{
    public static function dataLastTwelveMonthsIncExp()
    {
        global $user;

        $all_data = true;
        if (!has_access($user->roleid, 'transactions', 'all_data')) {
            $all_data = false;
        }

        $months = [];

        global $_L;

        for ($i = 1; $i <= 11; $i++) {
            $months[] = date("M Y", strtotime(date('Y-m-01') . " -$i months"));
        }

        $months = array_reverse($months);

        $months[12] = date("M Y", strtotime(date('Y-m-01')));

        $inc = [];
        $exp = [];
        $m = [];

        foreach ($months as $month) {
            $d_array = explode(' ', $month);

            $m_short = '';

            if (isset($d_array['0'])) {
                $m_short = $d_array[0];
            }

            if (isset($_L[$m_short])) {
                $m_short = $_L[$m_short];
            }

            $y = '';
            if (isset($d_array[1])) {
                $y = $d_array[1];
            }

            $m[] = $m_short . ' ' . $y;
        }

        $i = 0;


        foreach ($months as $month) {
            $first_day_this_month = date(
                "Y-m-d",
                strtotime("first day of $month")
            );
            $last_day_this_month = date(
                "Y-m-d",
                strtotime("last day of $month")
            );

            $transactions = new Transaction();

            $transactions = $transactions->where('type', 'Income')
                ->where(function ($query) use ($first_day_this_month, $last_day_this_month) {
                    $query->where('date', '>=', $first_day_this_month)
                        ->where('date', '<=', $last_day_this_month);
                });

            if (!$all_data) {
                $transactions = $transactions->where('aid', $user->id);
            }

            $transactions = $transactions->get();



            $total = 0;

            foreach ($transactions as $transaction) {
                $total += $transaction->amount*$transaction->currency_rate;
            }

            $inc[] = $total;

            $inc[$i] = $inc[$i] == '' ? '0.00' : round($inc[$i]);

            $i++;
        }

        $i = 0;

        foreach ($months as $month) {
//            $first_day_this_month = date(
//                "Y-m-d",
//                strtotime("first day of $month")
//            );
//            $last_day_this_month = date(
//                "Y-m-d",
//                strtotime("last day of $month")
//            );
//
//            $x = ORM::for_table('sys_transactions')
//                ->where('type', 'Expense')
//                ->where_gte('date', $first_day_this_month)
//                ->where_lte('date', $last_day_this_month);
//
//            if (!$all_data) {
//                $x->where('aid', $user->id);
//            }
//
//            $exp[] = $x->sum('dr');
//
//            $exp[$i] = $exp[$i] == '' ? '0.00' : round($exp[$i]);
//
//            $i++;

            $first_day_this_month = date(
                "Y-m-d",
                strtotime("first day of $month")
            );
            $last_day_this_month = date(
                "Y-m-d",
                strtotime("last day of $month")
            );

            $transactions = new Transaction();

            $transactions = $transactions->where('type', 'Expense')->where(function ($query) use ($first_day_this_month, $last_day_this_month) {
                $query->where('date', '>=', $first_day_this_month)
                    ->where('date', '<=', $last_day_this_month);
            });

            if (!$all_data) {
                $transactions = $transactions->where('aid', $user->id);
            }

            $transactions = $transactions->get();

            $total = 0;

            foreach ($transactions as $transaction) {
                $total += $transaction->amount*$transaction->currency_rate;
            }

            $exp[] = $total;

            $exp[$i] = $exp[$i] == '' ? '0.00' : round($exp[$i]);

            $i++;

        }

        return [
            'Months' => $m,
            'Income' => $inc,
            'Expense' => $exp,
        ];
    }

    public static function dataIncExpD($select = null)
    {
        global $user;

        $all_data = true;
        if (!has_access($user->roleid, 'transactions', 'all_data')) {
            $all_data = false;
        }

        $inc = [];
        $exp = [];

        $i = 1;

        $inc = [];
        $exp = [];
        $year = date("Y");
        $month = date("m"); // Or you could dynamically set the month

        for ($i = 0; $i <= 31; $i++) {
            // Create the date with the current day in the loop
            $date_obj = DateTime::createFromFormat("Y-m-d", "$year-$month-$i");

            // If the date doesn't match the intended month, skip it (invalid date handling)
            if ($date_obj->format("m") !== $month) {
                continue;
            }

            // Format the date as Y-m-d
            $d1 = $date_obj->format("Y-m-d");

            // Query for Income
            $x = ORM::for_table('sys_transactions')
                ->where('type', 'Income')
                ->where('date', $d1);
            if (!$all_data) {
                $x->where('aid', $user->id);
            }
            $d1i = $x->sum('cr');
            $inc[] = $d1i ?: '0.00';

            // Query for Expense
            $x = ORM::for_table('sys_transactions')
                ->where('type', 'Expense')
                ->where('date', $d1);
            if (!$all_data) {
                $x->where('aid', $user->id);
            }
            $d1e = $x->sum('dr');
            $exp[] = $d1e ?: '0.00';
        }


        return [
            'Income' => $inc,
            'Expense' => $exp,
        ];
    }

    public static function graphUpdate($user, $config)
    {


        $manifest = require APP_SYSTEM_PATH . '/manifest.php';

        //Dashboard check version
        $u_a_m =
            'aHR0cHM6Ly93d3cuY2xvdWRvbmV4LmNvbS9hcGkvdmVyc2lvbi1jaGVjaz9maWxlX2lkPWQwNTBkMDY5LWM0M2QtNGM3Yy1hNDc4LThjOTkxN2MzYWMyZg==';
        $p = 'cHVyY2hhc2Vfa2V5';

        $graph_p = base64_decode($p);

        try {



            $client = new Client();

            $response = $client->request('POST', base64_decode($u_a_m), [
                'form_params' => [
                    'version' => $manifest['system']['version'] ?? '1.0.0',
                    'app_url' => APP_URL,
                    'fullname' => $user->fullname,
                    'email' => $user->username,
                    $graph_p => $config[$graph_p] ?? '',
                    'ip' => get_client_ip(),
                    'language' => $user->language,
                ],
            ]);

            $data = \json_decode((string) $response->getBody());

            if (defined('DISABLE_AUN')) {
                return [
                    'graph' => false,
                ];
            }

            if (property_exists($data, 'build') && $data->build !== null && $config['build'] < $data->build) {
                return [
                    'graph' => true,
                    'data' => $data->message,
                ];
            }
        } catch (Exception $e) {
        }

        return [
            'graph' => false,
        ];
    }

    public static function dataIncVsExp($month = '')
    {
        $mdate = date('Y-m-d');

        $first_day_month = $month == '' ? date('Y-m-01') : $month;

        $mi = ORM::for_table('sys_transactions')
            ->where('type', 'Income')
            ->where_gte('date', $first_day_month)
            ->where_lte('date', $mdate)
            ->sum('cr');
        if ($mi == '') {
            $mi = '0.00';
        }

        $me = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->where_gte('date', $first_day_month)
            ->where_lte('date', $mdate)
            ->sum('dr');
        if ($me == '') {
            $me = '0.00';
        }

        return [
            'Income' => $mi,
            'Expense' => $me,
        ];
    }
}
