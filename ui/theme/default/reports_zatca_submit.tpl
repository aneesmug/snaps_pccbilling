{extends file="$layouts_admin"}

{block name="content"}

<div class="row mb-3">
    <div class="col-md-12">
        <h2 class="mb-2">ZATCA Submit Report</h2>
        <p class="text-muted mb-0">Submitted/registered ZATCA invoices (including Return Invoices and Cancelled invoices, shown as negative amounts) with reporting and validation status, plus paid expenses/bills (utility, electric, etc.) for reference. This is an internal audit export, not a ZATCA upload file — expense rows are not submitted to ZATCA.</p>
    </div>
</div>

<div class="card border mb-3">
    <div class="card-body">
        <form method="get" action="{$_url}reports/zatca-submit-report/">
            <input type="hidden" name="ng" value="reports/zatca-submit-report">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="fdate" value="{$fdate}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="tdate" value="{$tdate}">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a class="btn btn-success" href="{$_url}reports/export-zatca-submit-report/?fdate={$fdate}&tdate={$tdate}">
                        <i class="fal fa-file-excel"></i> Download Audit Excel
                    </a>
                    <a class="btn btn-outline-primary" href="{$_url}reports/export-zatca-vat-return/?fdate={$fdate}&tdate={$tdate}">
                        <i class="fal fa-file-invoice-dollar"></i> Download VAT Return Excel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{if !empty($vat_summary)}
<div class="card border mb-3">
    <div class="card-header"><strong>ZATCA VAT Return Summary</strong> <span class="text-muted small">(figures for the period above — verify against the ZATCA portal before filing)</span></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-bordered mb-3 mb-md-0">
                    <thead><tr><th colspan="3">Sales (Outputs)</th></tr></thead>
                    <tbody>
                        <tr><td>Standard-rated sales</td><td class="text-end">{$vat_summary['standard_sales_net']|string_format:"%.2f"}</td><td class="text-end">{$vat_summary['standard_sales_vat']|string_format:"%.2f"}</td></tr>
                        <tr><td>Less: Sales returns / credit notes</td><td class="text-end text-danger">-{$vat_summary['returns_net']|string_format:"%.2f"}</td><td class="text-end text-danger">-{$vat_summary['returns_vat']|string_format:"%.2f"}</td></tr>
                        <tr><td>Less: Cancelled invoices</td><td class="text-end text-danger">-{$vat_summary['cancelled_net']|string_format:"%.2f"}</td><td class="text-end text-danger">-{$vat_summary['cancelled_vat']|string_format:"%.2f"}</td></tr>
                        <tr><td>Zero-rated / exempt sales</td><td class="text-end">{$vat_summary['zero_exempt_sales_net']|string_format:"%.2f"}</td><td class="text-end">0.00</td></tr>
                        <tr class="table-light"><td><strong>Total Output VAT</strong></td><td></td><td class="text-end"><strong>{$vat_summary['total_output_vat']|string_format:"%.2f"}</strong></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-bordered mb-3 mb-md-0">
                    <thead><tr><th colspan="3">Purchases (Inputs)</th></tr></thead>
                    <tbody>
                        <tr><td>Standard-rated purchases / expenses</td><td class="text-end">{$vat_summary['standard_purchases_net']|string_format:"%.2f"}</td><td class="text-end">{$vat_summary['standard_purchases_vat']|string_format:"%.2f"}</td></tr>
                        <tr><td>Zero-rated / exempt purchases</td><td class="text-end">{$vat_summary['zero_purchases_net']|string_format:"%.2f"}</td><td class="text-end">0.00</td></tr>
                        <tr class="table-light"><td><strong>Total Input VAT</strong></td><td></td><td class="text-end"><strong>{$vat_summary['total_input_vat']|string_format:"%.2f"}</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="alert {if $vat_summary['net_vat_due'] ge 0}alert-warning{else}alert-info{/if} mb-0">
            <strong>Net VAT {if $vat_summary['net_vat_due'] ge 0}Due{else}Refundable{/if}:</strong> {$vat_summary['net_vat_due']|string_format:"%.2f"}
        </div>
    </div>
</div>
{/if}

<div class="card border">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Invoice Number</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Customer VAT</th>
                    <th>Status</th>
                    <th>Sub Total</th>
                    <th>Tax</th>
                    <th>Total</th>
                    <th>ZATCA Status</th>
                    <th>Reporting Status</th>
                    <th>Validation Status</th>
                    <th>Submitted At</th>
                </tr>
                </thead>
                <tbody>
                {foreach $rows as $row}
                    <tr{if $row['status'] eq 'Cancelled'} class="table-secondary"{/if}>
                        <td>{$row['source']}</td>
                        <td>{$row['id']}</td>
                        <td>{$row['invoice_number']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['customer']}</td>
                        <td>{$row['customer_vat']}</td>
                        <td>{if $row['status'] eq 'Cancelled'}<span class="badge bg-dark">Cancelled</span>{else}{$row['status']}{/if}</td>
                        <td{if $row['subtotal'] lt 0} class="text-danger fw-bold"{/if}>{$row['subtotal']}</td>
                        <td{if $row['tax'] lt 0} class="text-danger fw-bold"{/if}>{$row['tax']}</td>
                        <td{if $row['total'] lt 0} class="text-danger fw-bold"{/if}>{$row['total']}</td>
                        <td>{$row['zatca_status']}</td>
                        <td>{$row['reporting_status']}</td>
                        <td>{$row['validation_status']}</td>
                        <td>{$row['submitted_at']}</td>
                    </tr>
                {foreachelse}
                    <tr>
                        <td colspan="14" class="text-center text-muted">No submitted ZATCA invoices or paid expenses found for selected date range.</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

{/block}
