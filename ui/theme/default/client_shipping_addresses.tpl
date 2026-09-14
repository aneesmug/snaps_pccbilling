{extends file="$layouts_client"}


{block name="content"}
    <div class="panel">
        <div class="panel-hdr">
            <h2>{$_L['Shipping Addresses']}</h2>
            <div class="panel-toolbar">

            <a href="{$_url}client/shipping-address/" class="btn btn-sm btn-success"> {$_L['Add Address']}</a>
            </div>

        </div>
        <div class="panel-container">

        <div class="panel-content">


        <div class="table-responsive">
        <table class="table  table-striped sys_table">




        <thead style="background: #f0f2ff">
            <tr>


            <th>{$_L['Shipping Addresses']}</th>
            <th class="float-right">{$_L['Manage']}</th>

            </tr>



        </thead>

        <tbody>
        {foreach $shipping_addresses as $shipping_address}


            <tr>

                <td class="h6">{$shipping_address->address_line_1}
                <br>
                    {$shipping_address->zip}, {$shipping_address->city}
                    <br>
                    {$shipping_address->state}, {$shipping_address->country}
                </td>
                <td>

                    <div class="btn-group float-end">
                        {if $shipping_address->is_default}
                            {else}
                            <a href="{$_url}client/set-default-shipping-address/{$shipping_address->id}" class="btn btn-primary ">{$_L['Set Default']} </a>
                        {/if}

                        <a href="{$_url}client/shipping-address/{$shipping_address->id}" class="btn btn-warning btn-icon"><i class="fal fa-pencil"></i> </a>
                        <a href="" class="btn btn-danger btn-icon cdelete" id="{$shipping_address->id}" ><i class="fal fa-trash-alt"></i> </a>
                    </div>
                </td>




            </tr>

        {/foreach}

        </tbody>

        </table>


        </div>
        </div>


        </div>

    </div>






{/block}
{block name="script"}


    <script>
        $(function () {

            var $modal = $('#cloudonex_body');

            $modal.on('click', '.cdelete', function(e){

                e.preventDefault();
                var id = this.id;
                app.confirm("Are You Sure?", function(result) {
                    if(result){
                        var _url = $("#_url").val();
                        window.location.href = _url + "client/delete-shipping-address/" + id;
                    }
                });


            });





        });
    </script>
{/block}




