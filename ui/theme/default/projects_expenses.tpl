{extends file="project_base.tpl"}


{block name="project_content"}
    <div class="row mb-3">
        <div class="col">
            <h2>{$_L['Expenses']}</h2>
        </div>

        <div class="col text-end">
            <a href="javascript:;" id="add_expense" class="btn btn-primary">New Expense</a>
        </div>



    </div>


    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped table-bordered" id="clx_datatable">
                <thead style="background: #f0f2ff">
                <tr>
                    <th class="h6">ID</th>
                    <th class="h6">{$_L['Date']}</th>
                    <th class="h6">{$_L['Account']}</th>
                    <th class="h6">{$_L['Type']}</th>
                    <th class="h6">{$_L['Amount']}</th>
                    <th class="h6">{$_L['Description']}</th>



                    <th class="text-end">{$_L['Manage']}</th>
                </tr>
                </thead>
                <tbody>

                {foreach $expenses as $expense}
                    <tr>
                        <td class="h6">
                            {$expense->code}

                        </td>
                        <td class="h6">{$expense->date}
                        </td>
                        <td class="h6">

                            {if isset($accounts[$expense->account_id])}
                                {$accounts[$expense->account_id]->account}
                            {/if}

                        </td>
                        <td class="h6">
                            {$expense->type}

                        </td>
                        <td class="h6">
                            {formatCurrency($expense->amount,$expense->currency_iso_code)}
                        </td>
                        <td class="h6 text-info">{$expense->description}
                        </td>
                        <td>
                            <a href="{$_url}transactions/manage/{$expense['id']}" class="btn btn-primary btn-sm active text-end" role="button" aria-pressed="true">manage</a>

                        </td>



                    </tr>





                {/foreach}



                </tbody>



            </table>
        </div>
    </div>



{/block}

{block name="script"}
    <script>
        $(function () {
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





           $('#add_expense').on('click',function (e) {
               e.preventDefault();

               $.fancybox.open({
                   src  :  base_url + 'projects/expense/{$project->id}',
                   type : 'ajax',
                   opts : {
                       afterShow : function( instance, current ) {
                           $(".datepicker").datepicker();
                           $("#account").select2({

                           });
                           $("#payee").select2({

                           });
                           $("#company").select2({

                           });
                       }
                   },
               });
           });


            var $modal = $('#cloudonex_body');

            $modal.on('click', '#submit', function(event){

                event.preventDefault();

                $.post(base_url + 'projects/save-expense', $('#project_expense_form').serialize()).done(function (data) {
                    location.reload();
                }).fail(function(data) {
                    toastr.error(data.responseText);
                });

            });





        });
    </script>
{/block}
