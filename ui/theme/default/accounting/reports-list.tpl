{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-bar-chart"></i> {$_L['Financial Reports']|default:'Accounting Reports'}</h2>
      </div>
      <div class="card-body">

        <!-- Financial Statements -->
        <h5 class="text-uppercase text-muted mb-3" style="font-size:11px;letter-spacing:.5px;">Financial Statements</h5>
        <div class="row mb-4">

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-primary">
              <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0"><i class="fal fa-balance-scale"></i> {$_L['Trial Balance']|default:'Trial Balance'}</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Verify debit equals credit across all GL accounts.</p>
                <a href="{$_url}accounts/financial-reports/trial-balance" class="btn btn-primary btn-sm btn-block">{$_L['Generate']|default:'Generate'}</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-success">
              <div class="card-header bg-success text-white">
                <h6 class="card-title mb-0"><i class="fal fa-line-chart"></i> {$_L['Profit & Loss']|default:'Profit &amp; Loss'}</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Income and expense summary for a period.</p>
                <a href="{$_url}accounts/financial-reports/profit-loss" class="btn btn-success btn-sm btn-block">{$_L['Generate']|default:'Generate'}</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-info">
              <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0"><i class="fal fa-building"></i> {$_L['Balance Sheet']|default:'Balance Sheet'}</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Assets, liabilities, and equity as of a date.</p>
                <a href="{$_url}accounts/financial-reports/balance-sheet" class="btn btn-info btn-sm btn-block">{$_L['Generate']|default:'Generate'}</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-warning">
              <div class="card-header bg-warning text-white">
                <h6 class="card-title mb-0"><i class="fal fa-money"></i> {$_L['Cash Flow Statement']|default:'Cash Flow Statement'}</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Operating, investing, and financing cash flows.</p>
                <a href="{$_url}accounts/financial-reports/cash-flow" class="btn btn-warning btn-sm btn-block">{$_L['Generate']|default:'Generate'}</a>
              </div>
            </div>
          </div>

        </div>

        <!-- Receivables & Payables -->
        <h5 class="text-uppercase text-muted mb-3" style="font-size:11px;letter-spacing:.5px;">Receivables &amp; Payables</h5>
        <div class="row mb-4">

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-danger">
              <div class="card-header bg-danger text-white">
                <h6 class="card-title mb-0"><i class="fal fa-users"></i> AR Aging Report</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Outstanding customer balances by aging bucket.</p>
                <a href="{$_url}accounts/ar-dashboard/aging" class="btn btn-danger btn-sm btn-block">Generate</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-secondary">
              <div class="card-header bg-secondary text-white">
                <h6 class="card-title mb-0"><i class="fal fa-truck"></i> AP Aging Report</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Outstanding vendor balances by aging bucket.</p>
                <a href="{$_url}accounts/ap-dashboard/aging" class="btn btn-secondary btn-sm btn-block">Generate</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card" style="border-color:#6f42c1;">
              <div class="card-header text-white" style="background:#6f42c1;">
                <h6 class="card-title mb-0"><i class="fal fa-address-book"></i> Customer Statement</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Full transaction ledger for a specific customer.</p>
                <a href="{$_url}accounts/ar-dashboard" class="btn btn-sm btn-block" style="background:#6f42c1;color:#fff;">View AR Dashboard</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card" style="border-color:#20c997;">
              <div class="card-header text-white" style="background:#20c997;">
                <h6 class="card-title mb-0"><i class="fal fa-address-card"></i> Vendor Statement</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Full transaction ledger for a specific vendor.</p>
                <a href="{$_url}accounts/ap-dashboard" class="btn btn-sm btn-block" style="background:#20c997;color:#fff;">View AP Dashboard</a>
              </div>
            </div>
          </div>

        </div>

        <!-- Tax & Compliance -->
        <h5 class="text-uppercase text-muted mb-3" style="font-size:11px;letter-spacing:.5px;">Tax &amp; Compliance</h5>
        <div class="row mb-4">

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card" style="border-color:#fd7e14;">
              <div class="card-header text-white" style="background:#fd7e14;">
                <h6 class="card-title mb-0"><i class="fal fa-percent"></i> VAT / Tax Report</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Input and output VAT summary for a period.</p>
                <a href="{$_url}accounts/tax-vat/vat-report" class="btn btn-sm btn-block" style="background:#fd7e14;color:#fff;">Generate</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-dark">
              <div class="card-header bg-dark text-white">
                <h6 class="card-title mb-0"><i class="fal fa-shield-check"></i> ZATCA Status</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">ZATCA e-invoicing compliance status and config.</p>
                <a href="{$_url}accounts/tax-vat/zatca-status" class="btn btn-dark btn-sm btn-block">View</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card" style="border-color:#17a2b8;">
              <div class="card-header text-white" style="background:#17a2b8;">
                <h6 class="card-title mb-0"><i class="fal fa-file-invoice"></i> ZATCA Submit Report</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Review invoices submitted to the ZATCA portal.</p>
                <a href="{$_url}reports/zatca-submit-report" class="btn btn-sm btn-block" style="background:#17a2b8;color:#fff;">View</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card" style="border-color:#6610f2;">
              <div class="card-header text-white" style="background:#6610f2;">
                <h6 class="card-title mb-0"><i class="fal fa-exchange"></i> ZATCA Returns Report</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Summary of returns and credit notes via ZATCA.</p>
                <a href="{$_url}reports/zatca-returns-report" class="btn btn-sm btn-block" style="background:#6610f2;color:#fff;">View</a>
              </div>
            </div>
          </div>

        </div>

        <!-- General Ledger -->
        <h5 class="text-uppercase text-muted mb-3" style="font-size:11px;letter-spacing:.5px;">General Ledger</h5>
        <div class="row">

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-primary">
              <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0"><i class="fal fa-book"></i> Journal Entries</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Browse and audit all posted journal entries.</p>
                <a href="{$_url}accounts/journal-entries" class="btn btn-primary btn-sm btn-block">View</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-success">
              <div class="card-header bg-success text-white">
                <h6 class="card-title mb-0"><i class="fal fa-list"></i> Chart of Accounts</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Full list of GL accounts and current balances.</p>
                <a href="{$_url}accounts/gl-accounts" class="btn btn-success btn-sm btn-block">View</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-info">
              <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0"><i class="fal fa-university"></i> Bank Reconciliation</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Match bank statement to GL transactions.</p>
                <a href="{$_url}accounts/bank-reconciliation" class="btn btn-info btn-sm btn-block">View</a>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-warning">
              <div class="card-header bg-warning text-white">
                <h6 class="card-title mb-0"><i class="fal fa-tachometer"></i> Accounting Dashboard</h6>
              </div>
              <div class="card-body">
                <p class="text-muted small mb-3">Overview of financial health and key metrics.</p>
                <a href="{$_url}accounts/dashboard" class="btn btn-warning btn-sm btn-block">View</a>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>
{/block}


