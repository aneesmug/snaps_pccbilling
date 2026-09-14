# Database Cleanup Guide - Remove Test Data

This guide explains how to clean your iBilling database by removing all test/demo data while preserving ZATCA Sandbox settings.

## ⚠️ Important

**ZATCA Settings are SAFE** - The following settings will be preserved:
- ✓ ZATCA API keys and credentials
- ✓ Sandbox connection settings
- ✓ ZATCA configuration
- ✓ All sys_appconfig settings

## 🗑️ What Gets Cleaned

Test data that will be removed:
```
✓ Customers & Accounts (crm_accounts)
✓ Companies (sys_companies test entries)
✓ Invoices & Items (sys_invoices, sys_invoiceitems)
✓ Quotes (sys_quotes)
✓ Products (sys_items)
✓ Transactions (sys_transactions)
✓ Tickets (sys_tickets)
✓ Leads (crm_leads)
✓ Contracts (sys_contracts)
✓ Employees (employees)
✓ Purchase Orders (sys_purchase_orders)
✓ Expenses (sys_expenses)
✓ Access Logs (ib_invoice_access_log)
✓ Activity Logs (sys_activity)
✓ All other test data
```

## 📁 Configuration - SAFE (Not Cleaned)

These will be preserved:
```
✓ sys_appconfig (ZATCA settings!)
✓ Email templates
✓ Email configuration
✓ Currencies
✓ Tax rates
✓ Item categories
✓ Industries
✓ Lead sources
✓ All reference data
```

---

## 🚀 Option 1: Using PHP Script (Recommended)

### Step 1: Run the Cleanup Script
```bash
php install/cleanup-test-data.php
```

### Step 2: Output Example
```
Starting database cleanup...
================================================

Truncating test data tables...
─────────────────────────────
✓ TRUNCATED: crm_accounts
✓ TRUNCATED: sys_invoices
✓ TRUNCATED: sys_invoiceitems
✓ TRUNCATED: sys_items
✓ TRUNCATED: sys_transactions
[... more tables ...]

Verifying ZATCA settings are SAFE...
───────────────────────────────────
✓ ZATCA settings: PRESERVED (8 settings)
✓ Sandbox connection credentials: INTACT

================================================
✅ Database cleanup completed successfully!
================================================

Summary:
  - Tables truncated: 32
  - Test data removed: ✓
  - ZATCA settings preserved: ✓
  - Configuration intact: ✓
```

### Step 3: Verify
```bash
# Check remaining customer count
mysql -u root -p -e "SELECT COUNT(*) FROM ibilling.crm_accounts;"
# Should return: 0

# Verify ZATCA settings
mysql -u root -p -e "SELECT setting, value FROM ibilling.sys_appconfig WHERE setting LIKE 'zatca%';"
# Should show your ZATCA settings
```

---

## 💻 Option 2: Using SQL File (Manual)

### Step 1: Using MySQL Command Line
```bash
mysql -h localhost -u root -p database_name < install/cleanup-test-data.sql
```

### Step 2: Using phpMyAdmin
1. Go to phpMyAdmin
2. Select your database
3. Click "SQL" tab
4. Open file: `install/cleanup-test-data.sql`
5. Copy and paste contents
6. Click "Go"

### Step 3: Verify
Run these queries to verify:
```sql
-- Check if data is cleaned
SELECT 'crm_accounts' as table_name, COUNT(*) as rows FROM crm_accounts
UNION ALL
SELECT 'sys_invoices', COUNT(*) FROM sys_invoices
UNION ALL
SELECT 'sys_items', COUNT(*) FROM sys_items;

-- Check if ZATCA settings are safe
SELECT setting, LEFT(value, 50) as value 
FROM sys_appconfig 
WHERE setting LIKE 'zatca%';
```

---

## 📋 Tables Cleaned

| Table Name | Contains | Action |
|----------|----------|--------|
| crm_accounts | Test customers | TRUNCATE |
| sys_invoices | Test invoices | TRUNCATE |
| sys_invoiceitems | Invoice line items | TRUNCATE |
| sys_items | Test products | TRUNCATE |
| sys_quotes | Test quotes | TRUNCATE |
| sys_transactions | Test transactions | TRUNCATE |
| sys_tickets | Test tickets | TRUNCATE |
| crm_leads | Test leads | TRUNCATE |
| sys_contracts | Test contracts | TRUNCATE |
| employees | Test employees | TRUNCATE |
| sys_purchase_orders | Test POs | TRUNCATE |
| sys_expenses | Test expenses | TRUNCATE |
| ib_invoice_access_log | Access logs | TRUNCATE |
| sys_activity | Activity logs | TRUNCATE |
| sys_documents | Test documents | TRUNCATE |
| and more... | Test data | TRUNCATE |

---

## 🛡️ Tables NOT Cleaned (Preserved)

| Table Name | Contains | Action |
|----------|----------|--------|
| sys_appconfig | **ZATCA Settings** | ✓ SAFE |
| sys_email_templates | Email templates | ✓ SAFE |
| sys_emailconfig | Email settings | ✓ SAFE |
| sys_currencies | Currency data | ✓ SAFE |
| sys_tax | Tax rates | ✓ SAFE |
| sys_item_cats | Item categories | ✓ SAFE |
| and more... | Reference data | ✓ SAFE |

---

## ✅ Verification Checklist

After cleanup, verify:

```sql
-- 1. Verify ZATCA settings exist
SELECT COUNT(*) FROM sys_appconfig WHERE setting LIKE 'zatca%';
-- Result should be: > 0 ✓

-- 2. Verify test data removed
SELECT COUNT(*) FROM crm_accounts;
-- Result should be: 0 ✓

SELECT COUNT(*) FROM sys_invoices;
-- Result should be: 0 ✓

-- 3. Verify reference data still exists
SELECT COUNT(*) FROM sys_currencies;
-- Result should be: > 0 ✓

SELECT COUNT(*) FROM sys_tax;
-- Result should be: > 0 ✓
```

---

## 🔄 Reverting Changes

### If You Need to Restore Test Data

The cleanup only truncates test data. To restore:

1. **From Backup** (Recommended):
   ```bash
   mysql -u root -p database_name < backup_file.sql
   ```

2. **From Installation**:
   Re-run the installation wizard which will rebuild with demo data

3. **Manual Re-import**:
   Re-import the `install/primary.sql` file

---

## ⚡ Safety Features

✅ **Non-Destructive**
- Only removes test data
- Settings preserved
- ZATCA configuration intact

✅ **Reversible**
- Backup before cleanup (recommended)
- Can restore from backups
- Can reinstall if needed

✅ **Idempotent**
- Safe to run multiple times
- Won't fail on second run
- Uses IF EXISTS checks

✅ **Foreign Key Safe**
- Temporarily disables FK checks
- Re-enables after completion
- Prevents constraint violations

---

## 🧪 Test Run

To test without actually cleaning:

1. **Create a backup first**:
   ```bash
   mysqldump -u root -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Run cleanup**:
   ```bash
   php install/cleanup-test-data.php
   ```

3. **Verify results**:
   ```bash
   mysql -u root -p -e "SELECT COUNT(*) FROM database_name.crm_accounts;"
   ```

4. **If unhappy, restore**:
   ```bash
   mysql -u root -p database_name < backup_file.sql
   ```

---

## 📞 Troubleshooting

| Problem | Solution |
|---------|----------|
| "Access Denied" | Check database credentials in `/system/config.php` |
| "Table doesn't exist" | Normal - cleanup script skips non-existent tables |
| "Foreign Key Constraint" | Script handles this automatically |
| ZATCA settings missing | This shouldn't happen - verify `sys_appconfig` isn't truncated |

---

## 📝 File Locations

- **PHP Script**: `/install/cleanup-test-data.php`
- **SQL Script**: `/install/cleanup-test-data.sql`
- **This Guide**: `/install/CLEANUP_GUIDE.md`

---

## 🎯 Common Use Cases

### After Testing
```bash
php install/cleanup-test-data.php
```
Result: Fresh database for production

### Before Demo/Client Setup
```bash
php install/cleanup-test-data.php
```
Result: Clean slate with ZATCA settings preserved

### Fresh Start
```bash
php install/cleanup-test-data.php
```
Result: Remove all test data while keeping configuration

---

## ✨ Summary

- ✅ Removes all test data automatically
- ✅ Preserves ZATCA Sandbox settings
- ✅ Keeps all system configuration
- ✅ Safe and reversible
- ✅ Run anytime

**Command**: `php install/cleanup-test-data.php`

---

**Last Updated**: April 27, 2026
