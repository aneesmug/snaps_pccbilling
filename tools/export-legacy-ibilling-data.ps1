param(
    [Parameter(Mandatory = $true)]
    [string]$OldDbName,

    [string]$DbHost = "127.0.0.1",
    [int]$DbPort = 3306,
    [string]$DbUser = "root",
    [string]$Password = "",
    [string]$MySqlBinPath = "C:\\xampp\\mysql\\bin",
    [string]$OutputFile = "application/storage/ibilling_legacy_export.sql"
)

$ErrorActionPreference = "Stop"

$workspaceRoot = Split-Path -Parent $PSScriptRoot
$outputPath = if ([System.IO.Path]::IsPathRooted($OutputFile)) {
    $OutputFile
} else {
    Join-Path $workspaceRoot $OutputFile
}

$dumpExe = Join-Path $MySqlBinPath "mysqldump.exe"
$mysqlExe = Join-Path $MySqlBinPath "mysql.exe"

if (-not (Test-Path $dumpExe)) {
    throw "mysqldump.exe not found at: $dumpExe"
}

if (-not (Test-Path $mysqlExe)) {
    throw "mysql.exe not found at: $mysqlExe"
}

$outputDir = Split-Path -Parent $outputPath
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

$tablesToExport = @(
    @{ Name = "sys_currencies";  Where = $null },
    @{ Name = "crm_accounts";    Where = $null },
    @{ Name = "sys_companies";   Where = $null },
    @{ Name = "sys_items";       Where = $null },
    @{ Name = "sys_invoices";    Where = $null },
    @{ Name = "sys_invoiceitems";Where = $null },
    @{ Name = "sys_quotes";      Where = $null },
    @{ Name = "sys_quoteitems";  Where = $null },
    @{ Name = "sys_accounts";    Where = $null },
    @{ Name = "sys_transactions";Where = $null }
)

$header = @(
    "-- iBilling legacy data export for migration to iBilling_sute",
    "-- Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')",
    "SET NAMES utf8mb4;",
    "SET FOREIGN_KEY_CHECKS = 0;",
    "SET UNIQUE_CHECKS = 0;",
    ""
)

Set-Content -Path $outputPath -Value $header -Encoding UTF8

foreach ($entry in $tablesToExport) {
    $tableName = $entry.Name
    $whereClause = $entry.Where

    Write-Host "Exporting table: $tableName"

    $args = @(
        "--host=$DbHost",
        "--port=$DbPort",
        "--user=$DbUser",
        "--default-character-set=utf8mb4",
        "--single-transaction",
        "--quick",
        "--skip-triggers",
        "--no-create-info",
        "--complete-insert",
        "--skip-comments",
        "--skip-add-locks",
        "--skip-lock-tables"
    )

    if (-not [string]::IsNullOrWhiteSpace($Password)) {
        $args += "--password=$Password"
    }

    if (-not [string]::IsNullOrWhiteSpace($whereClause)) {
        $args += "--where=$whereClause"
    }

    $args += $OldDbName
    $args += $tableName

    $tableDump = & $dumpExe @args 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to export $tableName. mysqldump output:`n$tableDump"
    }

    Add-Content -Path $outputPath -Value "-- ----------------------------------------" -Encoding UTF8
    Add-Content -Path $outputPath -Value "-- Table: $tableName" -Encoding UTF8
    if (-not [string]::IsNullOrWhiteSpace($whereClause)) {
        Add-Content -Path $outputPath -Value "-- Filter: $whereClause" -Encoding UTF8
    }
    Add-Content -Path $outputPath -Value "-- ----------------------------------------" -Encoding UTF8
    Add-Content -Path $outputPath -Value $tableDump -Encoding UTF8
    Add-Content -Path $outputPath -Value "" -Encoding UTF8
}

$footer = @(
    "SET UNIQUE_CHECKS = 1;",
    "SET FOREIGN_KEY_CHECKS = 1;",
    ""
)

Add-Content -Path $outputPath -Value $footer -Encoding UTF8

Write-Host ""
Write-Host "Export completed successfully."
Write-Host "Output file: $outputPath"
Write-Host ""
Write-Host "Import into iBilling_sute target database with:"
Write-Host "\"$mysqlExe\" --host=$DbHost --port=$DbPort --user=$DbUser --password=*** TARGET_DB_NAME < \"$outputPath\""
