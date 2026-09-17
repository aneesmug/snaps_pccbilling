{extends file="$layouts_admin"}


{block name="head"}


    <style>

        .btn-default {
            color: #333;
            background-color: #fff;
            border-color: #ccc;
        }

        .btn-default:hover, .btn-default:focus, .btn-default:active, .btn-default.active {
            color: #333;
            background-color: #fff;
            border-color: #ccc;
        }

        {if $pos eq 'pos'}
        .pos_item {

        {if $config['nstyle'] == 'dark_mode'}
            background: #182138;
        {else}
            background: #f3f6f9;
        {/if}


            cursor: pointer;
        }

        .pos_item:hover {
            background: #2196f3;
            color: #ffffff;
        }

        .pos-split-layout {
            align-items: flex-start;
        }

        .pos-left-panel,
        .pos-right-panel {
            background: #f8f9fa;
            border: 1px solid #e5e9f2;
            border-radius: 10px;
            padding: 12px;
        }

        .pos-left-panel #block_items {
            max-height: 640px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .pos-left-panel .pos_item {
            border: 1px solid #e5e9f2;
            border-radius: 8px;
            min-height: 160px;
            padding: 10px;
        }

        .pos-left-panel .pos_item img {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 50%;
        }

        .pos-right-panel #invoice_primary_controls {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -6px 12px;
        }

        .pos-right-panel #invoice_primary_controls > [class*='col-sm-'] {
            width: 50%;
            padding: 0 6px;
        }

        .pos-right-panel #invoice_primary_controls .help-block {
            margin-bottom: 0;
        }

        .pos-right-panel .invoice-total {
            margin-bottom: 0;
        }

        .pos-cart-heading {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 18px;
        }

        .pos-summary-grid {
            border-top: 1px solid #dfe6f1;
            border-bottom: 1px solid #dfe6f1;
            display: grid;
            gap: 0;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 18px;
        }

        .pos-summary-item {
            align-items: center;
            display: flex;
            justify-content: space-between;
            min-height: 54px;
            padding: 0 14px;
        }

        .pos-summary-label {
            color: #1f2a44;
            font-size: 14px;
            font-weight: 500;
        }

        .pos-summary-value {
            color: #101828;
            font-size: 22px;
            font-weight: 700;
        }

        .pos-summary-value.small {
            font-size: 18px;
        }

        .pos-grand-total {
            align-items: center;
            background: #202b3f;
            border-radius: 4px;
            color: #ffffff;
            display: flex;
            justify-content: center;
            margin-top: 16px;
            min-height: 58px;
            padding: 12px 18px;
        }

        .pos-grand-total-label {
            font-size: 16px;
            font-weight: 700;
            margin-right: 6px;
        }

        .pos-grand-total-value {
            font-size: 38px;
            font-weight: 800;
            line-height: 1;
        }

        .pos-payment-actions {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            margin-top: 18px;
        }

        .pos-payment-btn {
            border: 0;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            min-height: 46px;
        }

        .pos-payment-btn.card { background: #12b886; }
        .pos-payment-btn.cash { background: #ef3340; }
        .pos-payment-btn.paypal { background: #5b4ce6; }
        .pos-payment-btn.cheque { background: #1f86e5; }
        .pos-payment-btn.gift-card { background: #b53874; }
        .pos-payment-btn.method-0 { background: #12b886; }
        .pos-payment-btn.method-1 { background: #ef3340; }
        .pos-payment-btn.method-2 { background: #5b4ce6; }
        .pos-payment-btn.method-3 { background: #1f86e5; }
        .pos-payment-btn.method-4 { background: #b53874; }

        .pos-payment-btn:focus,
        .pos-payment-btn:hover {
            color: #ffffff;
            opacity: 0.94;
        }

        @media (max-width: 1199px) {
            .pos-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pos-payment-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .pos-right-panel #invoice_primary_controls > [class*='col-sm-'] {
                width: 100%;
            }

            .pos-summary-grid,
            .pos-payment-actions {
                grid-template-columns: 1fr;
            }

            .pos-grand-total-value {
                font-size: 28px;
            }
        }

        .pos-compact-hidden {
            display: none !important;
        }

        #invoice_items.pos-cart-table-simple thead tr:nth-child(2) {
            display: none;
        }

        #invoice_items.pos-cart-table-simple thead tr:first-child th:nth-child(1),
        #invoice_items.pos-cart-table-simple thead tr:first-child th:nth-child(5),
        #invoice_items.pos-cart-table-simple thead tr:first-child th:nth-child(6),
        #invoice_items.pos-cart-table-simple tbody td:nth-child(1),
        #invoice_items.pos-cart-table-simple tbody td:nth-child(5),
        #invoice_items.pos-cart-table-simple tbody td:nth-child(6) {
            display: none;
        }

        /* Style Total (incl. VAT) column green */
        #invoice_items.pos-cart-table-simple tbody td:nth-child(8) input.lvtotal {
            color: #28a745;
            font-weight: bold;
        }



        {/if}

    </style>
{/block}


{block name="content"}

    <form id="invform" method="post">

        <div class="row" id="ibox_form">



            <div class="alert alert-danger" id="emsg" style="display: none;">
                <span id="emsgbody"></span>
            </div>

            <div class="col-md-12">


                <div class="panel">

                    <div class="panel-hdr">
                        <h2>


                            {if $invoice}

                                {if $action === 'credit-note'}
                                    {{__('Credit Note')}}-
                                    {else}
                                    {{__('Invoice')}}-
                                {/if}

                                    {$invoice->invoicenum}{if $invoice->cn neq ''} {$invoice->cn} {else}
                                    {$invoice->id} {/if}

                                {else}

                                {if $action === 'credit-note'}
                                    {predict_next_serial($config,'credit-note')}
                                {else}
                                    {predict_next_serial($config,'invoice')}
                                {/if}



                                {if $project}
                                    [{$project->name}]

                                    <input type="hidden" name="pid" value="{$project->id}">
                                {/if}

                            {/if}

                        </h2>
                        <div class="panel-toolbar {if $pos eq 'pos'}d-none{/if}">

                            {if $invoice}

                                <input type="hidden" name="invoice_id" value="{$invoice->id}">

                            {else}

                                <input type="hidden" name="invoice_id" value="">

                            {/if}

                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary" id="submit"> {$_L['Save']}</button>
                                <button class="btn btn-sm btn-info"
                                        id="save_n_close"> {$_L['Save n Close']}</button>
                            </div>
                        </div>
                    </div>

                    <div class="panel-container">
                        <div class="panel-content">


                            <div class="row">
                                <div class="col-md-12">

                                    <div id="invoice_primary_controls" class='row'>

                                        <div class='col-sm-4'>
                                            <div class='mb-3'>
                                                <label for="user_title">{$_L['Customer']}</label>

                                                <select id="cid" name="cid" class="form-select">
                                                    <option value="">{$_L['Select Contact']}...</option>
                                                    {foreach $c as $cs}
                                                        <option value="{$cs['id']}"
                                                                {if $p_cid eq ($cs['id'])}selected="selected" {/if}>{if $cs['company'] neq ''} {$cs['company']} - {/if} {$cs['account']} {if $cs['email'] neq ''}- {$cs['email']}{/if} {if $cs['phone'] neq ''}- {$cs['phone']}{/if} {if $cs['code']}[{$cs['code']}]{/if} </option>
                                                    {/foreach}

                                                </select>
                                                <span class="help-block"><a href="#"
                                                                            id="contact_add">| {$_L['Or Add New Customer']}</a> </span>
                                            </div>
                                        </div>
                                        <div class='col-sm-4'>
                                            <div class='mb-3'>
                                                <label for="aid">{$_L['Staff']}</label>
                                                <select class="form-select" name="aid" id="aid">
                                                    {foreach $staffs as $owner}
                                                        <option value="{$owner->id}"
                                                                {if $invoice}
                                                                {if $invoice->aid eq $owner->id}selected="selected"{/if}
                                                            {else}
                                                            {if $owner->id == $user->id}selected{/if}
                                                        {/if} >{$owner->fullname}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                        <div class='col-sm-2'>
                                            <div class="mb-3">
                                                <label for="currency">{$_L['Currency']}</label>

                                                <select id="currency" name="currency" class="form-select">

                                                    {foreach $currencies as $key=>$value}
                                                        <option value="{$key}"


                                                                {if $invoice}
                                                                    {if $invoice->currency_iso_code == $key}selected {/if}
                                                                {else}
                                                                    {if $config['home_currency'] eq ($key)}selected {/if}
                                                                {/if}


                                                                data-decimal-mark="{$value['decimal_mark']}" data-thousands-separator="{$value['thousands_separator']}" data-symbol="{$value['symbol']}"

                                                                {if $value['symbol_first']}
                                                                    data-symbol-first="yes"
                                                                {else}
                                                                    data-symbol-first="no"
                                                                {/if}



                                                        >{$key}</option>
                                                        {foreachelse}
                                                    {/foreach}

                                                </select>

                                            </div>  
                                        </div>
                                        <div class='col-sm-2'>
                                            <div class="mb-3" id="status_wrap">
                                                <label for="status">{$_L['Status']}</label>

                                                <select id="status" name="status" class="form-select">
                                                    <option value="Published" {if $invoice && $invoice->status != 'Draft'}selected{/if}>{$_L['Published']}</option>
                                                    <option value="Draft" {if $invoice && $invoice->status == 'Draft'}selected{/if}>{$_L['Draft']}</option>
                                                </select>

                                            </div>

                                            <div class="mb-3" id="pos_actions_wrap" style="display:none;">
                                                <label>{__('Actions')}</label>
                                                <div class="d-flex" style="gap:6px;">
                                                    <button type="button" class="btn btn-outline-secondary flex-fill" id="pos_save_close_btn">Save Close</button>
                                                    <button type="button" class="btn btn-warning flex-fill" id="pos_save_draft_btn">Draft</button>
                                                    <button type="button" class="btn btn-primary flex-fill" id="pos_save_btn">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="invoice_meta_extended">
                                    {if !empty($config['invoice_group'])}
                                        <div class="mb-3">
                                            <label>{__('Group')}</label>
                                            <select class="form-select" name="group_id">
                                                <option value="0">{__('None')}</option>
                                                {foreach $invoice_groups as $group}
                                                    <option value="{$group->id}" {if $invoice && $invoice->group_id == $group->id}selected{/if}>{$group->name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {/if}

                                    {if !empty($config['invoice_single_service'])}
                                        <div class="mb-3">
                                            <label>{__('Service')}</label>
                                            <select class="form-select" id="service_id" name="service_id">
                                                <option value="0">{__('None')}</option>
                                                {foreach $services as $service}
                                                    <option data-price="{numberFormatUsingCurrency($service->sales_price,$config['home_currency'])}" value="{$service->id}" {if $invoice && $invoice->service_id == $service->id}selected{/if}>{$service->name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {/if}


                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="mb-3">
                                                <label for="invoice_title">{$_L['Title']}  <small><em>({$_L['optional']})</em></small></label>

                                                <input type="text" class="form-control" id="invoice_title" name="title" {if $invoice}value="{$invoice->title}" {/if}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='row'>


                                        <div class='col-sm-4'>
                                            <div class="mb-3">
                                                <label for="address">{$_L['Address']}</label>

                                                <textarea id="address" readonly class="form-control" rows="5"></textarea>
                                            </div>
                                        </div>
                                        <div class='col-sm-4'>
                                            <div class="mb-3">
                                                <label for="invoicenum">{__('Prefix')}</label>

                                                <input type="text" class="form-control" id="invoicenum" name="invoicenum" {if $invoice}value="{$invoice->invoicenum}" {else}

                                                        {if $action == 'credit-note'}

                                                            {if !empty($config['credit_note_prefix'])}

                                                                value="{sp_transform_string_template($config['invoice_code_prefix'])}"



                                                                {else}

                                                                value="{__('CN')}"

                                                                {/if}

                                                            {else}
                                                            value="{sp_transform_string_template($config['invoice_code_prefix'])}"
                                                        {/if}

                                                        {/if}>
                                            </div>

                                            <div class="mb-3">
                                                <label for="cn">{__('Number')} #</label>

                                                <input type="text" class="form-control" id="cn" name="cn" {if $invoice}value="{$invoice->cn}" {else} value="{str_pad($config['invoice_code_current_number'], $config['number_pad'], '0', STR_PAD_LEFT)}" {/if}>
                                                {if $action !== 'credit-note'}
                                                    <span class="help-block">{$_L['invoice_number_help']}</span>
                                                {/if}


                                            </div>

                                        </div>
                                        <div class='col-sm-4'>
                                            {if $config['invoice_receipt_number'] eq '1'}
                                                <div class="mb-3">
                                                    <label for="receipt_number">{$_L['Receipt Number']}</label>

                                                    <input type="text" class="form-control" id="receipt_number"
                                                           name="receipt_number" {if $invoice}value="{$invoice->receipt_number}" {/if}>
                                                </div>
                                            {else}
                                                <input type="hidden" name="receipt_number" id="receipt_number" value="">
                                            {/if}

                                            <div class="mb-3">
                                                <label for="show_quantity_as">{$_L['Show quantity as']}</label>

                                                <input type="text" class="form-control" id="show_quantity_as"
                                                       name="show_quantity_as"

                                                        {if $invoice}
                                                            value="{$invoice->show_quantity_as}"
                                                        {else}

                                                            value="{if $config['show_quantity_as'] eq ''}{$_L['Qty']}{else}{$config['show_quantity_as']}{/if}"
                                                        {/if}

                                                >

                                            </div>

                                            {if $recurring}
                                                <div class="mb-3">
                                                    <label for="repeat">{$_L['Repeat Every']}</label>

                                                    <select class="form-select" name="repeat" id="repeat">

                                                        <option value="daily" {if $invoice && $invoice->r == '+1 day'} selected{/if}>{$_L['Daily']}</option>
                                                        <option value="week1" {if $invoice && $invoice->r == '+1 week'} selected{/if}>{$_L['Weekly']}</option>
                                                        <option value="weeks2" {if $invoice && $invoice->r == '+2 weeks'} selected{/if}>{$_L['Weeks_2']}</option>
                                                        <option value="weeks3" {if $invoice && $invoice->r == '+3 weeks'} selected{/if}>{$_L['Weeks_3']}</option>
                                                        <option value="weeks4" {if $invoice && $invoice->r == '+4 weeks'} selected{/if}>{$_L['Weeks_4']}</option>
                                                        <option value="month1" {if $invoice} {if $invoice->r == '+1 month'} selected{/if} {else} selected {/if}>{$_L['Month']}</option>
                                                        <option value="months2" {if $invoice && $invoice->r == '+2 months'} selected{/if}>{$_L['Months_2']}</option>
                                                        <option value="months3" {if $invoice && $invoice->r == '+3 months'} selected{/if}>{$_L['Months_3']}</option>
                                                        <option value="months6" {if $invoice && $invoice->r == '+6 months'} selected{/if}>{$_L['Months_6']}</option>
                                                        <option value="year1" {if $invoice && $invoice->r == '+1 year'} selected{/if}>{$_L['Year']}</option>
                                                        <option value="years2" {if $invoice && $invoice->r == '+2 years'} selected{/if}>{$_L['Years_2']}</option>
                                                        <option value="years3" {if $invoice && $invoice->r == '+3 years'} selected{/if}>{$_L['Years_3']}</option>
                                                        <option value="years4" {if $invoice && $invoice->r == '+4 years'} selected{/if}>{__('4 Years')}</option>
                                                        <option value="years5" {if $invoice && $invoice->r == '+5 years'} selected{/if}>{__('5 Years')}</option>
                                                        <option value="years10" {if $invoice && $invoice->r == '+10 years'} selected{/if}>{__('10 Years')}</option>

                                                    </select>
                                                </div>
                                            {else}
                                                <input type="hidden" name="repeat" id="repeat" value="0">
                                            {/if}



                                        </div>
                                    </div>


                                    {if $config['tax_system'] eq 'India'}
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label for="duedate">GSTIN</label>
                                                    <input type="text" class="form-control" id="business_number" name="business_number">

                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label for="duedate">Place of Supply</label>
                                                    <select id="place_of_supply" name="place_of_supply"
                                                            class="form-control">


                                                        {if $invoice}

                                                            <option value="{$config['business_location']}" {if $invoice->is_same_state == 1} selected{/if}>{$config['business_location']}</option>
                                                            <option value="other" {if $invoice->is_same_state == 0} selected{/if}>Other</option>

                                                        {else}

                                                            <option value="{$config['business_location']}">{$config['business_location']}</option>
                                                            <option value="other">Other</option>


                                                        {/if}


                                                        {*{foreach $states as $state}*}
                                                        {*<option value="{$state['name']}"*}
                                                        {*{if $invoice}*}
                                                        {*{if $contact && $contact->state == $state['name']} selected{/if}*}
                                                        {*{else}*}
                                                        {*{if $config['business_location'] == $state['name']}*}
                                                        {*selected*}
                                                        {*{/if}*}
                                                        {*{/if}*}

                                                        {*>{$state['name']}</option>*}
                                                        {*{/foreach}*}



                                                    </select>

                                                </div>
                                            </div>


                                        </div>
                                    {/if}


                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="mb-3">
                                                <label for="idate">{$_L['Date']}</label>

                                                <input type="text" class="form-control" id="idate" name="idate" datepicker
                                                       data-date-format="yyyy-mm-dd" data-auto-close="true"
                                                        {if $invoice}
                                                            value="{$invoice->date}"
                                                        {else}
                                                            value="{$idate}"
                                                        {/if}
                                                >
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="mb-3">
                                                <label for="duedate">{$_L['Payment Terms']}</label>

                                                {if $invoice}

                                                    <input type="text" class="form-control" id="duedate" name="duedate" datepicker
                                                           data-date-format="yyyy-mm-dd" data-auto-close="true"
                                                           value="{$invoice->duedate}">

                                                {else}

                                                    <select class="form-select" name="duedate" id="duedate">
                                                        <option value="due_on_receipt" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'due_on_receipt' )} selected{/if}>{$_L['Due On Receipt']}</option>
                                                        <option value="days3" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days3' )} selected{/if}>{$_L['days_3']}</option>
                                                        <option value="days5" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days5' )} selected{/if}>{$_L['days_5']}</option>
                                                        <option value="days7" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days7' )} selected{/if}>{$_L['days_7']}</option>
                                                        <option value="days10" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days10' )} selected{/if}>{$_L['days_10']}</option>
                                                        <option value="days15" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days15' )} selected{/if}>{$_L['days_15']}</option>
                                                        <option value="days30" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days30' )} selected{/if}>{$_L['days_30']}</option>
                                                        <option value="days45" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days45' )} selected{/if}>{$_L['days_45']}</option>
                                                        <option value="days60" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days60' )} selected{/if}>{$_L['days_60']}</option>
                                                    </select>

                                                {/if}


                                            </div>
                                        </div>
                                        <div class="col-sm-4">

                                            <div class="mb-3">
                                                <label for="allow_partial_payment">{__('Allow Partial Payment')}</label>
                                            </div>

                                            <label class="switch s-icons s-outline s-outline-primary">
                                                <input type="checkbox" id="allow_partial_payment" name="allow_partial_payment" value="1" {if $invoice && $invoice->allow_partial_payment}checked{/if}>
                                                <span class="slider round"></span>
                                            </label>

                                        </div>
                                    </div>






                                    {$extraHtml}

                                    </div>


                                </div>
                            </div>

                            {if $pos eq 'pos'}
                                <div class="row g-3 pos-split-layout">
                                    <div class="col-lg-6">
                                        <div class="pos-left-panel">
                                            <div class="ib-search-bar mb-3">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="ib_search_input"
                                                           placeholder="{$_L['Search']}..." autofocus data-list=".list_pos_items"></div>
                                            </div>

                                            <div id="block_items" class="list_pos_items row g-2"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="pos-right-panel">
                                            <div id="pos_controls_mount"></div>
                                            <div class="pos-cart-heading">Selected Products</div>
                            {/if}


                            <div class="table-responsive mt-3">

                                {if $config['tax_system'] == 'India'}
                                    <table class="table table-bordered invoice-table" id="invoice_items">


                                        <thead>


                                        <tr>

                                            <th width="25%" rowspan="2">{$_L['Item Name']}</th>
                                            <th rowspan="2">HSN / SAC</th>
                                            <th rowspan="2">{if $config['show_quantity_as'] eq ''}{$_L['Qty']}{else}{$config['show_quantity_as']}{/if}</th>
                                            <th rowspan="2">{$_L['Price']}</th>

                                            <th colspan="2">{$_L['Discount']}</th>

                                            <th rowspan="2" style="width: 80px;">Rate</th>

                                            <th colspan="3" class="text-center">
                                                Tax Values (Rs.)
                                            </th>

                                            <th rowspan="2">{$_L['Total']}</th>


                                        </tr>

                                        <tr>

                                            <th colspan="2">

                                                {if $invoice}

                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeP" type="radio" name="discount_type" value="p"
                                                               {if $invoice->discount_type == 'p'}checked{/if}
                                                        >%
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeF" type="radio" name="discount_type" value="f" {if $invoice->discount_type == 'f'}checked{/if}>Rs
                                                    </label>

                                                {else}

                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeP" type="radio" name="discount_type" value="p" checked>%
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeF" type="radio" name="discount_type" value="f">Rs
                                                    </label>

                                                {/if}



                                            </th>


                                            <th>CGST</th>
                                            <th>SGST</th>
                                            <th>IGST</th>

                                        </tr>

                                        </thead>
                                        <tbody>

                                        {if $items}

                                            {foreach $items as $item}



                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control item_name" name="desc[]" value="{$item->description}">
                                                        <input type="hidden" name="item_code[]" value="{$item->itemcode}"></td>
                                                    <td><input type="text" class="form-control tax_code" value="{$item->tax_code}" name="tax_code[]"></td>
                                                    <td><input type="text" class="form-control qty" value="{numberFormatUsingCurrency($item->qty,$invoice->currency_iso_code)}" name="qty[]"></td>
                                                    <td><input type="text" class="form-control item_price" name="amount[]" value="{numberFormatUsingCurrency($item->amount,$invoice->currency_iso_code)}">
                                                    <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value="{numberFormatUsingCurrency($item->discount_amount,$invoice->currency_iso_code)}">
                                                    </td>

                                                    <td>



                                                        <select class="form-select taxed" name="taxed[]">

                                                            {foreach $t as $ts}
                                                                <option value="{$ts['rate']}"
                                                                        {if $ts['rate'] eq $item['tax_rate']}selected{/if}>{$ts['name']}</option>
                                                            {/foreach}


                                                        </select>





                                                    </td>

                                                    <td>
                                                        <input type="text" class="form-control cgst" name="cgst[]" disabled value="">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control sgst" name="sgst[]" disabled value="">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control igst" name="igst[]" disabled value="">
                                                    </td>

                                                    <td class="ltotal"><input type="text" class="form-control lvtotal" readonly=""
                                                                              value=""></td>
                                                </tr>

                                            {/foreach}

                                        {/if}


                                        <tr>
                                            <td>
                                                <input type="text" class="form-control item_name" name="desc[]" value="">
                                                <input type="hidden" name="item_code[]" value=""></td>
                                            <td><input type="text" class="form-control tax_code" value="" name="tax_code[]"></td>
                                            <td><input type="text" class="form-control qty" value="" name="qty[]"></td>
                                            <td><input type="text" class="form-control item_price" name="amount[]" value="">
                                            <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value="">
                                            </td>

                                            <td><select class="form-select taxed" name="taxed[]">
                                                    {foreach $t as $ts}
                                                        <option value="{$ts['rate']}"
                                                                {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option>
                                                    {/foreach} </select></td>

                                            <td>
                                                <input type="text" class="form-control cgst" name="cgst[]" disabled value="">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control sgst" name="sgst[]" disabled value="">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control igst" name="igst[]" disabled value="">
                                            </td>

                                            <td class="ltotal"><input type="text" class="form-control lvtotal" readonly=""
                                                                      value=""></td>
                                        </tr>

                                        </tbody>
                                    </table>

                                {else}

                                    <table class="table table-bordered invoice-table" id="invoice_items">
                                        <thead>
                                        <tr>

                                            <th width="5%">{__('Item')}</th>

                                            <th width="30%">{$_L['Item Name']}</th>
                                            <th width="8%">{if $config['show_quantity_as'] eq ''}{$_L['Qty']}{else}{$config['show_quantity_as']}{/if}</th>
                                            <th width="15%">{$_L['Price']}</th>

                                            <th colspan="2" width="8%">{$_L['Discount']}</th>

                                            <th width="10%">{$_L['Tax']}</th>

                                            <th width="10%">{__('VAT')}</th>

                                            <th width="20%">{$_L['Total']}</th>

                                            <th width="5%">{__('Action')}</th>


                                        </tr>
                                        <tr>
                                            <th colspan="4"></th>
                                            <th colspan="2">

                                                {if $invoice}


                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeP" type="radio" name="discount_type" value="p" {if $invoice->discount_type == 'p'}checked{/if}> <span  data-bs-toggle="tooltip" data-placement="top" title="{$_L['Percentage']}">%</span>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeF" type="radio" name="discount_type" value="f" {if $invoice->discount_type == 'f'}checked{/if}> <span data-bs-toggle="tooltip" data-placement="top" title="{$_L['Fixed Amount']}" id="fixedDiscountText">
                                                    {if isset($currencies[$config['home_currency']])}
                                                        {$currencies[$config['home_currency']]['symbol']}
                                                    {else}

                                                    {/if}
                                                </span>
                                                    </label>

                                                {else}

                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeP" type="radio" name="discount_type" value="p" checked> <span  data-bs-toggle="tooltip" data-placement="top" title="{$_L['Percentage']}">%</span>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input class="discountType" id="discountTypeF" type="radio" name="discount_type" value="f"> <span data-bs-toggle="tooltip" data-placement="top" title="{$_L['Fixed Amount']}" id="fixedDiscountText">
                                                    {if isset($currencies[$config['home_currency']])}
                                                        {$currencies[$config['home_currency']]['symbol']}
                                                    {else}

                                                    {/if}
                                                </span>
                                                    </label>

                                                {/if}



                                            </th>
                                            <th colspan="4"></th>
                                        </tr>
                                        </thead>
                                        <tbody>


                                        {if $items}

                                            {foreach $items as $item}

                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button>
                                                    </td>
                                                    <td>
                                                        {if !empty($config['invoicing_allow_staff_selection_for_each_item'])}

                                                            <div class="mb-3">
                                                                <select name="staff_id[]" class="form-select"><option value="0">{{__('Select Staff')}}</option>{foreach $staffs as $employee}<option value="{$employee->id}"
                                                                    {if $item->staff_id == $employee->id} selected {/if}
                                                                    >{$employee->fullname}</option>{/foreach}</select>
                                                            </div>

                                                        {/if}
                                                        <input type="text" class="form-control item_name" name="desc[]" value="{$item->description}">
                                                        <input type="hidden" name="item_code[]" value="{$item->itemcode}"></td>
                                                    <td><input type="text" class="form-control qty" value="{numberFormatUsingCurrency($item->qty,$invoice->currency_iso_code)}" name="qty[]"></td>
                                                    <td><input type="text" class="form-control item_price" name="amount[]" value="{numberFormatUsingCurrency($item->amount,$invoice->currency_iso_code)}"></td>
                                                    <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value="{numberFormatUsingCurrency($item->discount_amount,$invoice->currency_iso_code)}">
                                                    </td>
                                                    <td><select class="form-select taxed" name="taxed[]">
                                                            {foreach $t as $ts}
                                                                <option value="{$ts['rate']}"
                                                                        {if $item->tax_rate eq $ts['rate']}selected{/if}>{$ts['name']}</option>
                                                            {/foreach} </select></td>

                                                        <td><input type="text" class="form-control lvat" readonly="" value=""></td>

                                                    <td class="ltotal"><input type="text" class="form-control lvtotal" readonly=""
                                                                              value=""></td>
                                                    <td class="text-center align-middle row-action-cell">
                                                        <button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button>
                                                    </td>
                                                </tr>

                                            {/foreach}

                                        {/if}
                                        <tr>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button>
                                            </td>
                                            <td>

                                                {if !empty($config['invoicing_allow_staff_selection_for_each_item'])}
                                                    <div class='mb-3'>

                                                        <select name="staff_id[]"  class="form-select"><option value="0">{{__('Select Staff')}}</option>{foreach $staffs as $employee}<option value="{$employee->id}">{$employee->fullname}</option>{/foreach}</select>

                                                    </div>

                                                {/if}


                                                <input type="text" class="form-control item_name" name="desc[]" value="">
                                                <input type="hidden" name="item_code[]" value="">
                                            </td>
                                            <td><input type="text" class="form-control qty" value="" name="qty[]"></td>
                                            <td><input type="text" class="form-control item_price" name="amount[]" value=""></td>
                                            <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value="">
                                            </td>
                                            <td><select class="form-select taxed" name="taxed[]">
                                                    {foreach $t as $ts}
                                                        <option value="{$ts['rate']}"
                                                                {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option>
                                                    {/foreach} </select></td>

                                                <td><input type="text" class="form-control lvat" readonly="" value=""></td>

                                            <td class="ltotal"><input type="text" class="form-control lvtotal" readonly=""
                                                                      value=""></td>
                                            <td class="text-center align-middle row-action-cell">
                                                <button type="button" class="btn btn-primary btn-sm row-add" data-bs-toggle="tooltip" data-placement="top" title="{__('Add blank Line')}"><i class="fal fa-plus"></i></button>
                                            </td>
                                        </tr>



                                        </tbody>
                                    </table>

                                {/if}


                            </div>
                            <!-- /table-responsive -->

                            {if $pos eq 'pos'}
                                <div id="pos_summary_mount" class="mt-3"></div>
                            {/if}

                            {if $pos eq 'pos'}
                                        </div>
                                    </div>
                                </div>
                            {/if}
                            <button type="button" class="d-none" id="blank-add"></button>
                            <button type="button" class="d-none" id="item-add"><i
                                        class="fal fa-search"></i> {$_L['Add Product OR Service']}</button>
                            <hr>

                            <input type="hidden" name="pos_payment_method" id="pos_payment_method" value="">
                            <input type="hidden" name="pmethod" id="pmethod" value="">

                            <div id="invoice_totals_wrap" class="row">
                                {if $pos eq 'pos'}
                                    <div class="col-md-12">
                                        <div class="pos-summary-grid">
                                            <div class="pos-summary-item">
                                                <span class="pos-summary-label">Total Item</span>
                                                <span class="pos-summary-value small" id="pos_total_items">00</span>
                                            </div>
                                            <div class="pos-summary-item">
                                                <span class="pos-summary-label">Total Cost</span>
                                                <span class="pos-summary-value small" id="sub_total">0.00</span>
                                            </div>
                                            <div class="pos-summary-item">
                                                <span class="pos-summary-label">Discount</span>
                                                <span class="pos-summary-value small" id="discount_amount_total">0.00</span>
                                            </div>
                                            <div class="pos-summary-item">
                                                <span class="pos-summary-label">Coupon</span>
                                                <span class="pos-summary-value small" id="pos_coupon_total">0.00</span>
                                            </div>
                                            <div class="pos-summary-item">
                                                <span class="pos-summary-label">Tax</span>
                                                <span class="pos-summary-value small" id="taxtotal">0.00</span>
                                            </div>
                                            <div class="pos-summary-item">
                                                <span class="pos-summary-label">Shipping</span>
                                                <span class="pos-summary-value small" id="pos_shipping_total">0.00</span>
                                            </div>
                                        </div>
                                        <div class="pos-grand-total">
                                            <span class="pos-grand-total-label">Grand Total:</span>
                                            <span class="pos-grand-total-value" id="total">0.00</span>
                                        </div>
                                        <div class="pos-payment-actions">
                                            {if isset($payment_methods) && count($payment_methods) > 0}
                                                {foreach $payment_methods as $pm}
                                                    <button type="button" class="pos-payment-btn method-{($pm@iteration-1)%5}" data-payment-method="{$pm->name|escape}">{$pm->name|escape}</button>
                                                {/foreach}
                                            {else}
                                                <button type="button" class="pos-payment-btn method-0" data-payment-method="Cash">Cash</button>
                                            {/if}
                                        </div>
                                    </div>
                                {else}
                                    <div class="col-md-4 offset-md-8">
                                        <table class="table invoice-total">
                                            <tbody>
                                            <tr>
                                                <td><strong>{$_L['Sub Total']} :</strong></td>
                                                <td id="sub_total" class="amount">0.00
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <strong>{$_L['Discount']} <span id="is_pt"></span> : </strong>
                                                </td>
                                                <td id="discount_amount_total" class="amount">0.00
                                                </td>
                                            </tr>
                                            {if $config['tax_system'] eq 'default'}
                                                <tr>
                                                    <td><strong>{$_L['TAX']} :</strong></td>
                                                    <td id="taxtotal" class="amount">0.00
                                                    </td>
                                                </tr>
                                            {elseif $config['tax_system'] eq 'ca_quebec'}
                                                <div id="taxValTr">
                                                    <tr>
                                                        <td><strong>{$_L['TAX']} :</strong></td>
                                                        <td id="taxtotal" class="amount">0.00
                                                        </td>
                                                    </tr>
                                                </div>
                                            {elseif $config['tax_system'] eq 'India'}
                                                <div id="taxValTr">
                                                    <tr>
                                                        <td><strong>{$_L['TAX']} :</strong></td>
                                                        <td id="taxtotal" class="amount">0.00
                                                        </td>
                                                    </tr>
                                                </div>
                                            {/if}
                                            <tr>
                                                <td><strong>{$_L['TOTAL']} :</strong></td>
                                                <td id="total" class="amount">0.00
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                {/if}
                            </div>


                            <div id="invoice_post_cart_meta">
                                <hr>
                                <textarea class="form-control" name="notes" id="notes" rows="3"
                                          placeholder="{$_L['Invoice Terms']}...">{if $invoice}{$invoice->notes}{else}{$config['invoice_terms']}{/if}</textarea>

                                <div class="mb-3">
                                    <label for="contract_id">{$_L['Contract']}</label>
                                    <select class="form-select" name="contract_id" id="contract_id">
                                        <option value="0">{$_L['None']}</option>
                                        {foreach $contracts as $contract}
                                            <option value="{$contract->id}"
                                                    {if $invoice && $invoice->contract_id == $contract->id}selected{/if}
                                            >{$contract->title}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>


                            {if $recurring}
                                <input type="hidden" id="is_recurring" value="yes">
                            {else}
                                <input type="hidden" id="is_recurring" value="no">
                            {/if}

                        </div>
                    </div>

                </div>





            </div>






        </div>


        <input type="hidden" name="document_type" value="{{$action}}">

    </form>

    {* lan variables *}
    <input type="hidden" id="_lan_set_discount" value="{$_L['Set Discount']}">
    <input type="hidden" id="_lan_discount" value="{$_L['Discount']}">
    <input type="hidden" id="_lan_discount_type" value="{$_L['Discount Type']}">
    <input type="hidden" id="_lan_percentage" value="{$_L['Percentage']}">
    <input type="hidden" id="_lan_fixed_amount" value="{$_L['Fixed Amount']}">
    <input type="hidden" id="_lan_btn_save" value="{$_L['Save']}">
    <input type="hidden" id="_lan_no_results_found" value="{$_L['No results found']}">
{/block}


{block name="script"}


    <script>



        String.prototype.replaceAll = function(search, replacement) {
            var target = this;
            return target.replace(new RegExp(search, 'g'), replacement);
        };

        String.prototype.trunc = String.prototype.trunc ||
            function (n) {
                return (this.length > n) ? this.substr(0, n - 1) + '&hellip;' : this;
            };


        $("#contract_id").select2({

        });

        var selectedCurrency;

        function getSelectedCurrencySymbol() {
            selectedCurrency = document.getElementById('currency');
            return selectedCurrency.options[selectedCurrency.selectedIndex].getAttribute('data-symbol');
        }

        function getSelectedCurrencyDecimalMark() {
            selectedCurrency = document.getElementById('currency');
            return selectedCurrency.options[selectedCurrency.selectedIndex].getAttribute('data-decimal-mark');
        }

        function getSelectedCurrencyThousandsSeparator() {
            selectedCurrency = document.getElementById('currency');
            return selectedCurrency.options[selectedCurrency.selectedIndex].getAttribute('data-thousands-separator');
        }

        function selectedCurrencyIsSymbolFirst() {
            selectedCurrency = document.getElementById('currency');
            if(selectedCurrency.options[selectedCurrency.selectedIndex].getAttribute('data-symbol-first') === 'yes')
            {
                return true;
            }
            else
            {
                return false;
            }

        }

        function clx_number_format(number, decimals, dec_point, thousands_sep) {

            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                toFixedFix = function (n, prec) {
                    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                    var k = Math.pow(10, prec);
                    return Math.round(n * k) / k;
                },
                s = (prec ? toFixedFix(n, prec) : Math.round(n)).toString().split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        function formatCurrency(amount) {
            var prefix = '';
            var suffix = '';
            if(selectedCurrencyIsSymbolFirst())
            {
                prefix = getSelectedCurrencySymbol() + ' ';
            }
            else
            {
                suffix = ' ' + getSelectedCurrencySymbol();
            }


            return prefix + clx_number_format(amount,2,getSelectedCurrencyDecimalMark(),getSelectedCurrencyThousandsSeparator()) + suffix;

        }


        function parseAmount(amount,to_fixed_digits) {

            let digits = 2;

            if(amount === '')
            {
                return 0.00;
            }

            if(typeof to_fixed_digits !== 'undefined')
            {
                digits = to_fixed_digits;
            }


            if(getSelectedCurrencyDecimalMark() === ',')
            {
                amount = amount.split('.').join('');
                amount = amount.replace(',','.');
            }
            else{
                amount = amount.replace(',','');
            }


            return parseFloat(amount).toFixed(digits);

        }



        function calculatePercentage(amount,percentage) {
            return (amount)*(percentage/100);
        }

        var is_same_state = false;

        {if $config['tax_system'] eq 'India'}

        var $place_of_supply = $('#place_of_supply');

        function taxState() {
            if($place_of_supply.val() == '{$config['business_location']}')
            {
                is_same_state = true;
            }
            else
            {
                is_same_state = false;
            }
        }


        taxState();





        {/if}



        $(document).ready(function () {

            $('[data-bs-toggle="tooltip"]').tooltip();




            var c_qty;
            var c_price;
            var c_taxed;
            var c_discount;

            var lineTotal;
            var lineDiscount;

            var tax_val;

            var $discount_amount_total = $("#discount_amount_total");

            var $discount_amount = $("#discount_amount");
            var $discount_type = $("#discount_type");


            function spEditor(selector) {

                $(selector).redactor({
                    minHeight: 30,
                    buttons: ['html', '|', 'formatting', '|', 'bold', 'italic', 'link', 'unorderedlist', 'orderedlist']
                });

            }


            function spMultiSelect(selector) {
                /*
                $(selector).multiselect(
                    {
                        allSelectedText: false,
                        nonSelectedText: 'None'
                    }
                );
                */


            }

            spMultiSelect('.taxed');


            var $total = $("#total");
            var $taxtotal = $("#taxtotal");
            var $sub_total = $("#sub_total");
            var $pos_total_items = $("#pos_total_items");
            var $pos_coupon_total = $("#pos_coupon_total");
            var $pos_shipping_total = $("#pos_shipping_total");
            var $pos_payment_method = $("#pos_payment_method");



            var $invoice_items = $('#invoice_items');

            var invTotal = 0;

            var totalTaxVal = 0;


            var lineTotalWithoutTax;

            var totalLineTotalWithoutTax = 0;

            var discount_type = 'p';

            var totalDiscount;

            function calculateTotal() {

                discount_type = document.querySelector('.discountType:checked').value;

                invTotal = 0;

                totalTaxVal = 0;

                tax_val = 0;

                lineTotalWithoutTax = 0;

                totalLineTotalWithoutTax = 0;

                totalDiscount = 0;

                var c_taxed_split;



                {if $config['tax_system'] == 'India'}


                $.each($('.qty'), function (index, value) {

                    c_qty = this.value;

                    c_qty = parseAmount(c_qty);

                    c_price = $(this).closest('tr').find('.item_price').val();


                    {if isset($config['decimal_places_products_and_services'])}
                    c_price = parseAmount(c_price,{$config['decimal_places_products_and_services']});
                    {else}
                    c_price = parseAmount(c_price);
                    {/if}



                    c_discount = $(this).closest('tr').find('.item_discount').val();
                    c_discount = parseAmount(c_discount);

                    if (c_qty === '' || c_price === '') {
                        return;
                    }




                    c_taxed = $(this).closest('tr').find('.taxed').val();

                    c_taxed_split = (c_taxed/2).toFixed(2);

                    lineTotal = c_price * c_qty;
                    lineTotal = parseFloat(lineTotal);

                    lineTotalWithoutTax = lineTotal;


                    if(discount_type == 'p')
                    {
                        lineDiscount = calculatePercentage(lineTotal,c_discount);
                    }
                    else {
                        lineDiscount = c_discount;
                    }

                    lineTotal = (lineTotal-lineDiscount);



                    if (c_taxed === '' || c_taxed === null) {

                        tax_val = 0;

                    }
                    else {
                        c_taxed = parseFloat(c_taxed).toFixed(3);

                        tax_val = (lineTotal * c_taxed) / 100;


                        lineTotal = lineTotal + tax_val;
                    }

                    if(is_same_state)
                    {
                        $(this).closest('tr').find('.cgst').val(tax_val/2);
                        $(this).closest('tr').find('.sgst').val(tax_val/2);
                        $(this).closest('tr').find('.igst').val(0);
                    }
                    else
                    {
                        $(this).closest('tr').find('.cgst').val(0);
                        $(this).closest('tr').find('.sgst').val(0);
                        $(this).closest('tr').find('.igst').val(tax_val/2);
                    }



                    //  console.log(c_taxed);


                    $(this).closest('tr').find('.lvat').val(tax_val.toFixed(2));
                    $(this).closest('tr').find('.lvtotal').val(lineTotal.toFixed(2));




                    totalTaxVal += tax_val;

                    totalLineTotalWithoutTax += lineTotalWithoutTax;

                    totalDiscount += lineDiscount;


                });






                {else}




                $.each($('.qty'), function (index, value) {
//                    console.log(index);
//                    console.log(this.value);


                    c_qty = this.value;

                    c_qty = parseAmount(c_qty);

                    c_price = $(this).closest('tr').find('.item_price').val();



                    {if isset($config['decimal_places_products_and_services'])}
                    c_price = parseAmount(c_price,{$config['decimal_places_products_and_services']});
                    {else}
                    c_price = parseAmount(c_price);
                    {/if}


                    c_discount = $(this).closest('tr').find('.item_discount').val();
                    c_discount = parseAmount(c_discount);

                    // console.log(c_discount);


                    if (c_qty === '' || c_price === '') {
                        return;
                    }


                    c_taxed = $(this).closest('tr').find('.taxed').val();



                    lineTotal = c_price * c_qty;

                    lineTotal = parseFloat(lineTotal);



                    lineTotalWithoutTax = lineTotal;

                    if(discount_type == 'p')
                    {
                        lineDiscount = calculatePercentage(lineTotal,c_discount);
                    }
                    else {
                        lineDiscount = c_discount;
                    }

                    // console.log(lineDiscount);

                    lineTotal = (lineTotal-lineDiscount);


                    if (c_taxed === '' || c_taxed === null) {

                        tax_val = 0;

                    }
                    else {
                        c_taxed = parseFloat(c_taxed).toFixed(3);

                        tax_val = (lineTotal * c_taxed) / 100;



                        //  console.log(c_taxed);
                        //  console.log(lineTotal);

                        lineTotal = lineTotal + tax_val;
                    }



                    $(this).closest('tr').find('.lvat').val(tax_val.toFixed(2));
                    $(this).closest('tr').find('.lvtotal').val(lineTotal.toFixed(2));




                    totalTaxVal += tax_val;

                    totalLineTotalWithoutTax += lineTotalWithoutTax;

                    lineDiscount = parseFloat(lineDiscount);

                    totalDiscount += lineDiscount;


                });







                {/if}


                totalDiscount = parseFloat(totalDiscount);

                invTotal = totalLineTotalWithoutTax - totalDiscount + totalTaxVal;

                var totalItems = 0;
                $.each($('.qty'), function () {
                    var qtyValue = parseFloat(parseAmount((this.value || '').toString()));
                    if (!isNaN(qtyValue)) {
                        totalItems += qtyValue;
                    }
                });

                $total.html(formatCurrency(invTotal.toFixed(2)));
                $taxtotal.html(formatCurrency(totalTaxVal.toFixed(2)));
                $sub_total.html(formatCurrency(totalLineTotalWithoutTax.toFixed(2)));
                $discount_amount_total.html(formatCurrency(totalDiscount.toFixed(2)));
                $pos_total_items.html(totalItems.toString().padStart(2, '0'));
                $pos_coupon_total.html(formatCurrency((0).toFixed(2)));
                $pos_shipping_total.html(formatCurrency((0).toFixed(2)));


            }

            calculateTotal();

            {if $config['tax_system'] == 'India'}

            $place_of_supply.on('change',function () {

                taxState();
                calculateTotal();

            });

            {/if}


            $('#discountTypeP').change(function () {
                calculateTotal();
            });

            $('#discountTypeF').change(function () {
                calculateTotal();
            });

            var $currency = $('#currency');
            var $fixedDiscountText = $('#fixedDiscountText');

            $currency.on('change',function () {
                $fixedDiscountText.html(getSelectedCurrencySymbol());
                calculateTotal();
            });


            var $block_items = $("#block_items");

            var _url = $("#_url").val();



            $('#notes').redactor(
                {
                    minHeight: 200, // pixels
                    plugins: ['fontcolor']
                }
            );
            $invoice_items.on('change', '.taxed', function () {
                //   $('#taxtotal').html('dd');
                // var taxrate = $('#stax').val().replace(',', '.');
                // $(this).val(taxrate);

                calculateTotal();


            });


            $invoice_items.on('change', '.qty', function () {

                calculateTotal();

            });

            $invoice_items.on('change', '.item_price', function () {

                calculateTotal();

            });

            $invoice_items.on('change', '.item_discount', function () {

                calculateTotal();

            });


            var item_remove = $('#item-remove');

            function syncInvoiceRowButtons() {
                var rows = $invoice_items.find('tbody tr');

                rows.each(function (index) {
                    var $row = $(this);
                    var $cell = $row.find('.row-action-cell');

                    if (!$cell.length) {
                        return;
                    }

                    if (index === 0) {
                        $cell.html('<button type="button" class="btn btn-primary btn-sm row-add" data-bs-toggle="tooltip" data-placement="top" title="{__('Add blank Line')}"><i class="fal fa-plus"></i></button>');
                    } else {
                        $cell.html('<button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button>');
                    }
                });
            }

            syncInvoiceRowButtons();

            function getInvoiceContactModalRoot() {
                var fancyboxInstance = $.fancybox.getInstance();

                if (
                    fancyboxInstance &&
                    fancyboxInstance.current &&
                    fancyboxInstance.current.$content &&
                    fancyboxInstance.current.$content.length
                ) {
                    return fancyboxInstance.current.$content;
                }

                return $('.fancybox-content:visible').last();
            }

            function applyInvoiceContactModalBuyerTypeRules() {
                var $root = getInvoiceContactModalRoot();

                if (!$root.length) {
                    return;
                }

                var buyerType = $.trim(String($root.find('#buyer_type').val() || '')).toLowerCase();
                var hasSelection = buyerType === 'company' || buyerType === 'individual';
                var isCompany = buyerType === 'company';
                var isIndividual = buyerType === 'individual';

                $root.find('.buyer-after-type').css('display', hasSelection ? '' : 'none');
                $root.find('.buyer-company-selector-only').css('display', isCompany ? '' : 'none');
                $root.find('.buyer-company-only').css('display', isCompany ? '' : 'none');
                $root.find('.buyer-individual-only').css('display', isIndividual ? '' : 'none');
            }


            function update_address() {
                var _url = $("#_url").val();
                var cid = $('#cid').val();
                if (cid != '') {
                    $.post(_url + 'contacts/json-single-contact/', {
                        cid: cid

                    })
                        .done(function (data) {
                            var adrs = $("#address");

                            adrs.html(data.address_full);

                            if (document.getElementById('business_number')) {
                                $('#business_number').val(data.business_number);
                            }

                        });
                }

            }

            update_address();

            $('#cid').select2({

                language: {
                    noResults: function () {
                        return $("#_lan_no_results_found").val();
                    }
                }
            })
                .on("change", function (e) {
                    // mostly used event, fired to the original element when the value changes
                    // log("change val=" + e.val);
                    //  alert(e.val);

                    update_address();
                });


            {if $config['tax_system'] eq 'India'}

            var $place_to_supply = $("#place_of_supply");

            $place_to_supply.select2({

                language: {
                    noResults: function () {
                        return $("#_lan_no_results_found").val();
                    }
                }
            })
                .on("change", function (e) {

                });
            {/if}


            var $itemLoadTargetRow = null;
            var $itemLoadPickedModalRow = null;

            $invoice_items.on('click', '.row-item-search', function () {
                $itemLoadTargetRow = $(this).closest('tr');
                $('#item-add').trigger('click');
            });

            $invoice_items.on('click', '.row-add', function () {
                $('#blank-add').trigger('click');
            });

            $invoice_items.on('click', '.row-remove', function () {
                $(this).closest('tr').remove();
                syncInvoiceRowButtons();
                calculateTotal();
            });

            // Add a new line quickly when Ctrl+Enter is pressed inside invoice row fields.
            $invoice_items.on('keydown', 'input, select, textarea', function (e) {
                var isEnter = e.key === 'Enter' || e.keyCode === 13;
                if (!(e.ctrlKey || e.metaKey) || !isEnter) {
                    return;
                }

                e.preventDefault();
                $('#blank-add').trigger('click');

                var $newRowItemInput = $invoice_items.find('tbody tr:last .item_name');
                if ($newRowItemInput.length) {
                    $newRowItemInput.trigger('focus');
                }
            });

            var $modal = $('#cloudonex_body');


            $('#item-add').on('click', function () {

                $itemLoadPickedModalRow = null;





                $.fancybox.open({
                    src  : base_url + 'ps/modal-list/',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#modal_items_table').dataTable(
                                {
                                    responsive: true,
                                    "language": {
                                        "emptyTable": "{$_L['No items to display']}",
                                        "info":      "{$_L['Showing _START_ to _END_ of _TOTAL_ entries']}",
                                        "infoEmpty":      "{$_L['Showing 0 to 0 of 0 entries']}",
                                        buttons: {
                                            pageLength: '{$_L['Show all']}'
                                        },
                                        searchPlaceholder: "{__('Search')}"
                                    },
                                });
                        },
                        touch: false,
                        autoFocus: false,
                    }
                });




            });

            // Allow selecting an item by clicking modal row directly.
            $(document).on('click', '#items_table tbody tr, #modal_items_table tbody tr', function (e) {
                var $row = $(this);
                var $checkbox = $row.find('input.si:checkbox');

                if ($(e.target).is('input:checkbox')) {
                    $row.toggleClass('table-primary', $(e.target).is(':checked'));
                } else {
                    var shouldCheck = !$checkbox.is(':checked');
                    $checkbox.prop('checked', shouldCheck);
                    $row.toggleClass('table-primary', shouldCheck);
                }

                if ($checkbox.is(':checked')) {
                    $itemLoadPickedModalRow = $row;
                }
            });

            /*
             / @since v 2.0
             */

            $('#contact_add').on('click', function (e) {
                e.preventDefault();

                $.fancybox.open({
                    src  : _url + 'contacts/modal_add/',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            var $content = current && current.$content ? current.$content : getInvoiceContactModalRoot();
                            $content.find('#country').select2({
                                dropdownParent: $content
                            });
                            applyInvoiceContactModalBuyerTypeRules();
                        }
                    }
                });
            $(document).on('change', '#buyer_type, #modal_company_id', function () {
                var $root = getInvoiceContactModalRoot();
                $root.find('.alert.alert-danger').remove();
                applyInvoiceContactModalBuyerTypeRules();
            });

            $(document).on('submit', '#rform', function (e) {
                e.preventDefault();
                $(this).find('.contact_submit').trigger('click');
            });



            });

            var rowNum = 0;

            let with_staff_selection = '';

            {if !empty($config['invoicing_allow_staff_selection_for_each_item'])}

            with_staff_selection = '<div class="mb-3"><select name="staff_id[]" class="form-select"><option value="0">{{__('Select Staff')}}</option>{foreach $staffs as $employee}<option value="{$employee->id}">{$employee->fullname}</option>{/foreach}</select></div>';

            {/if}

            $('#blank-add').on('click', function () {
                rowNum++;



                {if $config['tax_system'] == 'India'}



                $invoice_items.find('tbody')
                    .append(
                        '<tr>  <td> ' + with_staff_selection + '<input type="text" class="form-control item_name" name="desc[]" value=""> <input type="hidden" name="item_code[]" value=""></td> <td><input type="text" class="form-control tax_code" value="" name="tax_code[]"></td>'  +
                        ' <td><input type="text" class="form-control qty" value="" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value=""></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td>  <td> <select class="form-select taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td> <td>\n' +
                        '                                            <input type="text" class="form-control cgst" disabled name="cgst[]" value="">\n' +
                        '                                        </td>\n' +
                        '                                        <td>\n' +
                        '                                            <input type="text" class="form-control sgst" disabled name="sgst[]" value="">\n' +
                        '                                        </td>\n' +
                        '                                        <td>\n' +
                        '                                            <input type="text" class="form-control igst" disabled name="igst[]" value="">\n' +
                        '                                        </td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td>  </tr>'
                    );

                {else}

                $invoice_items.find('tbody')
                    .append(
                        '<tr><td class="text-center align-middle"><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button></td><td>' + with_staff_selection + '<input type="text" class="form-control item_name" name="desc[]" value=""> <input type="hidden" name="item_code[]" value=""> </td> <td><input type="text" class="form-control qty" value="" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value=""></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td>  <td> <select class="form-select taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td><td><input type="text" class="form-control lvat" readonly="" value=""></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td><td class="text-center align-middle row-action-cell"><button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button></td></tr>'
                    );

                {/if}

                spMultiSelect('#t_' + rowNum);

                syncInvoiceRowButtons();

                //   calculateTotal();


            });
            $modal.on('click', '.update', function () {
                var tableControl = document.getElementById('items_table');
                if (!tableControl) {
                    tableControl = document.getElementById('modal_items_table');
                }

                if (!tableControl) {
                    return;
                }

                var $selectedItems = $('input:checkbox:checked', tableControl);
                var $selectedRows = $selectedItems.closest('tr');

                $.fancybox.close();

                if (!$selectedRows.length && $itemLoadPickedModalRow && $itemLoadPickedModalRow.length) {
                    $selectedRows = $itemLoadPickedModalRow;
                }

                if (!$selectedRows.length) {
                    return;
                }

                // If modal was opened via row search, fill the same row instead of appending.
                if ($itemLoadTargetRow && $itemLoadTargetRow.length && $.contains(document, $itemLoadTargetRow.get(0))) {
                    var $pickedRow = $selectedRows.first();
                    var pickedCode = $.trim($pickedRow.find('td:eq(1)').text());
                    var pickedName = $.trim($pickedRow.find('td:eq(2)').text());
                    var pickedPrice = $.trim($pickedRow.find('td:eq(3)').text());
                    var pickedTaxCode = $.trim($pickedRow.find('td:eq(4)').text());

                    $itemLoadTargetRow.find('.item_name').val(pickedName);
                    $itemLoadTargetRow.find('input[name="item_code[]"]').val(pickedCode);
                    $itemLoadTargetRow.find('.item_price').val(pickedPrice);
                    if ($itemLoadTargetRow.find('.tax_code').length) {
                        $itemLoadTargetRow.find('.tax_code').val(pickedTaxCode);
                    }

                    if (!$itemLoadTargetRow.find('.qty').val()) {
                        $itemLoadTargetRow.find('.qty').val('1');
                    }

                    calculateTotal();

                    // If multiple items are selected, append the remaining ones as new rows.
                    $selectedRows = $selectedRows.slice(1);
                    if (!$selectedRows.length) {
                        $itemLoadTargetRow = null;
                        $itemLoadPickedModalRow = null;
                        return;
                    }
                }


                $selectedRows.each(function () {
                    rowNum++;
                    var item_code = $(this).find('td:eq(1)').text();
                    var item_name = $(this).find('td:eq(2)').text();

                    var item_price = $(this).find('td:eq(3)').text();
                    let tax_code = $(this).find('td:eq(4)').text();

                    var normalizedItemCode = $.trim((item_code || '').toString());
                    var normalizedItemName = $.trim((item_name || '').toString());
                    var mergedIntoExistingRow = false;

                    $invoice_items.find('tbody tr').each(function () {
                        var $row = $(this);
                        var existingCode = $.trim(($row.find('input[name="item_code[]"]').first().val() || '').toString());
                        var existingName = $.trim(($row.find('.item_name').first().val() || '').toString());

                        if (
                            (normalizedItemCode !== '' && existingCode === normalizedItemCode) ||
                            (normalizedItemCode === '' && normalizedItemName !== '' && existingName === normalizedItemName)
                        ) {
                            var $qtyInput = $row.find('.qty').first();
                            var currentQty = parseFloat(parseAmount(($qtyInput.val() || '').toString()));

                            if (isNaN(currentQty)) {
                                currentQty = 0;
                            }

                            $qtyInput.val((currentQty + 1).toString());
                            mergedIntoExistingRow = true;
                            return false;
                        }
                    });

                    if (mergedIntoExistingRow) {
                        calculateTotal();
                        return;
                    }

                    {if $config['tax_system'] == 'India'}

                    $invoice_items.find('tbody')
                        .append(
                            '<tr>  <td>' + with_staff_selection + '<input type="text" class="form-control item_name" name="desc[]" value="' + item_name + '"> <input type="hidden" name="item_code[]" value="' + item_code + '"> </td> <td><input type="text" class="form-control tax_code" value="' + tax_code + '" name="tax_code[]"></td>'  +
                            ' <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + item_price + '"></td> <td colspan="2"><input type="text" class="form-control item_discount" value="0.00" name="discount[]"></td>  <td> <select class="form-select taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td> <td>\n' +
                            '                                            <input type="text" class="form-control cgst" disabled name="cgst[]" value="">\n' +
                            '                                        </td>\n' +
                            '                                        <td>\n' +
                            '                                            <input type="text" class="form-control sgst" disabled name="sgst[]" value="">\n' +
                            '                                        </td>\n' +
                            '                                        <td>\n' +
                            '                                            <input type="text" class="form-control igst" disabled name="igst[]" value="">\n' +
                            '                                        </td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td>  </tr>'
                        );

                    {else}

                    $invoice_items.find('tbody')
                        .append(
                            '<tr><td class="text-center align-middle"><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button></td><td>' + with_staff_selection + '<input type="text" class="form-control item_name" name="desc[]" value="' + item_name + '"> <input type="hidden" name="item_code[]" value="' + item_code + '"></td> <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + item_price + '"></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td>  <td> <select class="form-select taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td><td><input type="text" class="form-control lvat" readonly="" value=""></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td><td class="text-center align-middle row-action-cell"><button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button></td></tr>'
                        );

                    {/if}

                    spMultiSelect('#t_' + rowNum);

                    syncInvoiceRowButtons();

                    calculateTotal();

                });


                $modal.modal('hide');
                $itemLoadTargetRow = null;
                $itemLoadPickedModalRow = null;

            });


            $modal.on('click', '.contact_submit', function (e) {
                e.preventDefault();
                var $content = getInvoiceContactModalRoot();

                if (!$content.length) {
                    return;
                }

                $content.find('.alert.alert-danger').remove();

                var buyerType = $.trim(String($content.find('#buyer_type').val() || '')).toLowerCase();
                var selectedCompanyId = $.trim(String($content.find('#modal_company_id').val() || ''));
                var isCompany = buyerType === 'company';
                function normalizeDigits(value) {
                    return String(value || '')
                        .replace(/[\u0660-\u0669]/g, function (d) { return String(d.charCodeAt(0) - 0x0660); })
                        .replace(/[\u06F0-\u06F9]/g, function (d) { return String(d.charCodeAt(0) - 0x06F0); });
                }

                var vatNumber = normalizeDigits($.trim(String($content.find('#vat_number').val() || ''))).replace(/\D+/g, '');
                var crnNumber = normalizeDigits($.trim(String($content.find('#crn_number').val() || ''))).replace(/\D+/g, '');
                var idIqama = normalizeDigits($.trim(String($content.find('#id_iqama').val() || ''))).replace(/\D+/g, '');
                var accountName = $.trim(String($content.find('#account').val() || ''));
                var phone = $.trim(String($content.find('#phone').val() || ''));
                var errors = [];

                if (buyerType !== 'company' && buyerType !== 'individual') {
                    errors.push('Buyer Type is required');
                } else if (buyerType === 'individual') {
                    if (!accountName) {
                        errors.push('Full Name is required');
                    }
                    if (!phone) {
                        errors.push('Phone is required');
                    }
                    if (idIqama && !/^\d{10}$/.test(idIqama)) {
                        errors.push('ID / Iqama must be exactly 10 digits');
                    }
                } else {
                    if (!selectedCompanyId) {
                        errors.push('Registered Company is required');
                    }
                    if (!$.trim(String($content.find('#company').val() || ''))) {
                        errors.push('Company Name is required');
                    }
                    if (!phone) {
                        errors.push('Phone is required');
                    }
                    if (!$.trim(String($content.find('#m_address').val() || ''))) {
                        errors.push('Address is required');
                    }
                    if (!$.trim(String($content.find('#city').val() || ''))) {
                        errors.push('City is required');
                    }
                    if (!$.trim(String($content.find('#state').val() || ''))) {
                        errors.push('State/Region is required');
                    }
                    if (!$.trim(String($content.find('#zip').val() || ''))) {
                        errors.push('ZIP/Postal Code is required');
                    }
                    if (!$.trim(String($content.find('#country').val() || ''))) {
                        errors.push('Country is required');
                    }
                    if (!$.trim(String($content.find('#building_number').val() || ''))) {
                        errors.push('Building Number is required');
                    }
                    if (!/^3\d{13}3$/.test(vatNumber)) {
                        errors.push('VAT Number must be 15 digits and start/end with 3');
                    }
                    if (!/^\d{10}$/.test(crnNumber)) {
                        errors.push('Unified No. (700#) must be 10 digits');
                    }
                }

                if (errors.length > 0) {
                    $content.prepend('<div class="alert alert-danger fade in">' + errors.join('<br>') +
                        '<button type="button" class="close btn btn-danger" data-dismiss="alert">&times;</button>' +
                        '</div>');
                    return;
                }

                var _url = $("#_url").val();
                $.post(_url + 'contacts/add-post/', {

                    buyer_type: buyerType,
                    cid: selectedCompanyId,
                    account: $content.find('#account').val(),
                    company: $content.find('#company').val(),
                    company_url: $content.find('#company_url').val(),
                    logo_url: $content.find('#logo_url').val(),
                    address: $content.find('#m_address').val(),
                    city: $content.find('#city').val(),
                    state: $content.find('#state').val(),
                    zip: $content.find('#zip').val(),
                    country: $content.find('#country').val(),
                    phone: $content.find('#phone').val(),
                    email: $content.find('#email').val(),
                    vat_number: vatNumber,
                    crn_number: crnNumber,
                    building_number: $content.find('#building_number').val(),
                    id_iqama: idIqama

                })
                    .done(function (data) {

                        var _url = $("#_url").val();
                        if ($.isNumeric(data)) {

                            // location.reload();
                            var is_recurring = $('#is_recurring').val();
                            if (is_recurring == 'yes') {
                                window.location = _url + 'invoices/add/recurring/' + data + '/';
                            }
                            else {
                                window.location = _url + 'invoices/add/1/' + data + '/';
                            }

                        }
                        else {


                            $content.prepend('<div class="alert alert-danger fade in">' + data +
                                    '<button type="button" class="close btn btn-danger" data-dismiss="alert">&times;</button>' +
                                    '</div>');
                            //  $("#cid").select2('data', { id: newID, text: newText });
                        }
                    });


            });


            // $("#add_discount").click(function (e) {
            //     e.preventDefault();
            //     var s_discount_amount = $('#discount_amount');
            //     var c_discount = s_discount_amount.val();
            //     var c_discount_type = $('#discount_type').val();
            //     var p_checked = "";
            //     var f_checked = "";
            //     if (c_discount_type == "p") {
            //         p_checked = 'checked="checked"';
            //     } else {
            //         f_checked = 'checked="checked"';
            //     }
            //     bootbox.dialog({
            //             title: $("#_lan_set_discount").val(),
            //             message: '<div class="row">  ' +
            //                 '<div class="col-md-12"> ' +
            //                 '<form class="form-horizontal" action="javascript:void(0);"> ' +
            //                 '<div class="mb-3"> ' +
            //                 '<label class="col-md-4 control-label" for="set_discount">' + $("#_lan_discount").val() + '</label> ' +
            //                 '<div class="col-md-4"> ' +
            //                 '<input id="set_discount" name="set_discount" type="text" class="form-control input-md" value="' + c_discount + '"> ' +
            //                 '</div> ' +
            //                 '</div> ' +
            //                 '<div class="mb-3"> ' +
            //                 '<label class="col-md-4 control-label" for="set_discount_type">' + $("#_lan_discount_type").val() + '</label> ' +
            //                 '<div class="col-md-4"> <div class="radio"> <label for="set_discount_type-0"> ' +
            //                 '<input type="radio" name="set_discount_type" id="set_discount_type-0" value="p" ' + p_checked + '> ' +
            //                 '' + $("#_lan_percentage").val() + ' (%) </label> ' +
            //                 '</div><div class="radio"> <label for="set_discount_type-1"> ' +
            //                 '<input type="radio" name="set_discount_type" id="set_discount_type-1" value="f" ' + f_checked + '> ' + $("#_lan_fixed_amount").val() + ' </label> ' +
            //                 '</div> ' +
            //                 '</div> </div>' +
            //                 '</form> </div>  </div>',
            //             buttons: {
            //                 success: {
            //                     label: $("#_lan_btn_save").val(),
            //                     className: "btn-success",
            //                     callback: function () {
            //                         var discount_amount = $('#set_discount').val();
            //                         var discount_type = $("input[name='set_discount_type']:checked").val();
            //                         $('#discount_amount').val(discount_amount);
            //                         $('#discount_type').val(discount_type);
            //                         calculateTotal();
            //                         //updateTax();
            //                         //updateTotal();
            //                     }
            //                 }
            //             }
            //         }
            //     );
            // });


            $(".progress").hide();
            $("#emsg").hide();
            $("#submit").click(function (e) {
                e.preventDefault();
                $('#ibox_form').block({ message: null });
                var _url = $("#_url").val();
                $.post(_url + 'invoices/add-post/', $('#invform').serialize(), function (data) {

                    var _url = $("#_url").val();
                    if ($.isNumeric(data)) {

                        window.location = _url + 'invoices/edit/' + data + '/';
                    }
                    else {
                        $('#ibox_form').unblock();
                        var body = $("html, body");
                        body.animate({ scrollTop: 0 }, '1000', 'swing');
                        $("#emsgbody").html(data);
                        $("#emsg").show("slow");
                    }
                });
            });


            $("#save_n_close").click(function (e) {
                e.preventDefault();
                $('#ibox_form').block({ message: null });
                var _url = $("#_url").val();
                $.post(_url + 'invoices/add-post/', $('#invform').serialize(), function (data) {

                    var _url = $("#_url").val();
                    if ($.isNumeric(data)) {

                        window.location = _url + 'invoices/view/' + data + '/';
                    }
                    else {
                        $('#ibox_form').unblock();
                        var body = $("html, body");
                        body.animate({ scrollTop: 0 }, '1000', 'swing');
                        $("#emsgbody").html(data);
                        $("#emsg").show("slow");
                    }
                });
            });


            function initPosActionButtonsUi() {
                var isPosScreen = $('#ib_search_input').length > 0;

                if (!isPosScreen) {
                    $('#status_wrap').show();
                    $('#pos_actions_wrap').hide();
                    $('#invoice_items').removeClass('pos-cart-table-simple');
                    return;
                }

                $('#status_wrap').hide();
                $('#pos_actions_wrap').show();
                $('.panel-toolbar').addClass('d-none');
                $('.panel-hdr h2').addClass('pos-compact-hidden');
                $('#invoice_items').addClass('pos-cart-table-simple');
                $('#invoice_items thead tr:first-child th').eq(6).text('VAT');
                $('#invoice_items thead tr:first-child th').eq(7).text('Total (incl. VAT)');

                if ($('#pos_controls_mount').length && $('#invoice_primary_controls').length) {     
                    $('#invoice_primary_controls').appendTo('#pos_controls_mount');
                }

                if ($('#pos_summary_mount').length && $('#invoice_totals_wrap').length) {
                    $('#invoice_totals_wrap').appendTo('#pos_summary_mount');
                }

                $('#invoice_meta_extended').addClass('pos-compact-hidden');
                $('#invoice_post_cart_meta').remove();

                // POS should start with an empty cart; remove the auto-added blank line.
                $('#invoice_items tbody tr').filter(function () {
                    var codeValue = $.trim(($(this).find('input[name="item_code[]"]').first().val() || '').toString());
                    var nameValue = $.trim(($(this).find('.item_name').first().val() || '').toString());
                    return codeValue === '' && nameValue === '';
                }).remove();
                syncInvoiceRowButtons();

                $('#pos_save_close_btn').off('click').on('click', function () {
                    $('#save_n_close').trigger('click');
                });

                $('#pos_save_btn').off('click').on('click', function () {
                    $('#status').val('Published').trigger('change');
                    $('#submit').trigger('click');
                });

                $('#pos_save_draft_btn').off('click').on('click', function () {
                    $('#status').val('Draft').trigger('change');
                    $('#submit').trigger('click');
                });

                $('.pos-payment-btn').off('click').on('click', function () {
                    var method = $(this).data('payment-method') || '';
                    $pos_payment_method.val(method);
                    $('#pmethod').val(method);
                    $('#status').val('Published').trigger('change');
                    $('#submit').trigger('click');
                });
            }

            initPosActionButtonsUi();


            {if $pos eq 'pos'}

            function loadItems() {

                $block_items.html(block_msg);

                var item_name;

                $.getJSON(base_url + "items/all/", function (data) {
                    var items = "";
                    var b_p;
                    $.each(data, function (key, val) {

                        item_name = val.name;

                        item_name = item_name.trunc(12);


                        var image;

                        if(val.image == '') {
                            image = '{$app_url}ui/lib/img/item_placeholder.png';
                        }
                        else{
                            image = '{$app_url}storage/items/thumb'+ val.image;
                        }


                        b_p = '<div class="col-lg-2 col-md-2 col-sm-3 col-xs-4"><div class="pos_item text-center" id="pos_item_'+ val.id +'" data-pos-item-name="'+val.name+'" data-pos-item-price="'+val.sales_price+'" data-pos-tax-code="'+val.tax_code+'" data-id="'+ val.id +'" data-pos-item-number="'+ val.item_number +'"><img src="'+ image +'" alt="'+ item_name +'" class="img-circle"><hr>'+ item_name +' <br>'+ val.sales_price +'  <hr></div> </div>';

                        items = items + b_p;
                    });

                    $block_items.html(items);

                    $('#ib_search_input').hideseek({
                        highlight: true
                    });

                });

            }

            loadItems();

            var pos_item_name, pos_item_price, pos_item_id, pos_item_number, pos_tax_code;

            $block_items.on('click', '.pos_item', function () {

                pos_item_number = $(this).data('pos-item-number');
                pos_item_name = $(this).data('pos-item-name');
                pos_item_price = $(this).data('pos-item-price');
                pos_item_id = $(this).data('id');
                pos_tax_code = ($(this).data('pos-tax-code')) ?? '';

                var $existingPosRow = null;
                $invoice_items.find('tbody tr').each(function () {
                    var $row = $(this);
                    var existingCode = $.trim(($row.find('input[name="item_code[]"]').first().val() || '').toString());
                    var existingName = $.trim(($row.find('.item_name').first().val() || '').toString());

                    if ((pos_item_number && existingCode === pos_item_number.toString()) || (!pos_item_number && existingName === pos_item_name.toString())) {
                        $existingPosRow = $row;
                        return false;
                    }
                });

                if ($existingPosRow && $existingPosRow.length) {
                    var $qtyInput = $existingPosRow.find('.qty').first();
                    var currentQty = parseFloat(parseAmount(($qtyInput.val() || '').toString()));
                    if (isNaN(currentQty)) {
                        currentQty = 0;
                    }
                    $qtyInput.val((currentQty + 1).toString());
                    calculateTotal();
                    return;
                }

                rowNum++;


                {if $config['tax_system'] == 'India'}

                $invoice_items.find('tbody')
                    .prepend(
                        '<tr>  <td>' + with_staff_selection + '<input type="text" class="form-control item_name" name="desc[]" value="' + pos_item_name + '"> <input type="hidden" name="item_code[]" value="' + pos_item_number + '"> </td> <td><input type="text" class="form-control tax_code" value="' + pos_tax_code + '" name="tax_code[]"></td>'  +
                        ' <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + pos_item_price + '"></td> <td colspan="2"><input type="text" class="form-control item_discount" value="0.00" name="discount[]"></td>  <td> <select class="form-select taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td> <td>\n' +
                        '                                            <input type="text" class="form-control cgst" disabled name="cgst[]" value="">\n' +
                        '                                        </td>\n' +
                        '                                        <td>\n' +
                        '                                            <input type="text" class="form-control sgst" disabled name="sgst[]" value="">\n' +
                        '                                        </td>\n' +
                        '                                        <td>\n' +
                        '                                            <input type="text" class="form-control igst" disabled name="igst[]" value="">\n' +
                        '                                        </td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td>  </tr>'
                    );

                {else}


                $invoice_items.find('tbody')
                    .prepend(
                        '<tr><td class="text-center align-middle"><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button></td><td>' + with_staff_selection + '<input type="text" class="form-control item_name" name="desc[]" value="' + pos_item_name + '"> <input type="hidden" name="item_code[]" value="' + pos_item_number + '"></td> <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + pos_item_price + '"></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td>  <td> <select class="form-select taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td><td><input type="text" class="form-control lvat" readonly="" value=""></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td><td class="text-center align-middle row-action-cell"><button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button></td></tr>'
                    );

                {/if}

                spMultiSelect('#t_' + rowNum);

                syncInvoiceRowButtons();

                calculateTotal();


            });



            {/if}


            {if !empty($config['invoice_single_service'])}

            const service_id = document.getElementById('service_id');

            service_id.addEventListener('change', () => {

                let item_sl = 15000;

                let price = service_id.options[service_id.selectedIndex].getAttribute('data-price');
                let name = service_id.options[service_id.selectedIndex].text;


                $invoice_items.find('tbody')
                    .prepend(
                        '<tr>  <td>' + with_staff_selection + '<input type="text" class="form-control item_name" name="desc[]" value="' + name + '"> <input type="hidden" name="item_code[]" value=""></td> <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + price + '"></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td>  <td> <select class="form-select taxed" name="taxed[]" id="t_' + item_sl + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td><td><input type="text" class="form-control lvat" readonly="" value=""></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td>  </tr>'
                    );


                spMultiSelect('#t_' + rowNum);

                calculateTotal();

                item_sl = item_sl + 1;
            });

            {/if}

        });
    </script>
{/block}
