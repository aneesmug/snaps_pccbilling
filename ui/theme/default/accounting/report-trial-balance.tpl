{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Trial Balance Report -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-balance-scale"></i>
                {$_L['Trial Balance']}
            </h2>
            <small class="text-muted">{$_L['As of']} {$as_of_date}</small>
        </div>
        <div class="col-md-4 text-right">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fal fa-print"></i>
                {$_L['Print']}
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline">
                <input type="hidden" name="ng" value="accounts/financial-reports/trial-balance">
                <div class="form-group mr-3">
                    <label class="mr-2">{$_L['As of Date']}:</label>
                    <input type="text"  name="as_of_date" class="form-control" value="{$as_of_date}" datepicker data-date-format="yyyy-mm-dd" data-auto-close="true">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fal fa-search"></i>
                    {$_L['Generate']}
                </button>
            </form>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Account Balances']}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered mb-0 print-table datatable" id="trial_balance_table">
                    <thead class="table-light">
                        <tr>
                            <th width="80">{$_L['Code']}</th>
                            <th>{$_L['Account Name']}</th>
                            <th class="text-right" width="120">{$_L['Debit']}</th>
                            <th class="text-right" width="120">{$_L['Credit']}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {assign var="total_debit" value=$data.totals.debit|default:0}
                        {assign var="total_credit" value=$data.totals.credit|default:0}
                        {foreach $data.accounts|default:[] as $account}
                            <tr>
                                <td>{$account['code']}</td>
                                <td>{$account['name']}</td>
                                <td class="text-right">{if $account['debit']}{$account['debit']|number_format:2}{else}&mdash;{/if}</td>
                                <td class="text-right">{if $account['credit']}{$account['credit']|number_format:2}{else}&mdash;{/if}</td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td colspan="4" class="text-center text-muted">{$_L['No records found']|default:'No records found'}</td>
                            </tr>
                        {/foreach}
                        <tr class="table-active font-weight-bold border-top-2">
                            <td colspan="2">{$_L['TOTAL']}</td>
                            <td class="text-right">{$total_debit|number_format:2}</td>
                            <td class="text-right">{$total_credit|number_format:2}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {if $data.is_balanced|default:false}
                <div class="alert alert-success mb-0">
                    <i class="fal fa-check-circle"></i>
                    {$_L['Trial Balance is verified']} - {$_L['Debit']} = {$_L['Credit']}
                </div>
            {else}
                <div class="alert alert-danger mb-0">
                    <i class="fal fa-exclamation-circle"></i>
                    {$_L['Trial Balance does not balance']}!
                    {$_L['Difference']}: {$data.totals.difference|default:0|number_format:2}
                </div>
            {/if}
        </div>
    </div>
</div>

<style media="print">
    .btn, .form-inline, .print-hidden { display: none !important; }
    .print-table { margin-top: 20px; }
</style>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#trial_balance_table')) {
        $('#trial_balance_table').DataTable().destroy();
    }
    $('#trial_balance_table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        responsive: true,
        pageLength: 25
    });
});
</script>

</div></div></div></div>
{/block}
