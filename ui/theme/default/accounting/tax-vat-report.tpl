{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - VAT Report -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-receipt"></i>
                {$_L['VAT Report']}
            </h2>
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
                <input type="hidden" name="ng" value="accounts/tax-vat/vat-report">
                <div class="form-group mr-3">
                    <label class="mr-2">{$_L['From']}:</label>
                    <input type="text"  name="from_date" class="form-control" value="{$from_date}" datepicker data-date-format="yyyy-mm-dd" data-auto-close="true">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">{$_L['To']}:</label>
                    <input type="text"  name="to_date" class="form-control" value="{$to_date}" datepicker data-date-format="yyyy-mm-dd" data-auto-close="true">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fal fa-search"></i>
                    {$_L['Generate']}
                </button>
            </form>
        </div>
    </div>

    <!-- VAT Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['Total Sales']}</h6>
                    <h3 class="text-primary">
                        {$summary->total_sales|number_format:2}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['Output VAT']}</h6>
                    <h3 class="text-success">
                        {$summary->output_vat|number_format:2}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['Input VAT']}</h6>
                    <h3 class="text-info">
                        {$summary->input_vat|number_format:2}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- VAT Due -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="card-title">{$_L['Output Tax (Sales)']}</h2>
                    <p class="text-success" style="font-size: 1.5em;">
                        {$summary->output_vat|number_format:2}
                    </p>
                </div>
                <div class="col-md-6">
                    <h2 class="card-title">{$_L['Input Tax (Purchases)']}</h2>
                    <p class="text-info" style="font-size: 1.5em;">
                        {$summary->input_vat|number_format:2}
                    </p>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-md-12">
                    <h4 class="font-weight-bold">{$_L['Net VAT Due']}</h4>
                    <p class="text-primary" style="font-size: 1.8em;">
                        {($summary->output_vat - $summary->input_vat)|number_format:2}
                    </p>
                    <small class="text-muted">
                        {if ($summary->output_vat - $summary->input_vat) > 0}
                            {$_L['VAT payable to tax authority']}
                        {else}
                            {$_L['VAT refund due from tax authority']}
                        {/if}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- VAT by Code -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['VAT by Code']}</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>{$_L['Tax Code']}</th>
                            <th class="text-right">{$_L['Rate']}</th>
                            <th class="text-right">{$_L['Taxable Amount']}</th>
                            <th class="text-right">{$_L['VAT Amount']}</th>
                            <th>{$_L['Type']}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $vat_details as $code => $detail}
                            <tr>
                                <td>{$code}</td>
                                <td class="text-right">{$detail['rate']}%</td>
                                <td class="text-right">{$detail['taxable']|number_format:2}</td>
                                <td class="text-right font-weight-bold">{$detail['vat']|number_format:2}</td>
                                <td>
                                    <span class="badge badge-{if $detail['type'] == 'output'}success{else}info{/if}">
                                        {ucfirst($detail['type'])}
                                    </span>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary { border-left: 4px solid #007bff; }
    .border-left-success { border-left: 4px solid #28a745; }
    .border-left-info { border-left: 4px solid #17a2b8; }
    @media print {
        .btn, .form-inline { display: none !important; }
    }
</style>

</div></div></div></div>
{/block}
