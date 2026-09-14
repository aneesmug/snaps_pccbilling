{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - Tax Codes -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0"><i class="fal fa-tags"></i> {$_L['Tax Codes']}</h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="{$_url}accounts/tax-vat/list" class="btn btn-secondary">
                <i class="fal fa-arrow-left"></i> {$_L['Back']}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>{$_L['Code']}</th>
                            <th>{$_L['Name']}</th>
                            <th class="text-right">{$_L['Rate']}</th>
                            <th>{$_L['Type']}</th>
                            <th>{$_L['Status']}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if isset($tax_codes) && $tax_codes|@count > 0}
                            {foreach $tax_codes as $code}
                                <tr>
                                    <td><strong>{$code->code}</strong></td>
                                    <td>{$code->name}</td>
                                    <td class="text-right">{$code->rate|number_format:2}%</td>
                                    <td>{if isset($code->tax_type)}{$code->tax_type|capitalize}{else}-{/if}</td>
                                    <td>
                                        <span class="badge badge-{if isset($code->is_active) && $code->is_active}success{else}secondary{/if}">
                                            {if isset($code->is_active) && $code->is_active}{$_L['Active']}{else}{$_L['Inactive']}{/if}
                                        </span>
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{$_L['No tax codes found']}</td>
                            </tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div></div></div></div>
{/block}
