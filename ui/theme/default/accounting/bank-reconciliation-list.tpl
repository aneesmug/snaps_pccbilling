{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-university"></i> {$_L['Bank Reconciliations']|default:'Bank Reconciliations'}</h2>
        <div class="ibox-tools">
          <a href="{$_url}accounts/bank-reconciliation/new" class="btn btn-primary">
            <i class="fal fa-plus"></i> {$_L['New Reconciliation']|default:'New Reconciliation'}
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>{$_L['Statement Date']|default:'Statement Date'}</th>
                <th>{$_L['Bank Account']|default:'Bank Account'}</th>
                <th>{$_L['Statement Balance']|default:'Statement Balance'}</th>
                <th>{$_L['Status']|default:'Status'}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            {foreach $reconciliations|default:[] as $r}
              <tr>
                <td>{$r->id|default:''}</td>
                <td>{$r->statement_date|default:''}</td>
                <td>{$r->bank_account_name|default:$r->bankAccount->account_name|default:'N/A'}</td>
                <td class="text-right">{($r->statement_closing_balance|default:0)|number_format:2}</td>
                <td>
                  {if $r->status eq 'completed'}
                    <span class="label label-success">{$_L['Completed']|default:'Completed'}</span>
                  {elseif $r->status eq 'in_progress'}
                    <span class="label label-warning">{$_L['In Progress']|default:'In Progress'}</span>
                  {else}
                    <span class="label label-default">{$r->status|default:''}</span>
                  {/if}
                </td>
                <td>
                  <a href="{$_url}accounts/bank-reconciliation/detail/{$r->id}" class="btn btn-info">
                    <i class="fal fa-eye"></i> {$_L['View']|default:'View'}
                  </a>
                </td>
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


