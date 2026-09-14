{extends file="$layouts_admin"}

{block name="content"}

<div class="row">
    <div class="col-md-4">
        <div class="panel">
            <div class="panel-hdr">
                <h3 class="card-title">{__('Upload')}</h3>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <form class="dropzone" id="upload_container">

                        <div class="dz-message">
                            <h3> <i class="fal fa-cloud-upload"></i>  {__('Drop a media file.')}</h3>
                            <br />
                            <span class="note">{__('or, click to upload')}</span>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="panel">
            <div class="panel-hdr">
                <h3 class="card-title">{__('Media')}</h3>
            </div>
            <div class="panel-container">
                <div class="panel-content">
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
                                    <button class="btn btn-primary btn-sm copy_media_url mb-2" data-path="{$media_file->path}">{__('Copy URL')}</button>
                                    <a href="javascript:;" onclick="confirmThenGoToUrl(event,'media/delete/{$media_file->id}')" class="btn btn-sm btn-danger mb-2">{__('Delete')}</a>
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

{/block}
{block name="scripts"}
    <script>
        Dropzone.autoDiscover = false;
        window.addEventListener('DOMContentLoaded', ()=> {

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

            const upload = new Dropzone('#upload_container', {
                url: base_url + 'media/upload',
                maxFiles: 1,
                acceptedFiles: '.jpeg,.jpg,.png,.gif,.svg,.webp,.mp4',
            });

            upload.on("success", function(file) {
                location.reload();
            });

            const copy_media_url = document.querySelectorAll('.copy_media_url');

            copy_media_url.forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    const path = e.target.getAttribute('data-path');
                    const url = '{APP_URL}/storage/media/' + path;
                    app.copyToClipboard(url);
                    toastr.success('{__('Copied')}');
                });
            });

        });
    </script>
{/block}

