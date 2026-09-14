{extends file="layouts/base.tpl"}
{block name="content_body"}
    <div class="mx-auto" style="max-width: 980px; width: 100%; margin-top: 50px;">
        <section class="panel">

            <div class="panel-container">
                <div class="panel-content">

                    <div class="invoice">

                        {if isset($notify)}
                            {$notify}
                        {/if}

                        <div>

                            <div class="text-end mb-3">

                                <a href="{$_url}supplier/dashboard/" class="btn btn-primary ml-sm no-shadow no-border"><i class="fal fa-long-arrow-left"></i> Back to Supplier Area</a>
                                <a href="{$_url}supplier/purchase_pdf/{$d['id']}/token_{$d['vtoken']}/dl/" class="btn btn-primary buttons-pdf ml-sm"><i class="fal fa-file-pdf-o"></i> {$_L['Download PDF']}</a>
                                <a href="{$_url}supplier/purchase_pdf/{$d['id']}/token_{$d['vtoken']}/view/" class="btn btn-primary buttons-excel ml-sm"><i class="fal fa-file-text-o"></i> {$_L['View PDF']}</a>
                                <a href="{$_url}supplier/purchase_print/{$d['id']}/token_{$d['vtoken']}" target="_blank" class="btn btn-primary buttons-print ml-sm"><i class="fal fa-print"></i> {$_L['Printable Version']}</a>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mt-md">
                                    <h2 class="h2 mt-none mb-sm text-dark text-bold">{$_L['Purchase']}</h2>
                                    <h4 class="h4 m-none text-dark text-bold">#{$d['invoicenum']}{if $d['cn'] neq ''} {$d['cn']} {else} {$d['id']} {/if}</h4>
                                    {if $d['status'] eq 'Unpaid'}
                                        <h3 class="alert alert-danger">{$_L['Unpaid']}</h3>
                                    {elseif $d['status'] eq 'Paid'}
                                        <h3 class="alert alert-success">{$_L['Paid']}</h3>
                                    {elseif $d['status'] eq 'Partially Paid'}
                                        <h3 class="alert alert-info">{$_L['Partially Paid']}</h3>
                                    {else}
                                        <h3 class="alert alert-info">{$d['status']}</h3>
                                    {/if}

                                    {if $config['invoice_receipt_number'] eq '1' && $d['receipt_number'] neq ''}
                                        <h4>{$_L['Receipt Number']}: {$d['receipt_number']}</h4>
                                        <hr>
                                    {/if}

                                </div>
                                <div class="col-sm-6 text-end mt-md mb-md">

                                    <div class="ib">
                                        <img src="{$app_url}storage/system/{$config['logo_default']}" alt="Logo" style="margin-bottom: 15px; max-height: 50px;">
                                    </div>

                                    <address class="ib mr-xlg">
                                        <strong>{$config['CompanyName']}</strong>
                                        <br>
                                        {$config['caddress']}
                                    </address>

                                </div>
                            </div>
                        </div>

                        <div class="bill-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="bill-to">
                                        <p class="h5 mb-xs text-dark text-semibold"><strong>{$_L['Supplier']}</strong></p>
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
                                            {$a['address']} <br>
                                            {$a['city']} <br>
                                            {$a['state']} - {$a['zip']} <br>
                                            {$a['country']}
                                            <br>
                                            <strong>{$_L['Phone']}:</strong> {$a['phone']}
                                            {if $config['fax_field'] neq '0' && $a['fax'] neq ''}
                                                <br>
                                                <strong>{$_L['Fax']}:</strong> {$a['fax']}
                                            {/if}
                                            <br>
                                            <strong>{$_L['Email']}:</strong> {$a['email']}
                                            {foreach $cf as $cfs}
                                                <br>
                                                <strong>{$cfs['fieldname']}: </strong> {get_custom_field_value($cfs['id'],$a['id'])}
                                            {/foreach}
                                        </address>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bill-data text-end">

                                        <p class="mb-none">

                                            <span class="text-dark">{$_L['Issued at']}</span>

                                            {*<span class="text-dark">Issued at</span>*}

                                            <span class="value">{date( $config['df'], strtotime($d['date']))}</span>
                                        </p>

                                        {*<p class="mb-none">*}
                                        {*<span class="text-dark">{$_L['Due Date']}:</span>*}
                                        {*<span class="value">{date( $config['df'], strtotime($d['duedate']))}</span>*}
                                        {*</p>*}

                                        <h2> {$_L['Invoice Total']}: {ib_money_format($d['total'],$config,$d['currency_symbol'])} </h2>
                                        {if ($d['credit']) neq '0.00'}
                                            <h2> {$_L['Total Paid']}: {ib_money_format($d['credit'],$config,$d['currency_symbol'])}</h2>
                                            <h2> {$_L['Amount Due']}: {ib_money_format($i_due,$config,$d['currency_symbol'])}</h2>
                                        {/if}
                                        {if (($d['status']) neq 'Paid') AND (ib_pg_count() neq '0' AND (($d['status']) neq 'Cancelled'))}

                                            {if $a->balance > 0 && $d->is_credit_invoice neq 1}
                                                <hr>
                                                <h3> Your Current Balance: <span class="amount">{$a->balance}</span> </h3>
                                                <a class="btn btn-primary" href="{$_url}client/pay_with_credit/{$d->id}/token_{$d->vtoken}"> Pay with Credit</a>
                                                <hr>
                                            {/if}

                                        {/if}

                                    </div>
                                </div>
                            </div>

                            {if $d['subject'] neq ''}
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr>

                                        <strong>{$d['subject']}</strong>

                                        <hr>

                                    </div>
                                </div>
                            {/if}

                        </div>

                        <div class="table-responsive">
                            <table class="table invoice-items">
                                <thead>
                                <tr class="h4 text-dark">
                                    <th id="cell-id" class="text-semibold">#</th>
                                    <th id="cell-item" class="text-semibold">{$_L['Item']}</th>

                                    <th id="cell-price" class="text-center text-semibold">{$_L['Price']}</th>
                                    {*<th id="cell-qty" class="text-center text-semibold">{$_L['Quantity']}</th>*}
                                    <th id="cell-qty" class="text-center text-semibold">{if $d['show_quantity_as'] eq '' || $d['show_quantity_as'] eq '1'}{$_L['Qty']}{else}{$d['show_quantity_as']}{/if}</th>
                                    <th id="cell-total" class="text-center text-semibold">{$_L['Total']}</th>
                                </tr>
                                </thead>
                                <tbody>

                                {foreach $items as $item}
                                    <tr>
                                        <td>{$item['itemcode']}</td>
                                        <td class="text-semibold text-dark">{$item['description']}</td>

                                        <td class="text-center">{ib_money_format($item['amount'],$config,$d['currency_symbol'])}</td>
                                        <td class="text-center">{$item['qty']}</td>
                                        <td class="text-center">{ib_money_format($item['total'],$config,$d['currency_symbol'])}</td>
                                    </tr>
                                {/foreach}

                                </tbody>
                            </table>
                        </div>

                        <div class="invoice-summary">
                            <div class="row">
                                <div class="col-sm-4 offset-md-8">
                                    <table class="table h5 text-dark">
                                        <tbody>
                                        <tr class="b-top-none">
                                            <td colspan="2">{$_L['Sub Total']}</td>
                                            <td class="text-left">{ib_money_format($d['subtotal'],$config,$d['currency_symbol'])}</td>
                                        </tr>

                                        {if ($d['discount']) neq '0.00'}
                                            <tr>
                                                <td colspan="2">{$_L['Discount']}
                                                    {if $d['discount_type'] eq 'p'}({$d['discount_value']}%){/if}
                                                </td>
                                                <td class="text-left">{ib_money_format($d['discount'],$config,$d['currency_symbol'])}</td>
                                            </tr>
                                        {/if}

                                        <tr>
                                            <td colspan="2">{$_L['TAX']}</td>
                                            <td class="text-left">{ib_money_format($d['tax'],$config,$d['currency_symbol'])}</td>
                                        </tr>
                                        {if ($d['credit']) neq '0.00'}
                                            <tr>
                                                <td colspan="2">{$_L['Total']}</td>
                                                <td class="text-left">{ib_money_format($d['total'],$config,$d['currency_symbol'])}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">{$_L['Total Paid']}</td>
                                                <td class="text-left">{ib_money_format($d['credit'],$config,$d['currency_symbol'])}</td>
                                            </tr>
                                            <tr class="h4">
                                                <td colspan="2">{$_L['Amount Due']}</td>
                                                <td class="text-left">{ib_money_format($i_due,$config,$d['currency_symbol'])}</td>
                                            </tr>
                                        {else}
                                            <tr class="h4">
                                                <td colspan="2">{$_L['Grand Total']}</td>
                                                <td class="text-left">{ib_money_format($d['total'],$config,$d['currency_symbol'])}</td>
                                            </tr>
                                        {/if}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>



                        {if !empty($trs_c)}
                            <h3>{$_L['Related Transactions']}</h3>
                            <table class="table table-bordered sys_table">
                                <th>{$_L['Date']}</th>
                                <th>{$_L['Account']}</th>


                                <th class="text-end">{$_L['Amount']}</th>

                                <th>{$_L['Description']}</th>




                                {foreach $trs as $tr}
                                    <tr class="{if $tr['cr'] eq '0.00'}warning {else}info{/if}">
                                        <td>{date( $config['df'], strtotime($tr['date']))}</td>
                                        <td>{$tr['account']}</td>


                                        <td class="text-end">{ib_money_format($tr['amount'],$config,$d['currency_symbol'])}</td>
                                        <td>{$tr['description']}</td>




                                    </tr>
                                {/foreach}



                            </table>
                        {/if}

                        {if !empty($inv_files_c)}

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


                    </div>



                </div>
            </div>

        </section>
    </div>
{/block}
