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
                                                <h2 class="mt-3">
                                                    {if isset($plans[$subscription->plan_id])}
                                                        {$plans[$subscription->plan_id]->title}
                                                    {/if}
                                                </h2>
                                                <p class="card-text">
                                                    {if isset($plans[$subscription->plan_id])}
                                                        {$plans[$subscription->plan_id]->description}
                                                    {/if}
                                                </p>
                                                <div class="annual-plan">
                                                    <div class="plan-price mt-2">

                                                        <span class="h1">{formatCurrency($subscription->price,$config['home_currency'])}</span>
                                                        <sub class="pricing-duration text-body fw-bold">/{{$subscription->type}}</sub>
                                                    </div>
                                                    <small class="annual-pricing d-none text-muted"></small>
                                                </div>
                                                <span>Start date</span>
                                                <span class="badge badge-outline-success mt-3">{{$subscription->start_date}}</span>
                                                {if $subscription->status eq 'Active'}
                                                    <span class="badge badge-outline badge-outline-success">{$subscription->status}
                                                    </span>
                                                    <p>
                                                        <span>{{__('Next Renewal Date')}}</span>
                                                        <span class="badge badge-outline-danger mt-3">{{$subscription->start_date}}</span>
                                                    </p>

                                                {elseif $subscription->status eq 'Inactive'}
                                                    <span class="badge badge-outline badge-outline-info">{$subscription->status}</span>
                                                {elseif $subscription->status eq 'Suspended'}
                                                    <span class="badge badge-outline badge-outline-danger">{$subscription->status}</span>
                                                {elseif $subscription->status eq 'Cancelled'}
                                                    <span class="badge badge-outline badge-outline-warning">{$subscription->status}</span>

                                                {/if}

                                                {if $subscription->status eq 'active'}
                                                    <span>Status</span>
                                                    <span class="badge badge-outline-success mt-3">Active</span>
                                                {elseif $subscription->status eq 'cancelled'}
                                                    <span>Status</span>
                                                    <span class="badge badge-outline-danger mt-3">Cancelled</span>


                                                {/if}

                                                <ul class="list-group list-group-circle text-center mt-4">
                                                </ul>
                                                <a href="{$_url}subscriptions/view-subscription/{$subscription->id}" class="btn w-100 btn-info-light mt-2 fw-bolder">{{__('View Details')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ basic plan -->
                                    {/foreach}
                                </div>
                            </div>
                    </div>
                    <!--/ pricing plan cards -->

                    <!-- pricing free trial -->

                    <!--/ pricing free trial -->

                    <!-- pricing faq -->

                    <!--/ pricing faq -->
                </section>

            </div>
        </div>
    </div>

{*    <div class="panel">*}
{*        <div class="panel-hdr">*}
{*            <h2>{{__('My Subscriptions')}}</h2>*}
{*            <div class="panel-toolbar">*}

{*            </div>*}

{*        </div>*}
{*        <div class="panel-container">*}
{*            <div class="panel-content">*}


{*                <div class="card">*}

{*                </div>*}

{*                <table class="table table-striped table-responsive">*}
{*                    <thead style="background: #f0f2ff">*}
{*                    <tr>*}

{*                        <th scope="col" class="">Name</th>*}
{*                        <th scope="col" class="">Start date</th>*}
{*                        <th scope="col" class="">Next Renewal Date</th>*}
{*                        <th scope="col"></th>*}
{*                    </tr>*}
{*                    </thead>*}
{*                    <tbody>*}

{*                    {foreach $subscriptions as $subscription}*}
{*                        <tr>*}

{*                            <td class="h6">*}
{*                                {if isset($plans[$subscription->plan_id])}*}
{*                                    {$plans[$subscription->plan_id]->title}*}
{*                                {/if}*}
{*                            </td>*}
{*                            <td>{$subscription->start_date}</td>*}
{*                            <td>{$subscription->next_renewal_date}</td>*}

{*                            <td></td>*}
{*                        </tr>*}

{*                    {/foreach}*}



{*                    </tbody>*}
{*                </table>*}
{*            </div>*}

{*        </div>*}

{*    </div>*}

{/block}
