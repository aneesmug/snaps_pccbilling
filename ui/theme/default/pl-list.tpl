{extends file="$layouts_admin"}

{block name="content"}

    <div class="row">
        <div class="col-md-12">
            <div class="row">

                <div class="col-md-12">

                    <div class="panel" id="uploading_inside">
                        <div class="panel-container">
                            <div class="panel-content">
                                <form action="{$_url}settings/plugin_upload/" class="dropzone" id="upload_container">

                                    <div class="dz-message">
                                        <h3> <i class="fal fa-cloud-upload"></i>  {$_L['plugin_drop_help']}</h3>
                                        <br />
                                        <span class="note">{$_L['plugin_upload_help']}</span>
                                    </div>

                                </form>
                            </div>

                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-hdr">
                                    <h2>{$_L['Plugins']}</h2>

                                </div>
                                <div class="panel-container">
                                    <div class="panel-content">
                                        <div class="project-list mt-md">
                                            <div id="progressbar">
                                            </div>

                                            <div id="application_ajaxrender">
                                                <table class="table table-striped">
                                                    <tbody>

                                                    {foreach $plugins as $key => $value}
                                                        <tr>
                                                            <td>
                                                                <img class="img-thumbnail" style="max-height: 64px;" src="{$value['icon_url']}">
                                                            </td>
                                                            <td>


                                                                <h5>{$value['name']}</h5>

                                                                {if !empty($value['description'])}

                                                                    <div class="mb-3">
                                                                        {$value['description']}
                                                                    </div>

                                                                {/if}

                                                                {if $value['status'] === 'Active'}
                                                                    <span class="badge badge-success">Active</span>
                                                                {/if}



                                                                <div>

                                                                    <div class="hr-line-dashed"></div>


                                                                    {if !empty($value['buttons'])}

                                                                        {foreach $value['buttons'] as $button}
                                                                            <a href="{$base_url}{$button['link']}"
                                                                                    {if !empty($button['class'])}
                                                                                        class="{$button['class']}"
                                                                                    {else}
                                                                                        class="mr-1 btn btn-primary mb-2"
                                                                                    {/if}


                                                                            >
                                                                                {if !empty($button['icon'])}
                                                                                    <i class="{$button['icon']}"></i>
                                                                                {elseif (!empty($button['text']))}
                                                                                    {$button['text']}
                                                                                {/if}

                                                                            </a>
                                                                        {/foreach}


                                                                    {/if}

                                                                    {if $value['status'] === 'Not Installed'}
                                                                        <a href="{$base_url}settings/plugin_install/{$key}/" class="btn btn-primary me-2 mb-2">Install</a>
                                                                        <a href="{$base_url}settings/plugin_delete/{$key}/" class="btn btn-danger me-2 mb-2">{__('Delete')}</a>
                                                                    {elseif $value['status'] === 'Active'}
                                                                        <a href="{$base_url}settings/plugin_deactivate/{$key}/" class="btn btn-warning me-2 mb-2">Deactivate</a>
                                                                    {elseif $value['status'] === 'Inactive'}
                                                                        <a href="{$base_url}settings/plugin_activate/{$key}/" class="btn btn-success me-2 mb-2">Activate</a>
                                                                        <a href="{$base_url}settings/plugin_uninstall/{$key}/" class="btn btn-danger me-2 mb-2">Uninstall</a>
                                                                    {/if}



                                                                    {if !empty($value['url'])}
                                                                        <a href="{$value['url']}" target="_blank" class="btn btn-primary mb-2">
                                                                            <i class="fal fa-external-link"></i>
                                                                        </a>
                                                                    {/if}

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
                    </div>

                </div>

            </div>
        </div>

{*        <div class="col-md-8">*}

{*            <h2 class="mb-4">Discover Plugins</h2>*}

{*            {if !empty($marketplace_plugins['items'])}*}
{*                {foreach $marketplace_plugins['items'] as $item}*}

{*                    <article class="card border-0 shadow-sm overflow-hidden mb-4">*}
{*                        <div class="row g-0">*}
{*                            <div class="col-sm-4 position-relative bg-position-center bg-repeat-0 bg-size-cover" style="background-image: url({$marketplace_plugins['image_processing_url']}/items/{$item['image']}?tr=h-300); min-height: 15rem;">*}
{*                            </div>*}
{*                            <div class="col-sm-8">*}
{*                                <div class="card-body">*}
{*                                    <div class="d-flex align-items-center mb-3">*}
{*                                        <span class="badge fs-sm bg-success">{$item['price_formatted']}</span>*}
{*                                        <span class="fs-sm text-muted border-start ps-3 ms-3">Plugin</span>*}
{*                                    </div>*}
{*                                    <h3 class="h4">*}
{*                                        {$item['name']}*}
{*                                    </h3>*}

{*                                    <p class="card-text">{$item['sales_short_description']}</p>*}
{*                                    <hr class="my-4">*}
{*                                    <div class="d-flex align-items-center justify-content-between">*}
{*                                        <div>*}

{*                                        </div>*}
{*                                        <div class="d-flex align-items-center">*}

{*                                            <a target="_blank" href="{$marketplace_plugins['base_url']}/{$item['slug']}" class="btn btn-primary">View</a>*}

{*                                        </div>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
{*                    </article>*}

{*                {/foreach}*}
{*            {/if}*}

{*        </div>*}

    </div>

    <input type="hidden" id="_msg_unzipping" value="{$_L['Unzipping']} ...">
    <input type="hidden" id="_msg_are_you_sure" value="{$_L['are_you_sure']}">

{/block}

{block name="script"}

    <script>
        Dropzone.autoDiscover = false;
        $(function() {
            var _url = $("#_url").val();
            var ib_file = new Dropzone("#upload_container",
                {
                    url: _url + "settings/plugin_upload/",
                    maxFiles: 1,
                    acceptedFiles: ".zip"
                }
            );

            //ib_file.on("addedfile", function(file) {
            //
            //});

            ib_file.on("success", function(file) {

                var _msg_unzipping = $('#_msg_unzipping').val();
                $('#uploading_inside').block({
                    message: "<h3>" + _msg_unzipping +"</h3>" ,
                    css: {
                        padding:        0,
                        margin:         0,
                        width:          '30%',
                        top:            '40%',
                        left:           '35%',
                        textAlign:      'center',
                        color:          '#FFFFFF',
                        border:         '0',
                        backgroundColor:'transparent',
                        cursor:         'wait'
                    }
                });
                //   $('#uploading_inside').block({ message: null });

                var _url = $("#_url").val();
                $.post(_url + 'settings/plugin_unzip/', {

                    name: file.name

                })
                    .done(function (data) {

                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    });
            });



            $(".c_uninstall").click(function (e) {
                e.preventDefault();
                var _msg_are_you_sure = $('#_msg_are_you_sure').val();
                var to_url = this.href;
                app.confirm(_msg_are_you_sure, function(result) {
                    if(result == true){
                        window.location = to_url;
                    }
                });



            });



        });
    </script>

{/block}
