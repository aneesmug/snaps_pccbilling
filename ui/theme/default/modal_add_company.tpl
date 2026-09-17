<div>
    <div class="card shadow-none mb-0">
        <div class="card-header">
            <h2>
                {if $f_type eq 'edit'}
                    {$_L['Edit']}
                {else}
                    {$_L['New Company']}
                {/if}
            </h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <div class="card-body">

                    <form class="form-horizontal" id="ib_modal_form">


                        <div class="row">
                            <div class="col-md-6">



                                <div class="mb-3">
                                    <label for="company_name">{$_L['Company Name']}<small class="red">*</small></label>

                                    <div><input type="text" id="company_name" name="company_name" class="form-control" value="{$val['company_name']}" required>

                                    </div>


                                </div>


                                <div class="mb-3">
                                    <label for="code">{$_L['Code']}<small class="red">*</small></label>

                                    <div><input type="text" id="code" name="code" class="form-control" value="{$val['code']}">

                                    </div>


                                </div>

                                <div class="mb-3">
                                    <label for="building_number">Building Number<small class="red">*</small></label>

                                    <div><input type="text" id="building_number" name="building_number" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="e.g. 1234" value="{$val['building_number']}" required>

                                    </div>


                                </div>

                                <div class="mb-3">
                                    <label for="vat_number">VAT Number<small class="red">*</small></label>

                                    <div><input type="text" id="vat_number" name="vat_number" class="form-control js-digits-only" inputmode="numeric" maxlength="15" placeholder="15 digits, starts and ends with 3" value="{$val['vat_number']}" required>

                                    </div>
                                    <small class="help-block">Used for company VAT.</small>

                                </div>

                                <div class="mb-3">
                                    <label for="crn_number">Unified No. (700#)<small class="red">*</small></label>

                                    <div><input type="text" id="crn_number" name="crn_number" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="10 digits" value="{$val['crn_number']}" required>

                                    </div>
                                    <small class="help-block">Used for company Unified No. (700#).</small>

                                </div>

                                {if $config['show_business_number'] eq '1'}


                                    <div class="mb-3">
                                        <label for="business_number">{$config['label_business_number']}</label>

                                        <div><input type="text" id="business_number" name="business_number" class="form-control" value="{$val['business_number']}">

                                        </div>


                                    </div>


                                {/if}


                                <div class="mb-3"><label for="url">{$_L['URL']}</label>

                                    <div><input type="text" id="url" name="url" class="form-control" value="{$val['url']}">

                                    </div>


                                </div>


                                <div class="mb-3"><label for="email">{$_L['Email']}</label>
                                    <div>
                                        <input type="text" id="email" name="email" class="form-control" value="{$val['email']}">
                                    </div>






                                </div>


                                <div class="mb-3"><label for="phone">{$_L['Phone']}<small class="red">*</small></label>

                                    <div><input type="text" id="phone" name="phone" class="form-control" value="{$val['phone']}" required>

                                    </div>


                                </div>


                                {if $config['fax_field'] eq '1'}


                                    <div class="mb-3"><label for="fax">{$_L['Fax']}</label>

                                        <div><input type="text" id="fax" name="fax" class="form-control" value="{$val['fax']}">

                                        </div>


                                    </div>



                                {/if}









                            </div>

                            <div class="col-md-6">

                                <div class="mb-3"><label for="logo_url">{$_L['Logo URL']}</label>

                                    <div><input type="text" id="logo_url" name="logo_url" class="form-control" value="{$val['logo_url']}">

                                    </div>


                                </div>

                                <div class="mb-3"><label for="c_address1">{$_L['Address']}<small class="red">*</small></label>

                                    <input type="text" id="c_address1" name="address1" class="form-control" value="{$val['address1']}" required>


                                </div>

                                <div class="mb-3"><label for="c_city">{$_L['City']}<small class="red">*</small></label>

                                    <div><input type="text" id="c_city" name="city" class="form-control" value="{$val['city']}" required>

                                    </div>


                                </div>

                                <div class="mb-3"><label for="c_state">{$_L['State Region']}<small class="red">*</small></label>


                                    <div><input type="text" id="c_state" name="state" class="form-control" value="{$val['state']}" required></div>


                                </div>

                                <div class="mb-3"><label for="c_zip">{$_L['ZIP Postal Code']}<small class="red">*</small></label>

                                    <input type="text" id="c_zip" name="zip" class="form-control" value="{$val['zip']}" required>


                                </div>

                                <div class="mb-3"><label for="c_country">{$_L['Country']}<small class="red">*</small></label>

                                    <select name="country" id="c_country" class="form-control country" required>
                                        <option value="">{$_L['Select Country']}</option>
                                        {$countries}
                                    </select>


                                </div>



                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary modal_submit mt-3">{$_L['Save']}</button>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="f_type" id="f_type" value="{$f_type}">
                        <input type="hidden" name="cid" id="cid" value="{$val['cid']}">

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>
