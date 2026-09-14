{extends file="$layouts_admin"}

{block name="head"}

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />

{/block}

{block name="content"}

    <div class="panel">
        <div class="panel-hdr">
            <h2>{$_L['Income Reports']} </h2>
        </div>

        <div class="panel-container">
            <div class="panel-content">
                <h4>{$_L['Income Summary']}</h4>


                <div class="row">
                    <div class="col-md-6">
                        <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white mb-g">
                            <div class="">
                                <h5 class="display-4 d-block l-h-n m-0 fw-500">
                                    {formatCurrency($total_income_all_time,$config['home_currency'])}
                                    <small class="m-0 l-h-n">{$_L['Total Income']}</small>
                                </h5>
                                <i class="fal fa-gem position-absolute pos-right pos-bottom opacity-15  mb-n1 mr-n4" style="font-size: 6rem;"></i>


                            </div>

                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-success-200 rounded overflow-hidden position-relative text-white mb-g">
                            <div class="details">
                                <h5 class="display-4 d-block l-h-n m-0 fw-500">
                                    {formatCurrency(Transaction::totalAmount('Income',0,'current_month',$all_data),$config['home_currency'])}
                                    <small class="m-0 l-h-n">{$_L['Total Income This Month']} </small>
                                </h5>
                                <i class="fal fa-lightbulb position-absolute pos-right pos-bottom opacity-15 mb-n5 mr-n6" style="font-size: 8rem;"></i>


                            </div>
                        </div>


                    </div>


                </div>




                <div class="row">
                    <div class="col-md-6">
                        <div class="p-3 bg-primary-200 rounded overflow-hidden position-relative text-white mb-g">                           <div>
                                <h5 class="display-4 d-block l-h-n m-0 fw-500">
                                    {formatCurrency(Transaction::totalAmount('Income',0,'last_30_days',$all_data),$config['home_currency'])}
                                    <small class="m-0 l-h-n">{$_L['Total Income Last 30 days']}  </small>
                                </h5>
                                <i class="fal fa-credit-card position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size:6rem"></i>


                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-info-200 rounded overflow-hidden position-relative text-white mb-g">                           <div>
                                <h5 class="display-4 d-block l-h-n m-0 fw-500">
                                    {formatCurrency(Transaction::totalAmount('Income',0,'current_week',$all_data),$config['home_currency'])}
                                    <small class="m-0 l-h-n">{$_L['Total Income This Week']}  </small>
                                </h5>
                                <i class="fal fa-globe position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n4" style="font-size: 6rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

{*                <div class="row">*}
{*                    <div class="col">*}
{*                        <h4>{$_L['Last 20 deposit Income']}</h4>*}
{*                    </div>*}
{*                    <div class="col text-end">*}
{*                        <a href="{$base_url}transactions/list/0/income/reports" class="btn btn-primary btn-sm">{$_L['Filter']}</a>*}
{*                    </div>*}
{*                </div>*}
{*                <hr>*}

                <div class="table-responsive">
                    <table class="table table-striped w-100" id="clx_datatable">

                        <thead>
                        <tr>
                            <th>{$_L['Date']}</th>
                            <th>{$_L['Account']}</th>
                            <th>{$_L['Type']}</th>
                            <th>{$_L['Category']}</th>
                            <th>{$_L['Customer']}</th>
                            <th class="text-end">{$_L['Amount']}</th>
                            <th>{$_L['Payer']}</th>

                            <th>{$_L['Description']}</th>
                            <th>{$_L['Item']}</th>

                            <th class="text-end">{$_L['Cr']}</th>
                        </tr>
                        </thead>


                        {foreach $d as $ds}
                            <tr>
                                <td>
                                    <a class="fw-bold" href="{$base_url}transactions/manage/{$ds['id']}">
                                        {date( $config['df'], strtotime($ds['date']))}
                                    </a>
                                </td>
                                <td>{$ds['account']}</td>
                                <td>{ib_lan_get_line($ds['type'])}</td>
                                <td>{if $ds['category'] == 'Uncategorized'}{$_L['Uncategorized']} {else} {$ds['category']} {/if}</td>
                                <td>
                                    {if !empty($ds['payerid']) && !empty($contacts[$ds['payerid']])}
                                        <a href="{$base_url}contacts/view/{$ds['payerid']}">{$contacts[$ds['payerid']]['account']}</a>
                                    {/if}
                                </td>
                                <td class="text-end">{$config['currency_code']} {number_format($ds['amount'],2,$config['dec_point'],$config['thousands_sep'])}</td>
                                <td>{$ds['payer']}</td>



                                <td>{$ds['description']}</td>

                                <td>
                                    {if !empty($items[$ds['item_id']])}
                                        {$items[$ds['item_id']]['name']}
                                    {/if}
                                </td>

                                <td class="text-end">{$config['currency_code']} {number_format($ds['cr'],2,$config['dec_point'],$config['thousands_sep'])}</td>


                            </tr>
                        {/foreach}



                    </table>

                </div>



                <div id="income_by_month"></div>
                <hr>

                <h4>{$_L['Income by Category']}</h4>

                <div id="category_pie_chart"></div>

            </div>

        </div>
    </div>

{/block}

{block name=script}

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>

    <script>
        $(function () {

            $('.amount').autoNumeric('init', {


                dGroup: 3,
                aPad: true,
                pSign: 'p',
                aDec: '{$config['dec_point']}',
                aSep: '{$config['thousands_sep']}',
                vMax: '9999999999999999.00',
                vMin: '-9999999999999999.00'

            });


           // var myChart = echarts.init(document.getElementById('placeholder'));

            // specify chart configuration item and data


            var income_by_months = {
                chart: { height: 500, type: "area", zoom: { enabled: !1 } },
                colors: ["#5A8DEE", "#FDAC41", "#FF5B5C", "#39DA8A", "#00CFDD"],
                dataLabels: { enabled: !1 },
                stroke: { curve: "straight" },
                series: [{ name: "{$_L['Income']}", data: [{foreach $m_data as $d}
                        {$d['value']},
                        {/foreach}] }],
                title: { text: "{$_L['Reports by Month']}", align: "left"},
                grid: { row: { colors: ["#f3f3f3", "transparent"], opacity: .5 } },
                xaxis: { categories: [
                        {foreach $m_data as $d}
                        "{$d['month']}",
                        {/foreach}
                    ] },
                yaxis: { tickAmount: 5 }
            };

            new ApexCharts(document.querySelector("#income_by_month"), income_by_months).render();


            var category_pie_chart_options = {
                chart: { type: "donut", height: 500 },
                colors: ["#5A8DEE", "#FDAC41", "#FF5B5C", "#39DA8A", "#00CFDD"],
                labels: [
                    {foreach $cat_data as $d}
                    '{$d['category']}',
                    {/foreach}
                ],
                series: [
                    {foreach $cat_data as $d}
                    {$d['value']},
                    {/foreach}
                ],
                legend: { itemMargin: { horizontal: 2 } },
                responsive: [ { breakpoint: 576, options: { chart: { width: 300 }, legend: { position: "bottom" } } }]
            };

            new ApexCharts(document.querySelector("#category_pie_chart"), category_pie_chart_options).render();

            $('#clx_datatable').dataTable(
                {
                    responsive: true,
                    lengthChange: false,
                    dom: "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'lB>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            titleAttr: 'Generate PDF',
                            className: 'btn-danger-light btn-sm mr-1'
                        },
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            titleAttr: 'Generate Excel',
                            className: 'btn-success-light btn-sm mr-1'
                        },
                        {
                            extend: 'csvHtml5',
                            text: 'CSV',
                            titleAttr: 'Generate CSV',
                            className: 'btn-primary-light btn-sm mr-1'
                        },
                        {
                            extend: 'copyHtml5',
                            text: 'Copy',
                            titleAttr: 'Copy to clipboard',
                            className: 'btn-info-light btn-sm mr-1'
                        },
                        {
                            extend: 'print',
                            text: 'Print',
                            titleAttr: 'Print Table',
                            className: 'btn-secondary-light btn-sm'
                        }
                    ],
                    "language": {
                        "emptyTable": "{$_L['No items to display']}",
                        "info":      "{$_L['Showing _START_ to _END_ of _TOTAL_ entries']}",
                        "infoEmpty":      "{$_L['Showing 0 to 0 of 0 entries']}",
                        buttons: {
                            pageLength: '{$_L['Show all']}'
                        },
                        searchPlaceholder: "{__('Search')}"
                    },
                }
            );


        });
    </script>


{/block}
