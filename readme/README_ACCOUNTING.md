# 🎉 SNAPS BILLING FINANCE - COMPLETE ACCOUNTING SYSTEM

**Status:** ✅ **Production Ready**  
**Date:** April 27, 2026  
**Version:** 1.0  

---

## 📌 QUICK NAVIGATION

- **🚀 First Time?** → Start with [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- **📖 Full Details?** → Read [ACCOUNTING_SYSTEM_GUIDE.md](ACCOUNTING_SYSTEM_GUIDE.md)  
- **📊 Project Overview?** → See [ACCOUNTING_SYSTEM_SUMMARY.md](ACCOUNTING_SYSTEM_SUMMARY.md)
- **🔄 Have Legacy Data?** → Follow [AccountingMigrationGuide.php](../system/lib/AccountingMigrationGuide.php)

---

## 🎯 WHAT IS THIS?

A **production-grade, double-entry accounting system** for SnapsBilling Finance with:

✅ General Ledger (GL) - Single source of truth for all accounts  
✅ Accounts Receivable (AR) - Customer invoices, payments, aging  
✅ Accounts Payable (AP) - Vendor bills, payments, approval  
✅ Cash & Bank - Bank reconciliation, transfers, cash flow  
✅ Tax/VAT - ZATCA Phase 2 compliance, VAT tracking  
✅ Financial Reports - Trial Balance, P&L, Balance Sheet, Cash Flow  
✅ Audit & Controls - Period locking, RBAC, complete audit trail  
✅ Zero Data Loss - Reversal entries only, no deletion  

---

## ⚡ 90-SECOND SETUP

```bash
# 1. Deploy database schema
mysql snaps_billing_db < application/storage/accounting_system_schema.sql

# 2. Initialize chart of accounts (30+ GL accounts, tax codes, periods)
php system/lib/AccountingInitializer.php

# 3. Done! Start using the API
# POST /api/accounting/journal-entries
# GET /api/accounting/reports/trial-balance
```

---

## 📂 FILES INCLUDED

### Database
- **`application/storage/accounting_system_schema.sql`** - 15 idempotent tables with INFORMATION_SCHEMA checks

### Services (Core Logic)
- **`system/lib/JournalEntryService.php`** - GL posting engine
- **`system/lib/AccountsReceivableService.php`** - AR management
- **`system/lib/AccountsPayableService.php`** - AP management
- **`system/lib/CashBankService.php`** - Bank reconciliation
- **`system/lib/TaxVATService.php`** - ZATCA compliance
- **`system/lib/FinancialReportService.php`** - Financial reports
- **`system/lib/AccountingControlsService.php`** - Governance

### Integration
- **`system/lib/AccountingInitializer.php`** - Setup script (creates CoA, periods, tax codes)
- **`system/lib/AccountingMigrationGuide.php`** - Migrate legacy invoices/bills to GL
- **`system/models/GLAccount.php`** - 12 Eloquent models (GL, AR, AP, Bank, etc.)
- **`system/controllers/AccountingAPIController.php`** - 30+ REST API endpoints

### Documentation
- **`readme/ACCOUNTING_SYSTEM_GUIDE.md`** - Complete implementation guide (50 KB)
- **`readme/ACCOUNTING_SYSTEM_SUMMARY.md`** - Project summary & features
- **`readme/QUICK_REFERENCE.md`** - Quick reference card
- **`readme/README.md`** - This file

---

## 🏗️ ARCHITECTURE

```
REST API (30+ endpoints)
    ↓
Service Layer (7 services)
├─ JournalEntryService (core GL engine)
├─ ARService, APService, BankService
├─ TaxVATService, ReportService, ControlsService
    ↓
Eloquent Models (12 models with relationships)
    ↓
Database (15 tables with INFORMATION_SCHEMA checks)
```

### Key Design Principles

1. **Double-Entry Mandatory** - Every JE: Debit = Credit (enforced at posting)
2. **GL is Source of Truth** - All reports generated from GL only
3. **Atomic Transactions** - All multi-step ops use DB::beginTransaction()
4. **No Deletion** - Financial records use reversal entries only
5. **Fully Audited** - Complete audit trail with user/time/IP
6. **Period Locking** - Prevents posting to closed periods
7. **ZATCA Compliant** - Phase 2 ready with VAT tracking

---

## 🚀 GETTING STARTED

### Step 1: Deploy Schema
```bash
mysql -u root -padmin123 snaps_billing_db < application/storage/accounting_system_schema.sql
```

Creates 15 tables:
- `sys_gl_accounts` - Chart of Accounts
- `sys_journal_entries` / `sys_journal_items` - Journal entries
- `sys_ar_ledger` / `sys_ap_ledger` - Subledgers
- `sys_bank_accounts` / `sys_bank_reconciliations` - Bank management
- `sys_tax_codes` - Tax configuration
- `sys_audit_logs` - Audit trail
- `sys_accounting_periods` - Period management

### Step 2: Initialize
```bash
php system/lib/AccountingInitializer.php
```

Creates:
- 30+ GL accounts (Cash, AR, AP, Revenue, Expenses, etc.)
- 12 accounting periods for 2026
- 3 tax codes (ZATCA compliant)
- Links customers to AR account
- Links vendors to AP account

### Step 3: Test
```php
// Create and post a balanced journal entry
$je = new JournalEntryService();
$result = $je->createAndPostJournalEntry([
    'entry_date' => '2026-04-27',
    'lines' => [
        ['account_id' => 1000, 'debit' => 1000, 'credit' => 0],    // Cash
        ['account_id' => 4000, 'debit' => 0, 'credit' => 1000]     // Revenue
    ]
]);

// Verify GL is balanced
$integrity = new AccountingControlsService();
$check = $integrity->validateGLIntegrity();
assert($check['is_valid'] === true);
```

---

## 📊 COMMON OPERATIONS

### Post Invoice to GL
```php
$ar = new AccountsReceivableService();
$ar->postInvoiceToAR($invoice_id);
// Creates: Dr AR, Cr Revenue, Cr VAT
```

### Record Customer Payment
```php
$ar->recordCustomerPayment([
    'customer_id' => 456,
    'bank_account_id' => 1,
    'amount' => 5000.00,
    'payment_date' => '2026-04-27',
    'allocations' => [
        ['invoice_id' => 123, 'amount' => 5000.00]
    ]
]);
// Creates: Dr Bank, Cr AR
```

### Get AR Aging Report
```php
$aging = $ar->getARAgingReport();
// Returns: [customer_id => [current, 30, 60, 90] days]
```

### Generate P&L
```php
$reports = new FinancialReportService();
$pl = $reports->getProfitLoss('2026-04-01', '2026-04-30');
// Returns: [revenue, expenses, net_income] breakdown by account
```

### Reconcile Bank Statement
```php
$bank = new CashBankService();
$recon = $bank->createReconciliation([
    'bank_account_id' => 1,
    'statement_date' => '2026-04-30',
    'closing_balance' => 12000.00
]);
// Match transactions and finalize
$bank->finalizeReconciliation($recon['reconciliation_id']);
```

---

## 🔌 REST API

### Journal Entries
```
POST   /api/accounting/journal-entries          Create JE
GET    /api/accounting/journal-entries          List JE
GET    /api/accounting/journal-entries/{id}     Get JE
POST   /api/accounting/journal-entries/{id}/reverse    Reverse
```

### Accounts Receivable
```
POST   /api/accounting/ar/post-invoice          Post invoice
POST   /api/accounting/ar/record-payment        Record payment
GET    /api/accounting/ar/aging-report          Aging by customer
GET    /api/accounting/ar/summary                Total AR outstanding
```

### Financial Reports
```
GET    /api/accounting/reports/trial-balance    Trial balance
GET    /api/accounting/reports/profit-loss      P&L statement
GET    /api/accounting/reports/balance-sheet    Balance sheet
GET    /api/accounting/reports/cash-flow        Cash flow
GET    /api/accounting/reports/gl-integrity     GL validation
```

---

## 🔒 SECURITY & COMPLIANCE

✅ **Double-Entry Enforcement** - Debit must equal Credit (no exceptions)  
✅ **ACID Transactions** - All operations atomic with rollback  
✅ **Audit Trail** - User/time/IP/action logged for all changes  
✅ **Period Locking** - Prevents posting to closed periods  
✅ **Role-Based Access** - Permission checking on all operations  
✅ **No Data Deletion** - Reversal entries only, complete history preserved  
✅ **GL Integrity** - Validation ensures GL always balanced  
✅ **ZATCA Phase 2** - VAT tracking and invoice linkage ready  

---

## 📈 MIGRATION FROM LEGACY SYSTEM

If you have existing invoices/bills in the old system:

```bash
# Migrate all historical invoices
php system/lib/AccountingMigrationGuide.php migrate-invoices

# Migrate all historical bills
php system/lib/AccountingMigrationGuide.php migrate-bills

# Migrate all historical payments
php system/lib/AccountingMigrationGuide.php migrate-payments

# Validate migration accuracy
php system/lib/AccountingMigrationGuide.php validate

# Or run all at once
php system/lib/AccountingMigrationGuide.php full-migration
```

**Non-destructive:** Old system continues to work, GL runs in parallel.

---

## 📊 FINANCIAL REPORTS GENERATED

### Trial Balance
Lists all GL accounts with debit/credit balances. Validates: Debit = Credit

### Profit & Loss (Income Statement)
```
Revenue                          $100,000
- Expenses                       ($50,000)
= Net Income                      $50,000
```

### Balance Sheet
```
Assets                           $150,000
= Liabilities          $50,000 + Equity    $100,000
```

### Cash Flow Statement
```
Operating Activities            $40,000
Investing Activities           ($20,000)
Financing Activities           ($10,000)
= Net Cash Change               $10,000
```

### Account Ledger (Individual Account)
Shows running balance for any GL account with all transactions.

---

## 🛠️ TROUBLESHOOTING

### Issue: Journal Entry Not Posting
**Check:** Debit must equal Credit (to 2 decimals)
```php
$je = JournalEntry::find($id);
assert($je->debit_total === $je->credit_total);
```

### Issue: GL Accounts Not in Reports
**Check:** Accounts must be `is_active = 1`
```php
GLAccount::where('is_active', 1)->get();
```

### Issue: Period Lock Not Working
**Check:** Period must have status = 'open'
```php
AccountingPeriod::where('status', 'open')->get();
```

### Issue: AR Aging Incorrect
**Check:** Verify allocations flag
```php
ARLedger::where('is_allocated', 1)->get();
```

---

## 📞 SUPPORT

1. **Full Guide:** Read `readme/ACCOUNTING_SYSTEM_GUIDE.md`
2. **Quick Ref:** Check `readme/QUICK_REFERENCE.md`
3. **Test:** Run validation script to check GL integrity
4. **Debug:** Review `sys_audit_logs` table for change history

---

## 📚 KEY DOCUMENTATION

| File | Purpose |
|------|---------|
| `QUICK_REFERENCE.md` | 60-second quick reference (this file) |
| `ACCOUNTING_SYSTEM_GUIDE.md` | Complete implementation guide (50 KB) |
| `ACCOUNTING_SYSTEM_SUMMARY.md` | Project summary & features |
| `AccountingMigrationGuide.php` | Migrate legacy data to GL |
| `JournalEntryService.php` | Core GL posting engine |
| `FinancialReportService.php` | Report generation |
| `AccountingAPIController.php` | REST API endpoints |

---

## ✨ HIGHLIGHTS

✅ **Zero Data Loss** - Reversal entries only, complete audit trail  
✅ **Fully Balanced** - Every transaction verified Debit = Credit  
✅ **Production Ready** - ACID transactions with rollback  
✅ **ZATCA Compliant** - Phase 2 ready with full compliance  
✅ **Well Documented** - 50+ KB of guides and examples  
✅ **RESTful API** - 30+ endpoints for integration  
✅ **Fully Tested** - Validation on all operations  

---

## 🎓 NEXT STEPS

1. **Deploy:** Run schema migration
2. **Initialize:** Run initializer script
3. **Verify:** Check GL integrity
4. **Test:** Post sample transaction
5. **Migrate:** Migrate legacy data (optional)
6. **Integrate:** Add GL posting to invoice/bill creation
7. **Monitor:** Review audit logs weekly

---

## 📋 FILE CHECKLIST

After deployment, you should have:

- ✅ Database tables created (`sys_gl_accounts`, `sys_journal_entries`, etc.)
- ✅ Chart of accounts initialized (30+ accounts)
- ✅ Tax codes created (ZATCA compliant)
- ✅ Accounting periods created (12 months)
- ✅ Services deployed (`JournalEntryService`, etc.)
- ✅ Models deployed (`GLAccount`, etc.)
- ✅ API controller deployed (`AccountingAPIController`)
- ✅ Documentation available (this README + guides)

---

**🎉 Your Accounting System is Ready!**

For questions, refer to the comprehensive guide in `readme/ACCOUNTING_SYSTEM_GUIDE.md`

---

**Version:** 1.0 | **Status:** Production Ready ✅  
**ZATCA Phase 2 Compliant** | **Zero Data Loss** | **Fully Audited**  
**Last Updated:** April 27, 2026
