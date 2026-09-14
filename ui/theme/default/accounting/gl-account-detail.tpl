{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-book"></i> {$_L['Account Ledger']|default:'Account Ledger'} &mdash; <code>{$account->code|default:''}</code> {$account->name|default:''}</h2>
        <div class="ibox-tools">
          <a href="{$_url}accounts/gl-accounts/list" class="btn btn-default">
            <i class="fal fa-arrow-left"></i> {$_L['Back']|default:'Back'}
          </a>
        </div>
      </div>
      <div class="card-body">

        <style>
          .acct-metric-card {
            color: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.10);
          }
          .acct-metric-value {
            margin: 0;
            font-size: 34px;
            line-height: 1;
            font-weight: 700;
          }
          .acct-metric-label {
            margin-top: 6px;
            font-size: 18px;
            font-weight: 600;
          }
          .acct-metric-progress {
            height: 6px;
            margin: 10px 0 6px;
            background: rgba(255, 255, 255, 0.22);
          }
          .acct-metric-progress .progress-bar {
            background: #fff;
            opacity: 0.95;
          }
          .acct-metric-note {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
          }
          .acct-summary-row {
            margin-top: 14px;
          }
          .acct-metric-teal { background: linear-gradient(135deg, #24b9c7 0%, #3bc18f 100%); }
          .acct-metric-red { background: linear-gradient(135deg, #f26545 0%, #f43f5e 100%); }
          .acct-metric-indigo { background: linear-gradient(135deg, #5b7cda 0%, #7757d9 100%); }
        </style>

        <div class="row mb-3 acct-summary-row">
          <div class="col-md-4">
            <div class="acct-metric-card acct-metric-teal">
              <h2 class="acct-metric-value">{($balance->debit_balance|default:0)|number_format:2}</h2>
              <div class="acct-metric-label">{$_L['Debit Balance']|default:'Debit Balance'}</div>
              <div class="progress acct-metric-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="acct-metric-note">100%</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="acct-metric-card acct-metric-red">
              <h2 class="acct-metric-value">{($balance->credit_balance|default:0)|number_format:2}</h2>
              <div class="acct-metric-label">{$_L['Credit Balance']|default:'Credit Balance'}</div>
              <div class="progress acct-metric-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="acct-metric-note">100%</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="acct-metric-card acct-metric-indigo">
              <h2 class="acct-metric-value">{($balance->net_balance|default:0)|number_format:2}</h2>
              <div class="acct-metric-label">{$_L['Net Balance']|default:'Net Balance'}</div>
              <div class="progress acct-metric-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="acct-metric-note">100%</p>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>{$_L['Date']|default:'Date'}</th>
                <th>{$_L['Description']|default:'Description'}</th>
                <th class="text-right">{$_L['Debit']|default:'Debit'}</th>
                <th class="text-right">{$_L['Credit']|default:'Credit'}</th>
              </tr>
            </thead>
            <tbody>
            {foreach $transactions|default:[] as $t}
              <tr>
                <td>{$t->entry_date|default:''}</td>
                <td>{$t->description|default:''}</td>
                <td class="text-right">{($t->debit_amount|default:0)|number_format:2}</td>
                <td class="text-right">{($t->credit_amount|default:0)|number_format:2}</td>
              </tr>
            {foreachelse}
              <tr><td colspan="4" class="text-center text-muted">{$_L['No transactions']|default:'No transactions'}</td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
{/block}

