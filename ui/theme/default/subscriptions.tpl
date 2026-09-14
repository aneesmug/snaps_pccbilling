{extends file="$layouts_admin"}

{block name="content"}

    <div class="subheader">
        <h1 class="subheader-title">
            <i class='subheader-icon fal fa-window'></i> {$_L['Subscriptions']}

        </h1>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{{__("Subscription List")}}</h2>

                    <div class="panel-toolbar">

                        <div class="btn-group">
                            <a href="{$_url}subscriptions/add" class="btn btn-primary  btn-sm"> {$_L['New Subscription']}</a>
                        </div>

                    </div>
                </div>

                <div class="panel-container">
                    <div class="panel-content">

                        <ul class="nav nav-tabs nav-tabs-clean mb-3" role="tablist">
                            <li class="nav-item"><a class="nav-link {if $tab == '' || $tab == 'active'}active{/if}" href="{$base_url}subscriptions/list">{$_L['Active']}</a></li>
                            <li class="nav-item"><a class="nav-link  {if $tab == 'inactive'}active{/if}" href="{$base_url}subscriptions/list/inactive">{$_L['Inactive']}</a></li>
                            <li class="nav-item"><a class="nav-link {if $tab == 'suspended'}active{/if}" href="{$base_url}subscriptions/list/suspended">{$_L['Suspended']}</a></li>
                            <li class="nav-item"><a class="nav-link {if $tab == 'cancelled'}active{/if}" href="{$base_url}subscriptions/list/cancelled">{$_L['Cancelled']}</a></li>
                            <li class="nav-item"><a class="nav-link {if $tab == 'all'}active{/if}" href="{$base_url}subscriptions/list/all">{$_L['All']}</a></li>
                        </ul>
                        <div class="table-responsive">
                            <table id="clx_datatable" class="table w-100 sys_table footable">
                                <thead style="background: #f0f2ff">
                                <tr>
                                    <th>#</th>
                                    <th>{__('Plan Name')}</th>
                                    <th>{$_L['Customer']}</th>

                                    <th>{{__('Date Subscribed')}}</th>
                                    <th>{__('Next Renewal Date')}</th>
                                    <th>
                                        {$_L['Status']}
                                    </th>
                                    <th>{$_L['Type']}</th>
                                    <th>{__('Quantity')}</th>
                                    <th>{__('Price')}</th>
                                    <th>{__('Total')}</th>
                                    <th class="text-end" width="140px;">{$_L['Manage']}</th>
                                </tr>
                                </thead>
                                <tbody>

                                {foreach $subscriptions as $subscription}

                                    <tr>
                                        <td data-value="{$subscription->id}" data-order="{$subscription@iteration}">{$subscription->id}</td>
                                        <td class="h6 text-info">{if isset($plans[$subscription->plan_id])}
                                                {$plans[$subscription->plan_id]->title}
                                            {/if}</td>

                                        <td class="h6"> {if isset($contacts[$subscription->contact_id])}
                                                {$contacts[$subscription->contact_id]->account}
                                            {/if}
                                        </td>

                                        <td class="text-success h6">{$subscription->start_date}</td>

                                        <td class="text-danger h6">{$subscription->next_renewal_date}</td>

                                        <td>
                                            {if $subscription->status eq 'Active'}
                                                <span class="badge badge-outline badge-outline-success">{$subscription->status}</span>
                                            {elseif $subscription->status eq 'Inactive'}
                                                <span class="badge badge-outline badge-outline-info">{$subscription->status}</span>
                                            {elseif $subscription->status eq 'Suspended'}
                                                <span class="badge badge-outline badge-outline-danger">{$subscription->status}</span>
                                            {elseif $subscription->status eq 'Cancelled'}
                                                <span class="badge badge-outline badge-outline-warning">{$subscription->status}</span>
                                            {/if}
                                        </td>
                                        <td>
                                            <span class="badge badge-outline badge-outline-success">
                                                    {if isset($type_names[$subscription->type])}
                                                        {$type_names[$subscription->type]}
                                                    {else}
                                                        {$subscription->type}
                                                    {/if}
                                            </span>
                                        </td>

                                        <td>{$subscription->quantity}</td>
                                        <td>{formatCurrency($subscription->price, $subscription->currency)}</td>
                                        <td>{formatCurrency($subscription->total, $subscription->currency)}</td>

                                        <td class="text-end">
                                            <div class="">

                                                <a href="{$_url}subscriptions/edit/{$subscription->id}/" class="btn btn-secondary-light btn-icon" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Edit']}"><i class="fal fa-file-edit"></i></a>

                                                <a href="javascript:;" class="btn btn-danger-light btn-icon" onclick="confirmThenGoToUrl(event,'subscriptions/delete-subscription/{$subscription->id}')" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Delete']}"><i class="fal fa-trash-alt"></i></a>

                                            </div>
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
    </div>
{/block}

{block name="script"}



    <script>
        $(function () {

            var $modal = $('#cloudonex_body');

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
                            className: 'btn-success-light btn-sm mr-1'
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


            $modal.on('click', '.cdelete', function(e){

                e.preventDefault();
                var id = this.id;
                app.confirm("Are You Sure?", function(result) {
                    if(result){
                        var _url = $("#_url").val();
                        window.location.href = _url + "delete/invoice/" + id;
                    }
                });


            });





        });
    </script>
{/block}
