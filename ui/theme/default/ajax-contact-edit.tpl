<form class="form-horizontal" id="rform">

    <div class="mb-3"><label for="buyer_type"><span class="h6">Buyer Type</span><span class="text-danger">*</span></label>

        <select id="buyer_type" name="buyer_type" class="form-control" required>
            <option value="" {if ($d['buyer_type']|default:'') eq ''}selected{/if}>--Select Buyer Type--</option>
            <option value="company" {if ($d['buyer_type']|default:'') eq 'company'}selected{/if}>Company</option>
            <option value="individual" {if ($d['buyer_type']|default:'') eq 'individual'}selected{/if}>Individual</option>
        </select>
    </div>

    <div class="mb-3 buyer-after-type buyer-individual-only" style="display:none;"><label for="account"><span class="h6">{$_L['Full Name']}</span><span class="text-danger">*</span></label>

        <input type="text" id="account" name="account" class="form-control" value="{$d['account']}" required>
    </div>

    <div class="mb-3 buyer-after-type buyer-individual-only" style="display:none;"><label for="id_iqama"><span class="h6">ID / Iqama</span></label>

        <input type="text" id="id_iqama" name="id_iqama" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="10 digits" value="{if ($d['buyer_type']|default:'') eq 'individual'}{$d['entity_number']}{/if}">
        <small class="help-block">Optional. If entered, it must be exactly 10 digits.</small>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 ">
            <div class="mb-3"><label for="code"><span class="h6">{$_L['Code']}</span></label>

                <input type="text" id="code" name="code" class="form-control" value="{$d['code']}">
            </div>

        </div>
        <div class="col-md-6 buyer-after-type buyer-company-selector-only" style="display:none;">
            <div class="mb-3 h6"><label for="company_id"><span class="h6">Registered Company</span><span class="text-danger">*</span></label>

                <select id="company_id" name="company_id" class="form-control" required>
                    <option value="">--Select Registered Company--</option>
                    {foreach $companies as $company}
                        <option value="{$company['id']}" {if $d->cid eq ($company['id'])}selected{/if}>{$company['company_name']}</option>
                    {/foreach}
                    <option value="__new__">+ Create New Company</option>
                </select>
                <small class="help-block">Choose an existing registered company or select Create New Company.</small>
            </div>


        </div>
    </div>

    <div class="mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="company"><span class="h6">{$_L['Company Name']}</span><span class="text-danger">*</span></label>

        <input type="text" id="company" name="company" class="form-control" value="{if $linked_company}{$linked_company['company_name']}{/if}" required>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="company_url"><span class="h6">{$_L['URL']}</span></label>

                <input type="text" id="company_url" name="company_url" class="form-control" placeholder="http://" value="{if $linked_company}{$linked_company['url']}{/if}">
            </div>
        </div>
        <div class="col-md-6 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="logo_url"><span class="h6">{$_L['Logo URL']}</span></label>

                <input type="text" id="logo_url" name="logo_url" class="form-control" value="{if $linked_company}{$linked_company['logo_url']}{/if}">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="vat_number"><span class="h6">VAT Number</span><span class="text-danger">*</span></label>
                <input type="text" id="vat_number" name="vat_number" class="form-control js-digits-only" inputmode="numeric" maxlength="15" placeholder="15 digits, starts and ends with 3" value="{if $linked_company}{$linked_company['vat_number']}{else}{$d['tax_number']}{/if}" required>
                <small class="help-block">Used for company VAT.</small>
            </div>
        </div>
        <div class="col-md-6 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="crn_number"><span class="h6">Unified No. (700#)</span><span class="text-danger">*</span></label>
                <input type="text" id="crn_number" name="crn_number" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="10 digits" value="{if $linked_company}{$linked_company['crn_number']}{else}{$d['entity_number']}{/if}" required>
                <small class="help-block">Used for company Unified No. (700#).</small>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 buyer-after-type" style="display:none;">
            <div class="mb-3 h6"><label for="edit_email"><span class="h6">{$_L['Email']}</span></label>

                <input type="text" id="edit_email" name="edit_email" class="form-control" value="{$d['email']}">
            </div>


        </div>
        <div class="col-md-6 buyer-after-type" style="display:none;">
            <div class="mb-3 h6"><label for="edit_secondary_email"><span class="h6">{$_L['Secondary Email']}</span></label>
                <input type="text" id="edit_secondary_email" name="secondary_email" class="form-control" value="{$d['secondary_email']}">
            </div>


        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 buyer-after-type" style="display:none;">
            <div class="mb-3 h6"><label for="phone"><span class="h6">{$_L['Phone']}</span><span class="text-danger">*</span></label>

                <input type="text" id="phone" name="phone" class="form-control" value="{$d['phone']}" required>
            </div>

        </div>
        <div class="col-md-6">
            <div class="mb-3 h6"><label for="owner_id"><span class="h6">{$_L['Owner']}</span></label>

                <select class="form-select" name="owner_id" id="owner_id">
                    {foreach $owners as $owner}
                        <option value="{$owner->id}" {if $owner->id == $d->o}selected{/if} >{$owner->fullname}</option>
                    {/foreach}
                </select>
            </div>

        </div>
    </div>



    {if $config['show_business_number'] eq '1'}

        <div class="mb-3 h6">

            <label for="business_number"><span class="h6">{$config['label_business_number']}</span></label>

            <input type="text" id="business_number" name="business_number" class="form-control" value="{$d['business_number']}">
        </div>

    {/if}

    {if $config['fax_field']}

        <div class="mb-3 h6"><label for="phone"><span class="h6">{$_L['Fax']}</span></label>

            <input type="text" id="fax" name="fax" class="form-control" value="{$d['fax']}">
        </div>

    {/if}


    <div class="mb-3 h6 buyer-after-type buyer-company-only" style="display:none;"><label for="address"><span class="h6">{$_L['Address']}</span><span class="text-danger">*</span></label>

        <input type="text" id="address" name="address" class="form-control" value="{if $linked_company}{$linked_company['address1']}{else}{$d['address']}{/if}" required>
    </div>
    <div class="row mt-2">
        <div class="col-md-6 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="city"><span class="h6">{$_L['City']}</span><span class="text-danger">*</span></label>

                <input type="text" id="city" name="city" class="form-control" value="{if $linked_company}{$linked_company['city']}{else}{$d['city']}{/if}" required>
            </div>

        </div>
        <div class="col-md-6 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="state"><span class="h6">{$_L['State Region']}</span><span class="text-danger">*</span></label>
                <input type="text" id="state" name="state" class="form-control" value="{if $linked_company}{$linked_company['state']}{else}{$d['state']}{/if}" required>
            </div>

        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-4 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="zip"><span class="h6">{$_L['ZIP Postal Code']}</span><span class="text-danger">*</span></label>
                <input type="text" id="zip" name="zip" class="form-control" value="{if $linked_company}{$linked_company['zip']}{else}{$d['zip']}{/if}" required>
            </div>
        </div>
        <div class="col-md-4 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="building_number"><span class="h6">Building Number</span><span class="text-danger">*</span></label>
                <input type="text" id="building_number" name="building_number" class="form-control js-digits-only" value="{if $linked_company}{$linked_company['building_number']}{else}{$d['building_number']}{/if}" inputmode="numeric" maxlength="10" placeholder="e.g. 1234" required>
            </div>
        </div>
        <div class="col-md-4 buyer-after-type buyer-company-only" style="display:none;">
            <div class="mb-3 h6"><label for="country"><span class="h6">{$_L['Country']}</span><span class="text-danger">*</span></label>
                <select name="country" id="country" class="form-control" required>
                    <option value="">{$_L['Select Country']}</option>
                    {$countries}
                </select>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col">
                    <label for="lat"><span class="h6">{__('Latitude')}</span></label>
                    <input type="text" id="lat" name="lat" class="form-control" value="{$d['lat']|default:''}">
                </div>
                <div class="col">
                    <label for="lon"><span class="h6">{__('Longitude')}</span></label>
                    <input type="text" id="lon" name="lon" class="form-control" value="{$d['lon']|default:''}">
                </div>
            </div>
        </div>
    </div>



    <div class="row mt-2">
        <div class="col-md-7">
            <div class="mb-3"><label for="group"><span class="h6">{$_L['Group']} </span></label>
                <select class="form-select" name="group" id="group">
                    <option value="0" {if ($d['gid']) eq 0}selected{/if}>{$_L['None']}</option>
                    {foreach $gs as $g}
                        <option value="{$g['id']}" {if ($d['gid']) eq ($g['id'])}selected{/if}>{$g['gname']}</option>
                    {/foreach}
                </select>
            </div>


        </div>
        <div class="col-md-5">
            {if $config['accounting'] eq '1'}

                <div class="mb-3"><label class="col-md-2" for="currency"><span class="h6">{$_L['Currency']}</span></label>
                    <select id="currency" name="currency" class="form-control">

                        {foreach $currencies as $currency}
                            <option value="{$currency['id']}"
                                    {if ($d['currency']) eq ($currency['id'])}selected="selected" {/if}>{$currency['cname']}</option>
                            {foreachelse}
                            <option value="0">{$config['home_currency']}</option>
                        {/foreach}

                    </select>
                </div>

            {/if}
        </div>
    </div>


    {if $config['client_dashboard'] eq '1'}

    {if $config['customer_custom_username']}

        <div class="mb-3 h6"><label for="username"><span class="h6">{$_L['Username']} </span></label>

            <input type="text" id="username" name="username" class="form-control" value="{$d['username']}">
        </div>


        {/if}


        <div class="mb-3 h6"><label for="password"><span class="h6">{$_L['Password']}</span> </label>

            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">

            <span class="help-block text-info h6">{$_L['password_change_help']}</span>
        </div>

    {/if}



    {foreach $fs as $f}
        <div class="mb-3"><label for="cf{$f['id']}">{$f['fieldname']}</label>
            {if ($f['fieldtype']) eq 'text'}


                <input type="text" id="cf{$f['id']}" name="cf{$f['id']}" class="form-control" value="{if get_custom_field_value($f['id'],$d['id']) neq ''} {get_custom_field_value($f['id'],$d['id'])}{/if}">
                {if ($f['description']) neq ''}
                    <span class="help-block">{$f['description']}</span>
                {/if}

            {elseif ($f['fieldtype']) eq 'password'}

                <input type="password" id="cf{$f['id']}" name="cf{$f['id']}" class="form-control" value="{if get_custom_field_value($f['id'],$d['id']) neq ''} {get_custom_field_value($f['id'],$d['id'])}{/if}">
                {if ($f['description']) neq ''}
                    <span class="help-block">{$f['description']}</span>
                {/if}

            {elseif ($f['fieldtype']) eq 'dropdown'}
                <select id="cf{$f['id']}" name="cf{$f['id']}" class="form-control">
                    {foreach explode(',',$f['fieldoptions']) as $fo}
                        <option value="{$fo}" {if get_custom_field_value($f['id'],$d['id']) eq $fo} selected="selected" {/if}>{$fo}</option>
                    {/foreach}
                </select>
                {if ($f['description']) neq ''}
                    <span class="help-block">{$f['description']}</span>
                {/if}

            {elseif ($f['fieldtype']) eq 'textarea'}

                <textarea id="cf{$f['id']}" name="cf{$f['id']}" class="form-control" rows="3">{if get_custom_field_value($f['id'],$d['id']) neq ''} {get_custom_field_value($f['id'],$d['id'])}{/if}</textarea>
                {if ($f['description']) neq ''}
                    <span class="help-block">{$f['description']}</span>
                {/if}

            {else}
            {/if}
        </div>
    {/foreach}

    <div class="mb-3"><label for="cid"><span class="h6">{$_L['Type']}</span> </label>

        <div class="checkbox">
            <label>
                <input type="checkbox" class="custom-checkbox" name="customer" value="Customer" {if $d->type == 'Customer,Supplier' || $d->type == 'Customer' } checked {/if}>
                {$_L['Customer']}
            </label>
        </div>

        {if $config['suppliers'] eq '1'}
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="custom-checkbox" name="supplier" value="Supplier"  {if $d->type == 'Customer,Supplier' || $d->type == 'Supplier' } checked {/if}>
                    {$_L['Supplier']}
                </label>
            </div>
        {/if}
    </div>

    <div class="mb-3"><label for="tags"><span class="h6">{$_L['Tags']}</span></label>

        <select name="tags[]" id="tags"  class="form-control" multiple="multiple">
            {foreach $tags as $tag}
                <option value="{$tag['text']}" {if in_array($tag['text'],$dtags)}selected="selected"{/if}>{$tag['text']}</option>
            {/foreach}

        </select>
    </div>



    <div class="mb-3">
        <button class="btn btn-primary" type="submit" id="submit"><i class="fal fa-check"></i> {$_L['Submit']}</button>
    </div>



    <input type="hidden" name="fcid" id="fcid" value="{$d['id']}">


</form>

<script>
    (function () {
        var root = document.getElementById('application_ajaxrender') || document;

        function normalizeDigits(value) {
            return String(value || '')
                .replace(/[٠-٩]/g, function (d) { return String(d.charCodeAt(0) - 0x0660); })
                .replace(/[۰-۹]/g, function (d) { return String(d.charCodeAt(0) - 0x06F0); });
        }

        function stripToDigits(value) {
            return normalizeDigits(value).replace(/\D+/g, '');
        }

        function applyDigitOnlyMask(input) {
            if (!input) {
                return;
            }

            var sanitized = stripToDigits(input.value);

            if (input.value !== sanitized) {
                input.value = sanitized;
            }
        }

        function bindDigitOnlyInputs() {
            var digitInputs = root.querySelectorAll('.js-digits-only');

            for (var i = 0; i < digitInputs.length; i++) {
                applyDigitOnlyMask(digitInputs[i]);
            }
        }

        function applyEditBuyerTypeRules() {
            var buyerType = (root.querySelector('#buyer_type') || {}).value || '';
            var hasSelection = buyerType === 'company' || buyerType === 'individual';
            var isCompany = buyerType === 'company';
            var isIndividual = buyerType === 'individual';

            var afterType = root.querySelectorAll('.buyer-after-type');
            var companySelectorOnly = root.querySelectorAll('.buyer-company-selector-only');
            var companyOnly = root.querySelectorAll('.buyer-company-only');
            var individualOnly = root.querySelectorAll('.buyer-individual-only');

            for (var i = 0; i < afterType.length; i++) {
                afterType[i].style.display = hasSelection ? '' : 'none';
            }

            for (var j = 0; j < companySelectorOnly.length; j++) {
                companySelectorOnly[j].style.display = isCompany ? '' : 'none';
            }

            for (var k = 0; k < companyOnly.length; k++) {
                companyOnly[k].style.display = isCompany ? '' : 'none';
            }

            for (var m = 0; m < individualOnly.length; m++) {
                individualOnly[m].style.display = isIndividual ? '' : 'none';
            }
        }

        function fillCompanyFields(company) {
            var map = {
                company: company.company_name,
                company_url: company.url,
                logo_url: company.logo_url,
                vat_number: company.vat_number,
                crn_number: company.crn_number,
                building_number: company.building_number,
                address: company.address1,
                city: company.city,
                state: company.state,
                zip: company.zip
            };

            Object.keys(map).forEach(function (fieldId) {
                var el = root.querySelector('#' + fieldId);
                if (el) {
                    el.value = map[fieldId] || '';
                }
            });

            var countryEl = root.querySelector('#country');
            if (countryEl && company.country) {
                countryEl.value = company.country;
                if (window.jQuery) {
                    window.jQuery(countryEl).trigger('change');
                }
            }

            if (company.phone) {
                var phoneEl = root.querySelector('#phone');
                if (phoneEl && !phoneEl.value) {
                    phoneEl.value = company.phone;
                }
            }
        }

        function loadExistingCompanyDetails(companyId) {
            if (!window.jQuery || !/^\d+$/.test(String(companyId || ''))) {
                return;
            }

            var baseUrl = '{$_url}';

            window.jQuery.getJSON(baseUrl + 'contacts/get_company_details/' + companyId, function (data) {
                if (data && data.success !== false) {
                    fillCompanyFields(data);
                }
            });
        }

        var buyerTypeEl = root.querySelector('#buyer_type');
        var companySelectEl = root.querySelector('#company_id');

        if (buyerTypeEl) {
            buyerTypeEl.addEventListener('change', applyEditBuyerTypeRules);
        }
        if (companySelectEl) {
            companySelectEl.addEventListener('change', function () {
                applyEditBuyerTypeRules();
                if (companySelectEl.value !== '__new__') {
                    loadExistingCompanyDetails(companySelectEl.value);
                }
            });
        }

        root.addEventListener('input', function (e) {
            if (!e || !e.target || !e.target.classList || !e.target.classList.contains('js-digits-only')) {
                return;
            }

            applyDigitOnlyMask(e.target);
        });

        bindDigitOnlyInputs();
        applyEditBuyerTypeRules();
    })();
</script>
