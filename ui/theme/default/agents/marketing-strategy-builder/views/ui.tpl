{extends file="$layouts_admin"}

{block name="head"}
    <style>
        /* Container for the 3x4 grid (including the left labels) */
        .plan-container {
            display: grid;
            grid-template-columns: 80px 1fr 1fr 1fr; /* Left column for vertical text + 3 main columns */
            grid-template-rows: auto auto auto;     /* 3 rows (Before, During, After) */
            margin: 0 auto;
        }

        /* Left (vertical) labels for each row */
        .vertical-label {
            writing-mode: vertical-rl;   /* Makes text run top to bottom */
            text-orientation: mixed;
            transform: rotate(180deg);   /* Reverse direction to match your preview */
            color: #fff;
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            justify-content: center;     /* Center text horizontally */
            align-items: center;         /* Center text vertically */
            padding: 1rem;
            text-align: center;
        }

        /* Background colors for each row’s left label */
        .before {
            background: #f87171;  /* Pink-ish */
        }
        .during {
            background: #fbbf24;  /* Gold-ish */
        }
        .after {
            background: #4ade80;  /* Green-ish */
        }

        /* Main box styling */
        .box {
            background: #fff;
            border-left: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 1rem;
        }

        /* Headings inside each box */
        .box h2 {
            font-size: 1.1rem;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        /* Optional icon styling (if you want them left of text) */
        .box h2 .icon {
            margin-right: 0.5rem;
        }

        /* List of fill-in lines or bullet points */
        .fill-lines {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .fill-lines li {
            margin: 0.3rem 0;
            padding: 0.3rem 0;
            border-bottom: 1px dashed #ccc;
            color: #666; /* Placeholder text color */
        }
    </style>
{/block}



{block name="content"}

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">


                    <h5>{{__('Generate Marketing Plan')}}</h5>

                    <div class="mb-4">
                        <form action="{$base_url}ai/agents/generate-strategy" id="generate_plan_form" method="post">
                            <div class="mb-3">
                                <label for="business_name" class="form-label">{__('Business Name')}</label>
                                <input type="text" class="form-control" id="business_name" name="business_name" value="{$config['CompanyName']}" required>
                            </div>

                            <div class="mb-3">
                                <label for="business_short_description" class="form-label">{__('Business Short Description')}</label>
                                <textarea class="form-control" id="business_short_description" name="business_short_description" rows="3" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" id="btn_submit">
                                {{__('Generate')}}
                            </button>

                        </form>
                    </div>

                    <h5>{{__('Recent Marketing Plans')}}</h5>


                    <div class="mb-4">
                        <div class="list-group">

                            {foreach $marketing_plans as $plan}
                                <a href="{$base_url}ai/agents/marketing-strategy-builder/{$plan->uuid}" class="list-group-item list-group-item-action {if $selected_plan->uuid eq $plan->uuid}active{/if}">
                                    {$plan->title}
                                </a>
                            {/foreach}

                        </div>
                    </div>

                    <a href="{$base_url}ai/responses" class="btn btn-primary btn-sm">{__('Manage')}</a>

                </div>
            </div>
        </div>
        <div class="col-md-8" id="plan_details">


            {if !empty($selected_plan->error)}
                <div class="alert alert-danger" role="alert">
                    {$selected_plan->error}
                </div>
            {/if}

            {if empty($plan_details)}
                <p>{__('No Data Available')}</p>
            {else}

                {if !empty($plan_details->marketing_plan->title)}
                    <h2 class="text-center mb-3">{$plan_details->marketing_plan->title}</h2>
                {/if}

                <div class="plan-container">

                    <!-- Row 1, Left label -->
                    <div class="vertical-label before">
                        {if !empty($plan_details->marketing_plan->phases[0]->phase_name)}
                        {$plan_details->marketing_plan->phases[0]->phase_name}
                            {else}
                                Before (Prospect)
                        {/if}
                    </div>
                    <!-- Box 1 -->
                    <div class="box">
                        <h2><span class="icon">🎯</span>1. {$plan_details->marketing_plan->phases[0]->steps[0]->title}</h2>
                        <ul class="fill-lines">

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[0]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[0]->steps[0]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[0]->details->target_audience)}
                                <li><strong>Target Audience:</strong> {$plan_details->marketing_plan->phases[0]->steps[0]->details->target_audience}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[0]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[0]->steps[0]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[0]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[0]->steps[0]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}

                        </ul>
                    </div>
                    <!-- Box 2 -->
                    <div class="box">
                        <h2><span class="icon">💬</span>2. {$plan_details->marketing_plan->phases[0]->steps[1]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[0]->steps[1]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[0]->steps[1]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[1]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[0]->steps[1]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[1]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[0]->steps[1]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                    <!-- Box 3 -->
                    <div class="box">
                        <h2><span class="icon">📣</span>3. {$plan_details->marketing_plan->phases[0]->steps[2]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[0]->steps[2]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[0]->steps[2]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[2]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[0]->steps[2]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[0]->steps[2]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[0]->steps[2]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>

                    <!-- Row 2, Left label -->
                    <div class="vertical-label during">
                        {$plan_details->marketing_plan->phases[1]->phase_name}
                    </div>
                    <!-- Box 4 -->
                    <div class="box">
                        <h2><span class="icon">🌪</span>4. {$plan_details->marketing_plan->phases[1]->steps[0]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[1]->steps[0]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[1]->steps[0]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[1]->steps[0]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[1]->steps[0]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[1]->steps[0]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[1]->steps[0]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                    <!-- Box 5 -->
                    <div class="box">
                        <h2><span class="icon">♻</span>5. {$plan_details->marketing_plan->phases[1]->steps[1]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[1]->steps[1]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[1]->steps[1]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[1]->steps[1]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[1]->steps[1]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[1]->steps[1]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[1]->steps[1]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                    <!-- Box 6 -->
                    <div class="box">
                        <h2><span class="icon">💰</span>6. {$plan_details->marketing_plan->phases[1]->steps[2]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[1]->steps[2]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[1]->steps[2]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[1]->steps[2]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[1]->steps[2]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[1]->steps[2]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[1]->steps[2]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>

                    <!-- Row 3, Left label -->
                    <div class="vertical-label after">
                        After (Customer)
                    </div>
                    <!-- Box 7 -->
                    <div class="box">
                        <h2><span class="icon">🎁</span>7. {$plan_details->marketing_plan->phases[2]->steps[0]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[2]->steps[0]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[2]->steps[0]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[2]->steps[0]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[2]->steps[0]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[2]->steps[0]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[2]->steps[0]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                    <!-- Box 8 -->
                    <div class="box">
                        <h2><span class="icon">💎</span>8. {$plan_details->marketing_plan->phases[2]->steps[1]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[2]->steps[1]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[2]->steps[1]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[2]->steps[1]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[2]->steps[1]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[2]->steps[1]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[2]->steps[1]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                    <!-- Box 9 -->
                    <div class="box">
                        <h2><span class="icon">🤝</span>9. {$plan_details->marketing_plan->phases[2]->steps[2]->title}</h2>
                        <ul class="fill-lines">
                            {if !empty($plan_details->marketing_plan->phases[2]->steps[2]->objective)}
                                <li><strong>Objective:</strong> {$plan_details->marketing_plan->phases[2]->steps[2]->objective}</li>
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[2]->steps[2]->details->key_activities)}
                                {foreach $plan_details->marketing_plan->phases[2]->steps[2]->details->key_activities as $ka}
                                    <li><strong>Key Activity:</strong> {$ka}</li>
                                {/foreach}
                            {/if}

                            {if !empty($plan_details->marketing_plan->phases[2]->steps[2]->details->channels)}
                                {foreach $plan_details->marketing_plan->phases[2]->steps[2]->details->channels as $ch}
                                    <li><strong>Channel:</strong> {$ch}</li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>

                </div>
            {/if}
        </div>
    </div>



{/block}

{block name="script"}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const generate_plan_form = document.getElementById('generate_plan_form');
            const btn_submit = document.getElementById('btn_submit');

            const plan_details = document.getElementById('plan_details');

            generate_plan_form.addEventListener('submit', function (event) {
                event.preventDefault();

                btn_submit.innerHTML = '{{__('Generating')}}...';

                plan_details.innerHTML = `<h5 class="card-title placeholder-glow">
      <span class="placeholder col-6"></span>
    </h5>
    <p class="card-text placeholder-glow">
      <span class="placeholder col-7"></span>
      <span class="placeholder col-4"></span>
      <span class="placeholder col-4"></span>
      <span class="placeholder col-6"></span>
      <span class="placeholder col-8"></span>
    </p>
    <a class="btn btn-primary disabled placeholder col-6" aria-disabled="true"></a>`;

                const business_name = document.getElementById('business_name').value;
                const business_short_description = document.getElementById('business_short_description').value;

                axios.post('{$base_url}ai/agents/generate-strategy', {
                    business_name: business_name,
                    business_short_description: business_short_description
                }).then(function (response) {
                    window.location.reload();
                });

            });

        });
    </script>

{/block}
