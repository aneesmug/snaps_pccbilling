{extends file="$layouts_admin"}

{block name="content"}

<div class="row">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-hdr">
                <h2>NRE Return Invoice (ZATCA)</h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <p class="text-muted">Select a ZATCA submitted invoice and create a return invoice. Then open it and click ZATCA Submit Return.</p>

                    <form method="get" action="{$_url}invoices/nre-return/" class="mb-3">
                        <input type="hidden" name="ng" value="invoices/nre-return">
                        <div class="row align-items-end">
                            <div class="col-md-6 mb-2">
                                <label class="form-label"><strong>Submitted Invoice</strong></label>
                                <select class="form-control" name="invoice_id" required>
                                    <option value="">Select invoice</option>
                                    {foreach $submitted_invoices as $inv}
                                        {assign var="inv_number" value=$inv['invoicenum']|default:''}
                                        {assign var="inv_code" value=$inv['cn']|default:$inv['id']}
                                        <option value="{$inv['id']}" {if $selected_invoice_id == $inv['id']}selected{/if}>
                                            {$inv_number}{$inv_code} - {$inv['account']} ({$inv['date']})
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="submit" class="btn btn-info"><i class="fal fa-search"></i> Load Invoice Items</button>
                                <a href="{$_url}invoices/list/" class="btn btn-secondary">Back to Invoices</a>
                            </div>
                        </div>
                    </form>

                    {if $source_invoice}
                        <form method="post" action="{$_url}invoices/nre-return-create/">
                            <input type="hidden" name="source_invoice_id" value="{$source_invoice->id}">

                            <div class="alert alert-info">
                                <strong>Source:</strong> {Invoice::getInvoiceNumber($source_invoice)} - {$source_invoice->account} |
                                <strong>Date:</strong> {$source_invoice->date} |
                                <strong>Total:</strong> {$_c['symbol']} {$source_invoice->total|number_format:2}
                            </div>

                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width:60px;">Select</th>
                                        <th>Description</th>
                                        <th style="width:140px;">Original Qty</th>
                                        <th style="width:140px;">Returned Qty</th>
                                        <th style="width:140px;">Remaining Qty</th>
                                        <th style="width:140px;">Unit Price</th>
                                        <th style="width:160px;">Return Qty</th>
                                        <th style="width:140px;">Line Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $source_invoice_items as $item}
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="js-item-select" name="selected_items[]" value="{$item->id}" {if isset($item->remaining_qty) && $item->remaining_qty <= 0}disabled{/if}>
                                            </td>
                                            <td>{$item->description|escape}</td>
                                            <td>{$item->qty}</td>
                                            <td>{if isset($item->returned_qty)}{$item->returned_qty|number_format:2}{else}0.00{/if}</td>
                                            <td>{if isset($item->remaining_qty)}{$item->remaining_qty|number_format:2}{else}{$item->qty}{/if}</td>
                                            <td>{$_c['symbol']} {$item->amount|number_format:2}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    class="form-control js-return-qty"
                                                    name="return_qty[{$item->id}]"
                                                    min="0"
                                                    max="{if isset($item->remaining_qty)}{$item->remaining_qty}{else}{$item->qty}{/if}"
                                                    step="0.01"
                                                    value="0"
                                                    data-max="{if isset($item->remaining_qty)}{$item->remaining_qty}{else}{$item->qty}{/if}"
                                                    {if isset($item->remaining_qty) && $item->remaining_qty <= 0}disabled{/if}
                                                >
                                            </td>
                                            <td>{$_c['symbol']} {$item->total|number_format:2}</td>
                                        </tr>
                                    {foreachelse}
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No invoice items found.</td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-success"><i class="fal fa-check"></i> Create Return Invoice</button>
                            <button type="button" class="btn btn-warning js-full-return"><i class="fal fa-reply-all"></i> Full Return Invoice</button>
                        </form>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

{/block}

{block name="script"}
<script>
    $(function () {
        $(document).on('change', '.js-item-select', function () {
            var $row = $(this).closest('tr');
            var $qty = $row.find('.js-return-qty');
            if ($(this).is(':checked')) {
                if (parseFloat($qty.val() || '0') <= 0) {
                    $qty.val($qty.data('max'));
                }
            } else {
                $qty.val('0');
            }
        });

        $(document).on('input', '.js-return-qty', function () {
            var max = parseFloat($(this).data('max') || '0');
            var value = parseFloat($(this).val() || '0');
            if (value > max) {
                $(this).val(max.toFixed(2));
            }

            var $row = $(this).closest('tr');
            $row.find('.js-item-select').prop('checked', parseFloat($(this).val() || '0') > 0);
        });

        $(document).on('click', '.js-full-return', function () {
            $('.js-return-qty').each(function () {
                var max = parseFloat($(this).data('max') || '0');
                if (max > 0) {
                    $(this).val($(this).data('max'));
                    $(this).closest('tr').find('.js-item-select').prop('checked', true);
                }
            });
        });
    });
</script>
{/block}
