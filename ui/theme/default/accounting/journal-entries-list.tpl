{extends file="$layouts_admin"}

{block name="content"}
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fal fa-pencil-square-o"></i> {$_L['Journal Entries']|default:'Journal Entries'}</h2>
        <div class="ibox-tools">
          <a href="{$_url}accounts/journal-entries/create" class="btn btn-primary">
            <i class="fal fa-plus"></i> {$_L['Create Entry']|default:'Create Entry'}
          </a>
        </div>
      </div>
      <div class="card-body">

        <form method="get" class="mb-3">
          <input type="hidden" name="ng" value="accounts/journal-entries/list">
          <div class="row">
            <div class="col-md-3 form-group">
              <label>{$_L['From']|default:'From'}</label>
              <input type="text" name="from_date" class="form-control" value="{$smarty.get.from_date|default:''}" datepicker data-date-format="yyyy-mm-dd" data-auto-close="true">
            </div>
            <div class="col-md-3 form-group">
              <label>{$_L['To']|default:'To'}</label>
              <input type="text" name="to_date" class="form-control" value="{$smarty.get.to_date|default:''}" datepicker data-date-format="yyyy-mm-dd" data-auto-close="true">
            </div>
            <div class="col-md-3 form-group">
              <label>{$_L['Status']|default:'Status'}</label>
              <select name="status" class="form-control">
                <option value="">{$_L['All Status']|default:'All Status'}</option>
                <option value="posted" {if $filter_status eq 'posted'}selected{/if}>Posted</option>
                <option value="draft" {if $filter_status eq 'draft'}selected{/if}>Draft</option>
                <option value="reversed" {if $filter_status eq 'reversed'}selected{/if}>Reversed</option>
              </select>
            </div>
            <div class="col-md-3 form-group" style="padding-top: 26px;">
              <button type="submit" class="btn btn-default">
                <i class="fal fa-search"></i> {$_L['Filter']|default:'Filter'}
              </button>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table id="ib_journal_entries_table" class="table table-striped table-bordered datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>{$_L['Date']|default:'Date'}</th>
                <th>{$_L['Reference']|default:'Reference'}</th>
                <th>{$_L['Description']|default:'Description'}</th>
                <th>{$_L['Status']|default:'Status'}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            {foreach $entries|default:[] as $entry}
              <tr>
                <td>{$entry->id|default:''}</td>
                <td>{$entry->entry_date|default:''}</td>
                <td><code>{$entry->reference|default:''}</code></td>
                <td>{$entry->description|default:''}</td>
                <td>
                  {if $entry->post_status eq 'posted'}
                    <span class="label label-success">Posted</span>
                  {elseif $entry->post_status eq 'draft'}
                    <span class="label label-warning">Draft</span>
                  {else}
                    <span class="label label-default">{$entry->post_status|default:''}</span>
                  {/if}
                </td>
                <td>
                  <a href="{$_url}accounts/journal-entries/view/{$entry->id}" class="btn btn-info">
                    <i class="fal fa-eye"></i> {$_L['View']|default:'View'}
                  </a>
                </td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
{/block}

{block name="script"}
<script>
$(function () {
  if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#ib_journal_entries_table')) {
    $('#ib_journal_entries_table').DataTable({
      responsive: true,
      pageLength: 25,
      language: {
        emptyTable: "{$_L['No journal entries found']|default:'No entries found'}",
        searchPlaceholder: "{$_L['Search']|default:'Search'}"
      }
    });
  }
});
</script>
{/block}


