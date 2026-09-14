{extends file="$layouts_admin"}

{block name="head"}

{/block}



{block name="content"}

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{$base_url}settings/save-invoice-group">
                        <div class="mb-3">
                            <label for="input_name">{__('Name')}</label>
                            <input type="text" name="name" id="input_name" class="form-control" value="{$selected_group->name|default:''}" required>
                        </div>

                        {if isset($selected_group->id)}
                            <input type="hidden" name="id" value="{$selected_group->id}">
                        {/if}

                        <button type="submit" class="btn btn-primary">{__('Save')}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="thead-light">
                        <table class="table table-striped">
                            <th><strong>{$_L['Group']}</strong></th>
                            <th><strong>{__('Invoices')}</strong></th>
                            <th class="text-end">{$_L['Manage']}</th>
                            {foreach $groups as $group}
                                <tr>
                                    <td><strong>{$group->name}</strong></td>

                                    <td>
                                        <strong>{Invoice::where('group_id',$group->id)->count()}</strong>
                                    </td>

                                    <td>
                                        <div class="float-end">

                                            <a href="{$base_url}invoices/list/{$group->id}" class="btn btn-sm btn-info"> {$_L['Invoices']}</a>

                                            <a href="{$base_url}settings/invoice-groups/{$group->id}" class="btn btn-sm btn-primary"> {$_L['Edit']}</a>

                                            <a href="javascript:;" onclick="confirmThenGoToUrl(event,'settings/delete-invoice-group/{$group->id}');" class="btn btn-sm btn-danger">{$_L['Delete']}</a>

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

{/block}

{block name="script"}

    <script>
        window.addEventListener('DOMContentLoaded', () => {

        });
    </script>

{/block}


