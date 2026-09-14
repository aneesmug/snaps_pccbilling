{extends file="$layouts_admin"}

{block name="head"}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />
{/block}

{block name="content"}
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-container">
                    <div class="panel-content">

                        <div class="row">
                            <div class="col-md-3 col-sm-6">

                                <form>
                                    <div class="mb-3">
                                        <label for="reportrange">{$_L['Date Range']}</label>
                                        <input type="text" name="reportrange" class="form-control" id="reportrange">
                                    </div>

                                    <div class="mb-3">
                                        <label for="tr_type">{{__('Transaction')}} {$_L['Type']}</label>
                                        <select id="tr_type" name="tr_type" class="form-control">
                                            <option value="">{$_L['All']}</option>
                                            <option value="Income" {if $tr_type eq 'Income'}selected{/if}>{$_L['Income']}</option>
                                            <option value="Expense" {if $tr_type eq 'Expense'}selected{/if}>{$_L['Expense']}</option>
                                            <option value="Transfer" {if $tr_type eq 'Transfer'}selected{/if}>{$_L['Transfer']}</option>
                                            <option value="Equity" {if $tr_type eq 'Transfer'}selected{/if}>{$_L['Equity']}</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="block_expense_type">
                                        <label for="tr_type">{__('Expense')} {$_L['Type']}</label>
                                        <select id="tr_type" name="tr_type" class="form-control">
                                            <option value="">{$_L['All']}</option>
                                            {foreach $expense_types as $expense_type}
                                                <option value="{$expense_type->name}">{$expense_type->name}</option>
                                            {/foreach}
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="account">{$_L['Account']}</label>
                                        <select id="account" name="account" class="form-control">
                                            <option value="">{$_L['All']}</option>
                                            {foreach $a as $as}
                                                <option value="{$as['account']}" {if $p_account eq ($as['id'])}selected="selected" {/if}>{$as['account']}</option>
                                            {/foreach}
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="staff_id">{$_L['Staff']}</label>
                                        <select class="form-select" name="staff_id" id="staff_id">
                                            <option value="">{$_L['All']}</option>
                                            {foreach $staffs as $staff}
                                                <option value="{$staff.id}">{$staff.fullname}</option>
                                            {/foreach}

                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="cid">{$_L['Contact']}</label>
                                        <select id="cid" name="cid" class="form-control">
                                            <option value="">{$_L['All']}</option>
                                            {foreach $c as $cs}
                                                <option value="{$cs['id']}"
                                                        {if $p_cid eq ($cs['id'])}selected="selected" {/if}>{$cs['account']} {if $cs['email'] neq ''}- {$cs['email']}{/if}</option>
                                            {/foreach}

                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category">{$_L['Category']}</label>
                                        <select id="category" name="category" class="form-control">
                                            <option value="">{$_L['All']}</option>
                                            {foreach $categories as $category}
                                                <option value="{$category->id}">{$category->name}</option>
                                            {/foreach}

                                        </select>
                                    </div>


                                    <div class="mb-3">
                                        <label for="payment_method">{$_L['Payment Method']}</label>
                                        <select id="payment_method" name="payment_method" class="form-select">
                                            <option value="">{$_L['All']}</option>
                                            {foreach $payment_methods as $payment_method}
                                                <option value="{$payment_method->name}">{$payment_method->name}</option>
                                            {/foreach}
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="group_id">{$_L['Customer Group']}</label>
                                        <select class="form-select" name="group_id" id="group_id">
                                            <option value="">{$_L['All']}</option>

                                            {foreach $contact_groups as $group}
                                                <option value="{$group.id}">{$group.gname}</option>
                                            {/foreach}

                                        </select>
                                    </div>


                                    <div class="mb-3">
                                        <label for="item_id">{__('Item')}</label>
                                        <select class="form-select" name="item_id" id="item_id">
                                            <option value="">{$_L['All']}</option>
                                            {foreach $items as $item}
                                                <option value="{$item.id}">{$item.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>



                                    <button type="submit" id="ib_filter" class="btn btn-primary">{__('Filter')}</button>

                                    <br>
                                </form>


                            </div>
                            <div class="col-md-9 col-sm-6 ib_right_panel">


                                <div class="table-responsive" id="ib_data_panel">


                                    <table class="table table-bordered display" id="ib_dt">
                                        <thead>
                                        <tr class="heading">
                                            <th>{$_L['ID']}</th>
                                            <th>{$_L['Date']}</th>
                                            <th>{$_L['Account']}</th>
                                            <th>{$_L['Type']}</th>

                                            <th class="text-end">{$_L['Amount']}</th>

                                            <th>{$_L['Customer']}</th>
                                            <th>{$_L['Description']}</th>
                                            <th>{$_L['Method']}</th>
                                            <th>{$_L['Ref']}</th>
                                            <th>{$_L['Staff']}</th>
                                            <th>{$_L['Item']}</th>
                                            <th class="text-end">
                                                {$_L['Dr']}
                                                {if $home_currency}({$home_currency->iso_code}){/if}
                                            </th>
                                            <th class="text-end">
                                                {$_L['Cr']}
                                                {if $home_currency}({$home_currency->iso_code}){/if}
                                            </th>

                                            <th>{$_L['Manage']}</th>
                                        </tr>
                                        </thead>

{*                                        <tfoot>*}
{*                                        <tr>*}
{*                                            <th colspan="4" style="text-align:right">{$_L['Total']}:</th>*}
{*                                            <th></th>*}
{*                                            <th colspan="9"></th>*}
{*                                        </tr>*}
{*                                        </tfoot>*}

                                    </table>
                                </div>


                                {if !$has_multiple_currencies}
                                    <h3 class="mt-3">{__('Total')}: <span id="amount_total">--</span></h3>
                                {/if}



                            </div>
                        </div>


                    </div>
                </div>
            </div>

        </div>


    </div> <!-- Row end-->



{/block}


{block name="script"}

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>

    <script>
        $(function () {
            var $block_expense_type = $("#block_expense_type");
            $block_expense_type.hide();

            $("#tr_type").on('change',function () {
                if($(this).val() == 'Expense'){
                    $block_expense_type.show('slow');
                }
                else{
                    $block_expense_type.hide('slow');
                }
            });

            var $ib_data_panel = $("#ib_data_panel");

            $ib_data_panel.block({ message:block_msg });

            var $cid = $('#cid');

            var $account = $("#account");

            var $category = $("#category");

            const $payment_method = $("#payment_method");

            $category.select2({

            });

            $cid.select2({

            });

            $account.select2({

            });

            $payment_method.select2({

            });


            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }

            var $reportrange = $("#reportrange");

            $reportrange.daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    '{__('Today')}': [moment(), moment()],
                    '{__('Yesterday')}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '{__('Last 7 Days')}': [moment().subtract(6, 'days'), moment()],
                    '{__('Last 30 Days')}': [moment().subtract(29, 'days'), moment()],
                    '{__('This Month')}': [moment().startOf('month'), moment().endOf('month')],
                    '{__('Last Month')}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: 'YYYY/MM/DD'
                }
            }, cb);

            cb(start, end);



            var ib_dt = $('#ib_dt').DataTable( {
                select: true,
                "serverSide": true,
                "ajax": {
                    "url": base_url + "transactions/tr_list/",
                    "type": "POST",
                    "data": function ( d ) {

                        d.tr_type = $('#tr_type').val();
                        d.reportrange = $reportrange.val();
                        d.cid = $cid.val();
                        d.account = $account.val();
                        d.category = $category.val();
                        d.payment_method = $payment_method.val();
                        d.group_id = $('#group_id').val();
                        d.staff_id = $('#staff_id').val();
                        d.item_id = $('#item_id').val();

                    }
                },
                rowId: 9,
                "language": {
                    "emptyTable": "{$_L['No items to display']}",
                    "info":      "{$_L['Showing _START_ to _END_ of _TOTAL_ entries']}",
                    "infoEmpty":      "{$_L['Showing 0 to 0 of 0 entries']}",
                    buttons: {
                        pageLength: '{$_L['Show all']}'
                    },
                    searchPlaceholder: "{__('Search')}"
                },
                "pageLength": 10,
                responsive: true,
                dom:
                /*	--- Layout Structure
                    --- Options
                    l	-	length changing input control
                    f	-	filtering input
                    t	-	The table!
                    i	-	Table information summary
                    p	-	pagination control
                    r	-	processing display element
                    B	-	buttons
                    R	-	ColReorder
                    S	-	Select

                    --- Markup
                    < and >				- div element
                    <"class" and >		- div with a class
                    <"#id" and >		- div with an ID
                    <"#id.class" and >	- div with an ID and a class

                    --- Further reading
                    https://datatables.net/reference/option/dom
                    --------------------------------------
                 */
                    "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'l><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [

                    {
                        extend: 'pdfHtml5',
                        text: '{__('PDF')}',
                        titleAttr: 'Generate PDF',
                        className: 'btn-danger btn-sm mb-2'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '{__('Excel')}',
                        titleAttr: 'Generate Excel',
                        className: 'btn-success btn-sm mb-2'
                    },
                    {
                        extend: 'csvHtml5',
                        text: '{__('CSV')}',
                        titleAttr: 'Generate CSV',
                        className: 'btn-primary btn-sm mb-2'
                    },
                    {
                        extend: 'copyHtml5',
                        text: '{__('Copy')}',
                        titleAttr: 'Copy to clipboard',
                        className: 'btn-dark btn-sm mb-2'
                    },
                    {
                        extend: 'print',
                        text: '{__('Print')}',
                        titleAttr: 'Print Table',
                        className: 'btn-secondary btn-sm mb-2'
                    },
                    {
                        extend: 'selectAll',
                        text: '{__('Select All')}',
                        titleAttr: 'Select All',
                        className: 'btn-success btn-sm mb-2'
                    },
                    {
                        extend: 'selectNone',
                        text: '{__('Select None')}',
                        titleAttr: 'Select None',
                        className: 'btn-danger btn-sm mb-2'
                    },
                    {
                        text: '{__('Delete')}',
                        className: 'btn-danger btn-sm mb-2',
                        action: function () {
                            let ids = ib_dt.rows({ selected: true }).ids();
                            if (ids.length > 0) {
                                let ids_string = ids.join(",");
                                let c = confirm("{__('Delete all selected items?')}");
                                if (c) {
                                    $.post('{$base_url}transactions/mass-delete', {
                                        'ids': ids_string,
                                    }, function (data) {
                                        location.reload();
                                    });
                                }
                            } else {
                                toastr.error("{__('No items selected')}");
                            }
                        }
                    }
                ],
                fixedHeader: {
                    headerOffset: 50
                },
                lengthMenu: [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                "columnDefs": [
                    { "orderable": false, "targets": 8 }
                ],
                "order": [[ 0, 'desc' ]],
                "scrollX": true,
                "initComplete": function () {
                    $ib_data_panel.unblock();
                },
                "footerCallback": function ( row, data, start, end, display ) {

                        if(document.getElementById('amount_total'))
                            {
                                var api = this.api(), data;

                                var intVal = function ( i ) {
                                    return typeof i === 'string' ?
                                        i.replace(/[\$,]/g, '')*1 :
                                        typeof i === 'number' ?
                                            i : 0;
                                };



                                let amount_total = 0;
                                let total = api
                                    .column( 4 )
                                    .data();

                                for (let i = 0; i < total.length; i++) {
                                    let amount = app.parseAmount(total[i],home_currency,2);
                                    amount = parseFloat(amount);
                                    if(amount)
                                    {
                                        amount_total += amount;
                                    }
                                }

                                document.getElementById('amount_total').innerHTML = app.formatCurrency(amount_total,home_currency);

                            }

                }
            } );

            var $ib_filter = $("#ib_filter");


            $ib_filter.on('click', function(e) {
                e.preventDefault();

                $ib_data_panel.block({ message:block_msg });

                ib_dt.ajax.reload(
                    function () {
                        $ib_data_panel.unblock();
                    }
                );


            });



        })
    </script>
{/block}
