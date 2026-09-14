{extends file="$layouts_admin"}
{block name="head"}

    {if empty($config['admin_dark_theme'])}
        <style>
            .table-striped tbody tr:nth-of-type(odd) {
                background-color: #F7F9FC;
            }
        </style>
    {/if}

    <style>
        .swal2-content {
            text-align: left !important;
        }
    </style>

{/block}

{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{$_L['Groups']}</h2>
                    <div class="panel-toolbar">
                        <div class="btn-group">
                            <a href="#" class="btn btn-sm btn-success" id="add_new_group"><i class="fal fa-plus"></i> {$_L['Add New Group']}</a>
                            <a href="{$_url}reorder/groups/" class="btn btn-sm btn-primary"><i class="fal fa-download"></i> {$_L['Reorder']}</a>
                        </div>


                    </div>

                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="thead-light">
                            <table class="table table-striped">
                                <th><strong>{$_L['Group']}</strong></th>
                                <th><strong>{__('Customers')}</strong></th>
                                <th><strong>{__('Color')}</strong></th>
                                <th class="text-end">{$_L['Manage']}</th>
                                {foreach $gs as $g}
                                    <tr>
                                        <td><strong>{$g['gname']}</strong></td>

                                        <td>
                                            <strong>{Contact::where('gid',$g['id'])->count()}</strong>
                                        </td>

                                        <td>
                                            <span class="badge" style="background-color: {$g['color']|default:'#3979FF'};">{$_L['Color']}</span>
                                        </td>

                                         <td>
                                             <div class="float-end">
                                                 <a href="{$_url}contacts/find_by_group/{$g['id']}/" class="btn btn-sm btn-info"> {$_L['List Contacts']}</a>
                                                 <a href="#" class="btn btn-sm btn-primary edit_group" id="e{$g['id']}" data-name="{$g['gname']}" data-color="{$g['color']|default:'#3979FF'}"> {$_L['Edit']}</a>

                                                 <a href="javascript:;" id="g{$g['id']}" class="btn btn-sm btn-danger cdelete"> {$_L['Delete']}</a>
                                             </div>


                                        </td>
                                    </tr>
                                {/foreach}


                            </table>
                        </div>



                    </div>

                    <br>
                    <br>


                </div>
            </div>



        </div>



    </div>

    <input type="hidden" name="_msg_add_new_group" id="_msg_add_new_group" value="{$_L['Add New Group']}">
    <input type="hidden" name="_msg_group_name" id="_msg_group_name" value="{$_L['Group Name']}">
    <input type="hidden" name="_msg_edit" id="_msg_edit" value="{$_L['Edit']}">
    <input type="hidden" name="_msg_ok" id="_msg_ok" value="{$_L['OK']}">
    <input type="hidden" name="_msg_cancel" id="_msg_cancel" value="{$_L['Cancel']}">


{/block}

{block name="script"}

    <script>

        $(document).ready(function () {

            var _url = $("#_url").val();


            $("#add_new_group").click(function(e){

                e.preventDefault();

                (async () => {

                    const { value: group_name } = await Swal.fire({
                        title: '{$_L['Add New Group']}',
                        input: 'text',
                        inputLabel: '{$_L['Group Name']}',
                        inputPlaceholder: '{$_L['Group Name']}',
                    })

                    if (group_name) {
                        $.post(  _url + "contacts/add_group/", { group_name: group_name })
                            .done(function( data ) {

                                if ($.isNumeric(data)) {

                                    location.reload();

                                }

                                else {
                                    Swal.fire({
                                        title: '{__('Error')}',
                                        text: data,
                                        type: 'error',
                                        confirmButtonText: '{$_L['Close']}'
                                    })
                                }

                            });
                    }

                })()



            });


            $(".cdelete").click(function (e) {
                e.preventDefault();
                var id = this.id;
                app.confirm(_L['are_you_sure'], function(result) {
                    if(result){
                        var _url = $("#_url").val();
                        window.location.href = _url + "delete/crm-group/" + id;
                    }
                });



            });


            $(".edit_group").click(function (e) {
                e.preventDefault();

                var eid = this.id;

                // alert(eid);

                var gname = $( this ).attr( "data-name" );

                var gcolor = $( this ).attr( "data-color" );


                (async () => {

                    const { value: group } = await Swal.fire({
                        title: '{$_L['Edit']}',
                        html:
                            '<div class="mb-3">' +
                                '<label class="mb-1" for="swal-input1">{$_L['Group Name']}</label>' +
                                '<input type="text" class="form-control" id="swal-input1" placeholder="{$_L['Group Name']}" value="' + gname + '">' +
                            '</div>' +
                            '<div class="mb-3">' +
                                '<label class="mb-1" for="swal-input2">{$_L['Color']}</label>' +
                                '<input type="color" class="form-control" id="swal-input2" value="' + gcolor + '">' +
                            '</div>',
                        focusConfirm: false,
                        preConfirm: () => {
                            return [
                                document.getElementById('swal-input1').value,
                                document.getElementById('swal-input2').value
                            ]
                        }
                    })

                    if (group) {
                        $.post(  _url + "contacts/group_edit/", { id: eid, gname: group[0], color: group[1] })
                            .done(function( data ) {
                                location.reload();
                            });
                    }

                })()



            });




        });
    </script>
{/block}
