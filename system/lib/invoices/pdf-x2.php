<?php
if ($config['rtl'] == 1) { ?>
<html dir="rtl">
<?php } else { ?>
<html>
<?php } ?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    @page {
        margin-top: 83mm;
        margin-bottom: 25mm;
        margin-left: 8mm;
        margin-right: 8mm;
        header: page-header;
        footer: page-footer;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: xbriyaz, dejavusanscondensed; font-size: 9px; line-height: 1.3; color: #222; }
    table { border-collapse: collapse; width: 100%; }
    table td, table th { border: 1px solid #888; padding: 3px 5px; font-size: 9px; }

    .no-border td, .no-border th { border: 0; }
    .section-title { text-align: right; font-size: 11px; font-weight: 700; color: #333; padding: 5px 8px; background: #f2f2f2; }
    .label-cell { font-size: 8px; color: #666; }
    .value-cell { font-size: 10px; color: #333; font-weight: 700; margin-top: 2px; }
    .items-header th { background: #f2f2f2; font-weight: 700; font-size: 9px; padding: 4px 6px; }
    .items-row td { padding: 3px 6px; vertical-align: top; }
    .grand-total { background: #f8f8f8; font-weight: 700; }
    .terms-box { margin-top: 8px; font-size: 8px; }

    <?php if ($config['rtl'] == 1) { ?>
    .section-title { text-align: left; }
    <?php } ?>
</style>
</head>
<body>

<?php
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
$invoice_type_ar = $_effective_invoice_type === 'standard'
    ? "\u{0641}\u{0627}\u{062A}\u{0648}\u{0631}\u{0629} \u{0636}\u{0631}\u{064A}\u{0628}\u{064A}\u{0629}"
    : "\u{0641}\u{0627}\u{062A}\u{0648}\u{0631}\u{0629} \u{0636}\u{0631}\u{064A}\u{0628}\u{064A}\u{0629} \u{0645}\u{0628}\u{0633}\u{0637}\u{0629}";

$issue_timestamp = !empty($zatca_data['timestamp'])
    ? date('Y-m-d H:i:s', strtotime($zatca_data['timestamp']))
    : date('Y-m-d H:i:s', strtotime($d['date']));

$qr_code_image = isset($qr_code) ? $qr_code : '';
$show_zatca_qr = !empty($config['invoice_show_qr_code']) && $config['invoice_show_qr_code'] == '1' && !empty($qr_code_image);
$zatca_qr_phase_label = 'TLV Phase 1';
if (class_exists('Zatca') && method_exists('Zatca', 'getInvoiceQrPhaseLabel')) {
    $zatca_qr_phase_label = Zatca::getInvoiceQrPhaseLabel($d, $config);
}

$display_subtotal = round((float) $d['subtotal'], 2);
$display_tax = round((float) $d['tax'], 2);
$display_total = round((float) $d['total'], 2);
$_inv_taxrate = (float) $d['taxrate'];
$is_cancelled_invoice = isset($d['status']) && strcasecmp((string) $d['status'], 'Cancelled') === 0;
$neg_style = $is_cancelled_invoice ? ' color:#c0392b;' : '';
$neg_prefix = $is_cancelled_invoice ? '- ' : '';

if (is_callable(['Finance', 'amount_to_words_bilingual'])) {
    $amount_words = call_user_func(['Finance', 'amount_to_words_bilingual'], $display_total);
} else {
    $fallback_words = Finance::convert_number_to_words((float) $display_total);
    $amount_words = [
        'en' => $fallback_words,
        'ar' => 'صفر',
    ];
}

if (!isset($amount_words['ar']) || trim((string) $amount_words['ar']) === '' || $amount_words['ar'] === $amount_words['en']) {
    if (is_callable(['Finance', 'amount_to_words_bilingual'])) {
        $resolved_words = call_user_func(['Finance', 'amount_to_words_bilingual'], $display_total);
        if (isset($resolved_words['ar']) && trim((string) $resolved_words['ar']) !== '') {
            $amount_words['ar'] = $resolved_words['ar'];
        }
    }
}

$dispid = $d['cn'] != '' ? $d['cn'] : $d['id'];
$invoice_ref = isset($zatca_data['invoice_number']) ? $zatca_data['invoice_number'] : ($d['invoicenum'] . $dispid);
$seller_name = isset($zatca_data['seller_name']) ? $zatca_data['seller_name'] : $config['CompanyName'];
$seller_vat = isset($zatca_data['seller_vat']) && trim((string) $zatca_data['seller_vat']) !== ''
    ? (string) $zatca_data['seller_vat']
    : (class_exists('Zatca') ? Zatca::resolveSellerVatNumber($config) : (isset($config['vat_number']) ? preg_replace('/\D+/', '', (string) $config['vat_number']) : ''));
$seller_crn = isset($zatca_data['seller_crn']) && trim((string) $zatca_data['seller_crn']) !== ''
    ? (string) $zatca_data['seller_crn']
    : (class_exists('Zatca') ? Zatca::resolveSellerCrn($config) : '');

$logo_file = !empty($config['logo_default']) ? $config['logo_default'] : 'logo.png';
$logo_path = APP_URL . '/storage/system/' . $logo_file;
?>

<htmlpageheader name="page-header">
<table class="no-border" style="width:100%; margin-bottom:8px;">
    <tr>
        <td style="border:0; width:45%; vertical-align:middle; text-align:left;">
            <img src="<?php echo htmlspecialchars($logo_path); ?>" style="width:190px; max-height:110px;" />
        </td>
        <td style="border:0; width:55%; vertical-align:middle; text-align:right;">
            <div style="font-size:22px; font-weight:700; color:#222;"><?php echo htmlspecialchars($config['CompanyName']); ?></div>
            <div style="font-size:8px; color:#555; line-height:1.5; margin-top:3px;"><?php echo nl2br(htmlspecialchars(strip_tags($config['caddress']))); ?></div>
        </td>
    </tr>
</table>

<div style="border-top:2px solid #333; margin-bottom:8px;"></div>

<table style="width:100%; border-collapse:collapse; border:1px solid #aaa; margin-bottom:4px;">
    <tr>
        <td style="border:0; border-right:1px solid #aaa; width:34%; padding:6px 10px; text-align:center; vertical-align:middle;">
            <div style="font-size:13px; font-weight:700; color:#333;"><?php echo $invoice_type_ar; ?></div>
            <div style="font-size:9px; margin-top:2px; color:#666;"><?php echo $invoice_type_label; ?></div>
        </td>
        <td style="border:0; border-right:1px solid #aaa; width:33%; padding:6px 10px; text-align:center; vertical-align:middle;">
            <div style="font-size:7px; color:#666; margin-bottom:3px;">Invoice Reference Number</div>
            <div style="font-size:11px; font-weight:700; color:#333;"><?php echo htmlspecialchars($invoice_ref); ?></div>
        </td>
        <td style="border:0; width:33%; padding:6px 10px; text-align:center; vertical-align:middle;">
            <div style="font-size:7px; color:#666; margin-bottom:3px;">Date and Time of Invoice Issuance</div>
            <div style="font-size:11px; font-weight:700; color:#333;"><?php echo date('Y/m/d H:i:s', strtotime($issue_timestamp)); ?></div>
        </td>
    </tr>
</table>

<table style="width:100%; border:1px solid #aaa; margin-bottom:4px;">
    <tr>
        <td colspan="4" class="section-title">Seller Information</td>
    </tr>
    <tr>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Seller Name</div><div class="value-cell"><?php echo htmlspecialchars($seller_name); ?></div></td>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Seller Address</div><div class="value-cell"><?php echo nl2br(htmlspecialchars(strip_tags($config['caddress']))); ?></div></td>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Seller VAT Registration Number</div><div class="value-cell"><?php echo htmlspecialchars($seller_vat !== '' ? $seller_vat : '----'); ?></div></td>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Additional Seller ID</div><div class="value-cell"><?php echo htmlspecialchars($seller_crn !== '' ? $seller_crn : '----'); ?></div></td>
    </tr>
</table>

<table style="width:100%; border:1px solid #aaa; margin-bottom:4px;">
    <tr>
        <td colspan="4" class="section-title">Buyer Information</td>
    </tr>
    <tr>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Buyer Name</div><div class="value-cell"><?php echo htmlspecialchars($a['company'] !== '' ? $a['company'] : $d['account']); ?></div></td>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Buyer Address</div><div class="value-cell"><?php echo htmlspecialchars(trim($a['address'] . ' ' . $a['city'] . ' ' . $a['state'] . ' ' . $a['country'])); ?></div></td>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Buyer VAT Registration Number</div><div class="value-cell"><?php echo !empty($a['tax_number']) ? htmlspecialchars($a['tax_number']) : '----'; ?></div></td>
        <td style="width:25%; padding:3px 6px; vertical-align:top;"><div class="label-cell">Additional Buyer ID</div><div class="value-cell"><?php echo !empty($a['entity_number']) ? htmlspecialchars($a['entity_number']) : '----'; ?></div></td>
    </tr>
</table>
</htmlpageheader>

<htmlpagefooter name="page-footer">
<table class="no-border" style="width:100%; border:1px solid #aaa; padding-top:4px;">
    <tr>
        <td style="border:0; width:100%; vertical-align:top;">
            <table class="no-border" style="width:100%; margin-bottom:3px; table-layout:fixed;">
                <tr>
                    <td style="border:0; width:50%; font-size:8px; text-align:left; vertical-align:top; padding-right:6px;">
                        <strong>Amount in words:</strong> <?php echo htmlspecialchars(ucfirst($amount_words['en'])); ?>
                    </td>
                    <td style="border:0; width:50%; font-size:8px; text-align:right; direction:rtl; vertical-align:top; padding-left:6px;">
                        <strong>المبلغ كتابةً:</strong> <?php echo htmlspecialchars($amount_words['ar']); ?>
                    </td>
                </tr>
            </table>
            <?php if (!empty($config['invoice_footer_html'])) { ?>
                <div style="font-size:8px; margin-top:4px;"><?php echo $config['invoice_footer_html']; ?></div>
            <?php } ?>
            <div style="font-size:7px; color:#999; text-align:right; margin-top:3px;">
                Page {PAGENO} of {nbpg}
            </div>
        </td>
    </tr>
</table>
</htmlpagefooter>

<table class="items-header" style="border:1px solid #aaa; width:100%; margin-bottom:4px;">
    <tr>
        <th style="width:36%; text-align:left;">Product</th>
        <th style="width:13%; text-align:center;">Unit Price</th>
        <th style="width:8%;  text-align:center;">Quantity</th>
        <th style="width:13%; text-align:center;">Subtotal Excl. VAT</th>
        <th style="width:9%;  text-align:center;">VAT Rate</th>
        <th style="width:10%; text-align:center;">VAT Amount</th>
        <th style="width:11%; text-align:center;">Total Incl. VAT</th>
    </tr>

    <?php foreach ($items as $item) {
        $line_tax_rate = (isset($item['taxed']) && (string) $item['taxed'] === '0') ? 0 : $_inv_taxrate;
        $line_tax_amount = $line_tax_rate > 0 ? round(((float) $item['total'] * $line_tax_rate) / 100, 2) : 0.0;
        $line_total_vat = (float) $item['total'] + $line_tax_amount;
    ?>
    <tr class="items-row">
        <td><?php echo htmlspecialchars($item['description']); ?></td>
        <td style="text-align:center;"><?php echo ib_money_format($item['amount'], $config, $d['currency_symbol']); ?></td>
        <td style="text-align:center;"><?php echo htmlspecialchars($item['qty']); ?></td>
        <td style="text-align:center;"><?php echo ib_money_format($item['total'], $config, $d['currency_symbol']); ?></td>
        <td style="text-align:center;"><?php echo number_format($line_tax_rate, 2) . '%'; ?></td>
        <td style="text-align:center;"><?php echo ib_money_format($line_tax_amount, $config, $d['currency_symbol']); ?></td>
        <td style="text-align:center;"><?php echo ib_money_format($line_total_vat, $config, $d['currency_symbol']); ?></td>
    </tr>
    <?php } ?>
</table>

<table class="no-border" style="width:100%; margin-bottom:6px;">
    <tr>
        <td style="border:0; width:55%; vertical-align:top; text-align:left; padding-top:2px;">
            <?php if ($show_zatca_qr) { ?>
                <img src="<?php echo $qr_code_image; ?>" style="width:190px; height:190px; border:1px solid #9aa6b2; padding:4px; background:#fff;" />
            <?php } else { ?>
                <div style="font-size:7px; color:#999; border:1px solid #ddd; padding:8px; width:150px; display:inline-block;">QR code is disabled in settings</div>
            <?php } ?>
        </td>
        <td style="border:0; width:45%; vertical-align:top; padding:0;">
            <table class="no-border" style="width:100%;">
                <tr>
                    <td style="border:1px solid #aaa; padding:4px 8px; font-size:10px; font-weight:700;">
                        <table class="no-border" style="width:100%; table-layout:fixed;">
                            <tr>
                                <td style="border:0; width:65%; text-align:left; font-size:10px; font-weight:700;">Total Excluding VAT</td>
                                <td style="border:0; width:35%; text-align:right; font-size:10px; font-weight:700;<?php echo $neg_style; ?>"><?php echo $neg_prefix . ib_money_format($display_subtotal, $config, $d['currency_symbol']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php if (($d['discount']) != '0.00') { ?>
                <tr>
                    <td style="border:1px solid #aaa; padding:4px 8px; font-size:10px; font-weight:700;">
                        <table class="no-border" style="width:100%; table-layout:fixed;">
                            <tr>
                                <td style="border:0; width:65%; text-align:left; font-size:10px; font-weight:700;">Discount <?php if ($d['discount_type'] == 'p') { echo '(' . $d['discount_value'] . ')%'; } ?></td>
                                <td style="border:0; width:35%; text-align:right; font-size:10px; font-weight:700;"><?php echo ib_money_format($d['discount'], $config, $d['currency_symbol']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php } ?>

                <tr>
                    <td style="border:1px solid #aaa; padding:4px 8px; font-size:10px; font-weight:700;">
                        <table class="no-border" style="width:100%; table-layout:fixed;">
                            <tr>
                                <td style="border:0; width:65%; text-align:left; font-size:10px; font-weight:700;">Applied VAT Value <?php if ((float) $d['taxrate'] > 0) { echo '(' . htmlspecialchars($d['taxrate']) . '%)'; } ?></td>
                                <td style="border:0; width:35%; text-align:right; font-size:10px; font-weight:700;<?php echo $neg_style; ?>"><?php echo $neg_prefix . ib_money_format($display_tax, $config, $d['currency_symbol']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="border:1px solid #aaa; padding:4px 8px; font-size:11px; font-weight:700; background:#f8f8f8;" class="grand-total">
                        <table class="no-border" style="width:100%; table-layout:fixed;">
                            <tr>
                                <td style="border:0; width:65%; text-align:left; font-size:11px; font-weight:700;">Total Including VAT</td>
                                <td style="border:0; width:35%; text-align:right; font-size:11px; font-weight:700;<?php echo $neg_style; ?>"><?php echo $neg_prefix . ib_money_format($display_total, $config, $d['currency_symbol']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php if ($d['credit'] != '0.00') { ?>
                <tr>
                    <td style="border:1px solid #aaa; padding:4px 8px; font-size:10px;">
                        <table class="no-border" style="width:100%; table-layout:fixed;">
                            <tr>
                                <td style="border:0; width:65%; text-align:left; font-size:10px;">Total Paid</td>
                                <td style="border:0; width:35%; text-align:right; font-size:10px;"><?php echo ib_money_format($d['credit'], $config, $d['currency_symbol']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="border:1px solid #aaa; padding:4px 8px; font-size:10px; font-weight:700; background:#fff3f3;">
                        <table class="no-border" style="width:100%; table-layout:fixed;">
                            <tr>
                                <td style="border:0; width:65%; text-align:left; font-size:10px; font-weight:700;">Amount Due</td>
                                <td style="border:0; width:35%; text-align:right; font-size:10px; font-weight:700;"><?php echo ib_money_format($i_due, $config, $d['currency_symbol']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </td>
    </tr>
</table>

<?php if ($trs_c != '') { ?>
<div style="margin-top:6px;">
    <div style="font-size:9px; font-weight:700; margin-bottom:3px;"><?php echo $_L['Related Transactions']; ?>:</div>
    <table style="width:100%; border:1px solid #aaa; font-size:8px;">
        <tr style="background:#f2f2f2;">
            <th style="text-align:left; padding:3px 5px;"><?php echo $_L['Date']; ?></th>
            <th style="text-align:left; padding:3px 5px;"><?php echo $_L['Account']; ?></th>
            <th style="text-align:left; padding:3px 5px; width:50%;"><?php echo $_L['Description']; ?></th>
            <th style="text-align:right; padding:3px 5px;"><?php echo $_L['Amount']; ?></th>
        </tr>
        <?php foreach ($trs as $tr) { ?>
        <tr>
            <td style="padding:2px 5px;"><?php echo date($config['df'], strtotime($tr['date'])); ?></td>
            <td style="padding:2px 5px;"><?php echo htmlspecialchars($tr['account']); ?></td>
            <td style="padding:2px 5px;"><?php echo htmlspecialchars($tr['description']); ?></td>
            <td style="padding:2px 5px; text-align:right;"><?php echo ib_money_format($tr['amount'], $config, $d['currency_symbol']); ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
<?php } ?>

<?php if ($d['notes'] != '') { ?>
<div class="terms-box" style="margin-top:6px; border-top:1px solid #ddd; padding-top:4px;">
    <strong><?php echo $_L['Terms']; ?>:</strong> <?php echo $d['notes']; ?>
</div>
<?php } ?>

</body>
</html>
