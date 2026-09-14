{extends file="$layouts_admin"}

{block name="content"}
    <div class="row">
        <div class="col-md-6">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>{$_L['General Settings']}</h2>

                </div>

                <div class="panel-container">
                    <div class="panel-content">

                        <form role="form" name="accadd" method="post" action="{$_url}settings/app-post">

                            <div class="mb-3">
                                <label for="company">{$_L['Application Name']}</label>
                                <input type="text" class="form-control" id="company" name="company"
                                       value="{$config['CompanyName']}">
                                <span class="help-block">{$_L['This Name will be']}</span>
                            </div>


                            <div class="mb-3">
                                <label for="default_landing_page">{$_L['Default Landing Page']}</label>
                                <select name="default_landing_page" id="default_landing_page" class="form-select">
                                    <option value="login"
                                            {if $config['default_landing_page'] eq 'login'}selected="selected" {/if}>{$_L['Admin Login']}</option>
                                    <option value="client/login"
                                            {if $config['default_landing_page'] eq 'client/login'}selected="selected" {/if}>{$_L['Client Login']}</option>
                                    <option value="client/home"
                                            {if $config['default_landing_page'] eq 'client/home'}selected="selected" {/if}>{__('Client Home')}</option>


                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="opt_dashboard">{$_L['Dashboard']}</label>
                                <select name="dashboard" id="opt_dashboard" class="form-select">
                                    <option value="canvas" {if $config['dashboard'] eq 'canvas'}selected="selected" {/if}>Canvas</option>
                                </select>
                            </div>

                            {if empty($config['disable_invoicing'])}
                                <hr>
                                <div class="mb-3">
                                    <label for="caddress">{$_L['Pay To Address']}</label>

                                    <textarea class="form-control" id="caddress" name="caddress"
                                              rows="3">{$config['caddress']}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="inputAddressFormat">{$_L['Address Format']}</label>
                                    <select name="address_format" id="inputAddressFormat" class="form-select">

                                        <option value="default" {if !isset($config['address_format']) || $config['address_format'] eq 'default'}selected="selected" {/if}>Default / USA / Australia / India & Other Counntries</option>

                                        <option value="eu" {if isset($config['address_format']) && $config['address_format'] eq 'eu'}selected="selected" {/if}>UK / EU / Brazil / Mexico / Israel / Russia / Turkey / China / Hongkong</option>

                                        <option value="saudi_zatca" {if isset($config['address_format']) && $config['address_format'] eq 'saudi_zatca'}selected="selected" {/if}>Saudi Arabia (ZATCA)</option>


                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="inputTaxSystem">{$_L['Tax System']}</label>
                                    <select name="tax_system" id="inputTaxSystem" class="form-select">

                                        <option value="default" {if $config['tax_system'] eq 'default'}selected="selected" {/if}>Default</option>
                                        <option value="ca_quebec" {if $config['tax_system'] eq 'ca_quebec'}selected="selected" {/if}>Quebec Canada</option>
                                        <option value="India" {if $config['tax_system'] eq 'India'}selected="selected" {/if}>India</option>
                                        <option value="saudi_zatca" {if $config['tax_system'] eq 'saudi_zatca'}selected="selected" {/if}>Saudi ZATCA</option>

                                    </select>
                                </div>


                                <div class="mb-3">
                                    <label for="inputBusinessLocation">{$_L['Business Location']}</label>
                                    {if $config['tax_system']  eq 'default'}

                                        <input type="text" class="form-control" id="inputBusinessLocation" name="business_location"
                                               value="{$config['business_location']}">
                                    {elseif $config['tax_system'] eq 'India'}
                                        <select name="business_location" id="inputBusinessLocation" class="form-select">

                                            {foreach Tax::indianStates() as $state}
                                                <option value="{$state['name']}" {if $config['business_location'] eq $state['name']}selected{/if}>{$state['name']}</option>
                                            {/foreach}

                                        </select>

                                    {else}
                                        <input type="text" class="form-control" id="inputBusinessLocation" name="business_location"
                                               value="{$config['business_location']}">
                                    {/if}
                                </div>


                                <div class="mb-3">

                                    {if $config['tax_system'] == 'India'}

                                        <label for="vat_number">GSTIN</label>

                                    {elseif $config['tax_system'] == 'saudi_zatca'}

                                        <label for="vat_number">VAT Number (ZATCA)</label>

                                    {else}

                                        <label for="vat_number">{$_L['TAX/VAT Number']}</label>

                                    {/if}


                                    <input type="text" class="form-control" id="vat_number" name="vat_number"
                                            {if isset($config['vat_number'])} value="{$config['vat_number']}" {/if}>

                                </div>

                                <div class="mb-3">
                                    <label>{__('Tax/VAT Number Label')}</label>
                                    <input type="text" class="form-control" id="label_tax_number" name="label_tax_number"
                                           value="{sp_config_or_default('label_tax_number')}">
                                </div>

                                <div class="mb-3">

                                    <label for="invoice_terms">{$_L['Default Invoice Terms']}</label>

                                    <textarea class="form-control" id="invoice_terms" name="invoice_terms"
                                              rows="3">{$config['invoice_terms']}</textarea>

                                </div>

                                <div class="mb-3">

                                    <label for="invoice_footer_html">{__('Invoice Footer HTML')}</label>

                                    <textarea class="form-control" id="invoice_footer_html" name="invoice_footer_html"
                                              rows="5">{$config['invoice_footer_html']|default:''}</textarea>
                                    <span class="help-block">{__('This HTML will be shown in invoice print and PDF footer block.')}</span>

                                </div>


                                <div class="mb-3">
                                    <label for="show_quantity_as">{$_L['Default']} : {$_L['Show quantity as']}</label>
                                    <input type="text" class="form-control" id="show_quantity_as" name="show_quantity_as" value="{$config['show_quantity_as']}">

                                </div>

                                <div class="mb-3">
                                    <label for="pdf_font">{$_L['PDF Font']}</label>
                                    <select name="pdf_font" id="pdf_font" class="form-select">
                                        <option value="default" {if $config['pdf_font'] eq 'default'}selected="selected" {/if}>Default
                                            [Faster PDF Creation with Less Memory]
                                        </option>
                                        <option value="Helvetica" {if $config['pdf_font'] eq 'Helvetica'}selected="selected" {/if}>
                                            Helvetica
                                        </option>
                                        <option value="dejavusanscondensed"
                                                {if $config['pdf_font'] eq 'dejavusanscondensed'}selected="selected" {/if}>
                                            dejavusanscondensed [Embed fonts with supports UTF8]
                                        </option>

                                        <option value="AdobeCJK" {if $config['pdf_font'] eq 'AdobeCJK'}selected="selected" {/if}>
                                            AdobeCJK [Adobe Asian Font pack]
                                        </option>

                                    </select>
                                </div>


                                <div class="mb-3">
                                    <label for="invoice_default_date">{$_L['Payment Terms']}</label>
                                    <select name="invoice_default_date" id="invoice_default_date" class="form-select">
                                        <option value="due_on_receipt" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'due_on_receipt' )} selected{/if}>{$_L['Due On Receipt']}</option>
                                        <option value="days3" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days3' )} selected{/if}>{$_L['days_3']}</option>
                                        <option value="days5" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days5' )} selected{/if}>{$_L['days_5']}</option>
                                        <option value="days7" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days7' )} selected{/if}>{$_L['days_7']}</option>
                                        <option value="days10" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days10' )} selected{/if}>{$_L['days_10']}</option>
                                        <option value="days15" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days15' )} selected{/if}>{$_L['days_15']}</option>
                                        <option value="days30" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days30' )} selected{/if}>{$_L['days_30']}</option>
                                        <option value="days45" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days45' )} selected{/if}>{$_L['days_45']}</option>
                                        <option value="days60" {if isset($config['invoice_default_date']) && ($config['invoice_default_date'] == 'days60' )} selected{/if}>{$_L['days_60']}</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="invoice_logo_height">{__('Invoice Logo Height')}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="invoice_logo_height" id="invoice_logo_height"
                                        value="{(!empty($config['invoice_logo_height'])) ? $config['invoice_logo_height'] : ''}"
                                        >
                                        <span class="input-group-text" id="basic-addon2">px</span>
                                    </div>
                                </div>


                            {/if}


                            <button type="submit" class="btn btn-primary">{$_L['Submit']}</button>
                        </form>

                    </div>
                </div>

            </div>







            <div class="panel">
                <div class="panel-hdr">
                    <h2>Google reCAPTCHA (V3)</h2>

                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <form role="form" name="accadd" method="post" action="{$_url}settings/recaptcha_post/">

                            <div class="mb-3">
                                <label for="recaptcha">{$_L['Enable Recaptcha']}</label>
                                <select name="recaptcha" id="recaptcha" class="form-select">
                                    <option value="1"
                                            {if $config['recaptcha'] eq '1'}selected="selected" {/if}>{$_L['Yes']}</option>
                                    <option value="0"
                                            {if $config['recaptcha'] eq '0'}selected="selected" {/if}>{$_L['No']}</option>


                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="recaptcha_sitekey">{$_L['Recaptcha']} {$_L['Site Key']}</label>
                                <input type="text" class="form-control" id="recaptcha_sitekey" name="recaptcha_sitekey" value="{$config['recaptcha_sitekey']}">

                            </div>

                            <div class="mb-3">
                                <label for="recaptcha_secretkey">{$_L['Recaptcha']} {$_L['Secret Key']}</label>
                                <input type="text" class="form-control" id="recaptcha_secretkey" name="recaptcha_secretkey" value="{$config['recaptcha_secretkey']}">

                            </div>



                            <button type="submit" class="btn btn-primary">{$_L['Submit']}</button>
                        </form>
                    </div>


                </div>
            </div>


            {if !empty($config['invoicing'])}

                <div class="panel">
                    <div class="panel-hdr">
                        <h2>{$_L['Other Settings']}</h2>

                    </div>
                    <div class="panel-container">

                        <div class="panel-content">

                            <form role="form" method="post" action="{$_url}settings/other-settings-post/">


                                <div class="mb-3">
                                    <label for="customer_code_prefix">{$_L['Customer Code Prefix']}</label>
                                    <input type="text" class="form-control" name="customer_code_prefix" value="{$config['customer_code_prefix']}">

                                </div>

                                <div class="mb-3">
                                    <label for="customer_code_current_number">{$_L['Customer Code Number']}</label>
                                    <input type="text" class="form-control" name="customer_code_current_number" value="{$config['customer_code_current_number']}">

                                </div>

                                <div class="mb-3">
                                    <label for="invoice_code_prefix">{$_L['Invoice Prefix']}</label>
                                    <input type="text" class="form-control" name="invoice_code_prefix" value="{$config['invoice_code_prefix']}">

                                </div>

                                <div class="mb-3">
                                    <label for="invoice_code_current_number">{$_L['Invoice Number']}</label>
                                    <input type="text" class="form-control" name="invoice_code_current_number" value="{$config['invoice_code_current_number']}">

                                </div>

                                <div class="mb-3">
                                    <label for="purchase_code_prefix">{$_L['Purchase Order Prefix']}</label>
                                    <input type="text" class="form-control" name="purchase_code_prefix" value="{$config['purchase_code_prefix']}">

                                </div>

                                <div class="mb-3">
                                    <label for="purchase_code_current_number">{$_L['Purchase Order Number']}</label>
                                    <input type="text" class="form-control" name="purchase_code_current_number" value="{$config['purchase_code_current_number']}">

                                </div>

                                <div class="mb-3">
                                    <label for="quotation_code_prefix">{$_L['Quote Prefix']}</label>
                                    <input type="text" class="form-control" name="quotation_code_prefix" value="{$config['quotation_code_prefix']}">

                                </div>

                                <div class="mb-3">
                                    <label for="quotation_code_current_number">{$_L['Quote Number']}</label>
                                    <input type="text" class="form-control" name="quotation_code_current_number" value="{$config['quotation_code_current_number']}">

                                </div>

                                <div class="mb-3">
                                    <label for="income_code_prefix">{$_L['Income Code Prefix']}</label>
                                    <input type="text" class="form-control" name="income_code_prefix" value="{$config['income_code_prefix']}">

                                </div>

                                <div class="mb-3">
                                    <label for="income_code_current_number">{$_L['Income Code Number']}</label>
                                    <input type="text" class="form-control" name="income_code_current_number" value="{$config['income_code_current_number']}">

                                </div>


                                <div class="mb-3">
                                    <label for="expense_code_prefix">{$_L['Expense Code Prefix']}</label>
                                    <input type="text" class="form-control" name="expense_code_prefix" value="{$config['expense_code_prefix']}">

                                </div>

                                <div class="mb-3">
                                    <label for="expense_code_current_number">{$_L['Expense Code Number']}</label>
                                    <input type="text" class="form-control" name="expense_code_current_number" value="{$config['expense_code_current_number']}">

                                </div>

                                <div class="mb-3">
                                    <label for="ticket_code_prefix">{$_L['Ticket Prefix']}</label>
                                    <input type="text" class="form-control" name="ticket_code_prefix" value="{$config['ticket_code_prefix']}">

                                </div>

                                <div class="mb-3">
                                    <label for="ticket_code_current_number">{$_L['Ticket Number']}</label>
                                    <input type="text" class="form-control" name="ticket_code_current_number" value="{$config['ticket_code_current_number']}">

                                </div>


                                <div class="mb-3">
                                    <label for="contact_extra_field">{$_L['Contact Extra Field Name']}</label>
                                    <input type="text" class="form-control" name="contact_extra_field" value="{$config['contact_extra_field']}">

                                </div>




                                <div class="mb-3">
                                    <label for="gmap_api_key">{$_L['Google Maps']} {$_L['API Key']}</label>
                                    <input type="text" class="form-control" id="gmap_api_key" name="gmap_api_key" value="{$config['gmap_api_key']}">

                                </div>

                                <div class="mb-3">
                                    <label for="slack_webhook_url">{$_L['Slack Webhook URL']}</label>
                                    <input type="text" class="form-control" id="slack_webhhok_url" name="slack_webhook_url" value="{$config['slack_webhook_url']}">

                                </div>




                                <button type="submit" class="btn btn-primary">{$_L['Submit']}</button>
                            </form>

                        </div>




                    </div>
                </div>

            {/if}



            <div class="panel" id="additional_settings">
                <div class="panel-hdr">
                    <h5>{$_L['Additional Settings']}</h5>


                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <table class="table table-hover">
                            <tbody>

                            <tr>
                                <td width="80%"><label for="url_rewrite">{$_L['URL Rewrite']} </label>
                                    <br>
                                    <p>Please do not enable this, unless you are sure.</p>
                                </td>
                                <td><input type="checkbox" {if get_option('url_rewrite') eq '1'}checked{/if}
                                           data-toggle="toggle" data-size="small" data-on="{$_L['Yes']}" data-off="{$_L['No']}"
                                           id="url_rewrite"></td>
                            </tr>


                            </tbody>
                        </table>

                    </div>



                </div>
            </div>


        </div>


        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="mb-5">
                        <h5>OpenAI</h5>
                        <form method="post" action="{$base_url}settings/save-settings">
                            <div class="mb-3">
                                <label for="openai_api_base_url">{__('Base URL')}</label>
                                <input type="text" class="form-control" id="openai_api_base_url" name="openai_api_base_url" value="{$config['openai_api_base_url']|default:'api.openai.com/v1'}">
                            </div>
                            <div class="mb-3">
                                <label for="openai_api_key">{__('API Key')}</label>
                                <input type="password" class="form-control" id="openai_api_key" name="openai_api_key" value="{$config['openai_api_key']|default:''}">
                            </div>
                            <div class="mb-3">
                                <label for="openai_api_model">{__('Model')}</label>
                                <input type="text" class="form-control" id="openai_api_model" name="openai_api_model" value="{$config['openai_api_model']|default:'gpt-4o'}">
                            </div>
                            <button class="btn btn-primary" type="submit">{__('Save')}</button>
                        </form>
                    </div>

                    <div class="mb-5">
                        <h5>Anthropic</h5>
                        <form method="post" action="{$base_url}settings/save-settings">
                            <div class="mb-3">
                                <label for="anthropic_api_key">{__('API Key')}</label>
                                <input type="password" class="form-control" id="anthropic_api_key" name="anthropic_api_key" value="{$config['anthropic_api_key']|default:''}">
                            </div>
                            <button class="btn btn-primary" type="submit">{__('Save')}</button>
                        </form>
                    </div>

                    <div class="mb-5">
                        <h5>Google</h5>
                        <form method="post" action="{$base_url}settings/save-settings">
                            <div class="mb-3">
                                <label for="google_maps_api_key">{__('Maps API Key')}</label>
                                <input type="text" class="form-control" id="google_maps_api_key" name="google_maps_api_key" value="{$config['google_maps_api_key']|default:''}">
                            </div>
                            <button class="btn btn-primary" type="submit">{__('Save')}</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
{/block}

{block name="script"}

    <script>
        $(function () {

            $('#invoice_terms').redactor({
                minHeight: 200,
                buttons: ['html', '|', 'formatting', '|', 'bold', 'italic', 'link', 'unorderedlist', 'orderedlist']
            });

            $('#invoice_footer_html').redactor({
                minHeight: 200,
                buttons: ['html', '|', 'formatting', '|', 'bold', 'italic', 'link', 'unorderedlist', 'orderedlist']
            });

            $('#console_notify_invoice_created').change(function() {

                $('#additional_settings').block({ message: null });


                if($(this).prop('checked')){

                    $.post( base_url+'settings/update_option/', { opt: "console_notify_invoice_created", val: "1" })
                        .done(function( data ) {
                            $('#additional_settings').unblock();
                            location.reload();
                        });

                }
                else{
                    $.post( base_url+'settings/update_option/', { opt: "console_notify_invoice_created", val: "0" })
                        .done(function( data ) {
                            $('#additional_settings').unblock();
                            location.reload();
                        });
                }
            });

        });
    </script>

{/block}
