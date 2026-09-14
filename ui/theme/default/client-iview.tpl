<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{$_L['INVOICE']} - {$d['invoicenum']}{if $d['cn'] neq ''} {$d['cn']} {else} {$d['id']} {/if}</title>

    <link rel="icon" href="{{APP_URL}}/storage/system/{get_or_default($config,'icon-32','icon-32x32.png')}" sizes="32x32" />
    <link rel="icon" href="{{APP_URL}}/storage/system/{get_or_default($config,'icon-192','icon-192x192.png')}" sizes="192x192" />
    <link rel="apple-touch-icon" href="{{APP_URL}}/storage/system/{get_or_default($config,'icon-180','icon-180x180.png')}" />
    <meta name="msapplication-TileImage" content="{{APP_URL}}/storage/system/{get_or_default($config,'icon-270','icon-270x270.png')}" />

    {if APP_STAGE == 'Dev'}

        {if $config['rtl'] eq '1'}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app-rtl.min.css?v={{_raid()}}">
        {else}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app.min.css?v={{_raid()}}">

        {/if}

        <link href="{$theme}default/css/themes/{$config['nstyle']}.css?v={{_raid()}}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {else}

        {if $config['rtl'] eq '1'}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app-rtl.min.css?v=2">
        {else}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app.min.css?v=2">
        {/if}

        <link href="{$theme}default/css/themes/{$config['nstyle']}.css?v=13" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    {/if}

    {block name=style}{/block}

    <script>
        var base_url = '{$_url}';
        var block_msg = '<div class="md-preloader text-center"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="32" width="32" viewbox="0 0 75 75"><circle cx="37.5" cy="37.5" r="33.5" stroke-width="6"/></svg></div>';
    </script>

    {$config['header_scripts']}

    <style type="text/css">
        body {

            background-color: #e9ebee;
            overflow-x: visible;
        }
        .paper {
            margin: 20px auto;
            max-width: 980px;
            background-color: #FFF;
            position: relative;

        }

        .fancybox-slide--iframe .fancybox-content {
            width  : 600px;
            max-width  : 80%;
            max-height : 80%;
            margin: 0;
        }

        .panel {

            /*box-shadow: none;*/

            -webkit-box-shadow: 0 10px 40px 0 rgba(18,106,211,.07), 0 2px 9px 0 rgba(18,106,211,.06);
            box-shadow: 0 10px 40px 0 rgba(18,106,211,.07), 0 2px 9px 0 rgba(18,106,211,.06);

        }

        .panel-body {
            padding: 25px;
        }

        .preview-top-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .preview-top-actions .btn {
            margin-left: 0 !important;
        }

        .preview-header-separator {
            margin-top: 12px;
            margin-bottom: 0;
        }

        {if isset($payment_gateways_by_processor['stripe'])}

        .StripeElement {
            background-color: white;
            height: 40px;
            padding: 10px 12px;
            border-radius: 4px;
            border: 1px solid transparent;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }

        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }

        .StripeElement--invalid {
            border-color: #fa755a;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }

        {/if}

        .table-bordered>thead>tr, .table-bordered>thead>tr>th{
            border-bottom-width: 0;
            border-top-width: 0;
        }
        .table-bordered>tbody>tr:first-child {
            border-top-width: 0;
        }

        .table>:not(:first-child) {
            border-top: none;
        }


        .table.invoice-items{
            border: 1px solid #dee2e6;
        }

        .table.invoice-items td, .table.invoice-items th {
            border: 1px solid #dee2e6;
        }

        .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: black;
            color: #ffffff;
        }

        .table-striped>tbody>tr:nth-of-type(odd)>* {
            color: #ffffff!important;
        }

        .thead-light th {
            background-color: #5d6675;
        }
        .table-bordered>thead>tr, .table-bordered>thead>tr>th {
            border-bottom-width: 0;
            border-top-width: 0;
        }

    /*    Disable bootstrap 5 table black borders */





    </style>

    {if isset($payment_gateways_by_processor['stripe'])}
        <script src="https://js.stripe.com/v3/"></script>
    {/if}

</head>

<body class="fixed-nav">

<div class="paper">
    <section class="panel">
        <div class="panel-body">
            <div class="invoice">
                {if isset($notify)}
                    {$notify}
                {/if}
                <header class="clearfix">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="preview-top-actions">

                                {if $has_login_token}
                                    <a href="{$_url}client/dashboard/" class="btn btn-primary ml-sm no-shadow no-border"><i class="fal fa-long-arrow-left"></i> {$_L['Back to Client Area']}</a>
                                {/if}

                                <a href="{$_url}client/ipdf/{$d['id']}/token_{$d['vtoken']}/dl/" class="btn btn-primary buttons-pdf ml-sm"><i class="fal fa-file-pdf-o"></i> {$_L['Download PDF']}</a>
                                <a href="{$_url}client/ipdf/{$d['id']}/token_{$d['vtoken']}/view/" class="btn btn-primary buttons-excel ml-sm"><i class="fal fa-file-text-o"></i> {$_L['View PDF']}</a>
                                <a href="{$_url}iview/print/{$d['id']}/token_{$d['vtoken']}" target="_blank" class="btn btn-primary buttons-print ml-sm"><i class="fal fa-print"></i> {$_L['Printable Version']}</a>
                            </div>

                            <div class="hr-line-dashed preview-header-separator"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 mt-md text-center">
                            {if $show_zatca_qr}
                                <img src="{$qr_code}" style="width: 130px; height: 130px; border: 1px solid #9aa6b2; padding: 4px; background: #fff;">
                            {else}
                                <img src="{$app_url}storage/system/{$config['logo_default']}" alt="{$config['CompanyName']}" style="max-width: 140px; {if !empty($config['invoice_logo_height'])}height: {$config['invoice_logo_height']}px;{/if}">
                            {/if}
                        </div>
                        <div class="col-sm-9 mt-md">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">
                                <div>
                                    <h2 class="h2 mt-none mb-xs text-dark text-bold">
                                        {if $d['type'] == 'Credit Note'}
                                            {{__('CREDIT NOTE')}}
                                        {else}
                                            {$_L['INVOICE']}
                                        {/if}
                                    </h2>
                                    <h4 class="h4 m-none text-dark text-bold">#{$d['invoicenum']}{if $d['cn'] neq ''} {$d['cn']} {else} {$d['id']} {/if}</h4>
                                </div>
                                <div class="text-end">
                                    {if $show_zatca_qr}
                                        <div class="ib">
                                            <img src="{$app_url}storage/system/{$config['logo_default']}" alt="{$config['CompanyName']}" style="max-width: 100px; {if !empty($config['invoice_logo_height'])}height: {$config['invoice_logo_height']}px;{/if}">
                                        </div>
                                    {/if}
                                    <div><strong>{$config['CompanyName']}</strong></div>
                                    <div style="font-size: 12px; color: #444;">{$config['caddress']}</div>
                                </div>
                            </div>

                            <div style="margin-top: 8px; border: 1px solid #aaa; padding: 6px 10px;">
                                <div style="font-size: 15px; font-weight: 700; color: #333;" dir="rtl">
                                    {if $invoice_type_label eq 'Standard Tax Invoice'}
                                        فاتورة ضريبية
                                    {else}
                                        فاتورة ضريبية مبسطة
                                    {/if}
                                </div>
                                <div style="font-size: 12px; color: #555;">{$invoice_type_label}</div>
                            </div>

                            <div class="row" style="margin-top: 6px; border: 1px solid #aaa;">
                                <div class="col-sm-6" style="padding: 6px 10px; border-right: 1px solid #aaa;">
                                    <div style="font-size: 10px; color: #666;">{$_L['Invoice Reference Number']|default:'Invoice Reference Number'}</div>
                                    <div style="font-size: 13px; font-weight: 700; color: #333;">{$invoice_ref}</div>
                                </div>
                                <div class="col-sm-6" style="padding: 6px 10px;">
                                    <div style="font-size: 10px; color: #666;">{$_L['Date and Time of Invoice Issuance']|default:'Date and Time of Invoice Issuance'}</div>
                                    <div style="font-size: 13px; font-weight: 700; color: #333;">{date('Y/m/d H:i:s', strtotime($issue_timestamp))}</div>
                                </div>
                            </div>

                            <div style="margin-top: 8px;">
                                {if $d['status'] eq 'Unpaid'}
                                    <span class="badge bg-danger">{$_L['Unpaid']}</span>
                                {elseif $d['status'] eq 'Paid'}
                                    <span class="badge bg-success">{$_L['Paid']}</span>
                                {elseif $d['status'] eq 'Partially Paid'}
                                    <span class="badge bg-info">{$_L['Partially Paid']}</span>
                                {else}
                                    <span class="badge bg-info">{__($d['status'])}</span>
                                {/if}

                                {if isset($d['title']) && $d['title'] != ''}
                                    <span class="ms-2">{$d['title']}</span>
                                {/if}

                                {if $config['invoice_receipt_number'] eq '1' && $d['receipt_number'] neq ''}
                                    <span class="ms-2">{$_L['Receipt Number']}: {$d['receipt_number']}</span>
                                {/if}
                            </div>
                        </div>
                    </div>
                </header>

                <div style="border: 1px solid #aaa; margin-top: 10px;">
                    <div style="text-align: right; font-size: 13px; font-weight: 700; color: #333; padding: 5px 10px; background: #f2f2f2;">{$_L['Seller Information']|default:'Seller Information'}</div>
                    <div class="row" style="margin: 0;">
                        <div class="col-sm-3" style="padding: 6px 10px;">
                            <div style="font-size: 10px; color: #666;">{$_L['Seller Name']|default:'Seller Name'}</div>
                            <div style="font-size: 12px; font-weight: 700; color: #333;">{$seller_name}</div>
                        </div>
                        <div class="col-sm-3" style="padding: 6px 10px;">
                            <div style="font-size: 10px; color: #666;">{$_L['Seller Address']|default:'Seller Address'}</div>
                            <div style="font-size: 12px; font-weight: 700; color: #333;">{$config['caddress']}</div>
                        </div>
                        <div class="col-sm-3" style="padding: 6px 10px;">
                            <div style="font-size: 10px; color: #666;">{$_L['Seller VAT Registration Number']|default:'Seller VAT Registration Number'}</div>
                            <div style="font-size: 12px; font-weight: 700; color: #333;">{if $seller_vat neq ''}{$seller_vat}{else}----{/if}</div>
                        </div>
                        <div class="col-sm-3" style="padding: 6px 10px;">
                            <div style="font-size: 10px; color: #666;">{$_L['Additional Seller ID']|default:'Additional Seller ID'}</div>
                            <div style="font-size: 12px; font-weight: 700; color: #333;">{if $seller_crn neq ''}{$seller_crn}{else}----{/if}</div>
                        </div>
                    </div>
                </div>

                <div class="bill-info" style="border: 1px solid #aaa; border-top: 0; margin-bottom: 10px;">
                    <div style="text-align: right; font-size: 13px; font-weight: 700; color: #333; padding: 5px 10px; background: #f2f2f2;">{$_L['Buyer Information']|default:'Buyer Information'}</div>
                    <div class="row" style="padding: 8px 10px;">
                        <div class="col-md-6">
                            <div class="bill-to">
                                <p class="h5 mb-xs text-dark text-semibold">
                                    <strong>

                                        {if $d['type'] == 'Credit Note'}

                                            {{__('To')}}

                                        {else}

                                            {$_L['Invoiced To']}:

                                        {/if}
                                    </strong></p>
                                <address>
                                    {if $a['company'] neq ''}
                                        {$a['company']}

                                        <br>

                                        {if $company && $config['show_business_number'] eq '1' }

                                            {if $company->business_number neq ''}
                                                {$config['label_business_number']}: {$company->business_number}
                                                <br>
                                            {/if}
                                        {/if}

                                        {$_L['ATTN']}: {$d['account']}
                                        <br>
                                    {else}
                                        {$d['account']}
                                        <br>
                                    {/if}

                                    {getContactFormattedAddress($config,$a)}
                                    <br>
                                    <strong>{$_L['Phone']}:</strong> {$a['phone']}

                                    {if $config['fax_field'] neq '0' && $a['fax'] neq ''}
                                        <br>
                                        <strong>{$_L['Fax']}:</strong> {$a['fax']}
                                    {/if}

                                    <br>
                                    <strong>{$_L['Email']}:</strong> {$a['email']}
                                    {foreach $cf as $cfs}
                                        {if $cfs['showinvoice'] == 'No'}
                                            {continue}
                                        {/if}
                                        <br>
                                        <strong>{$cfs['fieldname']}: </strong> {get_custom_field_value($cfs['id'],$a['id'])}
                                    {/foreach}
                                    {$x_html}
                                </address>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bill-data text-end">
                                <p class="mb-none">
                                    <span class="text-dark">

                                        {$_L['Invoice Date']}
                                    </span>
                                    <span class="value">{date( $config['df'], strtotime($d['date']))}</span>
                                </p>
                                <p class="mb-none">
                                    {if $d['type'] !== 'Credit Note'}

                                        <span class="text-dark">{$_L['Due Date']}:</span>
                                        <span class="value">{date( $config['df'], strtotime($d['duedate']))}</span>

                                    {/if}



                                </p>

                                <h2>  {if $d['type'] == 'Credit Note'}

                                        {{__('Total')}}

                                    {else}

                                        {$_L['Invoice Total']}:

                                    {/if}
                                    : {formatCurrency($d['total'],$d['currency_iso_code'])} </h2>
                                {if ($d['credit']) neq '0.00'}
                                    <h2> {$_L['Total Paid']}: {formatCurrency($d['credit'],$d['currency_iso_code'])}</h2>

                                    {if $d['i_due'] > 0}
                                        <h2> {$_L['Amount Due']}: {formatCurrency($d['i_due'],$d['currency_iso_code'])}</h2>
                                    {/if}

                                {/if}
                                {if (($d['status']) neq 'Paid') AND (ib_pg_count() neq '0' AND (($d['status']) neq 'Cancelled'))}




                                    {if $render === 'delivery'}

                                        <div class="col-md-6 float-end">
                                            <div class="bill-to">
                                                <p class="h5 mb-xs text-dark text-semibold"><strong>{$_L['Delivery To']}</strong></p>
                                                <address>
                                                    {if $a['company'] neq ''}
                                                        {$a['company']}

                                                        <br>

                                                        {if $company && $config['show_business_number'] eq '1' }

                                                            {if $company->business_number neq ''}
                                                                {$config['label_business_number']}: {$company->business_number}
                                                                <br>
                                                            {/if}
                                                        {/if}

                                                        {$_L['ATTN']}: {$d['account']}
                                                        <br>
                                                    {else}
                                                        {$d['account']}
                                                        <br>
                                                    {/if}

                                                    {getContactFormattedAddress($config,$a)}
                                                    <br>
                                                    <strong>{$_L['Phone']}:</strong> {$a['phone']}

                                                    {if $config['fax_field'] neq '0' && $a['fax'] neq ''}
                                                        <br>
                                                        <strong>{$_L['Fax']}:</strong> {$a['fax']}
                                                    {/if}

                                                    <br>
                                                    <strong>{$_L['Email']}:</strong> {$a['email']}
                                                    {foreach $cf as $cfs}
                                                        {if $cfs['showinvoice'] == 'No'}
                                                            {continue}
                                                        {/if}
                                                        <br>
                                                        <strong>{$cfs['fieldname']}: </strong> {get_custom_field_value($cfs['id'],$a['id'])}
                                                    {/foreach}
                                                    {$x_html}
                                                </address>
                                            </div>
                                        </div>


                                        {else}


                                        <form class="my-3" method="post" action="{$_url}client/ipay/{$d['id']}/token_{$d['vtoken']}">

                                            {if count($payment_gateways) == 1}
                                                {foreach $payment_gateways as $pg}
                                                    <input type="hidden" id="paymentGateway" name="pg" value="{$pg->processor}">
                                                {/foreach}
                                            {else}
                                                <div class="mb-3 has-success">
                                                    <select class="form-select" name="pg" id="paymentGateway">
                                                        {foreach $payment_gateways as $pg}
                                                            <option value="{$pg->processor}">{$pg->name}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            {/if}
                                            <div class="mb-3">

                                                {if $d->allow_partial_payment}
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control text-end" id="input_amount" name="amount" value="{$i_due}">
                                                    </div>
                                                {/if}

                                                <button type="submit" id="btnPayNow" class="btn btn-primary"><i class="fal fa-credit-card"></i> {$_L['Pay Now']}</button>
                                            </div>

                                        </form>





                                    {/if}



                                    {if $a->balance > 0 && $d->is_credit_invoice neq 1}
                                        <hr>
                                        <h3> Your Current Balance: <span class="amount">{$a->balance}</span> </h3>
                                         <a class="btn btn-primary" href="{$_url}client/pay_with_credit/{$d->id}/token_{$d->vtoken}"> Pay with Credit</a>
                                        <hr>
                                    {/if}

                                {if isset($payment_gateways_by_processor['stripe'])}

                                    <div id="stripeDiv" style="display: none; margin-bottom: 25px; margin-top: 15px; padding: 15px; background: #f5f5f6;">


                                        <form action="{$_url}client/payment-stripe" method="post" id="payment-form">
                                            <div class="row">
                                                <label for="card-element">
                                                    Credit or debit card
                                                </label>
                                                <div id="card-element" class="form-control">
                                                    <!-- A Stripe Element will be inserted here. -->
                                                </div>

                                                <!-- Used to display form errors. -->
                                                <div id="card-errors" role="alert"></div>
                                            </div>

                                            <input type="hidden" name="invoice_id" value="{$d['id']}">
                                            <input type="hidden" name="view_token" value="{$d['vtoken']}">
                                            <button class="btn btn-primary" id="btnStripeSubmit" style="margin-top: 20px;">Submit Payment</button>

                                        </form>
                                    </div>

                                {/if}


                                {/if}


                            </div>
                        </div>
                    </div>
                </div>

                {if $quote}

                        <h4>{$_L['Quote']}: {$quote->id}</h4>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                            {$quote->proposal}
                            <hr>
                        </div>
                    </div>
                {/if}

                <div class="table-responsive">

                    {if $config['tax_system'] == 'India'}

                        <table class="table table-bordered invoice-items">
                            <thead>
                            <tr class="text-dark">
                                <th id="cell-id" class="text-semibold">S/L</th>
                                <th id="cell-item" class="text-semibold">{$_L['Item']}</th>
                                <th class="text-semibold">HSN / SAC</th>
                                <th id="cell-price" class="text-center text-semibold">{$_L['Price']}</th>
                                <th id="cell-qty" class="text-center text-semibold">{if $d['show_quantity_as'] eq '' || $d['show_quantity_as'] eq '1'}{$_L['Qty']}{else}{$d['show_quantity_as']}{/if}</th>
                                <th class="text-end">Taxable Value</th>


                                {if $d['is_same_state']}

                                    <th class="text-end">CGST</th>
                                    <th class="text-end">SGST/UTGST</th>
                                    <th class="text-end">GST</th>

                                {else}

                                    <th class="text-end">IGST</th>

                                {/if}




                                <th id="cell-total" class="text-end text-semibold">{$_L['Total']}</th>
                            </tr>
                            </thead>
                            <tbody>

                            {foreach $items as $item}
                                <tr>
                                    <td>
                                        {if $item['itemcode'] != ''}
                                            {$item['itemcode']}
                                        {else}
                                            {counter}
                                        {/if}
                                    </td>
                                    <td class="text-semibold text-dark">{$item['description']}</td>
                                    <td class="text-semibold text-dark">{$item['tax_code']}</td>
                                    <td class="text-center amount" data-a-sign="{if $d['currency_symbol'] eq ''} {$config['currency_code']} {else} {$d['currency_symbol']}{/if} ">{$item['amount']}</td>
                                    <td class="text-center">{$item['qty']}</td>
                                    <td class="text-end">
                                        {if $item['discount_amount'] != '0.00'}

                                            Total: <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{($item['amount']*$item['qty'])}</span>


                                            <br>
                                            Discount: <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{$item['discount_amount']}</span>
                                            <br>
                                            Taxable amount: <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{($item['amount']*$item['qty'])-$item['discount_amount']}</span>

                                        {else}
                                            <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{($item['amount']*$item['qty'])}</span>

                                        {/if}


                                    </td>


                                    {if $d['is_same_state']}

                                        <td class="text-end">
                                            <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{gstIndiaSplitTaxValue($item['total'],$item['tax_rate'])}</span>
                                            <br>
                                            @{app_round($item['tax_rate']/2,2)}%
                                        </td>
                                        <td class="text-end">
                                            <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{gstIndiaSplitTaxValue($item['total'],$item['tax_rate'])}</span>
                                            <br>
                                            @{app_round($item['tax_rate']/2,2)}%
                                        </td>
                                        <td class="text-end">
                                            <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{app_round($item['taxamount'],2)}</span> <br>
                                            @{app_round($item['tax_rate'],2)}%

                                        </td>

                                    {else}



                                        <td class="text-end">
                                            <span class="amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{app_round(( ($item['tax_rate']*($item['qty'] * $item['amount'])) / 100),2)}</span> <br>
                                            @{app_round($item['tax_rate'],2)}%

                                        </td>

                                    {/if}


                                    <td class="text-end amount" data-a-sign="{$data_a_sign}" data-a-dec="{$data_a_dec}" data-a-sep="{$data_a_sep}" data-p-sign="{$data_p_sign}">{$item['total'] + $item['taxamount']}</td>

                                </tr>
                            {/foreach}
                            </tbody>
                        </table>

                    {else}

                        <table class="table table-bordered invoice-items">
                            <thead>
                            <tr class="text-dark">
                                <th id="cell-id" class="fw-bold">#</th>
                                <th id="cell-item" class="fw-bold">{$_L['Item']}</th>
                                <th id="cell-price" class="text-center fw-bold">{$_L['Unit Price']|default:'Unit Price'}</th>
                                <th id="cell-qty" class="text-center fw-bold">{if $d['show_quantity_as'] eq '' || $d['show_quantity_as'] eq '1'}{$_L['Qty']}{else}{$d['show_quantity_as']}{/if}</th>
                                <th class="text-center fw-bold">{$_L['Subtotal Excl. VAT']|default:'Subtotal Excl. VAT'}</th>
                                <th class="text-center fw-bold">{$_L['VAT Rate']|default:'VAT Rate'}</th>
                                <th class="text-center fw-bold">{$_L['VAT Amount']|default:'VAT Amount'}</th>
                                <th id="cell-total" class="text-center fw-bold">{$_L['Total Incl. VAT']|default:'Total Incl. VAT'}</th>
                            </tr>
                            </thead>
                            <tbody>

                            {foreach $items as $item}
                                <tr>
                                    <td>
                                        {if $item['itemcode'] != ''}
                                            {$item['itemcode']}
                                        {else}
                                            {counter}
                                        {/if}
                                    </td>
                                    <td class="text-dark">

                                        {$item['description']}

                                        {if !empty($config['invoicing_allow_staff_selection_for_each_item'])}
                                            {if $item['staff_id'] != ''}
                                                <br>
                                                {if !empty($staffs[$item['staff_id']])}
                                                    <p class="badge bg-primary">{$_L['Staff']}:
                                                        {$staffs[$item['staff_id']]->fullname}
                                                    </p>
                                                {/if}
                                            {/if}
                                        {/if}

                                    </td>
                                    <td class="text-center">{formatCurrency($item['amount'],$d['currency_iso_code'],$format_currency_override)}</td>
                                    <td class="text-center">{$item['qty']}</td>
                                    <td class="text-center">
                                        {if ($item['discount_amount'] > 0)}
                                            {formatCurrency($item['total'],$d['currency_iso_code'])} <br>
                                            <span class="text-danger">- {formatCurrency((get_discount_amount($item['discount_amount'],$item['discount_type'],($item['qty'] * $item['amount']))),$d['currency_iso_code'])}</span>
                                        {else}
                                            {formatCurrency(($item['total'] + $item['discount_amount']),$d['currency_iso_code'])}
                                        {/if}
                                    </td>
                                    <td class="text-center">{$item['tax_rate']|default:0}%</td>
                                    <td class="text-center">{formatCurrency($item['taxamount'],$d['currency_iso_code'])}</td>
                                    <td class="text-center">{formatCurrency(($item['total'] + $item['taxamount']),$d['currency_iso_code'])}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>

                    {/if}



                </div>
                <div class="invoice-summary">
                    <div class="row">
                        <div class="col-sm-5 offset-md-7">
                            <table class="table h5 text-dark" style="border: 1px solid #aaa;">
                                <tbody>
                                <tr class="b-top-none">
                                    <td colspan="2">{$_L['Total Excluding VAT']|default:'Total Excluding VAT'}</td>
                                    <td class="text-end">{formatCurrency($d['subtotal'],$d['currency_iso_code'])}</td>
                                </tr>

                                {if ($d['discount']) neq '0.00'}
                                    <tr>
                                        <td colspan="2">{$_L['Discount']}{if $d['discount_type'] eq 'p'} ({$d['discount_value']}%){/if}</td>
                                        <td class="text-end">{formatCurrency($d['discount'],$d['currency_iso_code'])}</td>
                                    </tr>
                                {/if}

                                {if $config['tax_system'] == 'India'}
                                    <tr>
                                        <td colspan="2">GST</td>
                                        <td class="text-end">{formatCurrency($d['tax'],$d['currency_iso_code'])}</td>
                                    </tr>
                                {else}



                                    <tr>
                                        <td colspan="2">{$_L['Applied VAT Value']|default:'Applied VAT Value'}{if $d['taxrate'] > 0} ({$d['taxrate']}%){/if}</td>
                                        <td class="text-end">{formatCurrency($d['tax'],$d['currency_iso_code'])}</td>
                                    </tr>



                                {/if}

                                <tr class="h4" style="background: #f8f8f8;">
                                    <td colspan="2">{$_L['Total Including VAT']|default:'Total Including VAT'}</td>
                                    <td class="text-end">{formatCurrency($d['total'],$d['currency_iso_code'])}</td>
                                </tr>

                                {if ($d['credit']) neq '0.00'}
                                    <tr>
                                        <td colspan="2">{$_L['Total Paid']}</td>
                                        <td class="text-end">{formatCurrency($d['credit'],$d['currency_iso_code'])}</td>
                                    </tr>

                                    {if $i_due > 0 }
                                        <tr class="h4" style="background: #fff3f3;">
                                            <td colspan="2">{$_L['Amount Due']}</td>
                                            <td class="text-end">{formatCurrency($i_due,$d['currency_iso_code'])}</td>
                                        </tr>
                                    {/if}
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {if !empty($amount_words)}
                        <div class="row" style="margin-top: 6px;">
                            <div class="col-sm-6" style="font-size: 12px;">
                                <strong>{$_L['Amount in words']|default:'Amount in words'}:</strong> {$amount_words['en']|default:''|ucfirst}
                            </div>
                            {if !empty($amount_words['ar'])}
                                <div class="col-sm-6 text-end" style="font-size: 12px;" dir="rtl">
                                    <strong>المبلغ كتابةً:</strong> {$amount_words['ar']}
                                </div>
                            {/if}
                        </div>
                    {/if}
                </div>


            </div>


            {if $contract}
                <div class="my-3">
                    <h3>{$contract->title}</h3>
                    <a href="{$_url}contracts/view/{$contract->id}/{$contract->uuid}" target="_blank" class="btn btn-primary  btn-sm"><i class="fal fa-file-alt"></i> <span class="d-none d-md-inline">{__('View Contract')}</span></a>
                </div>
            {/if}

            {if ($trs_c neq '')}
                <h3>{$_L['Related Transactions']}</h3>
                <table class="table table-bordered sys_table">
                    <th>{$_L['Date']}</th>
                    <th>{$_L['Account']}</th>


                    <th class="text-end">{$_L['Amount']}</th>

                    <th>{$_L['Description']}</th>
                    <th>{__('Method')}</th>
                    <th>{__('Ref')}</th>




                    {foreach $trs as $tr}
                        <tr class="{if $tr['cr'] eq '0.00'}warning {else}info{/if}">
                            <td>{date( $config['df'], strtotime($tr['date']))}</td>
                            <td>{$tr['account']}</td>


                            <td class="text-end">{ib_money_format($tr['amount'],$config,$d['currency_symbol'])}</td>
                            <td>{$tr['description']}</td>
                            <td>{$tr['method']}</td>
                            <td>{$tr['ref']}</td>


                        </tr>
                    {/foreach}



                </table>
            {/if}

            {if $inv_files_c neq ''}

                <table class="table table-bordered table-hover sys_table">
                    <thead>
                    <tr>
                        <th class="text-end" data-sort-ignore="true" width="20px;">{$_L['Type']}</th>

                        <th>{$_L['File']}</th>

                        <th class="text-end" data-sort-ignore="true" width="170px;">{$_L['Download']}</th>
                    </tr>
                    </thead>
                    <tbody>

                    {foreach $inv_files as $ds}

                        <tr>

                            <td>
                                {if $ds['file_mime_type'] eq 'jpg' || $ds['file_mime_type'] eq 'png' || $ds['file_mime_type'] eq 'gif'}
                                    <i class="fal fa-file-image-o"></i>
                                {elseif $ds['file_mime_type'] eq 'pdf'}
                                    <i class="fal fa-file-pdf-o"></i>
                                {elseif $ds['file_mime_type'] eq 'zip'}
                                    <i class="fal fa-file-archive-o"></i>
                                {else}
                                    <i class="fal fa-file"></i>
                                {/if}
                            </td>


                            <td>

                                {$ds['title']}

                                {if $ds['file_mime_type'] eq 'jpg' || $ds['file_mime_type'] eq 'png' || $ds['file_mime_type'] eq 'gif'}

                                    <hr>

                                    <img src="{$app_url}storage/docs/{$ds['file_path']}" class="img-responsive" alt="{$ds['title']}">

                                {/if}

                            </td>

                            <td class="text-end">

                                <a href="{$_url}client/dl/{$ds['id']}_{$ds['file_dl_token']}/" class="md-btn md-btn-primary"><i class="fal fa-download"></i> {$_L['Download']}</a>

                            </td>


                        </tr>

                    {/foreach}

                    </tbody>



                </table>

            {/if}

            {if ($d['notes']) neq ''}
                <div class="well m-t">
                    {$d['notes']}
                </div>
            {/if}




            {if !empty($invoice_append_footer)}
                <div class="hr-line-dashed"></div>
                <div class="my-3">
                    {$invoice_append_footer}
                </div>
            {/if}




                {if isset($config['invoice_client_can_attach_signature']) && $config['invoice_client_can_attach_signature'] == 1 }

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="signaturePadArea">

                            </div>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <h4>{__('Sign above')}</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" id="clearSignature" class="btn btn-danger btn-sm">{__('Clear signature')}</button>
                        </div>
                    </div>

                {/if}


        </div>
    </section>



</div>



<input type="hidden" id="_url" name="_url" value="{$_url}">
<input type="hidden" id="_df" name="_df" value="{$config['df']}">
<input type="hidden" id="_lan" name="_lan" value="{$config['language']}">


<script>

    var _L = [];


    _L['Save'] = '{$_L['Save']}';
    _L['Submit'] = '{$_L['Submit']}';
    _L['Loading'] = '{$_L['Loading']}';
    _L['Media'] = '{$_L['Media']}';
    _L['OK'] = '{$_L['OK']}';
    _L['Cancel'] = '{$_L['Cancel']}';
    _L['Close'] = '{$_L['Close']}';
    _L['Close'] = '{$_L['Close']}';
    _L['are_you_sure'] = '{$_L['are_you_sure']}';
    _L['Saved Successfully'] = '{$_L['Saved Successfully']}';
    _L['Empty'] = '{$_L['Empty']}';

    var app_url = '{$app_url}';
    var base_url = '{$base_url}';

    {if ($config['animate']) eq '1'}
    var config_animate = 'Yes';
    {else}
    var config_animate = 'No';
    {/if}
    {$jsvar}
</script>



{if APP_STAGE == 'Dev'}
    <script src="{{APP_URL}}/ui/theme/default/js/app.min.js?v={_raid()}"></script>
{else}
    <script src="{{APP_URL}}/ui/theme/default/js/app.min.js?v=2"></script>
{/if}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>



{block name=script}{/block}

<script>
    $(function () {
        $('.amount').autoNumeric('init');
    });
</script>

{if isset($config['invoice_client_can_attach_signature']) && $config['invoice_client_can_attach_signature'] == 1 }

    <script src="{$app_url}ui/lib/jSignature.min.js"></script>

    <script>



        $(function () {

            var $signaturePadArea = $("#signaturePadArea");

            $signaturePadArea.jSignature({
                color:"#000",


            });

            {if $d['signature_data_base64'] != '' }

            $signaturePadArea.jSignature("setData","{$d['signature_data_base64']}");

            {/if}

            $signaturePadArea.bind('change', function(e){
                var signData = $signaturePadArea.jSignature("getData");
                $.post( "{$_url}client/save-invoice-signature", {
                    invoice_id: '{$d['id']}',
                    view_token: '{$d['vtoken']}',
                    signData: signData,
                });
            });


            $('#clearSignature').on('click',function () {
                $signaturePadArea.jSignature("reset");
            });



        });
    </script>

{/if}

<script>
    jQuery(document).ready(function() {
        // initiate layout and plugins

        var $paymentGateway = $('#paymentGateway');

        {if isset($xjq)}
        {$xjq}
        {/if}

        if(document.getElementById('btnPayNow'))
            {

                $('#btnPayNow').on('click',function (e) {
                    {$plugin_extra_js}

                    {if isset($payment_gateways_by_processor['stripe'])}

                    $stripeDiv = $('#stripeDiv');

                    if($paymentGateway.val() === 'stripe')
                        {
                            e.preventDefault();

                            $stripeDiv.show('slow');
                        }



                    {/if}

                });


                {if isset($payment_gateways_by_processor['stripe'])}

                // Create a Stripe client.
                var stripe = Stripe('{$payment_gateways_by_processor['stripe']['value']}');

// Create an instance of Elements.
            var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
            var style = {
                base: {
                    color: '#32325d',
                    lineHeight: '18px',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };

// Create an instance of the card Element.
            var card = elements.create('card', { style: style });

// Add an instance of the card Element into the `card-element` <div>.
            card.mount('#card-element');

// Handle real-time validation errors from the card Element.
            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

// Handle form submission.
            var form = document.getElementById('payment-form');
            var $btnStripeSubmit = $('#btnStripeSubmit');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                $btnStripeSubmit.prop('disabled',true);
                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        // Inform the user if there was an error.
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        $btnStripeSubmit.prop('disabled',false);
                    } else {
                        // Send the token to your server.
                        stripeTokenHandler(result.token);

                    }
                });
            });

// Submit the form with the token ID.
            function stripeTokenHandler(token) {
                // Insert the token ID into the form so it gets submitted to the server
                var form = document.getElementById('payment-form');
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);

                if(document.getElementById('input_amount'))
                {
                    var amount = document.getElementById('input_amount').value;
                    let amountInput = document.createElement('input');
                    amountInput.setAttribute('type', 'hidden');
                    amountInput.setAttribute('name', 'amount');
                    amountInput.setAttribute('value', amount);
                    form.appendChild(amountInput);
                }

                // Submit the form
                form.submit();



            }

            {/if}
            }


    });

</script>
{$config['footer_scripts']}
</body>

</html>
