{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Balance Sheet Report -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-building"></i>
                {$_L['Balance Sheet']}
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
                <input type="hidden" name="ng" value="accounts/financial-reports/balance-sheet">
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

    <!-- Balance Sheet -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <!-- ASSETS -->
                    <h4 class="font-weight-bold mb-3 text-primary">{$_L['ASSETS']}</h4>
                    <table class="table table-sm table-striped table-bordered mb-4 datatable" id="bs_assets_table">
                        <tbody>
                            {assign var="total_assets" value=$data.assets.total|default:0}
                            {foreach $data.assets.items|default:[] as $item}
                                <tr>
                                    <td>{$item.name|default:''}</td>
                                    <td class="text-right">{$item.balance|default:0|number_format:2}</td>
                                </tr>
                            {/foreach}
                            <tr class="font-weight-bold border-top">
                                <td>{$_L['TOTAL ASSETS']}</td>
                                <td class="text-right text-primary">{$total_assets|number_format:2}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <!-- LIABILITIES & EQUITY -->
                    <h4 class="font-weight-bold mb-3 text-danger">{$_L['LIABILITIES']}</h4>
                    <table class="table table-sm table-striped table-bordered mb-4 datatable" id="bs_liabilities_table">
                        <tbody>
                            {assign var="total_liabilities" value=$data.liabilities.total|default:0}
                            {foreach $data.liabilities.items|default:[] as $item}
                                <tr>
                                    <td>{$item.name|default:''}</td>
                                    <td class="text-right">{$item.balance|default:0|number_format:2}</td>
                                </tr>
                            {/foreach}
                            <tr class="font-weight-bold border-top">
                                <td>{$_L['TOTAL LIABILITIES']}</td>
                                <td class="text-right text-danger">{$total_liabilities|number_format:2}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4 class="font-weight-bold mb-3 text-info">{$_L['EQUITY']}</h4>
                    <table class="table table-sm table-striped table-bordered datatable" id="bs_equity_table">
                        <tbody>
                            {assign var="total_equity" value=$data.equity.total|default:0}
                            {foreach $data.equity.items|default:[] as $item}
                                <tr>
                                    <td>{$item.name|default:''}</td>
                                    <td class="text-right">{$item.balance|default:0|number_format:2}</td>
                                </tr>
                            {/foreach}
                            <tr class="font-weight-bold border-top">
                                <td>{$_L['TOTAL EQUITY']}</td>
                                <td class="text-right text-info">{$total_equity|number_format:2}</td>
                            </tr>
                            <tr class="font-weight-bold border-top border-bottom border-3">
                                <td>{$_L['TOTAL LIAB. & EQUITY']}</td>
                                <td class="text-right">{($total_liabilities + $total_equity)|number_format:2}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-footer">
            {if $data.equation_balanced|default:false}
                <div class="alert alert-success mb-0">
                    <i class="fal fa-check-circle"></i>
                    {$_L['Balance Sheet is balanced']}
                </div>
            {else}
                <div class="alert alert-danger mb-0">
                    <i class="fal fa-exclamation-circle"></i>
                    {$_L['Balance Sheet does not balance']}!
                </div>
            {/if}
        </div>
    </div>
</div>

<style media="print">
    .btn, .form-inline, .print-hidden { display: none !important; }
</style>

<script>
$(document).ready(function() {
    ['#bs_assets_table', '#bs_liabilities_table', '#bs_equity_table'].forEach(function(selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
        }
        $(selector).DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            responsive: true,
            pageLength: 25
        });
    });
});
</script>

</div></div></div></div>
{/block}
