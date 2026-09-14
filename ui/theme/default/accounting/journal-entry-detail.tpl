{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-file-text"></i> {$_L['Journal Entry']|default:'Journal Entry'} #{$entry->id|default:''}</h2>
        <div class="ibox-tools">
          <a href="{$_url}accounts/journal-entries/list" class="btn btn-default">
            <i class="fal fa-arrow-left"></i> {$_L['Back']|default:'Back'}
          </a>
        </div>
      </div>
      <div class="card-body">

        <div class="row mb-3">
          <div class="col-md-3"><strong>{$_L['Date']|default:'Date'}:</strong> {$entry->entry_date|default:''}</div>
          <div class="col-md-3"><strong>{$_L['Reference']|default:'Reference'}:</strong> <code>{$entry->reference|default:''}</code></div>
          <div class="col-md-3"><strong>{$_L['Status']|default:'Status'}:</strong>
            {if $entry->post_status eq 'posted'}<span class="label label-success">Posted</span>
            {elseif $entry->post_status eq 'draft'}<span class="label label-warning">Draft</span>
            {else}<span class="label label-default">{$entry->post_status|default:''}</span>{/if}
          </div>
          <div class="col-md-3"><strong>{$_L['Description']|default:'Description'}:</strong> {$entry->description|default:''}</div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>{$_L['Account']|default:'Account'}</th>
                <th>{$_L['Description']|default:'Description'}</th>
                <th class="text-right">{$_L['Debit']|default:'Debit'}</th>
                <th class="text-right">{$_L['Credit']|default:'Credit'}</th>
              </tr>
            </thead>
            <tbody>
            {foreach $items|default:[] as $i}
              <tr>
                <td>{$i->account_id|default:''}</td>
                <td>{$i->description|default:''}</td>
                <td class="text-right">{($i->debit_amount|default:0)|number_format:2}</td>
                <td class="text-right">{($i->credit_amount|default:0)|number_format:2}</td>
              </tr>
            {foreachelse}
              <tr><td colspan="4" class="text-center text-muted">{$_L['No items']|default:'No items'}</td></tr>
            {/foreach}
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
{/block}

