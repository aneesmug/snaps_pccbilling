{extends file="$layouts_admin"}

{block name="content"}
<!-- Accounting System - New Bank Reconciliation -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header"><h2 class="card-title"><i class="fal fa-file-text"></i> {$_title|default:"Accounting"}</h2></div><div class="card-body">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fal fa-plus-circle"></i>
                {$_L['New Bank Reconciliation']}
            </h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="{$_url}accounts/bank-reconciliation/list" class="btn btn-secondary">
                <i class="fal fa-arrow-left"></i>
                {$_L['Back']}
            </a>
        </div>
    </div>

    <!-- Reconciliation Form -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['Reconciliation Details']}</h2>
        </div>
        <div class="card-body">
            <form method="POST" id="reconForm">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{$_L['Bank Account']} <span class="text-danger">*</span></label>
                        <select name="bank_account_id" class="form-control" required>
                            <option value="">{$_L['Select Bank Account']}</option>
                            {foreach $bank_accounts as $account}
                                <option value="{$account->id}">
                                    {$account->name} ({$account->account_number})
                                </option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="col-md-6 form-group">
                        <label>{$_L['Statement Date']} <span class="text-danger">*</span></label>
                        <input type="text"  name="statement_date" class="form-control" required datepicker data-date-format="yyyy-mm-dd" data-auto-close="true">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{$_L['Opening Balance']} <span class="text-danger">*</span></label>
                        <input type="number" name="opening_balance" class="form-control" step="0.01" placeholder="0.00" required>
                        <small class="text-muted">{$_L['As shown on bank statement']}</small>
                    </div>

                    <div class="col-md-6 form-group">
                        <label>{$_L['Closing Balance']} <span class="text-danger">*</span></label>
                        <input type="number" name="closing_balance" class="form-control" step="0.01" placeholder="0.00" required>
                        <small class="text-muted">{$_L['As shown on bank statement']}</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>{$_L['Notes']}</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="{$_L['Add any notes about this reconciliation']}"></textarea>
                </div>

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fal fa-save"></i>
                        {$_L['Start Reconciliation']}
                    </button>
                    <a href="{$_url}accounts/bank-reconciliation/list" class="btn btn-secondary">
                        {$_L['Cancel']}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Information -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">{$_L['How to Reconcile']}</h2>
        </div>
        <div class="card-body">
            <ol>
                <li>{$_L['Select the bank account to reconcile']}</li>
                <li>{$_L['Enter the statement date from your bank']}</li>
                <li>{$_L['Enter the opening and closing balances from the bank statement']}</li>
                <li>{$_L['Click Start Reconciliation to begin matching transactions']}</li>
                <li>{$_L['Match GL transactions with those on the bank statement']}</li>
                <li>{$_L['Mark the reconciliation as complete when balanced']}</li>
            </ol>
        </div>
    </div>
</div>

</div></div></div></div>
{/block}
