<div class="mx-auto" style="max-width: 800px;">

    <div class="panel mb-0 rounded-0">

        <div class="panel-hdr ">

            <h2>{$_L['Edit']}</h2>
        </div>


        <div class="panel-container">
            <div class="panel-content">
                <div class="row">
                    <div class="col-md-8">
                        <form class="form-horizontal" role="form" id="edit_form" method="post">
                            <div class="mb-3">
                                <label for="name">{$_L['Name']}</label>
                                <input type="text" class="form-control" value="{$item->name}" name="name" id="name">
                            </div>
                            <div class="mb-3">
                                <label for="rate">{$_L['Item Number']}</label>
                                <input type="text" class="form-control" name="item_number" value="{$item->item_number}" id="item_number">
                            </div>
                            <div class="mb-3">
                                <label for="rate" >{$_L['Sales Price']}</label>
                                <input type="text" id="sales_price" name="sales_price" class="form-control amount" autocomplete="off" data-a-sign="{$config['currency_code']}"  data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-d-group="2" value="{$item->sales_price}">
                            </div>

                            <div class="mb-3">
                                <label for="cost_price" >{$_L['Cost Price']}</label>
                                <input type="text" id="cost_price" name="cost_price" class="form-control amount" autocomplete="off" data-a-sign="{$config['currency_code']}"  data-a-dec="{$config['dec_point']}" data-a-sep="{$config['thousands_sep']}" data-d-group="2" value="{$item->cost_price}">
                            </div>
                            <div class="mb-3">
                                <label for="available" >{$_L['Available']}</label>
                                <input type="text" id="inventory" name="inventory" class="form-control" autocomplete="off" value="{round($item->inventory)}">
                            </div>

                            <div class="mb-3">
                                <label for="description">{$_L['Description']}</label>
                                <textarea id="description" name="description" class="form-control" rows="3">{$item->description}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="sku">{$_L['SKU']}</label>
                                <input type="text" class="form-control" name="sku" value="{$item->sku}" id="sku">
                            </div>

                            <div class="mb-3">

                                <label for="tax_code">
                                    {if $config['tax_system'] == 'India'}
                                        HSN/SAC
                                    {else}
                                        {$_L['Tax Code']}
                                    {/if}
                                </label>

                                <input type="text" id="tax_code" name="tax_code" class="form-control" autocomplete="off" value="{$item->tax_code}">
                            </div>

                            <div class="mb-3">
                                <label for="tax1_rate">{__('Tax Rate')} (%)</label>
                                <input type="text" id="tax1_rate" name="tax1_rate" class="form-control" autocomplete="off" value="{$item->tax1_rate}">
                            </div>

                            {if $type eq 'Product'}
                            <div class="mb-3"><label for="unit">{$_L['Unit']}</label>

                                <select class="form-select" id="unit" name="unit">
                                    <option value="">...</option>
                                    {foreach $units as $unit}
                                        <option value="{$unit['name']}"
                                                {if $item->unit === $unit['name']}selected{/if}
                                        >{$unit['name']}</option>
                                    {/foreach}
                                </select>
                            </div>
                            {/if}

                            <div class="mb-3">
                                <label for="inventory">{$_L['Size']}</label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="text" id="width" name="width" placeholder="{$_L['Width']}" class="form-control" value="{$item->width}" autocomplete="off">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="width" name="length" placeholder="{$_L['Length']}" class="form-control" value="{$item->length}" autocomplete="off">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="width" name="height" placeholder="{$_L['Height']}" class="form-control" value="{$item->height}" autocomplete="off">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="width" name="weight" placeholder="{$_L['Weight']}" class="form-control" value="{$item->weight}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="id" value="{$item->id}">
                            <input type="hidden" name="file_link" id="file_link_image" value="{$item->image}">
                            <button id="update" class="btn btn-primary">{$_L['Update']}</button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form action="" class="dropzone" id="upload_container">

                            <div class="dz-message">
                                <h3> <i class="fal fa-cloud-upload"></i>  {$_L['Drop File Here']}</h3>
                                <br />
                                <span class="note">{$_L['Click to Upload']}</span>
                            </div>

                            <hr>



                        </form>

                        {$has_img}

                    </div>

                </div>

            </div>


        </div>
    </div>


</div>


