<div class="mx-auto" style="max-width: 800px;">

    <div class="panel mb-0 rounded-0">
        <div class="panel-hdr">
            <h2>{$_L['Add New Contact']}</h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <form class="form-horizontal" id="rform">

                    <div class="mb-3"><label for="buyer_type">Buyer Type <span class="text-danger">*</span></label>
                        <select id="buyer_type" name="buyer_type" class="form-control" onchange="if (window.applyInvoiceContactModalBuyerTypeRules) { window.applyInvoiceContactModalBuyerTypeRules(); }">
                            <option value="" selected>--Select Buyer Type--</option>
                            <option value="company">Company</option>
                            <option value="individual">Individual</option>
                        </select>
                    </div>

                    <div class="mb-3 buyer-after-type buyer-company-selector-only" style="display:none;"><label for="modal_company_id">Registered Company <span class="text-danger">*</span></label>

                        <select id="modal_company_id" name="cid" class="form-control" onchange="if (window.applyInvoiceContactModalBuyerTypeRules) { window.applyInvoiceContactModalBuyerTypeRules(); }">
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
                            <input type="text" id="account" name="account" class="form-control" >
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-individual-only" style="display:none;"><label for="id_iqama">ID / Iqama</label>

                            <input type="text" id="id_iqama" name="id_iqama" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="10 digits" >
                            <small class="help-block">Optional. If entered, it must be exactly 10 digits.</small>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="company">{$_L['Company Name']} <span class="text-danger">*</span></label>

                            <input type="text" id="company" name="company" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-shared-contact-only" style="display:none;"><label for="phone">{$_L['Phone']} <span class="text-danger">*</span></label>

                            <input type="text" id="phone" name="phone" class="form-control js-digits-only" inputmode="numeric" >
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-shared-contact-only" style="display:none;"><label for="email">{$_L['Email']}</label>

                            <input type="text" id="email" name="email" class="form-control" >

                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="building_number">Building Number <span class="text-danger">*</span></label>

                            <input type="text" id="building_number" name="building_number" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="e.g. 1234" >
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="company_url">{$_L['URL']}</label>

                            <input type="text" id="company_url" name="company_url" class="form-control" placeholder="http://">
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="logo_url">{$_L['Logo URL']}</label>

                            <input type="text" id="logo_url" name="logo_url" class="form-control">
                        </div>

                        <div class="col-12 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="m_address">{$_L['Address']} <span class="text-danger">*</span></label>

                            <input type="text" id="m_address" name="m_address" class="form-control" >
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="city">{$_L['City']} <span class="text-danger">*</span></label>

                            <input type="text" id="city" name="city" class="form-control" >
                        </div>
                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="state">{$_L['State Region']} <span class="text-danger">*</span></label>

                            <input type="text" id="state" name="state" class="form-control" >
                        </div>
                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="zip">{$_L['ZIP Postal Code']} <span class="text-danger">*</span></label>

                            <input type="text" id="zip" name="zip" class="form-control js-digits-only" inputmode="numeric" >
                        </div>
                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="country">{$_L['Country']} <span class="text-danger">*</span></label>

                            <select name="country" class="country" id="country" class="form-control">
                                <option value="">{$_L['Select Country']}</option>
                                {$countries}
                            </select>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="vat_number">VAT Number <span class="text-danger">*</span></label>
                            <input type="text" id="vat_number" name="vat_number" class="form-control js-digits-only" inputmode="numeric" maxlength="15" placeholder="15 digits, starts and ends with 3">
                            <small class="help-block">Used for company VAT.</small>
                        </div>

                        <div class="col-md-6 mb-3 buyer-after-type buyer-company-create-only" style="display:none;"><label for="crn_number">Unified No. (700#) <span class="text-danger">*</span></label>
                            <input type="text" id="crn_number" name="crn_number" class="form-control js-digits-only" inputmode="numeric" maxlength="10" placeholder="10 digits">
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
            var modalRoot = document.getElementById('ajax-modal') || document;
            var companySelect = modalRoot.querySelector('#modal_company_id');
            var hasSelection = buyerType === 'company' || buyerType === 'individual';
            var isCompany = buyerType === 'company';
            var isIndividual = buyerType === 'individual';
            var isNewCompany = isCompany && companySelect && companySelect.value === '__new__';

            var afterType = document.querySelectorAll('#ajax-modal .buyer-after-type');
            var companySelectorOnly = document.querySelectorAll('#ajax-modal .buyer-company-selector-only');
            var companyCreateOnly = document.querySelectorAll('#ajax-modal .buyer-company-create-only');
            var individualOnly = document.querySelectorAll('#ajax-modal .buyer-individual-only');
            var sharedContactOnly = document.querySelectorAll('#ajax-modal .buyer-shared-contact-only');

            for (var i = 0; i < afterType.length; i++) {
                afterType[i].style.display = hasSelection ? '' : 'none';
            }

            for (var j = 0; j < companySelectorOnly.length; j++) {
                companySelectorOnly[j].style.display = isCompany ? '' : 'none';
            }

            for (var k = 0; k < companyCreateOnly.length; k++) {
                companyCreateOnly[k].style.display = isNewCompany ? '' : 'none';
            }

            for (var m = 0; m < individualOnly.length; m++) {
                individualOnly[m].style.display = isIndividual ? '' : 'none';
            }

            for (var n = 0; n < sharedContactOnly.length; n++) {
                sharedContactOnly[n].style.display = (isIndividual || isNewCompany) ? '' : 'none';
            }
        }

        document.addEventListener('change', function (e) {
            if (!e || !e.target) {
                return;
            }

            if (e.target.id === 'buyer_type' || e.target.id === 'modal_company_id') {
                applyModalBuyerTypeRules();
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






