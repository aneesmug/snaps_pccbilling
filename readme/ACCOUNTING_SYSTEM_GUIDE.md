# PRODUCTION-GRADE DOUBLE-ENTRY ACCOUNTING SYSTEM
## Complete Implementation Guide

**Version:** 1.0  
**Last Updated:** April 27, 2026  
**Platform:** PHP/Laravel, MySQL  
**ZATCA Phase 2 Compliant** ✓

---

## 📋 TABLE OF CONTENTS

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Core Components](#core-components)
5. [Implementation Guide](#implementation-guide)
6. [API Documentation](#api-documentation)
7. [Testing & Validation](#testing--validation)
8. [Compliance](#compliance)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 OVERVIEW

This is a complete, production-grade accounting system designed for SaaS invoicing platforms. It implements strict **double-entry accounting** with ZATCA Phase 2 compliance.

### Key Features

✅ **Double-Entry Accounting** - Every transaction balanced (Debit = Credit)  
✅ **General Ledger (GL)** - Single source of truth for all accounts  
✅ **Accounts Receivable (AR)** - Customer invoices, payments, aging reports  
✅ **Accounts Payable (AP)** - Vendor bills, payments, approval workflow  
✅ **Cash & Bank Management** - Bank reconciliation, transfers, cash flow  
✅ **Tax/VAT Compliance** - ZATCA Phase 2 integration, VAT tracking  
✅ **Financial Reporting** - Trial Balance, P&L, Balance Sheet, Cash Flow  
✅ **Audit Controls** - Period locking, role-based permissions, audit logs  
✅ **No Deletion** - Reversal entries only, complete audit trail  

---

## 🏗️ ARCHITECTURE

### System Design

```
┌─────────────────────────────────────────────────────────────┐
│                      REST API Layer                         │
│              (AccountingAPIController)                      │
└────────────────────────┬────────────────────────────────────┘
                         │
         ┌───────────────┼───────────────┐
         │               │               │
    ┌────▼────┐   ┌─────▼─────┐   ┌────▼─────┐
    │   AR    │   │   AP      │   │  Reports │
    │Service  │   │ Service   │   │ Service  │
    └────┬────┘   └─────┬─────┘   └────┬─────┘
         │              │              │
         └──────────────┼──────────────┘
                        │
              ┌─────────▼────────────┐
              │ Journal Entry Service │
              │  (Core GL Engine)    │
              └─────────┬────────────┘
                        │
         ┌──────────────┼──────────────┐
         │              │              │
    ┌────▼─────┐  ┌────▼────┐  ┌─────▼────┐
    │  GL      │  │ Controls│  │   Tax    │
    │ Accounts │  │ Service │  │  Service │
    └──────────┘  └─────────┘  └──────────┘
         │              │              │
         └──────────────┼──────────────┘
                        │
                   ┌────▼────┐
                   │ Database │
                   │  MySQL   │
                   └──────────┘
```

### Service Layer

| Service | Purpose | Key Methods |
|---------|---------|-------------|
| **JournalEntryService** | Core GL posting engine | createAndPostJournalEntry, reverseJournalEntry, validateBalance |
| **AccountsReceivableService** | AR management | postInvoiceToAR, recordCustomerPayment, getARAgingReport |
| **AccountsPayableService** | AP management | postBillToAP, recordVendorPayment, getAPAgingReport |
| **CashBankService** | Bank management | recordBankTransfer, createReconciliation, getReconciliationSummary |
| **TaxVATService** | Tax compliance | calculateInvoiceVAT, getVATReport, linkZATCAInvoiceToGL |
| **FinancialReportService** | Reporting | getTrialBalance, getProfitLoss, getBalanceSheet, getCashFlow |
| **AccountingControlsService** | Governance | createPeriod, lockPeriod, logAudit, validateGLIntegrity |

---

## 📊 DATABASE SCHEMA

### Core Tables

#### `sys_gl_accounts` (Chart of Accounts)
```sql
id, code, name, type (asset|liability|equity|revenue|expense),
parent_id, is_active, normal_balance, created_at
```

#### `sys_journal_entries` (JE Headers)
```sql
id, entry_date, reference, description, source_module, source_id,
post_status (draft|posted|reversed), post_date, created_at
```

#### `sys_journal_items` (JE Detail Lines)
```sql
id, journal_entry_id, account_id, debit, credit, created_at
```

#### `sys_gl_account_balances` (Denormalized Balances)
```sql
id, account_id, debit_balance, credit_balance, net_balance, last_updated
```

#### `sys_ar_ledger` (Customer AR Transactions)
```sql
id, customer_id, journal_entry_id, invoice_id, amount,
transaction_type (invoice|payment|adjustment), posting_date, due_date, is_allocated
```

#### `sys_ap_ledger` (Vendor AP Transactions)
```sql
id, vendor_id, journal_entry_id, bill_id, amount,
transaction_type (bill|payment|adjustment), posting_date, due_date, is_allocated
```

#### `sys_bank_accounts` (Cash Accounts)
```sql
id, name, account_number, gl_cash_account_id, balance, reconciled_balance, is_active
```

#### `sys_audit_logs` (Complete Audit Trail)
```sql
id, user_id, action, table_name, record_id, old_value, new_value,
ip_address, created_at
```

---

## 🔧 CORE COMPONENTS

### 1. JournalEntryService (Posting Engine)

**Purpose:** Core double-entry accounting engine. All GL postings go through this.

**Key Validation:**
- ✓ Debit MUST equal Credit
- ✓ All accounts must be active
- ✓ Period must be open
- ✓ Transaction processing is atomic (rollback on failure)

**Usage Example:**
```php
$je_service = new JournalEntryService();

$result = $je_service->createAndPostJournalEntry([
    'entry_date' => '2026-04-27',
    'reference' => 'INV-001',
    'description' => 'Invoice posting',
    'source_module' => 'invoice',
    'source_id' => 123,
    'lines' => [
        ['account_id' => 1000, 'debit' => 1000.00, 'credit' => 0, 'description' => 'AR increase'],
        ['account_id' => 4000, 'debit' => 0, 'credit' => 1000.00, 'description' => 'Revenue'],
    ]
]);

if ($result['success']) {
    echo "JE #" . $result['journal_entry_id'] . " posted";
}
```

### 2. AccountsReceivableService

**Purpose:** Manage customer invoices and receivables.

**Key Operations:**

| Operation | Method | GL Entry |
|-----------|--------|----------|
| Post Invoice | `postInvoiceToAR($invoice_id)` | Dr AR, Cr Revenue, Cr VAT |
| Record Payment | `recordCustomerPayment($data)` | Dr Bank, Cr AR |
| Bad Debt Writeoff | `writeOffBadDebt($customer_id, $amount)` | Dr Expense, Cr AR |

**Usage Example:**
```php
$ar_service = new AccountsReceivableService();

// Post an invoice to AR
$result = $ar_service->postInvoiceToAR(123);

// Record customer payment
$result = $ar_service->recordCustomerPayment([
    'customer_id' => 456,
    'bank_account_id' => 1,
    'amount' => 5000.00,
    'payment_date' => '2026-04-27',
    'reference' => 'CHQ-001',
    'allocations' => [
        ['invoice_id' => 123, 'amount' => 5000.00]
    ]
]);

// Get aging report
$aging = $ar_service->getARAgingReport();
// Returns: [customer_id => [current => X, 30 => Y, 60 => Z, 90 => W]]
```

### 3. AccountsPayableService

**Purpose:** Manage vendor bills and payables.

**Key Operations:**

| Operation | Method | GL Entry |
|-----------|--------|----------|
| Post Bill | `postBillToAP($bill_id)` | Dr Expense, Dr VAT, Cr AP |
| Record Payment | `recordVendorPayment($data)` | Dr AP, Cr Bank |
| Bill Credit | `recordBillCredit($vendor_id, $amount)` | Dr AP, Cr Discount |

### 4. CashBankService

**Purpose:** Bank account and reconciliation management.

**Key Operations:**

| Operation | Method |
|-----------|--------|
| Bank Transfer | `recordBankTransfer($data)` |
| Start Reconciliation | `createReconciliation($data)` |
| Match Transaction | `matchTransaction($recon_id, $je_id, ...)` |
| Finalize Reconciliation | `finalizeReconciliation($recon_id)` |

**Bank Reconciliation Flow:**
```
1. Create reconciliation with statement data
2. Match GL transactions to statement lines
3. System calculates differences
4. Finalize when balanced
5. Updates reconciled_balance in bank account
```

### 5. FinancialReportService

**Purpose:** Generate reports directly from GL.

**Reports Generated:**

| Report | Method | Formula |
|--------|--------|---------|
| Trial Balance | `getTrialBalance($date)` | Lists all GL accounts with debit/credit |
| P&L | `getProfitLoss($from, $to)` | Revenue - Expenses = Net Income |
| Balance Sheet | `getBalanceSheet($date)` | Assets = Liabilities + Equity |
| Cash Flow | `getCashFlowStatement($from, $to)` | Operating + Investing + Financing |
| Account Ledger | `getAccountLedger($account_id, $from, $to)` | All transactions for an account |

### 6. TaxVATService

**Purpose:** VAT/Tax compliance and ZATCA integration.

**Key Operations:**
```php
$tax_service = new TaxVATService();

// Calculate VAT on invoice
$vat = $tax_service->calculateInvoiceVAT($invoice_data, 'VAT-15');
// Returns: {taxable_amount, vat_amount, total_with_vat, gl_account_id}

// Generate VAT report
$vat_report = $tax_service->getVATReport('2026-01-01', '2026-03-31');
// Returns: {vat_payable, vat_recoverable, net_vat_due}

// Link ZATCA data to GL
$tax_service->linkZATCAInvoiceToGL($invoice_id, $zatca_data);
```

### 7. AccountingControlsService

**Purpose:** Governance, compliance, and audit.

**Key Operations:**
```php
$controls = new AccountingControlsService();

// Create accounting period
$controls->createPeriod('2026-Q2', '2026-04-01', '2026-06-30');

// Lock period (prevents further postings)
$controls->lockPeriod($period_id);

// Check GL integrity
$integrity = $controls->validateGLIntegrity();
// Returns: {is_valid, entry_count, total_debit, total_credit, difference}

// Generate compliance report
$compliance = $controls->getComplianceReport('2026-04-27');
// Checks: GL balance, pending entries, AR/AP, period status
```

---

## 📝 IMPLEMENTATION GUIDE

### Step 1: Run Database Migration

The idempotent migration in `application/storage/accounting_system_schema.sql` is safe to run multiple times:

```bash
mysql -u root -padmin123 snaps_billing_db < accounting_system_schema.sql
```

**What It Creates:**
- All GL account tables
- Journal entry and item tables
- AR/AP ledger tables
- Bank reconciliation tables
- Tax/VAT tables
- Audit log tables

### Step 2: Initialize Chart of Accounts

```php
// Create GL accounts for your business
$accounts_data = [
    // Assets
    ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
    ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset'],
    ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset'],
    
    // Liabilities
    ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
    ['code' => '2100', 'name' => 'VAT Payable', 'type' => 'liability'],
    
    // Equity
    ['code' => '3000', 'name' => 'Retained Earnings', 'type' => 'equity'],
    
    // Revenue
    ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'revenue'],
    
    // Expenses
    ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
    ['code' => '5100', 'name' => 'Bad Debt Expense', 'type' => 'expense'],
];

foreach ($accounts_data as $acc) {
    GLAccount::create($acc);
}
```

### Step 3: Link Customers/Vendors to AR/AP Accounts

```php
// Assign AR control account to customers
DB::table('sys_customers')
    ->update(['ar_account_id' => 1100]); // Link to AR account

// Assign AP control account to vendors
DB::table('sys_vendors')
    ->update(['ap_account_id' => 2000]); // Link to AP account
```

### Step 4: Link Bank Accounts to GL

```php
DB::table('sys_bank_accounts')->insert([
    'name' => 'Main Checking',
    'account_number' => 'XXX-123456',
    'gl_cash_account_id' => 1000, // Link to Cash GL account
    'balance' => 50000.00
]);
```

### Step 5: Create Tax Codes

```php
DB::table('sys_tax_codes')->insert([
    'code' => 'VAT-15',
    'name' => 'Standard VAT (15%)',
    'rate' => 15.00,
    'tax_type' => 'both',
    'gl_payable_account_id' => 2100,
    'gl_recoverable_account_id' => 1250, // VAT Recoverable asset account
    'is_zatca_compliant' => 1
]);
```

### Step 6: Create Accounting Periods

```php
$controls = new AccountingControlsService();

$controls->createPeriod('2026-Q1', '2026-01-01', '2026-03-31');
$controls->createPeriod('2026-Q2', '2026-04-01', '2026-06-30');
```

### Step 7: Post Invoices & Bills to GL

**When Invoice is Posted:**
```php
$ar_service = new AccountsReceivableService();
$result = $ar_service->postInvoiceToAR($invoice_id);

// This creates:
// Dr Accounts Receivable (1100)    1000.00
// Cr Sales Revenue (4000)                  1000.00
```

**When Bill is Posted:**
```php
$ap_service = new AccountsPayableService();
$result = $ap_service->postBillToAP($bill_id);

// This creates:
// Dr Expense (5000)                800.00
// Dr VAT Recoverable (1250)        120.00
// Cr Accounts Payable (2000)               920.00
```

---

## 🔌 API DOCUMENTATION

### Base URL
```
POST/GET /api/accounting/[endpoint]
```

### Authentication
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Journal Entry Endpoints

#### POST /api/accounting/journal-entries
Create and post journal entry
```json
{
    "entry_date": "2026-04-27",
    "reference": "INV-001",
    "description": "Invoice posting",
    "source_module": "invoice",
    "source_id": 123,
    "lines": [
        {"account_id": 1000, "debit": 1000.00, "credit": 0},
        {"account_id": 4000, "debit": 0, "credit": 1000.00}
    ]
}
```

#### GET /api/accounting/journal-entries
List journal entries
```
?source_module=invoice
?status=posted
?from_date=2026-04-01&to_date=2026-04-30
?per_page=50
```

#### GET /api/accounting/journal-entries/{id}
Get journal entry details

#### POST /api/accounting/journal-entries/{id}/reverse
Reverse a journal entry

### AR Endpoints

#### POST /api/accounting/ar/post-invoice
```json
{"invoice_id": 123}
```

#### POST /api/accounting/ar/record-payment
```json
{
    "customer_id": 456,
    "bank_account_id": 1,
    "amount": 5000.00,
    "payment_date": "2026-04-27",
    "reference": "CHQ-001",
    "allocations": [
        {"invoice_id": 123, "amount": 5000.00}
    ]
}
```

#### GET /api/accounting/ar/aging-report
Returns aging by customer (Current/30/60/90+ days)

#### GET /api/accounting/ar/summary
Returns total AR outstanding

### Financial Reports Endpoints

#### GET /api/accounting/reports/trial-balance
```
?as_of_date=2026-04-27
```

#### GET /api/accounting/reports/profit-loss
```
?from_date=2026-04-01&to_date=2026-04-30
```

#### GET /api/accounting/reports/balance-sheet
```
?as_of_date=2026-04-27
```

#### GET /api/accounting/reports/cash-flow
```
?from_date=2026-04-01&to_date=2026-04-30
```

---

## ✅ TESTING & VALIDATION

### Test Double-Entry Posting

```php
// Test 1: Balanced JE should succeed
$result = $je_service->createAndPostJournalEntry([
    'entry_date' => '2026-04-27',
    'lines' => [
        ['account_id' => 1000, 'debit' => 500, 'credit' => 0],
        ['account_id' => 2000, 'debit' => 0, 'credit' => 500]
    ]
]);
assert($result['success'] === true);

// Test 2: Unbalanced JE should fail
$result = $je_service->createAndPostJournalEntry([
    'entry_date' => '2026-04-27',
    'lines' => [
        ['account_id' => 1000, 'debit' => 500, 'credit' => 0],
        ['account_id' => 2000, 'debit' => 0, 'credit' => 400]  // Unbalanced
    ]
]);
assert($result['success'] === false);
assert(strpos($result['message'], 'NOT balanced') !== false);
```

### Test GL Integrity

```php
$integrity = $controls->validateGLIntegrity();

assert($integrity['is_valid'] === true);
assert(abs($integrity['total_debit'] - $integrity['total_credit']) < 0.01);
```

### Test Period Locking

```php
// Create period
$period = $controls->createPeriod('2026-Q1', '2026-01-01', '2026-03-31');

// Lock it
$lock = $controls->lockPeriod($period['period_id']);
assert($lock['success'] === true);

// Attempt to post to locked period should fail
$result = $je_service->createAndPostJournalEntry([
    'entry_date' => '2026-01-15',  // Within locked period
    // ...
]);
assert($result['success'] === false);
```

### Test AR/AP Aging

```php
// Create invoice
$ar_service->postInvoiceToAR(123);

// Get aging report
$aging = $ar_service->getARAgingReport();
assert(isset($aging[$customer_id]['current']));
assert(isset($aging[$customer_id]['30']));
assert(isset($aging[$customer_id]['60']));
assert(isset($aging[$customer_id]['90']));
```

### Test Bank Reconciliation

```php
// Create reconciliation
$recon = $cash_service->createReconciliation([
    'bank_account_id' => 1,
    'statement_date' => '2026-04-30',
    'opening_balance' => 10000,
    'closing_balance' => 12000
]);

// Match transactions
$cash_service->matchTransaction($recon['reconciliation_id'], $je_id_1);
$cash_service->matchTransaction($recon['reconciliation_id'], $je_id_2);

// Get summary
$summary = $cash_service->getReconciliationSummary($recon['reconciliation_id']);
assert($summary['is_balanced'] === true);

// Finalize
$result = $cash_service->finalizeReconciliation($recon['reconciliation_id']);
assert($result['success'] === true);
```

---

## 🔒 COMPLIANCE

### ZATCA Phase 2 Integration

```php
// When invoice is submitted to ZATCA
$zatca_response = submitToZATCA($invoice_xml);

if ($zatca_response['success']) {
    // Link ZATCA data to GL
    $tax_service->linkZATCAInvoiceToGL($invoice_id, [
        'uuid' => $zatca_response['uuid'],
        'status' => 'submitted',
        'hash' => $zatca_response['invoice_hash'],
        'signature' => $zatca_response['signature'],
        'response' => $zatca_response
    ]);
}

// Validate ZATCA compliance
$compliance = $tax_service->validateZATCACompliance($invoice_id);
assert($compliance['is_compliant'] === true);
```

### VAT Compliance

```php
// Generate VAT report for filing
$vat_report = $tax_service->getVATReport('2026-01-01', '2026-03-31');

// Extract data for ZATCA VAT filing
$vat_payable = $vat_report['vat_payable'];      // Sales tax
$vat_recoverable = $vat_report['vat_recoverable'];  // Input tax
$net_vat_due = $vat_report['net_vat_due'];  // Amount due/refund
```

---

## 🐛 TROUBLESHOOTING

### Issue: Journal Entry Not Posting

**Error:** "Journal entry is NOT balanced"

**Solution:** Ensure debit and credit lines match exactly (to 2 decimal places)
```php
$totals = $je->getTotals();
// Check: $totals['debit'] === $totals['credit']
// Check: $totals['difference'] < 0.01
```

### Issue: GL Accounts Don't Appear in Reports

**Cause:** Accounts not marked as active or no journal items posted

**Solution:**
```php
// Verify account is active
$account = GLAccount::find($account_id);
assert($account->is_active === 1);

// Verify JE is posted (not draft)
$je = JournalEntry::find($je_id);
assert($je->post_status === 'posted');
```

### Issue: Period Lock Not Working

**Cause:** Posting to closed period date

**Solution:**
```php
$period = $controls->getPeriodStatus($period_id);
assert($period['can_post'] === true);  // Period must be open
```

### Issue: Reconciliation Not Balanced

**Cause:** Unmatched transactions in bank statement

**Solution:**
```php
$unmatched = $cash_service->getUnmatchedTransactions($bank_id, $date);
// Review unmatched transactions and match them:
foreach ($unmatched as $tx) {
    $cash_service->matchTransaction($recon_id, $tx->id, $tx->description);
}
```

---

## 📚 ADDITIONAL RESOURCES

- **Database Schema:** `application/storage/accounting_system_schema.sql`
- **Models:** `system/models/GLAccount.php`
- **Services:** `system/lib/JournalEntryService.php`, etc.
- **API Controller:** `system/controllers/AccountingAPIController.php`

---

## 🔐 SECURITY & BEST PRACTICES

1. **Always use transactions** - Services handle this automatically
2. **Validate user permissions** - Check `hasPermission()` before actions
3. **Audit every change** - Automatic via `logAudit()`
4. **Never delete GL records** - Use reversal entries only
5. **Lock closed periods** - Prevent accidental modifications
6. **Reconcile regularly** - Bank reconciliation at month-end minimum
7. **Run compliance checks** - `getComplianceReport()` weekly/monthly

---

## 📞 SUPPORT

For issues or questions:
1. Check audit logs: `AuditLog::where('record_id', $id)->get()`
2. Run compliance report: `$controls->getComplianceReport()`
3. Validate GL integrity: `$controls->validateGLIntegrity()`
4. Review error messages in API response

---

**End of Implementation Guide**
