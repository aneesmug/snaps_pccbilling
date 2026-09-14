{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Cash Flow Report -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-water"></i>
                {$_L['Cash Flow Statement']}
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
                <input type="hidden" name="ng" value="accounts/financial-reports/cash-flow">
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

    <!-- Cash Flow Report -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <table class="table table-sm print-table datatable" style="margin-top: 20px;">
                        <thead class="table-light">
                            <tr>
                                <th>{$_L['Category']}</th>
                                <th class="text-right" width="150">{$_L['Amount']}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- OPERATING ACTIVITIES -->
                            <tr class="font-weight-bold bg-light">
                                <td>{$_L['Cash Flow from Operating Activities']}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">{$_L['Net Income']}</td>
                                <td class="text-right">{$data['net_income']|number_format:2}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">+ {$_L['Depreciation & Amortization']}</td>
                                <td class="text-right">{$data['depreciation']|number_format:2}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">Â± {$_L['Change in AR/AP']}</td>
                                <td class="text-right">{$data['ar_ap_change']|number_format:2}</td>
                            </tr>
                            {assign var="operating_cf" value=$data['net_income'] + $data['depreciation'] + $data['ar_ap_change']}
                            <tr class="font-weight-bold">
                                <td style="padding-left: 40px;">{$_L['Net Operating Cash Flow']}</td>
                                <td class="text-right">{$operating_cf|number_format:2}</td>
                            </tr>

                            <!-- INVESTING ACTIVITIES -->
                            <tr class="font-weight-bold bg-light mt-3">
                                <td>{$_L['Cash Flow from Investing Activities']}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">- {$_L['Fixed Asset Purchases']}</td>
                                <td class="text-right">({$data['capex']|number_format:2})</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">+ {$_L['Asset Sales']}</td>
                                <td class="text-right">{$data['asset_sales']|number_format:2}</td>
                            </tr>
                            {assign var="investing_cf" value=-$data['capex'] + $data['asset_sales']}
                            <tr class="font-weight-bold">
                                <td style="padding-left: 40px;">{$_L['Net Investing Cash Flow']}</td>
                                <td class="text-right">{$investing_cf|number_format:2}</td>
                            </tr>

                            <!-- FINANCING ACTIVITIES -->
                            <tr class="font-weight-bold bg-light mt-3">
                                <td>{$_L['Cash Flow from Financing Activities']}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">+ {$_L['Debt Proceeds']}</td>
                                <td class="text-right">{$data['debt_proceeds']|number_format:2}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">- {$_L['Debt Repayments']}</td>
                                <td class="text-right">({$data['debt_repayments']|number_format:2})</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 40px;">- {$_L['Dividend Payments']}</td>
                                <td class="text-right">({$data['dividends']|number_format:2})</td>
                            </tr>
                            {assign var="financing_cf" value=$data['debt_proceeds'] - $data['debt_repayments'] - $data['dividends']}
                            <tr class="font-weight-bold">
                                <td style="padding-left: 40px;">{$_L['Net Financing Cash Flow']}</td>
                                <td class="text-right">{$financing_cf|number_format:2}</td>
                            </tr>

                            <!-- NET CHANGE IN CASH -->
                            <tr class="font-weight-bold border-top border-bottom border-3" style="border-width: 3px !important;">
                                <td>{$_L['Net Change in Cash']}</td>
                                <td class="text-right" style="font-size: 1.2em;">
                                    {($operating_cf + $investing_cf + $financing_cf)|number_format:2}
                                </td>
                            </tr>

                            <!-- CASH POSITION -->
                            <tr>
                                <td>{$_L['Beginning Cash Balance']}</td>
                                <td class="text-right">{$data['beginning_cash']|number_format:2}</td>
                            </tr>
                            <tr class="font-weight-bold border-top">
                                <td>{$_L['Ending Cash Balance']}</td>
                                <td class="text-right text-success" style="font-size: 1.2em;">
                                    {($data['beginning_cash'] + $operating_cf + $investing_cf + $financing_cf)|number_format:2}
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

</div></div></div></div>
{/block}
