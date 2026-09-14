<!DOCTYPE html>
<html>

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title><?php echo $_L['Payment Voucher']; ?> - <?php echo htmlspecialchars($due->voucher_no); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font: 13px/1.4 Helvetica, Arial, sans-serif; color: #222; }
        #page-wrap { width: 700px; margin: 20px auto; }
        table { border-collapse: collapse; width: 100%; }
        table td, table th { border: 1px solid #999; padding: 6px 8px; }

        .top-row { border: 0; margin-bottom: 20px; }
        .top-row td { border: 0; vertical-align: top; }
        .company-name { font-size: 20px; font-weight: bold; color: #2f4f4f; }
        .voucher-title { font-size: 22px; font-weight: bold; text-align: right; text-transform: uppercase; color: #2f4f4f; }

        .customer-box { border: 1px solid #999; padding: 10px 12px; margin-bottom: 16px; }
        .customer-box h4 { margin-bottom: 6px; font-size: 14px; }

        .amount-table td.label { text-align: left; width: 50%; }
        .amount-table td.value { text-align: right; }
        .balance-due td { font-weight: bold; font-size: 15px; background: #fdecea; color: #c0392b; }

        .signatures { width: 100%; margin-top: 60px; }
        .signatures td { border: 0; text-align: center; padding-top: 30px; border-top: 1px solid #333; }

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
                <div class="voucher-title"><?php echo $_L['Payment Voucher']; ?></div>
                <div style="text-align: right; margin-top: 6px; color: #666;"><?php echo $_L['Voucher No']; ?>: <?php echo htmlspecialchars($due->voucher_no); ?></div>
                <div style="text-align: right; color: #666;"><?php echo $_L['Date']; ?>: <?php echo date($config['df'], strtotime($due->date)); ?></div>
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

    <?php if (!empty($due->description)) { ?>
    <div class="customer-box">
        <h4><?php echo $_L['Description']; ?></h4>
        <div><?php echo nl2br(htmlspecialchars($due->description)); ?></div>
    </div>
    <?php } ?>

    <table class="amount-table">
        <tr>
            <td class="label"><?php echo $_L['Amount']; ?></td>
            <td class="value"><?php echo formatCurrency($due->amount); ?></td>
        </tr>
        <tr>
            <td class="label"><?php echo $_L['Total Paid Amount']; ?></td>
            <td class="value"><?php echo formatCurrency($due->paid_amount); ?></td>
        </tr>
        <tr class="balance-due">
            <td class="label"><?php echo $_L['Balance']; ?></td>
            <td class="value"><?php echo formatCurrency((float) $due->amount - (float) $due->paid_amount); ?></td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td><?php echo $_L['Received By']; ?></td>
            <td><?php echo $_L['Authorized Signatory']; ?></td>
        </tr>
    </table>

    <div class="print-footer">
        <?php echo $_L['Payment Voucher']; ?> - <?php echo htmlspecialchars($config['CompanyName']); ?> - <?php echo date($config['df'] . ' H:i'); ?>
    </div>

    <div class="no-print" style="text-align: center;">
        <button class="button" onclick="window.print();"><?php echo $_L['Print']; ?></button>
    </div>

</div>
</body>
</html>
