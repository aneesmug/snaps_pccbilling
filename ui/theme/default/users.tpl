{extends file="$layouts_admin"}


{block name="head"}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />
{/block}




{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{$_L['Manage_Users']}</h2>

                    <div class="panel-toolbar">
                        <a href="{$_url}settings/users-add" class="btn btn-primary"> {$_L['Add_New_User']}</a>

                    </div>

                </div>
                <div class="panel-container">
                    <div class="panel-content">

                        <div class="table-responsive">
                            <table class="table table-striped" id="clx_datatable">
                                <thead style="background: #f0f2ff">
                                <tr>
                                    <th style="width: 60px;background: #f0f2ff">{$_L['Avatar']}</th>
                                    <th>{$_L['Details']}</th>
                                    <th>{$_L['Type']}</th>
                                    <th class="text-end">{$_L['Manage']}</th>
                                </tr>
                                </thead>

                                {foreach $d as $ds}
                                    <tr>
                                        <td>{if $ds['img'] eq 'gravatar'}
                                                <img src="http://www.gravatar.com/avatar/{(md5($ds['username']))}?s=60" class="img-circle" alt="{$user['fullname']}">
                                            {elseif $ds['img'] eq ''}
                                                <img src="{$app_url}ui/lib/img/default-user-avatar.png" style="max-height: 60px;" alt="">
                                            {else}
                                                <img src="{$app_url}/{$ds['img']}" class="img-circle" style="max-height: 60px;" alt="{$ds['fullname']}">
                                            {/if}</td>
                                        <td class="h6">
                                            {$ds['fullname']}
                                            <br>  {$ds['username']}
                                            {if $ds['phonenumber'] != ''}
                                                <br> {$ds['phonenumber']}
                                            {/if}
                                            {if $ds['address_line_1'] != ''}
                                                <br> {$ds['address_line_1']}
                                            {/if}
                                            {if $ds['address_line_2'] != ''}
                                                <br> {$ds['address_line_2']}
                                            {/if}
                                            {if $ds['city'] != ''}
                                                <br> {$ds['city']}
                                            {/if}
                                            {if $ds['state'] != ''}
                                                <br> {$ds['state']} - {$ds['zip']}
                                            {/if}
                                            {if $ds['country'] != ''}
                                                <br> {$ds['country']}
                                            {/if}
                                        </td>

                                        <td class="h6 text-info">
                                            {ib_lan_get_line($ds['user_type'])}

                                            {if isset($relations[$ds['id']])}
                                                <hr>
                                                {foreach $relations[$ds['id']] as $relation}

                                                    {if isset($departments[$relation])}
                                                        <span class="label label-success">{$departments[$relation]->dname}</span>
                                                    {/if}

                                                {/foreach}
                                            {/if}


                                        </td>
                                        <td>
                                            <div class="btn-group float-end">
                                                <a href="{$_url}settings/users-edit/{$ds['id']}" class="btn btn-primary btn-sm"><i class="fal fa-pencil"></i> </a>
                                                {if ($user['username']) neq ($ds['username'])}
                                                    <a href="javascript:;" id="{$ds['id']}" class="btn btn-danger btn-sm cdelete"><i class="fal fa-trash-alt"></i> </a>
                                                {/if}

                                            </div>

                                        </td>
                                    </tr>
                                {/foreach}


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
            $('#clx_datatable').dataTable({
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
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.cdelete').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    let id = this.id;
                    app.confirm("{__('are_you_sure')}", function(result) {
                        if(result){
                            window.location.href = base_url + "settings/users-delete/" + id;
                        }
                    });
                });
            });
        });


    </script>

{/block}
