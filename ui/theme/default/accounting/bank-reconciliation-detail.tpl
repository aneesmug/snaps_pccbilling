{extends file="$layouts_admin"}

{block name="content"}
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
  <h3>{$_L['Bank Reconciliation Detail']|default:'Bank Reconciliation Detail'}</h3>
  <a class="btn btn-secondary mb-3" href="{$_url}accounts/bank-reconciliation/list">Back</a>
  <p>ID: {$reconciliation.id|default:''}</p>
  <p>Date: {$reconciliation.statement_date|default:''}</p>
  <p>Status: {$reconciliation.status|default:''}</p>
</div>
</div></div></div></div>
{/block}
