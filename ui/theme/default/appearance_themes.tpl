{extends file="$layouts_admin"}

{block name="content"}
    <div class="row">
        <div class="col-md-6">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{$_L['Themes']}</h2>

                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <form role="form" method="post" action="{$_url}appearance/themes_post">


                            <div class="mb-3">
                                <label for="theme">{$_L['Theme']}</label>
                                <select name="theme" id="theme" class="form-select">

                                    {foreach $themes|default:array() as $theme}
                                        <option value="{$theme}"
                                                {if $config['theme'] eq $theme}selected="selected" {/if}>{ucfirst($theme)}</option>
                                    {/foreach}

                                </select>
                            </div>



                            <h3>{__('Frontend ')}</h3>

                            <div class="mb-3">
                                <label for="frontend_theme">{__('Theme')}</label>
                                <select name="frontend_theme" id="frontend_theme" class="form-select">
                                    <option value="">{__('Default')}</option>
                                    {foreach $frontend_themes|default:array() as $frontend_theme}
                                        <option value="{$frontend_theme}"
                                                {if !empty($config['frontend_theme']) and $config['frontend_theme'] eq $frontend_theme}selected="selected" {/if}>{ucfirst($frontend_theme)}</option>
                                    {/foreach}

                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="frontend_primary_color">{__('Primary Color')}</label>
                                <input type="color" id="frontend_primary_color" name="frontend_primary_color" value="{$config['frontend_primary_color']|default:'#007bff'}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="frontend_link_color">{__('Link Color')}</label>
                                <input type="color" id="frontend_link_color" name="frontend_link_color" value="{$config['frontend_link_color']|default:'#007bff'}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="frontend_primary_button_color">{__('Primary Button Color')}</label>
                                <input type="color" id="frontend_primary_button_color" name="frontend_primary_button_color" value="{$config['frontend_primary_button_color']|default:'#007bff'}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="frontend_primary_button_hover_color">{__('Primary Button Hover Color')}</label>
                                <input type="color" id="frontend_primary_button_hover_color" name="frontend_primary_button_hover_color" value="{$config['frontend_primary_button_hover_color']|default:'#0069d9'}" class="form-control">
                            </div>



                            <input type="hidden" name="nstyle" value="dark">

                            <button type="submit" class="btn btn-primary"> {$_L['Submit']}</button>
                        </form>
                    </div>



                </div>
            </div>










        </div>




    </div>
{/block}
