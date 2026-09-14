{extends file="$layouts_admin"}

{block name="content"}
    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>ZATCA Settings</h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <form method="post" action="{$_url}settings/zatca-post">
                            <div class="mb-3">
                                <label class="form-label">Enable ZATCA</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="zatca_enabled" name="zatca_enabled" value="1" {if !empty($config['zatca_enabled']) && $config['zatca_enabled'] eq '1'}checked{/if}>
                                    <label class="form-check-label" for="zatca_enabled">Enable ZATCA Integration</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Enable Phase 2</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="zatca_phase2_enabled" name="zatca_phase2_enabled" value="1" {if !empty($config['zatca_phase2_enabled']) && $config['zatca_phase2_enabled'] eq '1'}checked{/if}>
                                    <label class="form-check-label" for="zatca_phase2_enabled">Enable Phase 2 Clearance/Reporting Flow</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="zatca_environment" class="form-label">Environment</label>
                                <select class="form-select" id="zatca_environment" name="zatca_environment">
                                    {foreach $zatca_endpoint_options as $key => $label}
                                        <option value="{$key}" {if !empty($config['zatca_environment']) && $config['zatca_environment'] eq $key}selected{/if}>{$label}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="zatca_api_base_url" class="form-label">ZATCA API Base URL</label>
                                <input type="text" class="form-control" id="zatca_api_base_url" name="zatca_api_base_url" value="{if !empty($config['zatca_api_base_url'])}{$config['zatca_api_base_url']}{/if}">
                                <small class="text-muted">Sandbox default: https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal</small>
                            </div>

                            <div class="mb-3">
                                <label for="zatca_invoice_type" class="form-label">ZATCA Invoice Type</label>
                                <select class="form-select" id="zatca_invoice_type" name="zatca_invoice_type">
                                    {foreach $zatca_invoice_type_options as $key => $label}
                                        <option value="{$key}" {if !empty($config['zatca_invoice_type']) && $config['zatca_invoice_type'] eq $key}selected{/if}>{$label}</option>
                                    {/foreach}
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ZATCA Onboarding Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="zatca_setup_mode_csr" name="zatca_setup_mode" value="csr_otp" {if empty($config['zatca_setup_mode']) || $config['zatca_setup_mode'] eq 'csr_otp'}checked{/if}>
                                    <label class="form-check-label" for="zatca_setup_mode_csr">CSR + OTP (Step 1: Fetch/Refresh Compliance CSID) <span class="badge bg-success ms-1">Recommended</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="zatca_setup_mode_portal" name="zatca_setup_mode" value="portal_keys" {if !empty($config['zatca_setup_mode']) && $config['zatca_setup_mode'] eq 'portal_keys'}checked{/if}>
                                    <label class="form-check-label" for="zatca_setup_mode_portal">Portal Generated Keys (Manual Paste)</label>
                                </div>
                                <small class="text-muted">Choose one method. CSR + OTP uses step-by-step onboarding: first fetch Compliance CSID, then run Verify again to fetch Production CSID. Portal Keys lets you paste already-generated credentials.</small>
                            </div>

                            <div id="zatca_setup_mode_notice" class="alert alert-warning">
                                Use one onboarding method only. Do not mix CSR/OTP values with manually pasted portal credentials from a different onboarding attempt.
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="zatca_seller_name" class="form-label">Seller Name</label>
                                <input type="text" class="form-control" id="zatca_seller_name" name="zatca_seller_name" value="{if !empty($config['zatca_seller_name'])}{$config['zatca_seller_name']}{/if}">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zatca_vat_number" class="form-label">VAT Number</label>
                                        <input type="text" class="form-control" id="zatca_vat_number" name="zatca_vat_number" value="{if !empty($config['zatca_vat_number'])}{$config['zatca_vat_number']}{/if}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zatca_seller_crn" class="form-label">Unified No. (700#)</label>
                                        <input type="text" class="form-control" id="zatca_seller_crn" name="zatca_seller_crn" value="{if !empty($config['zatca_seller_crn'])}{$config['zatca_seller_crn']}{/if}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="zatca_building_no" class="form-label">Building No.</label>
                                        <input type="text" class="form-control" id="zatca_building_no" name="zatca_building_no" value="{if !empty($config['zatca_building_no'])}{$config['zatca_building_no']}{/if}">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="zatca_street_name" class="form-label">Street Name</label>
                                        <input type="text" class="form-control" id="zatca_street_name" name="zatca_street_name" value="{if !empty($config['zatca_street_name'])}{$config['zatca_street_name']}{/if}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="zatca_district" class="form-label">District</label>
                                        <input type="text" class="form-control" id="zatca_district" name="zatca_district" value="{if !empty($config['zatca_district'])}{$config['zatca_district']}{/if}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="zatca_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="zatca_city" name="zatca_city" value="{if !empty($config['zatca_city'])}{$config['zatca_city']}{/if}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="zatca_postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="zatca_postal_code" name="zatca_postal_code" value="{if !empty($config['zatca_postal_code'])}{$config['zatca_postal_code']}{/if}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="zatca_country_code" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="zatca_country_code" name="zatca_country_code" maxlength="2" value="{if !empty($config['zatca_country_code'])}{$config['zatca_country_code']}{else}SA{/if}">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <h5>Compliance Credentials</h5>
                            <div class="text-muted small mb-2">Fields marked <span class="text-danger">*</span> are required for the selected onboarding method.</div>

                            <div id="zatca_csr_otp_section">

                            <div class="mb-3">
                                <label for="zatca_csr_content" class="form-label">CSR (inline PEM text) <span id="req_zatca_csr_content" class="text-danger d-none">*</span></label>
                                <textarea class="form-control" id="zatca_csr_content" name="zatca_csr_content" rows="3">{if !empty($config['zatca_csr_content'])}{$config['zatca_csr_content']}{/if}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="zatca_otp" class="form-label">OTP <span id="req_zatca_otp" class="text-danger d-none">*</span></label>
                                <input type="text" class="form-control" id="zatca_otp" name="zatca_otp" value="" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="off" placeholder="Enter fresh 6-digit OTP from selected portal">
                                <small class="form-text text-warning d-block mt-2">
                                    <strong>⚠️ Important:</strong> Generate OTP from ZATCA portal immediately before clicking Verify. OTP expires within 1 hour. Do not wait or reuse old OTPs.
                                </small>
                            </div>

                            </div>

                            <div id="zatca_portal_keys_section">

                            <div class="mb-3">
                                <label for="zatca_binary_security_token" class="form-label">Binary Security Token <span id="req_zatca_binary_security_token" class="text-danger d-none">*</span></label>
                                <input type="text" class="form-control fetched-key-field" id="zatca_binary_security_token" name="zatca_binary_security_token" value="{if !empty($config['zatca_binary_security_token'])}{$config['zatca_binary_security_token']}{/if}" {if !empty($config['zatca_binary_security_token'])}readonly{/if}>
                            </div>

                            <div class="mb-3">
                                <label for="zatca_secret" class="form-label">Secret <span id="req_zatca_secret" class="text-danger d-none">*</span></label>
                                <input type="text" class="form-control fetched-key-field" id="zatca_secret" name="zatca_secret" value="{if !empty($config['zatca_secret'])}{$config['zatca_secret']}{/if}" {if !empty($config['zatca_secret'])}readonly{/if}>
                            </div>

                            <div class="mb-3">
                                <label for="zatca_compliance_request_id" class="form-label">Compliance Request ID</label>
                                <input type="text" class="form-control fetched-key-field" id="zatca_compliance_request_id" name="zatca_compliance_request_id" value="{if !empty($config['zatca_compliance_request_id'])}{$config['zatca_compliance_request_id']}{/if}" {if !empty($config['zatca_compliance_request_id'])}readonly{/if}>
                            </div>

                            <div class="mb-3">
                                <label for="zatca_certificate" class="form-label">Certificate (PEM text or server file path) <span id="req_zatca_certificate" class="text-danger d-none">*</span></label>
                                <textarea class="form-control fetched-key-field" id="zatca_certificate" name="zatca_certificate" rows="3" {if !empty($config['zatca_certificate'])}readonly{/if}>{if !empty($config['zatca_certificate'])}{$config['zatca_certificate']}{/if}</textarea>
                            </div>

                            </div>

                            <div class="mb-3">
                                <label for="zatca_private_key_passphrase" class="form-label">Private Key Passphrase</label>
                                <input type="text" class="form-control" id="zatca_private_key_passphrase" name="zatca_private_key_passphrase" value="{if !empty($config['zatca_private_key_passphrase'])}{$config['zatca_private_key_passphrase']}{/if}">
                            </div>

                            <div class="mb-3">
                                <label for="zatca_private_key" class="form-label">Private Key (inline PEM text) <span id="req_zatca_private_key" class="text-danger d-none">*</span></label>
                                <textarea class="form-control" id="zatca_private_key" name="zatca_private_key" rows="3">{if !empty($config['zatca_private_key'])}{$config['zatca_private_key']}{/if}</textarea>
                            </div>

                            <div class="row" id="zatca_fetched_credentials_section">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zatca_compliance_csid" class="form-label">Compliance CSID</label>
                                        <input type="text" class="form-control fetched-key-field" id="zatca_compliance_csid" name="zatca_compliance_csid" value="{if !empty($config['zatca_compliance_csid'])}{$config['zatca_compliance_csid']}{/if}" {if !empty($config['zatca_compliance_csid'])}readonly{/if}>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zatca_compliance_secret" class="form-label">Compliance Secret</label>
                                        <input type="text" class="form-control fetched-key-field" id="zatca_compliance_secret" name="zatca_compliance_secret" value="{if !empty($config['zatca_compliance_secret'])}{$config['zatca_compliance_secret']}{/if}" {if !empty($config['zatca_compliance_secret'])}readonly{/if}>
                                    </div>
                                </div>
                            </div>

                            <div id="zatca_production_credentials_section">
                            <h5>Production Credentials</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zatca_production_csid" class="form-label">Production CSID</label>
                                        <input type="text" class="form-control fetched-key-field" id="zatca_production_csid" name="zatca_production_csid" value="{if !empty($config['zatca_production_csid'])}{$config['zatca_production_csid']}{/if}" {if !empty($config['zatca_production_csid'])}readonly{/if}>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zatca_production_binary_security_token" class="form-label">Production Binary Security Token</label>
                                        <input type="text" class="form-control fetched-key-field" id="zatca_production_binary_security_token" name="zatca_production_binary_security_token" value="{if !empty($config['zatca_production_binary_security_token'])}{$config['zatca_production_binary_security_token']}{/if}" {if !empty($config['zatca_production_binary_security_token'])}readonly{/if}>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="zatca_production_secret" class="form-label">Production Secret</label>
                                        <input type="text" class="form-control fetched-key-field" id="zatca_production_secret" name="zatca_production_secret" value="{if !empty($config['zatca_production_secret'])}{$config['zatca_production_secret']}{/if}" {if !empty($config['zatca_production_secret'])}readonly{/if}>
                                    </div>
                                </div>
                            </div>
                            </div>

                            <div class="form-check mb-3" id="unlock_fetched_keys_wrapper">
                                <input class="form-check-input" type="checkbox" id="unlock_fetched_keys">
                                <label class="form-check-label" for="unlock_fetched_keys">
                                    Unlock fetched keys for manual edit/re-fetch
                                </label>
                                <div class="text-muted small">By default, fetched keys are read-only to prevent accidental overwrite.</div>
                            </div>

                            <div id="zatca_inline_validation" class="alert alert-danger d-none" role="alert"></div>

                            {assign var="isProductionEnv" value=!empty($config['zatca_environment']) && $config['zatca_environment'] eq 'production'}
                            {assign var="hasComplianceToken" value=!empty($config['zatca_binary_security_token']) || !empty($config['zatca_compliance_csid'])}
                            {assign var="hasComplianceSecret" value=!empty($config['zatca_secret']) || !empty($config['zatca_compliance_secret'])}
                            {assign var="hasComplianceRequestId" value=!empty($config['zatca_compliance_request_id'])}
                            {assign var="hasProductionToken" value=!empty($config['zatca_production_binary_security_token']) || !empty($config['zatca_production_csid'])}
                            {assign var="hasProductionSecret" value=!empty($config['zatca_production_secret'])}

                            {assign var="step1Disabled" value=false}
                            {assign var="step1Reason" value=""}
                            {if $hasProductionToken && $hasProductionSecret}
                                {assign var="step1Disabled" value=true}
                                {assign var="step1Reason" value="Step 1 already completed/covered by existing production credentials."}
                            {elseif $hasComplianceToken && $hasComplianceSecret && $hasComplianceRequestId}
                                {assign var="step1Disabled" value=true}
                                {assign var="step1Reason" value="Step 1 already completed: compliance credentials are already saved."}
                            {/if}

                            {* Step 2 is the 6 required compliance checks - must pass before Production CSID (Step 3). *}
                            {assign var="step2Disabled" value=false}
                            {assign var="step2Reason" value=""}
                            {if !($hasComplianceToken && $hasComplianceSecret)}
                                {assign var="step2Disabled" value=true}
                                {assign var="step2Reason" value="Step 2 requires compliance credentials first."}
                            {/if}

                            {* Step 3 is Production CSID - gated on Step 2's 6/6 compliance checks having passed. *}
                            {assign var="step3Disabled" value=false}
                            {assign var="step3Reason" value=""}
                            {if $hasProductionToken && $hasProductionSecret}
                                {assign var="step3Disabled" value=true}
                                {assign var="step3Reason" value="Step 3 already completed: production credentials are already saved."}
                            {elseif !$zatca_compliance_all_passed}
                                {assign var="step3Disabled" value=true}
                                {assign var="step3Reason" value="Step 3 requires all 6 compliance checks (Step 2) to pass first - see the count on the Step 2 button."}
                            {/if}

                            <button type="submit" class="btn btn-primary">{$_L['Submit']}</button>
                            <button type="submit" class="btn btn-secondary ms-2" id="zatca_verify_button" formaction="{$_url}settings/zatca-verify" {if $step1Disabled}disabled title="{$step1Reason|escape}"{/if}>Run Step 1 (Compliance CSID){if $step1Disabled} - Skipped{/if}</button>
                            <button type="submit" class="btn btn-warning ms-2" id="zatca_compliance_check_button" formaction="{$_url}settings/zatca-compliance-check" {if $step2Disabled}disabled title="{$step2Reason|escape}"{/if}>Run Step 2 (Compliance Invoice Check) - {$zatca_compliance_scenarios_passed}/{$zatca_compliance_scenarios_total} passed{if $step2Disabled} - Blocked{/if}</button>
                            <button type="submit" class="btn btn-info ms-2" id="zatca_step2_button" formaction="{$_url}settings/zatca-production-step2" {if $step3Disabled}disabled title="{$step3Reason|escape}"{/if}>Run Step 3 (Production CSID){if $step3Disabled} - Blocked{/if}</button>
                            <a href="{$_url}settings/zatca-clear-and-reset" class="btn btn-danger ms-2" onclick="return confirm('This will permanently delete all local ZATCA credentials and certificates. You will need to reset/revoke the device in ZATCA portal first. Continue?');">Clear Local ZATCA Data</a>

                            <div class="mt-2">
                                {if $step1Disabled}
                                    <div class="text-muted small">Step 1 disabled: {$step1Reason|escape}</div>
                                {/if}
                                {if $step2Disabled}
                                    <div class="text-muted small">Step 2 disabled: {$step2Reason|escape}</div>
                                {/if}
                                {if $step3Disabled}
                                    <div class="text-muted small">Step 3 disabled: {$step3Reason|escape}</div>
                                {/if}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {literal}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var unlockToggle = document.getElementById('unlock_fetched_keys');
            var fetchedFields = document.querySelectorAll('.fetched-key-field');
            var setupModeInputs = document.querySelectorAll('input[name="zatca_setup_mode"]');
            var csrOtpSection = document.getElementById('zatca_csr_otp_section');
            var portalKeysSection = document.getElementById('zatca_portal_keys_section');
            var fetchedCredentialsSection = document.getElementById('zatca_fetched_credentials_section');
            var productionCredentialsSection = document.getElementById('zatca_production_credentials_section');
            var zatcaEnabledCheckbox = document.getElementById('zatca_enabled');
            var zatcaForm = document.querySelector('form[action*="settings/zatca-post"]');
            var inlineValidationBox = document.getElementById('zatca_inline_validation');
            var unlockToggleWrapper = document.getElementById('unlock_fetched_keys_wrapper');
            var verifyButton = document.getElementById('zatca_verify_button');
            var setupModeNotice = document.getElementById('zatca_setup_mode_notice');
            var lastSubmitter = null;

            if (unlockToggle && fetchedFields.length) {
                fetchedFields.forEach(function (field) {
                    field.dataset.initialReadonly = field.hasAttribute('readonly') ? '1' : '0';
                });

                unlockToggle.addEventListener('change', function () {
                    var shouldUnlock = unlockToggle.checked;

                    fetchedFields.forEach(function (field) {
                        if (shouldUnlock) {
                            field.removeAttribute('readonly');
                        } else if (field.dataset.initialReadonly === '1') {
                            field.setAttribute('readonly', 'readonly');
                        }
                    });
                });
            }

            var fieldEls = {
                csr: document.getElementById('zatca_csr_content'),
                otp: document.getElementById('zatca_otp'),
                token: document.getElementById('zatca_binary_security_token'),
                secret: document.getElementById('zatca_secret'),
                cert: document.getElementById('zatca_certificate'),
                privateKey: document.getElementById('zatca_private_key')
            };

            var normalizeOtpInput = function (value) {
                if (!value) {
                    return '';
                }

                var mapped = String(value)
                    .replace(/[٠]/g, '0').replace(/[١]/g, '1').replace(/[٢]/g, '2').replace(/[٣]/g, '3').replace(/[٤]/g, '4')
                    .replace(/[٥]/g, '5').replace(/[٦]/g, '6').replace(/[٧]/g, '7').replace(/[٨]/g, '8').replace(/[٩]/g, '9')
                    .replace(/[۰]/g, '0').replace(/[۱]/g, '1').replace(/[۲]/g, '2').replace(/[۳]/g, '3').replace(/[۴]/g, '4')
                    .replace(/[۵]/g, '5').replace(/[۶]/g, '6').replace(/[۷]/g, '7').replace(/[۸]/g, '8').replace(/[۹]/g, '9');

                return mapped.replace(/[^0-9]/g, '').slice(0, 6);
            };

            if (fieldEls.otp) {
                fieldEls.otp.addEventListener('input', function () {
                    var normalized = normalizeOtpInput(fieldEls.otp.value);
                    if (fieldEls.otp.value !== normalized) {
                        fieldEls.otp.value = normalized;
                    }
                });
            }

            var requiredMarks = {
                csr: document.getElementById('req_zatca_csr_content'),
                otp: document.getElementById('req_zatca_otp'),
                token: document.getElementById('req_zatca_binary_security_token'),
                secret: document.getElementById('req_zatca_secret'),
                cert: document.getElementById('req_zatca_certificate'),
                privateKey: document.getElementById('req_zatca_private_key')
            };

            var setRequiredState = function (el, marker, isRequired) {
                if (marker) {
                    if (isRequired) {
                        marker.classList.remove('d-none');
                    } else {
                        marker.classList.add('d-none');
                    }
                }
            };

            var syncSetupModeSections = function () {
                if (!setupModeInputs.length || !csrOtpSection || !portalKeysSection) {
                    return;
                }

                var selectedMode = 'csr_otp';
                setupModeInputs.forEach(function (input) {
                    if (input.checked) {
                        selectedMode = input.value;
                    }
                });

                if (selectedMode === 'portal_keys') {
                    csrOtpSection.style.display = 'none';
                    portalKeysSection.style.display = '';
                    if (fetchedCredentialsSection) {
                        fetchedCredentialsSection.style.display = '';
                    }
                    if (productionCredentialsSection) {
                        productionCredentialsSection.style.display = '';
                    }
                    if (unlockToggleWrapper) {
                        unlockToggleWrapper.style.display = '';
                    }
                    if (verifyButton) {
                        verifyButton.textContent = 'Verify Manual Keys';
                    }

                    setRequiredState(fieldEls.csr, requiredMarks.csr, false);
                    setRequiredState(fieldEls.otp, requiredMarks.otp, false);
                    setRequiredState(fieldEls.token, requiredMarks.token, true);
                    setRequiredState(fieldEls.secret, requiredMarks.secret, true);
                    setRequiredState(fieldEls.cert, requiredMarks.cert, true);
                    setRequiredState(fieldEls.privateKey, requiredMarks.privateKey, true);

                    if (setupModeNotice) {
                        setupModeNotice.innerHTML = 'Portal Generated Keys mode expects a matching set of manually pasted credentials. Use the same private key, certificate, token, and secret from one onboarding attempt only.';
                    }
                } else {
                    csrOtpSection.style.display = '';
                    portalKeysSection.style.display = 'none';
                    if (fetchedCredentialsSection) {
                        fetchedCredentialsSection.style.display = 'none';
                    }
                    if (productionCredentialsSection) {
                        productionCredentialsSection.style.display = 'none';
                    }
                    if (unlockToggleWrapper) {
                        unlockToggleWrapper.style.display = 'none';
                    }
                    if (verifyButton) {
                        verifyButton.textContent = 'Run Step 1 (Compliance CSID)';
                    }

                    setRequiredState(fieldEls.csr, requiredMarks.csr, true);
                    setRequiredState(fieldEls.otp, requiredMarks.otp, true);
                    setRequiredState(fieldEls.token, requiredMarks.token, false);
                    setRequiredState(fieldEls.secret, requiredMarks.secret, false);
                    setRequiredState(fieldEls.cert, requiredMarks.cert, false);
                    setRequiredState(fieldEls.privateKey, requiredMarks.privateKey, true);

                    if (setupModeNotice) {
                        setupModeNotice.innerHTML = 'CSR + OTP mode is recommended for first-time onboarding. Use the CSR generated from your private key, then let the system fetch matching ZATCA credentials.';
                    }
                }
            };

            setupModeInputs.forEach(function (input) {
                input.addEventListener('change', syncSetupModeSections);
            });

            var clearInlineValidation = function () {
                if (!inlineValidationBox) {
                    return;
                }

                inlineValidationBox.classList.add('d-none');
                inlineValidationBox.innerHTML = '';
            };

            var markFieldInvalid = function (el, invalid) {
                if (!el) {
                    return;
                }

                if (invalid) {
                    el.classList.add('is-invalid');
                } else {
                    el.classList.remove('is-invalid');
                }
            };

            var getSelectedMode = function () {
                var selectedMode = 'csr_otp';
                setupModeInputs.forEach(function (input) {
                    if (input.checked) {
                        selectedMode = input.value;
                    }
                });
                return selectedMode;
            };

            var verifyRequiredFields = function () {
                var missing = [];
                var selectedMode = getSelectedMode();

                Object.keys(fieldEls).forEach(function (k) {
                    markFieldInvalid(fieldEls[k], false);
                });

                var requireField = function (key, label) {
                    var el = fieldEls[key];
                    if (!el) {
                        return;
                    }

                    var value = (el.value || '').trim();
                    if (value === '') {
                        missing.push(label);
                        markFieldInvalid(el, true);
                    }
                };

                requireField('privateKey', 'Private Key');

                if (selectedMode === 'portal_keys') {
                    requireField('token', 'Binary Security Token');
                    requireField('secret', 'Secret');
                    requireField('cert', 'Certificate');
                } else {
                    requireField('csr', 'CSR');
                    requireField('otp', 'OTP');
                }

                return missing;
            };

            document.querySelectorAll('button[type="submit"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    lastSubmitter = btn;
                });
            });

            Object.keys(fieldEls).forEach(function (k) {
                if (fieldEls[k]) {
                    fieldEls[k].addEventListener('input', function () {
                        markFieldInvalid(fieldEls[k], false);
                        clearInlineValidation();
                    });
                }
            });

            if (zatcaForm) {
                zatcaForm.addEventListener('submit', function (e) {
                    clearInlineValidation();

                    var submitter = e.submitter || lastSubmitter;
                    var submitAction = submitter && submitter.getAttribute('formaction') ? submitter.getAttribute('formaction') : '';
                    var isVerify = submitAction.indexOf('settings/zatca-verify') !== -1;

                    if (!isVerify) {
                        return;
                    }

                    if (zatcaEnabledCheckbox && !zatcaEnabledCheckbox.checked) {
                        return;
                    }

                    var missing = verifyRequiredFields();
                    if (!missing.length) {
                        return;
                    }

                    e.preventDefault();

                    if (inlineValidationBox) {
                        inlineValidationBox.innerHTML = '<strong>Please complete required ZATCA fields before Verify:</strong><br>' + missing.join(', ');
                        inlineValidationBox.classList.remove('d-none');
                        inlineValidationBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }

            syncSetupModeSections();
        });
    </script>
    {/literal}
{/block}
