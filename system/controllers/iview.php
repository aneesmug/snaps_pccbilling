<?php

/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

use chillerlan\QRCode\QRCode;

$ui->assign('selected_navigation', 'invoices');
$ui->assign('_title', $_L['Accounts'] . '- ' . $config['CompanyName']);
$action = $routes['1'];
$id = $routes['2'];
$d = ORM::for_table('sys_invoices')->find($id);

if ($d) {
    $token = $routes['3'];
    $token = str_replace('token_', '', $token);
    $vtoken = $d['vtoken'];
    if ($token != $vtoken) {
        echo 'Sorry Token does not match!';
        exit();
    }

    $items = ORM::for_table('sys_invoiceitems')
        ->where('invoiceid', $id)
        ->order_by_asc('id')
        ->find_many();

    $trs_c = ORM::for_table('sys_transactions')
        ->where('iid', $id)
        ->count();

    $trs = ORM::for_table('sys_transactions')
        ->where('iid', $id)
        ->order_by_desc('id')
        ->find_many();

    $a = ORM::for_table('crm_accounts')->find($d['userid']);

    $i_credit = $d['credit'];
    $i_due = '0.00';
    $i_total = $d['total'];
    $i_due = $d['credit'] != '0.00' ? $i_total - $i_credit : $d['total'];

    $cf = ORM::for_table('crm_customfields')
        ->where('showinvoice', 'Yes')
        ->order_by_asc('id')
        ->find_many();

    $company = $a->cid != '' || $a->cid != 0 ? Company::find($a->cid) : false;

    $quote = false;

    if (isset($config['decimal_places_products_and_services'])) {
        $format_currency_override['precision'] =
            $config['decimal_places_products_and_services'];
    }

    $invoice_url = U . 'client/iview/' . $d->id . '/token_' . $d->vtoken;

    $zatca_enabled =
        !empty($config['zatca_enabled']) &&
        (string) $config['zatca_enabled'] === '1';

    if ($zatca_enabled && class_exists('Zatca')) {
        $zatca_qr_payload = Zatca::buildInvoiceQrBase64($d, $config);
        $qr_code = $zatca_qr_payload !== ''
            ? (new QRCode())->render($zatca_qr_payload)
            : '';
    } else {
        $qr_code = (new QRCode())->render($invoice_url);
    }

    $invoice_append_footer = null;

    $app->emit('client_invoice_printable', [&$d, &$a]);

    require APP_SYSTEM_PATH . '/lib/invoices/render.php';
} else {
    r2(U . 'customers/list', 'e', $_L['Account_Not_Found']);
}
