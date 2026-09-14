{extends file="$layouts_client"}


{block name="content"}

    <div class="container mt-5 col-lg-9 col-12 mx-auto">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="mb-3">{$plan->title}</h2>

                        <div class="mb-5">
                            {$plan->description}
                        </div>

                        {if !empty($plan->features)}
                            {foreach json_decode($plan->features) as $feature}
                                <div class="justify-content-start d-flex  py-1 mt-3">
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#387BFF" class="bi bi-check-circle-fill text-primary"  viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                    </div>
                                    <div class="ps-2">
                                        <span class="">{$feature}</span>
                                    </div>
                                </div>

                            {/foreach}
                        {/if}

                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="mb-3">{formatCurrency($plan->price,$config['home_currency'])}</h2>
                            <div>
                                {if $user}
                                    {if $plan->stripe_pricing_id}
                                        <form action="{$base_url}subscriptions/stripe-create-checkout-session" method="POST">

                                            <input type="hidden" name="priceId" value="{$plan->stripe_pricing_id}" />
                                            <button type="submit" class="btn btn-primary">{__('Subscribe')}</button>
                                        </form>
                                    {/if}
                                {else}
                                    <div class="mb-3">
                                        {__('Login or Register to Subscribe')}
                                    </div>
                                    <a href="{$base_url}client/login&then=subscriptions/subscribe/{$plan->slug}" class="btn btn-primary">{$_L['Login']}</a>
                                    <a href="{$base_url}client/register&then=subscriptions/subscribe/{$plan->slug}" class="btn btn-primary">{$_L['Register']}</a>
                                {/if}
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{/block}

{block name=script}

    <script>

    </script>


{/block}
