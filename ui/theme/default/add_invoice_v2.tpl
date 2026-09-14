{extends file="$layouts_admin"}


{block name="head"}
    <link href="{$app_url}ui/lib/mselect/multiple-select.css" rel="stylesheet">

    {if $config['edition'] eq 't_event'}
        <link href="{$app_url}ui/lib/clockpicker/bootstrap-clockpicker.min.css" rel="stylesheet">
    {/if}

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
            background: #f3f6f9;
            cursor: pointer;
        }

        .pos_item:hover {
            background: #2196f3;
            color: #ffffff;
        }

        {/if}

    </style>
{/block}


{block name="content"}
    <div class="row" id="ibox_form">


        <div class="col-md-12">
            <h3 class="ibilling-page-header">{$_L['New Invoice']}</h3>
        </div>

        <form id="invform" method="post">
            <div class="col-md-12">
                <div class="alert alert-danger" id="emsg">
                    <span id="emsgbody"></span>
                </div>
            </div>


            <div class="col-md-12">


                <div class="panel panel-default">
                    <div class="panel-body">


                        <div class="row">
                            <div class="col-md-12">

                                <div class='row'>
                                    <div class="col-sm-6">
                                        <h3>{$_L['Invoice']} # {predictNextRow('sys_invoices')}</h3>
                                    </div>
                                    <div class='col-sm-6'>
                                        <div class="text-end btn-group">
                                            <input type="hidden" id="_dec_point" name="_dec_point"
                                                   value="{$config['dec_point']}">
                                            <button class="btn-xs btn-primary" id="submit"> {$_L['Save']}</button>
                                            <button class="btn-xs btn-info"
                                                    id="save_n_close"> {$_L['Save n Close']}</button>

                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <hr>
                                    </div>
                                </div>



                                <div class='row'>

                                    <div class='col-sm-4'>
                                        <div class='mb-3'>
                                            <label for="user_title">{$_L['Customer']}</label>

                                            <select id="cid" name="cid" class="form-control">
                                                <option value="">{$_L['Select Contact']}...</option>
                                                {foreach $c as $cs}
                                                    <option value="{$cs['id']}"
                                                            {if $p_cid eq ($cs['id'])}selected="selected" {/if}>{$cs['account']} {if $cs['email'] neq ''}- {$cs['email']}{/if}</option>
                                                {/foreach}

                                            </select>
                                            <span class="help-block"><a href="#"
                                                                        id="contact_add">| {$_L['Or Add New Customer']}</a> </span>
                                            <div id="invoice_type_indicator" class="mt-1"></div>
                                        </div>
                                    </div>
                                    <div class='col-sm-4'>
                                        <div class="mb-3">
                                            <label for="status">{$_L['Status']}</label>

                                            <select id="status" name="status" class="form-control">

                                                <option value="Published">{$_L['Published']}</option>
                                                <option value="Draft">{$_L['Draft']}</option>


                                            </select>

                                        </div>
                                    </div>
                                    <div class='col-sm-4'>
                                        <div class="mb-3">
                                            <label for="currency">{$_L['Currency']}</label>

                                            <select id="currency" name="currency" class="form-control">

                                                {foreach $currencies as $key=>$value}
                                                    <option value="{$key}"
                                                            {if $config['home_currency'] eq ($key)}selected="selected" {/if} data-decimal-mark="{$value['decimal_mark']}" data-thousands-separator="{$value['thousands_separator']}" data-symbol="{$value['symbol']}" >{$key}</option>
                                                    {foreachelse}
                                                {/foreach}

                                            </select>

                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label for="invoice_title">{$_L['Title']}  <small><em>({$_L['optional']})</em></small></label>

                                            <input type="text" class="form-control" id="invoice_title" name="title">
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
                                            <label for="invoicenum">{$_L['Invoice Prefix']}</label>

                                            <input type="text" class="form-control" id="invoicenum" name="invoicenum">
                                        </div>

                                        <div class="mb-3">
                                            <label for="cn">{$_L['Invoice']} #</label>

                                            <input type="text" class="form-control" id="cn" name="cn">
                                            <span class="help-block">{$_L['invoice_number_help']}</span>
                                        </div>

                                    </div>
                                    <div class='col-sm-4'>
                                        {if $config['invoice_receipt_number'] eq '1'}
                                            <div class="mb-3">
                                                <label for="receipt_number">{$_L['Receipt Number']}</label>

                                                <input type="text" class="form-control" id="receipt_number"
                                                       name="receipt_number">
                                            </div>
                                        {else}
                                            <input type="hidden" name="receipt_number" id="receipt_number" value="">
                                        {/if}

                                        <div class="mb-3">
                                            <label for="show_quantity_as">{$_L['Show quantity as']}</label>

                                            <input type="text" class="form-control" id="show_quantity_as"
                                                   name="show_quantity_as"
                                                   value="{if $config['show_quantity_as'] eq ''}{$_L['Qty']}{else}{$config['show_quantity_as']}{/if}">

                                        </div>

                                        {if $recurring}
                                            <div class="mb-3">
                                                <label for="repeat">{$_L['Repeat Every']}</label>

                                                <select class="form-select" name="repeat" id="repeat">
                                                    <option value="daily">{$_L['Daily']}</option>
                                                    <option value="week1">{$_L['Weekly']}</option>
                                                    <option value="weeks2">{$_L['Weeks_2']}</option>
                                                    <option value="weeks3">{$_L['Weeks_3']}</option>
                                                    <option value="weeks4">{$_L['Weeks_4']}</option>
                                                    <option value="month1" selected>{$_L['Month']}</option>
                                                    <option value="months2">{$_L['Months_2']}</option>
                                                    <option value="months3">{$_L['Months_3']}</option>
                                                    <option value="months6">{$_L['Months_6']}</option>
                                                    <option value="year1">{$_L['Year']}</option>
                                                    <option value="years2">{$_L['Years_2']}</option>
                                                    <option value="years3">{$_L['Years_3']}</option>

                                                </select>
                                            </div>
                                        {else}
                                            <input type="hidden" name="repeat" id="repeat" value="0">
                                        {/if}



                                        {*<div class="mb-3">*}
                                            {**}
                                            {*<label for="add_discount"><a href="#" id="add_discount"*}
                                                                         {*class="btn btn-info btn-xs"*}
                                                                         {*style="margin-top: 5px;"><i*}
                                                            {*class="fal fa-minus-circle"></i> {$_L['Set Discount']}*}
                                                {*</a></label>*}
                                            {*<br>*}


                                            {*<input type="hidden" id="stax" name="stax" value="0.00">*}
                                            {*<input type="hidden" id="discount_amount" name="discount_amount" value="">*}
                                            {*<input type="hidden" id="discount_type" name="discount_type" value="p">*}
                                            {*<input type="hidden" id="taxed_type" name="taxed_type" value="individual">*}

                                        {*</div>*}



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


                                                        {foreach $states as $state}
                                                            <option value="{$state['name']}"
                                                            {if $config['business_location'] == $state['name']}
                                                                selected
                                                            {/if}
                                                            >{$state['name']}</option>
                                                        {/foreach}



                                                </select>

                                            </div>
                                        </div>


                                    </div>
                                {/if}


                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <label for="idate">{$_L['Invoice Date']}</label>

                                            <input type="text" class="form-control" id="idate" name="idate" datepicker
                                                   data-date-format="yyyy-mm-dd" data-auto-close="true"
                                                   value="{$idate}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <label for="duedate">{$_L['Payment Terms']}</label>

                                            <select class="form-select" name="duedate" id="duedate">
                                                <option value="due_on_receipt" selected>{$_L['Due On Receipt']}</option>
                                                <option value="days3">{$_L['days_3']}</option>
                                                <option value="days5">{$_L['days_5']}</option>
                                                <option value="days7">{$_L['days_7']}</option>
                                                <option value="days10">{$_L['days_10']}</option>
                                                <option value="days15">{$_L['days_15']}</option>
                                                <option value="days30">{$_L['days_30']}</option>
                                                <option value="days45">{$_L['days_45']}</option>
                                                <option value="days60">{$_L['days_60']}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">

                                    </div>
                                </div>






                                {$extraHtml}


                            </div>
                        </div>

                        <div>


                            {*<div class="mb-3">*}
                            {*<label for="tid">{$_L['Sales TAX']}</label>*}

                            {*<select id="tid" name="tid" class="form-control">*}
                            {*<option value="">{$_L['None']}</option>*}
                            {*{foreach $t as $ts}*}
                            {*<option value="{$ts['id']}">{$ts['name']}*}
                            {*({{number_format($ts['rate'],2,$config['dec_point'],$config['thousands_sep'])}}*}
                            {*%)*}
                            {*</option>*}
                            {*{/foreach}*}

                            {*</select>*}

                            {*</div>*}


                        </div>

                    </div>
                </div>


            </div>

            <div class="col-md-12">


                {if $pos eq 'pos'}
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="ib-search-bar" style="margin-bottom: 30px;">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="ib_search_input"
                                           placeholder="{$_L['Search']}..." autofocus data-list=".list_pos_items"></div>
                            </div>

                            <hr>

                            <div id="block_items" class="list_pos_items">


                            </div>


                        </div>
                    </div>
                {/if}


                <div class="panel panel-default">
                    <div class="panel-body">

                        <div class="table-responsive m-t">

                            {if $config['tax_system'] == 'India'}
                                <table class="table table-bordered invoice-table" id="invoice_items">


                                    <thead>


                                    <tr>

                                        <th rowspan="2">{$_L['Item Name']}</th>
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
                                            <label class="radio-inline">
                                                <input class="discountType" id="discountTypeP" type="radio" name="discount_type" value="p" checked>%
                                            </label>
                                            <label class="radio-inline">
                                                <input class="discountType" id="discountTypeF" type="radio" name="discount_type" value="f">Rs
                                            </label>
                                        </th>


                                        <th>CGST</th>
                                        <th>SGST</th>
                                        <th>IGST</th>

                                    </tr>

                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control item_name" name="desc[]" value="">
                                            <input type="hidden" name="item_code[]" value=""></td>
                                        <td><input type="text" class="form-control tax_code" value="" name="tax_code[]"></td>
                                        <td><input type="text" class="form-control qty" value="" name="qty[]"></td>
                                        <td><input type="text" class="form-control item_price" name="amount[]" value="">
                                        <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value="">
                                        </td>

                                        <td><select class="form-control taxed" name="taxed[]">
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
                                        </th>
                                        <th colspan="4"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control item_name" name="desc[]" value="">
                                            <input type="hidden" name="item_code[]" value=""></td>
                                        <td><input type="text" class="form-control qty" value="" name="qty[]"></td>
                                        <td><input type="text" class="form-control item_price" name="amount[]" value=""></td>
                                        <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value="">
                                        </td>
                                        <td><select class="form-control taxed" name="taxed[]">
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
                        <button type="button" class="d-none" id="blank-add"></button>
                        <button type="button" class="d-none" id="item-add"></button>
                        <hr>

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
                                {*<tr>*}
                                    {*<td><strong>CGST:</strong></td>*}
                                    {*<td id="indiaCGST" class="amount">0.00*}
                                    {*</td>*}
                                {*</tr>*}
                                {*<tr>*}
                                    {*<td><strong>SGST:</strong></td>*}
                                    {*<td id="indiaSGST" class="amount">0.00*}
                                    {*</td>*}
                                {*</tr>*}
                                {*<tr>*}
                                    {*<td><strong>IGST:</strong></td>*}
                                    {*<td id="indiaIGST" class="amount">0.00*}
                                    {*</td>*}
                                {*</tr>*}

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


                        <hr>
                        <textarea class="form-control" name="notes" id="notes" rows="3"
                                  placeholder="{$_L['Invoice Terms']}...">{$config['invoice_terms']}</textarea>
                        <br>
                        {if $recurring}
                            <input type="hidden" id="is_recurring" value="yes">
                        {else}
                            <input type="hidden" id="is_recurring" value="no">
                        {/if}


                    </div>
                </div>


            </div>


        </form>


    </div>
    {* lan variables *}
    <input type="hidden" id="_lan_set_discount" value="{$_L['Set Discount']}">
    <input type="hidden" id="_lan_discount" value="{$_L['Discount']}">
    <input type="hidden" id="_lan_discount_type" value="{$_L['Discount Type']}">
    <input type="hidden" id="_lan_percentage" value="{$_L['Percentage']}">
    <input type="hidden" id="_lan_fixed_amount" value="{$_L['Fixed Amount']}">
    <input type="hidden" id="_lan_btn_save" value="{$_L['Save']}">
    <input type="hidden" id="_lan_no_results_found" value="{$_L['No results found']}">

{* Bootstrap 5 Ajax modal container *}
<div class="modal fade" id="ajax-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <!-- content loaded dynamically -->
            </div>
        </div>
    </div>
</div>
{/block}


{block name="script"}

    {if $config['edition'] eq 't_event'}
        <script src="{$app_url}ui/lib/clockpicker/bootstrap-clockpicker.min.js"></script>
    {/if}

    <script src="{$app_url}ui/lib/mselect/multiple-select.js"></script>
    <script>

        var selectedCurrency = document.getElementById('currency');
        var selectedCurrencyDecimalMark;
        var selectedCurrencyThousandsSeparator;
        var selectedCurrencySymbol;


        function parseAmount(amount) {

            if(amount === '')
                {
                    return 0.00;
                }


            selectedCurrencyDecimalMark = selectedCurrency.options[selectedCurrency.selectedIndex].getAttribute('data-decimal-mark');
            selectedCurrencyThousandsSeparator = selectedCurrency.options[selectedCurrency.selectedIndex].getAttribute('data-thousands-separator');

            if(selectedCurrencyDecimalMark == ',')
                {
                    amount = amount.replace(/\./g, '').replace(',', '.');
                    amount = !isNaN(parseFloat(amount)) && isFinite(amount);
                }

                return parseFloat(amount).toFixed(2);

        }

        parseAmount(100);

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

        String.prototype.trunc = String.prototype.trunc ||
            function (n) {
                return (this.length > n) ? this.substr(0, n - 1) + '&hellip;' : this;
            };

        $(document).ready(function () {

            $('[data-bs-toggle="tooltip"]').tooltip();

            {*{if $config['mininav'] eq '0'}*}

            {*$("body").toggleClass("mini-navbar");*}
            {*SmoothlyMenu();*}

            {*{/if}*}


            {if $config['edition'] eq 't_event'}
            $('.clockpicker').clockpicker({
                donetext: 'Done'
            });
            {/if}

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
                    paragraphize: false,
                    replaceDivs: false,
                    linebreaks: true,
                    minHeight: 30 // pixels
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

            var cDiscountVal = 0;

            var $invoice_items = $('#invoice_items');

            var invTotal = 0;

            var totalTaxVal = 0;


            var lineTotalWithoutTax;

            var totalLineTotalWithoutTax = 0;

            var discount_type = 'p';



            function calculateTotal() {

                discount_type = document.querySelector('.discountType:checked').value;

                invTotal = 0;

                totalTaxVal = 0;

                tax_val = 0;

                lineTotalWithoutTax = 0;

                totalLineTotalWithoutTax = 0;

                var c_taxed_split;

                {if $config['tax_system'] == 'India'}


                $.each($('.qty'), function (index, value) {

                    c_qty = this.value;

                    c_price = $(this).closest('tr').find('.item_price').val();




                    if (c_qty === '' || c_price === '') {
                        return;
                    }


                    c_taxed = $(this).closest('tr').find('.taxed').val();

                    c_taxed_split = (c_taxed/2).toFixed(2);

                    lineTotal = c_price * c_qty;
                    lineTotal = parseFloat(lineTotal);

                    lineTotalWithoutTax = lineTotal;

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


                    invTotal += lineTotal;

                    totalTaxVal += tax_val;

                    totalLineTotalWithoutTax += lineTotalWithoutTax;


                });


                if ($discount_type.val() === 'f' && $discount_amount.val() !== '') {
                    cDiscountVal = $discount_amount.val();
                }
                else if ($discount_type.val() === 'p' && $discount_amount.val() !== '') {
                    var tCDiscountval = parseFloat($discount_amount.val());
                    cDiscountVal = (invTotal * tCDiscountval) / 100;
                }
                else {

                }

                cDiscountVal = parseFloat(cDiscountVal);

                invTotal = invTotal - cDiscountVal;

                $total.html(invTotal.toFixed(2));
                $taxtotal.html(totalTaxVal.toFixed(2));
                $sub_total.html(totalLineTotalWithoutTax.toFixed(2));
                $discount_amount_total.html(cDiscountVal.toFixed(2));

                {else}




                $.each($('.qty'), function (index, value) {
//                    console.log(index);
//                    console.log(this.value);


                    c_qty = this.value;

                    c_price = $(this).closest('tr').find('.item_price').val();
                    c_price = parseAmount(c_price);

                    c_discount = $(this).closest('tr').find('.item_discount').val();
                    c_discount = parseAmount(c_discount);

                   // console.log(c_discount);


                    if (c_qty === '' || c_price === '') {
                        return;
                    }


                    c_taxed = $(this).closest('tr').find('.taxed').val();

                    //  console.log(c_taxed);



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


                    invTotal += lineTotal;

                    totalTaxVal += tax_val;

                    totalLineTotalWithoutTax += lineTotalWithoutTax;


                });


                if ($discount_type.val() === 'f' && $discount_amount.val() !== '') {
                    cDiscountVal = $discount_amount.val();
                }
                else if ($discount_type.val() === 'p' && $discount_amount.val() !== '') {
                    var tCDiscountval = parseFloat($discount_amount.val());
                    cDiscountVal = (invTotal * tCDiscountval) / 100;
                }
                else {

                }

                cDiscountVal = parseFloat(cDiscountVal);

               // console.log(cDiscountVal);

                invTotal = invTotal - cDiscountVal;

                $total.html(invTotal.toFixed(2));
                $taxtotal.html(totalTaxVal.toFixed(2));
                $sub_total.html(totalLineTotalWithoutTax.toFixed(2));
                $discount_amount_total.html(cDiscountVal.toFixed(2));

                {/if}



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
                selectedCurrencySymbol = selectedCurrency.options[selectedCurrency.selectedIndex].getAttribute('data-symbol');
                $fixedDiscountText.html(selectedCurrencySymbol);
            });


            var $block_items = $("#block_items");

            var _url = $("#_url").val();

            $('.amount').autoNumeric('init');
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

                            var $indicator = $("#invoice_type_indicator");
                            var buyerType = $.trim(String(data.buyer_type || '')).toLowerCase();
                            if (buyerType === 'company') {
                                $indicator.html('<span class="badge bg-info">{__("Standard Tax Invoice")}</span>');
                            } else if (buyerType === 'individual') {
                                $indicator.html('<span class="badge bg-secondary">{__("Simplified Tax Invoice")}</span>');
                            } else {
                                $indicator.html('');
                            }

                        });
                } else {
                    $("#invoice_type_indicator").html('');
                }

            }

            update_address();

            $('#cid').select2({
                theme: "bootstrap",
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
                theme: "bootstrap",
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

            var $modal = $('#ajax-modal');

            var _ajaxBsModal = null;
            function _getAjaxModal() {
                if (!_ajaxBsModal) {
                    _ajaxBsModal = new bootstrap.Modal(document.getElementById('ajax-modal'), { backdrop: true, keyboard: true });
                }
                return _ajaxBsModal;
            }

            $('#item-add').on('click', function () {
                $itemLoadPickedModalRow = null;
                $.get(_url + 'ps/modal-list/', function (html) {
                    $modal.find('.modal-body').html(html);
                    _getAjaxModal().show();
                });
            });

            // Allow selecting an item by clicking the row itself in modal list.
            $modal.on('click', '#items_table tbody tr, #modal_items_table tbody tr', function (e) {
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
                $.get(_url + 'contacts/modal_add/', function (html) {
                    $modal.find('.modal-body').html(html);
                    _getAjaxModal().show();
                    $("#ajax-modal .country").select2({ theme: "bootstrap" });
                    applyModalBuyerTypeRules();
                    setTimeout(applyModalBuyerTypeRules, 50);
                });
            });

            window.applyInvoiceContactModalBuyerTypeRules = function () {
                var buyerType = $.trim(String($modal.find('#buyer_type').val() || '')).toLowerCase();
                var selectedCompanyId = $.trim(String($modal.find('#modal_company_id').val() || ''));
                var hasSelection = buyerType === 'company' || buyerType === 'individual';
                var isCompany = buyerType === 'company';
                var isIndividual = buyerType === 'individual';
                var isNewCompany = isCompany && selectedCompanyId === '__new__';

                $modal.find('.buyer-after-type').css('display', hasSelection ? '' : 'none');
                $modal.find('.buyer-company-selector-only').css('display', isCompany ? '' : 'none');
                $modal.find('.buyer-company-create-only').css('display', isNewCompany ? '' : 'none');
                $modal.find('.buyer-individual-only').css('display', isIndividual ? '' : 'none');
                $modal.find('.buyer-shared-contact-only').css('display', (isIndividual || isNewCompany) ? '' : 'none');
            };

            function applyModalBuyerTypeRules() {
                if (typeof window.applyInvoiceContactModalBuyerTypeRules === 'function') {
                    window.applyInvoiceContactModalBuyerTypeRules();
                }
            }

            $modal.on('change', '#buyer_type, #modal_company_id', function () {
                $modal.find('.alert.alert-danger').remove();
                applyModalBuyerTypeRules();
            });

            $modal.on('submit', '#rform', function (e) {
                e.preventDefault();
                $modal.find('.contact_submit').trigger('click');
            });

            var rowNum = 0;

            $('#blank-add').on('click', function () {
                rowNum++;


                {if $config['tax_system'] == 'India'}

                $invoice_items.find('tbody')
                    .append(
                        '<tr>  <td><input type="text" class="form-control item_name" name="desc[]" value=""> <input type="hidden" name="item_code[]" value=""> </td> <td><input type="text" class="form-control tax_code" value="" name="tax_code[]"></td>'  +
                        ' <td><input type="text" class="form-control qty" value="" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value=""></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td>  <td> <select class="form-control taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td> <td>\n' +
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
                        '<tr><td class="text-center align-middle"><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button></td><td><input type="text" class="form-control item_name" name="desc[]" value=""> <input type="hidden" name="item_code[]" value=""> </td> <td><input type="text" class="form-control qty" value="" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value=""></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td>  <td> <select class="form-control taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td><td><input type="text" class="form-control lvat" readonly="" value=""></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td><td class="text-center align-middle row-action-cell"><button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button></td></tr>'
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
                    _getAjaxModal().hide();
                    return;
                }

                var $selectedItems = $('input:checkbox:checked', tableControl);
                var $selectedRows = $selectedItems.closest('tr');

                if (!$selectedRows.length && $itemLoadPickedModalRow && $itemLoadPickedModalRow.length) {
                    $selectedRows = $itemLoadPickedModalRow;
                }

                if (!$selectedRows.length) {
                    _getAjaxModal().hide();
                    return;
                }

                // If modal was opened via row search, fill the same row instead of appending.
                if ($itemLoadTargetRow && $itemLoadTargetRow.length && $.contains(document, $itemLoadTargetRow.get(0))) {
                    var $pickedRow = $selectedRows.first();
                    var pickedCode = $.trim($pickedRow.find('td:eq(1)').text());
                    var pickedName = $.trim($pickedRow.find('td:eq(2)').text());
                    var pickedPrice = $.trim($pickedRow.find('td:eq(3)').text());

                    $itemLoadTargetRow.find('.item_name').val(pickedName);
                    $itemLoadTargetRow.find('input[name="item_code[]"]').val(pickedCode);
                    $itemLoadTargetRow.find('.item_price').val(pickedPrice);

                    if (!$itemLoadTargetRow.find('.qty').val()) {
                        $itemLoadTargetRow.find('.qty').val('1');
                    }

                    calculateTotal();

                    // If multiple items are selected, append the remaining ones as new rows.
                    $selectedRows = $selectedRows.slice(1);
                    if (!$selectedRows.length) {
                        _getAjaxModal().hide();
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
                            '<tr>  <td><input type="text" class="form-control item_name" name="desc[]" value="' + item_name + '"> <input type="hidden" name="item_code[]" value="' + item_code + '"> </td> <td><input type="text" class="form-control tax_code" value="" name="tax_code[]"></td>'  +
                            ' <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + item_price + '"></td> <td><input type="text" class="form-control item_discount" value="0.00" name="discount[]"></td>  <td> <select class="form-control taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td> <td>\n' +
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
                            '<tr><td class="text-center align-middle"><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button></td><td><input type="text" class="form-control item_name" name="desc[]" value="' + item_name + '"> <input type="hidden" name="item_code[]" value="' + item_code + '"></td> <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + item_price + '"></td> <td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td> <td> <select class="form-control taxed" name="taxed[]" id="t_' + rowNum + '"> {foreach $t as $ts}  <option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option> {/foreach} </select></td><td><input type="text" class="form-control lvat" readonly="" value=""></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td><td class="text-center align-middle row-action-cell"><button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button></td></tr>'
                        );

                    {/if}

                    spMultiSelect('#t_' + rowNum);

                    syncInvoiceRowButtons();

                    calculateTotal();

                });

                //  console.debug(obj); // Write it to the console
                //  calculateTotal();


                _getAjaxModal().hide();
                $itemLoadTargetRow = null;
                $itemLoadPickedModalRow = null;

            });


            $modal.on('click', '.contact_submit', function (e) {
                e.preventDefault();
                var sbutton = $(this);
                var modalBody = $modal.find('.modal-body');
                modalBody.find('.alert.alert-danger').remove();

                var _url = $("#_url").val();
                var buyerType = $.trim(String(modalBody.find('#buyer_type').val() || '')).toLowerCase();
                var selectedCompanyId = $.trim(String(modalBody.find('#modal_company_id').val() || ''));
                var isNewCompany = buyerType === 'company' && selectedCompanyId === '__new__';

                var accountName = $.trim(String(modalBody.find('#account').val() || ''));
                var companyName = $.trim(String(modalBody.find('#company').val() || ''));
                var address = $.trim(String(modalBody.find('#m_address').val() || ''));
                var city = $.trim(String(modalBody.find('#city').val() || ''));
                var state = $.trim(String(modalBody.find('#state').val() || ''));
                var zip = $.trim(String(modalBody.find('#zip').val() || ''));
                var country = $.trim(String(modalBody.find('#country').val() || ''));
                var phone = $.trim(String(modalBody.find('#phone').val() || ''));
                var email = $.trim(String(modalBody.find('#email').val() || ''));
                var companyUrl = $.trim(String(modalBody.find('#company_url').val() || ''));
                var logoUrl = $.trim(String(modalBody.find('#logo_url').val() || ''));
                function normalizeDigits(value) {
                    return String(value || '')
                        .replace(/[\u0660-\u0669]/g, function (d) { return String(d.charCodeAt(0) - 0x0660); })
                        .replace(/[\u06F0-\u06F9]/g, function (d) { return String(d.charCodeAt(0) - 0x06F0); });
                }

                var buildingNumber = normalizeDigits($.trim(String(modalBody.find('#building_number').val() || ''))).replace(/\D+/g, '');
                var vatNumber = normalizeDigits($.trim(String(modalBody.find('#vat_number').val() || ''))).replace(/\D+/g, '');
                var crnNumber = normalizeDigits($.trim(String(modalBody.find('#crn_number').val() || ''))).replace(/\D+/g, '');
                var idIqama = $.trim(String(modalBody.find('#id_iqama').val() || ''));

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

                    if (isNewCompany) {
                        if (!companyName) {
                            errors.push('Company Name is required');
                        }
                        if (!phone) {
                            errors.push('Phone is required');
                        }
                        if (!address) {
                            errors.push('Address is required');
                        }
                        if (!city) {
                            errors.push('City is required');
                        }
                        if (!state) {
                            errors.push('State/Region is required');
                        }
                        if (!zip) {
                            errors.push('ZIP/Postal Code is required');
                        }
                        if (!country) {
                            errors.push('Country is required');
                        }
                        if (!buildingNumber) {
                            errors.push('Building Number is required');
                        }
                    }
                }

                if (errors.length > 0) {
                    modalBody.prepend('<div class="alert alert-danger fade in">' + errors.join('<br>') + '<button type="button" class="close btn btn-danger" data-dismiss="alert">&times;</button></div>');
                    return;
                }

                sbutton.html('<i class="fa fa-circle-o-notch fa-spin"></i> Working ...');
                sbutton.addClass('btn-danger');

                $.post(_url + 'contacts/add-post/', {
                    buyer_type: buyerType,
                    cid: buyerType === 'company' && !isNewCompany && /^\d+$/.test(selectedCompanyId) ? selectedCompanyId : '',
                    account: buyerType === 'individual' ? accountName : '',
                    company: buyerType === 'company' && isNewCompany ? companyName : '',
                    company_url: buyerType === 'company' && isNewCompany ? companyUrl : '',
                    logo_url: buyerType === 'company' && isNewCompany ? logoUrl : '',
                    address: buyerType === 'company' && isNewCompany ? address : '',
                    city: buyerType === 'company' && isNewCompany ? city : '',
                    state: buyerType === 'company' && isNewCompany ? state : '',
                    zip: buyerType === 'company' && isNewCompany ? zip : '',
                    country: buyerType === 'company' && isNewCompany ? country : '',
                    building_number: buyerType === 'company' && isNewCompany ? buildingNumber : '',
                    vat_number: buyerType === 'company' && isNewCompany ? vatNumber : '',
                    crn_number: buyerType === 'company' && isNewCompany ? crnNumber : '',
                    id_iqama: buyerType === 'individual' ? idIqama : '',
                    phone: phone,
                    email: email
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

                            sbutton.html('<i class="fal fa-check"></i> {$_L['Add Contact']}');
                            sbutton.removeClass('btn-danger');


                            $modal.find('.modal-body')
                                .prepend('<div class="alert alert-danger fade in">' + data +
                                    '<button type="button" class="close btn btn-danger" data-dismiss="alert">&times;</button>' +
                                    '</div>');
                            //  $("#cid").select2('data', { id: newID, text: newText });
                        }
                    });


            });


            $("#add_discount").click(function (e) {
                e.preventDefault();
                var s_discount_amount = $('#discount_amount');
                var c_discount = s_discount_amount.val();
                var c_discount_type = $('#discount_type').val();
                var p_checked = "";
                var f_checked = "";
                if (c_discount_type == "p") {
                    p_checked = 'checked="checked"';
                } else {
                    f_checked = 'checked="checked"';
                }
                bootbox.dialog({
                        title: $("#_lan_set_discount").val(),
                        message: '<div class="row">  ' +
                        '<div class="col-md-12"> ' +
                        '<form class="form-horizontal" action="javascript:void(0);"> ' +
                        '<div class="mb-3"> ' +
                        '<label class="col-md-4 control-label" for="set_discount">' + $("#_lan_discount").val() + '</label> ' +
                        '<div class="col-md-4"> ' +
                        '<input id="set_discount" name="set_discount" type="text" class="form-control input-md" value="' + c_discount + '"> ' +
                        '</div> ' +
                        '</div> ' +
                        '<div class="mb-3"> ' +
                        '<label class="col-md-4 control-label" for="set_discount_type">' + $("#_lan_discount_type").val() + '</label> ' +
                        '<div class="col-md-4"> <div class="radio"> <label for="set_discount_type-0"> ' +
                        '<input type="radio" name="set_discount_type" id="set_discount_type-0" value="p" ' + p_checked + '> ' +
                        '' + $("#_lan_percentage").val() + ' (%) </label> ' +
                        '</div><div class="radio"> <label for="set_discount_type-1"> ' +
                        '<input type="radio" name="set_discount_type" id="set_discount_type-1" value="f" ' + f_checked + '> ' + $("#_lan_fixed_amount").val() + ' </label> ' +
                        '</div> ' +
                        '</div> </div>' +
                        '</form> </div>  </div>',
                        buttons: {
                            success: {
                                label: $("#_lan_btn_save").val(),
                                className: "btn-success",
                                callback: function () {
                                    var discount_amount = $('#set_discount').val();
                                    var discount_type = $("input[name='set_discount_type']:checked").val();
                                    $('#discount_amount').val(discount_amount);
                                    $('#discount_type').val(discount_type);
                                    calculateTotal();
                                    //updateTax();
                                    //updateTotal();
                                }
                            }
                        }
                    }
                );
            });


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

            // Real-time duplicate full invoice number check (prefix + number)
            function checkDuplicateInvoiceNumber() {
                var invoicenum = $('#invoicenum').val().trim();
                var cn = $('#cn').val().trim();
                var documentType = $('#document_type').val() || 'invoice';
                
                if (invoicenum === '' || cn === '') {
                    $('#invoicenum').removeClass('is-invalid');
                    $('#cn').removeClass('is-invalid');
                    $('#invoicenum_warning').remove();
                    return; // Skip empty values
                }

                $.ajax({
                    type: 'POST',
                    url: _url + 'invoices/check-duplicate-number/',
                    data: {
                        invoicenum: invoicenum,
                        cn: cn,
                        document_type: documentType,
                        invoice_id: $('#invoice_id').val() || ''
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.exists) {
                            $('#invoicenum').addClass('is-invalid');
                            $('#cn').addClass('is-invalid');
                            $('#invoicenum_warning').remove();
                            $('#cn').after('<small id="invoicenum_warning" class="text-danger d-block mt-1"><i class="fas fa-exclamation-circle"></i> ' + response.message + '</small>');
                        } else {
                            $('#invoicenum').removeClass('is-invalid');
                            $('#cn').removeClass('is-invalid');
                            $('#invoicenum_warning').remove();
                        }
                    },
                    error: function () {
                        // Silently fail - validation will still happen on submit
                    }
                });
            }

            $('#invoicenum, #cn').on('blur', checkDuplicateInvoiceNumber);

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


                        b_p = '<div class="col-lg-2 col-md-2 col-sm-3 col-xs-4"><div class="pos_item text-center" id="pos_item_'+ val.id +'" data-pos-item-name="'+val.name+'" data-pos-item-price="'+val.sales_price+'" data-id="'+ val.id +'" data-pos-item-number="'+ val.item_number +'"><img src="'+ image +'" alt="'+ item_name +'" class="img-circle"><hr>'+ item_name +' <br>'+ val.sales_price +'  <hr></div> </div>';

                        items = items + b_p;
                    });

                    $block_items.html(items);

                    $('#ib_search_input').hideseek({
                        highlight: true
                    });

                });

            }

            loadItems();

            var pos_item_name, pos_item_price, pos_item_id, pos_item_number;

            var item_sl = 1;

            $block_items.on('click', '.pos_item', function () {

                pos_item_number = $(this).data('pos-item-number');
                pos_item_name = $(this).data('pos-item-name');
                pos_item_price = $(this).data('pos-item-price');
                pos_item_id = $(this).data('id');

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


                $invoice_items.find('tbody')
                    .prepend(
                        '<tr><td class="text-center align-middle"><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button></td><td><input type="text" class="form-control item_name" name="desc[]" value="' + pos_item_name + '"><input type="hidden" name="item_code[]" value="' + pos_item_number + '"></td><td><input type="text" class="form-control qty" value="1" name="qty[]"></td><td><input type="text" class="form-control item_price" name="amount[]" value="' + pos_item_price + '"></td><td colspan="2"><input type="text" class="form-control item_discount" name="discount[]" value=""></td><td><select class="form-control taxed" name="taxed[]" id="t_' + rowNum + '">{foreach $t as $ts}<option value="{$ts['rate']}" {if $ts['is_default'] eq '1'}selected{/if}>{$ts['name']}</option>{/foreach}</select></td><td><input type="text" class="form-control lvat" readonly="" value=""></td><td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td><td class="text-center align-middle row-action-cell"><button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button></td></tr>'
                    );


                spMultiSelect('#t_' + rowNum);

                syncInvoiceRowButtons();

                calculateTotal();

                item_sl = item_sl + 1;


            });



            {/if}

        });
    </script>
{/block}
