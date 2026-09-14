<a href="{$_url}contacts/print-statement/{$cid}/" target="_blank" class="btn btn-dark waves-effect waves-light"><i class="fal fa-print"></i> {$_L['Print']} {$_L['Customer Statement']}</a>

<hr>

<div class="panel border">
    <div class="panel-hdr">
        <h2>{$_L['Add Unpaid Amount']}</h2>
    </div>
    <div class="panel-container show">
        <div class="panel-content">
            <form action="{$_url}contacts/due-add-post/" method="post" class="row g-2 align-items-end">
                <input type="hidden" name="cid" value="{$cid}">
                <div class="col-md-3">
                    <label class="form-label">{$_L['Amount']}</label>
                    <input type="text" name="amount" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{$_L['Date']}</label>
                    <input type="date" name="date" class="form-control" value="{date('Y-m-d')}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{$_L['Description']}</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{$_L['Save']}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<hr>

<h5> {$_L['Total Invoice Amount']}: <span class="amount" data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-a-pad="{$config['currency_decimal_digits']}" data-p-sign="{$config['currency_symbol_position']}" data-a-sign="{$config['currency_code']} " data-d-group="{$config['thousand_separator_placement']}">{$total_due_amount}</span></h5>
<h5 class="text-success"> {$_L['Total Paid Amount']}: <span class="amount" data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-a-pad="{$config['currency_decimal_digits']}" data-p-sign="{$config['currency_symbol_position']}" data-a-sign="{$config['currency_code']} " data-d-group="{$config['thousand_separator_placement']}">{$total_paid_amount}</span></h5>
<h5 class="text-danger"> {$_L['Total Un Paid Amount']}: <span class="amount" data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-a-pad="{$config['currency_decimal_digits']}" data-p-sign="{$config['currency_symbol_position']}" data-a-sign="{$config['currency_code']} " data-d-group="{$config['thousand_separator_placement']}">{$total_unpaid_amount}</span></h5>

<hr>

<div class="table-responsive">
    <table class="table table-striped sys_table">
        <thead style="background: #f0f2ff">
        <tr>
            <th>{$_L['Voucher No']}</th>
            <th>{$_L['Date']}</th>
            <th>{$_L['Description']}</th>
            <th class="text-end">{$_L['Amount']}</th>
            <th class="text-end">{$_L['Total Paid Amount']}</th>
            <th class="text-end">{$_L['Balance']}</th>
            <th>{$_L['Status']}</th>
            <th class="text-end">{$_L['Manage']}</th>
        </tr>
        </thead>
        <tbody>

        {foreach $dues as $due}
            {assign var="due_balance" value=$due->amount-$due->paid_amount}
            <tr>
                <td>{$due->voucher_no}</td>
                <td>{date( $config['df'], strtotime($due->date))}</td>
                <td>{$due->description}</td>
                <td class="text-end">{formatCurrency($due->amount)}</td>
                <td class="text-end">{formatCurrency($due->paid_amount)}</td>
                <td class="text-end">{formatCurrency($due_balance)}</td>
                <td>{$due->status}</td>
                <td class="text-end">
                    <div class="btn-group float-end">
                        <a href="{$_url}contacts/print-voucher/{$due->id}/" target="_blank" class="btn btn-dark btn-sm"><i class="fal fa-print"></i> {$_L['Print Voucher']}</a>

                        {if $due_balance > 0}
                            <button type="button" class="btn btn-info btn-sm due-add-payment" data-due-id="{$due->id}">{$_L['Add Payment']}</button>
                        {/if}

                        {if $due->paid_amount == 0}
                            <a href="{$_url}contacts/due-delete/{$due->id}/" class="btn btn-danger btn-sm" onclick="return confirm('{$_L['Are you sure?']|default:'Are you sure?'}');"> {$_L['Delete']}</a>
                        {/if}
                    </div>
                </td>
            </tr>
        {/foreach}

        </tbody>
    </table>
</div>

<form id="due-payment-form" action="{$_url}contacts/due-payment-post/" method="post" style="display:none;">
    <input type="hidden" name="due_id" id="due-payment-id">
    <input type="hidden" name="amount" id="due-payment-amount">
</form>

<script>
    (function(){
        var sysrender = document.getElementById('application_ajaxrender');
        if (!sysrender) { return; }
        sysrender.querySelectorAll('.due-add-payment').forEach(function(btn){
            btn.addEventListener('click', function(){
                var amount = prompt('{$_L['Amount']}');
                if (amount) {
                    document.getElementById('due-payment-id').value = btn.getAttribute('data-due-id');
                    document.getElementById('due-payment-amount').value = amount;
                    document.getElementById('due-payment-form').submit();
                }
            });
        });
    })();
</script>
