
        <div class=" mx-auto p-3 ms-3" style="max-width: 650px;">

            <div class="panel-container ">
                <div class="panel-content">
                    <form class="form-horizontal" role="form" id="form_add_payment" method="post">
                        <div class="mb-3">
                            <lable>{{__('Account')}}</lable>
                            <select class="form-select" name="account">
                                <option value="">--</option>
                                {foreach $accounts as $account}
                                    <option value="{$account['id']}">{$account['account']}</option>
                                {/foreach}

                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="date">{{__('Date')}}</label>
                            <input type="text" class="form-control datepicker" value="2022-07-25" name="date" id="date" datepicker="" data-date-format="yyyy-mm-dd" data-auto-close="true">
                        </div>

                        <div class="mb-3">
                            <label for="description">{{__('Description')}}</label>
                            <input type="text" id="description" name="description" class="form-control" value="">
                        </div>


                        <div class="mb-3">
                            <label  for="amount"><span class="h6">{$_L['Amount']}</span></label>
                            <input type="text" class="form-control" id="amount" name="amount" {if $amount} value="{formatCurrency($amount,$currency)}" {/if} >
                        </div>

                        <div class="mb-3">
                            <label for="status"><span class="h6">{$_L['Status']}</span></label>
                            <select class="form-select" name="status" id="status">
                                <option value="Cleared">{$_L['Cleared']}</option>
                                <option value="Uncleared">{$_L['Uncleared']}</option>
                            </select>
                        </div>


                        <input type="hidden" name="invoice_id" value="{{$invoice_id}}">

                        {if $invoice}
                            <input type="hidden" name="currency" value="{{$invoice->currency}}">
                        {/if}

                        <button id="save_payment" class="btn btn-primary">{{__('Save')}}</button>

                    </form>
                </div>
            </div>


        </div>












