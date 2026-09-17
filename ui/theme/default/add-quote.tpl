{extends file="$layouts_admin"}

{block name="content"}
    <div class="row">
        <div class="col-lg-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>
                        {$_L['quote_alias']}
                    </h2>

                </div>

                <div class="panel-container">
                    <div class="panel-content" id="ibox_form">
                        <form id="invform" method="post">
                            <div class="ibox-content">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-danger" id="emsg" style="display: none;">
                                            <span id="emsgbody"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="subject">{$_L['Subject']}</label>
                                            <input type="text" class="form-control" name="subject" id="subject">
                                        </div>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">


                                    <div class="col-md-6">
                                        <div class="form-horizontal">





                                            <div class="mb-3">
                                                <label for="cid">{$_L['Customer']}</label>

                                                <select id="cid" name="cid" class="form-select">
                                                    <option value="">{$_L['Select Contact']}...</option>
                                                    {foreach $c as $cs}
                                                        <option value="{$cs['id']}"
                                                                {if $p_cid eq ($cs['id'])}selected="selected" {/if}>{$cs['account']} {if $cs['email'] neq ''}- {$cs['email']}{/if}</option>
                                                    {/foreach}

                                                </select>

                                                <span class="help-block"><a href="#"
                                                                            id="contact_add">| {$_L['Or Add New Customer']}</a> </span>
                                                <div id="invoice_type_indicator" class="mt-1"></div>

                                            </div>

                                            {$extra_fields}

                                            <div class="mb-3">
                                                <label>{$_L['Address']}</label>

                                                <textarea id="address" readonly class="form-control" rows="5"></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label for="invoicenum">{$_L['Quote Prefix']}</label>

                                                <input type="text" class="form-control" id="invoicenum" name="invoicenum" value="{$config['quotation_code_prefix']}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="cn">{$_L['Quote']} #</label>

                                                <input type="text" class="form-control" id="cn" name="cn" value="{str_pad($config['quotation_code_current_number'], $config['number_pad'], '0', STR_PAD_LEFT)}">
                                            </div>


                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-horizontal">
                                            <div class="mb-3">
                                                <label>{$_L['Date Created']}</label>

                                                <input type="text" class="form-control" id="idate" name="idate" datepicker
                                                       data-date-format="yyyy-mm-dd" data-auto-close="true"
                                                       value="{$idate}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="edate">{$_L['Expiry Date']}</label>

                                                <input type="text" class="form-control" id="edate" name="edate" datepicker
                                                       data-date-format="yyyy-mm-dd" data-auto-close="true"
                                                       value="{ib_after_1_month()}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="stage">{$_L['Stage']}</label>

                                                <select class="form-select" name="stage" id="stage">
                                                    <option value="Draft">{$_L['Draft']}</option>
                                                    <option value="Delivered">{$_L['Delivered']}</option>
                                                    <option value="Accepted">{$_L['Accepted']}</option>
                                                    <option value="Lost">{$_L['Lost']}</option>
                                                    <option value="Dead">{$_L['Dead']}</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="tid">{$_L['Sales TAX']}</label>

                                                <select id="tid" name="tid" class="form-select">
                                                    <option value="">{$_L['None']}</option>
                                                    {foreach $t as $ts}
                                                        <option value="{$ts['id']}">{$ts['name']}
                                                            ({{number_format($ts['rate'],2,$config['dec_point'],$config['thousands_sep'])}}
                                                            %)
                                                        </option>
                                                    {/foreach}

                                                </select>

                                                <input type="hidden" id="stax" name="stax" value="0.00">


                                            </div>

                                            <div class="mb-3">
                                                <label for="add_discount">{$_L['Discount']}</label>
                                                <a href="#" id="add_discount" class="btn btn-info btn-xs"
                                                   style="margin-top: 5px;"><i
                                                            class="fal fa-minus-circle"></i> {$_L['Set Discount']}</a>
                                            </div>


                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <hr>
                                        <div class="mb-3">
                                            <label for="proposal_text">{$_L['Proposal Text']}</label>
                                            <textarea class="form-control" id="proposal_text" name="proposal_text" rows="6"></textarea>
                                            <span class="help-block">{$_L['quote_help_top']}</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div>



                                <div class="table-responsive m-t">
                                    <table class="table table-bordered invoice-table" id="invoice_items">
                                        <thead>
                                        <tr>
                                            <th width="40%">{$_L['Item Name']}</th>
                                            <th width="12%">{$_L['Qty']}</th>
                                            <th width="12%">{$_L['Price']}</th>
                                            <th width="12%">{$_L['Total']}</th>
                                            <th width="12%">Tax</th>
                                            <th width="12%"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__('Add Product OR Service')}"><i class="fal fa-search"></i></button>
                                                <textarea class="form-control item_name" name="desc[]" rows="1"></textarea>
                                            </td>
                                            <td><input type="text" class="form-control qty" value="" name="qty[]"></td>
                                            <td><input type="text" class="form-control item_price" name="amount[]" value=""></td>
                                            <td class="ltotal"><input type="text" class="form-control lvtotal" readonly="" value=""></td>
                                            <td> <select class="form-control taxed" name="taxed[]"> <option value="Yes">Yes</option> <option value="No" selected="">No</option></select></td>
                                            <td class="row-action-cell text-center"><button type="button" class="btn btn-primary btn-sm row-add" data-bs-toggle="tooltip" data-placement="top" title="{__('Add blank Line')}"><i class="fal fa-plus"></i></button></td>
                                        </tr>

                                        </tbody>
                                    </table>

                                    <hr>

                                </div>
                                <!-- /table-responsive -->
                                <button type="button" class="d-none" id="blank-add"></button>
                                <button type="button" class="d-none" id="item-add"></button>
                                <button type="button" class="d-none" id="item-remove"></button>

                                <div class="mt-3 row">
                                    <div class="col-md-4 offset-md-8">
                                        <table class="table invoice-total">
                                            <tbody>
                                            <tr>
                                                <td><strong>{$_L['Sub Total']} :</strong></td>
                                                <td id="sub_total" class="amount" data-a-sign="" data-a-dec="{$config['dec_point']}"
                                                    data-a-sep="" data-d-group="2">0.00
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>{$_L['Discount']} <span id="is_pt"></span> :</strong></td>
                                                <td id="discount_amount_total" class="amount" data-a-sign=""
                                                    data-a-dec="{$config['dec_point']}" data-a-sep="" data-d-group="2">0.00
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>{$_L['TAX']} :</strong></td>
                                                <td id="taxtotal" class="amount" data-a-sign="" data-a-dec="{$config['dec_point']}"
                                                    data-a-sep="" data-d-group="2">0.00
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>{$_L['TOTAL']} :</strong></td>
                                                <td id="total" class="amount" data-a-sign="" data-a-dec="{$config['dec_point']}"
                                                    data-a-sep="" data-d-group="2">0.00
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr>

                                <div class="mb-3">
                                    <label for="customer_notes">{$_L['Customer Notes']}</label>
                                    <textarea class="form-control" id="customer_notes" name="customer_notes" rows="6"></textarea>
                                    <span class="help-block">{$_L['quote_help_footer']}</span>
                                </div>

                                <div class="text-end">
                                    <input type="hidden" id="_dec_point" name="_dec_point" value="{$config['dec_point']}">
                                    <input type="hidden" id="taxed_type" name="taxed_type" value="individual">
                                    <button class="btn btn-primary" id="submit">{$_L['Save']}
                                    </button>
                                </div>


                            </div>

                            <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="exampleModalLabel">{$_L['Set Discount']}</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="mb-2">{__('Type')}</label>

                                            <div class="mb-3">
                                                <div class="btn-group" role="group">
                                                    <input type="radio" class="btn-check discount_type" name="discount_type" id="radio_discount_type_p" autocomplete="off" value="p" checked>
                                                    <label class="btn btn-outline-primary" for="radio_discount_type_p">
                                                        {$_L['Percentage']}
                                                    </label>

                                                    <input type="radio" class="btn-check discount_type" name="discount_type" id="radio_discount_type_f" value="f" autocomplete="off">
                                                    <label class="btn btn-outline-primary" for="radio_discount_type_f">
                                                        {$_L['Fixed Amount']}
                                                    </label>

                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="mb-2">{__('Discount')}</label>
                                                <input type="text" class="form-control" id="discount_amount" name="discount_amount" value="">
                                            </div>


                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{$_L['Save']}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>


                    </div>
                </div>

            </div>
        </div>

    </div>



    {* lan variables *}

    <input type="hidden" id="_lan_set_discount" value="{$_L['Set Discount']}">
    <input type="hidden" id="_lan_discount" value="{$_L['Discount']}">
    <input type="hidden" id="_lan_discount_type" value="{$_L['Discount Type']}">
    <input type="hidden" id="_lan_percentage" value="{$_L['Percentage']}">
    <input type="hidden" id="_lan_fixed_amount" value="{$_L['Fixed Amount']}">
    <input type="hidden" id="_lan_btn_save" value="{$_L['Save']}">
    <input type="hidden" id="_lan_no_results_found" value="{$_L['No results found']}">


{/block}

{block name="script"}
    <script>
        $(function () {
            $('.amount').autoNumeric('init');


            var _url = $("#_url").val();


            $('#invoice_items').on('change', 'select', function(){
                //   $('#taxtotal').html('dd');
                var taxrate = $('#stax').val().replace(',', '.');
                // $(this).val(taxrate);

            });

            var item_remove = $('#item-remove');
            item_remove.hide();


            $('#proposal_text').redactor({
                minHeight: 300,
            });

            $('#customer_notes').redactor({
                minHeight: 300,
            });


            function update_address(){

                var cid = $('#cid').val();
                if(cid != ''){
                    $.post(_url + 'contacts/render-address/', {
                        cid: cid

                    })
                        .done(function (data) {
                            var adrs = $("#address");


                            adrs.html(data);

                        });

                    $.post(_url + 'contacts/json-single-contact/', {
                        cid: cid
                    })
                        .done(function (data) {
                            var $indicator = $("#invoice_type_indicator");
                            var buyerType = $.trim(String(data.buyer_type || '')).toLowerCase();
                            if (buyerType === 'company') {
                                $indicator.html('<span class="badge bg-info">{__("Standard Tax Invoice")}</span>');
                            } else if (buyerType === 'individual') {
                                $indicator.html('<span class="badge bg-secondary">{__("Simplified Tax Invoice")}</span>');
                            } else {
                                $indicator.html('');
                            }
                        });
                } else {
                    $("#invoice_type_indicator").html('');
                }

            }
            update_address();
            $('#cid').select2({
                language: {
                    noResults: function () {
                        return $("#_lan_no_results_found").val();
                    }
                }
            })
                .on("change", function(e) {
                    // mostly used event, fired to the original element when the value changes
                    // log("change val=" + e.val);
                    //  alert(e.val);

                    update_address();
                });






            function syncInvoiceRowButtons() {
                var rows = $("#invoice_items").find('tbody tr');

                rows.each(function (index) {
                    var $row = $(this);
                    var $cell = $row.find('.row-action-cell');

                    if (!$cell.length) {
                        return;
                    }

                    if (index === 0) {
                        $cell.html('<button type="button" class="btn btn-primary btn-sm row-add" data-bs-toggle="tooltip" data-placement="top" title="{__('Add blank Line')}"><i class="fal fa-plus"></i></button>');
                    } else {
                        $cell.html('<button type="button" class="btn btn-danger btn-sm row-remove" data-bs-toggle="tooltip" data-placement="top" title="{__('Delete')}"><i class="fal fa-minus"></i></button>');
                    }
                });
            }

            syncInvoiceRowButtons();

            $("#invoice_items").on('click', '.row-add', function () {
                $('#blank-add').trigger('click');
            });

            $("#invoice_items").on('click', '.row-remove', function () {
                $(this).closest('tr').remove();
                syncInvoiceRowButtons();
            });

            // Add a new line quickly when Ctrl+Enter is pressed inside an item row field.
            $("#invoice_items").on('keydown', 'input, select, textarea', function (e) {
                var isEnter = e.key === 'Enter' || e.keyCode === 13;
                if (!(e.ctrlKey || e.metaKey) || !isEnter) {
                    return;
                }

                e.preventDefault();
                $('#blank-add').trigger('click');

                var $newRowItemInput = $("#invoice_items").find('tbody tr:last .item_name');
                if ($newRowItemInput.length) {
                    $newRowItemInput.trigger('focus');
                }
            });

            var $itemLoadTargetRow = null;

            $("#invoice_items").on('click', '.row-item-search', function () {
                $itemLoadTargetRow = $(this).closest('tr');
                $('#item-add').trigger('click');
            });


            var $modal = $('#cloudonex_body');


            $('#item-add').on('click', function () {



                $.fancybox.open({
                    src  : base_url + 'ps/modal-list/',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $('#modal_items_table').dataTable(
                                {
                                    responsive: true,
                                    "language": {
                                        "emptyTable": "{$_L['No items to display']}",
                                        "info":      "{$_L['Showing _START_ to _END_ of _TOTAL_ entries']}",
                                        "infoEmpty":      "{$_L['Showing 0 to 0 of 0 entries']}",
                                        buttons: {
                                            pageLength: '{$_L['Show all']}'
                                        },
                                        searchPlaceholder: "{__('Search')}"
                                    },
                                });
                        },
                        touch: false,
                        autoFocus: false,
                    }
                });




            });


            /*
             / @since v 2.0
             */

            $('#contact_add').on('click', function(e){
                e.preventDefault();
                // create the backdrop and wait for next modal to be triggered
                $.fancybox.open({
                    src  : _url + 'contacts/modal_add/',
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            $("#country").select2({

                            });
                        }
                    }
                });

            });

            $('#blank-add').on('click', function(){
                $("#invoice_items").find('tbody')
                    .append(
                        '<tr> <td><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__("Add Product OR Service")}"><i class="fal fa-search"></i></button> <textarea class="form-control item_name" name="desc[]" rows="1"></textarea></td> <td><input type="text" class="form-control qty" value="" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value=""></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly value=""></td> <td> <select class="form-control taxed" name="taxed[]"> <option value="Yes">Yes</option> <option value="No" selected>No</option></select></td> <td class="row-action-cell text-center"></td></tr>'
                    );
                syncInvoiceRowButtons();

            });


            $modal.on('click', '.update', function(){
                var tableControl= document.getElementById('items_table');




                $('input:checkbox:checked', tableControl).each(function() {

                    var item_code = $(this).closest('tr').find('td:eq(1)').text();
                    var item_name = $(this).closest('tr').find('td:eq(2)').text();

                    var item_price = $(this).closest('tr').find('td:eq(3)').text();

                    //  obj.push(innertext);
                    $("#invoice_items").find('tbody')
                        .append(
                            '<tr> <td><button type="button" class="btn btn-info btn-sm row-item-search" data-bs-toggle="tooltip" data-placement="top" title="{__("Add Product OR Service")}"><i class="fal fa-search"></i></button> <textarea class="form-control item_name" name="desc[]" rows="1">' + item_name + '</textarea><input type="hidden" name="item_code[]" value="' + item_code + '"></td> <td><input type="text" class="form-control qty" value="1" name="qty[]"></td> <td><input type="text" class="form-control item_price" name="amount[]" value="' + item_price + '"></td> <td class="ltotal"><input type="text" class="form-control lvtotal" readonly value=""></td> <td> <select class="form-control taxed" name="taxed[]"> <option value="Yes">Yes</option> <option value="No" selected>No</option></select></td> <td class="row-action-cell text-center"></td></tr>'
                        );
                });

                syncInvoiceRowButtons();
                $itemLoadTargetRow = null;

                $.fancybox.close();

            });


            $modal.on('click', '.contact_submit', function(e){
                e.preventDefault();

                var $modalBody = $modal.find('.modal-body');
                $modalBody.find('.alert.alert-danger').remove();

                var buyerType = $.trim(String($('#buyer_type').val() || '')).toLowerCase();
                var selectedCompanyId = $.trim(String($('#modal_company_id').val() || ''));
                var isCompany = buyerType === 'company';

                function normalizeDigits(value) {
                    return String(value || '')
                        .replace(/[٠-٩]/g, function (d) { return String(d.charCodeAt(0) - 0x0660); })
                        .replace(/[۰-۹]/g, function (d) { return String(d.charCodeAt(0) - 0x06F0); });
                }

                var accountName = $.trim(String($('#account').val() || ''));
                var companyName = $.trim(String($('#company').val() || ''));
                var address = $.trim(String($('#m_address').val() || ''));
                var city = $.trim(String($('#city').val() || ''));
                var state = $.trim(String($('#state').val() || ''));
                var zip = $.trim(String($('#zip').val() || ''));
                var country = $.trim(String($('#country').val() || ''));
                var phone = $.trim(String($('#phone').val() || ''));
                var email = $.trim(String($('#email').val() || ''));
                var buildingNumber = normalizeDigits($.trim(String($('#building_number').val() || ''))).replace(/\D+/g, '');
                var vatNumber = normalizeDigits($.trim(String($('#vat_number').val() || ''))).replace(/\D+/g, '');
                var crnNumber = normalizeDigits($.trim(String($('#crn_number').val() || ''))).replace(/\D+/g, '');
                var idIqama = normalizeDigits($.trim(String($('#id_iqama').val() || ''))).replace(/\D+/g, '');

                var errors = [];

                if (buyerType !== 'company' && buyerType !== 'individual') {
                    errors.push('Buyer Type is required');
                } else if (buyerType === 'individual') {
                    if (!accountName) { errors.push('Full Name is required'); }
                    if (!phone) { errors.push('Phone is required'); }
                    if (idIqama && !/^\d{10}$/.test(idIqama)) { errors.push('ID / Iqama must be exactly 10 digits'); }
                } else {
                    if (!selectedCompanyId) { errors.push('Registered Company is required'); }
                    if (!companyName) { errors.push('Company Name is required'); }
                    if (!phone) { errors.push('Phone is required'); }
                    if (!address) { errors.push('Address is required'); }
                    if (!city) { errors.push('City is required'); }
                    if (!state) { errors.push('State/Region is required'); }
                    if (!zip) { errors.push('ZIP/Postal Code is required'); }
                    if (!country) { errors.push('Country is required'); }
                    if (!buildingNumber) { errors.push('Building Number is required'); }
                    if (!/^3\d{13}3$/.test(vatNumber)) { errors.push('VAT Number must be 15 digits and start/end with 3'); }
                    if (!/^\d{10}$/.test(crnNumber)) { errors.push('Unified No. (700#) must be 10 digits'); }
                }

                if (errors.length > 0) {
                    $modalBody.prepend('<div class="alert alert-danger fade in">' + errors.join('<br>') + '<button type="button" class="close btn btn-danger" data-dismiss="alert">&times;</button></div>');
                    return;
                }

                var _url = $("#_url").val();
                $.post(_url + 'contacts/add-post/', {
                    buyer_type: buyerType,
                    cid: isCompany ? selectedCompanyId : '',
                    account: buyerType === 'individual' ? accountName : '',
                    company: isCompany ? companyName : '',
                    company_url: isCompany ? $.trim(String($('#company_url').val() || '')) : '',
                    logo_url: isCompany ? $.trim(String($('#logo_url').val() || '')) : '',
                    address: isCompany ? address : '',
                    city: isCompany ? city : '',
                    state: isCompany ? state : '',
                    zip: isCompany ? zip : '',
                    country: isCompany ? country : '',
                    building_number: isCompany ? buildingNumber : '',
                    vat_number: isCompany ? vatNumber : '',
                    crn_number: isCompany ? crnNumber : '',
                    id_iqama: buyerType === 'individual' ? idIqama : '',
                    phone: phone,
                    email: email

                })
                    .done(function (data) {

                        var _url = $("#_url").val();
                        if ($.isNumeric(data)) {

                            // location.reload();
                            window.location = _url + 'quotes/new/1/' + data + '/';

                        }
                        else {

                            $modalBody.prepend('<div class="alert alert-danger fade in">' + data + '<button type="button" class="close btn btn-danger" data-dismiss="alert">&times;</button></div>');
                        }
                    });


            });

            const discountModal = new bootstrap.Modal('#discountModal', {
                keyboard: false,
            });

            const discount_types = document.querySelectorAll('input[name="discount_type"]');
            const discount_value = document.querySelector('#discount_value');

            discount_types.forEach((discount_type) => {
                discount_type.addEventListener('change', (event) => {
                    if (event.target.value === 'p') {
                        discount_value.setAttribute('max', '100');
                        discount_value.setAttribute('min', '0');
                    } else {
                        discount_value.removeAttribute('max');
                        discount_value.removeAttribute('min');
                    }

                });

            });


            $("#add_discount").click(function (e) {

                e.preventDefault();

                discountModal.show();


            });




            //var callbacks = $.Callbacks();
            //callbacks.add( updateTotal );
            //callbacks.fire(  alert('done') );


            $(".progress").hide();
            $("#emsg").hide();
            $("#submit").click(function (e) {
                e.preventDefault();
                $('#ibox_form').block({ message: null });
                var _url = $("#_url").val();
                $.post(_url + 'quotes/add-post/', $('#invform').serialize(), function(data){

                    var _url = $("#_url").val();
                    if ($.isNumeric(data)) {

                        window.location = _url + 'quotes/edit/' + data + '/';
                    }
                    else {
                        $('#ibox_form').unblock();
                        var body = $("html, body");
                        body.animate({ scrollTop:0 }, '1000', 'swing');
                        $("#emsgbody").html(data);
                        $("#emsg").show("slow");
                    }
                });
            });
        });
    </script>
{/block}
