{extends file="$layouts_admin"}

{block name="content"}

    <div class="row">
        <div class="col-md-6">
            <div class="panel" id="ib_box">
                <div class="panel-hdr">
                    <h2>{__('About')}</h2>
                </div>

                <div class="panel-container">
                    <div class="panel-content">

                        <div class="mb-2">
                            <strong>{__('Your version')}:</strong> {$installed_version}
                        </div>

                        {if $latest_version}

                        <div class="mb-2">
                            <strong>{__('Latest version')}:</strong> {$latest_version}
                        </div>



                            {if $update_available}

                                <div class="alert alert-danger mb-3">
                                    {__('An update is available')}
                                </div>

                                {if empty($config['purchase_key'])}
                                    <div class="alert alert-warning mb-3">
                                        {__('Please enter your purchase key to update')}
                                    </div>
                                    <form role="form" name="accadd" method="post" action="{$_url}settings/activate_license_post/">


                                        <div class="mb-3">

                                            <label>{__('License key')}</label>

                                            <input type="text" required class="form-control" id="purchase_key" name="purchase_key">

                                        </div>

                                        <div class="mb-3">
                                            <button type="submit" id="btn_save" class="btn btn-primary"><i class="fal fa-check"></i> {$_L['Save']}</button>
                                        </div>

                                    </form>
                                    {else}

                                    <p>
                                        <strong>{__('Purchase key')}:</strong> {$config['purchase_key']} <a href="{$_url}settings/remove-purchase-key">{__('Remove')}</a>
                                    </p>

                                    {if !empty($config['update_log'])}
                                        <div class="mb-3">
                                            <a href="{$_url}settings/clear-update-log">{__('Clear log')}</a>
                                        </div>
                                        <div class="mb-3">

                                            <pre>{$config['update_log']}</pre>

                                        </div>
                                    {/if}

                                    {if $update_step == '0'}
                                        <a href="{$_url}settings/update" class="cls_update btn btn-primary">{__('Download the latest version')}</a>
                                    {elseif $update_step == '1'}
                                        <div class="alert alert-success mb-3">
                                            {__('Latest version is downloaded, now unzip the file')}
                                        </div>
                                        <a href="{$_url}settings/update" class="cls_update btn btn-primary">{__('Unzip')}</a>
                                        <a href="{$_url}settings/update-cancel" class="btn btn-danger">{__('Cancel')}</a>
                                    {elseif $update_step == '2'}
                                        <div class="alert alert-success mb-3">
                                            {__('Run the database schema update')}
                                        </div>
                                        <a href="{$_url}settings/update" class="cls_update btn btn-primary">{__('Run')}</a>
                                    {elseif $update_step == '3'}
                                        <div class="alert alert-success mb-3">
                                            {__('Complete the update and cleanup the files and log.')}
                                        </div>
                                        <a href="{$_url}settings/update" class="cls_update btn btn-primary">{__('Complete')}</a>
                                    {/if}
                                {/if}



                                {/if}


                        {/if}







                    </div>
                </div>

            </div>

            {if $app_stage eq 'Demo'}

                <input type="hidden" name="purchase_code" id="purchase_code" value="">

            {else}


            {/if}



        </div>





    </div>

{/block}

{block name="script"}

{/block}
