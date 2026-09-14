{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-calculator"></i> {$_L['Accounting System']|default:'Accounting System'}</h2>
      </div>
      <div class="card-body">

        <style>
          .acct-card {
            display: block;
            color: #fff !important;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            min-height: 136px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.10);
            transition: transform .2s ease, box-shadow .2s ease;
            text-decoration: none !important;
          }
          .acct-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.15);
          }
          .acct-card .acct-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
          }
          .acct-card .acct-card-value {
            font-size: 32px;
            line-height: 1;
            font-weight: 700;
            margin: 0;
          }
          .acct-card .acct-card-title {
            font-size: 21px;
            font-weight: 600;
            margin-top: 8px;
          }
          .acct-card .acct-card-icon i {
            font-size: 20px;
          }
          .acct-card .acct-card-progress {
            height: 6px;
            margin: 10px 0 6px;
            background: rgba(255, 255, 255, 0.22);
          }
          .acct-card .acct-card-progress .progress-bar {
            background: #fff;
            opacity: 0.95;
          }
          .acct-card .acct-card-note {
            font-size: 12px;
            font-weight: 600;
            opacity: 0.95;
          }
          .acct-card-teal { background: linear-gradient(135deg, #24b9c7 0%, #3bc18f 100%); }
          .acct-card-red { background: linear-gradient(135deg, #f26545 0%, #f43f5e 100%); }
          .acct-card-indigo { background: linear-gradient(135deg, #5b7cda 0%, #7757d9 100%); }
          .acct-card-orange { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); }
          .acct-card-slate { background: linear-gradient(135deg, #475569 0%, #334155 100%); }
          .acct-card-blue { background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); }
          .acct-card-emerald { background: linear-gradient(135deg, #10b981 0%, #0d9488 100%); }
          .acct-card-violet { background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%); }
        </style>

        <div class="row">
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/gl-accounts/list" class="acct-card acct-card-teal">
              <div class="acct-card-top">
                <p class="acct-card-value">01</p>
                <span class="acct-card-icon"><i class="fal fa-book"></i></span>
              </div>
              <div class="acct-card-title">{$_L['GL Accounts']|default:'GL Accounts'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/journal-entries/list" class="acct-card acct-card-red">
              <div class="acct-card-top">
                <p class="acct-card-value">02</p>
                <span class="acct-card-icon"><i class="fal fa-pencil"></i></span>
              </div>
              <div class="acct-card-title">{$_L['Journal Entries']|default:'Journal Entries'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/ar-dashboard/dashboard" class="acct-card acct-card-indigo">
              <div class="acct-card-top">
                <p class="acct-card-value">03</p>
                <span class="acct-card-icon"><i class="fal fa-arrow-down"></i></span>
              </div>
              <div class="acct-card-title">{$_L['Accounts Receivable']|default:'AR Dashboard'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/ap-dashboard/dashboard" class="acct-card acct-card-orange">
              <div class="acct-card-top">
                <p class="acct-card-value">04</p>
                <span class="acct-card-icon"><i class="fal fa-arrow-up"></i></span>
              </div>
              <div class="acct-card-title">{$_L['Accounts Payable']|default:'AP Dashboard'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/financial-reports/list" class="acct-card acct-card-slate">
              <div class="acct-card-top">
                <p class="acct-card-value">05</p>
                <span class="acct-card-icon"><i class="fal fa-chart-bar"></i></span>
              </div>
              <div class="acct-card-title">{$_L['Financial Reports']|default:'Financial Reports'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/bank-reconciliation/list" class="acct-card acct-card-blue">
              <div class="acct-card-top">
                <p class="acct-card-value">06</p>
                <span class="acct-card-icon"><i class="fal fa-university"></i></span>
              </div>
              <div class="acct-card-title">{$_L['Bank Reconciliation']|default:'Bank Reconciliation'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/tax-vat/list" class="acct-card acct-card-emerald">
              <div class="acct-card-top">
                <p class="acct-card-value">07</p>
                <span class="acct-card-icon"><i class="fal fa-percent"></i></span>
              </div>
              <div class="acct-card-title">{$_L['Tax & VAT']|default:'Tax & VAT'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <a href="{$_url}accounts/accounting-settings/periods" class="acct-card acct-card-violet">
              <div class="acct-card-top">
                <p class="acct-card-value">08</p>
                <span class="acct-card-icon"><i class="fal fa-cog"></i></span>
              </div>
              <div class="acct-card-title">{$_L['Settings']|default:'Settings'}</div>
              <div class="progress acct-card-progress"><div class="progress-bar" style="width:100%"></div></div>
              <div class="acct-card-note">{$_L['Open Module']|default:'Open Module'}</div>
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
{/block}


