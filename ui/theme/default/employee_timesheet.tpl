{extends file="$layouts_admin"}

{block name="head"}

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />

{/block}

{block name="content"}
    <div class="row">

        <div class="col-md-3">
            <div class="card">
                <div class="card-body p-3">
                    <h5>{__('Filter')}</h5>

                    <div class="mb-3">
                        <label for="select_employee_id">{__('Employee')}</label>
                        <select class="form-select" id="select_employee_id" name="employee_id">
                            <option value="0">{__('All')}</option>
                            {foreach $employees as $employee}
                                <option value="{$employee->id}" {if $employee->id == $selected_employee_id}selected{/if}>{$employee->name}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reportrange">{$_L['Date Range']}</label>
                        <input type="text" name="reportrange" class="form-control" id="reportrange">
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="panel">

                <div class="panel-hdr">

                    <h2>{__('Time entries')}</h2>

                    <div class="panel-toolbar">

                        <button id="btn_add_time_entry" class="btn btn-sm btn-success"> {__('Add Time entry')}</button>

                    </div>
                </div>


                <div class="panel-container">


                    <div class="panel-content">
                        <div class="table-responsive" id="ib_data_panel">


                            <table class="table w-100"  id="clx_datatable">
                                <thead
                                        {if empty($config['admin_dark_theme'])}
                                            style="background: #f0f2ff"

                                        {/if}
                                >
                                <tr class="heading">
                                    <th>{__('Employee')}</th>

                                    <th>{__('Date')}</th>

                                    <th>{__('Total Hours')}</th>


                                    <th>{__('Description')}</th>

                                    <th class="text-end" style="width: 80px;">{$_L['Manage']}</th>
                                </tr>


                                </thead>


                                <tbody>

                                {foreach $time_entries as $time_entry}
                                    <tr>
                                        <td>
                                            {if !empty($users[$time_entry->employee_id])}
                                                {$users[$time_entry->employee_id]->fullname}
                                            {/if}
                                        </td>
                                        <td>
                                            {if !empty($time_entry->date)}
                                                {date( $config['df'], strtotime($time_entry->date))}
                                            {/if}
                                        </td>
                                        <td>
                                            {if !empty($time_entry->duration)}
                                                {appHumanReadableDuration($time_entry->duration)}
                                            {/if}
                                        </td>
                                        <td>
                                            {if !empty($time_entry->description)}
                                                {$time_entry->description}
                                            {/if}
                                        </td>
                                        <td></td>
                                    </tr>
                                {/foreach}


                                </tbody>


                            </table>
                        </div>
                    </div>


                </div>
            </div>
        </div>

    </div>
{/block}

{block name="script"}

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>


    <script>
        $(function() {

            var start = '{$start_date}';
            var end = '{$end_date}';


            var $reportrange = $("#reportrange");

            $reportrange.daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    '{__('Today')}': [moment(), moment()],
                    '{__('Yesterday')}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '{__('Last 7 Days')}': [moment().subtract(6, 'days'), moment()],
                    '{__('Last 30 Days')}': [moment().subtract(29, 'days'), moment()],
                    '{__('This Month')}': [moment().startOf('month'), moment().endOf('month')],
                    '{__('Last Month')}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: 'YYYY/MM/DD'
                }
            });

            $('#clx_datatable').dataTable(
                {
                    responsive: true,
                    lengthChange: false,
                    dom:
                        "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'lB>>" +
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



            const select_employee_id = document.getElementById('select_employee_id');

            if(select_employee_id)
            {
                select_employee_id.addEventListener('change', function() {
                    let employee_id = this.value;
                    window.location.href = "{$base_url}hrm/timesheet/" + employee_id;
                });
            }

            let report_range = document.getElementById('reportrange');



            report_range.addEventListener('change', function() {
                console.log(report_range.value);
                window.location.href = "{$base_url}hrm/timesheet/" + select_employee_id.value + "/" + report_range.value;
            });

            $reportrange.on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate;
                var endDate = picker.endDate;

                window.location.href = "{$base_url}hrm/timesheet/" + select_employee_id.value + "/" + startDate.format('YYYY-MM-DD') + '*' + endDate.format('YYYY-MM-DD');

            });


            const btn_add_time_entry = document.getElementById('btn_add_time_entry');

            btn_add_time_entry.addEventListener('click', function() {
                $.fancybox.open({
                    src  : base_url + 'contacts/modal_add/',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {

                        }
                    }
                });
            });


        });
    </script>
{/block}


