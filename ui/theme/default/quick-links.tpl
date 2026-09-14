{extends file="$layouts_admin"}

{block name="content"}
    {assign var="quick_links" value=[]}
    {if !empty($user.preferences.quick_links)}
        {assign var="quick_links" value=$user.preferences.quick_links|json_decode:true}
    {/if}

    <div class="row">
        <div class="col-md-7">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{__('Quick Links')}</h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        {if !empty($quick_links)}
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>{__('Name')}</th>
                                        <th>{__('URL')}</th>
                                        <th>{__('Target')}</th>
                                        <th class="text-center" style="width: 180px;">{__('Action')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $quick_links as $quick_link}
                                        <tr>
                                            <td>{$quick_link.name|escape}</td>
                                            <td>
                                                <a href="{$quick_link.url|escape}" target="_blank" rel="noopener">
                                                    {$quick_link.url|escape}
                                                </a>
                                            </td>
                                            <td>
                                                {if !empty($quick_link.open_new_tab)}
                                                    {__('Open in new tab')}
                                                {else}
                                                    {__('Same tab')}
                                                {/if}
                                            </td>
                                            <td class="text-center">
                                                <a class="btn btn-warning btn-sm"
                                                   href="{$_url}settings/quick-links/{$quick_link.id}">
                                                    <i class="fal fa-edit"></i> {__('Edit')}
                                                </a>
                                                <a class="btn btn-danger btn-sm"
                                                   href="{$_url}settings/delete-quick-link/{$quick_link.id}"
                                                   onclick="return confirm('{__('Are you sure?')}');">
                                                    <i class="fal fa-trash"></i> {__('Delete')}
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            <div class="alert alert-info mb-0">{__('No quick links found.')}</div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{if !empty($selected_quick_link)}{__('Edit Quick Link')}{else}{__('Add Quick Link')}{/if}</h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <form method="post" action="{$_url}settings/save-quick-link">
                            {if !empty($selected_quick_link)}
                                <input type="hidden" name="id" value="{$selected_quick_link->id}">
                            {/if}

                            <div class="mb-3">
                                <label for="quick_link_name" class="form-label">{__('Name')}</label>
                                <input type="text" class="form-control" id="quick_link_name" name="name"
                                       value="{if !empty($selected_quick_link)}{$selected_quick_link->name|escape}{/if}" required>
                            </div>

                            <div class="mb-3">
                                <label for="quick_link_url" class="form-label">{__('URL')}</label>
                                <input type="url" class="form-control" id="quick_link_url" name="url"
                                       value="{if !empty($selected_quick_link)}{$selected_quick_link->url|escape}{/if}" required>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="open_new_tab" name="open_new_tab" value="1"
                                       {if !empty($selected_quick_link) && !empty($selected_quick_link->open_new_tab)}checked{/if}>
                                <label class="form-check-label" for="open_new_tab">
                                    {__('Open in new tab')}
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fal fa-save"></i> {__('Save')}
                            </button>
                            <a href="{$_url}settings/quick-links" class="btn btn-secondary">{__('Reset')}</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}
