# iBilling Installation Process - Updated Features

## What's New in the Installation

Starting from this version, the iBilling installation process has been enhanced to automatically apply all necessary database migrations.

## Installation Flow

```
1. Welcome Page (index.php)
   ↓
2. Connection Info (step2.php)
   ↓
3. Database Configuration (step3.php)
   ↓
4. Create Config File (ajax_c.php)
   ↓
5. Import Primary Schema (db_import.php)
   ↓
6. Apply Database Migrations (db_import.php)
   ├─ ZATCA Phase-2 Migration
   ├─ Duplicate Invoice Prevention
   └─ [Additional migrations as needed]
   ↓
7. Profile Setup (profile.php)
   ↓
8. Installation Complete
```

## Key Components

### Installation Directory (`/install/`)

- **index.php** - Welcome page
- **step2.php** - Connection settings
- **step3.php** - Database configuration form
- **ajax_c.php** - Creates system/config.php
- **db_import.php** - **UPDATED** - Now includes migration runner
- **primary.sql** - Base database schema
- **base.php** - Installation helper functions
- **CloudOnexInstaller.php** - UI components
- **post_profile.php** - Profile creation
- **profile.php** - Profile setup page

### Updated File: db_import.php

This file now includes two new functions:

#### `runDatabaseMigrations()`
Orchestrates all pending database migrations

#### `applyZatcaMigration()`
Applies ZATCA Phase-2 columns:
- zatca_status
- zatca_uuid
- zatca_invoice_type
- zatca_last_submit_at
- zatca_last_response
- zatca_submission_http_code
- zatca_submission_response
- zatca_hash
- zatca_invoice_hash
- zatca_signature
- zatca_public_key
- zatca_ca_signature
- zatca_qr_signature

#### `applyDuplicateInvoicePreventionMigration()`
Applies duplicate invoice prevention:
- Creates unique index on (invoicenum, type)
- Prevents duplicate invoice serial numbers within same type

### Migration Safety Features

✅ **Idempotent Migrations**
- All migrations check if they already exist
- Safe to run multiple times
- Won't fail if already applied

✅ **INFORMATION_SCHEMA Checks**
- Uses MySQL INFORMATION_SCHEMA for cross-version compatibility
- Works with both mysqli and PDO connections
- Supports both InnoDB and MyISAM engines

✅ **Error Handling**
- Migration errors are logged but don't stop installation
- Existing installations continue to work if migrations skip
- Clear error messages for troubleshooting

## Database Architecture

### Primary Schema (primary.sql)
Contains all base tables and structure for a fresh installation.

### Migrations (db_import.php)
Applied AFTER primary schema import to add new features without breaking existing installations.

### Storage Location
Optional: `/application/storage/zatca_phase2_columns.sql`
For manual upgrades on existing installations.

## For System Administrators

### Installation on Multiple Servers

1. **First Server Installation:**
   - Run the standard installation wizard
   - All migrations are automatically applied
   - No additional steps needed

2. **Subsequent Server Installations:**
   - Use the same installation process
   - All migrations run automatically
   - Consistent across all servers

### Upgrading Existing Installations

See [DATABASE_MIGRATIONS_GUIDE.md](../readme/DATABASE_MIGRATIONS_GUIDE.md) for detailed upgrade instructions.

## Developer Notes

### Adding New Migrations

To add new database migrations in future versions:

1. **Create migration function** in `db_import.php`:
   ```php
   function applyNewFeatureMigration($c_mysqli, $c_pdo, $mysqli, $pdo) {
       // Migration logic here
   }
   ```

2. **Register migration** in `runDatabaseMigrations()`:
   ```php
   applyNewFeatureMigration($c_mysqli, $c_pdo, $mysqli, $pdo);
   ```

3. **Add to SQL file** in `application/storage/zatca_phase2_columns.sql`

4. **Document** in this file and migration guide

### Testing Migrations

```bash
# Test fresh installation
php install/index.php

# Test existing installation upgrade
php migrate-duplicate-invoice-prevention.php
php migrate-zatca-columns.php
```

## Files Modified

- ✅ `/install/db_import.php` - Added migration runner and functions
- ✅ `/migrate-duplicate-invoice-prevention.php` - New migration script
- ✅ `/application/storage/zatca_phase2_columns.sql` - New SQL migrations file
- ✅ `/readme/DATABASE_MIGRATIONS_GUIDE.md` - New upgrade documentation

## Version History

**v9.5.2 and later:**
- Automatic database migrations during installation
- Duplicate invoice prevention
- ZATCA Phase-2 compliance

**v9.5.1 and earlier:**
- Static primary.sql schema
- No automatic migrations

---

**Installation Date:** April 2026
**Last Updated:** April 2026
