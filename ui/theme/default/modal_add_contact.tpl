<div class="mx-auto" style="max-width: 800px;">

    <div class="panel mb-0 rounded-0">
        <div class="panel-hdr">
            <h2>{$_L['Add New Contact']}</h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <form class="form-horizontal" id="rform">

                    <div class="mb-3"><label for="buyer_type">Buyer Type <span class="text-danger">*</span></label>
                        <select id="buyer_type" name="buyer_type" class="form-control" required onchange="if (window.applyInvoiceContactModalBuyerTypeRules) { window.applyInvoiceContactModalBuyerTypeRules(); }">
                            <option value="" selected>--Select Buyer Type--</option>
                            <option value="company">Company</option>
                            <option value="individual">Individual</option>
                        </select>
                    </div>

                    <div class="mb-3 buyer-after-type buyer-company-selector-only" style="display:none;"><label for="modal_company_id">Registered Company <span class="text-danger">*</span></label>

                        <select id="modal_company_id" name="cid" class="form-control" required onchange="if (window.applyInvoiceContactModalBuyerTypeRules) { window.applyInvoiceContactModalBuyerTypeRules(); }">
                            <option value="">--Select Registered Company--</option>
                            {foreach $companies as $company}
                                <option value="{$company['id']}">{$company['company_name']}</option>
                            {/foreach}
                            <option value="__new__">+ Create New Company</option>
                        </select>
                        <small class="help-block">Choose an existing registered company or select Create New Company.</small>
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-6 mb-3 buyer-after-type buyer-individual-only" style="display:none;"><label for="account">{$_L['Full Name']} <span class="text-danger">*</span></label>
                            <input type="text" id="account" name="account" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-individual-only" style="display:none;"><label for="id_iqama">ID / Iqama</label>

                            <input type="text" id="id_iqama" name="id_iqama" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="10 digits" >
                            <small class="help-block">Optional. If entered, it must be exactly 10 digits.</small>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="company">{$_L['Company Name']} <span class="text-danger">*</span></label>

                            <input type="text" id="company" name="company" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type" style="display:none;"><label for="phone">{$_L['Phone']} <span class="text-danger">*</span></label>

                            <input type="text" id="phone" name="phone" class="form-control js-digits-only" inputmode="numeric" required>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type" style="display:none;"><label for="email">{$_L['Email']}</label>

                            <input type="text" id="email" name="email" class="form-control" >

                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="building_number">Building Number <span class="text-danger">*</span></label>

                            <input type="text" id="building_number" name="building_number" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="e.g. 1234" required>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="company_url">{$_L['URL']}</label>

                            <input type="text" id="company_url" name="company_url" class="form-control" placeholder="http://">
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="logo_url">{$_L['Logo URL']}</label>

                            <input type="text" id="logo_url" name="logo_url" class="form-control">
                        </div>

                        <div class="col-12 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="m_address">{$_L['Address']} <span class="text-danger">*</span></label>

                            <input type="text" id="m_address" name="m_address" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="city">{$_L['City']} <span class="text-danger">*</span></label>

                            <input type="text" id="city" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="state">{$_L['State Region']} <span class="text-danger">*</span></label>

                            <input type="text" id="state" name="state" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="zip">{$_L['ZIP Postal Code']} <span class="text-danger">*</span></label>

                            <input type="text" id="zip" name="zip" class="form-control js-digits-only" inputmode="numeric" required>
                        </div>
                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="country">{$_L['Country']} <span class="text-danger">*</span></label>

                            <select name="country" class="country" id="country" class="form-control" required>
                                <option value="">{$_L['Select Country']}</option>
                                {$countries}
                            </select>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="vat_number">VAT Number <span class="text-danger">*</span></label>
                            <input type="text" id="vat_number" name="vat_number" class="form-control js-digits-only" inputmode="numeric" maxlength="15" placeholder="15 digits, starts and ends with 3" required>
                            <small class="help-block">Used for company VAT.</small>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-only" style="display:none;"><label for="crn_number">Unified No. (700#) <span class="text-danger">*</span></label>
                            <input type="text" id="crn_number" name="crn_number" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="10 digits" required>
                            <small class="help-block">Used for company Unified No. (700#).</small>
                        </div>
                    </div>


                    <div class="mb-3">
                        <button class="btn btn-primary contact_submit" type="submit" id="contact_submit"><i class="fal fa-check"></i> {$_L['Add Contact']}</button>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        function normalizeDigits(value) {
            return String(value || '')
                .replace(/[\u0660-\u0669]/g, function (d) { return String(d.charCodeAt(0) - 0x0660); })
                .replace(/[\u06F0-\u06F9]/g, function (d) { return String(d.charCodeAt(0) - 0x06F0); });
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
            var digitInputs = document.querySelectorAll('#ajax-modal .js-digits-only');

            for (var i = 0; i < digitInputs.length; i++) {
                applyDigitOnlyMask(digitInputs[i]);
            }
        }

        function applyModalBuyerTypeRules() {
            var buyerType = (document.getElementById('buyer_type') || {}).value || '';
            var hasSelection = buyerType === 'company' || buyerType === 'individual';
            var isCompany = buyerType === 'company';
            var isIndividual = buyerType === 'individual';

            var afterType = document.querySelectorAll('#ajax-modal .buyer-after-type');
            var companySelectorOnly = document.querySelectorAll('#ajax-modal .buyer-company-selector-only');
            var companyOnly = document.querySelectorAll('#ajax-modal .buyer-company-only');
            var individualOnly = document.querySelectorAll('#ajax-modal .buyer-individual-only');

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

        function fillModalCompanyFields(company) {
            var modalRoot = document.getElementById('ajax-modal') || document;
            var map = {
                company: company.company_name,
                company_url: company.url,
                logo_url: company.logo_url,
                vat_number: company.vat_number,
                crn_number: company.crn_number,
                building_number: company.building_number,
                m_address: company.address1,
                city: company.city,
                state: company.state,
                zip: company.zip
            };

            Object.keys(map).forEach(function (fieldId) {
                var el = modalRoot.querySelector('#' + fieldId);
                if (el) {
                    el.value = map[fieldId] || '';
                }
            });

            var countryEl = modalRoot.querySelector('#country');
            if (countryEl && company.country) {
                countryEl.value = company.country;
                if (window.jQuery) {
                    window.jQuery(countryEl).trigger('change');
                }
            }

            if (company.phone) {
                var phoneEl = modalRoot.querySelector('#phone');
                if (phoneEl && !phoneEl.value) {
                    phoneEl.value = company.phone;
                }
            }
        }

        function loadModalExistingCompanyDetails(companyId) {
            if (!window.jQuery || !/^\d+$/.test(String(companyId || ''))) {
                return;
            }

            var baseUrl = window.jQuery('#_url').val() || '';

            window.jQuery.getJSON(baseUrl + 'contacts/get_company_details/' + companyId, function (data) {
                if (data && data.success !== false) {
                    fillModalCompanyFields(data);
                }
            });
        }

        document.addEventListener('change', function (e) {
            if (!e || !e.target) {
                return;
            }

            if (e.target.id === 'buyer_type' || e.target.id === 'modal_company_id') {
                applyModalBuyerTypeRules();
            }

            if (e.target.id === 'modal_company_id' && e.target.value !== '__new__') {
                loadModalExistingCompanyDetails(e.target.value);
            }
        });

        document.addEventListener('input', function (e) {
            if (!e || !e.target || !e.target.classList || !e.target.classList.contains('js-digits-only')) {
                return;
            }

            applyDigitOnlyMask(e.target);
        });

        bindDigitOnlyInputs();
        setTimeout(applyModalBuyerTypeRules, 0);
    })();
</script>






