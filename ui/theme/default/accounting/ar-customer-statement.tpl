{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Customer Statement -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-file-alt"></i>
                {$_L['Customer Statement']}
            </h2>
            <small class="text-muted">{$customer->name}</small>
        </div>
        <div class="col-md-4 text-right">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fal fa-print"></i>
                {$_L['Print']}
            </button>
            <a href="{$_url}accounts/ar-dashboard/dashboard" class="btn btn-secondary">
                <i class="fal fa-arrow-left"></i>
                {$_L['Back']}
            </a>
        </div>
    </div>

    <!-- Statement Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['Customer Name']}</h6>
                    <p class="mb-0"><strong>{$customer->name}</strong></p>
                    <p class="text-muted"><small>{$customer->address}</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="card-title text-muted">{$_L['Total Outstanding']}</h6>
                            <h4 class="text-primary">{$summary->total_outstanding|number_format:2}</h4>
                        </div>
                        <div class="col-md-6">
                            <h6 class="card-title text-muted">{$_L['Current Amount']}</h6>
                            <h4 class="text-success">{$summary->current|number_format:2}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Transaction History']}</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm print-table mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>{$_L['Date']}</th>
                            <th>{$_L['Transaction']} / {$_L['Reference']}</th>
                            <th class="text-right">{$_L['Charge']}</th>
                            <th class="text-right">{$_L['Payment']}</th>
                            <th class="text-right">{$_L['Balance']}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {assign var="balance" value=0}
                        {foreach $transactions as $txn}
                            {assign var="balance" value=$balance + $txn['charge'] - $txn['payment']}
                            <tr>
                                <td>{$txn['date']}</td>
                                <td>
                                    {if $txn['type'] == 'invoice'}
                                        <span class="badge badge-info">{$_L['Invoice']}</span>
                                    {else}
                                        <span class="badge badge-success">{$_L['Payment']}</span>
                                    {/if}
                                    {$txn['reference']}
                                </td>
                                <td class="text-right">{if $txn['charge']}{$txn['charge']|number_format:2}{else}â€”{/if}</td>
                                <td class="text-right">{if $txn['payment']}{$txn['payment']|number_format:2}{else}â€”{/if}</td>
                                <td class="text-right font-weight-bold">
                                    {$balance|number_format:2}
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Aging Summary -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['Current']}</h6>
                    <h4 class="text-success">{$aging->current|number_format:2}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['30-60 Days']}</h6>
                    <h4 class="text-warning">{$aging->days_30|number_format:2}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['60-90 Days']}</h6>
                    <h4 class="text-orange">{$aging->days_60|number_format:2}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">{$_L['90+ Days']}</h6>
                    <h4 class="text-danger">{$aging->days_90|number_format:2}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .btn, .print-hidden { display: none !important; }
    .print-table { margin-top: 20px; }
</style>

</div></div></div></div>
{/block}
