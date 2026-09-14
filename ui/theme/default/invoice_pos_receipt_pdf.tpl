<head>
    <meta charset="utf-8">
    <style type="text/css">
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #1f2937;
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 3.5mm;
            direction: rtl;
        }

        .box {
            border: none;
            border-radius: 0;
            padding: 4px 0;
            margin-bottom: 5px;
            background: #fff;
        }

        .logo-wrap {
            text-align: center;
            margin: 0 0 8px;
        }

        .logo-wrap img {
            max-width: 140px;
            max-height: 70px;
            width: auto;
            height: auto;
        }

        .title-box {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 9px;
        }

        .sub-box {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
        }

        .kv {
            font-size: 12px;
            line-height: 1.5;
        }

        .kv strong {
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-wrap {
            border: none;
            border-radius: 0;
            padding: 0;
            margin: 9px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #d9dfe5;
            padding: 5px 3px;
            text-align: center;
            font-size: 9.5px;
            vertical-align: middle;
        }

        .items-table th {
            background: #f5f7fa;
            font-weight: 700;
        }

        .items-table .product-cell {
            text-align: right;
            width: 26%;
        }

        .summary .row {
            border: none;
            border-radius: 0;
            padding: 3px 0;
            margin-bottom: 2px;
            font-size: 13px;
            font-weight: 700;
        }

        .summary .row .label {
            float: right;
        }

        .summary .row .value {
            float: left;
        }

        .summary .row:after {
            content: "";
            display: block;
            clear: both;
        }

        .footer-note {
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            margin: 18px 0 8px;
        }

        .qr-box {
            text-align: center;
            margin-top: 4px;
        }

        .qr-box img {
            width: 140px;
            height: 140px;
            border: 1px solid #9aa6b2;
            padding: 4px;
            background: #fff;
        }

        .muted {
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
{assign var="vat_percent" value=15}
{if $d['subtotal'] > 0}
    {assign var="vat_percent" value=(($d['tax'] * 100) / $d['subtotal'])|round:0}
{/if}

<div class="receipt">
    <div class="logo-wrap">
        <img src="{$app_url}storage/system/{$config['logo_default']}" alt="{$config['CompanyName']}">
    </div>

    <div class="box title-box">فاتورة ضريبة مبسطة</div>

    <div class="box sub-box">رقم الفاتورة: {$d['invoicenum']}{if $d['cn'] neq ''} {$d['cn']}{else}{$d['id']}{/if}</div>

    <div class="box sub-box">اسم المتجر: {$config['CompanyName']}</div>

    <div class="box sub-box">عنوان المتجر: {$config['caddress']|default:'-'}</div>

    <div class="box kv">
        <strong>تاريخ الفاتورة:</strong> {date('Y/m/d', strtotime($d['date']))}
    </div>

    <div class="box kv">
        <strong>رقم تسجيل ضريبة القيمة المضافة:</strong>
        {if isset($config['vat_number']) && $config['vat_number'] neq ''}{$config['vat_number']}{else}-{/if}
    </div>

    <div class="items-wrap">
        <table class="items-table">
            <thead>
            <tr>
                <th class="product-cell">المنتجات</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>ضريبة القيمة المضافة</th>
                <th>السعر شامل ضريبة القيمة المضافة</th>
            </tr>
            </thead>
            <tbody>
            {foreach $items as $index => $item}
                <tr>
                    <td class="product-cell">{if $item['description'] neq ''}{$item['description']}{else}منتج {$index+1}{/if}</td>
                    <td>{$item['qty']|number_format:1:'.':''}</td>
                    <td>{$item['amount']|number_format:2:'.':''}</td>
                    <td>{$item['taxamount']|number_format:2:'.':''}</td>
                    <td>{($item['total'] + $item['taxamount'])|number_format:2:'.':''}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>

    <div class="summary">
        <div class="row">
            <span class="label">إجمالي المبلغ الخاضع للضريبة</span>
            <span class="value">{$d['subtotal']|number_format:2:'.':''}</span>
        </div>
        <div class="row">
            <span class="label">ضريبة القيمة المضافة ({$vat_percent}%)</span>
            <span class="value">{$d['tax']|number_format:2:'.':''}</span>
        </div>
        <div class="row">
            <span class="label">إجمالي المبلغ شامل ضريبة القيمة المضافة ({$vat_percent}%)</span>
            <span class="value">{$d['total']|number_format:2:'.':''}</span>
        </div>
    </div>

    <div class="footer-note">&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt; اغلاق الفاتورة {$d['id']} &gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;&gt;</div>

    <div class="qr-box">
        {if !empty($qr_code)}
            <img src="{$qr_code}" alt="QR">
        {else}
            <div class="muted">QR غير متاح</div>
        {/if}
    </div>
</div>
</body>
