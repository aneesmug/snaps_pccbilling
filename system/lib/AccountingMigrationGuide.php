<?php

/**
 * ============================================================
 * ACCOUNTING SYSTEM - MIGRATION GUIDE
 * ============================================================
 * How to migrate existing invoices and bills to the new GL system
 * 
 * Strategy: Non-destructive, gradual migration
 * - Old system continues to work
 * - New GL system runs in parallel
 * - Reports can use either system or both
 * - Zero downtime migration
 * ============================================================
 */

/**
 * MIGRATION STRATEGY
 * 
 * Phase 1: Bulk Historical Load (Run once)
 * - Post all existing invoices to AR/GL
 * - Post all existing bills to AP/GL
 * - Creates opening balances in GL
 * 
 * Phase 2: Ongoing Integration (Run daily/weekly)
 * - New invoices auto-post to GL
 * - New bills auto-post to GL
 * - Payments reconcile automatically
 * 
 * Phase 3: Cutover (Optional)
 * - Lock legacy system
 * - Switch to GL-based reporting
 * - Archive old system
 */

class AccountingMigrationGuide
{
    /**
     * ============================================================
     * PHASE 1: MIGRATE HISTORICAL INVOICES
     * ============================================================
     */
    public function migrateHistoricalInvoices()
    {
        $ar = new AccountsReceivableService();
        $invoices = DB::table('sys_invoices')
            ->where('status', 'posted')
            ->where('posted_at', '<=', date('Y-m-d H:i:s', strtotime('-1 month')))
            ->orderBy('id')
            ->get();

        $count = 0;
        $errors = [];

        foreach ($invoices as $invoice) {
            try {
                // Check if already posted to GL
                $existing_je = DB::table('sys_journal_entries')
                    ->where('source_module', 'invoice')
                    ->where('source_id', $invoice->id)
                    ->exists();

                if (!$existing_je) {
                    $result = $ar->postInvoiceToAR($invoice->id);

                    if ($result['success']) {
                        $count++;
                        echo "✓ Invoice {$invoice->id} posted to GL\n";
                    } else {
                        $errors[] = "Invoice {$invoice->id}: " . $result['message'];
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Invoice {$invoice->id}: " . $e->getMessage();
            }

            // Batch processing
            if ($count % 100 === 0) {
                echo "Processed {$count} invoices...\n";
            }
        }

        return [
            'success' => count($errors) === 0,
            'processed' => $count,
            'errors' => $errors
        ];
    }

    /**
     * PHASE 1: MIGRATE HISTORICAL BILLS
     */
    public function migrateHistoricalBills()
    {
        $ap = new AccountsPayableService();
        $bills = DB::table('sys_bills')
            ->where('status', 'posted')
            ->where('posted_at', '<=', date('Y-m-d H:i:s', strtotime('-1 month')))
            ->orderBy('id')
            ->get();

        $count = 0;
        $errors = [];

        foreach ($bills as $bill) {
            try {
                // Check if already posted to GL
                $existing_je = DB::table('sys_journal_entries')
                    ->where('source_module', 'bill')
                    ->where('source_id', $bill->id)
                    ->exists();

                if (!$existing_je) {
                    $result = $ap->postBillToAP($bill->id);

                    if ($result['success']) {
                        $count++;
                        echo "✓ Bill {$bill->id} posted to GL\n";
                    } else {
                        $errors[] = "Bill {$bill->id}: " . $result['message'];
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Bill {$bill->id}: " . $e->getMessage();
            }

            if ($count % 100 === 0) {
                echo "Processed {$count} bills...\n";
            }
        }

        return [
            'success' => count($errors) === 0,
            'processed' => $count,
            'errors' => $errors
        ];
    }

    /**
     * PHASE 1: MIGRATE HISTORICAL PAYMENTS
     */
    public function migrateHistoricalPayments()
    {
        $ar = new AccountsReceivableService();
        $ap = new AccountsPayableService();

        // AR Payments
        $ar_payments = DB::table('sys_payments')
            ->where('payment_type', 'customer')
            ->where('status', 'posted')
            ->where('payment_date', '<=', date('Y-m-d', strtotime('-1 month')))
            ->orderBy('id')
            ->get();

        $ar_count = 0;
        $ar_errors = [];

        foreach ($ar_payments as $payment) {
            try {
                $existing_je = DB::table('sys_journal_entries')
                    ->where('source_module', 'payment')
                    ->where('source_id', $payment->id)
                    ->exists();

                if (!$existing_je) {
                    // Get allocations
                    $allocations = DB::table('sys_payment_allocations')
                        ->where('payment_id', $payment->id)
                        ->get();

                    $allocation_data = [];
                    foreach ($allocations as $alloc) {
                        $allocation_data[] = [
                            'invoice_id' => $alloc->invoice_id,
                            'amount' => $alloc->amount
                        ];
                    }

                    $result = $ar->recordCustomerPayment([
                        'customer_id' => $payment->customer_id,
                        'bank_account_id' => $payment->bank_account_id,
                        'amount' => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'reference' => $payment->reference,
                        'allocations' => $allocation_data
                    ]);

                    if ($result['success']) {
                        $ar_count++;
                        echo "✓ AR Payment {$payment->id} posted to GL\n";
                    } else {
                        $ar_errors[] = "Payment {$payment->id}: " . $result['message'];
                    }
                }
            } catch (\Exception $e) {
                $ar_errors[] = "Payment {$payment->id}: " . $e->getMessage();
            }
        }

        // AP Payments
        $ap_payments = DB::table('sys_payments')
            ->where('payment_type', 'vendor')
            ->where('status', 'posted')
            ->where('payment_date', '<=', date('Y-m-d', strtotime('-1 month')))
            ->orderBy('id')
            ->get();

        $ap_count = 0;
        $ap_errors = [];

        foreach ($ap_payments as $payment) {
            try {
                $existing_je = DB::table('sys_journal_entries')
                    ->where('source_module', 'payment')
                    ->where('source_id', $payment->id)
                    ->exists();

                if (!$existing_je) {
                    $allocations = DB::table('sys_payment_allocations')
                        ->where('payment_id', $payment->id)
                        ->get();

                    $allocation_data = [];
                    foreach ($allocations as $alloc) {
                        $allocation_data[] = [
                            'bill_id' => $alloc->bill_id,
                            'amount' => $alloc->amount
                        ];
                    }

                    $result = $ap->recordVendorPayment([
                        'vendor_id' => $payment->vendor_id,
                        'bank_account_id' => $payment->bank_account_id,
                        'amount' => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'reference' => $payment->reference,
                        'allocations' => $allocation_data
                    ]);

                    if ($result['success']) {
                        $ap_count++;
                        echo "✓ AP Payment {$payment->id} posted to GL\n";
                    } else {
                        $ap_errors[] = "Payment {$payment->id}: " . $result['message'];
                    }
                }
            } catch (\Exception $e) {
                $ap_errors[] = "Payment {$payment->id}: " . $e->getMessage();
            }
        }

        return [
            'ar_payments' => ['success' => count($ar_errors) === 0, 'processed' => $ar_count, 'errors' => $ar_errors],
            'ap_payments' => ['success' => count($ap_errors) === 0, 'processed' => $ap_count, 'errors' => $ap_errors]
        ];
    }

    /**
     * ============================================================
     * VALIDATION: VERIFY MIGRATION ACCURACY
     * ============================================================
     */
    public function validateMigration()
    {
        $checks = [];

        // Check 1: AR totals match
        $legacy_ar = DB::table('sys_invoices')
            ->where('status', 'posted')
            ->sum('total_due');

        $gl_ar = DB::table('sys_ar_ledger')
            ->sum('amount');

        $checks['ar_reconciliation'] = [
            'legacy_total' => $legacy_ar,
            'gl_total' => $gl_ar,
            'difference' => abs($legacy_ar - $gl_ar),
            'is_reconciled' => abs($legacy_ar - $gl_ar) < 0.01
        ];

        // Check 2: AP totals match
        $legacy_ap = DB::table('sys_bills')
            ->where('status', 'posted')
            ->sum('total_due');

        $gl_ap = DB::table('sys_ap_ledger')
            ->sum('amount');

        $checks['ap_reconciliation'] = [
            'legacy_total' => $legacy_ap,
            'gl_total' => $gl_ap,
            'difference' => abs($legacy_ap - $gl_ap),
            'is_reconciled' => abs($legacy_ap - $gl_ap) < 0.01
        ];

        // Check 3: GL balance
        $gl_integrity = new AccountingControlsService();
        $integrity = $gl_integrity->validateGLIntegrity();

        $checks['gl_integrity'] = $integrity;

        return $checks;
    }

    /**
     * ============================================================
     * PHASE 2: ENABLE AUTO-POSTING FOR NEW TRANSACTIONS
     * ============================================================
     * 
     * Add hooks to existing invoice/bill creation:
     * 
     * 1. When invoice is marked "posted":
     *    $ar->postInvoiceToAR($invoice_id);
     * 
     * 2. When bill is marked "posted":
     *    $ap->postBillToAP($bill_id);
     * 
     * 3. When payment is recorded:
     *    $ar->recordCustomerPayment($data);  // or AP variant
     * 
     * These integrations should be added to existing invoice/bill
     * creation code to auto-post to GL whenever these events occur.
     */

    /**
     * ============================================================
     * INTEGRATION: AUTO-POST NEW INVOICES
     * ============================================================
     * Add this to your invoice controller after marking "posted"
     */
    public function integrateInvoicePosting($invoice_id)
    {
        try {
            $ar = new AccountsReceivableService();
            $result = $ar->postInvoiceToAR($invoice_id);

            if ($result['success']) {
                // Log successful posting
                Log::info("Invoice {$invoice_id} posted to GL (JE #{$result['journal_entry_id']})");
            } else {
                // Log error but don't fail invoice posting
                Log::error("Failed to post invoice {$invoice_id} to GL: " . $result['message']);
                // Optionally notify admin
            }
        } catch (\Exception $e) {
            Log::error("Exception posting invoice to GL: " . $e->getMessage());
        }
    }

    /**
     * ============================================================
     * DUAL REPORTING: COMPARE LEGACY vs GL
     * ============================================================
     */
    public function generateComparisonReport($date)
    {
        $legacy_report = DB::table('sys_invoices')
            ->where('status', 'posted')
            ->where('invoice_date', '<=', $date)
            ->selectRaw('SUM(total_due) as total_due')
            ->selectRaw('SUM(amount_paid) as amount_paid')
            ->selectRaw('SUM(balance_due) as balance_due')
            ->first();

        $gl_report = DB::table('sys_ar_ledger')
            ->selectRaw('SUM(CASE WHEN transaction_type = "invoice" THEN amount ELSE 0 END) as total_invoiced')
            ->selectRaw('SUM(CASE WHEN transaction_type = "payment" THEN amount ELSE 0 END) as total_paid')
            ->selectRaw('SUM(CASE WHEN transaction_type = "invoice" THEN amount WHEN transaction_type = "payment" THEN -amount ELSE 0 END) as total_outstanding')
            ->first();

        return [
            'date' => $date,
            'legacy_system' => $legacy_report,
            'gl_system' => $gl_report,
            'reconciliation' => [
                'invoiced_match' => abs($legacy_report->total_due - $gl_report->total_invoiced) < 0.01,
                'paid_match' => abs($legacy_report->amount_paid - $gl_report->total_paid) < 0.01,
                'outstanding_match' => abs($legacy_report->balance_due - $gl_report->total_outstanding) < 0.01
            ]
        ];
    }

    /**
     * ============================================================
     * ROLLBACK: UNDO MIGRATION IF NEEDED
     * ============================================================
     */
    public function rollbackMigration()
    {
        // This removes all JE entries related to invoices/bills
        // Does NOT delete invoices/bills themselves
        
        $je_ids = DB::table('sys_journal_entries')
            ->where('source_module', 'invoice')
            ->orWhere('source_module', 'bill')
            ->orWhere('source_module', 'payment')
            ->pluck('id');

        DB::table('sys_journal_items')
            ->whereIn('journal_entry_id', $je_ids)
            ->delete();

        DB::table('sys_journal_entries')
            ->whereIn('id', $je_ids)
            ->delete();

        DB::table('sys_ar_ledger')->delete();
        DB::table('sys_ap_ledger')->delete();

        echo "Migration rolled back. Deleted " . count($je_ids) . " journal entries.\n";
    }
}

/**
 * ============================================================
 * MIGRATION SCRIPT EXECUTION
 * ============================================================
 * 
 * Run this in order:
 * 
 * 1. php migrate_phase1_invoices.php
 *    - Posts all historical invoices to GL
 * 
 * 2. php migrate_phase1_bills.php
 *    - Posts all historical bills to GL
 * 
 * 3. php migrate_phase1_payments.php
 *    - Posts all historical payments to GL
 * 
 * 4. php validate_migration.php
 *    - Compares legacy totals vs GL totals
 *    - Confirms GL is balanced
 * 
 * 5. Integration
 *    - Add auto-posting hooks to invoice/bill/payment creation
 * 
 * 6. Dual reporting (optional)
 *    - Run both legacy and GL reports in parallel
 *    - Verify accuracy before cutover
 * 
 * 7. Cutover (when confident)
 *    - Switch default reports to GL-based
 *    - Archive legacy system
 */

if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $guide = new AccountingMigrationGuide();

    $command = $argv[1];

    switch ($command) {
        case 'migrate-invoices':
            echo "Migrating historical invoices...\n";
            $result = $guide->migrateHistoricalInvoices();
            echo "Migrated {$result['processed']} invoices\n";
            if (!empty($result['errors'])) {
                echo "Errors: " . json_encode($result['errors'], JSON_PRETTY_PRINT) . "\n";
            }
            break;

        case 'migrate-bills':
            echo "Migrating historical bills...\n";
            $result = $guide->migrateHistoricalBills();
            echo "Migrated {$result['processed']} bills\n";
            if (!empty($result['errors'])) {
                echo "Errors: " . json_encode($result['errors'], JSON_PRETTY_PRINT) . "\n";
            }
            break;

        case 'migrate-payments':
            echo "Migrating historical payments...\n";
            $result = $guide->migrateHistoricalPayments();
            echo "AR Payments: {$result['ar_payments']['processed']}\n";
            echo "AP Payments: {$result['ap_payments']['processed']}\n";
            break;

        case 'validate':
            echo "Validating migration...\n";
            $checks = $guide->validateMigration();
            echo json_encode($checks, JSON_PRETTY_PRINT) . "\n";
            break;

        case 'rollback':
            echo "WARNING: This will delete all GL entries from migration!\n";
            echo "Continue? (yes/no): ";
            $response = trim(fgets(STDIN));
            if ($response === 'yes') {
                $guide->rollbackMigration();
            }
            break;

        case 'full-migration':
            echo "Running full migration...\n";
            $guide->migrateHistoricalInvoices();
            $guide->migrateHistoricalBills();
            $guide->migrateHistoricalPayments();
            $checks = $guide->validateMigration();
            echo "Migration complete!\n";
            echo json_encode($checks, JSON_PRETTY_PRINT) . "\n";
            break;

        default:
            echo "Usage:\n";
            echo "  php system/lib/AccountingMigrationGuide.php migrate-invoices\n";
            echo "  php system/lib/AccountingMigrationGuide.php migrate-bills\n";
            echo "  php system/lib/AccountingMigrationGuide.php migrate-payments\n";
            echo "  php system/lib/AccountingMigrationGuide.php validate\n";
            echo "  php system/lib/AccountingMigrationGuide.php full-migration\n";
            echo "  php system/lib/AccountingMigrationGuide.php rollback\n";
    }
}
