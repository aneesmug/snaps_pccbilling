{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - New Journal Entry -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-plus-circle"></i>
                {$_L['New Journal Entry']}
            </h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="{$_url}accounts/journal-entries" class="btn btn-secondary">
                <i class="fal fa-arrow-left"></i>
                {$_L['Back']}
            </a>
        </div>
    </div>

    <!-- Journal Entry Form -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Entry Details']}</h2>
        </div>
        <div class="card-body">
            <form method="POST" id="jeForm">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{$_L['Entry Date']} <span class="text-danger">*</span></label>
                        <input type="text"  name="entry_date" class="form-control" value="{$TODAY}" required datepicker data-date-format="yyyy-mm-dd" data-auto-close="true">
                    </div>

                    <div class="col-md-6 form-group">
                        <label>{$_L['Reference']} <span class="text-danger">*</span></label>
                        <input type="text" name="reference" class="form-control" placeholder="JE-2024-001" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>{$_L['Description']} <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="2" placeholder="{$_L['Describe the purpose of this entry']}" required></textarea>
                </div>

                <!-- Line Items -->
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="mb-3">{$_L['Line Items']}</h2>
                        <div class="table-responsive">
                            <table class="table table-sm datatable" id="lineItems">
                                <thead class="table-light">
                                    <tr>
                                        <th width="35%">{$_L['Account']}</th>
                                        <th class="text-right" width="30%">{$_L['Debit']}</th>
                                        <th class="text-right" width="30%">{$_L['Credit']}</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="line-item">
                                        <td>
                                            <select name="lines[0][account_id]" class="form-control form-control-sm" required>
                                                <option value="">{$_L['Select Account']}</option>
                                                {foreach $accounts as $acc}
                                                    <option value="{$acc->id}">{$acc->code} - {$acc->name}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="lines[0][debit]" class="form-control form-control-sm debit" step="0.01" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" name="lines[0][credit]" class="form-control form-control-sm credit" step="0.01" placeholder="0.00">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger" onclick="removeLineItem(this)">
                                                <i class="fal fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td><strong>{$_L['TOTAL']}</strong></td>
                                        <td class="text-right"><strong id="totalDebit">0.00</strong></td>
                                        <td class="text-right"><strong id="totalCredit">0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" class="btn btn-info" onclick="addLineItem()">
                            <i class="fal fa-plus"></i>
                            {$_L['Add Line']}
                        </button>
                    </div>
                </div>

                <!-- Balance Indicator -->
                <div class="mt-3">
                    <div id="balanceAlert" class="alert" style="display: none;">
                        <i class="fal fa-check-circle"></i> {$_L['Entry is balanced (Debit = Credit)']}
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-group mt-4 text-right">
                    <button type="submit" name="action" value="draft" class="btn btn-warning">
                        <i class="fal fa-save"></i>
                        {$_L['Save as Draft']}
                    </button>
                    <button type="submit" name="action" value="post" class="btn btn-success">
                        <i class="fal fa-check"></i>
                        {$_L['Post Entry']}
                    </button>
                    <a href="{$_url}accounts/journal-entries" class="btn btn-secondary">
                        {$_L['Cancel']}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let lineCount = 1;

function addLineItem() {
    const tbody = document.getElementById('itemsBody');
    const newRow = tbody.querySelector('.line-item').cloneNode(true);
    
    // Update names with new index
    newRow.innerHTML = newRow.innerHTML.replace(/lines\[0\]/g, 'lines[' + lineCount + ']');
    
    tbody.appendChild(newRow);
    lineCount++;
}

function removeLineItem(btn) {
    const tbody = document.getElementById('itemsBody');
    if (tbody.children.length > 1) {
        btn.parentNode.parentNode.remove();
    } else {
        alert('{$_L['Cannot remove the last line item']}');
    }
}

document.addEventListener('change', function() {
    const debits = Array.from(document.querySelectorAll('.debit')).reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
    const credits = Array.from(document.querySelectorAll('.credit')).reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
    
    document.getElementById('totalDebit').textContent = debits.toFixed(2);
    document.getElementById('totalCredit').textContent = credits.toFixed(2);
    
    const alert = document.getElementById('balanceAlert');
    if (Math.abs(debits - credits) < 0.01 && debits > 0) {
        alert.style.display = 'block';
        alert.className = 'alert alert-success';
    } else {
        alert.style.display = 'none';
    }
});
</script>

</div></div></div></div>
{/block}
