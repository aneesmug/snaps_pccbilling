{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Tax Reports List -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0"><i class="fal fa-receipt"></i> {$_L['Tax & VAT']|default:'Tax & VAT'}</h2>
            <p class="text-muted mb-0">{$_L['Tax compliance reports and status']|default:'Tax compliance reports and status'}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <a class="card h-100 text-decoration-none" href="{$_url}accounts/tax-vat/vat-report">
                <div class="card-body">
                    <h5 class="mb-1">{$_L['VAT Report']|default:'VAT Report'}</h5>
                    <small class="text-muted">{$_L['Output/Input VAT and net due']|default:'Output/Input VAT and net due'}</small>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a class="card h-100 text-decoration-none" href="{$_url}accounts/tax-vat/zatca-status">
                <div class="card-body">
                    <h5 class="mb-1">{$_L['ZATCA Status']|default:'ZATCA Status'}</h5>
                    <small class="text-muted">{$_L['E-invoicing compliance status']|default:'E-invoicing compliance status'}</small>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a class="card h-100 text-decoration-none" href="{$_url}accounts/accounting-settings/tax-codes">
                <div class="card-body">
                    <h5 class="mb-1">{$_L['Tax Codes']|default:'Tax Codes'}</h5>
                    <small class="text-muted">{$_L['Manage VAT/tax code configuration']|default:'Manage VAT/tax code configuration'}</small>
                </div>
            </a>
        </div>
    </div>
</div>

</div></div></div></div>
{/block}
