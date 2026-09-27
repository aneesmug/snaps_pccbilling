{* Input VAT fields for an expense. Optional: $vat_row (existing sys_transactions row). *}
<div class="card border mb-3 js-expense-vat">
    <div class="card-body pb-1">
        <div class="mb-3">
            <label for="vat_treatment"><span class="h6">VAT Treatment</span></label>
            <select class="form-select" name="vat_treatment" id="vat_treatment">
                {foreach $vat_treatments as $vt_key => $vt_label}
                    <option value="{$vt_key}" {if isset($vat_row) && $vat_row && $vat_row['vat_treatment'] eq $vt_key}selected{/if}>{$vt_label}</option>
                {/foreach}
            </select>
            <small class="help-block" id="vat_treatment_help"></small>
        </div>

        <div class="mb-3" id="vat_amount_group">
            <label for="vat_amount"><span class="h6">VAT Amount (included in amount)</span></label>
            <div class="input-group">
                <input type="text" class="form-control" id="vat_amount" name="vat_amount" inputmode="decimal"
                       value="{if isset($vat_row) && $vat_row && $vat_row['tax'] > 0}{$vat_row['tax']}{/if}" placeholder="0.00">
                <button type="button" class="btn btn-outline-secondary" id="vat_calc_15">15% incl.</button>
            </div>
            <small class="help-block">Only VAT shown on a valid tax invoice (or customs declaration) can be claimed.</small>
        </div>

        <div class="mb-3">
            <label for="supplier_vat_number"><span class="h6">Supplier VAT No.</span></label>
            <input type="text" class="form-control" id="supplier_vat_number" name="supplier_vat_number" inputmode="numeric" maxlength="15"
                   placeholder="15 digits, starts and ends with 3"
                   value="{if isset($vat_row) && $vat_row}{$vat_row['supplier_vat_number']}{/if}">
        </div>

        <div class="mb-3">
            <label for="supplier_invoice_number"><span class="h6" id="supplier_invoice_number_label">Supplier Invoice No.</span></label>
            <input type="text" class="form-control" id="supplier_invoice_number" name="supplier_invoice_number" maxlength="100"
                   value="{if isset($vat_row) && $vat_row}{$vat_row['supplier_invoice_number']}{/if}">
        </div>
    </div>
</div>
