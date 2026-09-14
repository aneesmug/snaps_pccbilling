{extends file="$layouts_admin"}


{block name="content"}

    <div class="panel">
        <div class="panel-hdr">
            <h2>{$_L['Web to Lead']}</h2>
            <div class="panel-toolbar">
                <a href="{$_url}leads/form" class="btn btn-primary">{$_L['New Form']}</a>
            </div>
        </div>
        <div class="panel-container">
            <div class="panel-content">

                <table class="table table-striped w-100" data-order="[[ 0, &quot;desc&quot; ]]" id="clx_datatable">
                    <thead>
                    <tr class="heading">
                        <th>#</th>
                        <th class="h6">{$_L['Form Name']}</th>
                        <th class="h6">{$_L['Total Submissions']}</th>
                        <th class="h6">{$_L['Created']}</th>
                        <th class="float-right h6">{$_L['Manage']}</th>
                    </tr>


                    </thead>


                    <tbody>
                    {foreach $lead_forms as $lead_form}
                        <tr>
                            <td>
                                {$lead_form->id}
                            </td>
                            <td>
                                <a class="h6" href="{$_url}leads/form/{$lead_form->id}">{$lead_form->name}</a>
                            </td>
                            <td>
                                {if isset($leads_count[$lead_form->id])}
                                    {{$leads_count[$lead_form->id]}}
                                    {else}
                                    0
                                {/if}
                            </td>
                            <td>
                                {date( $config['df'], strtotime($lead_form->created_at))}
                            </td>

                            <td>
                                <div class="btn-group float-end">

                                    <a class="btn btn-primary" href="{$_url}leads/form-builder/{$lead_form->id}/">{$_L['Form Builder']}</a>
                                    <a class="btn btn-success" href="{$_url}leads/form-embed/{$lead_form->id}/">{$_L['Embed']}</a>
                                    <a href="{$_url}leads/form/{$lead_form->id}/" data-bs-toggle="tooltip" class="btn btn-warning btn-icon waves-effect waves-themed has-tooltip" data-title="{$_L['Edit']}" data-placement="top"><i class="fal fa-pencil"></i></i> </a>

                                    <a target="_blank" href="{$_url}client/form/{$lead_form->uuid}/" data-bs-toggle="tooltip" class="btn btn-info btn-icon waves-effect waves-themed has-tooltip" data-title="{$_L['View']}" data-placement="top"><i class="fal fa-file"></i> </a>


                                    <a class="btn btn-danger btn-icon waves-effect waves-themed has-tooltip confirm_delete" data-bs-toggle="tooltip" data-title="{$_L['Delete']}" id="delete_{$lead_form->id}" data-placement="top" href="#"><i class="fal fa-trash-alt"></i></a>
                                </div>
                            </td>

                        </tr>
                    {/foreach}
                    </tbody>


                </table>

            </div>
        </div>
    </div>

{/block}

{block name=script}

    <script>

        $(function () {
            $('#clx_datatable').dataTable(
                {
                    responsive: true,
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

            let $cloudonex_body = $('#cloudonex_body');

            $cloudonex_body.on('click', '.confirm_delete', function(e){

                e.preventDefault();
                let id = this.id;
                app.confirm("{$_L['are_you_sure']}", function(result) {
                    if(result){
                        window.location.href = base_url + "leads/delete-web-to-lead-form/" + id;
                    }
                });


            });



        });

    </script>


{/block}
