{extends file="$layouts_admin"}

{block name="content"}

    <div class="panel">
        <div class="panel-hdr">
            <h3 class="card-title">{__('Pages')}</h3>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                        <tr>
                            <th>{__('Page')}</th>
                            <th class="text-end">{__('Manage')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                            <td class="fw-bold">
                                {if !empty($home_page)}
                                    <a href="{$base_url}cms/post/{$home_page->id}">{$home_page->title}</a>
                                    {else}
                                    <a href="{$base_url}cms/create-post/home">{__('Create Home Page')}</a>
                                {/if}

                            </td>

                            <td class="text-end">
                                {if !empty($home_page)}
                                    <a class="btn btn-sm btn-dark" href="{$base_url}cms/post/{$home_page->id}">{__('Launch Builder')}</a>
                                {/if}
                            </td>

                        </tr>
                        <tr>

                            <td class="fw-bold">
                                {if !empty($terms_and_conditions_page)}
                                    <a href="{$base_url}cms/post/{$terms_and_conditions_page->id}">{$terms_and_conditions_page->title}</a>
                                {else}
                                    <a href="{$base_url}cms/create-post/terms_and_conditions">{__('Create Terms and Conditions Page')}</a>
                                {/if}

                            </td>
                            <td class="text-end">
                                {if !empty($terms_and_conditions_page)}
                                    <a class="btn btn-sm btn-dark" href="{$base_url}cms/post/{$terms_and_conditions_page->id}">{__('Launch Builder')}</a>
                                {/if}
                            </td>
                        </tr>
                        <tr>

                            <td class="fw-bold">
                                {if !empty($privacy_policy_page)}
                                    <a href="{$base_url}cms/post/{$privacy_policy_page->id}">{$privacy_policy_page->title}</a>
                                {else}
                                    <a href="{$base_url}cms/create-post/privacy_policy">{__('Create Privacy Policy Page')}</a>
                                {/if}

                            </td>
                            <td class="text-end">
                                {if !empty($privacy_policy_page)}
                                    <a class="btn btn-sm btn-dark" href="{$base_url}cms/post/{$privacy_policy_page->id}">{__('Launch Builder')}</a>
                                {/if}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

{/block}

