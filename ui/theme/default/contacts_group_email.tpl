{extends file="$layouts_admin"}

{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <div class="panel"  id="iform">
                <div class="panel-hdr">
                    <h5>{$_L['Send Email']}</h5>

                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <form id="mainForm" method="post">

                            <div class="mail-box">


                                <div class="mail-body">


                                    <div class="mb-3"><label for="emails">{$_L['To']}:</label>

                                        <select class="form-select" name="emails[]" id="emails" multiple="multiple">

                                            {foreach $ds as $d}
                                                <option value="{$d['email']}" selected>{$d['email']}</option>
                                            {/foreach}

                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cc">{$_L['Cc']}:</label>
                                        <input type="text" id="cc" name="cc" class="form-control" value="">
                                    </div>

                                    <div class="mb-3">
                                        <label for="bcc">{$_L['Bcc']}:</label>
                                        <input type="text" id="bcc" name="bcc" class="form-control" value="">
                                    </div>

                                    <div class="mb-3">
                                        <label for="subject">{$_L['Subject']}:</label>
                                        <input type="text" id="subject" name="subject" class="form-control mb-3" required value="">

                                        <textarea id="content"  class="form-control sysedit" name="message"></textarea>

                                    </div>




                                </div>


                                <div class="mail-body text-end">

                                    <button type="submit" id="send_email"  class="btn btn-sm btn-primary"><i class="fal fa-paper-plane-o"></i> {$_L['Send']}</button>
                                </div>

                            </div>

                        </form>
                    </div>



                </div>
            </div>



        </div>



    </div>
{/block}

{block name="script"}

    <script>
        $(function () {

            var form = document.getElementById("mainForm");


            $('#content').redactor(
                {
                    minHeight: 200, // pixels
                    plugins: ['fontcolor']
                }
            );

            $("#emails").select2({
                    language: {
                        noResults: function () {
                            return $("#_lan_no_results_found").val();
                        }
                    }
                }
            );


            const $send_email = $("#send_email");

            $("#mainForm").submit(function (e) {

                e.preventDefault();
                $send_email.prop('disabled', true);

                $.post('{{$base_url}}contacts/group_email_post', $( "#mainForm" ).serialize())
                    .done(function (data) {
                        window.location = '{{$base_url}}util/sent-emails';
                    }).fail(function(data) {
                    $('#clx_form_box').unblock();
                });

            });



        });

    </script>
{/block}
