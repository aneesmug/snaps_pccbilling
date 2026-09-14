<head>

    <meta charset="utf-8">


    <style type="text/css">

        body{
            padding: 0;
            margin: 0;
        }

        #invoice-POS {
            padding: 0;
            margin: 0 auto;
            width: 80mm;
            background: #FFF;
        }
        #invoice-POS ::selection {
            background-color: #FFF;
            color: #FFF;
        }
        #invoice-POS ::moz-selection {
            background-color: #FFF;
            color: #FFF;
        }
        #invoice-POS h1 {
            font-size: 1.6em;
            color: #222;
        }
        #invoice-POS h2 {
            font-size: 1em;
        }
        #invoice-POS h3 {
            font-size: 1.3em;
            font-weight: 300;
            line-height: 2em;
        }
        #invoice-POS p {
            font-size: 0.9em;
            color: #222;
            line-height: 1.2em;
        }
        #invoice-POS #top, #invoice-POS #mid, #invoice-POS #bot {
            /* Targets all id with 'col-' */
            border-bottom: 1px solid #EEE;
        }
        #invoice-POS #top {
            min-height: 100px;
        }
        #invoice-POS #mid {
            min-height: 80px;
        }
        #invoice-POS #bot {
            min-height: 50px;
        }
        #invoice-POS #top .logo {
            height: 80px;
            width: 80px;
            background-size: 80px 80px;
        }
        #invoice-POS .info {
            display: block;
            margin-left: 0;
        }
        #invoice-POS .title {
            float: right;
        }
        #invoice-POS .title p {
            text-align: right;
        }
        #invoice-POS table {
            width: 100%;
            border-collapse: collapse;
        }
        #invoice-POS .tabletitle {
            font-size: 0.7em;
            background: #EEE;
        }
        #invoice-POS .service {
            border-bottom: 1px solid #EEE;
        }
        #invoice-POS .item {
            width: 24mm;
        }
        #invoice-POS .itemtext {
            font-size: 0.8em;
        }
        #invoice-POS #legalcopy {
            margin-top: 5mm;
        }
        #invoice-POS .signature {
            margin-top: 5mm;
            border-top: 1px solid #EEE;
            text-align: center;
        }


        .table.invoice-items{
            border: 1px solid #dee2e6;
        }

        .table.invoice-items td, .table.invoice-items th {
            border: 1px solid #dee2e6;
        }


    </style>



</head>
<body class="fixed-nav">

<section class="">
    <div class="">
        <div class="invoice">

            <div id="invoice-POS">
                <center id="top">
                    <div class="mt-2">
                        <table>
                            <tr>
                                <td>
                                    <img src="{$app_url}storage/system/{$config['logo_default']}" alt="{$config['CompanyName']}" class="img-fluid"  style="margin-bottom: 15px; max-width: 100px;">
                                </td>
                                <td>
                                    {Invoice::getInvoiceNumber($d)}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="info">
                        <table>
                            <tr>
                                <td>
                                    <p class="info">

                                        {$config['caddress']}

                                        {if isset($config['vat_number']) && ($config['vat_number'] != '')}

                                            <br>
                                            <strong>{sp_config_or_default('label_tax_number',$_L['Vat number'])}:</strong> {$config['vat_number']}

                                        {/if}


                                    </p>
                                </td>
                                <td>

                                </td>
                            </tr>
                        </table>
                    </div><!--End Info-->
                </center><!--End InvoiceTop-->

                <div id="mid">
                    <div class="info" style="margin-top: 5px;">

                        <table>
                            <tr>
                                <td>
                                    <strong>{{__('Invoiced To')}}</strong>
                                    <p>
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

                                    </p>
                                </td>
                                <td align="right">
                                    <img src="{$qr_code_admin_url}" style="height: 100px;">
                                </td>
                            </tr>
                        </table>



                        <div style="margin-top: 5px; margin-bottom: 5px; text-align: center;">

                            <img src="data:image/png;base64,{base64_encode($barcode_generator->getBarcode($d['id'], $barcode_generator::TYPE_CODE_128))}">

                            <p>
                                <strong>{Invoice::getInvoiceNumber($d)}</strong>
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>


</section>


</body>

<!--End Invoice-->
