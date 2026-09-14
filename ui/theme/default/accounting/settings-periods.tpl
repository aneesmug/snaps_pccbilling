{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-calendar"></i> {$_L['Accounting Periods']|default:'Accounting Periods'}</h2>
        <div class="ibox-tools">
          <a href="{$_url}accounts/accounting-settings/periods/new" class="btn btn-primary">
            <i class="fal fa-plus"></i> {$_L['New Period']|default:'New Period'}
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>{$_L['Period Name']|default:'Period Name'}</th>
                <th>{$_L['Start Date']|default:'Start Date'}</th>
                <th>{$_L['End Date']|default:'End Date'}</th>
                <th>{$_L['Status']|default:'Status'}</th>
              </tr>
            </thead>
            <tbody>
            {foreach $periods|default:[] as $p}
              <tr>
                <td>{$p->period_name|default:''}</td>
                <td>{$p->start_date|default:''}</td>
                <td>{$p->end_date|default:''}</td>
                <td>
                  {if $p->is_closed}
                    <span class="label label-danger">{$_L['Closed']|default:'Closed'}</span>
                  {else}
                    <span class="label label-success">{$_L['Open']|default:'Open'}</span>
                  {/if}
                </td>
              </tr>
            {foreachelse}
              <tr><td colspan="4" class="text-center text-muted">{$_L['No periods found']|default:'No periods found'}</td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
{/block}


