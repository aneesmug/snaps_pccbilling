{extends file="$layouts_admin"}

{block name="head"}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css" />
    <style>
        {if empty($config['admin_dark_theme'])}
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #F7F9FC;
        }
        .bg-success{
            color:#23a52d;
        }
        .bg-info{
            color:#2F92B5!important;
        }
        {/if}

        .clx-status-badge {
            display: inline-block;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
            color: #fff !important;
        }

        .clx-status-paid {
            background: #6d5efc;
        }

        .clx-status-unpaid {
            background: #f4b740;
        }

        .clx-status-partially-paid {
            background: #2f92b5;
        }

        .clx-status-returned {
            background: #f59e0b;
        }

        .clx-status-partially-returned {
            background: #3b82f6;
        }

        .clx-status-return-note {
            background: #e11d48;
        }

        .clx-status-cancelled {
            background: #4b5563;
        }

        .clx-zatca-reported {
            background: #10b981;
        }

        .clx-zatca-cleared {
            background: #2563eb;
        }

        .clx-zatca-registered {
            background: #0f766e;
        }

        .clx-zatca-pending {
            background: #f59e0b;
        }

        .clx-zatca-not-submitted {
            background: #94a3b8;
        }

        .clx-zatca-pass {
            background: #34c38f;
        }

        .clx-zatca-warning {
            background: #f59e0b;
        }

        .clx-zatca-failed {
            background: #ef4444;
        }

        @media (min-width: 1200px) {
            .clx-summary-col {
                flex: 0 0 25%;
                max-width: 25%;
            }
        }


    </style>

{/block}



{block name="content"}





    <div class="row">
        <div class="col-md-12">

            <div class="panel">

                <div class="panel-hdr">




                    <h2>{$_L['Invoices']}</h2>


                    <div class="panel-toolbar">

                        <div class="btn-group">
                            <a href="{$_url}invoices/add/" class="btn btn-primary  btn-sm"> {$_L['Add Invoice']}</a>
                            <a href="{$_url}reports/invoices/" class="btn btn-warning btn-sm"> {$_L['View Reports']}</a>
                        </div>

                    </div>
                </div>

                <div class="panel-container">
                    <div class="panel-content">
                        <div class="row">
                            <div class="col-xl-2 col-lg-3 col-md-6 clx-summary-col">
                                <div class="dashboard-stat2" style="background: linear-gradient(87deg,#2dce89 0,#2dcecc 100%)!important;border-radius: .375rem; min-height: 1px;
    padding: 1.5rem;
    flex: 1 1 auto">
                                    <div class="number">
                                        <h3 class="h2 font-weight-bold mb-0 text-white">
                                            <span>{formatCurrency($invoice_paid_amount,$config['home_currency'])}</span>
                                        </h3>
                                        <small class="h5  mb-0 text-white">{$_L['Paid']}</small>
                                    </div>

                                    <div class="progress-info">


                                        <div class="progress">
                                            <span style="width: {$p['Paid']['percentage']}%;" class="progress-bar bg-info">

                                            </span>
                                        </div>
                                        <div class="progress-status">
                                            <div class="text-nowrap text-white font-weight-600"> {$p['Paid']['percentage']}% </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 clx-summary-col">
                                <div class="dashboard-stat2" style="background: linear-gradient(87deg,#f5365c 0,#f56036 100%)!important;    border-radius: .375rem;min-height: 1px;
    padding: 1.5rem;
    flex: 1 1 auto">
                                    <div class="number">
                                        <h3 class="h2 font-weight-bold mb-0 text-white">
                                            <span>{formatCurrency($invoice_unpaid_amount,$config['home_currency'])}</span>
                                        </h3>
                                        <small class="h5 mb-0 text-white">{$_L['Unpaid']}</small>
                                    </div>
                                    <div class="progress-info">
                                        <div class="progress">
                                            <span style="width: {$p['Unpaid']['percentage']}%;" class="progress-bar  bg-success">
                                                <span class="sr-only">{$p['Unpaid']['percentage']}%</span>
                                            </span>
                                        </div>
                                        <div class="progress-status">
                                            <div class="text-nowrap text-white font-weight-600"> {$p['Unpaid']['percentage']}% </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 clx-summary-col">
                                <div class="dashboard-stat2 " style="background: linear-gradient(87deg,#5e72e4 0,#825ee4 100%)!important;    border-radius: .375rem; rgba(0,0,0,.05); min-height: 1px;
    padding: 1.5rem;
    flex: 1 1 auto">
                                    <div class="number">
                                        <h3 class="h2 font-weight-bold mb-0 text-white">
                                            <span>{formatCurrency($invoice_partially_paid_amount,$config['home_currency'])}</span>
                                        </h3>
                                        <small class="h5 mb-0 text-white"">{$_L['Partially Paid']}</small>
                                    </div>
                                    <div class="progress-info">
                                        <div class="progress">
                                            <span style="width: {$p['Partially Paid']['percentage']}%;" class="progress-bar  bg-success">
                                                <span class="sr-only">{$p['Partially Paid']['percentage']}%</span>
                                            </span>
                                        </div>
                                        <div class="progress-status">
                                            <div class="text-nowrap text-white font-weight-600"> {$p['Partially Paid']['percentage']}% </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 clx-summary-col">
                                <div class="dashboard-stat2" style="background: linear-gradient(87deg,#f59e0b 0,#ef4444 100%)!important;border-radius: .375rem; min-height: 1px;
    padding: 1.5rem;
    flex: 1 1 auto">
                                    <div class="number">
                                        <h3 class="h2 font-weight-bold mb-0 text-white">
                                            <span>{formatCurrency($invoice_returned_amount,$config['home_currency'])}</span>
                                        </h3>
                                        <small class="h5 mb-0 text-white">Returned Total</small>
                                    </div>

                                    <div class="progress-info">
                                        <div class="progress">
                                            <span style="width: {$returned_percentage|default:0}% ;" class="progress-bar bg-success">
                                            </span>
                                        </div>
                                        <div class="progress-status">
                                            <div class="text-nowrap text-white font-weight-600"> {$returned_percentage|default:0}% </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs nav-tabs-clean mb-3" role="tablist">
                            <li class="nav-item"><a class="nav-link {if $tab == 'filter'}active{/if}" href="{$base_url}invoices/list/">{$_L['Filter']}</a></li>
                            <li class="nav-item"><a class="nav-link {if $tab == 'unpaid'}active{/if}" href="{$base_url}invoices/list/">{$_L['Unpaid']}</a></li>
                            <li class="nav-item"><a class="nav-link {if $tab == 'partially_paid'}active{/if}" href="{$base_url}invoices/list/0/partially_paid/">{$_L['Partially Paid']}</a></li>
                            <li class="nav-item"><a class="nav-link  {if $tab == 'paid'}active{/if}" href="{$base_url}invoices/list/0/paid/">{$_L['Paid']}</a></li>
                            <li class="nav-item"><a class="nav-link {if $tab == 'return_invoices'}active{/if}" href="{$base_url}invoices/list/0/return_invoices/">Return Invoices</a></li>
                            <li class="nav-item"><a class="nav-link {if $tab == 'all'}active{/if}" href="{$base_url}invoices/list/0/all/">{$_L['All']}</a></li>
                        </ul>

                        <div class="table-responsive">

                            <table id="clx_datatable" class="table table-striped w-100 sys_table footable">
                                <thead style="background: #f0f2ff">
                                <tr>
                                    <th>#</th>
                                    <th>{$_L['Account']}</th>
                                    <th>{$_L['Customer Group']}</th>
                                    {* <th>{__('Staff')}</th> *}

                                    {if !empty($config['invoice_group'])}
                                        <th>
                                            {$_L['Group']}
                                        </th>
                                    {/if}

                                    {if !empty($config['invoice_single_service'])}
                                        <th>
                                            {__('Service')}
                                        </th>
                                    {/if}

                                    {* <th>{$_L['Title']}</th> *}

                                    <th>{$_L['Amount']}</th>
                                    <th>{$_L['Total Paid']}</th>
                                    <th>{{$_L['Amount Due']}}</th>
                                    <th>{$_L['Invoice Date']}</th>
                                    {* <th>{$_L['Due Date']}</th> *}
                                    <th>
                                        {$_L['Status']}
                                    </th>
                                    {if !empty($config['invoice_items_purchasing'])}
                                        <th>{__('Purchase Cost')}</th>
                                    {/if}
                                    {if !empty($config['invoice_items_shipping'])}
                                        <th>{__('Shipping Cost')}</th>
                                    {/if}
                                    <th>{__('ZATCA Status')}</th>
                                    <th>{__('ZATCA Validation')}</th>
                                    <th class="text-end" width="140px;">{$_L['Manage']}</th>
                                </tr>
                                </thead>
                                <tbody>

                                {foreach $d as $ds}
                                    <tr>
                                        <td data-value="{$ds['id']}" data-order="{$ds@iteration}"><a href="{$_url}invoices/view/{$ds['id']}/">{$ds['invoicenum']}{if $ds['cn'] neq ''} {$ds['cn']} {else} {$ds['id']} {/if}</a> </td>
                                        <td>
                                            {if isset($contacts[$ds['userid']])}
                                                <a href="{$_url}invoices/view/{$ds['id']}/">
                                                    <strong>
                                                        {$ds['account']}
                                                        {if $contacts[$ds['userid']]->company != ''}
                                                            <br>  {$contacts[$ds['userid']]->company}
                                                        {/if}
                                                    </strong>
                                                </a>
                                                {if $contacts[$ds['userid']]->email != ''}
                                                    <div class="mt-1">
                                                        {$contacts[$ds['userid']]->email}
                                                    </div>
                                                {/if}
                                                {if $contacts[$ds['userid']]->phone != ''}
                                                    <div class="mt-1">
                                                        {$contacts[$ds['userid']]->phone}
                                                    </div>
                                                {/if}
                                            {/if}
                                        </td>
                                        <td>
                                            {if isset($contacts[$ds['userid']])}
                                                {if !empty($contacts[$ds['userid']]->gid)}
                                                    {$contacts[$ds['userid']]->gname}
                                                {/if}
                                            {/if}
                                        </td>

                                        {*
                                        <td>
                                            {if isset($staffs[$ds['aid']])}
                                                {$staffs[$ds['aid']]->fullname}
                                            {/if}
                                        </td>
                                        *}

                                        {if !empty($config['invoice_group'])}
                                            <td>
                                                {if !empty($invoice_groups[$ds['group_id']])}
                                                    {$invoice_groups[$ds['group_id']]->name}
                                                {/if}
                                            </td>
                                        {/if}

                                        {if !empty($config['invoice_single_service'])}
                                            <td>
                                                {if !empty($services[$ds['service_id']])}
                                                    {$services[$ds['service_id']]->name}
                                                {/if}
                                            </td>
                                        {/if}

                                        {*
                                        <td>
                                            {if !empty($ds['title'])}
                                                <a href="{$_url}invoices/view/{$ds['id']}/">
                                                    {$ds['title']}
                                                </a>
                                            {/if}
                                        </td>
                                        *}
                                        <td>
                                            {if ($ds['type'] eq 'Credit Note' && $ds['parent_id'] gt 0) || $ds['status'] eq 'Cancelled'}
                                                <span class="text-danger fw-bold">- {if $ds['total'] lt 0}{formatCurrency((0 - $ds['total']),$ds['currency_iso_code'])}{else}{formatCurrency($ds['total'],$ds['currency_iso_code'])}{/if}</span>
                                            {else}
                                                {formatCurrency($ds['total'],$ds['currency_iso_code'])}
                                            {/if}
                                        </td>
                                        <td>
                                            {if !empty($ds['credit'] && $ds['credit'] > 0)}
                                                {formatCurrency($ds['credit'],$ds['currency_iso_code'])}
                                            {/if}
                                        </td>
                                        <td>
                                            {if !empty($ds['credit'] && $ds['credit'] > 0) && $ds['credit'] < $ds['total']}
                                                {formatCurrency(($ds['total']-$ds['credit']),$ds['currency_iso_code'])}
                                            {/if}
                                        </td>
                                        <td data-value="{strtotime($ds['date'])}">{date( $config['df'], strtotime($ds['date']))}</td>
                                        {* <td data-value="{strtotime($ds['duedate'])}">{date( $config['df'], strtotime($ds['duedate']))}</td> *}
                                        <td>

                                            {if $ds['status'] eq 'Unpaid'}
                                                <span class="clx-status-badge clx-status-unpaid">{ib_lan_get_line($ds['status'])}</span>
                                            {elseif $ds['status'] eq 'Paid'}
                                                <span class="clx-status-badge clx-status-paid">{ib_lan_get_line($ds['status'])}</span>
                                            {elseif $ds['status'] eq 'Partially Paid'}
                                                <span class="clx-status-badge clx-status-partially-paid">{ib_lan_get_line($ds['status'])}</span>
                                            {elseif $ds['status'] eq 'Cancelled'}
                                                <span class="clx-status-badge clx-status-cancelled">{ib_lan_get_line($ds['status'])}</span>
                                            {else}
                                                {ib_lan_get_line($ds['status'])}
                                            {/if}

                                            {if $ds['type'] eq 'Invoice' && isset($return_progress_by_invoice[$ds['id']])}
                                                {if $return_progress_by_invoice[$ds['id']]['is_full']}
                                                    <br><span class="clx-status-badge clx-status-returned mt-1">Returned</span>
                                                {elseif $return_progress_by_invoice[$ds['id']]['is_partial']}
                                                    <br><span class="clx-status-badge clx-status-partially-returned mt-1">Partially Returned</span>
                                                {/if}
                                            {/if}

                                            {if $ds['type'] eq 'Credit Note' && $ds['parent_id'] gt 0}
                                                <br><span class="clx-status-badge clx-status-return-note mt-1">Credit Note / Return</span>
                                            {/if}



                                        </td>

                                        {if !empty($config['invoice_items_purchasing'])}
                                            <td>
                                                {if !empty($ds['purchase_cost'])}
                                                    {formatCurrency($ds['purchase_cost'],$ds['currency_iso_code'])}
                                                {/if}
                                            </td>
                                        {/if}
                                        {if !empty($config['invoice_items_shipping'])}
                                            <td>
                                                {if !empty($ds['shipping_cost'])}
                                                    {formatCurrency($ds['shipping_cost'],$ds['currency_iso_code'])}
                                                {/if}
                                            </td>
                                        {/if}

                                        {* ZATCA Status Column *}
                                        <td>
                                            {assign var="zatca_reporting_status" value=''}
                                            {assign var="zatca_validation_status" value=''}
                                            {if !empty($ds['zatca_last_response'])}
                                                {assign var="zatca_response" value=$ds['zatca_last_response']|json_decode:true}
                                                {if is_array($zatca_response)}
                                                    {assign var="zatca_reporting_status" value=$zatca_response['reportingStatus']|default:''}
                                                    {if $zatca_reporting_status eq ''}
                                                        {assign var="zatca_reporting_status" value=$zatca_response['reporting_status']|default:''}
                                                    {/if}
                                                    {if $zatca_reporting_status eq ''}
                                                        {assign var="zatca_reporting_status" value=$zatca_response['clearanceStatus']|default:''}
                                                    {/if}
                                                {/if}
                                            {/if}
                                            {if $zatca_reporting_status eq 'REPORTED'}
                                                <span class="clx-status-badge clx-zatca-reported">Reported to ZATCA</span>
                                            {elseif $zatca_reporting_status eq 'CLEARED'}
                                                <span class="clx-status-badge clx-zatca-cleared">Cleared by ZATCA</span>
                                            {elseif $ds['zatca_status']|lower eq 'submitted'}
                                                <span class="clx-status-badge clx-zatca-registered">Registered</span>
                                            {elseif !empty($ds['zatca_status'])}
                                                <span class="clx-status-badge clx-zatca-pending">{$ds['zatca_status']|ucfirst}</span>
                                            {else}
                                                <span class="clx-status-badge clx-zatca-not-submitted">Not Submitted</span>
                                            {/if}
                                        </td>

                                        {* ZATCA Validation Status Column *}
                                        <td>
                                            {assign var="zatca_validation_status" value=''}
                                            {assign var="zatca_error_count" value=0}
                                            {assign var="zatca_reporting_status" value=''}
                                            {if !empty($ds['zatca_last_response'])}
                                                {assign var="zatca_response" value=$ds['zatca_last_response']|json_decode:true}
                                                {if is_array($zatca_response)}
                                                    {assign var="zatca_reporting_status" value=$zatca_response['reportingStatus']|default:''}
                                                    {if $zatca_reporting_status eq ''}
                                                        {assign var="zatca_reporting_status" value=$zatca_response['reporting_status']|default:''}
                                                    {/if}
                                                    {if $zatca_reporting_status eq ''}
                                                        {assign var="zatca_reporting_status" value=$zatca_response['clearanceStatus']|default:''}
                                                    {/if}

                                                    {assign var="validation_results" value=$zatca_response['validationResults']|default:[]}
                                                    {if empty($validation_results)}
                                                        {assign var="validation_results" value=$zatca_response['validation_results']|default:[]}
                                                    {/if}

                                                    {if is_array($validation_results)}
                                                        {assign var="zatca_validation_status" value=$validation_results['status']|default:$zatca_response['validation_status']|default:''}
                                                        {assign var="zatca_error_count" value=$validation_results['errorMessages']|count|default:0}
                                                    {/if}
                                                {/if}
                                            {/if}
                                            {if (($zatca_reporting_status eq 'REPORTED' || $zatca_reporting_status eq 'CLEARED') || $ds['zatca_status']|lower eq 'submitted') && $zatca_error_count eq 0}
                                                <span class="clx-status-badge clx-zatca-pass">PASS</span>
                                            {elseif $zatca_validation_status eq 'PASS'}
                                                <span class="clx-status-badge clx-zatca-pass">PASS</span>
                                            {elseif $zatca_validation_status eq 'WARNING'}
                                                <span class="clx-status-badge clx-zatca-warning">WARNING</span>
                                            {elseif $zatca_error_count gt 0}
                                                <span class="clx-status-badge clx-zatca-failed">FAILED</span>
                                            {else}
                                                <span class="text-muted">-</span>
                                            {/if}
                                        </td>

                                        <td class="text-end">

                                            {* Determine if invoice is locked (ZATCA submitted successfully with no errors) *}
                                            {assign var="invoice_locked" value=false}
                                            {if !empty($ds['zatca_last_response'])}
                                                {assign var="zatca_response" value=$ds['zatca_last_response']|json_decode:true}
                                                {if is_array($zatca_response)}
                                                    {assign var="reporting_status" value=$zatca_response['reportingStatus']|default:''}
                                                    {if $reporting_status eq ''}
                                                        {assign var="reporting_status" value=$zatca_response['reporting_status']|default:''}
                                                    {/if}
                                                    {if $reporting_status eq ''}
                                                        {assign var="reporting_status" value=$zatca_response['clearanceStatus']|default:''}
                                                    {/if}

                                                    {assign var="validation_results" value=$zatca_response['validationResults']|default:[]}
                                                    {if empty($validation_results)}
                                                        {assign var="validation_results" value=$zatca_response['validation_results']|default:[]}
                                                    {/if}

                                                    {if ($reporting_status eq 'REPORTED' || $reporting_status eq 'CLEARED' || $ds['zatca_status']|lower eq 'submitted')}
                                                        {assign var="zatca_error_count" value=0}
                                                        {if is_array($validation_results) && isset($validation_results['errorMessages'])}
                                                            {assign var="zatca_error_count" value=$validation_results['errorMessages']|count}
                                                        {/if}
                                                        {if $zatca_error_count eq 0}
                                                            {assign var="invoice_locked" value=true}
                                                        {/if}
                                                    {/if}
                                                {/if}
                                            {/if}

                                            <div class="btn-group">
                                                <a href="{$_url}invoices/view/{$ds['id']}/" class="btn btn-primary btn-icon" data-bs-toggle="tooltip" data-placement="top" title="{$_L['View']}"><i class="fal fa-file-alt"></i></a>

                                                <a href="{$_url}invoices/clone/{$ds['id']}/" class="btn btn-success btn-icon" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Clone']}"><i class="fal fa-copy"></i></a>

                                                {if $ds['type'] == 'Invoice' && $invoice_locked}
                                                    <a href="{$_url}invoices/nre-return/{$ds['id']}/" class="btn btn-secondary btn-icon" data-bs-toggle="tooltip" data-placement="top" title="NRE Return"><i class="fal fa-undo"></i></a>
                                                {/if}

                                                {if !$invoice_locked}
                                                    <a href="{$_url}invoices/edit/{$ds['id']}/" class="btn btn-info btn-icon" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Edit']}"><i class="fal fa-file-edit"></i></a>
                                                {/if}

                                                {if $ds['r'] neq '0'}

                                                    <a href="{$_url}invoices/stop_recurring/{$ds['id']}/" class="btn btn-info btn-icon" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Stop Recurring']}"><i class="fal fa-stop"></i></a>

                                                {/if}

                                                {if !$invoice_locked}
                                                    <a href="#" class="btn btn-danger btn-icon cdelete" id="iid{$ds['id']}" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Delete']}"><i class="fal fa-trash-alt"></i></a>
                                                {/if}
                                            </div>


                                        </td>
                                    </tr>
                                {/foreach}

                                </tbody>



                            </table>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
{/block}

{block name="script"}


    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>

    <script>
        $(function () {

            var $modal = $('#cloudonex_body');

            $('#clx_datatable').dataTable(
                {
                    responsive: true,
                    lengthChange: false,
                    dom:
                    /*	--- Layout Structure
                        --- Options
                        l	-	length changing input control
                        f	-	filtering input
                        t	-	The table!
                        i	-	Table information summary
                        p	-	pagination control
                        r	-	processing display element
                        B	-	buttons
                        R	-	ColReorder
                        S	-	Select

                        --- Markup
                        < and >				- div element
                        <"class" and >		- div with a class
                        <"#id" and >		- div with an ID
                        <"#id.class" and >	- div with an ID and a class

                        --- Further reading
                        https://datatables.net/reference/option/dom
                        --------------------------------------
                     */
                        "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'lB>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        /*{
                            extend:    'colvis',
                            text:      'Column Visibility',
                            titleAttr: 'Col visibility',
                            className: 'mr-sm-3'
                        },*/
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            titleAttr: 'Generate PDF',
                            className: 'btn-danger btn-sm mr-1'
                        },
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            titleAttr: 'Generate Excel',
                            className: 'btn-success btn-sm mr-1'
                        },
                        {
                            extend: 'csvHtml5',
                            text: 'CSV',
                            titleAttr: 'Generate CSV',
                            className: 'btn-primary btn-sm mr-1'
                        },
                        {
                            extend: 'copyHtml5',
                            text: 'Copy',
                            titleAttr: 'Copy to clipboard',
                            className: 'btn-warning btn-sm mr-1'
                        },
                        {
                            extend: 'print',
                            text: 'Print',
                            titleAttr: 'Print Table',
                            className: 'btn-secondary btn-sm'
                        }
                    ],
                    "language": {
                        "emptyTable": "{$_L['No items to display']}",
                        "info":      "{$_L['Showing _START_ to _END_ of _TOTAL_ entries']}",
                        "infoEmpty":      "{$_L['Showing 0 to 0 of 0 entries']}",
                        buttons: {
                            pageLength: '{$_L['Show all']}'
                        },
                        searchPlaceholder: "{__('Search')}"
                    },
                }
            );


            $modal.on('click', '.cdelete', function(e){

                e.preventDefault();
                var id = this.id;
                app.confirm("{__('are_you_sure')}", function(result) {
                    if(result){
                        window.location.href = base_url + "delete/invoice/" + id;
                    }
                });


            });





        });
    </script>
{/block}


