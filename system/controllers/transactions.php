<?php
/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

_auth();
$ui->assign('_title', $_L['Transactions'] . '- ' . $config['CompanyName']);
$ui->assign('selected_navigation', 'transactions');
$ui->assign('content_inner', inner_contents($config['c_cache']));
$action = $routes['1'];
$user = authenticate_admin();
$mdate = date('Y-m-d');
$data = request()->all();
Event::trigger('transactions');

if (
    !has_access($user->roleid, 'transactions') &&
    !has_access($user->roleid, 'transactions', 'create')
) {
    permissionDenied();
}

switch ($action) {
    case 'deposit':
        $currencies_all = Currency::getAllCurrencies();

        Event::trigger('transactions/deposit/');

        $d = Account::get()->toArray();

        $p = Contact::all();

        $ui->assign('p', $p);
        $ui->assign('d', $d);
        $cats = ORM::for_table('sys_cats')
            ->where('type', 'Income')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('cats', $cats);
        $pms = ORM::for_table('sys_pmethods')->find_many();
        $ui->assign('pms', $pms);
        $ui->assign('mdate', $mdate);
        $tags = Tags::get_all('Income');
        $ui->assign('tags', $tags);

        $x = Transaction::where('type', 'Income');
        if (!has_access($user->roleid, 'transactions', 'all_data')) {
            $x->where('aid', $user->id);
        }

        $x->orderBy('id', 'desc')->limit(20);
        $tr = $x->get()->toArray();
        $currency_rate = 1;

        $companies = Company::all();

        $companies_by_ids = $companies->keyBy('id')->all();

        $staffs = User::all();

        $items = Item::select(['id','name'])->get()->keyBy('id')->all();

        $ui->assign('tr', $tr);


        view('transactions_deposit', [
            'currencies' => Currency::all(),
            'currency_rate' => $currency_rate,
            'currencies_all' => $currencies_all,
            'companies' => $companies,
            'companies_by_ids' => $companies_by_ids,
            'staffs' => $staffs,
            'items' => $items,
        ]);
        break;

    case 'deposit-post':
        $msg = '';

        $data = $request->all();
        Event::trigger('transactions/deposit-post/');
        $account = _post('account');

        $currency_iso_code = _post('currency');

        $code = _post('code');

        if ($account == '') {
            $msg .= $_L['Select An Account'] . '<br />';
        }
        $date = _post('date');
        $amount = _post('amount');
        $amount = createFromCurrency($amount, $currency_iso_code);

        $item_id = _post('item_id');

        $payerid = _post('payer');

        $ref = _post('ref');

        if ($ref != '') {
            $r_check = Transaction::where('type', 'Income')
                ->where('ref', $ref)
                ->where('date', $date)
                ->first();

            if ($r_check) {
                $msg .= $_L['Ref'] . ' ' . $_L['already exist'] . '<br />';
            }
        }

        $pmethod = _post('pmethod');
        $cat = _post('cats');

        $category_name = '';
        $cat_id = 0;

        if(!empty($cat))
        {
            $category = TransactionCategory::find($cat);
            if ($category) {
                $current_total_amount = $category->total_amount;
                $category->total_amount = $current_total_amount + $amount;
                $category->save();
                $cat_id = $category->id;

                $category_name = $category->name;

            }
        }

        $tags = $data['tags'] ?? [];
        $attachments = _post('attachments');
        if ($payerid == '') {
            $payerid = '0';
        }

        $description = _post('description');

        if ($description == '') {
            $msg .= $_L['description_error'] . '<br />';
        }

        if (is_numeric($amount) == false) {
            $msg .= $_L['amount_error'] . '<br />';
        }

        $status = _post('status');

        $company = _post('company');

        $staff_id = _post('staff_id');





        if ($msg == '') {
            $account_find = Account::find($account);

            Tags::save($tags, 'Income');

            $currency_rate = 0.00;

            $currency = Currency::where('iso_code', $currency_iso_code)->first();

            if($currency)
            {
                $currency_rate = $currency->rate ?? 1.00;
            }

            $d = new Transaction();
            $d->account = $account_find->account;
            $d->account_id = $account;
            $d->type = 'Income';
            $d->payerid = $payerid;
            $d->tags = Arr::arr_to_str($tags);
            $d->amount = $amount;
            $d->category = $category_name;
            $d->cat_id = $cat_id;
            $d->method = $pmethod;
            $d->ref = $ref;
            $d->description = $description;

            $d->attachments = $attachments;
            $d->date = $date;
            $d->dr = '0.00';
            $d->cr = $amount;
            $d->bal = 0.0;

            $d->payer = '';
            $d->payee = '';
            $d->payeeid = '0';
            $d->status = 'Cleared';
            $d->tax = '0.00';
            $d->iid = 0;
            $d->aid = $user->id;

            $d->vid = _raid(8);

            $d->status = $status;

            $d->currency_iso_code = $currency_iso_code;

            $d->currency_rate = $currency_rate;

            $d->updated_at = date('Y-m-d H:i:s');

            $d->code = $code;

            $d->item_id = $item_id;

            if (is_numeric($staff_id)) {
                $d->staff_id = $staff_id;
            }

            if (is_numeric($company)) {
                $d->company_id = $company;
            }

            $d->save();

            update_option(
                'income_code_current_number',
                current_number_would_be($code)
            );

            $tid = $d->id;
            _log(
                'New Deposit: ' .
                    $description .
                    ' [TrID: ' .
                    $tid .
                    ' | Amount: ' .
                    $amount .
                    ']',
                'Admin',
                $user->id
            );
            _msglog('s', $_L['Transaction Added Successfully']);

            echo $tid;
        } else {
            echo $msg;
        }

        break;

    case 'expense':
        Event::trigger('transactions/expense/');
        $currencies = Currency::all();
        $currencies_all = Currency::getAllCurrencies();
        $ui->assign('currencies', $currencies);
        $d = ORM::for_table('sys_accounts')->find_many();
        $p = ORM::for_table('crm_accounts')->find_many();
        $ui->assign('p', $p);
        $ui->assign('d', $d);
        $tags = Tags::get_all('Expense');
        $ui->assign('tags', $tags);
        $cats = ORM::for_table('sys_cats')
            ->where('type', 'Expense')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('cats', $cats);
        $pms = ORM::for_table('sys_pmethods')->find_many();
        $ui->assign('pms', $pms);
        $ui->assign('mdate', $mdate);

        $x = Transaction::where('type', 'Expense');
        if (!has_access($user->roleid, 'transactions', 'all_data')) {
            $x->where('aid', $user->id);
        }

        $x->orderBy('id', 'DESC')->limit(20);
        $tr = $x->get()->toArray();
        $ui->assign('tr', $tr);

        //

        $currency_rate = 1;

        $companies = Company::all();

        $companies_by_ids = $companies->keyBy('id')->all();

        $staffs = User::all();

        $items = Item::select(['id','name'])->get()->keyBy('id')->all();

        view('transactions_expense', [
            'currency_rate' => $currency_rate,
            'expense_types' => ExpenseType::orderBy('sorder')->get(),
            'currencies_all' => $currencies_all,
            'companies' => $companies,
            'companies_by_ids' => $companies_by_ids,
            'staffs' => $staffs,
            'items' => $items,
        ]);

        break;


    case 'modal-expense':

        $invoice_id = route(2,0);
        $invoice = null;
        $invoice_total = 0;
        $invoice_credit = 0;
        $invoice_due = 0;
        $amount = '';

        if($invoice_id !== '' && $invoice_id !== '0')
        {
            $invoice = Invoice::find($invoice_id);

            if($invoice)
            {
                $invoice_total = $invoice->total;
                $invoice_credit = $invoice->credit;
                $invoice_due = $invoice_total - $invoice_credit;
                $amount = $invoice_due;
            }

        }

        $accounts = Account::all();
        $currency = $invoice->currency ?? $config['home_currency'];


        \view('modal-expense',[
            'accounts' => $accounts,
            'invoice_id' => $invoice_id,
            'invoice' => $invoice,
            'invoice_total' => $invoice_total,
            'invoice_credit' => $invoice_credit,
            'invoice_due' => $invoice_due,
            'amount' => $amount,
            'currency' => $currency,
        ]);

    case 'expense-post':
        Event::trigger('transactions/expense-post/');

        $msg = '';



        $code = _post('code');

        $currency_iso_code = _post('currency');

        $account = _post('account');
        $date = _post('date');
        $amount = _post('amount');

        $amount = createFromCurrency($amount, $currency_iso_code);

        $payee = _post('payee');

        $ref = _post('ref');

        $invoice_id = $data['invoice_id'] ?? 0;

        if($invoice_id !== '' && $invoice_id !== '0')
        {
            $invoice = Invoice::find($invoice_id);

            if($invoice)
            {
                if($invoice->type === 'Credit Note')
                {
                    if($amount == $invoice->total)
                    {
                        $invoice->status = 'Paid';
                    }
                }

                $invoice->save();
            }

        }

        if ($account == '') {
            $msg .= $_L['Select An Account'];
        }

        if ($ref != '') {
            $r_check = Transaction::where('type', 'Expense')
                ->where('ref', $ref)
                ->where('date', $date)
                ->first();

            if ($r_check) {
                $msg .= $_L['Ref'] . ' ' . $_L['already exist'] . '<br />';
            }
        }
        $pmethod = _post('pmethod');

        $sub_type = _post('sub_type');
        $cat = _post('cats',0);
        $tags = $data['tags'] ?? [];
        $attachments = _post('attachments');
        if (!is_numeric($payee)) {
            $payee = '0';
        }


        $category_name = '';
        $cat_id = 0;

        if(!empty($cat))
        {
            $category = TransactionCategory::find($cat);
            if ($category) {
                $current_total_amount = $category->total_amount;
                $category->total_amount = $current_total_amount + $amount;
                $category->save();
                $cat_id = $category->id;
                $category_name = $category->name;
            }
        }



        $description = _post('description');

        if ($description == '') {
            $msg .= $_L['description_error'] . '<br />';
        }

        if (is_numeric($amount) == false) {
            $msg .= $_L['amount_error'] . '<br />';
        }

        $company = _post('company');

        $staff_id = _post('staff_id');
        $item_id = _post('item_id');

        if ($msg == '') {
            Tags::save($tags, 'Expense');

            $status = _post('status');

            $currency_rate = 0.00;

            $currency = Currency::where('iso_code', $currency_iso_code)->first();

            if($currency)
            {
                $currency_rate = $currency->rate ?? 1.00;
            }




            $account_find = Account::find($account);
            $d = new Transaction();
            $d->account = $account_find->account;
            $d->account_id = $account_find->id;
            $d->type = 'Expense';
            $d->payeeid = $payee;
            $d->tags = Arr::arr_to_str($tags);
            $d->amount = $amount;
            $d->category = $category_name;
            $d->cat_id = $cat_id;
            $d->method = $pmethod;
            $d->ref = $ref;
            $d->description = $description;

            $d->attachments = $attachments;
            $d->date = $date;
            $d->dr = $amount;
            $d->cr = '0.00';
            $d->bal = 0.0;

            $d->payer = '';
            $d->payee = '';
            $d->payerid = '0';
            $d->status = 'Cleared';
            $d->tax = '0.00';
            $d->iid = $invoice_id;

            $d->sub_type = $sub_type;
            $d->aid = $user->id;

            $d->vid = _raid(8);

            $d->currency_iso_code = $currency_iso_code;
            $d->currency_rate = $currency_rate;

            $d->status = $status;

            $d->updated_at = date('Y-m-d H:i:s');

            $d->code = $code;

            if (is_numeric($staff_id)) {
                $d->staff_id = $staff_id;
            }

            if (is_numeric($company)) {
                $d->company_id = $company;
            }

            $d->item_id = (int) $item_id;

            $d->save();
            $tid = $d->id;
            _log(
                'New Expense: ' .
                    $description .
                    ' [TrID: ' .
                    $tid .
                    ' | Amount: ' .
                    $amount .
                    ']',
                'Admin',
                $user->id
            );
            _msglog('s', $_L['Transaction Added Successfully']);

            update_option(
                'expense_code_current_number',
                current_number_would_be($code)
            );

            echo $tid;
        } else {
            echo $msg;
        }

        break;

    case 'transfer':
        Event::trigger('transactions/transfer/');
        $currencies_all = Currency::getAllCurrencies();
        $d = ORM::for_table('sys_accounts')->find_many();
        $ui->assign('p', $d);
        $ui->assign('d', $d);
        $pms = ORM::for_table('sys_pmethods')->find_many();
        $ui->assign('pms', $pms);
        $ui->assign('mdate', $mdate);
        $tags = Tags::get_all('Transfer');
        $ui->assign('tags', $tags);

        $x = Transaction::where('type', 'Out');
        if (!has_access($user->roleid, 'transactions', 'all_data')) {
            $x->where('aid', $user->id);
        }

        $x->orderBy('id', 'DESC')->limit(20);
        $tr = $x->get()->toArray();
        $ui->assign('tr', $tr);

        $currency_rate = 1;

        view('transfer', [
            'currencies' => Currency::all(),
            'currency_rate' => $currency_rate,
            'currencies_all' => $currencies_all,
        ]);

        break;

    case 'transfer-post':
        Event::trigger('transactions/transfer-post/');
        $currency_iso_code = _post('currency');
        $faccount = _post('faccount');
        $taccount = _post('taccount');
        $date = _post('date');
        $amount = _post('amount');

        $amount = createFromCurrency($amount, $currency_iso_code);

        $pmethod = _post('pmethod');
        $ref = _post('ref');
        $description = _post('description');
        $msg = '';

        if ($faccount == '') {
            $msg .= $_L['Choose an Account'] . ' ' . '<br />';
        }

        if ($taccount == '') {
            $msg .= $_L['Choose the Traget Account'] . ' ' . '<br />';
        }

        if ($description == '') {
            $msg .= $_L['description_error'] . '<br />';
        }

        if (is_numeric($amount) == false) {
            $msg .= $_L['amount_error'] . '<br />';
        }

        if ($faccount === $taccount) {
            $msg .= $_L['same_account_error'] . '<br />';
        }

        $tags = $data['tags'] ?? [];
        Tags::save($tags, 'Transfer');

        if ($msg == '') {
            $d = new Transaction();
            $account_find = Account::find($faccount);
            $d->account = $account_find->account;
            $d->account_id = $account_find->id;
            $d->type = 'Out';
            $d->amount = $amount;
            $d->method = $pmethod;
            $d->ref = $ref;
            $d->tags = Arr::arr_to_str($tags);
            $d->description = $description;
            $d->date = $date;
            $d->dr = $amount;
            $d->cr = '0.00';
            $d->bal = 0.0;

            $d->payer = '';
            $d->payee = '';
            $d->payerid = '0';
            $d->payeeid = '0';
            $d->category = '';
            $d->status = 'Cleared';
            $d->tax = '0.00';
            $d->iid = 0;
            $d->aid = $user->id;

            $d->vid = _raid(8);

            $d->updated_at = date('Y-m-d H:i:s');
            $d->currency_iso_code = $currency_iso_code;

            $d->save();

            $d = new Transaction();
            $account_find = Account::find($taccount);
            $d->account = $account_find->account;
            $d->account_id = $account_find->id;
            $d->type = 'In';
            $d->amount = $amount;
            $d->method = $pmethod;
            $d->ref = $ref;
            $d->tags = Arr::arr_to_str($tags);
            $d->description = $description;
            $d->date = $date;
            $d->dr = '0.00';
            $d->cr = $amount;
            $d->bal = 0.0;

            $d->payer = '';
            $d->payee = '';
            $d->payerid = '0';
            $d->payeeid = '0';
            $d->category = '';
            $d->status = 'Cleared';
            $d->tax = '0.00';
            $d->iid = 0;
            $d->aid = 0;

            $d->vid = _raid(8);

            $d->updated_at = date('Y-m-d H:i:s');
            $d->currency_iso_code = $currency_iso_code;

            $d->save();
            _msglog('s', $_L['Transaction Added Successfully']);
            echo '1';
        } else {
            echo $msg;
        }

        break;

    case 'list':
        Event::trigger('transactions/list/');
        $cid = route(2);
        if ($cid == '' || $cid == '0') {
            $ui->assign('p_cid', '');
        } else {
            $ui->assign('p_cid', $cid);
        }

        $tr_type = route(3);
        if ($tr_type == 'income') {
            $tr_type = 'Income';
        } elseif ($tr_type == 'expense') {
            $tr_type = 'Expense';
        } else {
            $tr_type = '';
        }

        $parent_menu = route(4);
        if ($parent_menu == 'reports') {
            $ui->assign('selected_navigation', 'reports');
        }

        $account = route(3);
        if ($account == '' || $account == '0') {
            $ui->assign('p_account', '');
        } else {
            $ui->assign('p_account', $account);
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

        $categories = TransactionCategory::all();

        $home_currency = homeCurrency();

        $payment_methods = PaymentMethod::all();

        $contact_groups = ContactGroup::all();


        $staffs= User::get()->keyBy('id')->toArray();

        $items = Item::orderBy('name')
            ->limit(250)
            ->get();

        $currencies = Currency::all();

        $has_multiple_currencies = count($currencies) > 1;

        view('transactions_list', [
            'tr_type' => $tr_type,
            'expense_types' => ExpenseType::orderBy('sorder')->get(),
            'categories' => $categories,
            'home_currency' => $home_currency,
            'payment_methods' => $payment_methods,
            'contact_groups' => $contact_groups,
            'staffs' => $staffs,
            'items' => $items,
            'currencies' => $currencies,
            'has_multiple_currencies' => $has_multiple_currencies,
        ]);

        break;

    case 'a':
        Event::trigger('transactions/a/');
        $d = ORM::for_table('sys_accounts')->find_many();

        $p = ORM::for_table('crm_accounts')->find_many();
        $ui->assign('p', $p);
        $ui->assign('d', $d);
        $cats = ORM::for_table('sys_cats')
            ->where('type', 'Income')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('cats', $cats);
        $pms = ORM::for_table('sys_pmethods')->find_many();
        $ui->assign('pms', $pms);

        view('tra');
        break;

    case 'list-income':
        Event::trigger('transactions/list-income/');
        $ui->assign('selected_navigation', 'reports');
        $paginator = Paginator::bootstrap('sys_transactions', 'type', 'Income');
        $d = ORM::for_table('sys_transactions')
            ->where('type', 'Income')
            ->offset($paginator['startpoint'])
            ->limit($paginator['limit'])
            ->order_by_desc('date')
            ->find_many();
        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);
        view('transactions');
        break;

    case 'list-expense':
        Event::trigger('transactions/list-expense/');
        $ui->assign('selected_navigation', 'reports');
        $paginator = Paginator::bootstrap(
            'sys_transactions',
            'type',
            'Expense'
        );
        $d = ORM::for_table('sys_transactions')
            ->where('type', 'Expense')
            ->offset($paginator['startpoint'])
            ->limit($paginator['limit'])
            ->order_by_desc('date')
            ->find_many();
        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);
        view('transactions');
        break;

    case 'manage':
        Event::trigger('transactions/manage/');
        $id = $routes['2'];
        $t = ORM::for_table('sys_transactions')->find($id);
        if ($t) {
            $p = ORM::for_table('crm_accounts')->find_many();
            $ui->assign('p', $p);
            $ui->assign('t', $t);
            $d = ORM::for_table('sys_accounts')->find_many();
            $ui->assign('d', $d);
            $icat = '1';
            if ($t['type'] == 'Income') {
                $cats = ORM::for_table('sys_cats')
                    ->where('type', 'Income')
                    ->find_many();
                $tags = Tags::get_all('Income');
            } elseif ($t['type'] == 'Expense') {
                $cats = ORM::for_table('sys_cats')
                    ->where('type', 'Expense')
                    ->find_many();
                $tags = Tags::get_all('Expense');
            } elseif ($t['type'] == 'Equity') {
                $cats = [];
                $tags = [];
            } else {
                $cats = '0';
                $icat = '0';
                $tags = Tags::get_all('Transfer');
            }

            $ui->assign('tags', $tags);
            $dtags = explode(',', ($t['tags'] ?? ''));
            $ui->assign('dtags', $dtags);
            $ui->assign('icat', $icat);
            $ui->assign('cats', $cats);
            $pms = ORM::for_table('sys_pmethods')->find_many();
            $ui->assign('pms', $pms);
            $ui->assign('mdate', $mdate);

            $companies = Company::all();

            $staffs = User::all();

            $items = Item::select(['id','name'])->get()->keyBy('id')->all();

            view('transactions_manage', [
                'companies' => $companies,
                'staffs' => $staffs,
                'items' => $items,
            ]);
        } else {
            r2(U . 'transactions/list', 'e', $_L['Transaction_Not_Found']);
        }

        break;

    case 'edit-post':
        if (!has_access($user->roleid, 'transactions', 'edit')) {
            echo $_L['You do not have permission'];
            exit();
        }

        Event::trigger('transactions/edit-post/');

        $id = _post('id');
        $d = Transaction::find($id);
        if ($d) {
            $cat = _post('cats', '0');
            $pmethod = _post('pmethod');
            $ref = _post('ref');
            $date = _post('date');
            $payer = _post('payer');
            $payee = _post('payee');
            $description = _post('description');

            $item_id = _post('item_id');

            ray($item_id);

            $msg = '';
            if ($description == '') {
                $msg .= $_L['description_error'] . '<br />';
            }

            if (!is_numeric($payer)) {
                $payer = '0';
            }

            if (!is_numeric($payee)) {
                $payee = '0';
            }

            $tags = $data['tags'] ?? [];

            $company = _post('company');
            $staff_id = _post('staff_id');

            $category = TransactionCategory::find($cat);
            $category_name = '';
            if ($category) {
                $category_name = $category->name;
            }

            if ($msg == '') {
                Tags::save($tags, $d['type']);
                $d->category = $category_name;
                $d->cat_id = $cat;
                $d->payerid = $payer;
                $d->payeeid = $payee;
                $d->method = $pmethod;
                $d->ref = $ref;
                $d->tags = Arr::arr_to_str($tags);
                $d->description = $description;
                $d->date = $date;

                $d->company_id = is_numeric($company) ? $company : 0;

                $d->staff_id = is_numeric($staff_id) ? $staff_id : 0;

                $d->item_id = is_numeric($item_id) ? $item_id : 0;

                $d->save();
                _msglog('s', $_L['edit_successful']);
                echo $d->id;
            } else {
                echo $msg;
            }
        } else {
            echo 'Transaction Not Found';
        }

        break;

    case 'delete-post':
        Event::trigger('transactions/delete-post/');
        if (!has_access($user->roleid, 'transactions', 'delete')) {
            permissionDenied();
        }

        $id = _post('id');
        if (Transaction::remove($id)) {
            Transaction::rebuildCatData();

            r2(
                U . 'transactions/list',
                's',
                $_L['transaction_delete_successful']
            );
        } else {
            r2(U . 'transactions/list', 'e', $_L['an_error_occured']);
        }

        break;

    case 's':
        Event::trigger('transactions/s/');
        $d = ORM::for_table('sys_accounts')->find_many();

        $c = ORM::for_table('crm_accounts')->find_many();
        $ui->assign('c', $c);
        $ui->assign('d', $d);
        $cats = ORM::for_table('sys_cats')
            ->where('type', 'Income')
            ->order_by_asc('sorder')
            ->find_many();
        $ui->assign('cats', $cats);
        $pms = ORM::for_table('sys_pmethods')->find_many();
        $ui->assign('pms', $pms);
        $mdate = date('Y-m-d');
        $fdate = date('Y-m-d', strtotime('today - 30 days'));
        $ui->assign('fdate', $fdate);
        $ui->assign('tdate', $mdate);
        view('trs');
        break;

    case 'export_csv':
        Event::trigger('transactions/export_csv/');
        $fileName = 'transactions_' . time() . '.csv';
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}

");
        header("Expires: 0");
        header("Pragma: public");
        $fh = @fopen('php://output', 'w');
        $headerDisplayed = false;

        $results = db_find_array('sys_transactions');

        foreach ($results as $data) {
            if (!$headerDisplayed) {
                fputcsv($fh, array_keys($data));
                $headerDisplayed = true;
            }

            fputcsv($fh, $data);
        }

        fclose($fh);
        break;

    case 'handle_attachment':
        $uploader = new Uploader();
        $uploader->setDir('storage/transactions/');
        $uploader->sameName(false);
        $uploader->setExtensions(['jpg', 'jpeg', 'png', 'gif', 'pdf']); //allowed extensions list//
        if ($uploader->uploadFile('file')) {
            $uploaded = $uploader->getUploadName(); //get uploaded file name, renames on upload//
            $file = $uploaded;
            $msg = 'Uploaded Successfully';
            $success = 'Yes';
        } else {
            //upload failed
            $file = '';
            $msg = $uploader->getMessage();
            $success = 'No';
        }

        $a = [
            'success' => $success,
            'msg' => $msg,
            'file' => $file,
        ];
        header('Content-Type: application/json');
        echo json_encode($a);
        break;

    case 'tr_list':
        $columns = [];
        $columns[] = 'id';
        $columns[] = 'date';
        $columns[] = 'account';
        $columns[] = 'type';
        $columns[] = 'amount';
        $columns[] = 'description';
        $columns[] = 'contact';
        $columns[] = 'method';
        $columns[] = 'ref';
        $columns[] = 'dr';
        $columns[] = 'cr';
        $columns[] = 'bal';
        $columns[] = 'manage';
        $order_by = $data['order'];
        $o_c_id = $order_by[0]['column'];
        $o_type = $order_by[0]['dir'];
        $a_order_by = $columns[$o_c_id];

        $transactions = Transaction::select([
            'id',
            'account',
            'type',
            'date',
            'amount',
            'description',
            'method',
            'ref',
            'dr',
            'cr',
            'bal',
            'currency_iso_code',
            'code',
            'staff_id',
            'payerid',
            'payeeid',
            'item_id',
        ]);

        $tr_type = _post('tr_type');
        if ($tr_type == 'Transfer') {
            $transactions = $transactions->where(function ($query) {
                $query->where('type', 'In')->orWhere('type', 'Out');
            });
        } elseif ($tr_type != '') {
            $transactions = $transactions->where('type', $tr_type);
        }

        $account = _post('account');
        if ($account != '') {
            $transactions = $transactions->where('account', $account);
        }

        $category = _post('category');



        if ($category != '') {
            $transactions = $transactions->where('cat_id', $category);
        }

        $payment_method = $data['payment_method'] ?? null;

        if($payment_method)
        {
            $transactions = $transactions->where('method', $payment_method);
        }

        $cid = _post('cid');
        if ($cid != '') {
            $transactions = $transactions->where(function ($query) use ($cid) {
                $query->where('payerid', $cid)->orWhere('payeeid', $cid);
            });
        }

        $item_id = _post('item_id');
        $item_id = (int) $item_id;

        if ($item_id > 0) {
            $transactions = $transactions->where('item_id', $item_id);
        }


        $staff_id = _post('staff_id');
        $staff_id = (int) $staff_id;

        if (!empty($staff_id)) {
            $transactions = $transactions->where('staff_id', $staff_id);
        }

        $group_id = _post('group_id');

        $contact_ids = [];

        if(!empty($group_id))
        {
            $contact_ids = Contact::where('gid', $group_id)
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();

        }

        if (!empty($contact_ids)) {
            $transactions = $transactions->where(function ($query) use ($contact_ids) {
                $query->whereIn('payerid', $contact_ids)->orWhereIn('payeeid', $contact_ids);
            });
        }

        $reportrange = _post('reportrange');
        if ($reportrange != '') {
            $reportrange = explode('-', $reportrange);
            $from_date = trim($reportrange[0]);
            $to_date = trim($reportrange[1]);
            $transactions = $transactions->whereBetween('date', [
                $from_date,
                $to_date,
            ]);
        }

        if (!has_access($user->roleid, 'transactions', 'all_data')) {
            $transactions = $transactions->where('aid', $user->id);
        }

        $iTotalRecords = $transactions->count();

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
            $transactions = $transactions->orderBy($a_order_by, 'DESC');
        } else {
            $transactions = $transactions->orderBy($a_order_by, 'ASC');
        }

        $transactions = $transactions->limit($end);
        $transactions = $transactions->offset($iDisplayStart);
        $transactions = $transactions->get()->toArray();
        $i = $iDisplayStart;

        $home_currency = homeCurrency();

        $currencies = Currency::all()->keyBy('iso_code');

        $staffs = User::all()->keyBy('id');

        $contacts = Contact::all()->keyBy('id');

        $items = Item::limit(500)->get()->keyBy('id');

        foreach ($transactions as $xs) {
            $dr = $xs['dr'];
            $cr = $xs['cr'];

            if (!empty($currencies[$xs['currency_iso_code']]->rate)) {
                $rate = $currencies[$xs['currency_iso_code']]->rate;
                if ($dr != 0.0) {
                    $dr = $rate * $dr;
                }
                if ($cr != 0.0) {
                    $cr = $rate * $cr;
                }
            }

            $tr_id = $xs['id'];

            if (isset($xs['code']) && $xs['code'] != '') {
                $tr_id = $xs['code'];
            }

            $contact = '';

            if($xs['type'] != 'Transfer')
            {
                $contact_id = $xs['payerid'] ? $xs['payerid'] : $xs['payeeid'];

                if(!empty($contact_id))
                {
                    $contact = $contacts[$contact_id]->account ?? '';
                }

            }


            $staff = '';

            if (isset($xs['staff_id']) && $xs['staff_id'] != '') {
                $staff = $staffs[$xs['staff_id']]->fullname ?? '';
            }

            $item = '';

            if (isset($xs['item_id']) && $xs['item_id'] != '') {
                $item = $items[$xs['item_id']]->name ?? '';
            }


            $records["data"][] = [
                '<a href="' .
                U .
                'transactions/manage/' .
                $xs['id'] .
                '">' .
                $tr_id .
                '</a>',
                '<a href="' .
                U .
                'transactions/manage/' .
                $xs['id'] .
                '">' .
                date($config['df'], strtotime($xs['date'])).
                '</a>',
                htmlentities($xs['account']),
                htmlentities(__($xs['type'])),
                formatCurrency($xs['amount'], $xs['currency_iso_code']),
                htmlentities($contact),
                htmlentities($xs['description']),
                htmlentities($xs['method']),
                htmlentities($xs['ref']),
                htmlentities($staff),
                htmlentities($item),
                $dr,
                $cr,
                // $xs['bal'],
                '<a href="' .
                U .
                'transactions/manage/' .
                $xs['id'] .
                '" class="btn btn-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <polygon points="0 0 24 0 24 24 0 24"></polygon>
        <path d="M5.85714286,2 L13.7364114,2 C14.0910962,2 14.4343066,2.12568431 14.7051108,2.35473959 L19.4686994,6.3839416 C19.8056532,6.66894833 20,7.08787823 20,7.52920201 L20,20.0833333 C20,21.8738751 19.9795521,22 18.1428571,22 L5.85714286,22 C4.02044787,22 4,21.8738751 4,20.0833333 L4,3.91666667 C4,2.12612489 4.02044787,2 5.85714286,2 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"></path>
        <rect fill="#000000" x="6" y="11" width="9" height="2" rx="1"></rect>
        <rect fill="#000000" x="6" y="15" width="5" height="2" rx="1"></rect>
    </g>
</svg></a>',
                $xs['id'],

            ];
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        api_response($records);

        break;

    case 'exchange':
        $d = ORM::for_table('sys_accounts')->find_many();
        $p = ORM::for_table('crm_accounts')->find_many();
        $ui->assign('p', $p);
        $ui->assign('d', $d);
        $pms = ORM::for_table('sys_pmethods')->find_many();
        $ui->assign('pms', $pms);
        $ui->assign('mdate', $mdate);

        $currencies = ORM::for_table('sys_currencies')->find_array();
        $ui->assign('currencies', $currencies);

        view('transactions_exchange', []);

        break;

    case 'get_balance':
        $account_id = route(2);

        $balances = Balance::where('account_id', $account_id)->get();

        $txt = '';
        if ($balances) {
            $txt .= 'Current Balance: <br>';
            $occur = false;
            foreach ($balances as $balance) {
                $occur = true;
                $currency_id = $balance->currency_id;
                $currency = Currency::find($currency_id);
                $bal = $balance->balance;

                $txt .= $currency->iso_code . ': ' . $bal . ' <br>';
            }
        }

        if ($occur) {
            echo $txt;
        } else {
            echo 'This account does not have any balance';
        }

        break;

    case 'print':
        view('transactions_print');

        break;

    case 'receipt':
        $transaction_id = route(2);

        $transaction = Transaction::find($transaction_id);

        view('transactions_receipt');

        break;

    case 'uncleared':
        $transactions = Transaction::where('status', 'Uncleared')->get();

        view('transactions_uncleared', [
            'transactions' => $transactions,
        ]);

        break;

    case 'mark-cleared':
        $id = route(2);

        $transaction = Transaction::find($id);

        if ($transaction) {
            $transaction->status = 'Cleared';
            $transaction->save();

            r2(U . 'transactions/uncleared', 's', $_L['Data Updated']);
        }

        break;

    case 'bills':
        $days_before = date('Y-m-d', strtotime('-30 days'));
        $days_after = date('Y-m-d', strtotime('+30 days'));

        $today = date('Y-m-d');

        $bills_upcoming = Bill::whereBetween('next_date', [$today, $days_after])
            ->orderBy('next_date', 'asc')
            ->get();

        $bills_past_due = Bill::whereBetween('next_date', [
            $today,
            $days_before,
        ])
            ->orderBy('next_date', 'asc')
            ->where('is_paid', 0)
            ->get();

        view('transactions_bills', [
            'bills_upcoming' => $bills_upcoming,
            'bills_pas_due' => $bills_past_due,
        ]);

        break;

    case 'bills-all':
        $bills = Bill::orderBy('next_date', 'desc')->get();
        view('transactions_bills_all', [
            'bills' => $bills,
        ]);

        break;

    case 'bill':
        $bill = false;

        $id = route(2);

        if ($id != '') {
            $bill = Bill::find($id);
        }

        $categories = TransactionCategory::where('type', 'Expense')
            ->orderBy('sorder', 'asc')
            ->get();

        $contacts = Contact::getAllContacts();

        $accounts = Account::getAllAccounts();

        $currencies = Currency::all();
        $currencies_all = Currency::getAllCurrencies();

        view('transactions_bill', [
            'categories' => $categories,
            'contacts' => $contacts,
            'accounts' => $accounts,
            'currencies' => $currencies,
            'currencies_all' => $currencies_all,
            'bill' => $bill,
        ]);

        break;

    case 'bill-save':
        $validator = new Validator();
        $data = $request->all();

        $validation = $validator->validate($data, [
            'title' => 'required',
            'next_date' => 'required|date',
            'amount' => 'required',
            'currency' => 'required',
            'recurring_type' => 'required',
        ]);

        if ($validation->fails()) {
            $message = '';
            foreach ($validation->errors()->all() as $key => $value) {
                $message .= $value . ' <br> ';
            }
            echo $message;
            exit();
        } else {
            $currency_iso_code = _post('currency');

            $amount = _post('amount');

            $amount = createFromCurrency($amount, $currency_iso_code);

            $bill_id = _post('bill_id');

            $bill = false;

            if ($bill_id != '') {
                $bill = Bill::find($bill_id);
            }

            if (!$bill) {
                $bill = new Bill();
            }

            $bill->title = $data['title'];

            $bill->currency = $data['currency'];

            if (
                isset($data['from_account_id']) &&
                $data['from_account_id'] != ''
            ) {
                $bill->from_account_id = $data['from_account_id'];
            }

            if (isset($data['contact_id']) && $data['contact_id'] != '') {
                $bill->contact_id = $data['contact_id'];
            }

            if (isset($data['category_id']) && $data['category_id'] != '') {
                $bill->category_id = $data['category_id'];
            }

            if (isset($data['start_date']) && $data['start_date'] != '') {
                $bill->start_date = $data['start_date'];
            }

            if (isset($data['end_date']) && $data['end_date'] != '') {
                $bill->end_date = $data['end_date'];
            }

            $bill->next_date = $data['next_date'];

            $bill->net_amount = $amount;

            $bill->recurring_type = $data['recurring_type'];

            if (isset($data['website']) && $data['website'] != '') {
                $bill->website = $data['website'];
            }

            $bill->save();

            echo $bill->id;
        }

        break;

    case 'delete-bill':
        $id = route(2);

        $bill = Bill::find($id);

        if ($bill) {
            $bill->delete();
        }

        r2(U . 'transactions/bills', 's', $_L['delete_successful']);

        break;

    case 'bill-mark-as-paid':
        $id = route(2);

        $bill = Bill::find($id);

        if ($bill) {
            $bill->is_paid = 1;
            $bill->save();
        }

        r2(U . 'transactions/bills', 's', $_L['Data Updated']);

        break;

    case 'mass-delete':
        if (!has_access($user->roleid, 'transactions', 'all_data')) {
            permissionDenied();
        }
        if (!has_access($user->roleid, 'transactions', 'delete')) {
            permissionDenied();
        }
        $data = request()->all();
        $ids = $data['ids'] ?? '';
        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id !== 0) {
                $transaction = Transaction::find($id);
                if ($transaction) {
                    $transaction->delete();
                }
            }
        }
        break;

    default:
        echo 'action not defined';
}
