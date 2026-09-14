<div class="mx-auto" style="max-width: 800px;">
    <div class="panel mb-0 rounded-0">
        <div class="panel-hdr">
            <h2>{$_L['Products n Services']}</h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <table class="table table-striped table-bordered" id="modal_items_table">
                    <thead>
                    <tr>
                        <th width="10%">#</th>
                        <th width="20%">{$_L['Item Code']}</th>
                        <th>{$_L['Item Name']}</th>

                        <th width="15%">{$_L['Price']}</th>
                        <th>{$_L['Tax Code']}</th>
                    </tr>
                    </thead>
                    <tbody>

                    {foreach $d as $ds}
                        <tr>
                            <td><input type="checkbox" class="si"></td>
                            <td>{$ds['id']}</td>
                            <td>{$ds['name']}</td>

                            <td class="price">{formatCurrency($ds['sales_price'],$config['home_currency'],$format_currency_override)}</td>
                            <td class="tax_code">{$ds['tax_code']}</td>
                        </tr>
                    {/foreach}


                    </tbody>
                </table>

                <div class="mb-3">
                    <button type="button" data-bs-dismiss="modal" class="btn">{$_L['Close']}</button>
                    <button class="btn btn-primary update">{$_L['Select']}</button>
                </div>
            </div>
        </div>
    </div>
</div>
