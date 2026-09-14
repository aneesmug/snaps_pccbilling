<!DOCTYPE html>
<html>

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title><?php echo $_L['Customer Statement']; ?> - <?php echo htmlspecialchars($d['account']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font: 13px/1.4 Helvetica, Arial, sans-serif; color: #222; }
        #page-wrap { width: 900px; margin: 20px auto; }
        table { border-collapse: collapse; width: 100%; }
        table td, table th { border: 1px solid #999; padding: 6px 8px; }

        .top-row { border: 0; margin-bottom: 20px; }
        .top-row td { border: 0; vertical-align: top; }
        .company-name { font-size: 20px; font-weight: bold; color: #2f4f4f; }
        .statement-title { font-size: 22px; font-weight: bold; text-align: right; text-transform: uppercase; color: #2f4f4f; }

        .customer-box { border: 1px solid #999; padding: 10px 12px; margin-bottom: 16px; }
        .customer-box h4 { margin-bottom: 6px; font-size: 14px; }

        #items th { background: #eee; }
        #items td.text-end, #items th.text-end { text-align: right; }

        .totals-table { width: 320px; margin-left: auto; margin-top: 10px; }
        .totals-table td { border: 1px solid #999; }
        .totals-table td.label { text-align: left; }
        .totals-table td.value { text-align: right; }
        .balance-due td { font-weight: bold; font-size: 15px; background: #fdecea; color: #c0392b; }

        .print-footer { margin-top: 20px; font-size: 11px; color: #666; text-align: center; }

        .button {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            cursor: pointer;
            border: 0;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            background: #333030;
        }

        @media print {
            .no-print, .no-print * { display: none !important; }
            #page-wrap { width: 100%; margin: 0; }
        }
    </style>
</head>

<body>
<div id="page-wrap">

    <table class="top-row">
        <tr>
            <td width="50%">
                <img src="<?php echo APP_URL . '/storage/system/' . (!empty($config['logo_default']) ? $config['logo_default'] : 'logo.png'); ?>" alt="logo" style="max-width: 160px; max-height: 60px;" /><br><br>
                <div class="company-name"><?php echo htmlspecialchars($config['CompanyName']); ?></div>
            </td>
            <td width="50%">
                <div class="statement-title"><?php echo $_L['Customer Statement']; ?></div>
                <div style="text-align: right; margin-top: 6px; color: #666;"><?php echo $_L['Date']; ?>: <?php echo date($config['df']); ?></div>
            </td>
        </tr>
    </table>

    <div class="customer-box">
        <h4><?php echo $_L['Customer']; ?></h4>
        <div><strong><?php echo htmlspecialchars($d['account']); ?></strong></div>
        <?php if (!empty($d['company'])) { ?><div><?php echo htmlspecialchars($d['company']); ?></div><?php } ?>
        <?php if (!empty($d['phone'])) { ?><div><?php echo $_L['Phone']; ?>: <?php echo htmlspecialchars($d['phone']); ?></div><?php } ?>
        <?php if (!empty($d['email'])) { ?><div><?php echo $_L['Email']; ?>: <?php echo htmlspecialchars($d['email']); ?></div><?php } ?>
        <?php if (!empty($d['address'])) { ?><div><?php echo htmlspecialchars($d['address']); ?></div><?php } ?>
        <?php if (!empty($d['tax_number'])) { ?><div>VAT: <?php echo htmlspecialchars($d['tax_number']); ?></div><?php } ?>
    </div>

    <table id="items">
        <thead>
        <tr>
            <th>#</th>
            <th><?php echo $_L['Invoice Date']; ?></th>
            <th><?php echo $_L['Due Date']; ?></th>
            <th class="text-end"><?php echo $_L['Amount']; ?></th>
            <th class="text-end"><?php echo $_L['Total Paid Amount']; ?></th>
            <th class="text-end"><?php echo $_L['Total Un Paid Amount']; ?></th>
            <th><?php echo $_L['Status']; ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($invoices_summary['invoices'] as $invoice) {
            $inv_total = (float) $invoice['total'];
            $inv_paid = (float) $invoice['credit'];
            $inv_balance = $inv_total - $inv_paid;
            $inv_number = !empty($invoice['cn']) ? $invoice['cn'] : $invoice['id'];
        ?>
            <tr>
                <td><?php echo htmlspecialchars($invoice['invoicenum'] . $inv_number); ?></td>
                <td><?php echo date($config['df'], strtotime($invoice['date'])); ?></td>
                <td><?php echo date($config['df'], strtotime($invoice['duedate'])); ?></td>
                <td class="text-end"><?php echo formatCurrency($inv_total, $invoice['currency_iso_code']); ?></td>
                <td class="text-end"><?php echo formatCurrency($inv_paid, $invoice['currency_iso_code']); ?></td>
                <td class="text-end"><?php echo formatCurrency($inv_balance, $invoice['currency_iso_code']); ?></td>
                <td><?php echo ib_lan_get_line($invoice['status']); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <?php if (!empty($dues_summary['dues']) && count($dues_summary['dues']) > 0) { ?>
    <h4 style="margin: 20px 0 8px;"><?php echo $_L['Due Amount']; ?></h4>
    <table id="items">
        <thead>
        <tr>
            <th><?php echo $_L['Voucher No']; ?></th>
            <th><?php echo $_L['Date']; ?></th>
            <th><?php echo $_L['Description']; ?></th>
            <th class="text-end"><?php echo $_L['Amount']; ?></th>
            <th class="text-end"><?php echo $_L['Total Paid Amount']; ?></th>
            <th class="text-end"><?php echo $_L['Total Un Paid Amount']; ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($dues_summary['dues'] as $due) {
            $due_balance = (float) $due->amount - (float) $due->paid_amount;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($due->voucher_no); ?></td>
                <td><?php echo date($config['df'], strtotime($due->date)); ?></td>
                <td><?php echo htmlspecialchars($due->description); ?></td>
                <td class="text-end"><?php echo formatCurrency($due->amount); ?></td>
                <td class="text-end"><?php echo formatCurrency($due->paid_amount); ?></td>
                <td class="text-end"><?php echo formatCurrency($due_balance); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php } ?>

    <table class="totals-table">
        <tr>
            <td class="label"><?php echo $_L['Total Invoice Amount']; ?></td>
            <td class="value"><?php echo formatCurrency($invoices_summary['total_invoiced_amount'] + $dues_summary['total_due_amount']); ?></td>
        </tr>
        <tr>
            <td class="label"><?php echo $_L['Total Paid Amount']; ?></td>
            <td class="value"><?php echo formatCurrency($invoices_summary['total_paid_amount'] + $dues_summary['total_paid_amount']); ?></td>
        </tr>
        <tr class="balance-due">
            <td class="label"><?php echo $_L['Total Un Paid Amount']; ?></td>
            <td class="value"><?php echo formatCurrency($invoices_summary['total_unpaid_amount'] + $dues_summary['total_unpaid_amount']); ?></td>
        </tr>
    </table>

    <div class="print-footer">
        <?php echo $_L['Customer Statement']; ?> - <?php echo htmlspecialchars($config['CompanyName']); ?> - <?php echo date($config['df'] . ' H:i'); ?>
    </div>

    <div class="no-print" style="text-align: center;">
        <button class="button" onclick="window.print();"><?php echo $_L['Print']; ?></button>
    </div>

</div>
</body>
</html>
