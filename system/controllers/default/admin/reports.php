<?php
/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

_auth();
$ui->assign('_title', $_L['Reports'] . '- ' . $config['CompanyName']);
$ui->assign('selected_navigation', 'reports');
$action = $routes['1'];
$user = authenticate_admin();
$mdate = date('Y-m-d');
$tdate = date('Y-m-d', strtotime('today - 30 days'));

$first_day_month = date('Y-m-01');
$this_week_start = date('Y-m-d', strtotime('previous sunday'));
$before_30_days = date('Y-m-d', strtotime('today - 30 days'));
$month_n = date('n');
$data = $_GET;

if (!function_exists('zatca_report_date_range')) {
    function zatca_report_date_range(array $data)
    {
        $default_from = date('Y-m-01');
        $default_to = date('Y-m-d');

        $from = isset($data['fdate']) ? trim((string) $data['fdate']) : $default_from;
        $to = isset($data['tdate']) ? trim((string) $data['tdate']) : $default_to;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = $default_from;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = $default_to;
        }

        if (strtotime($from) > strtotime($to)) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        return [$from, $to];
    }
}

if (!function_exists('zatca_response_statuses')) {
    function zatca_response_statuses($invoice_or_raw_response)
    {
        $reporting_status = '';
        $validation_status = '';

        if (
            class_exists('ZatcaPhase2') &&
            (is_array($invoice_or_raw_response) || is_object($invoice_or_raw_response))
        ) {
            $report = ZatcaPhase2::buildInvoiceRegistrationReport($invoice_or_raw_response);

            return [
                strtoupper(trim((string) ($report['reporting_status'] ?? ''))),
                strtoupper(trim((string) ($report['validation_status'] ?? ''))),
            ];
        }

        $raw_response = $invoice_or_raw_response;

        if (!is_string($raw_response) || trim($raw_response) === '') {
            return [$reporting_status, $validation_status];
        }

        $decoded = json_decode($raw_response, true);
        if (!is_array($decoded)) {
            return [$reporting_status, $validation_status];
        }

        if (isset($decoded['reportingStatus'])) {
            $reporting_status = strtoupper(trim((string) $decoded['reportingStatus']));
        } elseif (isset($decoded['reporting_status'])) {
            $reporting_status = strtoupper(trim((string) $decoded['reporting_status']));
        } elseif (isset($decoded['clearanceStatus'])) {
            $reporting_status = strtoupper(trim((string) $decoded['clearanceStatus']));
        }

        if (isset($decoded['validationResults']['status'])) {
            $validation_status = strtoupper(trim((string) $decoded['validationResults']['status']));
        } elseif (isset($decoded['validation_results']['status'])) {
            $validation_status = strtoupper(trim((string) $decoded['validation_results']['status']));
        } elseif (isset($decoded['validation_status'])) {
            $validation_status = strtoupper(trim((string) $decoded['validation_status']));
        }

        return [$reporting_status, $validation_status];
    }
}

if (!function_exists('zatca_bool_flag')) {
    function zatca_bool_flag($input, $default = false)
    {
        if (is_array($input)) {
            $input = end($input);
        }

        if ($input === null) {
            return (bool) $default;
        }

        $input = strtolower(trim((string) $input));

        return in_array($input, ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('zatca_vat_return_summary')) {
    /**
     * Aggregates invoices/expenses into the ZATCA VAT return declaration boxes
     * (Standard-rated / zero-rated / exempt sales & purchases, output vs input VAT).
     * Returns/Cancelled invoices are netted off output VAT the same way ZATCA expects
     * a credit note to reduce the original supply's declared value.
     */
    function zatca_vat_return_summary(array $invoices, array $expenses)
    {
        $s = [
            'standard_sales_net' => 0.0,
            'standard_sales_vat' => 0.0,
            'zero_exempt_sales_net' => 0.0,
            'returns_net' => 0.0,
            'returns_vat' => 0.0,
            'cancelled_net' => 0.0,
            'cancelled_vat' => 0.0,
            'standard_purchases_net' => 0.0,
            'standard_purchases_vat' => 0.0,
            'zero_purchases_net' => 0.0,
        ];

        foreach ($invoices as $inv) {
            $is_return = (string) ($inv['type'] ?? '') === 'Credit Note' && (int) ($inv['parent_id'] ?? 0) > 0;
            $is_cancelled = strcasecmp((string) ($inv['status'] ?? ''), 'Cancelled') === 0;
            $net = abs((float) ($inv['subtotal'] ?? 0));
            $tax = abs((float) ($inv['tax'] ?? 0));

            if ($is_return) {
                $s['returns_net'] += $net;
                $s['returns_vat'] += $tax;
            } elseif ($is_cancelled) {
                $s['cancelled_net'] += $net;
                $s['cancelled_vat'] += $tax;
            } elseif ($tax > 0) {
                $s['standard_sales_net'] += $net;
                $s['standard_sales_vat'] += $tax;
            } else {
                $s['zero_exempt_sales_net'] += $net;
            }
        }

        foreach ($expenses as $exp) {
            $gross = (float) ($exp['amount'] ?? 0);
            $tax = (float) ($exp['tax'] ?? 0);
            $net = round($gross - $tax, 2);
            if ($net < 0) {
                $net = 0.0;
            }

            if ($tax > 0) {
                $s['standard_purchases_net'] += $net;
                $s['standard_purchases_vat'] += $tax;
            } else {
                $s['zero_purchases_net'] += $net;
            }
        }

        $s['total_output_vat'] = round($s['standard_sales_vat'] - $s['returns_vat'] - $s['cancelled_vat'], 2);
        $s['total_input_vat'] = round($s['standard_purchases_vat'], 2);
        $s['net_vat_due'] = round($s['total_output_vat'] - $s['total_input_vat'], 2);

        foreach ($s as $k => $v) {
            $s[$k] = round($v, 2);
        }

        return $s;
    }
}

switch ($action) {
    case 'statement':
        $all_data = has_access($user->roleid, 'bank_n_cash', 'all_data');

        $d = ORM::for_table('sys_accounts');

        if (!$all_data) {
            $d->where('owner_id', $user->id);
        }

        $d = $d->find_many();

        $ui->assign('d', $d);

        $ui->assign('mdate', $mdate);
        $ui->assign('tdate', $tdate);

        view('statement');

        break;

    case 'statement-view':
        $fdate = _post('fdate');
        $tdate = _post('tdate');
        $account = _post('account');
        $stype = _post('stype');
        $d = ORM::for_table('sys_transactions');
        $d->where('account', $account);
        if ($stype == 'credit') {
            $d->where('dr', '0.00');
        } elseif ($stype == 'debit') {
            $d->where('cr', '0.00');
        } else {
        }
        $d->where_gte('date', $fdate);
        $d->where_lte('date', $tdate);
        $d->order_by_desc('id');
        $x = $d->find_many();

        $ui->assign('d', $x);
        $ui->assign('fdate', $fdate);
        $ui->assign('tdate', $tdate);
        $ui->assign('account', $account);
        $ui->assign('stype', $stype);

        view('statement-view');
        break;

    case 'by-date':

        \view('reports-by-date');

        break;

    case 'income':
        $all_data = has_access($user->roleid, 'transactions', 'all_data');

        $d = ORM::for_table('sys_transactions')
            ->where('type', 'Income')
            ->limit(500)
            ->order_by_desc('id');

        if (!$all_data) {
            $d->where('aid', $user->id);
        }

        $d = $d->find_many();

        $ui->assign('d', $d);

        $a = ORM::for_table('sys_transactions');

        if (!$all_data) {
            $a->where('aid', $user->id);
        }

        $a = $a->sum('cr');

        if ($a == '') {
            $a = '0.00';
        }
        $ui->assign('a', $a);

        $m = ORM::for_table('sys_transactions')
            ->where('type', 'Income')
            ->where_gte('date', $first_day_month)
            ->where_lte('date', $mdate);

        if (!$all_data) {
            $m->where('aid', $user->id);
        }

        $m = $m->sum('cr');

        if ($m == '') {
            $m = '0.00';
        }
        $ui->assign('m', $m);

        $w = ORM::for_table('sys_transactions')
            ->where_gte('date', $this_week_start)
            ->where_lte('date', $mdate);

        if (!$all_data) {
            $w->where('aid', $user->id);
        }

        $w = $w->sum('cr');

        if ($w == '') {
            $w = '0.00';
        }

        $ui->assign('w', $w);

        $m3 = ORM::for_table('sys_transactions')
            ->where_gte('date', $before_30_days)
            ->where_lte('date', $mdate);

        if (!$all_data) {
            $m3->where('aid', $user->id);
        }

        $m3 = $m3->sum('cr');

        if ($m3 == '') {
            $m3 = '0.00';
        }
        $ui->assign('m3', $m3);

        $ui->assign('mdate', $mdate);
        $array = [
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        ];
        $till = $month_n - 1;
        $gstring = '';

        $m_data = [];

        $i = 0;

        for ($m = 0; $m <= $till; $m++) {
            $mnth = $array[$m];
            $cal = ORM::for_table('sys_transactions')
                ->where_gte(
                    'date',
                    date('Y-m-d', strtotime("first day of $mnth"))
                )
                ->where_lte(
                    'date',
                    date('Y-m-d', strtotime("last day of $mnth"))
                );

            if (!$all_data) {
                $cal->where('aid', $user->id);
            }

            $cal = $cal->sum('cr');
            $gstring .= '["' . ib_lan_get_line($mnth) . '",' . $cal . '], ';

            $m_data[$i]['month'] = ib_lan_get_line($mnth);
            $m_data[$i]['value'] = $cal;

            $i++;
        }
        $gstring = rtrim($gstring, ',');

        $currencies = Currency::all();

        $latest_income = Transaction::where('type', 'Income')
            ->orderBy('date', 'desc')
            ->take(20);

        if (!$all_data) {
            $latest_income->where('aid', $user->id);
        }

        $latest_income = $latest_income->get();

        $incomes = Transaction::where('type', 'Income');
        if (!$all_data) {
            $incomes->where('aid', $user->id);
        }
        $incomes = $incomes->get();

        $collection = collect($incomes);

        $cats = $collection->unique('category');

        $cat_data = [];

        $i = 0;

        foreach ($cats as $cat) {
            $cat_data[$i]['category'] = $cat->category;

            $val = Transaction::where('Type', 'Income')->where(
                'category',
                $cat->category
            );
            if (!$all_data) {
                $val->where('aid', $user->id);
            }
            $val = $val->sum('amount');
            $cat_data[$i]['value'] = $val;

            $i++;
        }

        $total_income_all_time = Transaction::totalAmount(
            'Income',
            '',
            'all',
            $all_data
        );

        $contacts = Contact::all()->keyBy('id');

        $items = Item::all()->keyBy('id');

        view('reports_income', [
            'currencies' => $currencies,
            'd' => $latest_income,
            'm_data' => $m_data,
            'cat_data' => $cat_data,
            'total_income_all_time' => $total_income_all_time,
            'all_data' => $all_data,
            'contacts' => $contacts,
            'items' => $items,
        ]);

        break;

    case 'expense':
        $all_data = has_access($user->roleid, 'transactions', 'all_data');

        $d = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->limit(20)
            ->order_by_desc('id')
            ->find_many();
        $ui->assign('d', $d);
        $a = ORM::for_table('sys_transactions')->sum('dr');
        if ($a == '') {
            $a = '0.00';
        }
        $ui->assign('a', $a);
        $m = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->where_gte('date', $first_day_month)
            ->where_lte('date', $mdate)
            ->sum('dr');
        if ($m == '') {
            $m = '0.00';
        }
        $ui->assign('m', $m);

        $w = ORM::for_table('sys_transactions')
            ->where_gte('date', $this_week_start)
            ->where_lte('date', $mdate)
            ->sum('dr');
        if ($w == '') {
            $w = '0.00';
        }
        $ui->assign('w', $w);

        $m3 = ORM::for_table('sys_transactions')
            ->where_gte('date', $before_30_days)
            ->where_lte('date', $mdate)
            ->sum('dr');
        if ($m3 == '') {
            $m3 = '0.00';
        }
        $ui->assign('m3', $m3);

        $ui->assign('mdate', $mdate);
        $array = [
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        ];
        $till = $month_n - 1;
        $gstring = '';

        $m_data = [];

        $i = 0;

        for ($m = 0; $m <= $till; $m++) {
            $mnth = $array[$m];
            $cal = ORM::for_table('sys_transactions')
                ->where_gte(
                    'date',
                    date('Y-m-d', strtotime("first day of $mnth"))
                )
                ->where_lte(
                    'date',
                    date('Y-m-d', strtotime("last day of $mnth"))
                );

            if (!$all_data) {
                $cal->where('aid', $user->id);
            }

            $cal = $cal->sum('dr');

            $gstring .= '["' . ib_lan_get_line($mnth) . '",' . $cal . '], ';

            $m_data[$i]['month'] = ib_lan_get_line($mnth);
            $m_data[$i]['value'] = $cal;

            $i++;
        }
        $gstring = rtrim($gstring, ',');

        $currencies = Currency::all();

        $latest_expenses = Transaction::where('type', 'Expense')
            ->orderBy('date', 'desc')
            ->take(20);

        if (!$all_data) {
            $latest_expenses->where('aid', $user->id);
        }

        $latest_expenses = $latest_expenses->get();

        $incomes = Transaction::where('type', 'Expense');

        if (!$all_data) {
            $incomes->where('aid', $user->id);
        }

        $incomes = $incomes->get();

        $collection = collect($incomes);

        $cats = $collection->unique('category');

        $cat_data = [];

        $i = 0;

        foreach ($cats as $cat) {
            $cat_data[$i]['category'] = $cat->category;

            $val = Transaction::where('Type', 'Expense')->where(
                'category',
                $cat->category
            );

            if (!$all_data) {
                $val->where('aid', $user->id);
            }

            $val = $val->sum('amount');

            $cat_data[$i]['value'] = $val;

            $i++;
        }

        $total_expense_all_time = Transaction::totalAmount(
            'Expense',
            '',
            'all',
            $all_data
        );

        $contacts = Contact::all()->keyBy('id');

        $items = Item::all()->keyBy('id');

        view('reports_expense', [
            'currencies' => $currencies,
            'd' => $d,
            'latest_expenses' => $latest_expenses,
            'm_data' => $m_data,
            'cat_data' => $cat_data,
            'total_expense_all_time' => $total_expense_all_time,
            'all_data' => $all_data,
            'contacts' => $contacts,
            'items' => $items,
        ]);

        break;

    case 'income-vs-expense':
        $all_data = has_access($user->roleid, 'transactions', 'all_data');

        $ai = ORM::for_table('sys_transactions');

        if (!$all_data) {
            $ai->where('aid', $user->id);
        }

        $ai = $ai->sum('cr');

        if ($ai == '') {
            $ai = '0.00';
        }
        $ui->assign('ai', $ai);

        $mi = ORM::for_table('sys_transactions')
            ->where_gte('date', $first_day_month)
            ->where_lte('date', $mdate);

        if (!$all_data) {
            $mi->where('aid', $user->id);
        }

        $mi = $mi->sum('cr');

        if ($mi == '') {
            $mi = '0.00';
        }
        $ui->assign('mi', $mi);

        $wi = ORM::for_table('sys_transactions')
            ->where_gte('date', $this_week_start)
            ->where_lte('date', $mdate);

        if (!$all_data) {
            $wi->where('aid', $user->id);
        }

        $wi = $wi->sum('cr');

        if ($wi == '') {
            $wi = '0.00';
        }
        $ui->assign('wi', $wi);

        $m3i = ORM::for_table('sys_transactions')
            ->where_gte('date', $before_30_days)
            ->where_lte('date', $mdate);

        if (!$all_data) {
            $m3i->where('aid', $user->id);
        }

        $m3i = $m3i->sum('cr');

        if ($m3i == '') {
            $m3i = '0.00';
        }

        $ui->assign('m3i', $m3i);

        $ae = ORM::for_table('sys_transactions');

        if (!$all_data) {
            $ae->where('aid', $user->id);
        }

        $ae = $ae->sum('dr');

        if ($ae == '') {
            $ae = '0.00';
        }
        $ui->assign('ae', $ae);

        $me = ORM::for_table('sys_transactions')
            ->where_gte('date', $first_day_month)
            ->where_lte('date', $mdate);

        if (!$all_data) {
            $me->where('aid', $user->id);
        }

        $me = $me->sum('dr');

        if ($me == '') {
            $me = '0.00';
        }
        $ui->assign('me', $me);

        $ui->assign('mdate', $mdate);
        $aime = $ai - $ae;
        $ui->assign('aime', $aime);
        $mime = $mi - $me;
        $ui->assign('mime', $mime);
        $array = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ];
        $till = $month_n - 1;
        $gstring = '';
        $egstring = '';
        for ($m = 0; $m <= $till; $m++) {
            $mnth = $array[$m];
            $cal = ORM::for_table('sys_transactions')
                ->where_gte(
                    'date',
                    date('Y-m-d', strtotime("first day of $mnth"))
                )
                ->where_lte(
                    'date',
                    date('Y-m-d', strtotime("last day of $mnth"))
                );

            if (!$all_data) {
                $cal->where('aid', $user->id);
            }

            $cal = $cal->sum('dr');

            if ($cal == '') {
                $cal = '0';
            }
            $egstring .= '["' . $m . '",' . $cal . '], ';
            $cal = ORM::for_table('sys_transactions')
                ->where_gte(
                    'date',
                    date('Y-m-d', strtotime("first day of $mnth"))
                )
                ->where_lte(
                    'date',
                    date('Y-m-d', strtotime("last day of $mnth"))
                );

            if (!$all_data) {
                $cal->where('aid', $user->id);
            }

            $cal = $cal->sum('cr');

            if ($cal == '') {
                $cal = '0';
            }
            $gstring .= '["' . $m . '",' . $cal . '], ';
        }
        $gstring = rtrim($gstring, ',');

        view('reports-income-vs-expense');

        break;

    case 'categories':
        $d = ORM::for_table('sys_cats')->find_many();
        $ui->assign('d', $d);

        $ui->assign('mdate', $mdate);
        $ui->assign('tdate', $tdate);

        view('reports-categories');

        break;

    case 'category-view':
        $fdate = _post('fdate');
        $tdate = _post('tdate');
        $cat = _post('cat');

        $d = ORM::for_table('sys_transactions');
        $d->where('category', $cat);

        $d->where_gte('date', $fdate);
        $d->where_lte('date', $tdate);
        $d->order_by_desc('id');
        $x = $d->find_many();

        $ui->assign('d', $x);
        $ui->assign('fdate', $fdate);
        $ui->assign('tdate', $tdate);

        view('report-common');
        break;

    case 'payees':
        $d = ORM::for_table('sys_payee')->find_many();
        $ui->assign('d', $d);

        $ui->assign('mdate', $mdate);
        $ui->assign('tdate', $tdate);

        view('reports-payees');

        break;

    case 'payees-view':
        $fdate = _post('fdate');
        $tdate = _post('tdate');
        $payee = _post('payee');

        $d = ORM::for_table('sys_transactions');
        $d->where('payee', $payee);

        $d->where_gte('date', $fdate);
        $d->where_lte('date', $tdate);
        $d->order_by_desc('id');
        $x = $d->find_many();

        $ui->assign('d', $x);
        $ui->assign('fdate', $fdate);
        $ui->assign('tdate', $tdate);

        view('report-common');
        break;

    case 'payers':
        $d = ORM::for_table('sys_payers')->find_many();
        $ui->assign('d', $d);

        $ui->assign('mdate', $mdate);
        $ui->assign('tdate', $tdate);

        view('reports-payers');

        break;

    case 'payer-view':
        $fdate = _post('fdate');
        $tdate = _post('tdate');
        $payer = _post('payer');

        $d = ORM::for_table('sys_transactions');
        $d->where('payer', $payer);

        $d->where_gte('date', $fdate);
        $d->where_lte('date', $tdate);
        $d->order_by_desc('id');
        $x = $d->find_many();

        $ui->assign('d', $x);
        $ui->assign('fdate', $fdate);
        $ui->assign('tdate', $tdate);

        view('report-common');
        break;

    case 'cats':

    case 'sales':
        $all_data = has_access($user->roleid, 'transactions', 'all_data');

        $tab = route(2,'by-item');

        $ui->assign('tab', $tab);

        switch ($tab)
        {
            case 'by-item':
                if ($all_data) {
                    $invoice_items = ORM::for_table('sys_invoiceitems')->find_array();
                } else {
                    $invoice_ids = Invoice::where('aid', $user->id)
                        ->select('id')
                        ->get()
                        ->pluck('id')
                        ->toArray();
                    $invoice_items = InvoiceItem::whereIn('id', $invoice_ids)->get();
                }

                $ui->assign('invoice_items', $invoice_items);

                $mdate = date('Y-m-d');
                $ui->assign('mdate', $mdate);
                view('reports.sales.by-item',[
                    'tab' => $tab
                ]);
                break;

            case 'by-staffs':

                $selected_staff_id = route(3,0);
                $staffs = User::all();

                $total_items_sold = 0;
                $total_amount = 0;
                $items_sold = [];

                $total_items_sold = new InvoiceItem();
                $total_amount = new InvoiceItem();
                $items_sold = new InvoiceItem();

                if($selected_staff_id !== '' && $selected_staff_id !== '0') {

                    $total_items_sold = $total_items_sold->where('staff_id', $selected_staff_id);
                    $total_amount = $total_amount->where('staff_id', $selected_staff_id);
                    $items_sold = $items_sold->where('staff_id', $selected_staff_id);

                }



                $reportRange = route(4, false);

                if(!empty($reportRange[1]) && $reportRange !== '' && $reportRange !== '0') {
                    $reportRange = explode('-', $reportRange);
                    $startDate = $reportRange[0];
                    $startDate = str_replace('*','-',$startDate);
                    $endDate = $reportRange[1];
                    $endDate = str_replace('*','-',$endDate);
                    $total_items_sold = $total_items_sold->whereBetween('created_at', [$startDate, $endDate]);
                    $total_amount = $total_amount->whereBetween('created_at', [$startDate, $endDate]);
                    $items_sold = $items_sold->whereBetween('created_at', [$startDate, $endDate]);
                }

                $total_items_sold = $total_items_sold->count();
                $total_amount = $total_amount->sum('amount');
                $items_sold = $items_sold->get();


                view('reports.sales.by-staffs',[
                    'tab' => $tab,
                    'staffs' => $staffs,
                    'selected_staff_id' => $selected_staff_id,
                    'total_items_sold' => $total_items_sold,
                    'total_amount' => $total_amount,
                    'items_sold' => $items_sold,
                ]);
                break;
        }



        break;

    case 'sales_invoice_calendar':
        $all_data = has_access($user->roleid, 'transactions', 'all_data');

        header('Content-Type: application/json');

        $start = _get('start') . ' 00:00:00';
        $end = _get('end') . ' 23:59:00';

        $calendar_data = ORM::for_table('sys_invoices')
            ->where_gte('duedate', $start)
            ->where_lte('duedate', $end)
            ->select('id')
            ->select('account')
            ->select('duedate')
            ->select('invoicenum')
            ->select('cn')
            ->select('total')
            ->select('id', 'eventid')
            ->select('status');

        if (!$all_data) {
            $calendar_data->where('aid', $user->id);
        }

        $calendar_data = $calendar_data->find_array();

        $events = [];

        $i = 0;
        foreach ($calendar_data as $event) {
            $inv_n = $event['cn'] == '' ? $event['id'] : $event['cn'];
            $events[$i]['eventid'] = $event['id'];
            $events[$i]['title'] =
                '#' .
                $event['invoicenum'] .
                $inv_n .
                ' [ Amount: ' .
                $event['total'] .
                ' ]';
            $events[$i]['start'] = $event['duedate'];

            $i++;
        }

        echo json_encode($events);

        break;

    case 'invoices':
        $all_data = has_access($user->roleid, 'sales', 'all_data');

        $cid = route(2);
        if ($cid == '' || $cid == '0') {
            $ui->assign('p_cid', '');
        } else {
            $ui->assign('p_cid', $cid);
        }

        $logo_mime = 'image/png';

        $c = ORM::for_table('crm_accounts')
            ->select('id')
            ->select('account')
            ->select('company')
            ->select('email')
            ->order_by_desc('id')
            ->find_many();
        $ui->assign('c', $c);

        $a = ORM::for_table('sys_accounts')->find_array();
        $ui->assign('a', $a);

        view('reports_invoices', []);

        break;

    case 'invoices_summary':
        $all_data = has_access($user->roleid, 'sales', 'all_data');

        $paginator = [];

        $mode_css = '';
        $mode_js = '';
        $view_type = 'default';

        $view_type = 'filter';

        $f = ORM::for_table('sys_invoices');

        if (route(3) != '') {
            $s_f = route(3);

            if ($s_f == 'paid') {
                $f->where('status', 'Paid');
            } elseif ($s_f == 'unpaid') {
                $f->where('status', 'Unpaid');
            } elseif ($s_f == 'partially_paid') {
                $f->where('status', 'Partially Paid');
            } elseif ($s_f == 'cancelled') {
                $f->where('status', 'Cancelled');
            } else {
            }
        }

        if (!$all_data) {
            $f->where('aid', $user->id);
        }

        $d = $f
            ->order_by_desc('id')
            ->limit(50)
            ->find_many();

        $paginator['contents'] = '';

        $ui->assign('view_type', $view_type);

        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);

        $last_12_months = lastTwelveMonths();

        $m = [];

        foreach ($last_12_months as $month) {
            //  echo date('Y-m-d', strtotime($month)).' ';

            $first_day = date('Y-m-d', strtotime($month));
            $last_day = date('Y-m-t', strtotime($month));

            $m['display'][] = $month;
            $t = Invoice::where('status', 'Paid')->whereBetween('datepaid', [
                $first_day,
                $last_day,
            ]);

            if (!$all_data) {
                $t = $t->where('aid', $user->id);
            }

            $m['data'][] = $t->sum('total');
        }

        $total_invoice = $all_data ? Invoice::count() : Invoice::where('aid', $user->id)->count();

        $total_invoice_items = InvoiceItem::sum('qty');

//        $total_invoice_amount = $all_data ? Invoice::sum('total') : Invoice::where('aid', $user->id)->sum(
//            'total'
//        );

        $total_invoice_amount = 0;

        $invoices_all = new Invoice();
        if (!$all_data) {
            $invoices_all->where('aid', $user->id);
        }

        $invoices_all = $invoices_all->get();

        foreach ($invoices_all as $invoice) {
            $total_invoice_amount += ($invoice->total*$invoice->currency_rate);
        }

        view('reports_invoices_summary', [
            'm' => $m,
            'total_invoice_items' => $total_invoice_items,
            'total_invoice_amount' => $total_invoice_amount,
            'total_invoice' => $total_invoice,
            'all_data' => $all_data,
        ]);

        break;

    case 'invoices_expense':
        $all_data = has_access($user->roleid, 'transactions', 'all_data');

        $last_12_months = lastTwelveMonths();

        $m = [];

        foreach ($last_12_months as $month) {
            $first_day = date('Y-m-d', strtotime($month));
            $last_day = date('Y-m-t', strtotime($month));

            $m['display'][] = $month;

            $invoice_total = Invoice::whereBetween('date', [
                $first_day,
                $last_day,
            ]);

            if ($all_data) {
                $invoice_total = $invoice_total->where('aid', $user->id);
            }

            $m['invoice_total'][] = $invoice_total->sum('total');

            $invoice_paid = Invoice::where(
                'status',
                'Paid'
            )->whereBetween('datepaid', [$first_day, $last_day]);

            if (!$all_data) {
                $invoice_paid->where('aid', $user->id);
            }

            $m['invoice_paid'][] = $invoice_paid->sum('total');

            $expense_total = Transaction::where(
                'type',
                'Expense'
            )->whereBetween('date', [$first_day, $last_day]);

            if (!$all_data) {
                $expense_total->where('aid', $user->id);
            }

            $m['expense_total'][] = $expense_total->sum('amount');

            $expense_type_1 = Transaction::where('type', 'Expense')
                ->where('sub_type', $config['expense_type_1'])
                ->whereBetween('date', [$first_day, $last_day]);

            if (!$all_data) {
                $expense_type_1 = $expense_type_1->where('aid', $user->id);
            }

            $m['expense_type_1'][] = $expense_type_1->sum('amount');

            $expense_type_2 = Transaction::where('type', 'Expense')
                ->where('sub_type', $config['expense_type_2'])
                ->whereBetween('date', [$first_day, $last_day]);

            if (!$all_data) {
                $expense_type_2 = $expense_type_2->where('aid', $user->id);
            }

            $m['expense_type_2'][] = $expense_type_2->sum('amount');
        }

        view('reports_invoices_expense', [
            'm' => $m,
        ]);

        break;

    case 'json_invoices':
        $columns = [];
        $columns[] = 'id';
        $columns[] = 'account';
        $columns[] = 'total';
        $columns[] = 'credit';
        $columns[] = 'due';
        $columns[] = 'date';
        $columns[] = 'manage';
        $order_by = $data['order'];
        $o_c_id = $order_by[0]['column'];
        $o_type = $order_by[0]['dir'];
        $a_order_by = $columns[$o_c_id];

        $d = ORM::for_table('sys_invoices');

        $cid = _post('cid');
        if ($cid != '') {
            $d->where('userid', $cid);
        }

        $reportrange = _post('reportrange');
        if ($reportrange != '') {
            $reportrange = explode('-', $reportrange);
            $from_date = trim($reportrange[0]);
            $to_date = trim($reportrange[1]);
            $d->where_gte('date', $from_date);
            $d->where_lte('date', $to_date);
        }

        if (!has_access($user->roleid, 'sales', 'all_data')) {
            $d->where('aid', $user->id);
        }

        $x = $d->find_array();
        $iTotalRecords = $d->count();

        $iDisplayLength = (int) $_REQUEST['length'];
        $iDisplayLength =
            $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = (int) $_REQUEST['start'];
        $sEcho = (int) $_REQUEST['draw'];
        $records = [];
        $records["data"] = [];
        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
        if ($o_type == 'desc') {
            $d->order_by_desc($a_order_by);
        } else {
            $d->order_by_asc($a_order_by);
        }

        $d->limit($end);
        $d->offset($iDisplayStart);
        $x = $d->find_array();
        $i = $iDisplayStart;

        foreach ($x as $xs) {
            $invoice_id = $xs['invoicenum'];
            if (!empty($xs['cn'])) {
                $invoice_id .= $xs['cn'];
            } else {
                $invoice_id .= $xs['id'];
            }
            ray($xs['id']);
            $due = $xs['total'] - $xs['credit'];
            $records["data"][] = [
                '<a href="' .
                U .
                'invoices/view/' .
                $xs['id'] .
                '">' .
                $invoice_id .
                '</a>',
                htmlentities($xs['account']),
                formatCurrency($xs['total'],$xs['currency_iso_code']),
                formatCurrency($xs['credit'],$xs['currency_iso_code']),
                formatCurrency($due,$xs['currency_iso_code']),
                $xs['date'],
                '<a href="' .
                U .
                'invoices/view/' .
                $xs['id'] .
                '" class="btn btn-primary btn-xs"><i class="fal fa-file-alt"></i></a>',
            ];
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        api_response($records);

        break;

    case 'purchases':
        $all_data = has_access($user->roleid, 'sales', 'all_data');

        $cid = route(2);
        if ($cid == '' || $cid == '0') {
            $ui->assign('p_cid', '');
        } else {
            $ui->assign('p_cid', $cid);
        }

        $c = ORM::for_table('crm_accounts')
            ->select('id')
            ->select('account')
            ->select('company')
            ->select('email')
            ->order_by_desc('id')
            ->find_many();
        $ui->assign('c', $c);

        $a = ORM::for_table('sys_accounts')->find_array();
        $ui->assign('a', $a);

        view('reports_purchases');

        break;

    case 'json_purchases':
        $columns = [];
        $columns[] = 'id';
        $columns[] = 'account';
        $columns[] = 'total';
        $columns[] = 'credit';
        $columns[] = 'due';
        $columns[] = 'date';
        $columns[] = 'manage';
        $order_by = $data['order'];
        $o_c_id = $order_by[0]['column'];
        $o_type = $order_by[0]['dir'];
        $a_order_by = $columns[$o_c_id];

        $d = ORM::for_table('sys_purchases');

        $cid = _post('cid');
        if ($cid != '') {
            $d->where('userid', $cid);
        }

        $reportrange = _post('reportrange');
        if ($reportrange != '') {
            $reportrange = explode('-', $reportrange);
            $from_date = trim($reportrange[0]);
            $to_date = trim($reportrange[1]);
            $d->where_gte('date', $from_date);
            $d->where_lte('date', $to_date);
        }

        if (!has_access($user->roleid, 'sales', 'all_data')) {
            $d->where('aid', $user->id);
        }

        $x = $d->find_array();
        $iTotalRecords = $d->count();

        $iDisplayLength = (int) $_REQUEST['length'];
        $iDisplayLength =
            $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = (int) $_REQUEST['start'];
        $sEcho = (int) $_REQUEST['draw'];
        $records = [];
        $records["data"] = [];
        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
        if ($o_type == 'desc') {
            $d->order_by_desc($a_order_by);
        } else {
            $d->order_by_asc($a_order_by);
        }

        $d->limit($end);
        $d->offset($iDisplayStart);
        $x = $d->find_array();
        $i = $iDisplayStart;
        foreach ($x as $xs) {
            $due = $xs['total'] - $xs['credit'];
            $records["data"][] = [
                '<a href="' .
                U .
                'purchases/view/' .
                $xs['id'] .
                '">' .
                $xs['id'] .
                '</a>',
                htmlentities($xs['account']),
                $xs['total'],
                $xs['credit'],
                $due,
                $xs['date'],
                '<a href="' .
                U .
                'purchases/view/' .
                $xs['id'] .
                '" class="btn btn-primary btn-xs"><i class="fal fa-file-alt"></i></a>',
            ];
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        api_response($records);

        break;

    case 'purchases_summary':
        $all_data = has_access($user->roleid, 'sales', 'all_data');

        $paginator = [];

        $view_type = 'default';

        $f = ORM::for_table('sys_purchases');

        if (route(3) != '') {
            $s_f = route(3);

            if ($s_f == 'paid') {
                $f->where('status', 'Paid');
            } elseif ($s_f == 'unpaid') {
                $f->where('status', 'Unpaid');
            } elseif ($s_f == 'partially_paid') {
                $f->where('status', 'Partially Paid');
            } elseif ($s_f == 'cancelled') {
                $f->where('status', 'Cancelled');
            } else {
            }
        }

        if (!$all_data) {
            $f->where('aid', $user->id);
        }

        $d = $f
            ->order_by_desc('id')
            ->limit(50)
            ->find_many();

        $paginator['contents'] = '';

        $ui->assign('view_type', $view_type);

        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);

        $last_12_months = lastTwelveMonths();

        $m = [];

        foreach ($last_12_months as $month) {
            $first_day = date('Y-m-d', strtotime($month));
            $last_day = date('Y-m-t', strtotime($month));

            $m['display'][] = $month;
            $t = Invoice::where('status', 'Paid')->whereBetween('datepaid', [
                $first_day,
                $last_day,
            ]);

            if (!$all_data) {
                $t = $t->where('aid', $user->id);
            }

            $m['data'][] = $t->sum('total');
        }

        $total_invoice = $all_data ? Invoice::count() : Invoice::where('aid', $user->id)->count();

        $total_invoice_items = InvoiceItem::sum('qty');

//        $total_invoice_amount = $all_data ? Invoice::sum('total') : Invoice::where('aid', $user->id)->sum(
//            'total'
//        );

//        $total_invoice_amount = ib_money_format($total_invoice_amount, $config);

        $total_invoice_amount = 0;

        $invoices_all = new Purchase();
        if (!$all_data) {
            $invoices_all->where('aid', $user->id);
        }

        $invoices_all = $invoices_all->get();

        foreach ($invoices_all as $invoice) {
            $total_invoice_amount += ($invoice->total*$invoice->currency_rate);
        }

        view('reports_purchases_summary', [
            'm' => $m,
            'total_invoice_items' => $total_invoice_items,
            'total_invoice_amount' => $total_invoice_amount,
            'total_invoice' => $total_invoice,
            'all_data' => $all_data,
        ]);

        break;

    case 'zatca-submit-report':
        $all_data = has_access($user->roleid, 'sales', 'all_data');
        $transactions_all_data = has_access($user->roleid, 'transactions', 'all_data');
        [$fdate, $tdate] = zatca_report_date_range($data);

        $has_zatca_columns =
            class_exists('Zatca') &&
            is_callable(['Zatca', 'hasInvoiceColumn']) &&
            Zatca::hasInvoiceColumn('zatca_status') &&
            Zatca::hasInvoiceColumn('zatca_last_submit_at') &&
            Zatca::hasInvoiceColumn('zatca_last_response');

        $invoices_query = ORM::for_table('sys_invoices')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate)
            ->order_by_desc('id');

        if ($has_zatca_columns) {
            $invoices_query->where_raw(
                "(LOWER(COALESCE(zatca_status,'')) = ? OR COALESCE(zatca_last_submit_at,'') <> '' OR COALESCE(zatca_last_response,'') <> '')",
                ['submitted']
            );
        } else {
            $invoices_query->where_raw('1 = 0');
        }

        if (!$all_data) {
            $invoices_query->where('aid', $user->id);
        }

        $invoices = $invoices_query->find_array();

        $contact_ids = [];
        foreach ($invoices as $inv) {
            if (!empty($inv['userid'])) {
                $contact_ids[] = (int) $inv['userid'];
            }
        }

        $contacts_map = [];
        if (!empty($contact_ids)) {
            $contacts = ORM::for_table('crm_accounts')->where_in('id', array_unique($contact_ids))->find_array();
            foreach ($contacts as $contact) {
                $contacts_map[(int) $contact['id']] = $contact;
            }
        }

        $report_rows = [];
        foreach ($invoices as $invoice) {
            [$reporting_status, $validation_status] = zatca_response_statuses($invoice);

            $contact = $contacts_map[(int) ($invoice['userid'] ?? 0)] ?? [];
            $customer_vat = $contact['tax_number'] ?? '';

            $is_return_row = (string) ($invoice['type'] ?? '') === 'Credit Note' && (int) ($invoice['parent_id'] ?? 0) > 0;
            $is_cancelled_row = strcasecmp((string) ($invoice['status'] ?? ''), 'Cancelled') === 0;
            $sign = ($is_return_row || $is_cancelled_row) ? -1 : 1;

            $report_rows[] = [
                'source' => $is_return_row ? 'Return Invoice' : 'Invoice',
                'id' => $invoice['id'],
                'invoice_number' => (string) ($invoice['invoicenum'] ?? '') . (!empty($invoice['cn']) ? (string) $invoice['cn'] : (string) ($invoice['id'] ?? '')),
                'date' => $invoice['date'] ?? '',
                'customer' => $invoice['account'] ?? '',
                'customer_vat' => $customer_vat,
                'status' => (string) ($invoice['status'] ?? ''),
                'subtotal' => $sign * abs((float) ($invoice['subtotal'] ?? 0)),
                'tax' => $sign * abs((float) ($invoice['tax'] ?? 0)),
                'total' => $sign * abs((float) ($invoice['total'] ?? 0)),
                'zatca_status' => strtoupper((string) ($invoice['zatca_status'] ?? '')),
                'reporting_status' => $reporting_status,
                'validation_status' => $validation_status,
                'submitted_at' => $invoice['zatca_last_submit_at'] ?? '',
            ];
        }

        $expenses_query = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate)
            ->order_by_desc('id');

        if (!$transactions_all_data) {
            $expenses_query->where('aid', $user->id);
        }

        $expenses = $expenses_query->find_array();

        foreach ($expenses as $expense) {
            $gross = (float) ($expense['amount'] ?? 0);
            $tax = (float) ($expense['tax'] ?? 0);
            $subtotal = round($gross - $tax, 2);
            if ($subtotal < 0) {
                $subtotal = 0.0;
            }

            $report_rows[] = [
                'source' => 'Expense',
                'id' => $expense['id'],
                'invoice_number' => !empty($expense['ref']) ? (string) $expense['ref'] : 'EXP-' . $expense['id'],
                'date' => $expense['date'] ?? '',
                'customer' => (string) ($expense['payee'] ?? $expense['category'] ?? $expense['account'] ?? ''),
                'customer_vat' => '',
                'status' => 'N/A',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $gross,
                'zatca_status' => 'N/A',
                'reporting_status' => 'Not Applicable',
                'validation_status' => 'Not Applicable',
                'submitted_at' => $expense['date'] ?? '',
            ];
        }

        usort($report_rows, function ($a, $b) {
            $d1 = (string) ($a['date'] ?? '');
            $d2 = (string) ($b['date'] ?? '');
            if ($d1 === $d2) {
                return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
            }

            return strcmp($d2, $d1);
        });

        // VAT return summary covers ALL invoices in the period (VAT is due whether
        // or not each invoice has been submitted to ZATCA yet), unlike the audit
        // table above which only lists ZATCA-submitted invoices.
        $vat_period_invoices_query = ORM::for_table('sys_invoices')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate);

        if (!$all_data) {
            $vat_period_invoices_query->where('aid', $user->id);
        }

        $vat_summary = zatca_vat_return_summary($vat_period_invoices_query->find_array(), $expenses);

        view('reports_zatca_submit', [
            'fdate' => $fdate,
            'tdate' => $tdate,
            'rows' => $report_rows,
            'vat_summary' => $vat_summary,
        ]);

        break;

    case 'export-zatca-vat-return':
        $all_data = has_access($user->roleid, 'sales', 'all_data');
        $transactions_all_data = has_access($user->roleid, 'transactions', 'all_data');
        [$fdate, $tdate] = zatca_report_date_range($data);

        $vat_invoices_query = ORM::for_table('sys_invoices')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate);

        if (!$all_data) {
            $vat_invoices_query->where('aid', $user->id);
        }

        $vat_invoices = $vat_invoices_query->find_array();

        $vat_expenses_query = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate);

        if (!$transactions_all_data) {
            $vat_expenses_query->where('aid', $user->id);
        }

        $vat_expenses = $vat_expenses_query->find_array();

        $vs = zatca_vat_return_summary($vat_invoices, $vat_expenses);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $summary_sheet = $spreadsheet->getActiveSheet();
        $summary_sheet->setTitle('VAT Return Summary');
        $summary_sheet->fromArray(
            [
                ['ZATCA VAT Return Summary', '', ''],
                ['Period', $fdate . ' to ' . $tdate, ''],
                ['', '', ''],
                ['Sales (Outputs)', 'Amount (Net)', 'VAT'],
                ['Standard-rated sales', $vs['standard_sales_net'], $vs['standard_sales_vat']],
                ['Less: Sales returns / credit notes', -$vs['returns_net'], -$vs['returns_vat']],
                ['Less: Cancelled invoices', -$vs['cancelled_net'], -$vs['cancelled_vat']],
                ['Zero-rated / exempt sales', $vs['zero_exempt_sales_net'], 0],
                ['Total Output VAT', '', $vs['total_output_vat']],
                ['', '', ''],
                ['Purchases (Inputs)', 'Amount (Net)', 'VAT'],
                ['Standard-rated purchases / expenses', $vs['standard_purchases_net'], $vs['standard_purchases_vat']],
                ['Zero-rated / exempt purchases', $vs['zero_purchases_net'], 0],
                ['Total Input VAT', '', $vs['total_input_vat']],
                ['', '', ''],
                ['Net VAT Due / (Refundable)', '', $vs['net_vat_due']],
                ['', '', ''],
                ['Note: figures are aggregated from invoices/expenses recorded in this system for the period above.', '', ''],
                ['File the return via the ZATCA portal (ERAD) using these totals; this workbook is not an upload file accepted by ZATCA.', '', ''],
            ],
            null,
            'A1'
        );
        $summary_sheet->getColumnDimension('A')->setWidth(48);
        $summary_sheet->getColumnDimension('B')->setWidth(18);
        $summary_sheet->getColumnDimension('C')->setWidth(18);

        $detail_sheet = $spreadsheet->createSheet();
        $detail_sheet->setTitle('Invoice & Expense Detail');
        $detail_header = ['Type', 'ID', 'Reference', 'Date', 'Customer/Payee', 'Status', 'Net', 'VAT', 'Gross'];
        $detail_sheet->fromArray($detail_header, null, 'A1');

        $detail_rows = [];
        foreach ($vat_invoices as $invoice) {
            $is_return_row = (string) ($invoice['type'] ?? '') === 'Credit Note' && (int) ($invoice['parent_id'] ?? 0) > 0;
            $is_cancelled_row = strcasecmp((string) ($invoice['status'] ?? ''), 'Cancelled') === 0;
            $sign = ($is_return_row || $is_cancelled_row) ? -1 : 1;

            $detail_rows[] = [
                $is_return_row ? 'Return Invoice' : 'Invoice',
                $invoice['id'] ?? '',
                (string) ($invoice['invoicenum'] ?? '') . (!empty($invoice['cn']) ? (string) $invoice['cn'] : (string) ($invoice['id'] ?? '')),
                $invoice['date'] ?? '',
                $invoice['account'] ?? '',
                (string) ($invoice['status'] ?? ''),
                $sign * abs((float) ($invoice['subtotal'] ?? 0)),
                $sign * abs((float) ($invoice['tax'] ?? 0)),
                $sign * abs((float) ($invoice['total'] ?? 0)),
            ];
        }

        foreach ($vat_expenses as $expense) {
            $gross = (float) ($expense['amount'] ?? 0);
            $tax = (float) ($expense['tax'] ?? 0);
            $net = round($gross - $tax, 2);
            if ($net < 0) {
                $net = 0.0;
            }

            $detail_rows[] = [
                'Expense',
                $expense['id'] ?? '',
                !empty($expense['ref']) ? (string) $expense['ref'] : 'EXP-' . $expense['id'],
                $expense['date'] ?? '',
                (string) ($expense['payee'] ?? $expense['category'] ?? $expense['account'] ?? ''),
                'N/A',
                $net,
                $tax,
                $gross,
            ];
        }

        usort($detail_rows, function ($a, $b) {
            return strcmp((string) $b[3], (string) $a[3]);
        });

        $detail_sheet->fromArray($detail_rows, null, 'A2');

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="zatca-vat-return-' . $fdate . '-to-' . $tdate . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        break;

    case 'export-zatca-submit-report':
        $all_data = has_access($user->roleid, 'sales', 'all_data');
        $transactions_all_data = has_access($user->roleid, 'transactions', 'all_data');
        [$fdate, $tdate] = zatca_report_date_range($data);

        $has_zatca_columns =
            class_exists('Zatca') &&
            is_callable(['Zatca', 'hasInvoiceColumn']) &&
            Zatca::hasInvoiceColumn('zatca_status') &&
            Zatca::hasInvoiceColumn('zatca_last_submit_at') &&
            Zatca::hasInvoiceColumn('zatca_last_response');

        $invoices_query = ORM::for_table('sys_invoices')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate)
            ->order_by_desc('id');

        if ($has_zatca_columns) {
            $invoices_query->where_raw(
                "(LOWER(COALESCE(zatca_status,'')) = ? OR COALESCE(zatca_last_submit_at,'') <> '' OR COALESCE(zatca_last_response,'') <> '')",
                ['submitted']
            );
        } else {
            $invoices_query->where_raw('1 = 0');
        }

        if (!$all_data) {
            $invoices_query->where('aid', $user->id);
        }

        $invoices = $invoices_query->find_array();

        $contact_ids = [];
        foreach ($invoices as $inv) {
            if (!empty($inv['userid'])) {
                $contact_ids[] = (int) $inv['userid'];
            }
        }

        $contacts_map = [];
        if (!empty($contact_ids)) {
            $contacts = ORM::for_table('crm_accounts')->where_in('id', array_unique($contact_ids))->find_array();
            foreach ($contacts as $contact) {
                $contacts_map[(int) $contact['id']] = $contact;
            }
        }

        $excel_rows = [];
        foreach ($invoices as $invoice) {
            [$reporting_status, $validation_status] = zatca_response_statuses($invoice);

            $contact = $contacts_map[(int) ($invoice['userid'] ?? 0)] ?? [];
            $customer_vat = $contact['tax_number'] ?? '';

            $is_return_row = (string) ($invoice['type'] ?? '') === 'Credit Note' && (int) ($invoice['parent_id'] ?? 0) > 0;
            $is_cancelled_row = strcasecmp((string) ($invoice['status'] ?? ''), 'Cancelled') === 0;
            $sign = ($is_return_row || $is_cancelled_row) ? -1 : 1;

            $excel_rows[] = [
                $is_return_row ? 'Return Invoice' : 'Invoice',
                $invoice['id'] ?? '',
                (string) ($invoice['invoicenum'] ?? '') . (!empty($invoice['cn']) ? (string) $invoice['cn'] : (string) ($invoice['id'] ?? '')),
                $invoice['date'] ?? '',
                $invoice['account'] ?? '',
                $customer_vat,
                (string) ($invoice['status'] ?? ''),
                $sign * abs((float) ($invoice['subtotal'] ?? 0)),
                $sign * abs((float) ($invoice['tax'] ?? 0)),
                $sign * abs((float) ($invoice['total'] ?? 0)),
                strtoupper((string) ($invoice['zatca_status'] ?? '')),
                $reporting_status,
                $validation_status,
                $invoice['zatca_last_submit_at'] ?? '',
            ];
        }

        $expenses_query = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate)
            ->order_by_desc('id');

        if (!$transactions_all_data) {
            $expenses_query->where('aid', $user->id);
        }

        $expenses = $expenses_query->find_array();

        foreach ($expenses as $expense) {
            $gross = (float) ($expense['amount'] ?? 0);
            $tax = (float) ($expense['tax'] ?? 0);
            $subtotal = round($gross - $tax, 2);
            if ($subtotal < 0) {
                $subtotal = 0.0;
            }

            $excel_rows[] = [
                'Expense',
                $expense['id'] ?? '',
                !empty($expense['ref']) ? (string) $expense['ref'] : 'EXP-' . $expense['id'],
                $expense['date'] ?? '',
                (string) ($expense['payee'] ?? $expense['category'] ?? $expense['account'] ?? ''),
                '',
                'N/A',
                $subtotal,
                $tax,
                $gross,
                'N/A',
                'Not Applicable',
                'Not Applicable',
                $expense['date'] ?? '',
            ];
        }

        exportExcel(
            'zatca-submit-report-' . $fdate . '-to-' . $tdate . '.xlsx',
            [
                'Type',
                'ID',
                'Invoice Number',
                'Date',
                'Customer',
                'Customer VAT',
                'Status',
                'Sub Total',
                'Tax',
                'Total',
                'ZATCA Status',
                'Reporting Status',
                'Validation Status',
                'Submitted At',
            ],
            $excel_rows
        );

        break;

    case 'zatca-returns-report':
        $sales_all_data = has_access($user->roleid, 'sales', 'all_data');
        $transactions_all_data = has_access($user->roleid, 'transactions', 'all_data');
        [$fdate, $tdate] = zatca_report_date_range($data);

        $only_taxable = zatca_bool_flag($data['only_taxable'] ?? null, false);
        $only_vat_suppliers = zatca_bool_flag($data['only_vat_suppliers'] ?? null, false);
        $include_expenses = zatca_bool_flag($data['include_expenses'] ?? null, true);
        $only_returns = zatca_bool_flag($data['only_returns'] ?? null, false);

        $invoices_query = ORM::for_table('sys_invoices')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate);

        if (class_exists('Zatca') && method_exists('Zatca', 'hasInvoiceColumn')) {
            Zatca::hasInvoiceColumn('zatca_status');
            Zatca::hasInvoiceColumn('zatca_last_submit_at');
            Zatca::hasInvoiceColumn('zatca_last_response');
            $invoices_query->where_raw(
                "(LOWER(COALESCE(zatca_status,'')) = ? OR COALESCE(zatca_last_submit_at,'') <> '' OR COALESCE(zatca_last_response,'') <> '')",
                ['submitted']
            );
        }

        if ($only_taxable) {
            $invoices_query->where_gt('tax', 0);
        }

        if ($only_returns) {
            $invoices_query->where_raw("COALESCE(is_credit_invoice,0) = ?", [1]);
        }

        if (!$sales_all_data) {
            $invoices_query->where('aid', $user->id);
        }

        $invoices = $invoices_query
            ->order_by_desc('date')
            ->order_by_desc('id')
            ->find_array();

        $contact_ids = [];
        foreach ($invoices as $inv) {
            if (!empty($inv['userid'])) {
                $contact_ids[] = (int) $inv['userid'];
            }
        }

        $contacts_map = [];
        if (!empty($contact_ids)) {
            $contacts = ORM::for_table('crm_accounts')->where_in('id', array_unique($contact_ids))->find_array();
            foreach ($contacts as $contact) {
                $contacts_map[(int) $contact['id']] = $contact;
            }
        }

        $rows = [];

        foreach ($invoices as $invoice) {
            $contact = $contacts_map[(int) ($invoice['userid'] ?? 0)] ?? [];
            $supplier_vat = trim((string) ($contact['tax_number'] ?? ''));
            $supplier_unified = trim((string) ($contact['entity_number'] ?? ''));

            if ($only_vat_suppliers && $supplier_vat === '') {
                continue;
            }

            $is_return_invoice =
                ((int) ($invoice['is_credit_invoice'] ?? 0) === 1) ||
                (strtolower(trim((string) ($invoice['type'] ?? ''))) === 'credit note');

            if ($only_returns && !$is_return_invoice) {
                continue;
            }

            [$reporting_status, $validation_status] = zatca_response_statuses($invoice);

            $net = (float) ($invoice['subtotal'] ?? 0);
            $tax = (float) ($invoice['tax'] ?? 0);
            $gross = (float) ($invoice['total'] ?? 0);

            $rate = 0.0;
            if ($net > 0) {
                $rate = round(($tax / $net) * 100, 2);
            } elseif (isset($invoice['taxrate'])) {
                $rate = (float) $invoice['taxrate'];
            }

            $reference = (string) ($invoice['invoicenum'] ?? '') . (!empty($invoice['cn']) ? (string) $invoice['cn'] : (string) ($invoice['id'] ?? ''));

            $rows[] = [
                'source' => 'ZATCA Invoice',
                'date' => $invoice['date'] ?? '',
                'type' => $is_return_invoice ? 'ZATCA Return Invoice' : 'ZATCA Submitted Invoice',
                'reference' => $reference,
                'supplier' => $invoice['account'] ?? '',
                'category' => 'Invoice',
                'description' => (string) ($invoice['notes'] ?? ''),
                'net' => $net,
                'tax' => $tax,
                'gross' => $gross,
                'rate' => $rate,
                'txn_id' => $invoice['id'] ?? '',
                'details' => [
                    'supplier_vat' => $supplier_vat,
                    'supplier_unified' => $supplier_unified,
                    'zatca_uuid' => (string) ($invoice['zatca_uuid'] ?? ''),
                    'zatca_invoice_type' => (string) ($invoice['zatca_invoice_type'] ?? ''),
                    'zatca_submission_status' => (string) ($invoice['zatca_submission_status'] ?? $invoice['zatca_status'] ?? ''),
                    'zatca_submitted_at' => (string) ($invoice['zatca_last_submit_at'] ?? $invoice['zatca_submission_date'] ?? ''),
                    'reporting_status' => $reporting_status,
                    'validation_status' => $validation_status,
                ],
            ];
        }

        $expenses_query = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate);

        if ($only_taxable) {
            $expenses_query->where_gt('tax', 0);
        }

        if (!$transactions_all_data) {
            $expenses_query->where('aid', $user->id);
        }

        $expenses = [];
        if ($include_expenses) {
            $expenses = $expenses_query
                ->order_by_desc('date')
                ->order_by_desc('id')
                ->find_array();
        }

        $payee_ids = [];
        foreach ($expenses as $expense) {
            if (!empty($expense['payeeid'])) {
                $payee_ids[] = (int) $expense['payeeid'];
            }
        }

        $payee_contacts_map = [];
        if (!empty($payee_ids)) {
            $payee_contacts = ORM::for_table('crm_accounts')->where_in('id', array_unique($payee_ids))->find_array();
            foreach ($payee_contacts as $payee_contact) {
                $payee_contacts_map[(int) $payee_contact['id']] = $payee_contact;
            }
        }

        foreach ($expenses as $expense) {
            $payee_contact = $payee_contacts_map[(int) ($expense['payeeid'] ?? 0)] ?? [];
            $supplier_vat = trim((string) ($payee_contact['tax_number'] ?? ''));

            if ($only_vat_suppliers && $supplier_vat === '') {
                continue;
            }

            $gross = (float) ($expense['amount'] ?? 0);
            $tax = (float) ($expense['tax'] ?? 0);
            $net = round($gross - $tax, 2);
            if ($net < 0) {
                $net = 0.0;
            }

            $rate = 0.0;
            if ($net > 0) {
                $rate = round(($tax / $net) * 100, 2);
            }

            $rows[] = [
                'source' => 'Expense',
                'date' => $expense['date'] ?? '',
                'type' => 'Expense',
                'reference' => (string) ($expense['ref'] ?? ''),
                'supplier' => (string) ($expense['payee'] ?? $expense['account'] ?? ''),
                'category' => (string) ($expense['category'] ?? 'Expense'),
                'description' => (string) ($expense['description'] ?? ''),
                'net' => $net,
                'tax' => $tax,
                'gross' => $gross,
                'rate' => $rate,
                'txn_id' => $expense['id'] ?? '',
                'details' => [
                    'supplier_vat' => $supplier_vat,
                    'supplier_unified' => trim((string) ($payee_contact['entity_number'] ?? '')),
                    'zatca_uuid' => '',
                    'zatca_invoice_type' => '',
                    'zatca_submission_status' => (string) ($expense['status'] ?? ''),
                    'zatca_submitted_at' => '',
                    'reporting_status' => '',
                    'validation_status' => '',
                ],
            ];
        }

        usort($rows, function ($a, $b) {
            $d1 = (string) ($a['date'] ?? '');
            $d2 = (string) ($b['date'] ?? '');
            if ($d1 === $d2) {
                return (int) ($b['txn_id'] ?? 0) <=> (int) ($a['txn_id'] ?? 0);
            }

            return strcmp($d2, $d1);
        });

        $summary_net = 0.0;
        $summary_tax = 0.0;
        $summary_gross = 0.0;
        $summary_expenses = 0.0;

        foreach ($rows as $row) {
            $summary_net += (float) ($row['net'] ?? 0);
            $summary_tax += (float) ($row['tax'] ?? 0);
            $summary_gross += (float) ($row['gross'] ?? 0);

            if (($row['source'] ?? '') === 'Expense') {
                $summary_expenses += (float) ($row['gross'] ?? 0);
            }
        }

        $total_invoices = 0;
        $total_expenses = 0;
        foreach ($rows as $row) {
            if (($row['source'] ?? '') === 'ZATCA Invoice') {
                $total_invoices++;
            } elseif (($row['source'] ?? '') === 'Expense') {
                $total_expenses++;
            }
        }

        view('reports_zatca_returns', [
            'fdate' => $fdate,
            'tdate' => $tdate,
            'total_invoices' => $total_invoices,
            'total_expenses' => $total_expenses,
            'rows' => $rows,
            'summary_net' => $summary_net,
            'summary_tax' => $summary_tax,
            'summary_gross' => $summary_gross,
            'summary_expenses' => $summary_expenses,
            'only_taxable' => $only_taxable,
            'only_vat_suppliers' => $only_vat_suppliers,
            'include_expenses' => $include_expenses,
            'only_returns' => $only_returns,
        ]);

        break;

    case 'export-zatca-returns-invoices':
        $sales_all_data = has_access($user->roleid, 'sales', 'all_data');
        [$fdate, $tdate] = zatca_report_date_range($data);

        $only_taxable = zatca_bool_flag($data['only_taxable'] ?? null, false);
        $only_vat_suppliers = zatca_bool_flag($data['only_vat_suppliers'] ?? null, false);
        $only_returns = zatca_bool_flag($data['only_returns'] ?? null, false);

        $invoices_query = ORM::for_table('sys_invoices')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate)
            ->order_by_asc('date')
            ->order_by_asc('id');

        if ($only_taxable) {
            $invoices_query->where_gt('tax', 0);
        }

        if ($only_returns) {
            $invoices_query->where_raw("COALESCE(is_credit_invoice,0) = ?", [1]);
        }

        if (!$sales_all_data) {
            $invoices_query->where('aid', $user->id);
        }

        $invoices = $invoices_query->find_array();

        $contact_ids = [];
        foreach ($invoices as $inv) {
            if (!empty($inv['userid'])) {
                $contact_ids[] = (int) $inv['userid'];
            }
        }

        $contacts_map = [];
        if (!empty($contact_ids)) {
            $contacts = ORM::for_table('crm_accounts')->where_in('id', array_unique($contact_ids))->find_array();
            foreach ($contacts as $contact) {
                $contacts_map[(int) $contact['id']] = $contact;
            }
        }

        $excel_rows = [];
        foreach ($invoices as $invoice) {
            $contact = $contacts_map[(int) ($invoice['userid'] ?? 0)] ?? [];
            $customer_vat = trim((string) ($contact['tax_number'] ?? ''));

            if ($only_vat_suppliers && $customer_vat === '') {
                continue;
            }

            [$reporting_status, $validation_status] = zatca_response_statuses($invoice);

            $excel_rows[] = [
                $invoice['date'] ?? '',
                (string) ($invoice['invoicenum'] ?? '') . (!empty($invoice['cn']) ? (string) $invoice['cn'] : (string) ($invoice['id'] ?? '')),
                $invoice['account'] ?? '',
                $customer_vat,
                $invoice['subtotal'] ?? 0,
                $invoice['tax'] ?? 0,
                $invoice['total'] ?? 0,
                $invoice['status'] ?? '',
                strtoupper((string) ($invoice['zatca_status'] ?? '')),
                $reporting_status,
                $validation_status,
            ];
        }

        exportExcel(
            'zatca-returns-invoices-' . $fdate . '-to-' . $tdate . '.xlsx',
            [
                'Date',
                'Invoice Number',
                'Customer',
                'Customer VAT',
                'Taxable Amount',
                'VAT Amount',
                'Total Amount',
                'Invoice Status',
                'ZATCA Status',
                'Reporting Status',
                'Validation Status',
            ],
            $excel_rows
        );

        break;

    case 'export-zatca-returns-expenses':
        $transactions_all_data = has_access($user->roleid, 'transactions', 'all_data');
        [$fdate, $tdate] = zatca_report_date_range($data);

        $only_taxable = zatca_bool_flag($data['only_taxable'] ?? null, false);
        $only_vat_suppliers = zatca_bool_flag($data['only_vat_suppliers'] ?? null, false);

        $expenses_query = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->where_gte('date', $fdate)
            ->where_lte('date', $tdate)
            ->order_by_asc('date')
            ->order_by_asc('id');

        if ($only_taxable) {
            $expenses_query->where_gt('tax', 0);
        }

        if (!$transactions_all_data) {
            $expenses_query->where('aid', $user->id);
        }

        $expenses = $expenses_query->find_array();

        $payee_ids = [];
        foreach ($expenses as $expense) {
            if (!empty($expense['payeeid'])) {
                $payee_ids[] = (int) $expense['payeeid'];
            }
        }

        $payee_contacts_map = [];
        if (!empty($payee_ids)) {
            $payee_contacts = ORM::for_table('crm_accounts')->where_in('id', array_unique($payee_ids))->find_array();
            foreach ($payee_contacts as $payee_contact) {
                $payee_contacts_map[(int) $payee_contact['id']] = $payee_contact;
            }
        }

        $excel_rows = [];
        foreach ($expenses as $expense) {
            $payee_contact = $payee_contacts_map[(int) ($expense['payeeid'] ?? 0)] ?? [];
            $supplier_vat = trim((string) ($payee_contact['tax_number'] ?? ''));

            if ($only_vat_suppliers && $supplier_vat === '') {
                continue;
            }

            $excel_rows[] = [
                $expense['date'] ?? '',
                $expense['account'] ?? '',
                $expense['payee'] ?? '',
                $supplier_vat,
                $expense['category'] ?? '',
                $expense['description'] ?? '',
                $expense['amount'] ?? 0,
                $expense['tax'] ?? 0,
                $expense['method'] ?? '',
                $expense['ref'] ?? '',
                $expense['status'] ?? '',
            ];
        }

        exportExcel(
            'zatca-returns-expenses-' . $fdate . '-to-' . $tdate . '.xlsx',
            [
                'Date',
                'Account',
                'Payee',
                'Payee VAT',
                'Category',
                'Description',
                'Amount',
                'Tax',
                'Method',
                'Reference',
                'Status',
            ],
            $excel_rows
        );

        break;

    case 'export':
        $total_customers = Contact::count();
        $total_transactions = Transaction::count();
        $total_invoices = Invoice::count();
        $total_products = Item::count();

        view('reports_export', [
            'total_customers' => $total_customers,
            'total_transactions' => $total_transactions,
            'total_invoices' => $total_invoices,
            'total_products' => $total_products,
        ]);

        break;

    case 'export-customers':
        $data = [];

        $contacts = Contact::all();

        foreach ($contacts as $contact) {
            $data[] = [
                $contact->account,
                $contact->email,
                $contact->phone,
                $contact->company,
                $contact->address,
                $contact->city,
                $contact->state,
                $contact->zip,
                $contact->country,
                $contact->balance,
            ];
        }

        exportExcel(
            'customers.xlsx',
            [
                $_L['Name'],
                $_L['Email'],
                $_L['Phone'],
                $_L['Company'],
                $_L['Address'],
                $_L['City'],
                $_L['State Region'],
                $_L['ZIP Postal Code'],
                $_L['Country'],
                $_L['Balance'],
            ],
            $data
        );

        break;

    case 'export-transactions':
        $data = [];
        $transactions = Transaction::all();
        foreach ($transactions as $transaction) {
            $data[] = [
                $transaction->date,
                $transaction->account,
                $transaction->type,
                $transaction->category,
                $transaction->amount,
                $transaction->method,
                $transaction->ref,
                $transaction->description,
            ];
        }

        exportExcel(
            'transactions.xlsx',
            [
                $_L['Date'],
                $_L['Account'],
                $_L['Type'],
                $_L['Category'],
                $_L['Amount'],
                $_L['Method'],
                $_L['Ref'],
                $_L['Description'],
            ],
            $data
        );

        break;

    case 'export-invoices':
        $data = [];

        $invoices = Invoice::all();

        foreach ($invoices as $invoice) {
            $data[] = [
                $invoice->id,
                $invoice->date,
                $invoice->account,
                $invoice->subtotal,
                $invoice->total,
                $invoice->credit,
                $invoice->status,
            ];
        }

        exportExcel(
            'invoices.xlsx',
            [
                $_L['Invoice'],
                $_L['Date'],
                $_L['Customer'],
                'Sub Total',
                $_L['Total'],
                $_L['Credit'],
                $_L['Status'],
            ],
            $data
        );

        break;

    case 'export-pdf-invoices':
        exportPdf('dd');

        break;

    case 'tax':
        view('reports_tax', []);

        break;


    default:
        echo 'action not defined';
}
