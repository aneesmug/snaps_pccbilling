<div>
    <div class="card">
        <div class="card-body">

            <h3>{{__('Media')}}</h3>

            <div class="table-responsive">
                <table class="table table-condensed table-hover table-bordered" id="clx_datatable">
                    <thead>
                    <tr>
                        <th><strong>{__('Media')}</strong></th>
                        <th><strong>{__('Title')}</strong></th>
                        <th class="text-end"><strong>{__('Manage')}</strong></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $media_files as $media_file}
                        <tr>
                            <td data-order="{$media_file@iteration}">
                                {if $media_file->extension == 'png' || $media_file->extension == 'jpg' || $media_file->extension == 'jpeg' || $media_file->extension == 'gif'}
                                    <img src="{BASE_URL_ALT}media/image/{$media_file->path}&h=50" class="img-responsive" style="max-width: 100px; max-height: 100px;" />
                                {else}
                                    <i class="fal fa-file fa-5x"></i>
                                {/if}
                            </td>
                            <td>
                                {$media_file->title}
                            </td>
                            <td class="text-end">
                                <button data-path="media/{$media_file->path}" class="btn btn-primary btn-sm choose_media mb-2" data-path="{$media_file->path}">{__('Choose')}</button>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
