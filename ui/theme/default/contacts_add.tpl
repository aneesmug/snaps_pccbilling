{extends file="$layouts_admin"}
{block name="head"}
    {if !empty($config['google_maps_api_key'])}
        <script src="https://maps.googleapis.com/maps/api/js?key={$config['google_maps_api_key']}&libraries=places"></script>
    {/if}
{/block}

{block name="content"}

    <div class="row">

        <div class="col-md-12">



            <div class="panel">
                <div class="panel-hdr">
                    <h2><span></span>{$_L['Add Contact']}</h2>

                </div>

                <div class="panel-container show" id="ibox_form">

                    <div class="panel-content">

                        <div class="px-2">
                            <div class="alert alert-danger" id="emsg" style="display: none;">
                                <span id="emsgbody"></span>
                            </div>

                            <form id="rform">

                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="mb-3 row">
                                            <label for="buyer_type" class="col-sm-3"><span class="h6">Buyer Type</span><span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <select id="buyer_type" name="buyer_type" class="form-control">
                                                    <option value="" selected>--Select Buyer Type--</option>
                                                    <option value="company">Company</option>
                                                    <option value="individual">Individual</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-individual-only" style="display:none;">
                                            <label for="account" class="col-sm-3"><span class="h6">{$_L['Full Name']}</span><span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="account" name="account" class="form-control" autofocus>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="code" class="col-sm-3"><span class="h6">{$_L['Code']}</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="code" name="code" class="form-control" value="{$predict_customer_number}">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="display_name" class="col-sm-3"><span class="h6">{$config['contact_extra_field']}</span> </label>
                                            <div class="col-sm-9">
                                                <input type="text" id="display_name" name="display_name" class="form-control">
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-selector-only" style="display:none;">
                                            <label for="cid" class="col-sm-3"><span class="h6">Registered Company</span><span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <select id="cid" name="cid" class="form-control">
                                                    <option value="">--Select Registered Company--</option>
                                                    {foreach $companies as $company}
                                                        <option value="{$company['id']}" {if $c_selected_id eq ($company['id'])}selected{/if}>
                                                            {$company['company_name']}
                                                        </option>
                                                    {/foreach}
                                                    <option value="__new__">+ Create New Company</option>
                                                </select>
                                                <small class="help-block">Choose an existing registered company or select Create New Company.</small>
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="company" class="col-sm-3"><span class="h6">{$_L['Company Name']}</span><span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="company" name="company" class="form-control">
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="company_url" class="col-sm-3"><span class="h6">{$_L['URL']}</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="company_url" name="company_url" class="form-control" placeholder="http://">
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="logo_url" class="col-sm-3"><span class="h6">{$_L['Logo URL']}</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="logo_url" name="logo_url" class="form-control">
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="building_number" class="col-sm-3"><span class="h6">Building Number</span><span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="building_number" name="building_number" class="form-control" inputmode="numeric" maxlength="10" placeholder="e.g. 1234">
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="vat_number" class="col-sm-3"><span class="h6">VAT Number</span><span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="vat_number" name="vat_number" class="form-control" maxlength="15" placeholder="15 digits, starts and ends with 3">
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="crn_number" class="col-sm-3"><span class="h6">Unified No. (700#)</span><span class="text-danger">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="crn_number" name="crn_number" class="form-control" maxlength="10" placeholder="10 digits">
                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-individual-only" style="display:none;">
                                            <label for="id_iqama" class="col-sm-3"><span class="h6">ID / Iqama</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" id="id_iqama" name="id_iqama" class="form-control" inputmode="numeric" maxlength="10" placeholder="10 digits">
                                                <small class="help-block">Optional. If entered, it must be exactly 10 digits.</small>
                                            </div>
                                        </div>



                                        {if $config['show_business_number'] eq '1'}
                                            <div class="mb-3 row">
                                                <label for="business_number" class="col-sm-3"><span class="h6">{$config['label_business_number']}</span> </label>
                                                <div class="col-sm-9">
                                                    <input type="text" id="business_number" name="business_number" class="form-control">
                                                </div>
                                            </div>


                                        {/if}

                                        <div class="mb-3 row">
                                            <label for="type" class="col-sm-3"><span class="h6">{$_L['Type']}</span> </label>
                                            <div class="col-sm-9">
                                                <div class="custom-control my-2 custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="defaultChecked" name="customer" value="Customer" {if $contact_type eq 'customer'}checked{/if}>
                                                    <label class="custom-control-label" for="defaultChecked"><span class="h6">{$_L['Customer']}</span></label>
                                                </div>

                                                <div class="custom-control my-2 custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="input_supplier" name="supplier" value="Supplier" {if $contact_type eq 'supplier'}checked{/if}>
                                                    <label class="custom-control-label" for="input_supplier"><span class="h6">{$_L['Supplier']}</span></label>
                                                </div>

                                            </div>
                                        </div>


                                        {if $config['fax_field'] eq '1'}

                                            <div class="mb-3 row">
                                                <label for="fax" class="col-sm-3"><span class="h6">{$_L['Fax']}</span></label>
                                                <div class="col-sm-9">

                                                    <input type="text" id="fax" name="fax" class="form-control">


                                                </div>
                                            </div>


                                        {/if}



                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="address" class="col-sm-3"><span class="h6">{$_L['Address']}</span> </label>
                                            <div class="col-sm-9">

                                                <input type="text" id="address" name="address" class="form-control">


                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="city" class="col-sm-3"><span class="h6">{$_L['City']}</span> </label>
                                            <div class="col-sm-9">

                                                <input type="text" id="city" name="city" class="form-control">


                                            </div>
                                        </div>

                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="state" class="col-sm-3"><span class="h6">{$_L['State Region']}</span> </label>
                                            <div class="col-sm-9">

                                                <input type="text" id="state" name="state" class="form-control">


                                            </div>
                                        </div>


                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="zip" class="col-sm-3"><span class="h6">{$_L['ZIP Postal Code']}</span> </label>
                                            <div class="col-sm-9">

                                                <input type="text" id="zip" name="zip" class="form-control">
                                            </div>
                                        </div>


                                        <div class="mb-3 row buyer-after-type buyer-company-create-only" style="display:none;">
                                            <label for="country" class="col-sm-3"><span class="h6">{$_L['Country']}</span> </label>
                                            <div class="col-sm-9">

                                                <select name="country" id="country" class="form-control">
                                                    <option value=""><span></span>{$_L['Select Country']}</option>
                                                    {$countries}
                                                </select>
                                            </div>
                                        </div>


                                        <div class="mb-3 row">
                                            <label for="lat_lon" class="col-sm-3"><span class="h6">{__('Location')}</span> </label>
                                            <div class="col-sm-9">
                                                <div class="row">
                                                    <div class="col">
                                                        <input type="text" id="lat" name="lat" class="form-control" placeholder="{__('Latitude')}">
                                                    </div>
                                                    <div class="col">
                                                        <input type="text" id="lon" name="lon" class="form-control" placeholder="{__('Longitude')}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>




                                        {foreach $fs as $f}
                                            <div class="mb-3 row">
                                                <label class="form-label col-sm-3" for="cf{$f['id']}"><span class="h6">{$f['fieldname']}</span></label>
                                                {if ($f['fieldtype']) eq 'text'}


                                                    <div class="col-sm-9">

                                                        <input type="text" id="cf{$f['id']}" name="cf{$f['id']}" class="form-control">
                                                        {if ($f['description']) neq ''}
                                                            <p class="help-block mb-2">{$f['description']}</p>
                                                        {/if}
                                                    </div>


                                                {elseif ($f['fieldtype']) eq 'password'}

                                                    <div class="col-sm-9">
                                                        <input type="password" id="cf{$f['id']}" name="cf{$f['id']}" class="form-control">
                                                        {if ($f['description']) neq ''}
                                                            <p class="help-block mb-2">{$f['description']}</p>
                                                        {/if}
                                                    </div>



                                                {elseif ($f['fieldtype']) eq 'dropdown'}

                                                    <div class="col-sm-9">
                                                        <select id="cf{$f['id']}" name="cf{$f['id']}" class="form-control">
                                                            {foreach explode(',',$f['fieldoptions']) as $fo}
                                                                <option value="{$fo}">{$fo}</option>
                                                            {/foreach}
                                                        </select>
                                                        {if ($f['description']) neq ''}
                                                            <p class="help-block mb-2">{$f['description']}</p>
                                                        {/if}
                                                    </div>


                                                {elseif ($f['fieldtype']) eq 'textarea'}

                                                    <div class="col-sm-9">
                                                        <textarea id="cf{$f['id']}" name="cf{$f['id']}" class="form-control" rows="3"></textarea>
                                                        {if ($f['description']) neq ''}
                                                            <p class="help-block mb-2">{$f['description']}</p>
                                                        {/if}
                                                    </div>

                                                {/if}

                                            </div>
                                        {/foreach}

                                    </div>
                                    <div class="col-md-6 col-sm-12">

                                        <div class="mb-3 row buyer-after-type buyer-shared-contact-only" style="display:none;">
                                            <label for="email" class="col-sm-3"><span class="h6">{$_L['Email']}</span> </label>
                                            <div class="col-sm-9">

                                                <input type="text" id="email" name="email" class="form-control">


                                            </div>
                                        </div>
                                        <div class="mb-3 row buyer-after-type buyer-shared-contact-only" style="display:none;">
                                            <label for="secondary_email" class="col-sm-3"><span class="h6">{$_L['Secondary Email']}</span> </label>
                                            <div class="col-sm-9">

                                                <input type="text" id="secondary_email" name="secondary_email" class="form-control">


                                            </div>
                                        </div>
                                        <div class="mb-3 row buyer-after-type buyer-shared-contact-only" style="display:none;">
                                            <label for="phone" class="col-sm-3"><span class="h6">{$_L['Phone']}</span><span class="text-danger">*</span> </label>
                                            <div class="col-sm-9">

                                                <input type="text" id="phone" name="phone" class="form-control">


                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="currency" class="col-sm-3"><span class="h6">{$_L['Currency']}</span> </label>
                                            <div class="col-sm-9">

                                                <select id="currency" name="currency" class="form-control">

                                                    {foreach $currencies as $currency}
                                                        <option value="{$currency['id']}"
                                                                {if $config['home_currency'] eq ($currency['cname'])}selected="selected" {/if}>{$currency['cname']}</option>
                                                        {foreachelse}
                                                        <option value="0">{$config['home_currency']}</option>
                                                    {/foreach}

                                                </select>
                                            </div>
                                        </div>


                                        <div class="mb-3 row">
                                            <label for="group" class="col-sm-3"><span class="h6">{$_L['Group']} </span></label>
                                            <div class="col-sm-9">

                                                <select class="form-select" name="group" id="group">
                                                    <option value="0">{$_L['None']}</option>
                                                    {foreach $gs as $g}
                                                        <option value="{$g['id']}" {if $g_selected_id eq ($g['id'])}selected{/if}>{$g['gname']}</option>
                                                    {/foreach}
                                                </select>
                                                <span class="help-block "><a href="#" id="add_new_group" class="text-info"> <span class="h6 text-info"><i class="fal fa-plus"></i> {$_L['Add New Group']}</a></span> </span>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="owner_id" class="col-sm-3"><span class="h6">{$_L['Owner']}</span> </label>
                                            <div class="col-sm-9">

                                                <select class="form-select" name="owner_id" id="owner_id">
                                                    {foreach $owners as $owner}
                                                        <option value="{$owner->id}" {if $owner->id == $user->id}selected{/if} >{$owner->fullname}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>



                                        {if $config['customer_custom_username']}

                                            <div class="mb-3 row">
                                                <label for="zip" class="col-sm-3"><span class="h6">{$_L['Username']} </span></label>
                                                <div class="col-sm-9">

                                                    <input type="text" id="username" name="username" class="form-control">
                                                </div>
                                            </div>

                                        {/if}

                                        <div class="mb-3 row">
                                            <label for="password" class="col-sm-3"><span class="h6">{$_L['Password']}</span> </label>
                                            <div class="col-sm-9">
                                                <input type="password" id="password" name="password" class="form-control">
                                                <a href="javascript:;" id="generate_password"><span>{__('Generate Password')}</span></a>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="cpassword" class="col-sm-3"><span class="h6">{$_L['Confirm Password']}</span></label>
                                            <div class="col-sm-9">

                                                <input type="password" id="cpassword" name="cpassword" class="form-control">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="send_client_signup_email" class="col-sm-3"><span class="h6">{$_L['Welcome Email']}</span></label>
                                            <div class="col-sm-9">
                                                <label class="switch s-icons s-outline s-outline-primary">
                                                    <input type="checkbox" name="send_client_signup_email" value="on" id="send_client_signup_email">
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">


                                            <button class="btn btn-primary mt-3 me-3" type="submit" id="submit">{$_L['Save']}</button>


                                        </div>
                                    </div>
                                </div>


                            </form>
                        </div>

                    </div>



                </div>
            </div>
        </div>
    </div>




{/block}

{block name="script"}
    <script>
        $(document).ready(function () {
            $(".progress").hide();
            $("#emsg").hide();
            var _url = '{$_url}';





            $('#tags').select2({
                tags: true,
                tokenSeparators: [','],
                theme: "bootstrap"
            });

            var $cid = $('#cid');

            $cid.select2();

            var $buyerType = $('#buyer_type');

            function applyBuyerTypeRules() {
                var buyerType = $.trim(String($buyerType.val() || '')).toLowerCase();
                var selectedCompanyId = $.trim(String($cid.val() || ''));
                var hasSelection = buyerType === 'company' || buyerType === 'individual';
                var isCompany = buyerType === 'company';
                var isIndividual = buyerType === 'individual';
                var isNewCompany = isCompany && selectedCompanyId === '__new__';

                $('.buyer-after-type').toggle(hasSelection);
                $('.buyer-company-selector-only').toggle(isCompany);
                $('.buyer-company-create-only').toggle(isNewCompany);
                $('.buyer-individual-only').toggle(isIndividual);
                $('.buyer-shared-contact-only').toggle(isIndividual || isNewCompany);
            }

            $buyerType.on('change', function () {
                applyBuyerTypeRules();
            });

            $cid.on('change', function () {
                applyBuyerTypeRules();
            });

            applyBuyerTypeRules();

            $country = $("#country");

            $country.select2();


            //
            $("#submit").click(function (e) {
                e.preventDefault();
                $('#ibox_form').block({ message:block_msg });
                $.post(base_url + 'contacts/add-post/', $( "#rform" ).serialize())
                    .done(function (data) {
                        var sbutton = $("#submit");
                        if ($.isNumeric(data)) {

                            window.location = base_url + 'contacts/view/' + data;
                        }
                        else {
                            $('#ibox_form').unblock();
                            var body = $("html, body");
                            body.animate({ scrollTop:0 }, '1000', 'swing');
                            $("#emsgbody").html(data);
                            $("#emsg").show("slow");
                        }
                    });
            });





            {if $config['show_business_number'] eq '1'}


            var $business_number = $("#business_number");

            var $address = $("#address");

            var $city = $("#city");

            var $state = $("#state");

            var $zip = $("#zip");



            function getBusinessDetails() {

                if(!/^\d+$/.test(String($cid.val() || '')) || $cid.val() === '0'){
                   // $business_number.val('');
                    return;
                }

                $.getJSON( base_url + "contacts/get_company_details/" +  $cid.val(), function( data ) {

                    console.log(data);

                    if(data.success === false){

                    }
                    else{

                        $business_number.val(data.business_number);

                        $address.val(data.address1);

                        $city.val(data.city);

                        $state.val(data.state);

                        $zip.val(data.zip);

                        $country.val(data.country).trigger('change');

                    }

                });
            }

            getBusinessDetails();


            $cid.change(function () {

                getBusinessDetails();


            });


            {/if}


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

                                    window.location = _url + 'contacts/add/customer/' + data + '/' + $cid.val();

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

            function generatePassword() {
                let length = 10,
                    charset = "abcdefghijklmnopqrstuvwxyz@#ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
                    retVal = "";
                for (let i = 0, n = charset.length; i < length; ++i) {
                    retVal += charset.charAt(Math.floor(Math.random() * n));
                }

                // Check if password contains at least one number
                if (!/\d/.test(retVal)) {
                    retVal = generatePassword();
                }

                return retVal;

            }

            const generate_password = document.getElementById('generate_password');
            generate_password.addEventListener('click', function (e) {
                e.preventDefault();
                const password = generatePassword();
                document.getElementById('password').value = password;
                document.getElementById('cpassword').value = password;

                //Show password
                document.getElementById('password').type = 'text';
                document.getElementById('cpassword').type = 'text';

            });

            {include file="includes/google-places-autocomplete.js"}


        });
    </script>



{/block}


