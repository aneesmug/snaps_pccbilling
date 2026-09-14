{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-history"></i> {$_L['Audit Log']|default:'Audit Log'}</h2>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>{$_L['Date']|default:'Date'}</th>
                <th>{$_L['User']|default:'User'}</th>
                <th>{$_L['Action']|default:'Action'}</th>
                <th>{$_L['Table']|default:'Table'}</th>
                <th>{$_L['Record ID']|default:'Record ID'}</th>
              </tr>
            </thead>
            <tbody>
            {foreach $audit_logs|default:[] as $l}
              <tr>
                <td>{$l->created_at|default:''}</td>
                <td>{$l->user_id|default:''}</td>
                <td><span class="label label-info">{$l->action|default:''}</span></td>
                <td><code>{$l->table_name|default:''}</code></td>
                <td>{$l->record_id|default:''}</td>
              </tr>
            {foreachelse}
              <tr><td colspan="5" class="text-center text-muted">{$_L['No audit records']|default:'No audit records'}</td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
{/block}

