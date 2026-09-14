{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Compliance Report -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-shield-alt"></i>
                {$_L['Compliance Report']}
            </h2>
        </div>
        <div class="col-md-4 text-right">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fal fa-print"></i>
                {$_L['Print']}
            </button>
        </div>
    </div>

    <!-- Compliance Status -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 {if $compliance->gl_balanced}text-success{else}text-danger{/if}">
                        {if $compliance->gl_balanced}<i class="fal fa-check-circle"></i>{else}<i class="fal fa-times-circle"></i>{/if}
                    </div>
                    <h6 class="mt-3">{$_L['GL Balanced']}</h6>
                    <small class="text-muted">Debit = Credit</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 {if $compliance->no_pending}text-success{else}text-warning{/if}">
                        {if $compliance->no_pending}<i class="fal fa-check-circle"></i>{else}<i class="fal fa-exclamation-circle"></i>{/if}
                    </div>
                    <h6 class="mt-3">{$_L['No Pending Entries']}</h6>
                    <small class="text-muted">{$compliance->pending_count} {$_L['pending']}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 {if $compliance->periods_locked}text-success{else}text-info{/if}">
                        {if $compliance->periods_locked}<i class="fal fa-lock"></i>{else}<i class="fal fa-unlock"></i>{/if}
                    </div>
                    <h6 class="mt-3">{$_L['Periods Locked']}</h6>
                    <small class="text-muted">{$compliance->locked_periods} {$_L['of']} {$compliance->total_periods}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-4 {if $compliance->zatca_compliant}text-success{else}text-danger{/if}">
                        {if $compliance->zatca_compliant}<i class="fal fa-check-circle"></i>{else}<i class="fal fa-times-circle"></i>{/if}
                    </div>
                    <h6 class="mt-3">{$_L['ZATCA Compliant']}</h6>
                    <small class="text-muted">Invoice signing</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Compliance Checks -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Compliance Checks']}</h2>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['General Ledger Balance']}
                    <span class="badge badge-{if $compliance->gl_balanced}success{else}danger{/if}">
                        {if $compliance->gl_balanced}{$_L['OK']}{else}{$_L['ERROR']}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['No Unposted Entries']}
                    <span class="badge badge-{if $compliance->no_pending}success{else}warning{/if}">
                        {$compliance->pending_count} {$_L['pending']}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Accounting Periods']}
                    <span class="badge badge-info">
                        {$compliance->locked_periods}/{$compliance->total_periods} {$_L['locked']}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['AR/AP Reconciliation']}
                    <span class="badge badge-{if $compliance->ar_ap_reconciled}success{else}warning{/if}">
                        {if $compliance->ar_ap_reconciled}{$_L['OK']}{else}{$_L['REVIEW']}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['ZATCA Compliance']}
                    <span class="badge badge-{if $compliance->zatca_compliant}success{else}danger{/if}">
                        {if $compliance->zatca_compliant}{$_L['COMPLIANT']}{else}{$_L['NON-COMPLIANT']}{/if}
                    </span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Audit Logging Enabled']}
                    <span class="badge badge-success">{$_L['ACTIVE']}</span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {$_L['Data Integrity']}
                    <span class="badge badge-{if $compliance->data_integrity}success{else}danger{/if}">
                        {if $compliance->data_integrity}{$_L['OK']}{else}{$_L['ERROR']}{/if}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{$compliance->total_entries}</h3>
                    <p class="mb-0 text-muted">{$_L['Journal Entries']}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{$compliance->total_invoices}</h3>
                    <p class="mb-0 text-muted">{$_L['Invoices']}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{$compliance->total_bills}</h3>
                    <p class="mb-0 text-muted">{$_L['Bills']}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{$compliance->audit_events}</h3>
                    <p class="mb-0 text-muted">{$_L['Audit Events']}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .btn { display: none !important; }
</style>

</div></div></div></div>
{/block}
