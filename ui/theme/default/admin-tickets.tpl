{extends file="$layouts_admin"}
{block name="head"}

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />

{/block}
{block name="content"}

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{__('Tickets')}}</h2>
        </div>
        <div class="card-body">

            <table class="table table-striped"  id="clx_datatable">
                <thead class="thead-light">
                <tr>
                    <th scope="col">&nbsp;</th>
                    <th scope="col">{{__('Customer')}}</th>
                    <th scope="col">{{__('Subject')}}</th>
                    <th scope="col">{{__('Status')}}</th>
                    <th scope="col">{{__('Created')}}</th>
                    <th scope="col">{{__('Updated')}}</th>

                </tr>
                </thead>
                <tbody>
                {foreach $tickets as $ticket}
                    <tr>

                        <td>
                            {if !empty($contacts[$ticket->userid])}
                                <a href="{{$base_url}}tickets/admin/view/{{$ticket->id}}">{sp_get_contact_image($contacts[$ticket->userid])}</a>
                            {/if}
                        </td>
                        <td class="h6">
                            <a href="{{$base_url}}tickets/admin/view/{{$ticket->id}}">{$ticket->account}</a>
                        </td>
                        <td class="h6 text-info">
                            <a href="{{$base_url}}tickets/admin/view/{{$ticket->id}}">{$ticket->subject}</a>
                        </td>
                        <td>
                            {if $ticket->status eq 'Open'}
                                <span class="badge bg-secondary">{$_L['Open']}</span>

                            {elseif $ticket->status eq 'On Hold'}
                                <span class="badge bg-danger">{$_L['On Hold']}</span>
                            {elseif $ticket->status eq 'Escalated'}
                                <span class="badge bg-primary">{$_L['Escalated']}</span>
                            {else}
                                <span class="badge bg-success">{$_L['Closed']}</span>
                            {/if}
                        </td>
                        <td>{date( 'D M d, h:i A', strtotime($ticket->created_at))}</td>
                        <td>{date( 'D M d, h:i A', strtotime($ticket->updated_at))}</td>

                    </tr>
                {/foreach}


                </tbody>
            </table>



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
