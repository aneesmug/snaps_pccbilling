{extends file="reports/sales/base.tpl"}
{block name='inner-content'}

    <div class="table-responsive">
        <table class="table  table-striped dataTable" id="salesItems">
            <thead style="background: #f0f2ff">
            <tr>
                <th class="h6">{$_L['Item']}</th>
                <th class="h6">{$_L['Qty']}</th>
                <th class="h6">{$_L['Amount']}</th>
                <th class="h6">{$_L['Total']}</th>
                <th class="h6">{$_L['Date']}</th>
            </tr>
            </thead>
            <tbody>

            {foreach $invoice_items as $item}
                <tr>
                    <td class="h6">{$item['description']}</td>
                    <td class="h6">{$item['qty']}</td>
                    <td class="h6">{$item['amount']}</td>
                    <td class="h6">{$item['total']}</td>
                    <td class="h6 text-info">{$item['duedate']}</td>
                </tr>
            {/foreach}

            </tbody>
            <tfoot>
            <tr>
                <th>{$_L['Item']}</th>
                <th>{$_L['Qty']}</th>
                <th>{$_L['Amount']}</th>
                <th>{$_L['Total']}</th>
                <th>{$_L['Date']}</th>
            </tr>
            </tfoot>
        </table>
    </div>

{/block}

{block name="script"}
    <script>
        $(function () {
            $('#salesItems').DataTable();
        });
    </script>
{/block}
