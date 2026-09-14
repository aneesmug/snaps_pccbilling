{extends file="$layouts_admin"}


{block name="content"}

    {assign var="zatca_warning_count" value=0}
    {assign var="zatca_reporting_success" value='no'}
    {assign var="zatca_validation_pass" value='no'}
    {assign var="zatca_submission_locked" value='no'}
    {assign var="zatca_error_count" value=0}
    {assign var="zatca_validation_display_status" value=''}
    {assign var="invoice_due_amount" value=($d['total']|default:0) - ($d['credit']|default:0)}
    {assign var="is_return_invoice" value=($d['type'] eq 'Credit Note' && $d['parent_id'] gt 0)}
    {assign var="is_refunded_credit_note" value=($d['type'] eq 'Credit Note' && ($invoice_due_amount le 0 || $d['status'] eq 'Paid' || (isset($trs_c) && $trs_c neq '')))}
    {assign var="is_cancelled_invoice" value=($d['status'] eq 'Cancelled')}
    
    {if !empty($zatca_status_card)}
        {* Count errors *}
        {if isset($zatca_status_card['counts']) && isset($zatca_status_card['counts']['errors'])}
            {assign var="zatca_error_count" value=$zatca_status_card['counts']['errors']}
        {/if}
        
        {* Count warnings *}
        {if isset($zatca_status_card['counts']) && isset($zatca_status_card['counts']['warnings'])}
            {assign var="zatca_warning_count" value=$zatca_status_card['counts']['warnings']}
        {/if}

        {* Check if successfully reported *}
        {if isset($zatca_status_card['reporting_status']) && ($zatca_status_card['reporting_status'] eq 'REPORTED' || $zatca_status_card['reporting_status'] eq 'CLEARED')}
            {assign var="zatca_reporting_success" value='yes'}
        {/if}

        {* Normalize validation display status *}
        {if isset($zatca_status_card['validation_status'])}
            {assign var="zatca_validation_display_status" value=$zatca_status_card['validation_status']|upper}
        {/if}

        {* Force PASS for successful ZATCA registration with zero errors *}
        {if $zatca_reporting_success eq 'yes' && $zatca_error_count eq 0}
            {assign var="zatca_validation_display_status" value='PASS'}
            {assign var="zatca_validation_pass" value='yes'}
        {elseif $zatca_validation_display_status eq 'PASS'}
            {assign var="zatca_validation_pass" value='yes'}
        {/if}

        {* Lock (hide) buttons if successfully submitted with no errors *}
        {if $zatca_reporting_success eq 'yes' && $zatca_error_count eq 0}
            {assign var="zatca_submission_locked" value='yes'}
        {/if}

        {* Return invoices should not be editable once they enter ZATCA submission flow *}
        {if $d['type'] eq 'Credit Note' && isset($zatca_status_card['has_submission']) && $zatca_status_card['has_submission']}
            {assign var="zatca_submission_locked" value='yes'}
        {/if}
    {/if}

    <div class="row">
        <div class="col-lg-12">
            <div class="mb-3">
                <label for="exampleInputEmail1">{$_L['Unique Invoice URL']}:</label>
                <input type="text" class="form-control mb-3" id="invoice_public_url" onClick="this.setSelectionRange(0, this.value.length)" value="{$_url}client/iview/{$d['id']}/token_{$d['vtoken']}">
            </div>
        </div>


        <div class="text-center mb-3">
            <div class="btn-group flex-wrap" role="group">

                <div class="btn-group " role="group">
                    <button type="button" class="btn  btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fal fa-envelope"></i> <span class="d-none d-md-inline">{$_L['Send Email']}</span>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-item"><a href="#" id="mail_invoice_created">{$_L['Invoice Created']}</a></li>
                        <li class="dropdown-item"><a href="#" id="mail_invoice_reminder">{$_L['Invoice Payment Reminder']}</a></li>
                        <li class="dropdown-item"><a href="#" id="mail_invoice_overdue">{$_L['Invoice Overdue Notice']}</a></li>
                        <li class="dropdown-item"><a href="#" id="mail_invoice_confirm">{$_L['Invoice Payment Confirmation']}</a></li>
                        <li class="dropdown-item"><a href="#" id="mail_invoice_refund">{$_L['Invoice Refund Confirmation']}</a></li>
                    </ul>
                </div>

                {*<div class="btn-group" role="group">
                    <button type="button" class="btn  btn-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fal fa-envelope-o"></i>  <span class="d-none d-md-inline">{$_L['SMS']}</span>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-item"><a href="#" id="sms_invoice_created">{$_L['Invoice Created']}</a></li>
                        <li class="dropdown-item"><a href="#" id="sms_invoice_reminder">{$_L['Invoice Payment Reminder']}</a></li>
                        <li class="dropdown-item"><a href="#" id="sms_invoice_overdue">{$_L['Invoice Overdue Notice']}</a></li>
                        <li class="dropdown-item"><a href="#" id="sms_invoice_confirm">{$_L['Invoice Payment Confirmation']}</a></li>
                        <li class="dropdown-item"><a href="#" id="sms_invoice_refund">{$_L['Invoice Refund Confirmation']}</a></li>

                        <li><hr class="dropdown-divider"></li>

                        {foreach $sms_templates_transformed as $key => $value}
                            {if !empty($value['tpl'])}
                                <li class="dropdown-item"><a target="_blank" href="sms:{$value['to']}?&body={urlencode($value['sms'])}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-phone" viewBox="0 0 16 16">
                                            <path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/>
                                            <path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                                        </svg> {__($value['tpl'])}</a></li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>*}
                {*<div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm dropdown-toggle text-white" style="background-color: #239970;" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                        </svg>  <span class="d-none d-md-inline">{__('Whatsapp')}</span>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        {foreach $sms_templates_transformed as $key => $value}
                            {if !empty($value['tpl'])}
                                <li class="dropdown-item"><a target="_blank" href="https://api.whatsapp.com/send?phone={$value['to']}&text={urlencode($value['sms'])}">{__($value['tpl'])}</a></li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>*}

                {if $invoice_due_amount gt 0 && !$is_return_invoice}
                    <div class="btn-group" role="group">
                        <button type="button" class="btn  btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fal fa-ellipsis-v"></i>  <span class="d-none d-md-inline">{$_L['Mark As']}</span>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            {if $d['status'] neq 'Paid'}
                                <li class="dropdown-item set_invoice_status" data-status="Paid"><a href="javascript:;">{$_L['Paid']}</a></li>
                            {/if}
                            {if $d['status'] neq 'Unpaid'}
                                <li class="dropdown-item set_invoice_status" data-status="Unpaid"><a href="javascript:;">{$_L['Unpaid']}</a></li>
                            {/if}
                            {if $d['status'] neq 'Partially Paid'}
                                <li class="dropdown-item set_invoice_status set_invoice_status" data-status="Partially Paid"><a href="javascript:;">{$_L['Partially Paid']}</a></li>
                            {/if}
                            {if $d['status'] neq 'Cancelled'}
                                <li class="dropdown-item set_invoice_status" data-status="Cancelled"><a href="javascript:;">{__('Cancelled')}</a></li>
                            {/if}
                        </ul>
                    </div>
                {/if}

                {if $d['type'] == 'Credit Note'}

                    {if $config['accounting'] eq '1' && !$is_refunded_credit_note && $invoice_due_amount gt 0}
                        {* do not change the ID although it's same as add payment *}
                        <button type="button" class="btn  btn-danger btn-sm" id="add_payment"> <span class="d-none d-md-inline">{{__('Refund')}}</span></button>
                    {/if}

                {else}

                    {if $config['accounting'] eq '1' && $invoice_due_amount > 0}
                        <button type="button" class="btn  btn-danger btn-sm" id="add_payment"><i class="fal fa-plus"></i> <span class="d-none d-md-inline">{$_L['Add Payment']}</span></button>
                    {/if}

                {/if}

                <a href="{$_url}client/iview/{$d['id']}/token_{$d['vtoken']}" target="_blank" class="btn btn-primary  btn-sm"><i class="fal fa-file-alt"></i> <span class="d-none d-md-inline">{$_L['Preview']}</span></a>

                {if $d['type'] == 'Invoice'}

                    <a href="{$_url}invoices/pos-receipt/{$d['id']}" target="_blank" class="btn btn-info  btn-sm"><i class="fal fa-file-alt"></i> <span class="d-none d-md-inline">{{__('Receipt')}}</span></a>

                    {if $zatca_submission_locked neq 'yes' && isset($zatca_connected) && $zatca_connected}
                        <a href="{$_url}invoices/submit-zatca/{$d['id']}" class="btn btn-primary btn-sm"><i class="fal fa-paper-plane"></i> <span class="d-none d-md-inline">{{__('Submit ZATCA')}}</span></a>
                    {/if}

                    {if (!isset($is_fully_returned_invoice) || !$is_fully_returned_invoice) && isset($zatca_status_card['is_registered']) && $zatca_status_card['is_registered']}
                        <a href="{$_url}invoices/nre-return/{$d['id']}" class="btn btn-secondary btn-sm"><i class="fal fa-undo"></i> <span class="d-none d-md-inline">{{__('NRE Return')}}</span></a>
                    {/if}

                {elseif $d['type'] == 'Credit Note' && $d['parent_id'] gt 0}

                    {if (isset($zatca_connected) && $zatca_connected) && (!isset($zatca_status_card['is_registered']) || !$zatca_status_card['is_registered'])}
                        <a href="{$_url}invoices/submit-zatca-return/{$d['id']}" class="btn btn-primary btn-sm"><i class="fal fa-paper-plane"></i> <span class="d-none d-md-inline">ZATCA Submit Return</span></a>
                    {/if}

                {/if}


                {if $zatca_submission_locked neq 'yes'}
                    <a href="{$_url}invoices/edit/{$d['id']}" class="btn btn-warning  btn-sm"><i class="fal fa-pencil"></i> <span class="d-none d-md-inline">{$_L['Edit']}</span></a>
                {/if}
                <div class="btn-group" role="group">
                    <button type="button" class="btn  btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fal fa-file-pdf-o"></i>
                        <span class="d-none d-md-inline">{$_L['PDF']}</span>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-item"><a href="{$_url}client/ipdf/{$d['id']}/token_{$d['vtoken']}/view/" target="_blank">{$_L['View PDF']}</a></li>
                        <li class="dropdown-item"><a href="{$_url}client/ipdf/{$d['id']}/token_{$d['vtoken']}/dl/" target="_blank">{$_L['Download PDF']}</a></li>
                    </ul>
                </div>

                <a data-bs-toggle="modal" href="#modal_add_item" class="btn btn-sm btn-dark"><i class="fal fa-paperclip"></i> </a>
                <a href="{$_url}invoices/clone/{$d['id']}/" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-placement="top" title="{$_L['Clone']}"><i class="fal fa-copy"></i> </a>

                <a href="{$_url}iview/print/{$d['id']}/token_{$d['vtoken']}" target="_blank" class="btn btn-dark  btn-sm"><i class="fal fa-print"></i>  <span class="d-none d-md-inline">{$_L['Print']}</span></a>


            </div>
        </div>


        <div class="col-lg-12"  id="application_ajaxrender">
            <div class="panel">
                <div class="panel-hdr">
                    <h2 class="d-none d-md-inline">


                        {if $d['type'] == 'Credit Note'}

                            {{__('Credit Note')}}

                        {else}

                            {$_L['Invoice']}

                        {/if}


                        - {$d['invoicenum']}{if $d['cn'] neq ''} {$d['cn']} {else} {$d['id']} {/if} </h2>
                    {if !empty($invoice_type_label)}
                        <span class="badge bg-info ms-2 text-white">{$invoice_type_label}</span>
                    {/if}
                    {if $is_return_invoice}
                        <span class="badge bg-danger ms-2">CREDIT NOTE / RETURN</span>
                    {/if}
                    {if $is_refunded_credit_note}
                        <span class="badge bg-success ms-2">REFUNDED</span>
                    {/if}
                    {if $is_cancelled_invoice}
                        <span class="badge bg-dark ms-2">CANCELED</span>
                    {/if}
                    <input type="hidden" name="iid" value="{$d['id']}" id="iid">

                    <div class="panel-toolbar">

                    </div>

                </div>

                <div class="panel-container">
                    <div class="panel-content" style="padding: 20px 24px 24px 24px;">

                        <style>
                            .invoice .invoice-items {
                                border-collapse: collapse;
                            }

                            .invoice .invoice-items th,
                            .invoice .invoice-items td {
                                border: 1px solid #d5dbe3 !important;
                            }

                            .return-negative {
                                color: #c0392b;
                                font-weight: 700;
                            }
                        </style>


                        {if $is_return_invoice}
                            <div class="alert alert-danger border-danger mb-4" role="alert">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <strong class="d-block">CREDIT NOTE / RETURN INVOICE</strong>
                                        <span>This is a return invoice. All amounts below represent returned values.</span>
                                    </div>
                                    <a href="{$_url}invoices/view/{$d['parent_id']}/" class="btn btn-outline-danger btn-sm">View Original Invoice #{$d['parent_id']}</a>
                                </div>
                            </div>
                        {/if}

                        {if $is_cancelled_invoice}
                            <div class="alert alert-dark border-dark mb-4" role="alert">
                                <div>
                                    <strong class="d-block">INVOICE CANCELED</strong>
                                    <span>This invoice has been canceled. All amounts below are shown as void.</span>
                                </div>
                            </div>
                        {/if}



                        <div class="invoice border rounded-3 bg-white" style="margin: 0 0 24px 0; padding: 16px 20px; box-sizing: border-box;">
                            {* Top Section: QR Code (Left), Invoice Reference (Center), Status & ZATCA (Right) *}
                            <div class="row mb-4 g-3 align-items-stretch mx-0">
                                {* QR Code Box (Left) *}
                                <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                                    <div class="border rounded-3 bg-white p-3 text-center" style="min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        {if !empty($qr_code)}
                                            <img src="{$qr_code}" style="width: 140px; height: 140px; max-width: 100%; margin-bottom: 8px;" alt="ZATCA QR Code">
                                            <small class="text-success fw-bold">ZATCA Phase 2 QR</small>
                                        {else}
                                            <div class="small text-muted text-center">
                                                <i class="fal fa-qrcode fa-3x mb-2" style="opacity: 0.5;"></i>
                                                <p>QR Code</p>
                                                <small>Generated after submission</small>
                                            </div>
                                        {/if}
                                    </div>
                                </div>

                                {* Invoice Reference & Date (Center) *}
                                <div class="col-lg-4 col-md-4 mb-3 mb-md-0">
                                    <div class="border rounded-3 bg-white p-3 h-100">
                                        <div class="mb-3">
                                            <label class="text-muted small">Invoice Reference Number</label>
                                            <p class="mb-0 fw-bold text-primary">{$d['invoicenum']}{if $d['cn'] neq ''} {$d['cn']} {else} {$d['id']} {/if}</p>
                                        </div>
                                        <hr class="my-2">
                                        <div class="mb-0">
                                            <label class="text-muted small">Date and Time of Invoice Issuance</label>
                                            <p class="mb-0 fw-bold">{if !empty($zatca_data['timestamp'])}{date('Y-m-d H:i:s', strtotime($zatca_data['timestamp']))}{else}{date($config['df'], strtotime($d['date']))}{/if}</p>
                                        </div>
                                    </div>
                                </div>

                                {* ZATCA Status (Right) *}
                                <div class="col-lg-5 col-md-4">
                                    {if !empty($zatca_status_card)}
                                        <div id="zatca-response-live" class="border border-info rounded-3 bg-white p-3 h-100">
                                            {assign var="zatca_reporting_upper" value=$zatca_status_card['reporting_status']|default:''|upper}
                                            {assign var="zatca_validation_upper" value=$zatca_validation_display_status|default:''|upper}
                                            {assign var="zatca_overall_text" value=$zatca_status_card['status_label']|default:'ZATCA Submitted'}
                                            {assign var="zatca_overall_badge" value='bg-secondary'}
                                            {if $zatca_reporting_upper eq 'CLEARED'}
                                                {assign var="zatca_overall_text" value='Cleared by ZATCA'}
                                                {assign var="zatca_overall_badge" value='bg-info text-white'}
                                            {elseif $zatca_reporting_upper eq 'REPORTED'}
                                                {assign var="zatca_overall_text" value='Reported to ZATCA'}
                                                {assign var="zatca_overall_badge" value='bg-success'}
                                            {elseif $zatca_validation_upper eq 'PASS'}
                                                {assign var="zatca_overall_text" value='Validation Passed'}
                                                {assign var="zatca_overall_badge" value='bg-success'}
                                            {elseif $zatca_validation_upper eq 'FAIL'}
                                                {assign var="zatca_overall_text" value='Validation Failed'}
                                                {assign var="zatca_overall_badge" value='bg-danger'}
                                            {/if}

                                            {assign var="zatca_validation_badge" value='bg-secondary'}
                                            {if $zatca_validation_upper eq 'PASS'}
                                                {assign var="zatca_validation_badge" value='bg-success'}
                                            {elseif $zatca_validation_upper eq 'WARNING'}
                                                {assign var="zatca_validation_badge" value='bg-warning text-dark'}
                                            {elseif $zatca_validation_upper eq 'FAIL'}
                                                {assign var="zatca_validation_badge" value='bg-danger'}
                                            {/if}

                                            {assign var="zatca_reporting_badge" value='bg-secondary'}
                                            {if $zatca_reporting_upper eq 'CLEARED'}
                                                {assign var="zatca_reporting_badge" value='bg-info text-white'}
                                            {elseif $zatca_reporting_upper eq 'REPORTED'}
                                                {assign var="zatca_reporting_badge" value='bg-success'}
                                            {elseif $zatca_reporting_upper eq 'NOT SUBMITTED'}
                                                {assign var="zatca_reporting_badge" value='bg-secondary'}
                                            {elseif $zatca_reporting_upper neq ''}
                                                {assign var="zatca_reporting_badge" value='bg-warning text-dark'}
                                            {/if}

                                            <h6 class="mb-2 text-dark fw-bold">ZATCA Status</h6>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge {$zatca_overall_badge} p-2">{$zatca_overall_text}</span>
                                                {if !empty($zatca_validation_upper)}
                                                    <span class="badge {$zatca_validation_badge} p-2">Validation: {$zatca_validation_upper}</span>
                                                {/if}
                                                {if !empty($zatca_reporting_upper)}
                                                    <span class="badge {$zatca_reporting_badge} p-2">Reporting: {$zatca_reporting_upper}</span>
                                                {/if}
                                            </div>
                                            {if !empty($zatca_status_card['submitted_at'])}
                                                <small class="d-block mb-0 text-muted"><strong>Submitted at:</strong> {$zatca_status_card['submitted_at']}</small>
                                            {/if}

                                            {if !empty($zatca_status_card['zatca_request_id']) || !empty($zatca_status_card['zatca_uuid']) || !empty($zatca_status_card['zatca_invoice_hash'])}
                                                <div class="mt-3 p-2 border rounded-2 bg-light">
                                                    <small class="d-block fw-bold mb-2">ZATCA Live Response IDs</small>
                                                    {if !empty($zatca_status_card['zatca_request_id'])}
                                                        <div class="mb-1"><span class="badge bg-primary">Request ID</span> <span class="small text-dark">{$zatca_status_card['zatca_request_id']}</span></div>
                                                    {/if}
                                                    {if !empty($zatca_status_card['zatca_uuid'])}
                                                        <div class="mb-1"><span class="badge bg-secondary">UUID</span> <span class="small text-dark">{$zatca_status_card['zatca_uuid']}</span></div>
                                                    {/if}
                                                    {if !empty($zatca_status_card['zatca_invoice_hash'])}
                                                        <div><span class="badge bg-dark">Invoice Hash</span> <span class="small text-dark">{$zatca_status_card['zatca_invoice_hash']}</span></div>
                                                    {/if}
                                                </div>
                                            {/if}
                                        </div>
                                    {else}
                                        <div class="border rounded-3 bg-light p-3 h-100">
                                            <h6 class="mb-2 text-dark fw-bold">Invoice Status</h6>
                                            {if $d['status'] eq 'Cancelled'}
                                                <span class="badge bg-dark p-2">{__('Cancelled')}</span>
                                            {elseif $d['status'] eq 'Unpaid'}
                                                <span class="badge bg-danger p-2">{$_L['Unpaid']}</span>
                                            {elseif $d['status'] eq 'Paid'}
                                                <span class="badge bg-success p-2">{$_L['Paid']}</span>
                                            {elseif $d['status'] eq 'Partially Paid'}
                                                <span class="badge bg-info p-2">{$_L['Partially Paid']}</span>
                                            {else}
                                                <span class="badge bg-secondary p-2">{$d['status']}</span>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                            </div>

                            {* Seller & Buyer Information Section *}
                            <div class="row mb-4">
                                {* Seller Information (Left) *}
                                <div class="col-lg-6 col-md-6 d-flex">
                                    <div class="border rounded-3 bg-white p-3 h-100 w-100">
                                        <h6 class="mb-3 text-dark fw-bold">Seller Information</h6>
                                        <div class="row">
                                            <div class="col-sm-6 mb-2">
                                                <strong>Seller Name:</strong><br>{$config['CompanyName']}
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <strong>Seller VAT Number:</strong><br>{if !empty($zatca_data['seller_vat'])}{$zatca_data['seller_vat']}{elseif isset($config['vat_number']) && $config['vat_number'] != ''}{$config['vat_number']}{else}N/A{/if}
                                            </div>
                                            <div class="col-sm-6 mb-0">
                                                <strong>Seller Address:</strong><br>{$config['caddress']}
                                            </div>
                                            <div class="col-sm-6 mb-0">
                                                <strong>Additional Seller ID:</strong><br>{if !empty($zatca_data['seller_crn'])}{$zatca_data['seller_crn']}{else}N/A{/if}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {* Buyer Information (Right) *}
                                <div class="col-lg-6 col-md-6 d-flex">
                                    <div class="border rounded-3 bg-white p-3 h-100 w-100">
                                        <h6 class="mb-3 text-dark fw-bold">Buyer Information</h6>
                                        <div class="row">
                                            <div class="col-sm-6 mb-2">
                                                <strong>Buyer Name:</strong><br>{if $a['company'] neq ''}{$a['company']}{else}{$d['account']}{/if}
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <strong>Buyer VAT Number:</strong><br>{if !empty($a['tax_number'])}{$a['tax_number']}{else}N/A{/if}
                                            </div>
                                            <div class="col-sm-6 mb-0">
                                                <strong>Buyer Address:</strong><br>{getContactFormattedAddress($config,$a)}
                                            </div>
                                            <div class="col-sm-6 mb-0">
                                                <strong>Additional Buyer ID:</strong><br>{if !empty($a['entity_number'])}{$a['entity_number']}{else}N/A{/if}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                        {if !empty($config['invoice_items_purchasing'])}

                                            <div class="p-3 rounded-3 mb-4" style="background: #C6DAFF;">



                                                <div class="row">
                                                    <div class="col-4">
                                                        <h5>Purchase Info</h5>
                                                        {if $d['purchase_status']}
                                                            <span class="badge bg-info text-white">{$d['purchase_status']}</span>
                                                        {/if}
                                                    </div>
                                                    <div class="col-8 text-end">

                                                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Purchase Status
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li class="dropdown-item set_invoice_purchase_status" data-status="Purchased"><a href="javascript:;">{__('Purchased')}</a></li>
                                                                    <li class="dropdown-item set_invoice_purchase_status" data-status="On Hold"><a href="javascript:;">{__('On Hold')}</a></li>
                                                                    <li class="dropdown-item set_invoice_purchase_status" data-status="Arrived"><a href="javascript:;">{__('Arrived')}</a></li>
                                                                    <li class="dropdown-item set_invoice_purchase_status" data-status="Collected"><a href="javascript:;">{__('Collected')}</a></li>
                                                                </ul>
                                                            </div>
                                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchaseInfo"><i class="fal fa-plus"></i> {__('Purchase Info')}</button>
                                                        </div>


                                                    </div>
                                                </div>

                                                {if !empty($d['purchase_id'])}
                                                    <p class="mb-1"><strong>Purchase ID:</strong> {$d['purchase_id']}</p>
                                                {/if}

                                                {if !empty($d['purchase_date'])}
                                                    <p class="mb-1"><strong>Purchase Date:</strong> {$d['purchase_date']}</p>
                                                {/if}

                                                {if !empty($d['purchase_cost'])}
                                                    <p class="mb-1"><strong>Purchase Cost:</strong> {formatCurrency($d['purchase_cost'],$d['currency'])}</p>
                                                {/if}

                                                {if !empty($d['purchase_staff_id']) && !empty($staffs[$d['purchase_staff_id']])}
                                                    <p class="mb-1"><strong>Staff:</strong> {$staffs[$d['purchase_staff_id']]->fullname}</p>
                                                {/if}

                                                {if !empty($d['purchase_notes'])}
                                                    <p class="mb-1"><strong>Notes:</strong> {$d['purchase_notes']}</p>
                                                {/if}

                                            </div>

                                        {/if}

                                        {if !empty($config['invoice_items_shipping'])}

                                            <div class="p-3 rounded-3 mb-4" style="background: #C6DAFF;">

                                                <div class="row">
                                                    <div class="col-3">
                                                        <h5>Shipping Info</h5>
                                                        {if $d['shipping_status']}
                                                            <span class="badge bg-info text-white">{$d['shipping_status']}</span>
                                                        {/if}
                                                    </div>
                                                    <div class="col-9 text-end">

                                                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Shipping Status
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    {if $d['shipping_status'] !== 'In Progress'}
                                                                        <li class="dropdown-item set_invoice_shipping_status" data-status="In Progress"><a href="javascript:;">{__('In Progress')}</a></li>
                                                                    {/if}

                                                                    {if $d['shipping_status'] !== 'Shipped'}
                                                                        <li class="dropdown-item set_invoice_shipping_status" data-status="Shipped"><a href="javascript:;">{__('Shipped')}</a></li>
                                                                    {/if}

                                                                </ul>
                                                            </div>
                                                            <a href="{$_url}invoices/delivery-label/{$d['id']}" target="_blank" class="btn btn-primary">{{__('Delivery Label')}}</a>
                                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shippingInfo"><i class="fal fa-plus"></i> {__('Shipping Info')}</button>
                                                        </div>

                                                    </div>
                                                </div>

                                                {if !empty($d['shipping_date'])}
                                                    <p class="mb-1"><strong>Shipping Date:</strong> {$d['shipping_date']}</p>
                                                {/if}

                                                {if !empty($d['shipping_tracking_number'])}
                                                    <p class="mb-1"><strong>Tracking Number:</strong> {$d['shipping_tracking_number']}</p>
                                                {/if}

                                                {if !empty($d['shipping_weight'])}
                                                    <p class="mb-1"><strong>Weight:</strong> {$d['shipping_weight']}</p>
                                                {/if}

                                                {if !empty($d['shipping_cost'])}
                                                    <p class="mb-1"><strong>Shipping Cost:</strong> {$d['shipping_cost']}</p>
                                                {/if}

                                                {if !empty($d['shipping_notes'])}
                                                    <p class="mb-1"><strong>Notes:</strong> {$d['shipping_notes']}</p>
                                                {/if}

                                            </div>

                                        {/if}
                            {* Line Items Section *}

                            {if $quote}

                                <h4>{$_L['Quote']}: {$quote->id}</h4>

                                <div class="row">
                                    <div class="col-md-12">
                                        <hr>
                                        {$quote->proposal}
                                        <hr>
                                    </div>
                                </div>
                            {/if}

                            <div class="table-responsive">


                                {if $config['tax_system'] == 'India'}

                                    <table class="table table-bordered invoice-items">
                                        <thead>
                                        <tr class="text-dark">
                                            <th id="cell-id" class="text-semibold">S/L</th>
                                            <th id="cell-item" class="text-semibold">{$_L['Item']}</th>
                                            <th class="text-semibold">HSN / SAC</th>
                                            <th id="cell-price" class="text-center text-semibold">{$_L['Price']}</th>
                                            <th id="cell-qty" class="text-center text-semibold">{if $d['show_quantity_as'] eq '' || $d['show_quantity_as'] eq '1'}{$_L['Qty']}{else}{$d['show_quantity_as']}{/if}</th>
                                            <th class="text-end text-semibold">Calculated VAT Value</th>
                                            <th class="text-end">Taxable Value</th>


                                            {if $d['is_same_state']}

                                                <th class="text-end">CGST</th>
                                                <th class="text-end">SGST/UTGST</th>
                                                <th class="text-end">GST</th>

                                            {else}

                                                <th class="text-end">IGST</th>

                                            {/if}

                                            <th id="cell-total" class="text-end text-semibold">{$_L['Total']}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        {foreach $items as $item}
                                            <tr>
                                                <td>
                                                    {if $item['itemcode'] != ''}
                                                        {$item['itemcode']}
                                                    {else}
                                                        {counter}
                                                    {/if}
                                                </td>
                                                <td class="text-semibold text-dark">{$item['description']}</td>
                                                <td class="text-semibold text-dark">{$item['tax_code']}</td>
                                                <td class="text-center">{formatCurrency($item['amount'],$d['currency_iso_code'],$format_currency_override)}</td>
                                                <td class="text-center">{$item['qty']}</td>
                                                <td class="text-end">{formatCurrency(round($item['taxamount'],2),$d['currency_iso_code'])}</td>
                                                <td class="text-end">
                                                    {if $item['discount_amount'] != '0.00'}

                                                        Total: {formatCurrency(($item['amount']*$item['qty']),$d['currency_iso_code'])}


                                                        <br>
                                                        Discount: {$item['discount_amount']}
                                                        <br>
                                                        Taxable amount: {formatCurrency((($item['amount']*$item['qty'])-$item['discount_amount']),$d['currency_iso_code'])}

                                                    {else}

                                                        {formatCurrency(($item['amount']*$item['qty']),$d['currency_iso_code'])}

                                                    {/if}


                                                </td>


                                                {if $d['is_same_state']}

                                                    <td class="text-end">
                                                        {formatCurrency((gstIndiaSplitTaxValue($item['total'],$item['tax_rate'])),$d['currency_iso_code'])}

                                                        <br>
                                                        @{round($item['tax_rate']/2,2)}%
                                                    </td>
                                                    <td class="text-end">

                                                            {formatCurrency((gstIndiaSplitTaxValue($item['total'],$item['tax_rate'])),$d['currency_iso_code'])}

                                                        <br>
                                                        @{round($item['tax_rate']/2,2)}%
                                                    </td>
                                                    <td class="text-end">
                                                        {formatCurrency(round($item['taxamount'],2),$d['currency_iso_code'])} <br>
                                                        @{round($item['tax_rate'],2)}%

                                                    </td>

                                                {else}



                                                    <td class="text-end">
                                                        {formatCurrency(round(( ($item['tax_rate']*($item['qty'] * $item['amount'])) / 100),2),$d['currency_iso_code'])} <br>
                                                        @{round($item['tax_rate'],2)}%

                                                    </td>

                                                {/if}




                                                <td class="text-end">{formatCurrency(($item['total'] + $item['taxamount']),$d['currency_iso_code'])}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>

                                {else}

                                    <table class="table table-bordered invoice-items">
                                        <thead>
                                        <tr class="text-dark">
                                            <th id="cell-id" class="fw-bold">#</th>
                                            <th id="cell-item" class="fw-bold">{$_L['Item']}</th>
                                            <th id="cell-price" class="text-end fw-bold">{$_L['Price']}</th>
                                            <th id="cell-qty" class="text-end fw-bold">{if $d['show_quantity_as'] eq '' || $d['show_quantity_as'] eq '1'}{$_L['Qty']}{else}{$d['show_quantity_as']}{/if}</th>
                                            <th class="text-end fw-bold">Calculated VAT Value</th>
                                            <th id="cell-total" class="text-end fw-bold">{$_L['Total']}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        {foreach $items as $item}
                                            <tr>
                                                <td>
                                                    {if $item['itemcode'] != ''}
                                                        {$item['itemcode']}
                                                    {else}
                                                        {counter}
                                                    {/if}
                                                </td>
                                                <td class="text-dark">

                                                    {$item['description']}

                                                    {if !empty($config['invoicing_allow_staff_selection_for_each_item'])}
                                                        {if $item['staff_id'] != ''}
                                                            <br>
                                                            {if !empty($staffs[$item['staff_id']])}
                                                            <p class="badge bg-primary">{$_L['Staff']}:
                                                                {$staffs[$item['staff_id']]->fullname}
                                                            </p>
                                                            {/if}
                                                        {/if}
                                                    {/if}

                                                </td>
                                                <td class="text-end">{formatCurrency($item['amount'],$d['currency_iso_code'],$format_currency_override)}</td>
                                                <td class="text-end">{$item['qty']}</td>
                                                <td class="text-end">{formatCurrency(round($item['taxamount'],2),$d['currency_iso_code'])}</td>
                                                <td class="text-end">
                                                    {if ($item['discount_amount'] > 0)}
                                                        {formatCurrency($item['total'],$d['currency_iso_code'])} <br>
                                                        <span class="text-danger">- {formatCurrency((get_discount_amount($item['discount_amount'],$item['discount_type'],($item['qty'] * $item['amount']))),$d['currency_iso_code'])}</span>
                                                        {else}
                                                        {formatCurrency(($item['total'] + $item['discount_amount']),$d['currency_iso_code'])}
                                                    {/if}

                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>

                                {/if}

                            </div>

                            <div class="invoice-summary mt-4">
                                <div class="row">
                                    <div class="col-md-6 offset-md-6">
                                        <div class="border rounded-3 bg-light p-3">
                                            <table class="table h6 text-dark mb-0">
                                                <tbody>
                                                <tr class="b-top-none">
                                                    <td><strong>{$_L['Total Excluding VAT']}</strong></td>
                                                    <td class="text-end"><strong>{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $d['subtotal'] lt 0}{formatCurrency((0 - $d['subtotal']),$d['currency_iso_code'])}{else}{formatCurrency($d['subtotal'],$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($d['subtotal'],$d['currency_iso_code'])}{/if}</strong></td>
                                                </tr>
                                                {if ($d['discount']) neq '0.00'}
                                                    <tr>
                                                        <td>{$_L['Discount']}</td>
                                                        <td class="text-end">{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $d['discount'] lt 0}{formatCurrency((0 - $d['discount']),$d['currency_iso_code'])}{else}{formatCurrency($d['discount'],$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($d['discount'],$d['currency_iso_code'])}{/if}</td>
                                                    </tr>
                                                {/if}

                                                {if $config['tax_system'] == 'India'}
                                                    <tr>
                                                        <td>{$_L['TAX']}</td>
                                                        <td class="text-end">{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $d['tax'] lt 0}{formatCurrency((0 - $d['tax']),$d['currency_iso_code'])}{else}{formatCurrency($d['tax'],$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($d['tax'],$d['currency_iso_code'])}{/if}</td>
                                                    </tr>
                                                {else}
                                                    <tr>
                                                        <td><strong>{$_L['VAT Total']}</strong></td>
                                                        <td class="text-end"><strong>{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $d['tax'] lt 0}{formatCurrency((0 - $d['tax']),$d['currency_iso_code'])}{else}{formatCurrency($d['tax'],$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($d['tax'],$d['currency_iso_code'])}{/if}</strong></td>
                                                    </tr>
                                                {/if}

                                                {if ($d['credit']) neq '0.00'}
                                                    <tr>
                                                        <td>{$_L['Total']}</td>
                                                        <td class="text-end">{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $d['total'] lt 0}{formatCurrency((0 - $d['total']),$d['currency_iso_code'])}{else}{formatCurrency($d['total'],$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($d['total'],$d['currency_iso_code'])}{/if}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>{$_L['Total Paid']}</td>
                                                        <td class="text-end">{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $d['credit'] lt 0}{formatCurrency((0 - $d['credit']),$d['currency_iso_code'])}{else}{formatCurrency($d['credit'],$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($d['credit'],$d['currency_iso_code'])}{/if}</td>
                                                    </tr>
                                                    <tr class="border-top">
                                                        <td class="h5"><strong>{$_L['Amount Due']}</strong></td>
                                                        <td class="text-end h5"><strong>{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $i_due lt 0}{formatCurrency((0 - $i_due),$d['currency_iso_code'])}{else}{formatCurrency($i_due,$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($i_due,$d['currency_iso_code'])}{/if}</strong></td>
                                                    </tr>
                                                {else}
                                                    <tr class="border-top">
                                                        <td class="h5"><strong>{$_L['Total Including VAT']}</strong></td>
                                                        <td class="text-end h5"><strong>{if $is_return_invoice || $is_cancelled_invoice}<span class="return-negative">- {if $d['total'] lt 0}{formatCurrency((0 - $d['total']),$d['currency_iso_code'])}{else}{formatCurrency($d['total'],$d['currency_iso_code'])}{/if}</span>{else}{formatCurrency($d['total'],$d['currency_iso_code'])}{/if}</strong></td>
                                                    </tr>
                                                {/if}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {if ($trs_c neq '')}
                            <h3>{$_L['Related Transactions']}</h3>
                            <table class="table table-bordered sys_table">
                                <th>{$_L['Date']}</th>
                                <th>{$_L['Account']}</th>
                                <th>{$_L['Staff']}</th>
                                <th class="text-end">{$_L['Amount']}</th>
                                <th>{$_L['Description']}</th>
                                <th>{$_L['Ref']}</th>
                                <th>{$_L['Method']}</th>

                                {foreach $trs as $tr}
                                    <tr class="{if $tr['cr'] eq '0.00'}warning {else}info{/if}">
                                        <td>{date( $config['df'], strtotime($tr['date']))}</td>
                                        <td>{$tr['account']}</td>
                                        <td>
                                            {if $tr['aid'] neq '0'}
                                                {if !empty($staffs[$tr['aid']])}
                                                    {$staffs[$tr['aid']]->fullname}
                                                {/if}
                                            {/if}
                                        </td>
                                        <td class="text-end">{formatCurrency($tr['amount'],$d['currency_iso_code'])}</td>
                                        <td>{$tr['description']}</td>
                                        <td>{$tr['ref']}</td>
                                        <td>{$tr['method']}</td>


                                    </tr>
                                {/foreach}


                            </table>
                        {/if}

                        {if $inv_files_c neq ''}

                            <table class="table table-bordered table-hover sys_table">
                                <thead>
                                <tr>
                                    <th class="text-end" data-sort-ignore="true" width="20px;">{$_L['Type']}</th>

                                    <th>{$_L['File']}</th>

                                    <th class="text-end" data-sort-ignore="true" width="100px;">{$_L['Manage']}</th>
                                </tr>
                                </thead>
                                <tbody>

                                {foreach $inv_files as $ds}

                                    <tr>

                                        <td>
                                            {if $ds['file_mime_type'] eq 'jpg' || $ds['file_mime_type'] eq 'png' || $ds['file_mime_type'] eq 'gif'}
                                                <i class="fal fa-file-image-o"></i>
                                            {elseif $ds['file_mime_type'] eq 'pdf'}
                                                <i class="fal fa-file-pdf-o"></i>
                                            {elseif $ds['file_mime_type'] eq 'zip'}
                                                <i class="fal fa-file-archive-o"></i>
                                            {else}
                                                <i class="fal fa-file"></i>
                                            {/if}
                                        </td>


                                        <td>

                                            {$ds['title']}

                                            {if $ds['file_mime_type'] eq 'jpg' || $ds['file_mime_type'] eq 'png' || $ds['file_mime_type'] eq 'gif'}

                                                <div class="hr-line-dashed"></div>

                                                <img src="{$app_url}storage/docs/{$ds['file_path']}" class="img-responsive" alt="{$ds['title']}">

                                            {/if}

                                        </td>

                                        <td class="text-end">

                                            <a data-bs-toggle="tooltip" data-placement="top" title="{$_L['Download']}" href="{$_url}client/dl/{$ds['id']}_{$ds['file_dl_token']}/" class="btn btn-primary"><i class="fal fa-download"></i> </a>

                                            <a data-bs-toggle="tooltip" data-placement="top" title="{$_L['Delete']}" onclick="confirmThenGoToUrl(event,'delete/document/{$ds['id']}');" href="javascript:;" class="btn btn-danger"><i class="fal fa-trash"></i> </a>

                                        </td>


                                    </tr>

                                {/foreach}

                                </tbody>



                            </table>

                        {/if}

                        {if ($d['notes']) neq ''}
                            <div class="well m-t">
                                {$d['notes']}
                            </div>
                        {/if}

                        {if ($emls_c neq '')}
                            <hr>
                            <h3>{$_L['Related Emails']}</h3>
                            <table class="table table-bordered sys_table">
                                <th width="20%">{$_L['Date']}</th>
                                <th>{$_L['Subject']}</th>







                                {foreach $emls as $eml}
                                    <tr>
                                        <td>{date( $config['df'], strtotime($eml['date']))}</td>
                                        <td>{$eml['subject']}</td>
                                    </tr>
                                {/foreach}



                            </table>
                        {/if}


                        {if count($access_logs) neq 0}
                            <hr>
                            <h3>{$_L['Customer']} : {$_L['Access Log']}</h3>
                            <table class="table table-bordered sys_table">
                                <th>{$_L['Time']}</th>
                                <th>{$_L['IP']}</th>
                                <th>{$_L['Country']}</th>
                                <th>{$_L['City']}</th>
                                <th>{$_L['Browser']}</th>

                                {foreach $access_logs as $log}
                                    <tr>
                                        <td>{date( $config['df']|cat:' H:i:s', strtotime($log['viewed_at']))}</td>
                                        <td>{$log['ip']}</td>
                                        <td>{$log['country']}</td>
                                        <td>{$log['city']}</td>
                                        <td>{$log['browser']}</td>
                                    </tr>
                                {/foreach}



                            </table>
                        {/if}


                        </div>



                    </div>
                </div>


            </div>
        </div>
    </div>



    <div id="modal_add_item" class="modal fade" tabindex="-1"  role="dialog" aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        {$_L['New Document']}
                    </h4>
                    <button type="button" class="close btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="doc_title">{$_L['Title']}</label>
                            <input type="text" class="form-control" id="doc_title" name="doc_title">

                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="is_global" name="is_global"> {$_L['Available for all Customers']}
                            </label>
                        </div>





                    </form>

                    <hr>

                    <form action="" class="dropzone" id="upload_container">

                        <div class="dz-message">
                            <h3> <i class="fal fa-cloud-upload"></i>  {$_L['Drop File Here']}</h3>
                            <br />
                            <span class="note">{$_L['Click to Upload']}</span>
                        </div>

                    </form>
                    <hr>
                    <h4>{$_L['Server Config']}:</h4>
                    <p>{$_L['Upload Maximum Size']}: {$upload_max_size}</p>
                    <p>{$_L['POST Maximum Size']}: {$post_max_size}</p>

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="file_link" id="file_link" value="">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-danger">{$_L['Close']}</button>
                    <button type="button" id="btn_add_file" class="btn btn-primary">{$_L['Submit']}</button>
                </div>
            </div>
        </div>


    </div>

    {if !empty($config['invoice_items_purchasing'])}

        <div class="modal fade" id="purchaseInfo" tabindex="-1" aria-labelledby="purchaseInfoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                   <form method="post" action="{$base_url}invoices/save-purchase-info">
                       <div class="modal-header">
                           <h1 class="modal-title fs-5" id="purchaseInfoLabel">Purchase Info</h1>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body">

                           <div class="mb-3">
                               <label for="purchase_id">Purchase ID</label>
                               <input type="text" class="form-control" id="purchase_id" name="purchase_id" value="{$d['purchase_id']}">
                           </div>

                           <div class="row">
                               <div class="col-md-6">
                                   <div class="mb-3">
                                       <label for="purchase_date">Purchase Date</label>
                                       <input type="text" class="form-control" id="purchase_date" datepicker
                                              data-date-format="yyyy-mm-dd" data-auto-close="true" name="purchase_date"

                                              {if !empty($d['purchase_date'])}
                                                  value="{$d['purchase_date']}"
                                                  {else}
                                                    value="{date('Y-m-d')}"
                                              {/if}


                                       >
                                   </div>
                               </div>
                               <div class="col-md-6">
                                   <div class="mb-3">
                                       <label for="purchase_cost">Purchase Cost</label>
                                       <input type="text" class="form-control" id="purchase_cost" name="purchase_cost" value="{$d['purchase_cost']}">
                                   </div>
                               </div>
                           </div>

                           <div class="mb-3">
                               <label for="staff_id">{__('Staff')}</label>
                               <select name="staff_id" id="staff_id" class="form-select">
                                   <option value="0">{__('Select Staff')}</option>
                                   {foreach $staffs as $staff}
                                       <option value="{$staff['id']}"
                                               {if $staff['id'] == $d['purchase_staff_id']}
                                                   selected="selected"
                                               {else}
                                                   {if $staff['id'] == $user['id']}
                                                       selected="selected"
                                                   {/if}
                                               {/if}
                                       >{$staff['fullname']}</option>
                                   {/foreach}
                               </select>
                           </div>

                           <div class="mb-3">
                               <label for="purchase_notes">Purchase Notes</label>
                               <textarea class="form-control" id="purchase_notes" rows="5" name="purchase_notes">{$d['purchase_notes']}</textarea>
                           </div>


                       </div>
                       <div class="modal-footer">
                           <input type="hidden" name="invoice_id" value="{$d['id']}">
                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                           <button type="submit" class="btn btn-primary">Save</button>
                       </div>
                   </form>
                </div>
            </div>
        </div>

    {/if}

    {if !empty($config['invoice_items_shipping'])}

        <div class="modal fade" id="shippingInfo" tabindex="-1" aria-labelledby="shippingInfoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="{$base_url}invoices/save-shipping-info">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="shippingInfoLabel">Shipping Info</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="shipping_weight">Weight</label>
                                <input type="text" class="form-control" id="shipping_weight" name="shipping_weight" value="{$d['shipping_weight']}">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="shipping_date">Shipping Date</label>
                                        <input type="text" class="form-control" id="shipping_date" datepicker
                                               data-date-format="yyyy-mm-dd" data-auto-close="true" name="shipping_date"

                                                {if !empty($d['shipping_date'])}
                                                    value="{$d['shipping_date']}"
                                                {else}
                                                    value="{date('Y-m-d')}"
                                                {/if}


                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="shipping_cost">Shipping Cost</label>
                                        <input type="text" class="form-control" id="shipping_cost" name="shipping_cost" value="{$d['shipping_cost']}">
                                    </div>
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="shipping_notes">Shipping Notes</label>
                                <textarea class="form-control" id="shipping_notes" rows="5" name="shipping_notes">{$d['shipping_notes']}</textarea>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="invoice_id" value="{$d['id']}">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    {/if}


    <input type="hidden" id="_lan_msg_confirm" value="{$_L['are_you_sure']}">
    <input type="hidden" id="admin_email" value="{$user->username}">




{/block}

{block name="script"}


    <script>


        Dropzone.autoDiscover = false;
        $(document).ready(function () {



            var _url = $("#_url").val();

            var $modal = $('#cloudonex_body');


            var sysrender = $modal;

            $('.amount').autoNumeric('init', {

                aSign: '{$config['currency_code']}',
                dGroup: '{$config['thousand_separator_placement']}',
                aPad: '{$config['currency_decimal_digits']}',
                pSign: '{$config['currency_symbol_position']}',
                aDec: '{$config['dec_point']}',
                aSep: '{$config['thousands_sep']}',
                vMax: '9999999999999999.00',
                vMin: '-9999999999999999.00',


            });

            var iid = $("#iid").val();
            sysrender.on('click', '#add_payment', function(e){
                e.preventDefault();

                {if $d['type'] === 'Credit Note'}
                let url = base_url + 'transactions/modal-expense/' + iid;
                {else}
                let url = base_url + 'invoices/add-payment/' + iid;
                {/if}

                $.fancybox.open({
                    src  :  url,
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $(".datepicker").datepicker();
                            $("#account").select2({

                            });
                            $("#cats").select2({

                            });
                            $("#pmethod").select2({

                            });
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });


            });


            sysrender.on('click', '#mail_invoice_created', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/mail_invoice_/' + iid + '/created',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').redactor();
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });



            });

            sysrender.on('click', '#mail_invoice_reminder', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/mail_invoice_/' + iid + '/reminder',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').redactor();
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });


            });

            sysrender.on('click', '#mail_invoice_overdue', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/mail_invoice_/' + iid + '/overdue',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').redactor();
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });







            });

            sysrender.on('click', '#mail_invoice_confirm', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/mail_invoice_/' + iid + '/confirm',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').redactor();
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });





            });

            sysrender.on('click', '#mail_invoice_refund', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/mail_invoice_/' + iid + '/refund',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').redactor();
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });


            });



            sysrender.on('click', '#sms_invoice_created', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/sms_invoice_/' + iid + '/created',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').countSms('#sms-counter');
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });




            });

            sysrender.on('click', '#sms_invoice_reminder', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/sms_invoice_/' + iid + '/reminder',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').countSms('#sms-counter');
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });



            });

            sysrender.on('click', '#sms_invoice_overdue', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/sms_invoice_/' + iid + '/overdue',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').countSms('#sms-counter');
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });


            });

            sysrender.on('click', '#sms_invoice_confirm', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/sms_invoice_/' + iid + '/confirm',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').countSms('#sms-counter');
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });



            });

            sysrender.on('click', '#sms_invoice_refund', function(e){
                e.preventDefault();
                var iid = $("#iid").val();

                $.fancybox.open({
                    src  :  base_url + 'invoices/sms_invoice_/' + iid + '/refund',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#message').countSms('#sms-counter');
                        },
                        touch: false,
                        autoFocus: false,
                        keyboard: false,
                    },
                });



            });




            $modal.on('click', '#btnModalSMSSend', function(event){

                event.preventDefault();

                $.post(base_url + 'sms/init/send_invoice', {


                    message: $('#message').val(),
                    to: $("#sms_to").val(),
                    from: $("#sms_from").val(),
                    invoice_id: $("#smsInvoiceId").val()

                }).done(function (data) {

                    toastr.success(data);
                });

            });



            $modal.on('click', '#send', function(e){

                e.preventDefault();

                var attach_pdf = 'No';

                if($("#attach_pdf").prop('checked') == true){
                    attach_pdf = 'Yes';
                }



                $.post(base_url + 'invoices/send_email', $('#email_form').serialize()).done(function (data) {
                    toastr.success(data);
                });

            });

            $modal.on('click', '#save_payment', function(e){
                e.preventDefault();

                {if $d['type'] === 'Credit Note'}

                let save_payment_url = base_url + 'transactions/expense-post/' + iid;
                {else}
                let save_payment_url = base_url + 'invoices/add-payment-post/' + iid;
                {/if}

                $.post(save_payment_url, $("#form_add_payment").serialize())

                    .done(function (data) {


                        if ($.isNumeric(data)) {
                            location.reload();
                        }
                        else {
                            toastr.error(data);

                        }
                    });


            });

            $("#mark_paid").click(function (e) {
                e.preventDefault();


                app.confirm($("#_lan_msg_confirm").val(), function(result) {
                    if(result){
                        var iid = $("#iid").val();
                        $.post(  _url + "invoices/markpaid", { iid: iid })
                            .done(function( data ) {
                                location.reload();
                            });
                    }
                });

            });


            $("#mark_unpaid").click(function (e) {
                e.preventDefault();


                app.confirm($("#_lan_msg_confirm").val(), function(result) {
                    if(result){
                        var iid = $("#iid").val();
                        $.post(  _url + "invoices/markunpaid", { iid: iid })
                            .done(function( data ) {
                                location.reload();
                            });
                    }
                });

            });

            $("#mark_partially_paid").click(function (e) {
                e.preventDefault();
                app.confirm($("#_lan_msg_confirm").val(), function(result) {
                    if(result){
                        var iid = $("#iid").val();
                        $.post(  _url + "invoices/markpartiallypaid", { iid: iid })
                            .done(function( data ) {
                                location.reload();
                            });
                    }
                });

            });



            $modal.on('click', '#send_bcc_to_admin', function(e){

                e.preventDefault();


                $("#bccemail").val($("#admin_email").val());

            });

            $modal.on('hidden.bs.modal', function () {
                location.reload();
            });


            $('[data-bs-toggle="tooltip"]').tooltip();

            var $btn_add_file = $("#btn_add_file");

            var $file_link = $("#file_link");

            var upload_resp;




            var ib_file = new Dropzone("#upload_container",
                {
                    url: _url + "documents/upload/",
                    maxFiles: 1
                }
            );


            ib_file.on("sending", function() {

                $btn_add_file.prop('disabled', true);

            });

            ib_file.on("success", function(file,response) {

                $btn_add_file.prop('disabled', false);

                upload_resp = response;

                if(upload_resp.success == 'Yes'){

                    toastr.success(upload_resp.msg);
                    $file_link.val(upload_resp.file);


                }
                else{
                    toastr.error(upload_resp.msg);
                }


            });




            var $doc_title = $("#doc_title");


            $btn_add_file.on('click', function(e) {
                e.preventDefault();


                $.post( _url + "documents/post/", { title: $doc_title.val(), file_link: $file_link.val(), rid: iid, rtype: 'invoice' })
                    .done(function( data ) {

                        if ($.isNumeric(data)) {

                            location.reload();

                        }

                        else {
                            toastr.error(data);
                        }




                    });


            });

            const set_invoice_status = document.querySelectorAll('.set_invoice_status');


            if (set_invoice_status){
                set_invoice_status.forEach((item) => {
                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        const status = item.getAttribute('data-status');
                        const invoice_id = '{$d['id']}';
                        app.confirm($("#_lan_msg_confirm").val(), function(result) {
                            if(result){

                                $.post(  base_url + "invoices/set-status", {
                                    status: status,
                                    invoice_id: invoice_id,
                                })
                                    .done(function( data ) {
                                        location.reload();
                                    });
                            }
                        });

                    });
                });
            }


            const set_invoice_purchase_status = document.querySelectorAll('.set_invoice_purchase_status');

            if (set_invoice_purchase_status) {
                    set_invoice_purchase_status.forEach((item) => {
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            const status = item.getAttribute('data-status');
                            const invoice_id = '{$d['id']}';
                            app.confirm($("#_lan_msg_confirm").val(), function(result) {
                                if(result){

                                    $.post(  base_url + "invoices/set-purchase-status", {
                                        status: status,
                                        invoice_id: invoice_id,
                                    })
                                        .done(function( data ) {
                                            location.reload();
                                        });
                                }
                            });

                        });
                    });
                }

            const set_invoice_shipping_status = document.querySelectorAll('.set_invoice_shipping_status');

            if (set_invoice_shipping_status) {
                    set_invoice_shipping_status.forEach((item) => {
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            const status = item.getAttribute('data-status');
                            const invoice_id = '{$d['id']}';
                            app.confirm($("#_lan_msg_confirm").val(), function(result) {
                                if(result){

                                    $.post(  base_url + "invoices/set-shipping-status", {
                                        status: status,
                                        invoice_id: invoice_id,
                                    })
                                        .done(function( data ) {
                                            location.reload();
                                        });
                                }
                            });

                        });
                    });
                }



        });



    </script>
{/block}
