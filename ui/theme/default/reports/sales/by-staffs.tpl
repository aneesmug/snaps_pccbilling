{extends file="reports/sales/base.tpl"}
{block name='inner-content'}

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label>Staff</label>
                <select name="staff_id" id="staff_id"  class="form-select">
                    <option value="0">{{__('All')}}</option>
                    {foreach $staffs as $employee}
                        <option value="{$employee->id}"
                                {if $selected_staff_id == $employee->id}
                                    selected
                                {/if}
                        >{$employee->fullname}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label>Date</label>
                <input type="text" name="reportrange" class="form-control" id="reportrange">
            </div>
        </div>
    </div>

    <div class="row mb-4">



        <div class="col-md-6">

            <div class="card mb-2">

                <div class="card-body">

                    <div class="h1 mb-0">{{$total_items_sold}}</div>


                    <h4 class="text-muted mt-1">{{__('Total items sold')}}</h4>
                </div>

            </div>


        </div>


        <div class="col-md-6">

            <div class="card mb-2">

                <div class="card-body">

                    <div class="h1 mb-0">{{formatCurrency($total_amount,$config['home_currency'])}}</div>


                    <h4 class="text-muted mt-1">{{__('Total amount')}}</h4>
                </div>

            </div>


        </div>




    </div>


    <table class="table" id="salesItems">
        <thead class="thead-light">
        <tr>
            <th>
                {{__('Item')}}
            </th>
            <th>
                {{__('Quantity')}}
            </th>
            <th>
                {{__('Amount')}}
            </th>
        </tr>

        </thead>

        <tbody>
        {foreach $items_sold as $item}
            <tr>
                <td>
                    <a href="#">{$item->description}</a>
                </td>
                <td>
                    {$item->qty}
                </td>
                <td>
                    {formatCurrency($item->amount,$config['home_currency'])}
                </td>
            </tr>
        {/foreach}
        </tbody>

    </table>





{/block}

{block name="script"}
    <script>
        $(function () {

            $('#staff_id').change(function () {
                let staff_id = $(this).val();
                window.location.href = '{$base_url}reports/sales/by-staffs/' + staff_id;
            });

            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }

            let $reportrange = $("#reportrange");

            $reportrange.daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    format: 'YYYY/MM/DD'
                }
            }, cb);

            cb(start, end);

            $reportrange.on('change',function () {

                let reportRangeValue = $reportrange.val();
                reportRangeValue = reportRangeValue.replace(/\//g, '*');
                //remove spaces
                reportRangeValue = reportRangeValue.replace(/\s/g, '');


                window.location.href = '{$base_url}reports/sales/by-staffs/' + $('#staff_id').val() + '/' + reportRangeValue;
            });

            $(function () {
                $('#salesItems').DataTable();
            });

        });
    </script>
{/block}
