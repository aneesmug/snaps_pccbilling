{extends file="$layouts_admin"}
{block name="head"}

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />

    <style>
        {if !empty($config['admin_dark_theme'])}
        .pagination .dp-off {
            background-color: #000!important;
        }
        .pagination .dp-divider {
            border-left: 2px solid #000!important;
        }
        {/if}

    </style>
{/block}

{block name="content"}
    <div class="row">


        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{$_L['Reports by Date']} </h2>

                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="dpx" class="table-responsive"></div>
                        <div id="result" class="mt-3">

                        </div>
                    </div>


                </div>
            </div>
        </div>



        <!-- Widget-2 end-->
    </div>



{/block}

{block name="script"}

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>

    <script>

        function loadForDate(date) {
            $.get( base_url + "ajax.date-summary/" + date, function( data ) {
                $( "#result" ).html( data );

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
        }

        $(function () {

            let date_obj = new Date;
            let timestamp = date_obj.getTime();

            loadForDate(timestamp);

            let $dpx = $('#dpx');

            $dpx.datepaginator(
                {

                    selectedDate: '{date('Y-m-d')}',
                    selectedDateFormat:  'YYYY-MM-DD',
                    textSelected:  "dddd<br/>YYYY-MM-DD"
                }
            );
            $dpx.on('selectedDateChanged', function(event, date) {
                $( "#result" ).html( "<h3>Loading.....</h3>" );
                loadForDate(date);
            });

        });
    </script>
{/block}
