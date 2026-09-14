{extends file="$layouts_admin"}

{block name="content"}
{assign var="total_net_balance" value=0}
{assign var="accounts_with_balance" value=0}
{foreach $accounts|default:[] as $account_for_totals}
  {assign var="bal_for_totals" value=$balances[$account_for_totals->id]|default:null}
  {assign var="line_net" value=$bal_for_totals->net_balance|default:0}
  {assign var="total_net_balance" value=$total_net_balance+$line_net}
  {if $line_net != 0}
    {assign var="accounts_with_balance" value=$accounts_with_balance+1}
  {/if}
{/foreach}

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-book"></i> {$_L['Chart of Accounts']|default:'Chart of Accounts'}</h2>
        <div class="ibox-tools">
          <a href="{$_url}accounts/journal-entries/create" class="btn btn-primary">
            <i class="fal fa-plus"></i> {$_L['New Journal Entry']|default:'New Journal Entry'}
          </a>
        </div>
      </div>
      <div class="card-body">

        <style>
          .coa-summary-card {
            color: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.10);
          }
          .coa-summary-row {
            margin-top: 14px;
          }
          .coa-summary-card .coa-label {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
          }
          .coa-summary-card .coa-value {
            margin: 4px 0 0;
            font-size: 34px;
            font-weight: 700;
            line-height: 1;
          }
          .coa-summary-card .coa-progress {
            height: 6px;
            margin: 10px 0 6px;
            background: rgba(255, 255, 255, 0.22);
          }
          .coa-summary-card .coa-progress .progress-bar {
            background: #fff;
            opacity: 0.95;
          }
          .coa-summary-card .coa-note {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
          }
          .coa-table-wrap {
            margin-top: 8px;
            border-radius: 8px;
            overflow: hidden;
          }
          .coa-card-teal { background: linear-gradient(135deg, #24b9c7 0%, #3bc18f 100%); }
          .coa-card-red { background: linear-gradient(135deg, #f26545 0%, #f43f5e 100%); }
          .coa-card-indigo { background: linear-gradient(135deg, #5b7cda 0%, #7757d9 100%); }
        </style>

        <div class="row coa-summary-row">
          <div class="col-md-4">
            <div class="coa-summary-card coa-card-teal">
              <p class="coa-label">{$_L['Total Accounts']|default:'Total Accounts'}</p>
              <h3 class="coa-value">{$accounts|@count}</h3>
              <div class="progress coa-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="coa-note">100%</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="coa-summary-card coa-card-red">
              <p class="coa-label">{$_L['Accounts With Balance']|default:'Accounts With Balance'}</p>
              <h3 class="coa-value">{$accounts_with_balance|default:0}</h3>
              <div class="progress coa-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="coa-note">100%</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="coa-summary-card coa-card-indigo">
              <p class="coa-label">{$_L['Total Net Balance']|default:'Total Net Balance'}</p>
              <h3 class="coa-value">{($total_net_balance|default:0)|number_format:2}</h3>
              <div class="progress coa-progress"><div class="progress-bar" style="width:100%"></div></div>
              <p class="coa-note">100%</p>
            </div>
          </div>
        </div>

        <div class="coa-table-wrap">
        <div class="table-responsive">
          <table class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>{$_L['Code']|default:'Code'}</th>
                <th>{$_L['Name']|default:'Name'}</th>
                <th>{$_L['Type']|default:'Type'}</th>
                <th>{$_L['Category']|default:'Category'}</th>
                <th class="text-right">{$_L['Net Balance']|default:'Net Balance'}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            {foreach $accounts|default:[] as $account}
              {assign var="bal" value=$balances[$account->id]|default:null}
              <tr>
                <td><code>{$account->code|default:''}</code></td>
                <td>{$account->name|default:''}</td>
                <td><span class="label label-default">{$account->type|default:''}</span></td>
                <td>{$account->category|default:''}</td>
                <td class="text-right">{($bal->net_balance|default:0)|number_format:2}</td>
                <td>
                  <a href="{$_url}accounts/gl-accounts/view/{$account->id}" class="btn btn-info">
                    <i class="fal fa-eye"></i> {$_L['View']|default:'View'}
                  </a>
                </td>
              </tr>
            {foreachelse}
              <tr><td colspan="6" class="text-center text-muted">{$_L['No accounts found']|default:'No accounts found'}</td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
{/block}


