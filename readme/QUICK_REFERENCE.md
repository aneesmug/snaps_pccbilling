# ACCOUNTING SYSTEM - QUICK REFERENCE CARD

## ⚡ 60-SECOND SETUP

```bash
# 1. Deploy schema (adds 15 tables with INFORMATION_SCHEMA checks)
mysql -u root -padmin123 snaps_billing_db < application/storage/accounting_system_schema.sql

# 2. Initialize chart of accounts (30+ default GL accounts, 12 periods, tax codes)
php system/lib/AccountingInitializer.php

# 3. Link existing data
# - sys_customers.ar_account_id = 1100 (AR control account)
# - sys_vendors.ap_account_id = 2000 (AP control account)
```

---

## 🔑 CORE CONCEPTS

### Double-Entry Accounting Rule
**EVERY transaction: Debit = Credit (always)**

Example:
```
Invoice $1,000 sales:
  Dr Accounts Receivable (1100)  $1,000
  Cr Sales Revenue (4000)                $1,000
                                  ✓ BALANCED
```

### GL is Single Source of Truth
- All reports generated from GL only
- GL updated by services, never directly
- AR/AP subledgers reconcile to GL control accounts

### No Deletion Policy
- Financial records NEVER deleted
- Use reversal entries instead
- Maintains complete audit trail

---

## 📊 COMMON TASKS

### 1. Post Invoice to GL
```php
$ar = new AccountsReceivableService();
$result = $ar->postInvoiceToAR($invoice_id);
// Creates: Dr AR, Cr Revenue, Cr VAT
```

### 2. Record Customer Payment
```php
$result = $ar->recordCustomerPayment([
    'customer_id' => 456,
    'bank_account_id' => 1,
    'amount' => 5000.00,
    'payment_date' => '2026-04-27',
    'allocations' => [['invoice_id' => 123, 'amount' => 5000.00]]
]);
// Creates: Dr Bank, Cr AR
```

### 3. Get AR Aging Report
```php
$aging = $ar->getARAgingReport();
// Returns: [customer_id => [current => X, 30 => Y, 60 => Z, 90 => W]]
```

### 4. Generate P&L Statement
```php
$reports = new FinancialReportService();
$pl = $reports->getProfitLoss('2026-04-01', '2026-04-30');
// Returns: [revenue, expenses, net_income]
```

### 5. Reconcile Bank Statement
```php
$bank = new CashBankService();

// Create reconciliation
$recon = $bank->createReconciliation([
    'bank_account_id' => 1,
    'statement_date' => '2026-04-30',
    'closing_balance' => 12000.00
]);

// Match transactions
$bank->matchTransaction($recon['reconciliation_id'], $je_id_1);

// Finalize
$bank->finalizeReconciliation($recon['reconciliation_id']);
```

---

## 🛠️ GL ACCOUNT TYPES

| Type | Example | Normal Balance |
|------|---------|-----------------|
| Asset | Cash (1000), AR (1100) | Debit (increase by debit) |
| Liability | AP (2000), VAT Payable (2100) | Credit (increase by credit) |
| Equity | Capital (3000), Retained Earnings (3100) | Credit |
| Revenue | Sales (4000) | Credit (increase by credit) |
| Expense | COGS (5000), Rent (5400) | Debit (increase by debit) |

---

## 📈 STANDARD GL ACCOUNT STRUCTURE

```
1000-1999: ASSETS
  1000: Cash
  1100: Accounts Receivable
  1200: Inventory
  1250: VAT Recoverable
  1500: Fixed Assets
  1510: Accumulated Depreciation

2000-2999: LIABILITIES
  2000: Accounts Payable
  2100: VAT Payable
  2200: Accrued Expenses
  2500: Short-term Debt

3000-3999: EQUITY
  3000: Capital Stock
  3100: Retained Earnings
  3200: Drawings

4000-4999: REVENUE
  4000: Sales Revenue
  4100: Service Revenue
  4200: Interest Income

5000-5999: EXPENSES
  5000: Cost of Goods Sold
  5100: Bad Debt Expense
  5300: Salaries & Wages
  5400: Rent
  5500: Utilities
  5700: Depreciation
  5800: Interest Expense
```

---

## 📋 JOURNAL ENTRY POSTING WORKFLOW

```
1. Create JE with lines [Dr/Cr pairs]
        ↓
2. Validate: Debit = Credit ✓
        ↓
3. Validate: All accounts active ✓
        ↓
4. Validate: Period is open ✓
        ↓
5. Begin transaction
        ↓
6. Insert JE header
        ↓
7. Insert JE items
        ↓
8. Update GL account balances
        ↓
9. Commit (or rollback on error)
        ↓
10. Return JE ID
```

---

## 🔒 PERIOD MANAGEMENT

```php
$controls = new AccountingControlsService();

// Create period
$controls->createPeriod('2026-April', '2026-04-01', '2026-04-30');

// Lock period (prevents posting)
$controls->lockPeriod($period_id);

// Close period
$controls->closePeriod($period_id);

// Status: open | locked | closed
```

**Rule:** Cannot post to locked/closed periods

---

## 🧾 FINANCIAL REPORTS

| Report | Method | Use Case |
|--------|--------|----------|
| Trial Balance | `getTrialBalance()` | Verify GL balance |
| P&L | `getProfitLoss()` | Revenue - Expenses |
| Balance Sheet | `getBalanceSheet()` | Assets = Liabilities + Equity |
| Cash Flow | `getCashFlowStatement()` | Operating/Investing/Financing |
| Account Ledger | `getAccountLedger()` | Individual account history |
| General Ledger | `getGeneralLedger()` | Full transaction list |

---

## 📱 REST API QUICK REFERENCE

### Post Invoice
```
POST /api/accounting/ar/post-invoice
{"invoice_id": 123}
```

### Record Payment
```
POST /api/accounting/ar/record-payment
{
  "customer_id": 456,
  "bank_account_id": 1,
  "amount": 5000.00,
  "allocations": [{"invoice_id": 123, "amount": 5000.00}]
}
```

### Get AR Aging
```
GET /api/accounting/ar/aging-report
```

### Get Trial Balance
```
GET /api/accounting/reports/trial-balance?as_of_date=2026-04-27
```

### Create JE
```
POST /api/accounting/journal-entries
{
  "entry_date": "2026-04-27",
  "lines": [
    {"account_id": 1000, "debit": 500, "credit": 0},
    {"account_id": 2000, "debit": 0, "credit": 500}
  ]
}
```

---

## ❌ COMMON MISTAKES

❌ **Don't:** Post unbalanced JE  
✅ **Do:** Validate Debit = Credit before posting

❌ **Don't:** Delete GL records  
✅ **Do:** Create reversal entry instead

❌ **Don't:** Update GL balance directly  
✅ **Do:** Post JE (service updates balance)

❌ **Don't:** Post to locked period  
✅ **Do:** Check period is open first

❌ **Don't:** Use old AR/AP ledger balances  
✅ **Do:** Recalculate from JE items each time

---

## 🐛 TROUBLESHOOTING

### GL Not Balanced
```php
$integrity = $controls->validateGLIntegrity();
// Check: total_debit === total_credit (within 0.01)
```

### AR Balance Wrong
```php
$summary = $ar->getARSummary();
// Verify allocations added up correctly
```

### Invoice Not Posting
```php
// Check 1: Is GL account active?
$account = GLAccount::find($account_id);
assert($account->is_active === 1);

// Check 2: Is period open?
$period = $controls->getPeriodStatus($period_id);
assert($period['status'] === 'open');

// Check 3: Is JE balanced?
$je = JournalEntry::find($je_id);
$totals = $je->getTotals();
assert($totals['debit'] === $totals['credit']);
```

---

## 🔐 AUDIT & COMPLIANCE

### View Audit Log
```php
$logs = AuditLog::where('record_id', $id)->get();
// Shows: user, action, old_value, new_value, timestamp, ip
```

### Compliance Report
```php
$compliance = $controls->getComplianceReport('2026-04-27');
// Checks: GL balance, pending entries, AR/AP, period status
```

### GL Integrity Check
```php
$integrity = $controls->validateGLIntegrity();
// Returns: is_valid, entry_count, total_debit, total_credit, difference
```

---

## 📚 FILES & LOCATIONS

| File | Location | Purpose |
|------|----------|---------|
| Schema | `application/storage/accounting_system_schema.sql` | Database migration |
| Services | `system/lib/` | Business logic |
| Models | `system/models/GLAccount.php` | Eloquent ORM |
| API | `system/controllers/AccountingAPIController.php` | REST endpoints |
| Init | `system/lib/AccountingInitializer.php` | Setup script |
| Guide | `readme/ACCOUNTING_SYSTEM_GUIDE.md` | Full documentation |

---

## ✅ CHECKLIST: NEW SETUP

- [ ] Run schema migration
- [ ] Run initializer script
- [ ] Link customers to AR account
- [ ] Link vendors to AP account
- [ ] Link bank accounts to cash GL account
- [ ] Create accounting periods
- [ ] Verify GL integrity (should show 0 balance)
- [ ] Test: Post sample invoice
- [ ] Test: Record sample payment
- [ ] Test: Run trial balance
- [ ] Review audit logs

---

## 🚀 GETTING STARTED

1. **Read:** `readme/ACCOUNTING_SYSTEM_GUIDE.md` (complete guide)
2. **Deploy:** Run schema migration
3. **Initialize:** Run initializer script
4. **Verify:** Check GL integrity
5. **Integrate:** Link existing invoices/bills
6. **Test:** Post sample transaction
7. **Monitor:** Review audit logs

---

**Version:** 1.0 | **Status:** Production Ready ✅  
**ZATCA Phase 2 Compliant** | **Zero Data Loss** | **Fully Audited**
