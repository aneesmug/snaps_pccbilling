# Database Migration Guide

This guide explains how to apply database schema updates when upgrading your iBilling installation.

## For New Installations

When installing iBilling on a new system, all database migrations are applied **automatically** during the installation process. You don't need to do anything manually.

The installation wizard will:
1. Import the primary database schema
2. Apply all pending migrations (ZATCA Phase-2, duplicate invoice prevention, etc.)
3. Complete the setup

## For Existing Installations (Upgrading)

If you are upgrading an existing iBilling installation, follow these steps to apply the latest database migrations:

### Option 1: Using PHP Migration Scripts (Recommended)

#### Step 1: Duplicate Invoice Prevention
Run this script to add duplicate invoice number prevention:

```bash
php migrate-duplicate-invoice-prevention.php
```

#### Step 2: ZATCA Phase-2 Migration
If you're using ZATCA features, run:

```bash
php migrate-zatca-columns.php
```

### Option 2: Using SQL File Directly

If the PHP scripts don't work in your environment, you can run the SQL migrations directly:

1. **Backup your database first:**
   ```sql
   mysqldump -h localhost -u root -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Import the migration file:**
   ```bash
   mysql -h localhost -u root -p database_name < application/storage/zatca_phase2_columns.sql
   ```

3. **Verify the migrations were applied:**
   ```sql
   SHOW INDEXES FROM sys_invoices;
   ```

   You should see an index called `unique_invoicenum_type` and columns like `zatca_status`, `zatca_uuid`, etc.

### Option 3: Using phpMyAdmin

1. Go to your phpMyAdmin panel
2. Select your iBilling database
3. Click on the "SQL" tab
4. Copy and paste the contents of `application/storage/zatca_phase2_columns.sql`
5. Click "Go" to execute the SQL

## What's New

### Duplicate Invoice Prevention
- **Added:** Unique index on `(invoicenum, type)` columns
- **Purpose:** Prevents creating invoices with duplicate serial numbers within the same invoice type
- **Benefit:** Ensures data integrity and prevents accounting errors

### ZATCA Phase-2 Support
- **Added:** 13 new columns for ZATCA compliance tracking
- **Purpose:** Store ZATCA submission status, responses, and signatures
- **Benefit:** Enables full ZATCA Phase-2 e-invoicing compliance

## Migration Safety

All migrations are **idempotent** - meaning they can be run multiple times safely:

- ✅ Migrations check if changes already exist before applying them
- ✅ Existing indexes are not recreated
- ✅ Existing columns are not re-added
- ✅ Safe to run on live production servers

## Troubleshooting

### Error: "Duplicate entry for key 'unique_invoicenum_type'"

This means you have duplicate invoice numbers in your existing data. Before applying the unique index, you need to clean up duplicates:

```sql
-- Find duplicate invoice numbers
SELECT invoicenum, type, COUNT(*) as cnt 
FROM sys_invoices 
GROUP BY invoicenum, type 
HAVING cnt > 1;

-- Manually review and remove or merge duplicates before retrying the migration
```

### Column already exists error

This is normal and expected. If a column already exists, the migration scripts safely skip adding it again.

### Migration script not running

If the PHP migration scripts don't execute:

1. Check that PHP has access to the application directory
2. Ensure database credentials in `system/config.php` are correct
3. Try the SQL file option (Option 2) instead
4. Check server error logs for detailed error messages

## Verifying Migrations

After running migrations, verify they were successful:

```sql
-- Check for ZATCA columns
SHOW COLUMNS FROM sys_invoices LIKE 'zatca_%';

-- Check for duplicate prevention index
SHOW INDEXES FROM sys_invoices WHERE Key_name = 'unique_invoicenum_type';

-- Check table structure
DESCRIBE sys_invoices;
```

## Support

If you encounter migration issues:

1. Check the error messages carefully
2. Verify your database backups exist
3. Consult the troubleshooting section above
4. Contact CloudOnex support with your error messages

## File Locations

- Migration scripts: `/migrate-*.php`
- SQL migrations: `/application/storage/zatca_phase2_columns.sql`
- Installation scripts: `/install/`

---

**Last Updated:** April 2026
**Version:** 1.0
