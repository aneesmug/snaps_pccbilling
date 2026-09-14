{extends file="$layouts_admin"}

{block name="content"}
    <div class="row">

        <div class="col-md-12">

            <div class="panel">
                <div class="panel-hdr">
                    <h2>{$account} {$_L['Statement']} [{date( $config['df'], strtotime($fdate))} - {date( $config['df'], strtotime($tdate))}]</h2>
                    <div class="panel-toolbar">
                        <form class="form-horizontal me-3" method="post" action="{$_url}export/printable" target="_blank">
                            <input type="hidden" name="fdate" value="{$fdate}">
                            <input type="hidden" name="tdate" value="{$tdate}">
                            <input type="hidden" name="stype" value="{$stype}">
                            <input type="hidden" name="account" value="{$account}">
                            <button type="submit" class="btn btn-primary"> {$_L['Export for Print']}</button>

                        </form>
                        <form class="form-horizontal" method="post" action="{$_url}export/pdf">
                            <input type="hidden" name="fdate" value="{$fdate}">
                            <input type="hidden" name="tdate" value="{$tdate}">
                            <input type="hidden" name="stype" value="{$stype}">
                            <input type="hidden" name="account" value="{$account}">
                            <button type="submit" class="btn btn-primary"> {$_L['Export to PDF']}</button>
                        </form>
                    </div>

                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <table class="table table-striped table-bordered sys_table" id="clx_datatable">

                            <thead>
                            <tr>
                                <th>{$_L['Date']}</th>
                                <th class="h6">{$_L['Description']}</th>
                                <th class="text-end">{$_L['Dr']}</th>
                                <th class="text-end">{$_L['Cr']}</th>
                            </tr>
                            </thead>

                            <tbody>
                            {foreach $d as $ds}
                                <tr>
                                    <td>{date( $config['df'], strtotime($ds['date']))}</td>
                                    <td class="h6">{$ds['description']}</td>


                                    <td class="text-end h6 text-danger">{$config['currency_code']} {number_format($ds['dr'],2,$config['dec_point'],$config['thousands_sep'])}</td>
                                    <td class="text-end h6 text-success">{$config['currency_code']} {number_format($ds['cr'],2,$config['dec_point'],$config['thousands_sep'])}</td>

                                </tr>
                            {/foreach}
                            </tbody>




                        </table>
                        <div class="row">
                            <div>
                                &nbsp;
                            </div>
                            <div class="col-md-6" style="text-align: right"> </div>
                            <div class="col-md-6" style="text-align: right"> </div>
                        </div>
                    </div>



                </div>
            </div>
        </div>



        <!-- Widget-2 end-->
    </div>
    <!-- Row end-->


    <!-- Row end-->


    <!-- Row end-->
{/block}

{block name="script"}

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>


    <script>
        $(function() {

            $('#clx_datatable').dataTable(
                {
                    responsive: true,
                    lengthChange: false,
                    dom:
                    /*	--- Layout Structure
                        --- Options
                        l	-	length changing input control
                        f	-	filtering input
                        t	-	The table!
                        i	-	Table information summary
                        p	-	pagination control
                        r	-	processing display element
                        B	-	buttons
                        R	-	ColReorder
                        S	-	Select

                        --- Markup
                        < and >				- div element
                        <"class" and >		- div with a class
                        <"#id" and >		- div with an ID
                        <"#id.class" and >	- div with an ID and a class

                        --- Further reading
                        https://datatables.net/reference/option/dom
                        --------------------------------------
                     */
                        "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'lB>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        /*{
                        	extend:    'colvis',
                        	text:      'Column Visibility',
                        	titleAttr: 'Col visibility',
                        	className: 'mr-sm-3'
                        },*/
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
