<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />

    <title><?php echo $_L['INVOICE']; ?> <?php echo $d['id']; ?></title>
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo APP_URL; ?>/application/storage/icon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL.'/'; ?>application/storage/icon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo APP_URL.'/'; ?>application/storage/icon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo APP_URL.'/'; ?>application/storage/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo APP_URL.'/'; ?>application/storage/icon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_URL.'/'; ?>application/storage/icon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo APP_URL.'/'; ?>application/storage/icon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo APP_URL.'/'; ?>application/storage/icon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <style>

        * { margin: 0; padding: 0; }
        body {
            font: 14px/1.4 Helvetica, Arial, sans-serif;
        }
        #page-wrap { width: 800px; margin: 0 auto; }

        textarea { border: 0; font: 14px Helvetica, Arial, sans-serif; overflow: hidden; resize: none; }
        table { border-collapse: collapse; }
        table td, table th { border: 1px solid black; padding: 5px; }

        #header { height: 15px; width: 100%; margin: 20px 0; background: #222; text-align: center; color: white; font: bold 15px Helvetica, Sans-Serif; text-decoration: uppercase; letter-spacing: 20px; padding: 8px 0px; }

        #address { width: 250px; height: 150px; float: left; }
        #customer { overflow: hidden; }

        #logo { text-align: right; float: right; position: relative; margin-top: 25px; border: 1px solid #fff; max-width: 540px; overflow: hidden; }
        #customer-title { font-size: 20px; font-weight: bold; float: left; }

        #meta { margin-top: 1px; width: 100%; float: right; }
        #meta td { text-align: right;  }
        #meta td.meta-head { text-align: left; background: #eee; }
        #meta td textarea { width: 100%; height: 20px; text-align: right; }

        #items { clear: both; width: 100%; margin: 30px 0 0 0; border: 1px solid black; }
        #items th { background: #eee; }
        #items textarea { width: 80px; height: 50px; }
        #items tr.item-row td {  vertical-align: top; }
        #items td.description { width: 300px; }
        #items td.item-name { width: 175px; }
        #items td.description textarea, #items td.item-name textarea { width: 100%; }
        #items td.total-line { border-right: 0; text-align: right; }
        #items td.total-value { border-left: 0; padding: 10px; }
        #items td.total-value textarea { height: 20px; background: none; }
        #items td.balance { background: #eee; }
        #items td.blank { border: 0; }

        #terms { text-align: center; margin: 20px 0 0 0; }
        #terms h5 { text-transform: uppercase; font: 13px Helvetica, Sans-Serif; letter-spacing: 10px; border-bottom: 1px solid black; padding: 0 0 8px 0; margin: 0 0 8px 0; }
        #terms textarea { width: 100%; text-align: center;}



        .delete-wpr { position: relative; }
        .delete { display: block; color: #000; text-decoration: none; position: absolute; background: #EEEEEE; font-weight: bold; padding: 0px 3px; border: 1px solid; top: -6px; left: -22px; font-family: Verdana; font-size: 12px; }

        /* Extra CSS for Print Button*/
        .button {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            overflow: hidden;
            margin-top: 20px;
            padding: 12px 12px;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-transition: all 60ms ease-in-out;
            transition: all 60ms ease-in-out;
            text-align: center;
            white-space: nowrap;
            text-decoration: none !important;

            color: #fff;
            border: 0 none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.3;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;

            -webkit-box-pack: center;
            -webkit-justify-content: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-flex: 0;
            -webkit-flex: 0 0 160px;
            -ms-flex: 0 0 160px;
            flex: 0 0 160px;
        }
        .button:hover {
            -webkit-transition: all 60ms ease;
            transition: all 60ms ease;
            opacity: .85;
        }
        .button:active {
            -webkit-transition: all 60ms ease;
            transition: all 60ms ease;
            opacity: .75;
        }
        .button:focus {
            outline: 1px dotted #959595;
            outline-offset: -4px;
        }

        .button.-regular {
            color: #202129;
            background-color: #edeeee;
        }
        .button.-regular:hover {
            color: #202129;
            background-color: #e1e2e2;
            opacity: 1;
        }
        .button.-regular:active {
            background-color: #d5d6d6;
            opacity: 1;
        }

        .button.-dark {
            color: #FFFFFF;
            background: #333030;
        }
        .button.-dark:focus {
            outline: 1px dotted white;
            outline-offset: -4px;
        }

        @media print
        {
            .no-print, .no-print *
            {
                display: none !important;
            }
        }

        .refund-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 92px;
            font-weight: 700;
            letter-spacing: 6px;
            color: rgba(192, 57, 43, 0.15);
            text-transform: uppercase;
            pointer-events: none;
            z-index: 999;
            white-space: nowrap;
        }

    </style>

</head>

<body>

<?php
$_c = isset($config) && is_array($config) ? $config : [];

$to_float = function ($value) {
    if (is_int($value) || is_float($value)) {
        return (float) $value;
    }

    $value = trim((string) $value);
    if ($value === '') {
        return 0.0;
    }

    $value = str_replace(["\xC2\xA0", ' '], '', $value);

    // Keep digits, minus, comma and dot only.
    $value = preg_replace('/[^0-9,\.\-]/', '', $value);

    // If both separators exist, assume comma is thousands separator.
    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
        $value = str_replace(',', '', $value);
    } elseif (strpos($value, ',') !== false) {
        // If only comma exists, treat as decimal separator.
        $value = str_replace(',', '.', $value);
    }

    return is_numeric($value) ? (float) $value : 0.0;
};

$_effective_invoice_type = $d['zatca_invoice_type'] ?? '';
if (empty($_effective_invoice_type)) {
    $_buyer = ORM::for_table('crm_accounts')->find($d['userid']);
    $_buyer_type = $_buyer ? strtolower(trim((string) $_buyer['buyer_type'])) : '';
    if ($_buyer_type === 'individual') {
        $_effective_invoice_type = 'simplified';
    } elseif ($_buyer_type === 'company') {
        $_effective_invoice_type = 'standard';
    } else {
        $_effective_invoice_type = isset($config['zatca_invoice_type']) ? $config['zatca_invoice_type'] : 'simplified';
    }
}
$invoice_type_label = $_effective_invoice_type === 'standard' ? 'Standard Tax Invoice' : 'Simplified Tax Invoice';
$issue_timestamp = !empty($zatca_data['timestamp'])
    ? date('Y-m-d H:i:s', strtotime($zatca_data['timestamp']))
    : date('Y-m-d H:i:s', strtotime($d['date']));
$zatca_report_for_qr = class_exists('ZatcaPhase2') ? ZatcaPhase2::buildInvoiceRegistrationReport($d) : ['is_registered' => false];
$show_zatca_qr = !empty($_c['invoice_show_qr_code']) && (string) $_c['invoice_show_qr_code'] === '1' && !empty($qr_code);
$zatca_qr_phase_label = 'TLV Phase 1';
if (class_exists('Zatca') && method_exists('Zatca', 'getInvoiceQrPhaseLabel')) {
    $zatca_qr_phase_label = Zatca::getInvoiceQrPhaseLabel($d, $_c);
} elseif (!empty($zatca_report_for_qr['is_registered'])) {
    $zatca_qr_phase_label = 'Phase 2';
}
// Compute display totals from filtered items (after return deductions)
$_disp_sub = 0.0; $_disp_tax_sum = 0.0; $_inv_taxrate = $to_float(isset($d['taxrate']) ? $d['taxrate'] : 0);
foreach ($items as $_di) {
    $_lt = $to_float(isset($_di['total']) ? $_di['total'] : 0);
    $_disp_sub += $_lt;
    if (!isset($_di['taxed']) || (string) $_di['taxed'] !== '0') {
        $_line_tax_rate = 0.0;
        $_item_tax_rate = $to_float(isset($_di['tax_rate']) ? $_di['tax_rate'] : 0);
        if ($_item_tax_rate > 0) {
            $_line_tax_rate = $_item_tax_rate;
        } elseif ($_inv_taxrate > 0) {
            $_line_tax_rate = $_inv_taxrate;
        }

        if ($_line_tax_rate > 0) {
            $_disp_tax_sum += round($_lt * ($_line_tax_rate / 100), 2);
        }
    }
}
$display_subtotal = round($_disp_sub, 2);
$display_tax = round($_disp_tax_sum, 2);

// Fallback to invoice tax values when per-item rate is unavailable.
if ($display_tax <= 0) {
    $invoice_tax_total = $to_float(isset($d['tax_total']) ? $d['tax_total'] : 0);
    $invoice_tax = $to_float(isset($d['tax']) ? $d['tax'] : 0);

    if ($invoice_tax_total > 0) {
        $display_tax = round($invoice_tax_total, 2);
    } elseif ($invoice_tax > 0) {
        $display_tax = round($invoice_tax, 2);
    }
}

$display_total = round($display_subtotal + $display_tax, 2);

// Resolve a display VAT rate even when invoice-level taxrate is empty.
$invoice_taxrate_display = (float) $d['taxrate'];
if ($invoice_taxrate_display <= 0 && $display_subtotal > 0 && $display_tax > 0) {
    $invoice_taxrate_display = round(($display_tax / $display_subtotal) * 100, 2);
}
if (method_exists('Finance', 'amount_to_words_bilingual')) {
    $amount_words = call_user_func(['Finance', 'amount_to_words_bilingual'], $display_total);
} else {
    $fallback_words = Finance::convert_number_to_words((float) $display_total);
    $amount_words = [
        'en' => $fallback_words,
        'ar' => $fallback_words,
    ];
}
    $seller_vat = isset($zatca_data['seller_vat']) && trim((string) $zatca_data['seller_vat']) !== ''
        ? (string) $zatca_data['seller_vat']
        : (class_exists('Zatca') ? Zatca::resolveSellerVatNumber($config) : (isset($config['vat_number']) ? preg_replace('/\D+/', '', (string) $config['vat_number']) : ''));
$seller_crn = isset($zatca_data['seller_crn']) && trim((string) $zatca_data['seller_crn']) !== ''
    ? (string) $zatca_data['seller_crn']
    : Zatca::resolveSellerCrn($config);
$logo_file = !empty($config['logo_default']) ? $config['logo_default'] : 'logo.png';
$logo_src = APP_URL . '/storage/system/' . $logo_file;
$print_due_amount = isset($i_due) ? (float) $i_due : ((float) $d['total'] - (float) $d['credit']);
$is_refunded_credit_note =
    isset($d['type']) &&
    (string) $d['type'] === 'Credit Note' &&
    ($print_due_amount <= 0.0001 || strcasecmp((string) $d['status'], 'Paid') === 0);
$is_cancelled_invoice = isset($d['status']) && strcasecmp((string) $d['status'], 'Cancelled') === 0;
?>

<div id="page-wrap">
    <?php if ($is_refunded_credit_note) { ?>
        <div class="refund-watermark">REFUNDED</div>
    <?php } elseif ($is_cancelled_invoice) { ?>
        <div class="refund-watermark">CANCELED</div>
    <?php } ?>

    <div style="display: none;">

    <table width="100%">
        <tr>
            <td style="border: 0;  text-align: left" width="62%">
                <img id="image" src="<?php echo $logo_src; ?>" alt="logo" />
                <br><br>
                <span style="font-size: 12px; color: #666;"><strong>Invoice Type:</strong> <?php echo $invoice_type_label; ?></span>
                <br><br>
                <span style="font-size: 18px; color: #2f4f4f"><strong><?php echo $_L['INVOICE']; ?> # <?php
                        if($d['cn'] != ''){
                            $dispid = $d['cn'];
                        }
                        else{
                            $dispid = $d['id'];
                        }
                        echo $d['invoicenum'].$dispid;
                        ?></strong></span>
            </td>
            <td style="border: 0;  text-align: right" width="62%">
                <div id="logo">
                    <strong><?=$_c['CompanyName']?></strong>
                    <br>
                    <?php echo htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($_c['caddress'])))); ?>
                </div>
            </td>
        </tr>



    </table>

    <hr>
    <br>

    <div style="clear:both"></div>

    <div id="customer">

        <table id="meta">
            <tr>
                <td rowspan="5" style="border: 1px solid white; border-right: 1px solid black; text-align: left" width="62%">
                    <strong><?php echo $_L['Invoiced To']; ?></strong> <br>
                    <?php if($a['company'] != '') {
                        ?>
                        <?php echo $a['company']; ?> <br>
                       <?php echo $_L['ATTN']; ?>: <?php echo $a['account']; ?> <br>
                    <?php
                    }
                    else{
                        ?>
                        <?php echo $d['account']; ?> <br>
                    <?php
                    }
                    ?>

                    <?php echo $a['address']; ?> <br>
                    <?php echo $a['city']; ?> <?php echo $a['state']; ?> <?php echo $a['zip']; ?> <br>
                    <?php echo $a['country']; ?> <br>
                    <?php
                    if(($a['phone']) != ''){
                        echo 'Phone: '. $a['phone']. ' <br>';
                    }
                    if(($a['email']) != ''){
                        echo 'Email: '. $a['email']. ' <br>';
                    }
                    foreach ($cf as $cfs){
                        echo $cfs['fieldname'].': '. get_custom_field_value($cfs['id'],$a['id']). ' <br>';
                    }
                    ?></td>
                <td class="meta-head"><?php echo $_L['INVOICE']; ?> #</td>
                <td><?php echo $d['invoicenum'].$dispid; ?></td>
            </tr>
            <tr>

                <td class="meta-head"><?php echo $_L['Status']; ?></td>
                <td><?php
                    echo ib_lan_get_line($d['status']);
                    ?></td>
            </tr>
            <tr>

                <td class="meta-head"><?php echo $_L['Invoice Date']; ?></td>
                <td><?php echo date($config['df'], strtotime($d['date'])); ?></td>
            </tr>
            <tr>

                <td class="meta-head"><?php echo $_L['Due Date']; ?></td>
                <td><?php echo date($config['df'], strtotime($d['duedate'])); ?></td>
            </tr>

            <tr>

                <td class="meta-head"><?php echo $_L['Amount Due']; ?></td>
                <td><div class="due"><?php echo ib_money_format($i_due,$config,$d['currency_symbol']) ?></div></td>
            </tr>

        </table>

    </div>

    <table width="100%" style="margin: 20px 0 25px; border: 1px solid #dfe7eb; background: #fbfdfd;">
        <tr>
            <td width="35%" style="vertical-align: top; background: #fff;">
                <h4 style="margin: 0 0 10px; color: #2f4f4f;">Seller Information</h4>
                <p><strong>Seller Name:</strong> <?php echo htmlspecialchars(isset($zatca_data['seller_name']) ? $zatca_data['seller_name'] : $config['CompanyName']); ?></p>
                <p><strong>Seller VAT Number:</strong> <?php echo htmlspecialchars(isset($zatca_data['seller_vat']) ? $zatca_data['seller_vat'] : ''); ?></p>
                <p><strong>Seller Address:</strong> <?php echo htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($_c['caddress'])))); ?></p>
                <p><strong>Date and Time of Issue:</strong> <?php echo htmlspecialchars($issue_timestamp); ?></p>
            </td>
            <td width="35%" style="vertical-align: top; background: #fff;">
                <h4 style="margin: 0 0 10px; color: #2f4f4f;">Invoice Information</h4>
                <p><strong>Invoice Type:</strong> <?php echo $invoice_type_label; ?></p>
                <p><strong>Invoice Reference Number:</strong> <?php echo htmlspecialchars(isset($zatca_data['invoice_number']) ? $zatca_data['invoice_number'] : ($d['invoicenum'] . $dispid)); ?></p>
                <?php if (!empty($zatca_data['uuid'])) { ?>
                    <p><strong>Invoice UUID:</strong> <?php echo htmlspecialchars($zatca_data['uuid']); ?></p>
                <?php } ?>
                <?php if (!empty($a['tax_number'])) { ?>
                    <p><strong>Buyer VAT Number:</strong> <?php echo htmlspecialchars($a['tax_number']); ?></p>
                <?php } ?>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($a['company'] !== '' ? $a['company'] : $d['account']); ?></p>
            </td>
            <td width="30%" style="vertical-align: top; text-align: center; background: #fff;">
                <h4 style="margin: 0 0 10px; color: #2f4f4f;">ZATCA QR Code</h4>
                <?php if ($show_zatca_qr) { ?>
                    <img src="<?=$qr_code?>" style="width: 180px; height: auto; aspect-ratio: 1 / 1; object-fit: contain; max-width: 100%; margin-bottom: 10px;">
                <?php } else { ?>
                    <p style="margin: 8px 0 12px; color: #999;">QR code is disabled in settings.</p>
                <?php } ?>
                <p><strong>Subtotal (Excl. VAT):</strong> <?php echo ib_money_format($display_subtotal,$config,$d['currency_symbol']); ?></p>
                <p><strong>VAT Amount:</strong> <?php echo ib_money_format($display_tax,$config,$d['currency_symbol']); ?></p>
                <p><strong>Total (Incl. VAT):</strong> <?php echo ib_money_format($display_total,$config,$d['currency_symbol']); ?></p>
            </td>
        </tr>
    </table>

    <table id="items">

        <tr>
            <th width="65%"><?php echo $_L['Item']; ?></th>
            <th align="right"><?php echo $_L['Price']; ?></th>
            <th align="right"><?php echo $_L['Qty']; ?></th>
            <th align="right"><?php echo $_L['Total']; ?></th>

        </tr>



        <?php

        foreach ($items as $item){
            echo '  <tr class="item-row">


            <td class="description">'.$item['description'].'</td>
            <td align="right">'.ib_money_format($item['amount'],$config,$d['currency_symbol']).'</td>
            <td align="right">'.$item['qty'].'</td>
            <td align="right"><span class="price">'.ib_money_format($item['total'],$config,$d['currency_symbol']).'</span></td>
        </tr>';
        }

        ?>


            <tr>
                <td class="blank"> </td>
                <td colspan="2" class="total-line"><?php echo $_L['Sub Total']; ?></td>
                <td class="total-value"><div id="subtotal"><?php echo ib_money_format($display_subtotal,$config,$d['currency_symbol']); ?></div></td>
            </tr>
            <?php
            if(($d['discount']) != '0.00'){

           ?>
            <tr>
                <td class="blank"> </td>
                <td colspan="2" class="total-line"><?php echo $_L['Discount']; ?>
                    <?php
                    if($d['discount_type'] == 'p'){
                        echo '('.$d['discount_value'].')%';
                    }
                    ?>
                </td>
                <td class="total-value"><div id="subtotal"><?php echo ib_money_format($d['discount'],$config,$d['currency_symbol']); ?></div></td>
            </tr>
                <?php
            }
            ?>
        <?php
        if (($d['tax']) != '0.00'){
            ?>
            <tr>

                <td class="blank"> </td>
                <td colspan="2" class="total-line"><?php echo $_L['TAX']; ?></td>
                <td class="total-value"><div id="total"><?php echo ib_money_format($display_tax,$config,$d['currency_symbol']); ?></div></td>
            </tr>
        <?php
        }
        ?>

        <?php
        if($d['credit'] != '0.00'){
            ?>
            <tr>
                <td class="blank"> </td>
                <td colspan="2" class="total-line"><?php echo $_L['Invoice Total']; ?></td>
                <td class="total-value"><div class="due"><?php echo ib_money_format($display_total,$config,$d['currency_symbol']); ?></div></td>
            </tr>
            <tr>
                <td class="blank"> </td>
                <td colspan="2" class="total-line"><?php echo $_L['Total Paid']; ?></td>
                <td class="total-value"><div class="due"><?php echo ib_money_format($d['credit'],$config,$d['currency_symbol']); ?></div></td>
            </tr>
            <tr>
                <td class="blank"> </td>
                <td colspan="2" class="total-line balance"><?php echo $_L['Amount Due']; ?></td>
                <td class="total-value balance"><div class="due"><?php echo ib_money_format($i_due,$config,$d['currency_symbol']) ?></div></td>
            </tr>
        <?php
        }
        else{
            ?>
            <tr>
                <td class="blank"> </td>
                <td colspan="2" class="total-line balance"><?php echo $_L['Grand Total']; ?></td>
                <td class="total-value balance"><div class="due"><?php echo ib_money_format($display_total,$config,$d['currency_symbol']); ?></div></td>
            </tr>
        <?php
        }
        ?>

    </table>

    </div>

    <div style="border: 1px solid #ddd; border-radius: 0; background: #fff; padding: 12px 12px 8px; margin-bottom: 20px;">
        <table width="100%" style="border: 0; margin-bottom: 18px;">
            <tr>
                <td style="border: 0; width: 22%; text-align: center; vertical-align: top;">
                    <img id="image" src="<?php echo $logo_src; ?>" alt="logo" style="max-width: 120px; max-height: 50px; margin-bottom: 6px;" /><br/>
                    <?php if ($show_zatca_qr) { ?>
                        <div style="border: 1px solid #aaa; padding: 3px; display: inline-block; border-radius: 0;">
                            <img src="<?=$qr_code?>" style="width: 170px; height: auto; aspect-ratio: 1 / 1; object-fit: contain; max-width: 100%;" />
                        </div>
                    <?php } else { ?>
                        <div style="border: 1px dashed #aaa; padding: 10px; display: inline-block; border-radius: 0; color: #999; font-size: 10px;">QR code is disabled in settings.</div>
                    <?php } ?>
                </td>
                <td style="border: 0; width: 78%; vertical-align: top; padding-left: 12px;">
                    <!-- Company name top-right -->
                    <table width="100%" style="border: 0; margin-bottom: 10px;">
                        <tr>
                            <td style="border: 0; text-align: right; vertical-align: top;">
                                <div style="font-size: 18px; font-weight: 700; color: #222;"><?php echo htmlspecialchars($_c['CompanyName']); ?></div>
                                <div style="font-size: 10px; line-height: 1.4; margin-top: 3px; margin-bottom: 8px; color: #444; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($_c['caddress'])))); ?></div>
                            </td>
                        </tr>
                    </table>
                    <!-- Invoice type + Ref/Date below company -->
                    <div style="border: 1px solid #aaa; padding: 5px 8px; border-radius: 0; text-align: right; margin-top: 40px; margin-bottom: 3px;">
                        <div style="font-size: 13px; font-weight: 700; color: #333;"><?php echo $invoice_type_label === 'Standard Tax Invoice' ? 'فاتورة ضريبية' : 'فاتورة ضريبية مبسطة'; ?></div>
                        <div style="font-size: 9px; margin-top: 2px; color: #666;"><?php echo $invoice_type_label; ?></div>
                    </div>
                    <table width="100%" style="border-collapse: collapse; border: 1px solid #aaa;">
                        <tr>
                            <td style="border: 0; border-right: 1px solid #aaa; width: 50%; padding: 3px 6px; vertical-align: top;">
                                <div style="font-size: 7px; color: #666; margin-bottom: 2px;">Invoice Reference Number</div>
                                <div style="font-size: 10px; font-weight: 700; color: #333;"><?php echo htmlspecialchars(isset($zatca_data['invoice_number']) ? $zatca_data['invoice_number'] : ($d['invoicenum'] . $dispid)); ?></div>
                            </td>
                            <td style="border: 0; width: 50%; padding: 3px 6px; vertical-align: top;">
                                <div style="font-size: 7px; color: #666; margin-bottom: 2px;">Date and Time of Invoice Issuance</div>
                                <div style="font-size: 10px; font-weight: 700; color: #333;"><?php echo date('Y/m/d H:i:s', strtotime($issue_timestamp)); ?></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table width="100%" style="margin-bottom: 4px; border: 1px solid #aaa; border-collapse: collapse;">
            <tr>
                <td colspan="4" style="text-align: right; font-size: 11px; font-weight: 700; color: #333; padding: 5px 8px; background: #f2f2f2;">Seller Information</td>
            </tr>
            <tr>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Seller Name</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700;"><?php echo htmlspecialchars(isset($zatca_data['seller_name']) ? $zatca_data['seller_name'] : $config['CompanyName']); ?></div></td>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Seller Address</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($_c['caddress'])))); ?></div></td>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Seller VAT Registration Number</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700;"><?php echo htmlspecialchars($seller_vat !== '' ? $seller_vat : '----'); ?></div></td>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Additional Seller ID</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700;"><?php echo htmlspecialchars($seller_crn !== '' ? $seller_crn : '----'); ?></div></td>
            </tr>
        </table>

        <table width="100%" style="margin-bottom: 4px; border: 1px solid #aaa; border-collapse: collapse;">
            <tr>
                <td colspan="4" style="text-align: right; font-size: 11px; font-weight: 700; color: #333; padding: 5px 8px; background: #f2f2f2;">Buyer Information</td>
            </tr>
            <tr>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Buyer Name</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700;"><?php echo htmlspecialchars($a['company'] !== '' ? $a['company'] : $d['account']); ?></div></td>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Buyer Address</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700;"><?php echo htmlspecialchars(trim($a['address'] . ' ' . $a['city'] . ' ' . $a['state'] . ' ' . $a['zip'] . ' ' . $a['country'])); ?></div></td>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Buyer VAT Registration Number</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700;"><?php echo !empty($a['tax_number']) ? htmlspecialchars($a['tax_number']) : '----'; ?></div></td>
                <td style="width: 25%; vertical-align: top; padding: 3px 6px;"><div style="font-size: 8px; color: #666;">Additional Buyer ID</div><div style="font-size: 10px; color: #333; margin-top: 2px; font-weight: 700;"><?php echo !empty($a['entity_number']) ? htmlspecialchars($a['entity_number']) : '----'; ?></div></td>
            </tr>
        </table>

        <table width="100%" style="margin-bottom: 4px; border: 1px solid #aaa; border-collapse: collapse;">
            <tr style="background: #f2f2f2; color: #333; font-weight: 700; font-size: 12px !important;">
                <th>Product</th>
                <th align="center">Unit Price</th>
                <th align="center">Quantity</th>
                <th align="center">Subtotal Excl. VAT</th>
                <th align="center">VAT Rate</th>
                <th align="center">VAT Amount</th>
                <th align="center">Total Incl. VAT</th>
            </tr>
            <?php foreach ($items as $item) {
                $line_tax_rate = 0.0;
                if (!(isset($item['taxed']) && (string) $item['taxed'] === '0')) {
                    if (isset($item['tax_rate']) && is_numeric($item['tax_rate']) && (float) $item['tax_rate'] > 0) {
                        $line_tax_rate = (float) $item['tax_rate'];
                    } else {
                        $line_tax_rate = $invoice_taxrate_display;
                    }
                }
                $line_tax_amount = $line_tax_rate > 0 ? round(((float) $item['total'] * $line_tax_rate) / 100, 2) : 0.0;
                $line_total_with_tax = (float) $item['total'] + $line_tax_amount;
            ?>
            <tr>
                <td style="padding: 3px 6px; font-size: 10px !important;"><?php echo htmlspecialchars($item['description']); ?></td>
                <td align="center" style="padding: 3px 6px; font-size: 10px !important;"><?php echo ib_money_format($item['amount'],$config,$d['currency_symbol']); ?></td>
                <td align="center" style="padding: 3px 6px; font-size: 10px !important;"><?php echo htmlspecialchars($item['qty']); ?></td>
                <td align="center" style="padding: 3px 6px; font-size: 10px !important;"><?php echo ib_money_format($item['total'],$config,$d['currency_symbol']); ?></td>
                <td align="center" style="padding: 3px 6px; font-size: 10px !important;"><?php echo htmlspecialchars(number_format($line_tax_rate, 2)) . '%'; ?></td>
                <td align="center" style="padding: 3px 6px; font-size: 10px !important;"><?php echo ib_money_format($line_tax_amount,$config,$d['currency_symbol']); ?></td>
                <td align="center" style="padding: 3px 6px; font-size: 10px !important;"><?php echo ib_money_format($line_total_with_tax,$config,$d['currency_symbol']); ?></td>
            </tr>
            <?php } ?>
        </table>

        <table width="100%" style="border: 0; margin-bottom: 6px;">
            <tr>
                <td style="border: 0; width: 55%;"></td>
                <td style="border: 1px solid #aaa; padding: 4px 8px; font-size: 10px; font-weight: 700; color: #333; width: 45%;">
                    <span style="float: right;<?php if ($is_cancelled_invoice) { echo ' color:#c0392b;font-weight:700;'; } ?>"><?php if ($is_cancelled_invoice) { echo '- '; } ?><?php echo ib_money_format($display_subtotal,$config,$d['currency_symbol']); ?></span>
                    <span>Total Excluding VAT</span>
                </td>
            </tr>
            <tr>
                <td style="border: 0;"></td>
                <td style="border: 1px solid #aaa; padding: 4px 8px; font-size: 10px; font-weight: 700; color: #333; width: 45%;">
                    <span style="float: right;<?php if ($is_cancelled_invoice) { echo ' color:#c0392b;font-weight:700;'; } ?>"><?php if ($is_cancelled_invoice) { echo '- '; } ?><?php echo ib_money_format($display_tax,$config,$d['currency_symbol']); ?></span>
                    <?php if ($invoice_taxrate_display > 0) { ?>
                        <span>VAT Total (<?php echo htmlspecialchars(number_format($invoice_taxrate_display, 2)); ?>%)</span>
                    <?php } else { ?>
                        <span>VAT Total</span>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td style="border: 0;"></td>
                <td style="border: 1px solid #aaa; padding: 4px 8px; font-size: 11px; font-weight: 700; color: #333; width: 45%; background: #f8f8f8;">
                    <span style="float: right;<?php if ($is_cancelled_invoice) { echo ' color:#c0392b;font-weight:700;'; } ?>"><?php if ($is_cancelled_invoice) { echo '- '; } ?><?php echo ib_money_format($display_total,$config,$d['currency_symbol']); ?></span>
                    <span>Total Including VAT</span>
                </td>
            </tr>
        </table>
    </div>

<!--    related transactions -->

    <?php
    if ($trs_c != ''){
        ?>
        <br>
        <h5><?php echo $_L['Related Transactions']; ?>: </h5>
        <table id="related_transactions" style="width: 100%">

            <tr style="background: #f2f2f2; color: #333; font-weight: 700; font-size: 12px !important;">
                <th align="left" width="20%"><?php echo $_L['Date']; ?></th>
                <th align="left"><?php echo $_L['Account']; ?></th>
                <th width="50%" align="left"><?php echo $_L['Description']; ?></th>
                <th align="right"><?php echo $_L['Amount']; ?></th>

            </tr>



            <?php

            foreach ($trs as $tr){
                echo '  <tr class="item-row">


            <td align="left">'.date( $config['df'], strtotime($tr['date'])).'</td>
            <td align="left">'.$tr['account'].'</td>
            <td align="left">'.$tr['description'].'</td>
            <td align="right"><span class="price">'.ib_money_format($tr['amount'],$config,$d['currency_symbol']).'</span></td>
        </tr>';
            }

            ?>


        </table>
    <?php
    }
    ?>

<!--    end related transactions -->

    <?php
    if($d['notes'] != ''){

        ?>
        <div id="terms">
            <h5><?php echo $_L['Terms']; ?></h5>
            <?php echo $d['notes']; ?>
        </div>
    <?php
    }
    ?>

    <div style="margin-top: 30px; border-top: 1px solid #aaa; padding-top: 4px;">
        <div style="font-size: 8px; margin-bottom: 3px;">
            <strong>Amount in words:</strong> <?php echo htmlspecialchars(ucfirst($amount_words['en'])); ?>
            <span style="float: right; direction: rtl; text-align: right;"><strong>المبلغ كتابةً:</strong> <?php echo htmlspecialchars($amount_words['ar']); ?></span>
        </div>
        <?php if(isset($config['invoice_footer_html']) && !empty($config['invoice_footer_html'])){ ?>
            <div style="font-size: 8px; margin-top: 4px;"><?php echo $config['invoice_footer_html']; ?></div>
        <?php } ?>
        <div style="font-size: 7px; color: #999; text-align: right; margin-top: 3px;">Page 1 of 1</div>
    </div>

    <button class='button -dark center no-print'  onClick="window.print();">Click Here to Print</button>
</div>

</body>

</html>