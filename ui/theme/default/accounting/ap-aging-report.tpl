{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - AP Aging Report -->
<div class="row">
<div class="col-md-12">
<div class="card">
<div class="card-header">
<h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2>
</div>
<div class="card-body">

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline">
                <input type="hidden" name="ng" value="accounts/ap-dashboard/aging">
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


    <style>
      .aging-summary-card {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #fff !important;
        border-radius: 8px;
        padding: 20px 16px;
        margin-bottom: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.10);
        text-decoration: none !important;
      }
      .aging-summary-card h5 {
        font-size: 28px;
        line-height: 1;
        font-weight: 700;
        margin-bottom: 8px;
      }
      .aging-summary-card .label {
        font-size: 14px;
        font-weight: 600;
      }
      .aging-green { background: linear-gradient(135deg, #24b9c7 0%, #3bc18f 100%); }
      .aging-orange { background: linear-gradient(135deg, #ffb547 0%, #ffa500 100%); }
      .aging-gray { background: linear-gradient(135deg, #7c8fa3 0%, #98a8bb 100%); }
      .aging-red { background: linear-gradient(135deg, #f26545 0%, #f43f5e 100%); }
    </style>

    <!-- Aging Summary with Colorful Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="aging-summary-card aging-green">
                <h5>{$summary->current|number_format:2}</h5>
                <div class="label"><i class="fal fa-clock"></i> {$_L['Current']}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aging-summary-card aging-orange">
                <h5>{$summary->days_30|number_format:2}</h5>
                <div class="label"><i class="fal fa-calendar"></i> {$_L['30-60 Days']}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aging-summary-card aging-gray">
                <h5>{$summary->days_60|number_format:2}</h5>
                <div class="label"><i class="fal fa-calendar"></i> {$_L['60-90 Days']}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aging-summary-card aging-red">
                <h5>{$summary->days_90|number_format:2}</h5>
                <div class="label"><i class="fal fa-exclamation-circle"></i> {$_L['90+ Days']}</div>
            </div>
        </div>
    </div>


    <!-- AP Aging Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['AP Aging Detail']}</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered print-table mb-0 datatable" id="aging_table">
                    <thead class="table-light">
                        <tr>
                            <th>{$_L['Vendor']}</th>
                            <th class="text-right">{$_L['Current']}</th>
                            <th class="text-right">{$_L['30-60 Days']}</th>
                            <th class="text-right">{$_L['60-90 Days']}</th>
                            <th class="text-right">{$_L['90+ Days']}</th>
                            <th class="text-right">{$_L['Total']}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {assign var="total_current" value=0}
                        {assign var="total_30" value=0}
                        {assign var="total_60" value=0}
                        {assign var="total_90" value=0}
                        {foreach $aging_details as $vendor}
                            {assign var="total_current" value=$total_current + $vendor['current']}
                            {assign var="total_30" value=$total_30 + $vendor['days_30']}
                            {assign var="total_60" value=$total_60 + $vendor['days_60']}
                            {assign var="total_90" value=$total_90 + $vendor['days_90']}
                            <tr>
                                <td><strong>{$vendor['name']}</strong></td>
                                <td class="text-right">{$vendor['current']|number_format:2}</td>
                                <td class="text-right">{$vendor['days_30']|number_format:2}</td>
                                <td class="text-right">{$vendor['days_60']|number_format:2}</td>
                                <td class="text-right text-danger">{$vendor['days_90']|number_format:2}</td>
                                <td class="text-right font-weight-bold">
                                    {($vendor['current'] + $vendor['days_30'] + $vendor['days_60'] + $vendor['days_90'])|number_format:2}
                                </td>
                            </tr>
                        {/foreach}
                        <tr class="table-active font-weight-bold border-top">
                            <td>{$_L['TOTAL']}</td>
                            <td class="text-right">{$total_current|number_format:2}</td>
                            <td class="text-right">{$total_30|number_format:2}</td>
                            <td class="text-right">{$total_60|number_format:2}</td>
                            <td class="text-right">{$total_90|number_format:2}</td>
                            <td class="text-right">{($total_current + $total_30 + $total_60 + $total_90)|number_format:2}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .btn, .form-inline { display: none !important; }
    .print-table { margin-top: 20px; }
</style>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#aging_table')) {
        $('#aging_table').DataTable().destroy();
    }
    $('#aging_table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        responsive: true,
        pageLength: 25,
        language: {
            search: "Filter:",
            lengthMenu: "_MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ records"
        }
    });
});
</script>

</div></div></div></div>
{/block}
