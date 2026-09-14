{extends file="$layouts_admin"}


{block name="content"}



    <div class="container-xxl">

        <ol class="breadcrumb page-breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{$config['CompanyName']}</a></li>
            <li class="breadcrumb-item">{$_L['Dashboard']}</li>
            <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
        </ol>

        <div class="subheader">
            <h1 class="subheader-title">
                {$_L['Dashboard']}
            </h1>
            <div class="subheader-block d-lg-flex align-items-center">
                <a href="{$_url}dashboard/canvas/ai-insights" class="btn btn-primary btn-icon btn-sm me-2" data-bs-toggle="tooltip" data-bs-title="{__('Generate AI Insights')}">
                    <i class="fal fa-wand-magic"></i>
                </a>
            </div>
        </div>



        <div class="row">
            <div class="col-lg-12">
                <div id="panel-1" class="panel" data-panel-lock="false" data-panel-close="false" data-panel-collapsed="false"  data-panel-locked="false" data-panel-refresh="false" data-panel-reset="false">
                    <div class="panel-hdr">
                        <h2 class="fw-bolder">
                            {$_L['Summary']}
                        </h2>

                        <div class="panel-toolbar">
                            <a href="{$_url}dashboard" data-bs-toggle="tooltip" data-original-title="{$_L['Refresh']}">
                            <span class="svg-icon svg-icon-primary svg-icon-2x"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <rect x="0" y="0" width="24" height="24"/>
        <path d="M8.43296491,7.17429118 L9.40782327,7.85689436 C9.49616631,7.91875282 9.56214077,8.00751728 9.5959027,8.10994332 C9.68235021,8.37220548 9.53982427,8.65489052 9.27756211,8.74133803 L5.89079566,9.85769242 C5.84469033,9.87288977 5.79661753,9.8812917 5.74809064,9.88263369 C5.4720538,9.8902674 5.24209339,9.67268366 5.23445968,9.39664682 L5.13610134,5.83998177 C5.13313425,5.73269078 5.16477113,5.62729274 5.22633424,5.53937151 C5.384723,5.31316892 5.69649589,5.25819495 5.92269848,5.4165837 L6.72910242,5.98123382 C8.16546398,4.72182424 10.0239806,4 12,4 C16.418278,4 20,7.581722 20,12 C20,16.418278 16.418278,20 12,20 C7.581722,20 4,16.418278 4,12 L6,12 C6,15.3137085 8.6862915,18 12,18 C15.3137085,18 18,15.3137085 18,12 C18,8.6862915 15.3137085,6 12,6 C10.6885336,6 9.44767246,6.42282109 8.43296491,7.17429118 Z" fill="#000000" fill-rule="nonzero"/>
    </g>
</svg></span>
                            </a>
                        </div>

                    </div>

                    <div class="panel-container show">
                        <div class="panel-content border-left-0 border-right-0 border-top-0"

                                {if !empty($config['admin_dark_theme'])}
                                    style="background: #232F47;"
                                {else}
                                    style="background: #fff;"
                                {/if}

                        >
                            <div class="row no-gutters">

                                <div class="col-lg-4 col-xl-4">

                                    <div class="p-8  mb-2 flex-grow-1" style="
                                    {if !empty($config['admin_dark_theme'])}
                                            background: #232F47;
                                    {else}
                                            background: #242C3A;
                                    {/if}
                                            border-radius: 8px; padding: 26px 20px; margin-left:1rem; margin-right:1rem;
                                            box-shadow: 0 0.1rem 1rem 0.25rem rgb(0 0 0 / 5%);
                                            ">

                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-circle symbol-light-success symbol-40 flex-shrink-0 me-3">
                                                <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart-fill" viewBox="0 0 16 16">
  <path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z"/>
</svg><!--end::Svg Icon--></span>                            </div>
                                            </div>
                                            <div class="ms-3">
                                                <h3 class="text-white">{formatCurrency($total_income,$config['home_currency'])}</h3>
                                                <div class="font-size-sm text-white">{$_L['Total Income']}</div>
                                            </div>
                                        </div>
                                        <!--end::Item-->

                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-circle symbol-light-danger symbol-40 flex-shrink-0 me-3">
                                                <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-right-square-fill" viewBox="0 0 16 16">
  <path d="M14 16a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12zM5.904 5.197 10 9.293V6.525a.5.5 0 0 1 1 0V10.5a.5.5 0 0 1-.5.5H6.525a.5.5 0 0 1 0-1h2.768L5.197 5.904a.5.5 0 0 1 .707-.707z"/>
</svg>
                                    </span>                            </div>
                                            </div>
                                            <div class="ms-3">
                                                <h3 class="text-white">{formatCurrency($total_expense,$config['home_currency'])}</h3>
                                                <div class="font-size-sm text-white">{$_L['Total Expense']}</div>
                                            </div>
                                        </div>

                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-circle symbol-light-success symbol-40 flex-shrink-0 me-3">
                                                <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#14B981" class="bi bi-credit-card-fill" viewBox="0 0 16 16">
  <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7H0zm3 2h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1z"/>
</svg>
                                </span>                            </div>
                                            </div>
                                            <div class="ms-3">
                                                <h3 class="text-white">{formatCurrency($income_today,$config['home_currency'])}</h3>
                                                <div class="font-size-sm text-white">{$_L['Income Today']}</div>
                                            </div>
                                        </div>
                                        <!--end::Item-->

                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-circle symbol-light-warning symbol-40 flex-shrink-0 me-3">
                                                <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-square-fill" viewBox="0 0 16 16">
  <path d="M16 14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12zm-4.5-6.5H5.707l2.147-2.146a.5.5 0 1 0-.708-.708l-3 3a.5.5 0 0 0 0 .708l3 3a.5.5 0 0 0 .708-.708L5.707 8.5H11.5a.5.5 0 0 0 0-1z"/>
</svg>
                                    </span>
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <h3 class="text-white">{formatCurrency($expense_today,$config['home_currency'])}</h3>
                                                <div class="font-size-sm text-white">{$_L['Expense Today']}</div>
                                            </div>
                                        </div>
                                        <!--end::Item-->

                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-circle symbol-light-primary symbol-40 flex-shrink-0 me-3">
                                                <div class="symbol-label">
                                <span class="svg-icon svg-icon-md ">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#5046E4" class="bi bi-arrow-left-square-fill" viewBox="0 0 16 16">
  <path d="M16 14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12zm-4.5-6.5H5.707l2.147-2.146a.5.5 0 1 0-.708-.708l-3 3a.5.5 0 0 0 0 .708l3 3a.5.5 0 0 0 .708-.708L5.707 8.5H11.5a.5.5 0 0 0 0-1z"/>
</svg>
                                    </span>                            </div>
                                            </div>
                                            <div class="ms-3">
                                                <h3 class="text-white">{formatCurrency($income_this_month,$config['home_currency'])}</h3>
                                                <div class="font-size-sm text-white">{$_L['Income This Month']}</div>
                                            </div>
                                        </div>
                                        <!--end::Item-->

                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-circle symbol-light-danger symbol-40 flex-shrink-0 me-3">
                                                <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-square-fill" viewBox="0 0 16 16">
  <path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/>
</svg>
                                    </span>
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <h3 class="text-white">{formatCurrency($expense_this_month,$config['home_currency'])}</h3>
                                                <div class="font-size-sm text-white">{$_L['Expense This Month']}</div>
                                            </div>
                                        </div>

                                    </div>


                                </div>


                                <div class="col-lg-8 col-xl-8">

                                    <div style="padding-left: 0.5rem; padding-right: 1rem;">

                                        <div class="row">



                                            <div class="{if $config['leads'] && $config['companies']}col-md-4 {else} col-md-6{/if}">

                                                <a  href="{$base_url}contacts/list/">
                                                    <div class="card mb-2 shadow-none" style="



                                                    {if !empty($config['admin_dark_theme'])}
                                                            background-color: #213137;
                                                    {/if}

                                                            height: 130px"

                                                    >

                                                        <div class="card-body" >
                                                            <div class="row">
                                                                <div class="col">
                                                                    <div class="h1 mb-0">{$customers_total}</div>


                                                                </div>
                                                                <div class="col">
                                                                    <div class="symbol symbol-circle symbol-light symbol-40 flex-shrink-0 me-3 float-end">
                                                                        <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor]" class="bi bi-person-fill" viewBox="0 0 16 16">
  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
</svg>
                                </span>                            </div>
                                                                    </div>


                                                                </div>

                                                            </div>
                                                            <div class="row">
                                                                <div class="col">
                                                                    <h4 class="text-muted mt-1">{$_L['Customers']}</h4>

                                                                </div>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </a>


                                            </div>

                                            {if isset($config['companies']) && $config['companies']}

                                                <div class="{if $config['leads'] && $config['companies']}col-md-4 {else} col-md-6{/if}">

                                                    <a href="{$base_url}contacts/companies/">
                                                        <div class="card mb-2 shadow-none" style="

                                                        {if !empty($config['admin_dark_theme'])}
                                                                background-color: #372F29;
                                                        {/if}

                                                                height: 130px">

                                                            <div class="card-body" >
                                                                <div class="row">

                                                                    <div class="col">
                                                                        <div class="h1 mb-0">{$companies_total}</div>

                                                                    </div>
                                                                    <div class="col">
                                                                        <div class="symbol symbol-circle symbol-light symbol-40 flex-shrink-0 me-3 float-end">
                                                                            <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart-fill" viewBox="0 0 16 16">
  <path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z"/>
</svg>
                                </span>                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>


                                                                <h4 class="text-muted mt-1">{$_L['Companies']}</h4>
                                                            </div>

                                                        </div>
                                                    </a>


                                                </div>

                                            {/if}


                                            {if isset($config['leads']) && $config['leads']}

                                                <div class="{if $config['leads'] && $config['companies']}col-md-4 {else} col-md-6{/if}">

                                                    <a href="{$base_url}leads/">

                                                        <div class="card mb-2 shadow-none" style="

                                                        {if !empty($config['admin_dark_theme'])}
                                                                background-color: #372533;
                                                        {/if}
                                                                height: 130px"
                                                        >
                                                            <div class="card-body">

                                                                <div class="row">
                                                                    <div class="col">

                                                                        <div class="h1  mb-0">{$leads_total}</div>

                                                                    </div>
                                                                    <div class="col">

                                                                        <div class="symbol symbol-circle symbol-light symbol-40 flex-shrink-0 me-3 float-end">
                                                                            <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shift-fill" viewBox="0 0 16 16">
  <path d="M7.27 2.047a1 1 0 0 1 1.46 0l6.345 6.77c.6.638.146 1.683-.73 1.683H11.5v3a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1v-3H1.654C.78 10.5.326 9.455.924 8.816L7.27 2.047z"/>
</svg>
                                </span>                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>
                                                                <div class="row">
                                                                    <div class="col">
                                                                        <h4 class="text-muted mt-1">{$_L['Leads']}</h4>

                                                                    </div>
                                                                </div>



                                                            </div>
                                                        </div>

                                                    </a>

                                                </div>
                                            {/if}

                                        </div>
                                    </div>


                                    <div class="position-relative">



                                        <div class="col-md-12 mt-3">
                                            <div id="cash_flow_chart">

                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>



            </div>
        </div>





        <div class="row">





            <div class="col-lg-6">
                <div id="panel-12" class="panel border-0 ">
                    <div class="panel-hdr">
                        <h2 class="fw-bolder">
                            {$_L['Recent Clients']}
                        </h2>
                    </div>
                    <div class="panel-container show p-3">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="thead-light">
                                <tr>
                                    <th class="h6">
                                        {$_L['Image']}
                                    </th>

                                    <th class="h6">
                                        {$_L['Name']}
                                    </th>


                                    <th class="h6">
                                        {$_L['Created']}
                                    </th>
                                </tr>
                                </thead>
                                {foreach $recent_customers as $recent_customer}

                                    <tr>
                                        <td data-order="{$recent_customer@iteration}">
                                            <a href="{$_url}contacts/view/{$recent_customer->id}">{sp_get_contact_image($recent_customer)}</a>
                                        </td>
                                        <td>
                                            <a class="h6" href="{$base_url}contacts/view/{$recent_customer->id}">
                                                {$recent_customer->account}
                                            </a></br>
                                            {$recent_customer->email}
                                        </td>



                                        <td>
                                            {if !empty($recent_customer->created_at)}
                                                {date( $config['df'], strtotime($recent_customer->created_at))}
                                            {/if}
                                        </td>
                                    </tr>

                                {/foreach}
                            </table>

                        </div>

                    </div>
                </div>
            </div>








            <div class="col-lg-6">
                <div id="panel-3" class="panel border-0" data-panel-close="false">
                    <div class="panel-hdr">
                        <h2 class="fw-bolder">{$_L['Recent Invoices']}</h2>

                    </div>
                    <div class="panel-container show p-3">

                        <div class="table-responsive  widget-table">
                            <table class="table">
                                <thead class="thead-light">
                                <tr>
                                    <th class="h6">#</th>
                                    <th class="h6">{$_L['Account']}</th>
                                    <th class="h6">{$_L['Amount']}</th>
                                    <th class="h6">{$_L['Invoice Date']}</th>
                                    <th class="h6">{$_L['Due Date']}</th>
                                    <th class="h6">{$_L['Status']}</th>
                                </tr>
                                </thead>
                                {foreach $invoices as $ds}
                                    <tr class="cursor-pointer clickable-row" data-id="{$ds['id']}">
                                        <td class="text-info h6">
                                            <strong>
                                                {$ds['invoicenum']}{if $ds['cn'] neq ''} {$ds['cn']} {else} {$ds['id']} {/if}
                                            </strong>
                                        </td>
                                        <td class="h6">


                                            <strong>{$ds['account']}</strong> <br>
                                        </td>
                                        <td class="h6">{formatCurrency($ds['total'],$ds['currency_iso_code'])}</td>
                                        <td class="h6">

                                            {if !empty($ds['date'])}
                                                {date( $config['df'], strtotime($ds['date']))}
                                                {else}
                                                --
                                            {/if}


                                        </td>
                                        <td class="h6">

                                            {if !empty($ds['duedate'])}
                                                {date( $config['df'], strtotime($ds['duedate']))}
                                                {else}
                                                --
                                            {/if}

                                        </td>
                                        <td class="h6">
                                            {if $ds['status'] == 'Unpaid'}

                                                <span class="badge badge-outline badge-outline-danger">{ib_lan_get_line($ds['status'])}</span>

                                            {else}

                                                <span class="badge badge-outline  badge-outline-success">{ib_lan_get_line($ds['status'])}</span>

                                            {/if}


                                        </td>

                                    </tr>
                                {/foreach}
                            </table>

                        </div>

                        <div class="panel-content">
                            <div id="invoice_stats"></div>
                        </div>

                    </div>


                </div>
            </div>

            <div class="col-lg-6">
                <div id="panel-5" class="panel">
                    <div class="panel-hdr">
                        <h2 class="fw-bolder">
                            {$_L['Income_Vs_Expense']}
                        </h2>
                    </div>
                    <div class="panel-container show p-3">
                        <div id="chart_income_vs_expense"></div>
                    </div>
                </div>
            </div>

            {if isset($config['projects']) && $config['projects']}
                <div class="col-lg-6">
                    <div id="panel-4" class="panel border-0">
                        <div class="panel-hdr">
                            <h2 class="fw-bolder">
                                {$_L['Recent Projects']}
                            </h2>
                        </div>
                        <div class="panel-container border-0 show p-3">
                            <table class="table">
                                <thead class="thead-light">
                                <tr>

                                    <th class="h6">
                                        {$_L['Name']}
                                    </th>
                                    <th class="h6">
                                        {$_L['Budget']}

                                    </th>
                                    <td class="h6">
                                        {$_L['Status']}
                                    </td>

                                    <th class="h6">
                                        {$_L['Due Date']}
                                    </th>

                                </tr>
                                </thead>
                                {foreach $recent_projects as $project}

                                    <tr>

                                        <td class="h6">
                                            {$project->name}
                                        </td>
                                        <td class="h6">
                                            {formatCurrency($project->budget,$project->currency)}
                                        </td>
                                        <td>
                                            {if $project->status == 'Completed'}
                                                <span class="badge badge-outline text-uppercase badge-outline-success mb-4">{$project->status}</span>
                                            {else}
                                                <span class="badge badge-outline text-uppercase badge-outline-warning mb-4">{$project->status}</span>
                                            {/if}

                                        </td>
                                        <td>
                                            {if !empty($project->due_date)}
                                                {date( $config['df'], strtotime($project->due_date))}
                                                {else}
                                                --
                                            {/if}
                                        </td>

                                    </tr>

                                {/foreach}
                            </table>
                        </div>
                    </div>
                </div>

            {/if}

            <div class="col-lg-6">
                <div id="panel-11" class="panel border-0">
                    <div class="panel-hdr">
                        <h2 class="fw-bolder">
                            {$_L['Calendar']}
                        </h2>
                    </div>
                    <div class="panel-container show p-5">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>




            <div class="col-lg-6">
                <div class="panel" id="panel-7">
                    <div class="panel-hdr">

                        <h2 class="fw-bolder">{$_L['Latest Expense']}</h2>
                    </div>

                    <div class="panel-container show p-3 ">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="thead-light">
                                <tr>
                                    <th class="h6">{$_L['Date']}</th>
                                    <th class="h6">{$_L['Description']}</th>
                                    <th class="text-end h6 ">{$_L['Amount']}</th>
                                </tr>
                                </thead>
                                {foreach $exp as $exps}
                                    <tr>
                                        <td class="text-info h6">
                                            {if !empty($exps['date'])}
                                                {date( $config['df'], strtotime($exps['date']))}
                                                {else}
                                                --
                                            {/if}
                                        </td>
                                        <td><a href="{$_url}transactions/manage/{$exps['id']}/"><strong>{$exps['description']}</strong></a> </td>
                                        <td class="text-end text-danger amount h6">{formatCurrency($exps['amount'],$config['home_currency'])}</td>
                                    </tr>
                                {/foreach}



                            </table>
                        </div>

                    </div>

                </div>

            </div>
            <div class="col-lg-6">
                <div id="panel-2" class="panel" data-panel-fullscreen="false">
                    <div class="panel-hdr">
                        <h2 class="fw-bolder">
                            {$_L['Expense by Category']}
                        </h2>
                    </div>
                    <div class="panel-container show">

                        <div class="panel-content p-0">
                            <div id="expense_chart" class=""></div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-md-6">
                <div class="panel" id="panel-6">
                    <div class="panel-hdr">
                        <h2 class="fw-bolder">{{__('Net Worth n Account Balances')}}</h2>
                        <div class="panel-toolbar">

                            <a href="#" id="set_goal" class="btn btn-primary btn-xs pull-right"> {{__('Set Goal')}} </a>
                        </div>


                    </div>
                    <div class="panel-container" style="min-height: 365px;">
                        <div class="panel-content">

                            <h1 class="text-center amount">{formatCurrency($net_worth,$config['home_currency'])}</h1>


                            <div>
                                <span class="amount">{formatCurrency($net_worth,$config['home_currency'])}</span> {$_L['of']} <span class="amount">{$config['networth_goal']}</span>
                                <small class="float-right"><span class="amount_no_currency">{$pg}</span>%</small>

                                <div class="progress progress-small">
                                    <div style="width: {$pgb}%;" class="progress-bar progress-bar-{$pgc}  bg-success"></div>
                                </div>

                            </div>


                            <table class="table" style="margin-top: 26px;">

                                <thead class="thead-light">
                                <tr>
                                    <th class="text-info h6"><strong>{$_L['Account']}</strong></th>

                                    <th class="text-end h6">{$_L['Balance']}</th>
                                </tr>
                                </thead>


                                {foreach $balances['banks'] as $bank}

                                    <tr>
                                        <td class="text-info h6"><strong>{$bank->account}</strong></td>

                                        <td class="text-end h6">
                                            {if (isset($balances['balances'][$bank->id]))}
                                                {formatCurrency($balances['balances'][$bank->id],$balances['home_currency']->iso_code)}
                                            {/if}
                                        </td>

                                    </tr>

                                {/foreach}



                            </table>



                        </div>





                        <div>





                        </div>






                    </div>
                </div>
            </div>

            {if $config['invoicing'] && $all_data_access}


                <div class="col-md-6">

                    <div class="panel" id="panel-7">
                        <div class="panel-hdr">
                            <h2 class="fw-bolder">{$_L['Sales']}</h2>
                        </div>
                        <div class="panel-container">
                            <div class="panel-content">
                                <div class="ibox-content">

                                    <div id="sales_pie_graph"></div>

                                </div>
                            </div>
                        </div>

                    </div>


                </div>

                <div class="col-md-6">
                    <div class="panel" id="panel-8">
                        <div class="panel-hdr">
                            <h2 class="fw-bolder">{$_L['Sales']}</h2>
                            <div class="panel-toolbar">
                                <a href="{$_url}invoices/list/" class="btn btn-primary btn-xs pull-right"> {$_L['Invoices']}</a>
                            </div>



                        </div>
                        <div class="panel-container">
                            <table class="table table-striped " >
                                <thead class="thead-light">
                                <tr>

                                    <th class="h6"><strong>{$_L['Name']}</strong></th>
                                    <th class="h6">{$_L['Sales Count']}</th>
                                    <th class="h6">{$_L['Amount']}</th>


                                </tr>
                                </thead>
                                <tbody>

                                {foreach $item_sold as $sold}
                                    <tr>

                                        <td class="text-info h6">
                                            <strong>
                                                {strTrunc($sold['name'],30)}
                                            </strong>

                                        </td>
                                        <td class="h6">{floor($sold['sold_count'])}</td>
                                        <td class="amount h6">{$sold['total_amount']}</td>



                                    </tr>
                                {/foreach}

                                </tbody>
                            </table>


                        </div>



                    </div>

                </div>
                <div class="col-lg-6">
                    <div class="panel" id="panel-6">
                        <div class="panel-hdr">

                            <h2 class="fw-bolder">{$_L['Latest Income']}</h2>
                        </div>
                        <div class="panel-container show p-3">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="thead-light">
                                    <tr>
                                        <th class="h6">{$_L['Date']}</th>
                                        <th class="h6">{$_L['Description']}</th>
                                        <th class="text-end h6">{$_L['Amount']}</th>
                                    </tr>
                                    </thead>
                                    {foreach $inc as $incs}
                                        <tr>
                                            <td class="h6 text-info">

                                                {if !empty($incs->date)}
                                                {date( $config['df'], strtotime($incs['date']))}
                                                {else}
                                                --
                                                {/if}


                                            </td>
                                            <td><a href="{$_url}transactions/manage/{$incs['id']}/"><strong>{$incs['description']}</strong></a> </td>
                                            <td class="text-end text-success amount h6">{formatCurrency($incs['amount'],$config['home_currency'])}</td>
                                        </tr>
                                    {/foreach}



                                </table>
                            </div>

                        </div>

                    </div>

                </div>

            {/if}



        </div>
    </div>


{/block}

{block name="script"}
    <script>

        function displayEvent(event_id=0,date=0)
        {
            $.fancybox.open({
                src  :  base_url + 'calendar/event/'+event_id+'/'+date,
                type : 'ajax',
                opts : {
                    afterShow : function( instance, current ) {
                        // $('[data-toggle="datepicker"]').datepicker();
                        // $start_time.timepicker();
                        // $end_time.timepicker();

                        $('#start').datepicker();
                        $('#start_time').timepicker();
                        $('#end').datepicker();

                        let eventForm = $('#eventForm');
                        eventForm.on('submit',function (e) {
                            e.preventDefault();
                            $.post( base_url + "calendar/save_event/", eventForm.serialize())
                                .done(function( data ) {



                                    if ($.isNumeric(data)) {

                                        location.reload();

                                    }

                                    else {
                                        toastr.error(data);
                                    }

                                });


                            return false;
                        });

                    }
                },
            });
        }

        $(function () {

            $.getJSON( "{$_url}dashboard/json_income_vs_exp", function( data ) {
                var options1 = {
                    chart: {
                        fontFamily: 'Open Sans, sans-serif',
                        height: 300,
                        {if !empty($config['admin_dark_theme'])}
                        foreColor: '#fff',
                        {/if}
                        toolbar: {
                            show: false
                        },
                        events: {
                            mounted: function(ctx, config) {
                                const highest1 = ctx.getHighestValueInSeries(0);
                                const highest2 = ctx.getHighestValueInSeries(1);

                                ctx.addPointAnnotation({
                                    x: new Date(ctx.w.globals.seriesX[0][ctx.w.globals.series[0].indexOf(highest1)]).getTime(),
                                    y: highest1,
                                    label: {
                                        style: {
                                            cssClass: 'd-none'
                                        }
                                    },
                                    customSVG: {
                                        SVG: '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#1b55e2" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="feather feather-circle"><circle cx="12" cy="12" r="10"></circle></svg>',
                                        cssClass: undefined,
                                        offsetX: -8,
                                        offsetY: 5
                                    }
                                })

                                ctx.addPointAnnotation({
                                    x: new Date(ctx.w.globals.seriesX[1][ctx.w.globals.series[1].indexOf(highest2)]).getTime(),
                                    y: highest2,
                                    label: {
                                        style: {
                                            cssClass: 'd-none'
                                        }
                                    },
                                    customSVG: {
                                        SVG: '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#e7515a" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="feather feather-circle"><circle cx="12" cy="12" r="10"></circle></svg>',
                                        cssClass: undefined,
                                        offsetX: -8,
                                        offsetY: 5
                                    }
                                })
                            },
                        }
                    },
                    {if !empty($config['admin_dark_theme'])}
                    colors: ["#4586DB", "#C23C3E"],
                    {else}
                    colors: ["#242C3A", "#FF016E"],
                    {/if}

                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: [0, 2, 5],
                        curve: 'smooth'
                    },
                    fill: {
                        opacity: [0.5, 0.25, 1],
                        gradient: { shade: '#1449DC', shadeIntensity: .8, opacityFrom: .8, opacityTo: .25, stops: [0, 95, 100] }
                    },

                    markers: {
                        discrete: [{
                            seriesIndex: 0,
                            dataPointIndex: 7,
                            fillColor: '#000',
                            strokeColor: '#000',
                            size: 5
                        }, {
                            seriesIndex: 2,
                            dataPointIndex: 11,
                            fillColor: '#000',
                            strokeColor: '#000',
                            size: 4
                        }]
                    },
                    subtitle: {
                        text: '{$_L['Net Worth']}',
                        align: 'left',
                        margin: 0,
                        offsetX: -10,
                        offsetY: 35,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            {if !empty($config['admin_dark_theme'])}
                            color:  '#fff',
                            {else}
                            color:  '#565674',
                            {/if}
                        }
                    },
                    title: {
                        text: '{formatCurrency($net_worth,$config['home_currency'])}',
                        align: 'left',
                        margin: 0,
                        offsetX: -10,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '25px',
                            {if !empty($config['admin_dark_theme'])}
                            color:  '#fff',
                            {else}
                            color:  '#0e1726'
                            {/if}
                        },
                    },

                    series: [{
                        type: 'area',
                        name: 'Income',
                        data: data.Income,

                    }, {
                        type: 'area',
                        name: 'Expenses',
                        data: data.Expense,
                    }],
                    labels: data.Months,
                    xaxis: {


                        crosshairs: {
                            show: true
                        },
                        labels: {
                            offsetX: 0,
                            offsetY: 5,
                            style: {
                                fontSize: '12px',
                                fontFamily: 'Open Sans, sans-serif',
                                cssClass: 'apexcharts-xaxis-title',
                            },
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value, index) {
                                return (value / 1000) + 'K'
                            },
                            offsetX: -22,
                            offsetY: 0,
                            style: {
                                fontSize: '12px',
                                fontFamily: 'Open Sans, sans-serif',
                                cssClass: 'apexcharts-yaxis-title',
                            },
                        }
                    },
                    grid: {
                        {if !empty($config['admin_dark_theme'])}
                        borderColor: "#2B2C3F",
                        {else}
                        borderColor: "#f1f1f1",
                        {/if}
                        padding: {
                            top: 10
                        },
                        strokeDashArray: 4,
                        yaxis: {
                            lines: {
                                show: true
                            }
                        }

                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        offsetY: -50,
                        fontSize: '16px',
                        fontFamily: 'Open Sans, sans-serif',
                        markers: {
                            width: 10,
                            height: 10,
                            strokeWidth: 0,
                            strokeColor: '#fff',
                            fillColors: undefined,
                            radius: 12,
                            onClick: undefined,
                            offsetX: 0,
                            offsetY: 0
                        },
                        itemMargin: {
                            horizontal: 0,
                            vertical: 20
                        }
                    },
                    tooltip: {
                        theme: 'dark',
                        marker: {
                            show: true,
                        },
                        x: {
                            show: false,
                        }
                    },
                    responsive: [{
                        breakpoint: 575,
                        options: {
                            legend: {
                                offsetY: -30,
                            },
                        },
                    }],
                    plotOptions: { bar: { horizontal: !1, columnWidth: "15%", endingShape: "rounded" } },
                    {if !empty($config['admin_dark_theme'])}
                    background: '#000',
                    foreColor: '#fff',
                    {/if}
                };


                var chart1 = new ApexCharts(
                    document.querySelector("#cash_flow_chart"),
                    options1
                );

                chart1.render();
            });


            var expense_chart_options = {
                chart: {
                    height: 403,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                    {if !empty($config['admin_dark_theme'])}
                    foreColor: '#fff',
                    {/if}

                },
                colors: ["#242C3A"],
                xaxis: {
                    categories: [
                        {foreach $expense_cats as $expnese_cat}
                        '{addslashes($expnese_cat->name)}',
                        {/foreach}
                    ],
                },
                series: [{
                    data: [{foreach $expense_cats as $expnese_cat}
                        {$expnese_cat->total_amount},
                        {/foreach}]
                }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        endingShape: 'rounded',

                    }
                },
                grid: {
                    {if !empty($config['admin_dark_theme'])}
                    borderColor: "#2B2C3F"
                    {else}
                    borderColor: "#f1f1f1"
                    {/if}

                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            }

            var expense_chart = new ApexCharts(
                document.querySelector("#expense_chart"),
                expense_chart_options
            );

            expense_chart.render();


            $.getJSON( "{$_url}dashboard/json_d_chart_data/", function( data ) {
                var chart_income_vs_expense_options = {
                    chart: {
                        height: 400,
                        type: 'bar',
                        toolbar: {
                            show: false,
                        },
                        {if !empty($config['admin_dark_theme'])}
                        foreColor: '#fff',
                        {/if}
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 0,
                        colors: ['transparent']
                    },
                    series: [{
                        name: '{$_L['Income']}',
                        data: data.Income
                    }, {
                        name: '{$_L['Expense']}',
                        data: data.Expense
                    }],
                    xaxis: {
                        categories: [
                            '01',
                            '02',
                            '03',
                            '04',
                            '05',
                            '06',
                            '07',
                            '08',
                            '09',
                            '10',
                            '11',
                            '12',
                            '13',
                            '14',
                            '15',
                            '16',
                            '17',
                            '18',
                            '19',
                            '20',
                            '21',
                            '22',
                            '23',
                            '24',
                            '25',
                            '26',
                            '27',
                            '28',
                            '29',
                            '30',
                            '31'
                        ],
                    },
                    yaxis: {
                        title: {
                            text: '{$_L['Amount']}'
                        }
                    },
                    colors: ["#5C34F4", "#FC9740"],
                    fill: {
                        opacity: 1

                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val
                            }
                        }
                    }
                }

                var chart_income_vs_expense = new ApexCharts(
                    document.querySelector("#chart_income_vs_expense"),
                    chart_income_vs_expense_options
                );

                chart_income_vs_expense.render();
            });

            $('.clickable-row').on('click',function () {
                window.location = base_url + 'invoices/view/' + $(this).data('id');
            });

            var invoice_stats = $("#invoice_stats");

            $.get( base_url + "dashboard/invoice_stats_json/", function( data ) {

                invoice_stats.html(' <span class="font-12 head-font txt-dark">{$_L['Unpaid']} <span class="float-right">'+ data.Unpaid.percentage +'%</span></span>\
                        <div class="progress my-2 progress-small mt-10">\
                        <div class="progress-bar progress-bar-danger" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100" style="width: '+ data.Unpaid.percentage +'%" role="progressbar"> <span class="sr-only">'+ data.Unpaid.percentage +'%</span> </div>\
                        </div>\
                        <span class="font-12 head-font txt-dark">{$_L['Partially Paid']} <span class="float-right">'+ data['Partially Paid'].percentage +'%</span></span>\
                        <div class="progress my-2 progress-small mt-10">\
                        <div class="progress-bar progress-bar-info" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100" style="width: '+ data['Partially Paid'].percentage +'%" role="progressbar"> <span class="sr-only">'+ data['Partially Paid'].percentage +'</span> </div>\
                        </div>\
                        <span class="font-12 head-font txt-dark">{$_L['Paid']} <span class="float-right">'+ data.Paid.percentage +'%</span></span>\
                        <div class="progress my-2 progress-small mt-10">\
                        <div class="progress-bar progress-bar-success" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100" style="width: '+ data.Paid.percentage +'%" role="progressbar"> <span class="sr-only">'+ data.Paid.percentage +'</span> </div>\
                        </div>\
                        ');
                invoice_stats.slideDown( "slow" );

                // Load Account Balances



            });


            $('#clx-page-content').smartPanel({
                localStorage: true,
                onChange: function () {},
                onSave: function () {},
                opacity: 1,
                deleteSettingsKey: '#deletesettingskey-options',
                settingsKeyLabel: 'Reset settings?',
                deletePositionKey: '#deletepositionkey-options',
                positionKeyLabel: 'Reset position?',
                sortable: true,
                buttonOrder: '%collapse% %fullscreen% %close%',
                buttonOrderDropdown: '%refresh% %locked% %color% %custom% %reset%',
                // customButton: true,
                // customButtonLabel: "Custom Button",
                onCustom: function () {},
                closeButton: false,
                onClosepanel: function() {
                    if (myapp_config.debugState)
                        console.log($(this).closest(".panel").attr('id') + " onClosepanel")
                },
                fullscreenButton: true,
                onFullscreen: function() {
                    if (myapp_config.debugState)
                        console.log($(this).closest(".panel").attr('id') + " onFullscreen")
                },
                collapseButton: true,
                onCollapse: function() {
                    if (myapp_config.debugState)
                        console.log($(this).closest(".panel").attr('id') + " onCollapse")
                },
                lockedButton: false,
                lockedButtonLabel: "Lock Position",
                onLocked: function() {
                    if (myapp_config.debugState)
                        console.log($(this).closest(".panel").attr('id') + " onLocked")
                },
                // refreshButton: true,
                // refreshButtonLabel: "Refresh Content",
                // onRefresh: function() {
                //     if (myapp_config.debugState)
                //         console.log($(this).closest(".panel").attr('id') + " onRefresh")
                // },
                colorButton: true,
                colorButtonLabel: "Panel Style",
                onColor: function() {
                    if (myapp_config.debugState)
                        console.log($(this).closest(".panel").attr('id') + " onColor")
                },
                panelColors: ['bg-primary-700 bg-success-gradient',
                    'bg-primary-500 bg-info-gradient',
                    'bg-primary-600 bg-primary-gradient',
                    'bg-info-600 bg-primray-gradient',
                    'bg-info-600 bg-info-gradient',
                    'bg-info-700 bg-success-gradient',
                    'bg-success-900 bg-info-gradient',
                    'bg-success-700 bg-primary-gradient',
                    'bg-success-600 bg-success-gradient',
                    'bg-danger-900 bg-info-gradient',
                    'bg-fusion-400 bg-fusion-gradient',
                    'bg-faded'],
                resetButton: true,
                resetButtonLabel: "Reset Panel",
                onReset: function() {
                    if (myapp_config.debugState)
                        console.log( $(this).closest(".panel").attr('id') + " onReset callback" )
                }
            });


            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl,
                {
                    plugins: ['dayGrid', 'list', 'timeGrid', 'interaction', 'bootstrap'],
                    themeSystem: 'bootstrap',
                    timeZone: 'UTC',
                    //dateAlignment: "month", //week, month
                    buttonText:
                        {
                            today: 'today',
                            month: 'month',
                            week: 'week',
                            day: 'day',
                            list: 'list'
                        },
                    eventTimeFormat:
                        {
                            hour: 'numeric',
                            minute: '2-digit',
                            meridiem: 'short'
                        },
                    navLinks: true,
                    header:
                        {
                            left: 'title',
                            center: '',
                            right: 'today prev,next'
                        },
                    footer:
                        {
                            left: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                            center: '',
                            right: ''
                        },
                    editable: true,
                    eventLimit: true, // allow "more" link when too many events
                    eventSources: [{
                        url: base_url + 'calendar/data/',
                        type: 'GET',
                        error: function() {

                        }
                    } ],
                    viewSkeletonRender: function()
                    {
                        $('.fc-toolbar .btn-default').addClass('btn-sm');
                        $('.fc-header-toolbar h2').addClass('fs-md');
                        $('#calendar').addClass('fc-reset-order')
                    },

                    dateClick: function(info) {
                        displayEvent(0,info.dateStr);
                    },
                    eventClick: function(info) {
                        displayEvent(info.event.id);
                    }


                });

            calendar.render();

            $("#set_goal").click(function (e) {
                e.preventDefault();

                (async () => {

                    const { value: result } = await Swal.fire({
                        title: '{__('Set New Goal for Net Worth')}',
                        input: 'text',
                        inputLabel: '{__('Goal')}',
                        inputPlaceholder: '{__('Amount')}',
                    })

                    if (result) {

                        $.post( base_url + "settings/networth_goal/", { goal: result })
                            .done(function( data ) {
                                location.reload();
                            });
                    }

                })()

            });




            var options = {
                series: [
                    {foreach $item_sold as $sold}
                    {$sold['sold_count']},
                    {/foreach}
                ],
                chart: {
                    height: 350,
                    type: 'radialBar',
                    {if !empty($config['admin_dark_theme'])}
                    foreColor: '#fff',
                    colors: ["#4586DB", "#C23C3E"],
                    {else}
                    colors: ["#7E5FF4", "#FB8141"],
                    {/if}


                },
                plotOptions: {
                    radialBar: {
                        dataLabels: {
                            name: {
                                fontSize: '22px',
                            },
                            value: {
                                fontSize: '16px',
                            },
                            total: {
                                show: true,
                                label: '{$_L['Total']}',
                                formatter: function (w) {
                                    // By default this function returns the average of all series. The below is just an example to show the use of custom formatter function
                                    return {count($item_sold)}
                                }
                            }
                        }
                    }
                },
                labels: [
                    {foreach $item_sold as $sold}
                    '{strTrunc(addslashes($sold['name']),30)}',
                    {/foreach}
                ],
                {if !empty($config['admin_dark_theme'])}
                background: '#000',
                {/if}
            }


            var chart = new ApexCharts(document.querySelector("#sales_pie_graph"), options);
            chart.render();




        });
    </script>
{/block}
