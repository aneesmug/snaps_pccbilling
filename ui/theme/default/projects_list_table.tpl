{extends file="$layouts_admin"}

{block name="head"}

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />

{/block}

{block name="content"}

    <div class="subheader">
        <h1 class="subheader-title">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <rect x="0" y="0" width="24" height="24"/>
                    <path d="M8.40093151,17 L5,17 C3.34314575,17 2,15.1568542 2,13.5 C2,11.8431458 3.34314575,10 5,10 L6,10 L6,8 L21,8 L21,11.5 C21,15.6421356 17.6421356,19 13.5,19 C11.5309185,19 9.73907026,18.2411745 8.40093151,17 Z M6.86504659,15 C6.38614142,14.0940164 6.08736465,13.0781211 6.01640228,12 L5,12 C4.44771525,12 4,12.9477153 4,13.5 C4,14.0522847 4.44771525,15 5,15 L6.86504659,15 Z" fill="#000000"/>
                    <rect fill="#000000" opacity="0.3" x="6" y="21" width="15" height="2" rx="1"/>
                    <path d="M8.11576273,0 L9.27322553,1.15267194 C8.41777098,2.01168201 8.42065331,3.40153019 9.27966338,4.25698473 C9.35322262,4.3302395 9.4318859,4.39818368 9.51506091,4.46030566 L10,4.82249831 L9.02250371,6.13126634 L8.53756462,5.76907368 C8.39249331,5.66072242 8.25529121,5.54221626 8.12699144,5.41444753 C6.62873232,3.92238985 6.62370505,1.49825912 8.11576273,0 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                    <path d="M13.1157627,1 L14.2732255,2.15267194 C13.417771,3.01168201 13.4206533,4.40153019 14.2796634,5.25698473 C14.3532226,5.3302395 14.4318859,5.39818368 14.5150609,5.46030566 L15,5.82249831 L14.0225037,7.13126634 L13.5375646,6.76907368 C13.3924933,6.66072242 13.2552912,6.54221626 13.1269914,6.41444753 C11.6287323,4.92238985 11.6237051,2.49825912 13.1157627,1 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                    <path d="M18.1157627,0 L19.2732255,1.15267194 C18.417771,2.01168201 18.4206533,3.40153019 19.2796634,4.25698473 C19.3532226,4.3302395 19.4318859,4.39818368 19.5150609,4.46030566 L20,4.82249831 L19.0225037,6.13126634 L18.5375646,5.76907368 C18.3924933,5.66072242 18.2552912,5.54221626 18.1269914,5.41444753 C16.6287323,3.92238985 16.6237051,1.49825912 18.1157627,0 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                </g>
            </svg>

            {{__('Projects')}} <span class='fw-300'></span>
        </h1>
        <div class="subheader-block d-lg-flex align-items-center">
            <a href="{$_url}projects/project" class="btn btn-primary">{{__('Create New Project')}}</a>
        </div>

    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="row">

                <div class="panel-container ">
                    <div class=" card card-body table-responsive">
                        <table class="table" id="clx_datatable">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col" class="h5">{{__('Name')}}</th>
                                <th scope="col" class="h5">{{__('Customer')}}</th>
                                <th scope="col" class="h5">{{__('Budget')}}</th>
                                <th scope="col" class="h5">{{__('Startdate')}}</th>
                                <th scope="col" class="h5">{{__('Deadline')}}</th>
                                <th scope="col" class="h5">{{__('Members')}}</th>
                                <th scope="col" class="h5">{{__('Status')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $projects as $project}

                                <tr>
                                    <th scope="row" class="h5">
                                        <a href="{$_url}projects/view/{$project->id}">{$project->name}</a>
                                    </th>
                                    <td class="h5">
                                        {if $project->contact_id && isset($contacts[$project->contact_id])}
                                            {$contacts[$project->contact_id]->account}
                                        {/if}
                                    </td>
                                    <td class="h5">
                                        {if $project->budget}
                                            {formatCurrency($project->budget,$project->currency)}
                                        {/if}
                                    </td>
                                    <td class="h5 text-success">{date( $config['df'], strtotime($project->start_date))}</td>
                                    <td class="h5 text-danger">
                                        {date( $config['df'], strtotime($project->due_date))}</td>
                                    <td>
                                        {if $project->members}
                                            <div class="fs-sm d-flex align-items-center my-3">
                                                {foreach json_decode($project->members) as $member}
                                                    {if isset($staffs[$member])}
                                                        <a href="javascript:;" class="btn-m-s user-stacked">

                                                            {if $staffs[$member]->img}
                                                                <img src="{{APP_URL}}/{{$staffs[$member]->img}}" class="profile-image rounded-circle" alt="{{$staffs[$member]->fullname}}">
                                                            {else}
                                                                <span class="clx-avatar">{sp_get_contact_image($staffs[$member])}</span>
                                                            {/if}

                                                        </a>
                                                    {/if}
                                                {/foreach}
                                            </div>
                                        {/if}
                                    </td>
                                    <td>{if $project->status == 'Completed'}
                                            <span class="badge badge-outline text-uppercase badge-outline-success mb-4">{$project->status}</span>
                                        {elseif $project->status == 'Started'}
                                            <span class="badge badge-outline text-uppercase badge-outline-info mb-4">{$project->status}</span>
                                            {else}
                                            <span class="badge badge-outline text-uppercase badge-outline-danger mb-4">{$project->status}</span>

                                        {/if}
                                    </td>
                                </tr>

                            {/foreach}

                            </tbody>
                        </table>
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
