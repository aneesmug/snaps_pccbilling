{extends file="$layouts_admin"}

{block name="content"}

    <div class="container">
        <div class="card">
            <div class="card-body">
                <h3>{{__("Add New Subscription")}}</h3>
                <div class="hr-line-dashed"></div>

                <form id="form_main" method="post">

                    <div class="mb-3">
                        <label for="contact_id">{$_L['Customer']}</label>
                        <select class="form-select" data-pristine-required data-pristine-required-message="{$_L['This field is required']}" id="contact_id" name="contact_id">
                            <option value="0">{$_L['None']}</option>
                            {foreach $contacts as $contact}
                                <option value="{$contact->id}"
                                        {if !empty($subscription->contact_id) && ($contact->id == $subscription->contact_id)}
                                            selected

                                        {/if}
                                >{$contact->account}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="plan_id">{__('Subscription Plan')}</label>
                        <select class="form-select" id="plan_id" name="plan_id">
                            <option value="">{__('None')}</option>
                            {foreach $plans as $plan}
                                <option value="{$plan->id}"
                                        {if !empty($subscription->plan_id) && ($plan->id == $subscription->plan_id)}
                                            selected

                                        {/if}
                                        data-price="{{formatCurrency($plan->price)}}">{$plan->title}</option>
                            {/foreach}
                        </select>
                    </div>


                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="price">{__('Currency')}</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="">{__('None')}</option>
                                    {foreach $currencies as $currency}
                                        <option value="{$currency->iso_code}"
                                        {if empty($subscription)}
                                            {if $currency->iso_code == $config['home_currency']} selected {/if}
                                            {else}
                                            {if $currency->iso_code == $subscription->currency} selected {/if}
                                        {/if}
                                        >{$currency->iso_code}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="price">{__('Price')}</label>
                                <input id="price" name="price" {if !empty($subscription)} value="{{formatCurrency($subscription->price)}}" {/if} class="form-control mb-3">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quantity">{__('Quantity')}</label>
                                <input type="number" min="1" id="quantity" name="quantity" value="1" class="form-control mb-3">
                            </div>
                        </div>
                    </div>







                    <div class="mb-3">
                        <label>{{__('Type')}}</label>
                        <select class= "form-control" name="type" id="type">
                            <option value="daily" {if $subscription && $subscription->type == '+1 day'} selected{/if}>{$_L['Daily']}</option>
                            <option value="week1" {if $subscription && $subscription->type == '+1 week'} selected{/if}>{$_L['Weekly']}</option>
                            <option value="weeks2" {if $subscription && $subscription->type == '+2 weeks'} selected{/if}>{$_L['Weeks_2']}</option>
                            <option value="weeks3" {if $subscription && $subscription->type == '+3 weeks'} selected{/if}>{$_L['Weeks_3']}</option>
                            <option value="weeks4" {if $subscription && $subscription->type == '+4 weeks'} selected{/if}>{$_L['Weeks_4']}</option>
                            <option value="month1" {if $subscription} {if $subscription->type == '+1 month'} selected{/if} {else} selected {/if}>{$_L['Monthly']}</option>
                            <option value="months2" {if $subscription && $subscription->type == '+2 months'} selected{/if}>{$_L['Months_2']}</option>
                            <option value="months3" {if $subscription && $subscription->type == '+3 months'} selected{/if}>{$_L['Months_3']}</option>
                            <option value="months6" {if $subscription && $subscription->type == '+6 months'} selected{/if}>{$_L['Months_6']}</option>
                            <option value="year1" {if $subscription && $subscription->type == '+1 year'} selected{/if}>{$_L['Yearly']}</option>
                            <option value="years2" {if $subscription && $subscription->type == '+2 years'} selected{/if}>{$_L['Years_2']}</option>
                            <option value="years3" {if $subscription && $subscription->type == '+3 years'} selected{/if}>{$_L['Years_3']}</option>
                        </select

                    </div>
                    <div class="mb-3 mt-3">
                        <label>{{__('Status')}}</label>
                        <select class= "form-control" name="status" id="status">
                            <option value="Active" {if $subscription && $subscription->status == 'Active'} selected{/if}>
                                {{__('Active')}}
                            </option>
                            <option value="Inactive" {if $subscription && $subscription->status == 'Inactive'} selected{/if}>
                                {{__('Inactive')}}
                            </option>
                            <option value="Suspended" {if $subscription && $subscription->status == 'Suspended'} selected{/if}>{{__('Suspended')}}
                            </option>
                            <option value="Cancelled" {if $subscription && $subscription->status == 'Cancelled'} selected{/if}>{{__('Cancelled')}}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3 mt-3">
                        <label>
                            {$_L['Start Date']}
                        </label>
                        <input class="form-control" name="start_date" value="{date('Y-m-d')}"  id="start_date" datepicker data-date-format="yyyy-mm-dd" data-auto-close="true" data-pristine-required data-pristine-required-message="{$_L['This field is required']}">
                    </div>

                    {if !empty($subscription)}
                        <input type="hidden" name="id" value="{$subscription->id}">
                    {/if}

                    <button id="btn_plan" type="submit" class="btn btn-primary">{{__('Save')}}</button>
                </form>

            </div>
        </div>
    </div>

{/block}

{block name=script}


    <script>

        $(function () {

            var form = document.getElementById("form_main");
            var pristine = new Pristine(form);
            let $form_main = $('#form_main');
            let $start_date = $("#start_date");


            $("#contact_id").select2({
                    language: {
                        noResults: function () {
                            return $("#_lan_no_results_found").val();
                        }
                    }
                }
            );

            // $('[data-toggle="datepicker"]').datepicker();

            $form_main.on('submit',function (event) {
                event.preventDefault();
                if(pristine.validate())
                {
                    axios.post(base_url + 'subscriptions/subscription',$form_main.serialize()).then(function (response) {

                        window.location = base_url + 'subscriptions/list/' + response.data.id;
                    }).catch(function (error) {
                        $.each(error.response.data, function(key, value) {
                            toastr.error(value);
                        });
                    });
                }

            });


            const plan_id = document.getElementById('plan_id');
            const price = document.getElementById('price');

            if(plan_id)
            {
                {if empty($subscription)}
                plan_id.addEventListener('change', function() {

                    let plan_price = this.options[this.selectedIndex].getAttribute('data-price');

                    if(price)
                        {
                            price.value = plan_price;
                        }

                });
                {/if}
            }


        });

    </script>


{/block}
