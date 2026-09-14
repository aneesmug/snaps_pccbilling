{extends file="$layouts_client"}

{block name="content"}

    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <section id="pricing-plan">
                    <!-- title text and switch button -->
                    <div class="text-center">
                        <h1 class="mt-5 mb-4">{{__("Subscription Plans")}}</h1>
                    </div>
                    <!--/ title text and switch button -->
                    <!-- pricing plan cards -->
                    <div class="row pricing-card">
                        <div class="col-12 col-sm-offset-2 col-sm-10 col-md-12 col-lg-offset-2 col-lg-10 mx-auto">
                            <div class="row">
                                <!-- basic plan -->
                                {foreach $subscriptions as $subscription}
                                    <div class="col-12 col-md-4 mb-4">
                                        <div class="card basic-pricing text-center">
                                            <div class="card-body">
                                                <h2>
                                                    {$subscription->title}
                                                </h2>
                                                <p class="card-text">
                                                    {$subscription->description}
                                                </p>
                                                <div class="annual-plan">
                                                    <div class="plan-price mt-2">

                                                        <span class="h1">{formatCurrency($subscription->price,$config['home_currency'])}</span>
{*                                                        <sub class="pricing-duration text-body fw-bold">/{{$subscription->type}}</sub>*}
                                                    </div>
                                                    <small class="annual-pricing d-none text-muted"></small>
                                                </div>
                                                {if !empty($subscription->features)}
                                                    {foreach json_decode($subscription->features) as $feature}
                                                        <div class="justify-content-start d-flex  py-1 mt-3">
                                                            <div>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#387BFF" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                                                </svg>
                                                            </div>
                                                            <div class="ps-2">
                                                                <span class="text-sm">{$feature}</span>
                                                            </div>
                                                        </div>

                                                    {/foreach}
                                                {/if}

                                                <ul class="list-group list-group-circle text-center mt-4">
                                                </ul>
                                                <a href="{$_url}subscriptions/subscribe/{$subscription->slug}" class="btn w-100 btn-info-light mt-2 fw-bolder">{{__('Learn More')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ basic plan -->
                                {/foreach}
                            </div>
                        </div>
                    </div>
                    <!--/ pricing plan cards -->

                </section>

            </div>
        </div>
    </div>


{/block}
