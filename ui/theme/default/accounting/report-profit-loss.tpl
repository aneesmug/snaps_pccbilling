{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Profit & Loss Report -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-chart-line"></i>
                {$_L['Profit & Loss Statement']}
            </h2>
            <small class="text-muted">{$_L['Period']} {$from_date} {$_L['to']} {$to_date}</small>
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
                <input type="hidden" name="ng" value="accounts/financial-reports/profit-loss">
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

    <!-- P&L Report -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <table class="table table-sm table-striped table-bordered print-table datatable" id="pl_table" style="margin-top: 20px;">
                        <thead class="table-light">
                            <tr>
                                <th>{$_L['Account']}</th>
                                <th class="text-right" width="150">{$_L['Amount']}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- REVENUE SECTION -->
                            <tr class="font-weight-bold bg-light">
                                <td>{$_L['REVENUE']|default:'REVENUE'}</td>
                                <td class="text-right">{$data.revenue.total|default:0|number_format:2}</td>
                            </tr>
                            {foreach $data.revenue.items|default:[] as $item}
                                <tr>
                                    <td style="padding-left: 40px;">{$item->name|default:''}</td>
                                    <td class="text-right">{$item->amount|default:0|number_format:2}</td>
                                </tr>
                            {/foreach}

                            <!-- EXPENSES -->
                            <tr class="font-weight-bold bg-light mt-3">
                                <td>{$_L['Operating Expenses']|default:'Operating Expenses'}</td>
                                <td class="text-right">{$data.expenses.total|default:0|number_format:2}</td>
                            </tr>
                            {foreach $data.expenses.items|default:[] as $item}
                                <tr>
                                    <td style="padding-left: 40px;">{$item->name|default:''}</td>
                                    <td class="text-right">{$item->amount|default:0|number_format:2}</td>
                                </tr>
                            {/foreach}

                            <!-- NET INCOME -->
                            <tr class="font-weight-bold border-top border-bottom border-3" style="border-width: 3px !important;">
                                <td>{$_L['NET INCOME']|default:'NET INCOME'}</td>
                                <td class="text-right text-success" style="font-size: 1.2em;">
                                    {$data.net_income|default:0|number_format:2}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .btn, .form-inline, .print-hidden { display: none !important; }
    .print-table { margin-top: 20px; }
</style>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#pl_table')) {
        $('#pl_table').DataTable().destroy();
    }
    $('#pl_table').DataTable({
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
