# Legacy iBilling Data Export (for import into iBilling_sute)

This guide exports only the requested data from your old iBilling database into one SQL file that can be imported into this iBilling_sute setup.

## Exported Data

- Currencies -> `sys_currencies`
- Customers -> `crm_accounts`
- Companies -> `sys_companies`
- Products and Services -> `sys_items`
- Invoices -> `sys_invoices`
- Invoice Items -> `sys_invoiceitems`
- Quotes -> `sys_quotes`
- Quote Items -> `sys_quoteitems`
- Bank Accounts -> `sys_accounts`
- Transactions -> `sys_transactions`

## File Created

- Export script: `tools/export-legacy-ibilling-data.ps1`
- Export output (default): `application/storage/ibilling_legacy_export.sql`

## Step 1: Run the Export (from this workspace)

Open PowerShell in the workspace root and run:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\export-legacy-ibilling-data.ps1 \
  -OldDbName "OLD_IBILLING_DB" \
  -DbHost "127.0.0.1" \
  -DbPort 3306 \
  -DbUser "root" \
  -Password "YOUR_OLD_DB_PASSWORD"
```

If your MySQL binaries are not in XAMPP default path, add:

```powershell
-MySqlBinPath "C:\\path\\to\\mysql\\bin"
```

## Step 2: Import into iBilling_sute Database

```powershell
"C:\xampp\mysql\bin\mysql.exe" --host=127.0.0.1 --port=3306 --user=root --password=YOUR_NEW_DB_PASSWORD NEW_IBILLING_SUTE_DB < "application/storage/ibilling_legacy_export.sql"
```

## Important Notes

- Take backups of both old and new databases before running export/import.
- This is a data-only export (`--no-create-info`), so it does not alter table structure.
- The export uses complete INSERT statements (`--complete-insert`) to reduce column-order mismatch risk.
- `crm_accounts` is exported as-is because legacy schemas may not include a `type` column.
- If old and new databases have very different versions/custom columns, import may report column mismatch errors; in that case, share the error and we can provide a compatibility mapping script.
