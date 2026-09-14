{extends file="$layouts_admin"}

{block name="content"}
    <div class="row">



        <div class="col-md-12">

            <div class="panel">

                <div class="panel-hdr">

                    <div class="panel-toolbar">
                        <a href="{$_url}contacts/group_email/{$gid}/" id="send_group_email" class="btn btn-primary mb-md"><i class="fal fa-paper-plane"></i> {$_L['Send Email']}</a>
                    </div>

                </div>

                <div class="panel-container">
                    <div class="panel-content">
                        <div class="table-responsive">
                            <div class="thead-light">
                                <table class="table table-striped table-bordered table-hover sys_table" id="clx_datatable">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{$_L['Name']}</th>
                                        <th>{$_L['Company Name']}</th>
                                        <th>{$_L['Email']}</th>
                                        <th>{$_L['Phone']}</th>
                                        <th class="text-end">{$_L['Manage']}</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    {foreach $d as $ds}

                                        <tr>

                                            <td><a href="{$_url}contacts/view/{$ds['id']}/">{$ds['id']}</a> </td>

                                            <td><a href="{$_url}contacts/view/{$ds['id']}/">{$ds['account']}</a> </td>
                                            <td>{$ds['company']}</td>

                                            <td>
                                                {$ds['email']}

                                            </td>
                                            <td>
                                                {$ds['phone']}
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="{$_url}contacts/view/{$ds['id']}/" class="btn btn-primary btn-xs"> {$_L['View']}</a>
                                                </div>

                                            </td>
                                        </tr>

                                    {/foreach}

                                    </tbody>
                                </table>
                            </div>


                        </div>

                    </div>



                    <br>



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

            $('.has-tooltip').tooltip();



        });
    </script>
{/block}
