{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - ZATCA Compliance Status -->
<div class="zatca-status-page">
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-certificate"></i>
                {$_L['ZATCA Compliance Status']|default:'ZATCA Compliance Status'}
            </h2>
            <small class="text-muted">{$_L['ZATCA Phase 2 E-Invoicing Status']|default:'ZATCA Phase 2 E-Invoicing Status'}</small>
        </div>
        <div class="col-md-4 text-right">
            <a href="{$_url}accounts/tax-vat/vat-report" class="btn btn-secondary">
                <i class="fal fa-arrow-left"></i>
                {$_L['Back']|default:'Back'}
            </a>
        </div>
    </div>

    <!-- Compliance Status -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 {if $status->zatca_registered}text-success{else}text-danger{/if}">
                        {if $status->zatca_registered}<i class="fal fa-check-circle"></i>{else}<i class="fal fa-times-circle"></i>{/if}
                    </div>
                    <h6 class="mt-3">{$_L['Registration Status']|default:'Registration Status'}</h6>
                    <small class="text-muted">
                        {if $status->zatca_registered}{$_L['REGISTERED']|default:'REGISTERED'}{else}{$_L['NOT REGISTERED']|default:'NOT REGISTERED'}{/if}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 {if $status->certificates_valid}text-success{else}text-warning{/if}">
                        {if $status->certificates_valid}<i class="fal fa-lock"></i>{else}<i class="fal fa-lock-open"></i>{/if}
                    </div>
                    <h6 class="mt-3">{$_L['Digital Certificates']|default:'Digital Certificates'}</h6>
                    <small class="text-muted">
                        {if $status->certificates_valid}{$_L['VALID']|default:'VALID'}{else}{$_L['EXPIRED/INVALID']|default:'EXPIRED/INVALID'}{/if}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 text-info">
                        <i class="fal fa-file-alt"></i>
                    </div>
                    <h6 class="mt-3">{$_L['Invoices Submitted']|default:'Invoices Submitted'}</h6>
                    <small class="text-muted">{$status->invoices_submitted} / {$status->total_invoices}</small><br>
                    <small class="text-muted">{$_L['Source']|default:'Source'}: {$status->invoice_count_source|default:'ZATCA-linked invoices'}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 {if $status->compliance_score >= 80}text-success{elseif $status->compliance_score >= 60}text-warning{else}text-danger{/if}">
                        {$status->compliance_score}%
                    </div>
                    <h6 class="mt-3">{$_L['Compliance Score']|default:'Compliance Score'}</h6>
                    <small class="text-muted">Target: 100%</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Compliance -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Compliance Details']|default:'Compliance Details'}</h5>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['ZATCA Registration']|default:'ZATCA Registration'}
                    <span class="badge zatca-badge {if $status->zatca_registered}zatca-badge-success{else}zatca-badge-danger{/if}">
                        {if $status->zatca_registered}{$_L['REGISTERED']|default:'REGISTERED'}{else}{$_L['PENDING']|default:'PENDING'}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Certificate Status']|default:'Certificate Status'}
                    <span class="badge zatca-badge {if $status->certificates_valid}zatca-badge-success{else}zatca-badge-warning{/if}">
                        {if $status->certificates_valid}{$_L['VALID']|default:'VALID'}{else}{$_L['REVIEW REQUIRED']|default:'REVIEW REQUIRED'}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Invoice Signing']|default:'Invoice Signing'}
                    <span class="badge zatca-badge {if $status->signing_enabled}zatca-badge-success{else}zatca-badge-secondary{/if}">
                        {if $status->signing_enabled}{$_L['ENABLED']|default:'ENABLED'}{else}{$_L['DISABLED']|default:'DISABLED'}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['QR Code Generation']|default:'QR Code Generation'}
                    <span class="badge zatca-badge {if $status->qr_generation}zatca-badge-success{else}zatca-badge-secondary{/if}">
                        {if $status->qr_generation}{$_L['ENABLED']|default:'ENABLED'}{else}{$_L['DISABLED']|default:'DISABLED'}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Portal Connection']|default:'Portal Connection'}
                    <span class="badge zatca-badge {if $status->portal_connected}zatca-badge-success{else}zatca-badge-danger{/if}">
                        {if $status->portal_connected}{$_L['CONNECTED']|default:'CONNECTED'}{else}{$_L['DISCONNECTED']|default:'DISCONNECTED'}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Last Submission']|default:'Last Submission'}
                    <small class="text-muted">{$status->last_submission_date|default:'N/A'}</small>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Live ZATCA Response']|default:'Live ZATCA Response'}
                    <small class="text-muted">{$status->live_status_message|default:'Not checked'} ({$status->live_status_checked_at|default:'N/A'})</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Status -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Configuration']|default:'Configuration'}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>{$_L['Business Registration Number (CRN)']|default:'Business Registration Number (CRN)'}</strong></p>
                    <p>
                        <span class="badge zatca-badge {if !empty($status->crn)}zatca-badge-success{else}zatca-badge-secondary{/if}">
                            {$status->crn|default:'Not configured'}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>{$_L['VAT Registration Number']|default:'VAT Registration Number'}</strong></p>
                    <p>
                        <span class="badge zatca-badge {if !empty($status->vat_reg_number)}zatca-badge-success{else}zatca-badge-secondary{/if}">
                            {$status->vat_reg_number|default:'Not configured'}
                        </span>
                    </p>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <p><strong>{$_L['Certificate File']|default:'Certificate File'}</strong></p>
                    <p>
                        <span class="badge zatca-badge {if !empty($status->certificate_file)}zatca-badge-info{else}zatca-badge-warning{/if}">
                            {if $status->certificate_file}{$status->certificate_file}{else}{$_L['Not uploaded']|default:'Not uploaded'}{/if}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>{$_L['Certificate Expiry']|default:'Certificate Expiry'}</strong></p>
                    <p>
                        <span class="badge zatca-badge {if !empty($status->certificate_expiry)}zatca-badge-info{else}zatca-badge-secondary{/if}">
                            {if $status->certificate_expiry}{$status->certificate_expiry}{else}N/A{/if}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Retrieved ZATCA Config']|default:'Retrieved ZATCA Config'}</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="fal fa-shield"></i>
                {if $show_sensitive}
                    {$_L['Sensitive fields are visible in masked form (show_sensitive=1).']|default:'Sensitive fields are visible in masked form (show_sensitive=1).'}
                {else}
                    {$_L['Sensitive fields are hidden for security. Add show_sensitive=1 in URL to view masked values.']|default:'Sensitive fields are hidden for security. Add show_sensitive=1 in URL to view masked values.'}
                {/if}
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Environment']|default:'Environment'}</strong></p>
                    <span class="badge zatca-badge zatca-badge-info">{$status->environment|default:'N/A'|upper}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Invoice Type']|default:'Invoice Type'}</strong></p>
                    <span class="badge zatca-badge zatca-badge-info">{$status->invoice_type|default:'N/A'|upper}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Seller Name']|default:'Seller Name'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->seller_name)}zatca-badge-success{else}zatca-badge-secondary{/if}">{$status->seller_name|default:'Not configured'}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <p class="mb-1"><strong>{$_L['API Base URL']|default:'API Base URL'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->api_base_url)}zatca-badge-info{else}zatca-badge-secondary{/if}">{$status->api_base_url|default:'Not configured'}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Building No.']|default:'Building No.'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->building_no)}zatca-badge-success{else}zatca-badge-secondary{/if}">{$status->building_no|default:'Not configured'}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Street Name']|default:'Street Name'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->street_name)}zatca-badge-success{else}zatca-badge-secondary{/if}">{$status->street_name|default:'Not configured'}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['District']|default:'District'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->district)}zatca-badge-success{else}zatca-badge-secondary{/if}">{$status->district|default:'Not configured'}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['City']|default:'City'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->city)}zatca-badge-success{else}zatca-badge-secondary{/if}">{$status->city|default:'Not configured'}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Postal Code']|default:'Postal Code'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->postal_code)}zatca-badge-success{else}zatca-badge-secondary{/if}">{$status->postal_code|default:'Not configured'}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Country']|default:'Country'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->country_code)}zatca-badge-info{else}zatca-badge-secondary{/if}">{$status->country_code|default:'N/A'}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <p class="mb-1"><strong>{$_L['CSR']|default:'CSR'}</strong></p>
                    <span class="badge zatca-badge {if $status->csr_saved}zatca-badge-success{else}zatca-badge-secondary{/if}">{if $status->csr_saved}{$_L['SAVED']|default:'SAVED'}{else}{$_L['NOT SAVED']|default:'NOT SAVED'}{/if}</span>
                </div>
                <div class="col-md-3 mb-3">
                    <p class="mb-1"><strong>{$_L['OTP']|default:'OTP'}</strong></p>
                    <span class="badge zatca-badge {if $status->otp_saved}zatca-badge-success{else}zatca-badge-secondary{/if}">{if $status->otp_saved}{$_L['SAVED']|default:'SAVED'}{else}{$_L['NOT SAVED']|default:'NOT SAVED'}{/if}</span>
                </div>
                <div class="col-md-3 mb-3">
                    <p class="mb-1"><strong>{$_L['Private Key']|default:'Private Key'}</strong></p>
                    <span class="badge zatca-badge {if $status->private_key_saved}zatca-badge-success{else}zatca-badge-secondary{/if}">{if $status->private_key_saved}{$_L['SAVED']|default:'SAVED'}{else}{$_L['NOT SAVED']|default:'NOT SAVED'}{/if}</span>
                </div>
                <div class="col-md-3 mb-3">
                    <p class="mb-1"><strong>{$_L['Passphrase']|default:'Passphrase'}</strong></p>
                    <span class="badge zatca-badge {if $status->private_key_passphrase_saved}zatca-badge-success{else}zatca-badge-secondary{/if}">{if $status->private_key_passphrase_saved}{$_L['SAVED']|default:'SAVED'}{else}{$_L['NOT SAVED']|default:'NOT SAVED'}{/if}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Compliance Request ID']|default:'Compliance Request ID'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->compliance_request_id)}zatca-badge-info{else}zatca-badge-secondary{/if}">{$status->compliance_request_id|default:'Not configured'}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Compliance CSID']|default:'Compliance CSID'}</strong></p>
                    <span class="badge zatca-badge {if !empty($status->compliance_csid)}zatca-badge-success{else}zatca-badge-secondary{/if}">{$status->compliance_csid|default:'Not configured'}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Compliance Secret']|default:'Compliance Secret'}</strong></p>
                    {if empty($status->compliance_secret)}
                        <span class="badge zatca-badge zatca-badge-secondary">{$_L['Not configured']|default:'Not configured'}</span>
                    {elseif $show_sensitive}
                        <span class="badge zatca-badge zatca-badge-warning">{$status->compliance_secret}</span>
                    {else}
                        <span class="badge zatca-badge zatca-badge-danger">{$_L['Hidden for security']|default:'Hidden for security'}</span>
                    {/if}
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Binary Security Token']|default:'Binary Security Token'}</strong></p>
                    {if empty($status->binary_security_token)}
                        <span class="badge zatca-badge zatca-badge-secondary">{$_L['Not configured']|default:'Not configured'}</span>
                    {elseif $show_sensitive}
                        <span class="badge zatca-badge zatca-badge-warning">{$status->binary_security_token}</span>
                    {else}
                        <span class="badge zatca-badge zatca-badge-danger">{$_L['Hidden for security']|default:'Hidden for security'}</span>
                    {/if}
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Production CSID']|default:'Production CSID'}</strong></p>
                    {if empty($status->production_csid)}
                        <span class="badge zatca-badge zatca-badge-secondary">{$_L['Not configured']|default:'Not configured'}</span>
                    {elseif $show_sensitive}
                        <span class="badge zatca-badge zatca-badge-success">{$status->production_csid}</span>
                    {else}
                        <span class="badge zatca-badge zatca-badge-danger">{$_L['Hidden for security']|default:'Hidden for security'}</span>
                    {/if}
                </div>
                <div class="col-md-4 mb-3">
                    <p class="mb-1"><strong>{$_L['Production Secret']|default:'Production Secret'}</strong></p>
                    {if empty($status->production_secret)}
                        <span class="badge zatca-badge zatca-badge-secondary">{$_L['Not configured']|default:'Not configured'}</span>
                    {elseif $show_sensitive}
                        <span class="badge zatca-badge zatca-badge-warning">{$status->production_secret}</span>
                    {else}
                        <span class="badge zatca-badge zatca-badge-danger">{$_L['Hidden for security']|default:'Hidden for security'}</span>
                    {/if}
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-1">
                    <p class="mb-1"><strong>{$_L['Production Binary Security Token']|default:'Production Binary Security Token'}</strong></p>
                    {if empty($status->production_binary_security_token)}
                        <span class="badge zatca-badge zatca-badge-secondary">{$_L['Not configured']|default:'Not configured'}</span>
                    {elseif $show_sensitive}
                        <span class="badge zatca-badge zatca-badge-warning">{$status->production_binary_security_token}</span>
                    {else}
                        <span class="badge zatca-badge zatca-badge-danger">{$_L['Hidden for security']|default:'Hidden for security'}</span>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <!-- Issues/Warnings -->
    {if !empty($issues)}
        <div class="card mt-4 border-warning">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-dark">{$_L['Issues Found']|default:'Issues Found'}</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    {foreach $issues as $issue}
                        <li class="mb-2">
                            <i class="fal fa-exclamation-triangle text-warning"></i>
                            {$issue}
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {/if}
</div>

<style>
    .zatca-status-page .card,
    .zatca-status-page .card-body,
    .zatca-status-page .card-header,
    .zatca-status-page .card-footer,
    .zatca-status-page .list-group-item,
    .zatca-status-page p,
    .zatca-status-page strong,
    .zatca-status-page code {
        color: #2f3640 !important;
    }

    .zatca-status-page h1,
    .zatca-status-page h2,
    .zatca-status-page h3,
    .zatca-status-page h4,
    .zatca-status-page h5,
    .zatca-status-page h6 {
        color: #1f2d3d !important;
    }

    .zatca-status-page .text-muted {
        color: #6c757d !important;
    }

    .zatca-status-page code {
        background: #f4f6f9;
        border-radius: 3px;
        padding: 2px 6px;
    }

    .zatca-status-page .zatca-badge {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .2px;
        border-radius: 999px;
        padding: 6px 10px;
        color: #fff !important;
        display: inline-block;
    }

    .zatca-status-page .zatca-badge-success {
        background: linear-gradient(135deg, #2fbf71 0%, #27ae60 100%);
    }

    .zatca-status-page .zatca-badge-danger {
        background: linear-gradient(135deg, #ef5350 0%, #e53935 100%);
    }

    .zatca-status-page .zatca-badge-warning {
        background: linear-gradient(135deg, #ffb74d 0%, #fb8c00 100%);
    }

    .zatca-status-page .zatca-badge-info {
        background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
    }

    .zatca-status-page .zatca-badge-secondary {
        background: linear-gradient(135deg, #90a4ae 0%, #607d8b 100%);
    }
</style>

</div></div></div></div>
</div>
{/block}
