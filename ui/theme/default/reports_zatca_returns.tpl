{extends file="$layouts_admin"}

{block name="content"}

<div class="row">
    <div class="col-md-12">
        <div class="panel-hdr">
            <h2>ZATCA Purchases and Expenses Report</h2>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-container">
        <div class="panel-content">
            <form method="get" action="{$_url}reports/zatca-returns-report/">
                <input type="hidden" name="ng" value="reports/zatca-returns-report">
                <div class="row mb-3">
                    <div class="col-md-2 text-end">
                        <label class="form-label mt-2"><strong>From Date</strong></label>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="fdate" value="{$fdate}">
                    </div>
                    <div class="col-md-2 text-end">
                        <label class="form-label mt-2"><strong>To Date</strong></label>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="tdate" value="{$tdate}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fal fa-search"></i> Search</button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="border rounded p-2 bg-light">
                            <label class="me-3 mb-1">
                                <input type="checkbox" name="only_taxable" value="1" {if $only_taxable}checked{/if}> Only taxable entries (Tax amount &gt; 0)
                            </label>
                            <label class="me-3 mb-1">
                                <input type="checkbox" name="only_vat_suppliers" value="1" {if $only_vat_suppliers}checked{/if}> Only suppliers with VAT registration number
                            </label>
                            <input type="hidden" name="include_expenses" value="0">
                            <label class="me-3 mb-1">
                                <input type="checkbox" name="include_expenses" value="1" {if $include_expenses}checked{/if}> Include Expenses
                            </label>
                            <label class="mb-1">
                                <input type="checkbox" name="only_returns" value="1" {if $only_returns}checked{/if}> Only ZATCA Return Invoices
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="alert alert-success mb-0">
                            <strong>Net Amount:</strong>
                            <span class="float-end">{$_c['symbol']} {$summary_net|number_format:2}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning mb-0">
                            <strong>Tax Amount:</strong>
                            <span class="float-end">{$_c['symbol']} {$summary_tax|number_format:2}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-info mb-0">
                            <strong>Gross Amount:</strong>
                            <span class="float-end">{$_c['symbol']} {$summary_gross|number_format:2}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-danger mb-0">
                            <strong>Total Expenses:</strong>
                            <span class="float-end">{$_c['symbol']} {$summary_expenses|number_format:2}</span>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <a class="btn btn-success" href="{$_url}reports/export-zatca-returns-invoices/?fdate={$fdate}&tdate={$tdate}&only_taxable={if $only_taxable}1{else}0{/if}&only_vat_suppliers={if $only_vat_suppliers}1{else}0{/if}&only_returns={if $only_returns}1{else}0{/if}">
                            <i class="fal fa-file-excel"></i> Download Invoices Excel
                        </a>
                        <a class="btn btn-success" href="{$_url}reports/export-zatca-returns-expenses/?fdate={$fdate}&tdate={$tdate}&only_taxable={if $only_taxable}1{else}0{/if}&only_vat_suppliers={if $only_vat_suppliers}1{else}0{/if}">
                            <i class="fal fa-file-excel"></i> Download Expenses Excel
                        </a>
                    </div>
                </div>
            </form>

            <div class="alert alert-info">
                This report includes purchase and expense rows, along with submitted ZATCA invoice/return rows and supplier VAT/Unified No. (700#) details needed for return preparation.
            </div>

            <div class="d-flex justify-content-end mb-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" id="btnBasicView">Basic View</button>
                    <button type="button" class="btn btn-light" id="btnFullView">Full View</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="zatca_returns_table" width="100%">
                    <thead>
                    <tr>
                        <th>Source</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Supplier</th>
                        <th class="full-only-col">Category</th>
                        <th class="full-only-col">Description</th>
                        <th>Net</th>
                        <th>Tax</th>
                        <th>Gross</th>
                        <th class="full-only-col">Rate %</th>
                        <th>Txn ID</th>
                        <th class="full-only-col">Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $rows as $row}
                        <tr
                            data-supplier-vat="{$row['details']['supplier_vat']|default:'-'|escape:'htmlall':'UTF-8'}"
                            data-supplier-unified="{$row['details']['supplier_unified']|default:'-'|escape:'htmlall':'UTF-8'}"
                            data-zatca-uuid="{$row['details']['zatca_uuid']|default:'-'|escape:'htmlall':'UTF-8'}"
                            data-zatca-invoice-type="{$row['details']['zatca_invoice_type']|default:'-'|escape:'htmlall':'UTF-8'}"
                            data-zatca-submission-status="{$row['details']['zatca_submission_status']|default:'-'|escape:'htmlall':'UTF-8'}"
                            data-zatca-submitted-at="{$row['details']['zatca_submitted_at']|default:'-'|escape:'htmlall':'UTF-8'}"
                            data-reporting-status="{$row['details']['reporting_status']|default:'-'|escape:'htmlall':'UTF-8'}"
                            data-validation-status="{$row['details']['validation_status']|default:'-'|escape:'htmlall':'UTF-8'}"
                        >
                            <td>{$row['source']|escape}</td>
                            <td>{$row['date']|escape}</td>
                            <td>{$row['type']|escape}</td>
                            <td>{$row['reference']|escape}</td>
                            <td>{$row['supplier']|escape}</td>
                            <td class="full-only-cell">{$row['category']|escape}</td>
                            <td class="full-only-cell">{$row['description']|escape}</td>
                            <td class="text-end">{$_c['symbol']} {$row['net']|number_format:2}</td>
                            <td class="text-end">{$_c['symbol']} {$row['tax']|number_format:2}</td>
                            <td class="text-end">{$_c['symbol']} {$row['gross']|number_format:2}</td>
                            <td class="text-end full-only-cell">{$row['rate']|number_format:2}</td>
                            <td class="text-center">{$row['txn_id']|escape}</td>
                            <td class="text-center full-only-cell">
                                <button type="button" class="btn btn-info btn-xs js-toggle-detail"><i class="fal fa-plus"></i></button>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{/block}

{block name="script"}
    <script>
        $(function () {
            var table = null;
            var fullOnlyColumnIndexes = [5, 6, 10, 12];

            function detailHtml($tr) {
                function v(name) {
                    var val = $tr.data(name);
                    if (typeof val === 'undefined' || val === null || val === '') {
                        return '-';
                    }
                    return String(val);
                }

                return '' +
                    '<div class="row p-2">' +
                    '  <div class="col-md-6 mb-2">' +
                    '    <strong>Supplier VAT:</strong> ' + v('supplierVat') + '<br>' +
                    '    <strong>ZATCA UUID:</strong> ' + v('zatcaUuid') + '<br>' +
                    '    <strong>ZATCA Submission Status:</strong> ' + v('zatcaSubmissionStatus') + '<br>' +
                    '    <strong>Reporting Status:</strong> ' + v('reportingStatus') +
                    '  </div>' +
                    '  <div class="col-md-6 mb-2">' +
                    '    <strong>Supplier Unified No. (700#):</strong> ' + v('supplierUnified') + '<br>' +
                    '    <strong>ZATCA Invoice Type:</strong> ' + v('zatcaInvoiceType') + '<br>' +
                    '    <strong>ZATCA Submitted At:</strong> ' + v('zatcaSubmittedAt') + '<br>' +
                    '    <strong>Validation Status:</strong> ' + v('validationStatus') +
                    '  </div>' +
                    '</div>';
            }

            function setView(mode) {
                if (!table) {
                    return;
                }

                var isFull = mode === 'full';

                table.rows().every(function () {
                    if (this.child.isShown()) {
                        this.child.hide();
                    }
                });

                $('#zatca_returns_table tbody tr').removeClass('shown');
                $('.js-toggle-detail i').removeClass('fa-minus').addClass('fa-plus');

                fullOnlyColumnIndexes.forEach(function (idx) {
                    table.column(idx).visible(isFull, false);
                });
                table.columns.adjust().draw(false);

                $('#btnFullView').toggleClass('btn-primary', isFull).toggleClass('btn-light', !isFull);
                $('#btnBasicView').toggleClass('btn-primary', !isFull).toggleClass('btn-light', isFull);
            }

            if ($.fn.DataTable) {
                table = $('#zatca_returns_table').DataTable({
                    pageLength: 10,
                    order: [[1, 'desc']],
                    dom: 'Bfrtip',
                    language: {
                        emptyTable: 'No records found for selected filters.'
                    },
                    buttons: [
                        { extend: 'excel', text: '<i class="fal fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                        { extend: 'pdf', text: '<i class="fal fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' },
                        { extend: 'print', text: '<i class="fal fa-print"></i> Print', className: 'btn btn-primary btn-sm' }
                    ]
                });

                setView('basic');
            }

            $('#zatca_returns_table tbody').on('click', '.js-toggle-detail', function () {
                if (!table) {
                    return;
                }

                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var row = table.row($tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    $tr.removeClass('shown');
                    $btn.find('i').removeClass('fa-minus').addClass('fa-plus');
                } else {
                    row.child(detailHtml($tr)).show();
                    $tr.addClass('shown');
                    $btn.find('i').removeClass('fa-plus').addClass('fa-minus');
                }
            });

            $('#btnBasicView').on('click', function () {
                setView('basic');
            });

            $('#btnFullView').on('click', function () {
                setView('full');
            });
        });
    </script>
{/block}
