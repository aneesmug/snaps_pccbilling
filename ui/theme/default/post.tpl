{extends file="$layouts_admin"}

{block name="content"}

    <div class="panel">
        <div class="panel-hdr">
            <h3 class="card-title">{$post->title}</h3>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <form method="post" id="app_form">
                    <div class="mb-3">
                        <label for="input_title">{__('Title')}</label>
                        <input type="text" class="form-control" id="input_title" name="title" value="{$post->title}">
                    </div>

                    {if $post->is_home_page}
                        <h4>{__('Hero Section')}</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="input_hero_title">{__('Title')}</label>
                                    <input type="text" class="form-control" id="input_hero_title" name="settings[hero_title]" value="{$post_settings['hero_title']|default:''}">
                                </div>
                                <div class="mb-3">
                                    <label for="input_hero_description">{__('Description')}</label>
                                    <textarea class="form-control" id="input_hero_description" name="settings[hero_description]">{$post_settings['hero_description']|default:''}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="input_primary_button_text">{__('Primary Button Text')}</label>
                                            <input type="text" class="form-control" id="input_primary_button_text" name="settings[primary_button_text]" value="{$post_settings['primary_button_text']|default:''}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="input_primary_button_url">{__('Primary Button URL')}</label>
                                            <input type="text" class="form-control" id="input_primary_button_url" name="settings[primary_button_url]" value="{$post_settings['primary_button_url']|default:''}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="input_secondary_button_text">{__('Secondary Button Text')}</label>
                                            <input type="text" class="form-control" id="input_secondary_button_text" name="settings[secondary_button_text]" value="{$post_settings['secondary_button_text']|default:''}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="input_secondary_button_url">{__('Secondary Button URL')}</label>
                                            <input type="text" class="form-control" id="input_secondary_button_url" name="settings[secondary_button_url]" value="{$post_settings['secondary_button_url']|default:''}">
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="input_hero_image">{__('Hero Image')}</label>
                                    <input type="hidden" id="input_hero_image" name="settings[hero_image]" value="{$post_settings['hero_image']|default:''}">
                                    <div id="hero_image_renderer">
                                        {if !empty($post_settings['hero_image'])}
                                            <img src="{APP_URL}/storage/{$post_settings['hero_image']}" class="img-fluid">
                                        {/if}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <button type="button" id="choose_hero_image" class="btn btn-secondary btn-sm">{__('Choose Media')}</button>
                                </div>
                            </div>
                        </div>







                        {else}

                        <div class="mb-3">
                            <label for="content">{__('Content')}</label>
                            <textarea class="form-control" id="content" name="content">{$post->content|default:''}</textarea>
                        </div>

                    {/if}


                    <input type="hidden" name="id" id="post_id" value="{$post->id}">

                    <button type="submit" id="btnSavePost" class="btn btn-primary">{__('Save')}</button>


                </form>
            </div>
        </div>
    </div>

{/block}

{block name="script"}
    <script src="{APP_URL}/ui/lib/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function () {

            const btnSavePost = document.getElementById('btnSavePost');

            {if $post->is_home_page}

            const chooseHeroImage = document.getElementById('choose_hero_image');
            const heroImageRenderer = document.getElementById('hero_image_renderer');
            const heroImageInput = document.getElementById('input_hero_image');

            chooseHeroImage.addEventListener('click', function () {
                $.fancybox.open({
                    src  :  base_url + 'media/choose',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
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
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });
            });


            const $cloudonex_body = $('#cloudonex_body');

            $cloudonex_body.on('click', '.choose_media', function () {
                const media_path = $(this).data('path');
                heroImageInput.value = media_path;
                heroImageRenderer.innerHTML = `<img src="{APP_URL}${ '/storage/' + media_path }" class="img-fluid">`;
                $.fancybox.close();
            });

            btnSavePost.addEventListener('click', function (e) {
                e.preventDefault();

                axios.post('{$base_url}cms/post', new FormData(document.getElementById('app_form')))
                    .then(function (response) {
                        if (response.data.status === 'success') {
                            toastr.success(response.data.message);
                        } else {
                            toastr.error(response.data.message);
                        }
                    })
                    .catch(function (error) {
                        toastr.error(error);
                    });

            });

            {else}

                if(document.getElementById('content')){
                    tinymce.init({
                        selector: '#content',
                        // language: ib_lang,
                        relative_urls: false,
                        remove_script_host: false,
                        removed_menuitems: 'newdocument',
                        forced_root_block : false,
                        fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
                        setup: function(ed) {
                            ed.on('init', function() {
                                this.getDoc().body.style.fontSize = '14px';
                            });
                        },
                        table_default_styles: {
                            width: '100%'
                        },

                        toolbar: 'code | styles | bold italic underline strikethrough fontsize | numlist bullist link image table | alignleft aligncenter alignright alignjustify | outdent indent',
                        license_key: 'gpl',
                        branding: false,
                        promotion: false,
                        menubar: false,
                    });
                }

            btnSavePost.addEventListener('click', function (e) {
                e.preventDefault();

                let formData = new FormData();
                formData.append('id', document.getElementById('post_id').value);
                formData.append('title', document.getElementById('input_title').value);
                formData.append('content', tinyMCE.activeEditor.getContent());

                axios.post('{$base_url}cms/post', formData)
                    .then(function (response) {
                        if (response.data.status === 'success') {
                            toastr.success(response.data.message);
                        } else {
                            toastr.error(response.data.message);
                        }
                    })
                    .catch(function (error) {
                        toastr.error(error);
                    });

            });

            {/if}


        });
    </script>
{/block}

