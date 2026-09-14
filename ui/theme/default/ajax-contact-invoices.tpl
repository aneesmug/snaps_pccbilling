<a href="{$_url}invoices/add/1/{$cid}/" class="btn btn-primary waves-effect waves-light">{$_L['New Invoice']}</a>
<a href="{$_url}invoices/add/recurring/{$cid}/" class="btn btn-primary waves-effect waves-light">{$_L['New Recurring Invoice']}</a>
<a href="{$_url}contacts/print-statement/{$cid}/" target="_blank" class="btn btn-dark waves-effect waves-light"><i class="fal fa-print"></i> {$_L['Print']} {$_L['Customer Statement']}</a>

<hr>
<br>


<h5> {$_L['Total Invoice Amount']}: <span class="amount" data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-a-pad="{$config['currency_decimal_digits']}" data-p-sign="{$config['currency_symbol_position']}" data-a-sign="{$config['currency_code']} " data-d-group="{$config['thousand_separator_placement']}">{$total_invoice_amount}</span></h5>
<h5 class="text-success"> {$_L['Total Paid Amount']}: <span class="amount" data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-a-pad="{$config['currency_decimal_digits']}" data-p-sign="{$config['currency_symbol_position']}" data-a-sign="{$config['currency_code']} " data-d-group="{$config['thousand_separator_placement']}">{$total_paid_amount}</span></h5>
<h5 class="text-danger"> {$_L['Total Un Paid Amount']}: <span class="amount" data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-a-pad="{$config['currency_decimal_digits']}" data-p-sign="{$config['currency_symbol_position']}" data-a-sign="{$config['currency_code']} " data-d-group="{$config['thousand_separator_placement']}">{$total_unpaid_amount}</span></h5>

<hr>
<div class="table-responsive">
    <table class="table table-striped sys_table">
        <thead style="background: #f0f2ff">
        <tr>
            <th>#</th>
            <th>{$_L['Account']}</th>
            {if !empty($config['invoice_group'])}
                <th>
                    {$_L['Group']}
                </th>
            {/if}
            {if !empty($config['invoice_single_service'])}
                <th>
                    {__('Service')}
                </th>
            {/if}
            <th>{$_L['Staff']}</th>
            <th>{$_L['Amount']}</th>
            <th>{$_L['Total Un Paid Amount']}</th>
            <th>{$_L['Invoice Date']}</th>
            <th>{$_L['Due Date']}</th>
            <th>{$_L['Status']}</th>
            <th class="text-end">{$_L['Manage']}</th>
        </tr>
        </thead>
        <tbody>

        {foreach $invoices as $invoice}
            <tr>
                <td>{$invoice['invoicenum']}{if $invoice['cn'] neq ''} {$invoice['cn']} {else} {$invoice['id']} {/if}</td>
                <td>{$invoice['account']}</td>

                {if !empty($config['invoice_group'])}
                    <td>
                        {if !empty($invoice_groups[$invoice['group_id']])}
                            {$invoice_groups[$invoice['group_id']]->name}
                        {/if}
                    </td>
                {/if}
                {if !empty($config['invoice_single_service'])}
                    <td>
                        {if !empty($services[$invoice['service_id']])}
                            {$services[$invoice['service_id']]->name}
                        {/if}
                    </td>
                {/if}
                <td>
                    {if !empty($staffs[$invoice['aid']])}
                        {$staffs[$invoice['aid']]->fullname}
                    {/if}
                </td>
                <td>{formatCurrency($invoice['total'],$invoice['currency_iso_code'])}</td>
                <td>{formatCurrency($invoice['total']-$invoice['credit'],$invoice['currency_iso_code'])}</td>
                <td>{date( $config['df'], strtotime($invoice['date']))}</td>
                <td>{date( $config['df'], strtotime($invoice['duedate']))}</td>
                <td>{ib_lan_get_line($invoice['status'])}</td>
                <td>
                    <div class="btn-group float-end">
                        <a href="{$_url}invoices/view/{$invoice['id']}/" class="btn btn-primary btn-sm"> {$_L['View']}</a>
                        <a href="{$_url}invoices/edit/{$invoice['id']}/" class="btn btn-info btn-sm"> {$_L['Edit']}</a>

                    </div>

                </td>
            </tr>
        {/foreach}

        </tbody>
    </table>
</div>


