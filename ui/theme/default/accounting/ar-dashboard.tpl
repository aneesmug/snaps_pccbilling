{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-arrow-down"></i> {$_L['Accounts Receivable Dashboard']|default:'Accounts Receivable'}</h2>
        <div class="ibox-tools">
          <a href="{$_url}accounts/ar-dashboard/aging" class="btn btn-primary">
            <i class="fal fa-clock-o"></i> {$_L['Aging Report']|default:'Aging Report'}
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

        <div class="row acct-summary-row">
          <div class="col-md-4">
            <div class="acct-metric-card acct-metric-teal">
              <h2 class="acct-metric-value">{$summary->total_customers|default:0}</h2>
              <div class="acct-metric-label"><i class="fal fa-users"></i> {$_L['Total Customers']|default:'Total Customers'}</div>
              <div class="progress acct-metric-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="acct-metric-note">100%</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="acct-metric-card acct-metric-red">
              <h2 class="acct-metric-value">{($summary->net_ar_outstanding|default:0)|number_format:2}</h2>
              <div class="acct-metric-label"><i class="fal fa-money"></i> {$_L['Outstanding']|default:'Outstanding'}</div>
              <div class="progress acct-metric-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="acct-metric-note">100%</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="acct-metric-card acct-metric-indigo">
              <h2 class="acct-metric-value">{($summary->overdue_amount|default:0)|number_format:2}</h2>
              <div class="acct-metric-label"><i class="fal fa-exclamation-circle"></i> {$_L['Overdue']|default:'Overdue'}</div>
              <div class="progress acct-metric-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="acct-metric-note">100%</p>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>{$_L['Customer']|default:'Customer'}</th>
                <th class="text-right">{$_L['Current']|default:'Current'}</th>
                <th class="text-right">1-30 {$_L['Days']|default:'Days'}</th>
                <th class="text-right">31-60 {$_L['Days']|default:'Days'}</th>
                <th class="text-right">60+ {$_L['Days']|default:'Days'}</th>
                <th class="text-right">{$_L['Total']|default:'Total'}</th>
              </tr>
            </thead>
            <tbody>
            {foreach $aging|default:[] as $id => $b}
              <tr>
                <td>{$b.customer_name|default:'Unknown'}</td>
                <td class="text-right">{($b.current|default:0)|number_format:2}</td>
                <td class="text-right">{($b['30']|default:0)|number_format:2}</td>
                <td class="text-right">{($b['60']|default:0)|number_format:2}</td>
                <td class="text-right">{($b['90']|default:0)|number_format:2}</td>
                <td class="text-right">{(($b.current|default:0)+($b['30']|default:0)+($b['60']|default:0)+($b['90']|default:0))|number_format:2}</td>
              </tr>
            {foreachelse}
              <tr><td colspan="6" class="text-center text-muted">{$_L['No records found']|default:'No records found'}</td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
{/block}


