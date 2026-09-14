<?php
/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/
_auth();
$ui->assign('_title', $_L['Utilities'] . '- ' . $config['CompanyName']);
$ui->assign('selected_navigation', 'util');
$action = $routes['1'];
$user = authenticate_admin();

if (!has_access($user->roleid, 'utilities')) {
    permissionDenied();
}

function utilLegacyImportTables()
{
    return [
        'sys_currencies',
        'crm_accounts',
        'sys_companies',
        'sys_items',
        'sys_invoices',
        'sys_invoiceitems',
        'sys_quotes',
        'sys_quoteitems',
        'sys_accounts',
        'sys_transactions',
    ];
}

function utilLegacyDuplicateSignatureColumns()
{
    return [
        'sys_currencies' => [
            ['iso_code'],
            ['cname', 'symbol'],
        ],
        'crm_accounts' => [
            ['email'],
            ['account', 'phone'],
            ['account', 'company'],
        ],
        'sys_companies' => [
            ['company_name'],
            ['email', 'company_name'],
        ],
        'sys_items' => [
            ['item_number'],
            ['name', 'type'],
        ],
        'sys_invoices' => [
            ['id'],
            ['invoicenum', 'cn', 'type'],
        ],
        'sys_invoiceitems' => [
            ['invoiceid', 'description', 'qty', 'amount', 'itemcode'],
        ],
        'sys_quotes' => [
            ['vtoken'],
            ['subject', 'account', 'total', 'datecreated'],
        ],
        'sys_quoteitems' => [
            ['qid', 'description', 'qty', 'amount', 'itemcode'],
        ],
        'sys_accounts' => [
            ['account'],
            ['account_number'],
        ],
        'sys_transactions' => [
            ['ref', 'date', 'amount', 'account'],
            ['account', 'date', 'amount', 'description', 'type'],
        ],
    ];
}

function utilLegacyBuildRowSignature($tableName, $rowData)
{
    $signatureMap = utilLegacyDuplicateSignatureColumns();
    if (!isset($signatureMap[$tableName])) {
        return null;
    }

    $normalizeValue = function ($value) {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return mb_strtolower($value, 'UTF-8');
    };

    foreach ($signatureMap[$tableName] as $candidateColumns) {
        $parts = [];
        $isValid = true;

        foreach ($candidateColumns as $columnName) {
            $normalized = $normalizeValue($rowData[$columnName] ?? null);
            if ($normalized === null) {
                $isValid = false;
                break;
            }
            $parts[] = $columnName . '=' . $normalized;
        }

        if ($isValid) {
            return implode('|', $parts);
        }
    }

    return null;
}

function utilLegacySanitizeStatement($statement)
{
    $statement = str_replace(
        '<span class="redactor-invisible-space">',
        '',
        $statement
    );
    $statement = str_replace('</span>', '', $statement);

    return $statement;
}

function utilLegacyAnalyzeSqlEncoding($sqlFilePath)
{
    $result = [
        'question_groups' => 0,
        'arabic_chars' => 0,
        'replacement_chars' => 0,
        'utf8_invalid_lines' => 0,
        'scanned_lines' => 0,
        'has_risk' => false,
        'risk_reason' => '',
    ];

    $handle = @fopen($sqlFilePath, 'r');
    if (!$handle) {
        $result['has_risk'] = true;
        $result['risk_reason'] = 'Unable to read uploaded SQL file for UTF-8 safety scan.';
        return $result;
    }

    while (($line = fgets($handle)) !== false) {
        $result['scanned_lines']++;

        $result['question_groups'] += preg_match_all('/\?{2,}/', $line, $qMatches);
        $result['arabic_chars'] += preg_match_all('/[\x{0600}-\x{06FF}]/u', $line, $aMatches);
        $result['replacement_chars'] += substr_count($line, "\xEF\xBF\xBD");

        if (!mb_check_encoding($line, 'UTF-8')) {
            $result['utf8_invalid_lines']++;
        }
    }

    fclose($handle);

    $highQuestionMarks = $result['question_groups'] >= 10;
    $noArabicDetected = $result['arabic_chars'] === 0;
    $invalidUtf8Detected = $result['utf8_invalid_lines'] > 0;
    $replacementDetected = $result['replacement_chars'] > 0;

    if ($invalidUtf8Detected) {
        $result['has_risk'] = true;
        $result['risk_reason'] =
            'Invalid UTF-8 byte sequences found in SQL file (' .
            $result['utf8_invalid_lines'] .
            ' lines).';
    } elseif ($replacementDetected) {
        $result['has_risk'] = true;
        $result['risk_reason'] =
            'UTF-8 replacement characters were found; source text may already be corrupted.';
    } elseif ($highQuestionMarks && $noArabicDetected) {
        $result['has_risk'] = true;
        $result['risk_reason'] =
            'Many multi-question-mark patterns were found but no Arabic characters were detected.';
    }

    return $result;
}

function utilLegacyResolveSelectedTables($selectedTables)
{
    if (!is_array($selectedTables) && isset($_POST['import_tables'])) {
        $selectedTables = $_POST['import_tables'];
    }

    if (is_string($selectedTables) && trim($selectedTables) !== '') {
        $selectedTables = [trim($selectedTables)];
    }

    if (!is_array($selectedTables)) {
        $selectedTables = [];
    }

    return array_values(
        array_intersect(utilLegacyImportTables(), $selectedTables)
    );
}

function utilLegacySchemaCheckSqlFile($sqlFilePath, array $selectedTables)
{
    // Keep SQL errors non-fatal for this diagnostic/import flow.
    mysqli_report(MYSQLI_REPORT_OFF);

    $result = [
        'overall' => 'FAIL',
        'matched_statements' => 0,
        'parse_errors' => 0,
        'missing_target_tables' => [],
        'table_details' => [],
    ];

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_errno) {
        $result['parse_errors'] = 1;
        $result['missing_target_tables'][] = 'DB connection failed: ' . $mysqli->connect_error;
        return $result;
    }

    $mysqli->set_charset('utf8mb4');

    $tableSchemas = [];
    foreach ($selectedTables as $tableName) {
        $tableSchemas[$tableName] = [];
        $schemaQuery = "
            SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '" . $mysqli->real_escape_string($tableName) . "'
            ORDER BY ORDINAL_POSITION
        ";

        $schemaResult = $mysqli->query($schemaQuery);
        if ($schemaResult) {
            while ($column = $schemaResult->fetch_assoc()) {
                $tableSchemas[$tableName][$column['COLUMN_NAME']] = $column;
            }
            $schemaResult->free();
        }

        if (empty($tableSchemas[$tableName])) {
            $result['missing_target_tables'][] = $tableName;
        }

        $result['table_details'][$tableName] = [
            'statements' => 0,
            'unknown_legacy_columns' => 0,
            'required_new_columns' => 0,
            'unknown_samples' => [],
            'required_samples' => [],
        ];
    }

    $allowedTableMap = array_fill_keys($selectedTables, true);
    $handle = fopen($sqlFilePath, 'r');
    if (!$handle) {
        $mysqli->close();
        $result['parse_errors'] = 1;
        return $result;
    }

    $collectingStatement = false;
    $statement = '';
    $statementTable = '';

    $parseInsertStatement = function ($statement) {
        $trimmed = trim($statement);
        if (!preg_match(
            '/^(INSERT|REPLACE)\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*VALUES\s*(.+);$/is',
            $trimmed,
            $matches
        )) {
            return null;
        }

        return [
            'table' => $matches[2],
            'columns' => array_map(function ($column) {
                return trim($column, " `\r\n\t");
            }, explode(',', $matches[3])),
        ];
    };

    $processStatement = function ($statement, $tableName) use (&$result, $parseInsertStatement, $tableSchemas) {
        $statement = utilLegacySanitizeStatement($statement);
        $parsed = $parseInsertStatement($statement);

        if (!$parsed) {
            $result['parse_errors']++;
            return;
        }

        $targetSchema = $tableSchemas[$tableName] ?? [];
        if (empty($targetSchema)) {
            return;
        }

        $result['matched_statements']++;
        $result['table_details'][$tableName]['statements']++;

        $sourceColumns = $parsed['columns'];
        $sourceMap = array_fill_keys($sourceColumns, true);

        foreach ($sourceColumns as $columnName) {
            if (!isset($targetSchema[$columnName])) {
                $result['table_details'][$tableName]['unknown_legacy_columns']++;
                if (count($result['table_details'][$tableName]['unknown_samples']) < 5) {
                    $result['table_details'][$tableName]['unknown_samples'][] = $columnName;
                }
            }
        }

        foreach ($targetSchema as $columnName => $columnMeta) {
            if (isset($sourceMap[$columnName])) {
                continue;
            }

            if (stripos($columnMeta['EXTRA'] ?? '', 'auto_increment') !== false) {
                continue;
            }

            if (($columnMeta['IS_NULLABLE'] ?? 'YES') === 'NO' && $columnMeta['COLUMN_DEFAULT'] === null) {
                $result['table_details'][$tableName]['required_new_columns']++;
                if (count($result['table_details'][$tableName]['required_samples']) < 5) {
                    $result['table_details'][$tableName]['required_samples'][] = $columnName;
                }
            }
        }
    };

    while (($line = fgets($handle)) !== false) {
        if (!$collectingStatement) {
            if (preg_match('/^\s*(INSERT|REPLACE)\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i', $line, $matches)) {
                $tableName = $matches[2];
                if (isset($allowedTableMap[$tableName])) {
                    $collectingStatement = true;
                    $statementTable = $tableName;
                    $statement = $line;

                    if (preg_match('/;\s*$/', $line)) {
                        $processStatement($statement, $statementTable);
                        $collectingStatement = false;
                        $statement = '';
                        $statementTable = '';
                    }
                }
            }
            continue;
        }

        $statement .= $line;
        if (preg_match('/;\s*$/', $line)) {
            $processStatement($statement, $statementTable);
            $collectingStatement = false;
            $statement = '';
            $statementTable = '';
        }
    }

    fclose($handle);
    $mysqli->close();

    // Strict rule: every selected table must have at least one INSERT/REPLACE statement
    $result['no_inserts_tables'] = [];
    foreach ($selectedTables as $tableName) {
        $stmtCount = $result['table_details'][$tableName]['statements'] ?? 0;
        if ($stmtCount === 0) {
            $result['no_inserts_tables'][] = $tableName;
        }
    }

    if (
        empty($result['missing_target_tables']) &&
        $result['parse_errors'] === 0 &&
        $result['matched_statements'] > 0
    ) {
        $result['overall'] = 'PASS';
    }

    return $result;
}

function utilLegacyBuildCompactStatus(array $summary, $dryRun = false, $encodingCheck = null)
{
    $executed = (int) ($summary['executed'] ?? 0);
    $failed = (int) ($summary['failed'] ?? 0);
    $errors = is_array($summary['errors'] ?? null) ? count($summary['errors']) : 0;
    $selectedTables = is_array($summary['selected_tables'] ?? null)
        ? count($summary['selected_tables'])
        : 0;

    $checks = [
        [
            'label' => 'Statement errors',
            'ok' => $failed === 0 && $errors === 0,
            'detail' => $failed . ' failed / ' . $errors . ' error lines',
        ],
        [
            'label' => 'Target table selection',
            'ok' => $selectedTables > 0,
            'detail' => $selectedTables . ' selected tables',
        ],
        [
            'label' => $dryRun ? 'Planned row volume' : 'Imported row volume',
            'ok' => $executed > 0,
            'detail' => $executed . ' rows',
        ],
        [
            'label' => 'Duplicate handling',
            'ok' => isset($summary['duplicates_skipped']),
            'detail' => (int) ($summary['duplicates_skipped'] ?? 0) . ' duplicates skipped',
        ],
    ];

    if (is_array($encodingCheck)) {
        $checks[] = [
            'label' => 'UTF-8 safety',  
            'ok' => !($encodingCheck['has_risk'] ?? false),
            'detail' =>
                'lines=' .
                (int) ($encodingCheck['scanned_lines'] ?? 0) .
                ', arabic=' .
                (int) ($encodingCheck['arabic_chars'] ?? 0) .
                ', ???=' .
                (int) ($encodingCheck['question_groups'] ?? 0),
        ];
    }

    $allPassed = true;
    foreach ($checks as $check) {
        if (!$check['ok']) {
            $allPassed = false;
            break;
        }
    }

    return [
        'overall' => $allPassed ? 'PASS' : 'FAIL',
        'dry_run' => (bool) $dryRun,
        'checks' => $checks,
    ];
}

function utilLegacyPostImportChecklistSql(array $summary = [], $dryRun = false)
{
    $expectedMap = is_array($summary['tables'] ?? null) ? $summary['tables'] : [];

    $lines = [];
    $lines[] = '-- Post-Import Verification Checklist';
    $lines[] = '-- Run this in the target DB after legacy import.';
    $lines[] = 'USE ' . DB_NAME . ';';
    $lines[] = '';
    $lines[] = '-- 1) Row counts by migration table';
    $lines[] = "SELECT 'sys_currencies' AS table_name, COUNT(*) AS row_count FROM sys_currencies";
    $lines[] = "UNION ALL SELECT 'crm_accounts', COUNT(*) FROM crm_accounts";
    $lines[] = "UNION ALL SELECT 'sys_companies', COUNT(*) FROM sys_companies";
    $lines[] = "UNION ALL SELECT 'sys_items', COUNT(*) FROM sys_items";
    $lines[] = "UNION ALL SELECT 'sys_invoices', COUNT(*) FROM sys_invoices";
    $lines[] = "UNION ALL SELECT 'sys_invoiceitems', COUNT(*) FROM sys_invoiceitems";
    $lines[] = "UNION ALL SELECT 'sys_quotes', COUNT(*) FROM sys_quotes";
    $lines[] = "UNION ALL SELECT 'sys_quoteitems', COUNT(*) FROM sys_quoteitems";
    $lines[] = "UNION ALL SELECT 'sys_accounts', COUNT(*) FROM sys_accounts";
    $lines[] = "UNION ALL SELECT 'sys_transactions', COUNT(*) FROM sys_transactions;";
    $lines[] = '';
    $lines[] = '-- 2) Expected rows from latest ' . ($dryRun ? 'dry run' : 'import') . ' summary';

    if (!empty($expectedMap)) {
        foreach ($expectedMap as $table => $count) {
            $lines[] = '-- ' . $table . ': +' . (int) $count;
        }
    } else {
        $lines[] = '-- No table-level summary available yet.';
    }

    $lines[] = '';
    $lines[] = '-- 3) Duplicate checks (should return 0 rows)';
    $lines[] = "SELECT invoicenum, cn, type, COUNT(*) AS cnt";
    $lines[] = 'FROM sys_invoices';
    $lines[] = "WHERE invoicenum IS NOT NULL AND invoicenum <> ''";
    $lines[] = 'GROUP BY invoicenum, cn, type';
    $lines[] = 'HAVING COUNT(*) > 1;';
    $lines[] = '';
    $lines[] = "SELECT iso_code, COUNT(*) AS cnt";
    $lines[] = 'FROM sys_currencies';
    $lines[] = "WHERE iso_code IS NOT NULL AND iso_code <> ''";
    $lines[] = 'GROUP BY iso_code';
    $lines[] = 'HAVING COUNT(*) > 1;';
    $lines[] = '';
    $lines[] = '-- 4) Orphan checks (should return 0 rows)';
    $lines[] = 'SELECT ii.id, ii.invoiceid';
    $lines[] = 'FROM sys_invoiceitems ii';
    $lines[] = 'LEFT JOIN sys_invoices i ON i.id = ii.invoiceid';
    $lines[] = 'WHERE ii.invoiceid IS NOT NULL AND ii.invoiceid <> 0 AND i.id IS NULL';
    $lines[] = 'LIMIT 50;';
    $lines[] = '';
    $lines[] = 'SELECT qi.id, qi.qid';
    $lines[] = 'FROM sys_quoteitems qi';
    $lines[] = 'LEFT JOIN sys_quotes q ON q.id = qi.qid';
    $lines[] = 'WHERE qi.qid IS NOT NULL AND qi.qid <> 0 AND q.id IS NULL';
    $lines[] = 'LIMIT 50;';
    $lines[] = '';
    $lines[] = '-- 5) Financial consistency spot checks';
    $lines[] = 'SELECT i.id, i.total AS invoice_total, IFNULL(SUM(ii.total),0) AS items_total,';
    $lines[] = '       ROUND(i.total - IFNULL(SUM(ii.total),0), 2) AS diff';
    $lines[] = 'FROM sys_invoices i';
    $lines[] = 'LEFT JOIN sys_invoiceitems ii ON ii.invoiceid = i.id';
    $lines[] = 'GROUP BY i.id, i.total';
    $lines[] = 'HAVING ABS(ROUND(i.total - IFNULL(SUM(ii.total),0), 2)) > 0.01';
    $lines[] = 'LIMIT 50;';
    $lines[] = '';
    $lines[] = 'SELECT q.id, q.total AS quote_total, IFNULL(SUM(qi.total),0) AS items_total,';
    $lines[] = '       ROUND(q.total - IFNULL(SUM(qi.total),0), 2) AS diff';
    $lines[] = 'FROM sys_quotes q';
    $lines[] = 'LEFT JOIN sys_quoteitems qi ON qi.qid = q.id';
    $lines[] = 'GROUP BY q.id, q.total';
    $lines[] = 'HAVING ABS(ROUND(q.total - IFNULL(SUM(qi.total),0), 2)) > 0.01';
    $lines[] = 'LIMIT 50;';
    $lines[] = '';
    $lines[] = '-- 6) Sample rows for manual integrity review';
    $lines[] = 'SELECT id, account, email, phone, created_at FROM crm_accounts ORDER BY id DESC LIMIT 20;';
    $lines[] = 'SELECT id, invoicenum, account, date, duedate, total, status FROM sys_invoices ORDER BY id DESC LIMIT 20;';
    $lines[] = 'SELECT id, invoiceid, description, qty, amount, total FROM sys_invoiceitems ORDER BY id DESC LIMIT 20;';
    $lines[] = 'SELECT id, subject, account, total, datecreated FROM sys_quotes ORDER BY id DESC LIMIT 20;';
    $lines[] = 'SELECT id, account, type, date, amount, description, ref FROM sys_transactions ORDER BY id DESC LIMIT 20;';

    return implode(PHP_EOL, $lines);
}

function utilImportLegacySqlDump(
    $sqlFilePath,
    $truncateExisting = true,
    $selectedTables = [],
    $dryRun = false,
    $skipDuplicates = true
)
{
    // Keep SQL errors non-fatal for this diagnostic/import flow.
    mysqli_report(MYSQLI_REPORT_OFF);

    $allLegacyTables = utilLegacyImportTables();
    if (empty($selectedTables)) {
        $selectedTables = $allLegacyTables;
    }

    $selectedTables = array_values(
        array_intersect($allLegacyTables, $selectedTables)
    );

    $result = [
        'executed' => 0,
        'skipped' => 0,
        'failed' => 0,
        'errors' => [],
        'tables' => [],
        'truncated_tables' => [],
        'dry_run' => $dryRun,
        'selected_tables' => $selectedTables,
        'duplicates_skipped' => 0,
        'duplicate_tables' => [],
    ];

    $allowedTables = $selectedTables;
    $allowedTableMap = array_fill_keys($allowedTables, true);

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_errno) {
        $result['failed']++;
        $result['errors'][] =
            'DB connection failed: ' . $mysqli->connect_error;
        return $result;
    }

    $mysqli->set_charset('utf8mb4');
    $seenSignatures = [];

    $tableSchemas = [];
    foreach ($allowedTables as $tableName) {
        $tableSchemas[$tableName] = [];

        $schemaQuery = "
            SELECT
                COLUMN_NAME,
                DATA_TYPE,
                COLUMN_TYPE,
                IS_NULLABLE,
                COLUMN_DEFAULT,
                EXTRA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '" . $mysqli->real_escape_string($tableName) . "'
            ORDER BY ORDINAL_POSITION
        ";

        $schemaResult = $mysqli->query($schemaQuery);
        if ($schemaResult) {
            while ($column = $schemaResult->fetch_assoc()) {
                $tableSchemas[$tableName][$column['COLUMN_NAME']] = $column;
            }
            $schemaResult->free();
        }
    }

    if ($truncateExisting && !$dryRun) {
        $truncateOrder = [
            'sys_transactions',
            'sys_quoteitems',
            'sys_quotes',
            'sys_invoiceitems',
            'sys_invoices',
            'sys_accounts',
            'sys_items',
            'sys_companies',
            'crm_accounts',
            'sys_currencies',
        ];

        $mysqli->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($truncateOrder as $table) {
            if ($mysqli->query("TRUNCATE TABLE `$table`")) {
                $result['truncated_tables'][] = $table;
            } else {
                $result['errors'][] =
                    "Failed to truncate $table: " . $mysqli->error;
            }
        }
        $mysqli->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    $handle = fopen($sqlFilePath, 'r');
    if (!$handle) {
        $result['failed']++;
        $result['errors'][] = 'Unable to read uploaded SQL file.';
        $mysqli->close();
        return $result;
    }

    $buildSqlLiteral = function ($value, $columnMeta) use ($mysqli) {
        if ($value === null) {
            return 'NULL';
        }

        $dataType = strtolower($columnMeta['DATA_TYPE'] ?? '');
        $escapedValue = $mysqli->real_escape_string((string) $value);

        if (
            in_array($dataType, [
                'tinyint',
                'smallint',
                'mediumint',
                'int',
                'bigint',
                'decimal',
                'float',
                'double',
                'real',
                'bit',
            ])
        ) {
            $normalized = trim((string) $value);
            if ($normalized === '') {
                $normalized = '0';
            }

            return is_numeric($normalized) ? $normalized : '0';
        }

        return "'" . $escapedValue . "'";
    };

    $fallbackValueForColumn = function ($columnMeta) {
        $defaultValue = $columnMeta['COLUMN_DEFAULT'];
        if ($defaultValue !== null) {
            return $defaultValue;
        }

        if (($columnMeta['IS_NULLABLE'] ?? 'YES') === 'YES') {
            return null;
        }

        $columnType = strtolower($columnMeta['COLUMN_TYPE'] ?? '');
        $dataType = strtolower($columnMeta['DATA_TYPE'] ?? '');

        if (strpos($columnType, 'enum(') === 0) {
            preg_match_all("/'((?:\\'|[^'])*)'/", $columnType, $matches);
            if (!empty($matches[1][0])) {
                return str_replace("\\'", "'", $matches[1][0]);
            }
        }

        if (
            in_array($dataType, [
                'tinyint',
                'smallint',
                'mediumint',
                'int',
                'bigint',
                'decimal',
                'float',
                'double',
                'real',
                'bit',
            ])
        ) {
            return '0';
        }

        if ($dataType === 'date') {
            return '0000-00-00';
        }

        if (
            in_array($dataType, ['datetime', 'timestamp', 'time', 'year'])
        ) {
            return '0000-00-00 00:00:00';
        }

        return '';
    };

    $splitSqlTuples = function ($valuesSql) {
        $tuples = [];
        $buffer = '';
        $depth = 0;
        $inQuotes = false;
        $escaped = false;
        $collecting = false;
        $length = strlen($valuesSql);

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesSql[$i];

            // Ignore separators/spaces between tuples.
            if (!$collecting) {
                if ($char === '(') {
                    $collecting = true;
                    $depth = 1;
                    $buffer = '(';
                }
                continue;
            }

            $buffer .= $char;

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === "'") {
                if ($inQuotes && $i + 1 < $length && $valuesSql[$i + 1] === "'") {
                    $buffer .= "'";
                    $i++;
                    continue;
                }

                $inQuotes = !$inQuotes;
                continue;
            }

            if ($inQuotes) {
                continue;
            }

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    $trimmed = trim($buffer);
                    if ($trimmed !== '') {
                        $tuples[] = $trimmed;
                    }

                    $buffer = '';
                    $collecting = false;
                }
            }
        }

        return $tuples;
    };

    $parseInsertStatement = function ($statement) {
        $trimmed = trim($statement);
        if (!preg_match(
            '/^(INSERT|REPLACE)\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*VALUES\s*(.+);$/is',
            $trimmed,
            $matches
        )) {
            return null;
        }

        $columns = array_map(function ($column) {
            return trim($column, " `\r\n\t");
        }, explode(',', $matches[3]));

        return [
            'action' => strtoupper($matches[1]),
            'table' => $matches[2],
            'columns' => $columns,
            'values_sql' => $matches[4],
        ];
    };

    $processStatement = function ($statement, $statementTable) use (
        &$result,
        $parseInsertStatement,
        $tableSchemas,
        $splitSqlTuples,
        $fallbackValueForColumn,
        $buildSqlLiteral,
        $mysqli,
        $dryRun,
        $skipDuplicates,
        &$seenSignatures
    ) {
        $statement = utilLegacySanitizeStatement($statement);
        $parsedStatement = $parseInsertStatement($statement);

        if (!$parsedStatement) {
            $result['skipped']++;
            return;
        }

        $targetSchema = $tableSchemas[$statementTable] ?? [];
        $targetColumns = [];
        $sourceColumns = $parsedStatement['columns'];
        $sourceColumnMap = array_fill_keys($sourceColumns, true);

        foreach ($sourceColumns as $columnName) {
            if (isset($targetSchema[$columnName])) {
                $targetColumns[] = $columnName;
            }
        }

        foreach ($targetSchema as $columnName => $columnMeta) {
            if (
                isset($sourceColumnMap[$columnName]) ||
                stripos($columnMeta['EXTRA'] ?? '', 'auto_increment') !== false
            ) {
                continue;
            }

            if (
                ($columnMeta['IS_NULLABLE'] ?? 'YES') === 'NO' &&
                $columnMeta['COLUMN_DEFAULT'] === null
            ) {
                $targetColumns[] = $columnName;
            }
        }

        $targetColumns = array_values(array_unique($targetColumns));

        if (empty($targetColumns)) {
            $result['skipped']++;
            return;
        }

        $tuples = $splitSqlTuples($parsedStatement['values_sql']);
        $rebuiltRows = [];
        $rebuiltRowData = [];
        $skipDuplicatesForTable = $skipDuplicates && $statementTable !== 'sys_invoices';

        foreach ($tuples as $tupleSql) {
            $tupleBody = trim($tupleSql);
            $tupleBody = preg_replace('/^\(/', '', $tupleBody);
            $tupleBody = preg_replace('/\)$/', '', $tupleBody);

            // SQL-aware value splitter: respects single-quoted strings and backslash escapes
            $rawValues = [];
            $vbuf = '';
            $inQ = false;
            $tlen = strlen($tupleBody);
            for ($vi = 0; $vi < $tlen; $vi++) {
                $vc = $tupleBody[$vi];
                if ($inQ) {
                    if ($vc === '\\' && $vi + 1 < $tlen) {
                        $vbuf .= $vc . $tupleBody[$vi + 1];
                        $vi++;
                    } elseif ($vc === '\'' && $vi + 1 < $tlen && $tupleBody[$vi + 1] === '\'') {
                        $vbuf .= "''";
                        $vi++;
                    } elseif ($vc === '\'') {
                        $inQ = false;
                        $vbuf .= $vc;
                    } else {
                        $vbuf .= $vc;
                    }
                } else {
                    if ($vc === '\'') {
                        $inQ = true;
                        $vbuf .= $vc;
                    } elseif ($vc === ',') {
                        $rawValues[] = $vbuf;
                        $vbuf = '';
                    } else {
                        $vbuf .= $vc;
                    }
                }
            }
            $rawValues[] = $vbuf;

            if (count($rawValues) !== count($sourceColumns)) {
                $result['failed']++;
                $result['errors'][] =
                    "Column/value count mismatch for $statementTable (src=" .
                    count($rawValues) . " vs col=" . count($sourceColumns) . ").";
                continue;
            }

            $sourceData = [];
            foreach ($sourceColumns as $index => $columnName) {
                $rawValue = trim($rawValues[$index]);
                if (strtoupper($rawValue) === 'NULL') {
                    $sourceData[$columnName] = null;
                } elseif (strlen($rawValue) >= 2 && $rawValue[0] === '\'' && $rawValue[strlen($rawValue) - 1] === '\'') {
                    // Strip surrounding SQL quotes and decode escape sequences
                    $inner = substr($rawValue, 1, -1);
                    $decoded = '';
                    $ilen = strlen($inner);
                    for ($ii = 0; $ii < $ilen; $ii++) {
                        if ($inner[$ii] === '\\' && $ii + 1 < $ilen) {
                            $nc = $inner[$ii + 1];
                            if ($nc === 'n')      { $decoded .= "\n"; }
                            elseif ($nc === 'r')  { $decoded .= "\r"; }
                            elseif ($nc === 't')  { $decoded .= "\t"; }
                            elseif ($nc === '0')  { $decoded .= "\0"; }
                            else                  { $decoded .= $nc;  }
                            $ii++;
                        } elseif ($inner[$ii] === '\'' && $ii + 1 < $ilen && $inner[$ii + 1] === '\'') {
                            $decoded .= "'";
                            $ii++;
                        } else {
                            $decoded .= $inner[$ii];
                        }
                    }
                    $sourceData[$columnName] = $decoded;
                } else {
                    $sourceData[$columnName] = $rawValue;
                }
            }

            $rebuiltValues = [];
            $targetRowData = [];
            foreach ($targetColumns as $columnName) {
                $columnMeta = $targetSchema[$columnName];
                $value = array_key_exists($columnName, $sourceData)
                    ? $sourceData[$columnName]
                    : $fallbackValueForColumn($columnMeta);

                if (
                    $value === null &&
                    ($columnMeta['IS_NULLABLE'] ?? 'YES') === 'NO' &&
                    $columnMeta['COLUMN_DEFAULT'] === null
                ) {
                    $value = $fallbackValueForColumn($columnMeta);
                }

                    $targetRowData[$columnName] = $value;
                $rebuiltValues[] = $buildSqlLiteral($value, $columnMeta);
            }

            if ($skipDuplicatesForTable) {
                $signature = utilLegacyBuildRowSignature(
                    $statementTable,
                    $targetRowData
                );

                if ($signature !== null) {
                    if (!isset($seenSignatures[$statementTable])) {
                        $seenSignatures[$statementTable] = [];
                    }

                    if (isset($seenSignatures[$statementTable][$signature])) {
                        $result['duplicates_skipped']++;
                        if (!isset($result['duplicate_tables'][$statementTable])) {
                            $result['duplicate_tables'][$statementTable] = 0;
                        }
                        $result['duplicate_tables'][$statementTable]++;
                        continue;
                    }

                    $signatureColumns = explode('|', $signature);
                    $conditions = [];
                    foreach ($signatureColumns as $signaturePart) {
                        [$columnName, $columnValue] = explode('=', $signaturePart, 2);
                        $rawValue = $targetRowData[$columnName] ?? null;
                        if ($rawValue === null) {
                            continue;
                        }

                        $conditions[] =
                            '`' .
                            $mysqli->real_escape_string($columnName) .
                            '` = ' .
                            $buildSqlLiteral($rawValue, $targetSchema[$columnName]);
                    }

                    if (!empty($conditions)) {
                        $duplicateCheckSql =
                            'SELECT id FROM `' .
                            $statementTable .
                            '` WHERE ' .
                            implode(' AND ', $conditions) .
                            ' LIMIT 1';

                        $duplicateResult = $mysqli->query($duplicateCheckSql);
                        if ($duplicateResult && $duplicateResult->num_rows > 0) {
                            $duplicateResult->free();
                            $seenSignatures[$statementTable][$signature] = true;
                            $result['duplicates_skipped']++;
                            if (!isset($result['duplicate_tables'][$statementTable])) {
                                $result['duplicate_tables'][$statementTable] = 0;
                            }
                            $result['duplicate_tables'][$statementTable]++;
                            continue;
                        }

                        if ($duplicateResult) {
                            $duplicateResult->free();
                        }
                    }

                    $seenSignatures[$statementTable][$signature] = true;
                }
            }

            $rebuiltRows[] = '(' . implode(',', $rebuiltValues) . ')';
            $rebuiltRowData[] = $targetRowData;
        }

        if (empty($rebuiltRows)) {
            return;
        }

        if ($dryRun) {
            $result['executed'] += count($rebuiltRows);
            if (!isset($result['tables'][$statementTable])) {
                $result['tables'][$statementTable] = 0;
            }
            $result['tables'][$statementTable] += count($rebuiltRows);
            return;
        }

        $baseInsertSql =
            'INSERT INTO `' .
            $statementTable .
            '` (`' .
            implode('`,`', $targetColumns) .
            '`) VALUES ';

        $attemptInvoiceInsertWithUniqueNumber = function ($rowData) use (
            $statementTable,
            $targetColumns,
            $targetSchema,
            $buildSqlLiteral,
            $mysqli,
            &$result
        ) {
            if ($statementTable !== 'sys_invoices') {
                return false;
            }

            $attemptColumns = $targetColumns;

            // If legacy dump has duplicate IDs, let MySQL allocate a new ID.
            if (in_array('id', $attemptColumns, true)) {
                $attemptColumns = array_values(array_filter($attemptColumns, function ($columnName) {
                    return $columnName !== 'id';
                }));
            }

            if (empty($attemptColumns)) {
                return false;
            }

            $baseInvoiceNum = trim((string) ($rowData['invoicenum'] ?? ''));
            if ($baseInvoiceNum === '') {
                $baseInvoiceNum = 'INV';
            }

            // Keep room for suffix to respect common varchar(100) invoice number limits.
            $baseInvoiceNum = mb_substr($baseInvoiceNum, 0, 88, 'UTF-8');

            $buildInsertSql = function ($columns, $data) use ($buildSqlLiteral, $targetSchema, $statementTable) {
                $values = [];
                foreach ($columns as $columnName) {
                    $values[] = $buildSqlLiteral($data[$columnName] ?? null, $targetSchema[$columnName]);
                }

                return
                    'INSERT INTO `' .
                    $statementTable .
                    '` (`' .
                    implode('`,`', $columns) .
                    '`) VALUES (' .
                    implode(',', $values) .
                    ')';
            };

            for ($i = 1; $i <= 200; $i++) {
                $candidateData = $rowData;
                $candidateData['invoicenum'] = $baseInvoiceNum . '-R' . $i;
                $candidateSql = $buildInsertSql($attemptColumns, $candidateData);

                if ($mysqli->query($candidateSql)) {
                    $result['executed']++;
                    if (!isset($result['tables'][$statementTable])) {
                        $result['tables'][$statementTable] = 0;
                    }
                    $result['tables'][$statementTable]++;
                    return true;
                }

                $candidateError = (string) $mysqli->error;
                if (stripos($candidateError, 'Duplicate entry') === false) {
                    $result['failed']++;
                    $result['errors'][] =
                        'Failed statement for ' .
                        $statementTable .
                        ': ' .
                        $candidateError;
                    return true;
                }
            }

            $result['failed']++;
            $result['errors'][] =
                'Failed statement for ' .
                $statementTable .
                ': Could not resolve duplicate invoice number after 200 retries.';

            return true;
        };

        // Chunk large INSERT batches to avoid max_allowed_packet / oversized query failures.
        $chunks = array_chunk($rebuiltRows, 200);
        $chunkDataRows = array_chunk($rebuiltRowData, 200);
        foreach ($chunks as $chunkIndex => $chunkRows) {
            $chunkStatement = $baseInsertSql . implode(',', $chunkRows);
            if ($mysqli->query($chunkStatement)) {
                $result['executed'] += count($chunkRows);
                if (!isset($result['tables'][$statementTable])) {
                    $result['tables'][$statementTable] = 0;
                }
                $result['tables'][$statementTable] += count($chunkRows);
                continue;
            }

            // Fallback to row-by-row insert so one bad/duplicate row won't block full invoice batch.
            foreach ($chunkRows as $rowIndex => $singleRow) {
                $singleStatement = $baseInsertSql . $singleRow;
                if ($mysqli->query($singleStatement)) {
                    $result['executed']++;
                    if (!isset($result['tables'][$statementTable])) {
                        $result['tables'][$statementTable] = 0;
                    }
                    $result['tables'][$statementTable]++;
                    continue;
                }

                $dbError = (string) $mysqli->error;
                if (stripos($dbError, 'Duplicate entry') !== false) {
                    $rowData = $chunkDataRows[$chunkIndex][$rowIndex] ?? [];
                    if ($statementTable === 'sys_invoices' && $attemptInvoiceInsertWithUniqueNumber($rowData)) {
                        continue;
                    }

                    $result['duplicates_skipped']++;
                    if (!isset($result['duplicate_tables'][$statementTable])) {
                        $result['duplicate_tables'][$statementTable] = 0;
                    }
                    $result['duplicate_tables'][$statementTable]++;
                    continue;
                }

                $result['failed']++;
                $result['errors'][] =
                    "Failed statement for $statementTable: " .
                    $dbError;
            }
        }
    };

    $collectingStatement = false;
    $statement = '';
    $statementTable = '';

    while (($line = fgets($handle)) !== false) {
        if (!$collectingStatement) {
            if (
                preg_match(
                    '/^\s*(INSERT|REPLACE)\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i',
                    $line,
                    $matches
                )
            ) {
                $tableName = $matches[2];
                if (isset($allowedTableMap[$tableName])) {
                    $collectingStatement = true;
                    $statementTable = $tableName;
                    $statement = $line;

                    if (preg_match('/;\s*$/', $line)) {
                        $processStatement($statement, $statementTable);

                        $collectingStatement = false;
                        $statement = '';
                        $statementTable = '';
                    }
                } else {
                    $result['skipped']++;
                }
            }
            continue;
        }

        $statement .= $line;

        if (preg_match('/;\s*$/', $line)) {
            $processStatement($statement, $statementTable);

            $collectingStatement = false;
            $statement = '';
            $statementTable = '';
        }
    }

    fclose($handle);
    $mysqli->close();

    return $result;
}

switch ($action) {
    case 'activity':
        $paginator = Paginator::bootstrap('sys_logs');
        $d = ORM::for_table('sys_logs')
            ->offset($paginator['startpoint'])
            ->limit($paginator['limit'])
            ->order_by_desc('date')
            ->find_many();
        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);

        view('util-activity');
        break;

    case 'clear_logs':
        $b30 = date('Y-m-d H:i:s', strtotime('-30 days', time()));
        $d = ORM::for_table('sys_logs')
            ->where_lte('date', $b30)
            ->delete_many();
        _msglog('s', $_L['Logs has been deleted']);

        r2(U . 'util/activity');

        break;

    case 'sent-emails':
        $paginator = Paginator::bootstrap('sys_email_logs');
        $d = ORM::for_table('sys_email_logs')
            ->offset($paginator['startpoint'])
            ->limit($paginator['limit'])
            ->order_by_desc('date')
            ->find_many();
        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);

        view('util-sent-emails');
        break;

    case 'cronlogs':
        $paginator = Paginator::bootstrap(
            'sys_schedulelogs',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            5
        );
        $d = ORM::for_table('sys_schedulelogs')
            ->offset($paginator['startpoint'])
            ->limit($paginator['limit'])
            ->order_by_desc('date')
            ->find_many();
        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);

        view('util_cron_logs');
        break;

    case 'dbstatus':
        $dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if ($result = $dbc->query('SHOW TABLE STATUS')) {
            $size = 0;
            $decimals = 2;
            $tables = [];
            while ($row = $result->fetch_array()) {
                $size += $row["Data_length"] + $row["Index_length"];
                $total_size =
                    ($row["Data_length"] + $row["Index_length"]) / 1024;
                $tables[$row['Name']]['size'] = number_format($total_size, '0');
                $tables[$row['Name']]['rows'] = $row["Rows"];
                $tables[$row['Name']]['name'] = $row["Name"];
            }

            $mbytes = number_format(
                $size / (1024 * 1024),
                $decimals,
                $config['dec_point'],
                $config['thousands_sep']
            );

            $ui->assign('tables', $tables);
            $ui->assign('dbsize', $mbytes);
            view('dbstatus');
        }
        break;

    case 'dbbackup':
        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($mysqli->connect_errno) {
                throw new Exception(
                    "Failed to connect to MySQL: " . $mysqli->connect_error
                );
            }

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');
            header(
                'Content-Disposition: attachment;filename="backup_' .
                    date('Y-m-d_h_i_s') .
                    '.sql"'
            );
            header('Content-Transfer-Encoding: binary');

            ob_start();
            $f_output = fopen("php://output", 'w');

            print "-- pjl SQL Dump\n";
            print "-- Server version:" . $mysqli->server_info . "\n";
            print "-- Generated: " . date('Y-m-d h:i:s') . "\n";
            print '-- Current PHP version: ' . phpversion() . "\n";
            print '-- Host: ' . $db_host . "\n";
            print '-- Database:' . $db_name . "\n";

            $aTables = [];
            $strSQL = 'SHOW TABLES';
            if (!($res_tables = $mysqli->query($strSQL))) {
                throw new Exception(
                    "MySQL Error: " . $mysqli->error . 'SQL: ' . $strSQL
                );
            }

            while ($row = $res_tables->fetch_array()) {
                $aTables[] = $row[0];
            }

            $res_tables->free();

            foreach ($aTables as $table) {
                print "-- --------------------------------------------------------\n";
                print "-- Structure for '" . $table . "'\n";
                print "--\n\n";

                $strSQL = 'SHOW CREATE TABLE ' . $table;
                if (!($res_create = $mysqli->query($strSQL))) {
                    throw new Exception(
                        "MySQL Error: " . $mysqli->error . 'SQL: ' . $strSQL
                    );
                }
                $row_create = $res_create->fetch_assoc();

                print "\n" . $row_create['Create Table'] . ";\n";

                print "-- --------------------------------------------------------\n";
                print '-- Dump Data for `' . $table . "`\n";
                print "--\n\n";
                $res_create->free();

                $strSQL = 'SELECT * FROM ' . $table;
                if (!($res_select = $mysqli->query($strSQL))) {
                    throw new Exception(
                        "MySQL Error: " . $mysqli->error . 'SQL: ' . $strSQL
                    );
                }

                $fields_info = $res_select->fetch_fields();

                while ($values = $res_select->fetch_assoc()) {
                    $strFields = '';
                    $strValues = '';
                    foreach ($fields_info as $field) {
                        if ($strFields != '') {
                            $strFields .= ',';
                        }
                        $strFields .= "`" . $field->name . "`";

                        if ($strValues != '') {
                            $strValues .= ',';
                        }
                        $strValues .=
                            '"' .
                            preg_replace(
                                '/[^(\x20-\x7F)\x0A]*/',
                                '',
                                $values[$field->name] . '"'
                            );
                    }

                    print "INSERT INTO " .
                        $table .
                        " (" .
                        $strFields .
                        ") VALUES (" .
                        $strValues .
                        ");\n";
                }
                print "\n\n\n";

                $res_select->free();
            }
        } catch (Exception $e) {
            print $e->getMessage();
        }

        fclose($f_output);
        print ob_get_clean();
        $mysqli->close();

        break;

    case 'view-email':
        $id = $routes['2'];

        $d = ORM::for_table('sys_email_logs')->find($id);
        if ($d) {
            $ui->assign('d', $d);
            view('view-email');
        }

        break;

    case 'activity-ajax':
        $d = ORM::for_table('sys_logs')
            ->order_by_desc('id')
            ->limit(5)
            ->find_many();
        $html = '';
        $df = $config['df'] . ' H:i:s';
        foreach ($d as $ds) {
            $html .=
                '<li><div class="d-flex align-items-center">
                                                            <span class="d-flex flex-column flex-1">
                                                                
                                                                <span class="msg-a fs-sm">
                                                                    ' .
                $ds->description .
                '
                                                                </span>
                                                                
                                                                <span class="fs-nano text-muted mt-1">' .
                date($df, strtotime($ds->date)) .
                '</span>
                                                                
                                                            </span>
                                                            
                                                        </div></li>';
        }

        echo '<ul class="notification">
                                    ' .
            $html .
            '
                                </ul>';

        break;

    case 'terminal':
        view('terminal');

        break;

    case 'sys_status':
        $ui->assign('pinfo', Misc::systemInfo());

        $ui->assign('xjq', $xjq);
        $ui->assign('app_stage', APP_STAGE);

        view('util_sys_status');

        break;

    case 'sys_status_dl':
        break;

    case 'integrationcode':
        $s_client_login =
            '<form method="post" action="' .
            U .
            'client/auth/">
<input type="email" class="form-control" name="username" placeholder="' .
            $_L['Email Address'] .
            '"/>
<input type="password" class="form-control" name="password" placeholder="' .
            $_L['Password'] .
            '"/>
<button type="submit" class="btn btn-primary">' .
            $_L['Login'] .
            '</button>
</form>';

        $s_client_register =
            '<a href="' . U . 'client/register/">' . $_L['Register'] . '</a>';

        $form_client_login = htmlentities($s_client_login);
        $form_client_register = htmlentities($s_client_register);

        $ui->assign('form_client_login', $form_client_login);
        $ui->assign('form_client_register', $form_client_register);

        view('util_integrationcode');

        break;

    case 'invoice_access_log':
        $paginator = Paginator::bootstrap('ib_invoice_access_log');
        $d = ORM::for_table('ib_invoice_access_log')
            ->offset($paginator['startpoint'])
            ->limit($paginator['limit'])
            ->order_by_desc('viewed_at')
            ->find_array();
        $ui->assign('d', $d);
        $ui->assign('paginator', $paginator);

        view('util_invoice_access_log');

        break;

    case 'media':
        $folders = array_diff(scandir('./storage/'), [
            '..',
            '.',
            'index.html',
            '.DS_Store',
        ]);

        $current_path = route(2);

        $imgs = [];

        if ($current_path != '') {
            $files = glob("./storage/$current_path/*.*");
            for ($i = 0; $i < count($files); $i++) {
                $image = $files[$i];
                $supported_file = ['gif', 'jpg', 'jpeg', 'png'];

                $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                if (in_array($ext, $supported_file)) {
                    $imgs[] = $image;
                } else {
                    continue;
                }
            }
        }

        view('util_media', [
            'folders' => $folders,
            'imgs' => $imgs,
        ]);

        break;

    case 'tools':
        view('util_tools');

        break;

    case 'sql-import':
        $import_report = inSession('legacy_sql_import_report');
        $import_status = inSession('legacy_sql_import_status');
        $verification_sql = inSession('legacy_sql_verification_sql');
        $schema_check_report = inSession('legacy_sql_schema_check_report');
        $schema_check_status = inSession('legacy_sql_schema_check_status');
        $staged_import_token = inSession('legacy_sql_staged_token');
        $staged_file_name = inSession('legacy_sql_staged_file_name');
        $staged_options = inSession('legacy_sql_staged_options');

        unset($_SESSION['legacy_sql_import_report']);
        unset($_SESSION['legacy_sql_import_status']);
        unset($_SESSION['legacy_sql_verification_sql']);
        unset($_SESSION['legacy_sql_schema_check_report']);
        unset($_SESSION['legacy_sql_schema_check_status']);

        $ui->assign('import_report', $import_report);
        $ui->assign('import_status', $import_status);
        $ui->assign('verification_sql', $verification_sql);
        $ui->assign('schema_check_report', $schema_check_report);
        $ui->assign('schema_check_status', $schema_check_status);
        $ui->assign('staged_import_token', $staged_import_token);
        $ui->assign('staged_file_name', $staged_file_name);
        $ui->assign('staged_options', $staged_options);
        $ui->assign('legacy_import_tables', utilLegacyImportTables());
        view('util_sql_import');

        break;

    case 'sql-import-check-schema':
        if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== 0) {
            $_SESSION['legacy_sql_schema_check_report'] =
                'Upload failed. Please select a valid .sql file.';
            r2(U . 'util/sql-import', 'e', 'File upload failed');
        }

        $uploadedName = $_FILES['sql_file']['name'] ?? '';
        $tmpName = $_FILES['sql_file']['tmp_name'] ?? '';

        if (strtolower(pathinfo($uploadedName, PATHINFO_EXTENSION)) !== 'sql') {
            $_SESSION['legacy_sql_schema_check_report'] =
                'Invalid file extension. Please upload a .sql file.';
            r2(U . 'util/sql-import', 'e', 'Invalid file type');
        }

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $_SESSION['legacy_sql_schema_check_report'] =
                'No uploaded file was found in request.';
            r2(U . 'util/sql-import', 'e', 'Upload not detected');
        }

        $selectedTables = utilLegacyResolveSelectedTables(_post('import_tables'));
        if (empty($selectedTables)) {
            $_SESSION['legacy_sql_schema_check_report'] =
                'Please select at least one target table for schema check.';
            r2(U . 'util/sql-import', 'e', 'No tables selected');
        }

        $allowSuspectEncoding = _post('allow_suspect_encoding') === 'yes';
        $encodingCheck = utilLegacyAnalyzeSqlEncoding($tmpName);

        $targetPath =
            'storage/temp/legacy_schema_stage_' .
            date('Ymd_His') .
            '_' .
            _raid(6) .
            '.sql';

        if (!move_uploaded_file($tmpName, $targetPath)) {
            $_SESSION['legacy_sql_schema_check_report'] =
                'Could not save uploaded file into storage/temp.';
            r2(U . 'util/sql-import', 'e', 'Unable to save uploaded file');
        }

        $schemaCheck = utilLegacySchemaCheckSqlFile($targetPath, $selectedTables);

        $checks = [];
        $checks[] = [
            'label' => 'UTF-8 safety',
            'ok' => !($encodingCheck['has_risk'] ?? false) || $allowSuspectEncoding,
            'detail' =>
                'lines=' .
                (int) ($encodingCheck['scanned_lines'] ?? 0) .
                ', arabic=' .
                (int) ($encodingCheck['arabic_chars'] ?? 0) .
                ', ???=' .
                (int) ($encodingCheck['question_groups'] ?? 0),
        ];
        $checks[] = [
            'label' => 'Target table coverage',
            'ok' => empty($schemaCheck['missing_target_tables']),
            'detail' => empty($schemaCheck['missing_target_tables'])
                ? 'All selected tables exist in live DB'
                : 'Missing: ' . implode(', ', $schemaCheck['missing_target_tables']),
        ];
        $checks[] = [
            'label' => 'SQL parse validity',
            'ok' => (int) ($schemaCheck['parse_errors'] ?? 0) === 0,
            'detail' => (int) ($schemaCheck['parse_errors'] ?? 0) . ' parse errors',
        ];
        $checks[] = [
            'label' => 'Mapped statements found',
            'ok' => (int) ($schemaCheck['matched_statements'] ?? 0) > 0,
            'detail' => (int) ($schemaCheck['matched_statements'] ?? 0) . ' matched statements',
        ];
        $checks[] = [
            'label' => 'INSERT data present for all selected tables',
            'ok' => true,
            'detail' => empty($schemaCheck['no_inserts_tables'])
                ? 'All selected tables have INSERT statements'
                : 'Warning: no source data for: ' . implode(', ', $schemaCheck['no_inserts_tables']) . ' (will be skipped)',
        ];

        $schemaPass = true;
        foreach ($checks as $check) {
            if (!$check['ok']) {
                $schemaPass = false;
                break;
            }
        }

        if (isset($_SESSION['legacy_sql_staged_file_path']) && file_exists($_SESSION['legacy_sql_staged_file_path'])) {
            @unlink($_SESSION['legacy_sql_staged_file_path']);
        }

        unset($_SESSION['legacy_sql_staged_file_path']);
        unset($_SESSION['legacy_sql_staged_file_name']);
        unset($_SESSION['legacy_sql_staged_token']);
        unset($_SESSION['legacy_sql_staged_tables']);
        unset($_SESSION['legacy_sql_staged_options']);

        $reportLines = [];
        $reportLines[] = 'Schema Check ' . ($schemaPass ? 'PASS' : 'FAIL');
        $reportLines[] = '-------------------------------------';
        $reportLines[] = 'Uploaded file: ' . $uploadedName;
        $reportLines[] = 'Matched INSERT/REPLACE statements: ' . (int) ($schemaCheck['matched_statements'] ?? 0);
        $reportLines[] = 'Parse errors: ' . (int) ($schemaCheck['parse_errors'] ?? 0);
        $reportLines[] = 'UTF-8 scan lines: ' . (int) ($encodingCheck['scanned_lines'] ?? 0);
        $reportLines[] = 'UTF-8 Arabic chars: ' . (int) ($encodingCheck['arabic_chars'] ?? 0);
        $reportLines[] = 'UTF-8 ??? groups: ' . (int) ($encodingCheck['question_groups'] ?? 0);

        if (($encodingCheck['has_risk'] ?? false)) {
            $reportLines[] = 'UTF-8 risk: ' . ($encodingCheck['risk_reason'] ?? 'Potential text corruption detected.');
            if ($allowSuspectEncoding) {
                $reportLines[] = 'UTF-8 override: enabled';
            }
        }

        $reportLines[] = '-------------------------------------';
        $reportLines[] = 'Selected tables:';
        foreach ($selectedTables as $table) {
            $reportLines[] = '- ' . $table;
        }

        $reportLines[] = '-------------------------------------';
        $reportLines[] = 'Per-table schema map summary:';
        foreach (($schemaCheck['table_details'] ?? []) as $table => $detail) {
            $reportLines[] =
                '- ' .
                $table .
                ': statements=' .
                (int) ($detail['statements'] ?? 0) .
                ', unknown_legacy_columns=' .
                (int) ($detail['unknown_legacy_columns'] ?? 0) .
                ', required_new_columns=' .
                (int) ($detail['required_new_columns'] ?? 0);

            $unknownSamples = $detail['unknown_samples'] ?? [];
            if (!empty($unknownSamples)) {
                $reportLines[] = '  unknown sample: ' . implode(', ', $unknownSamples);
            }

            $requiredSamples = $detail['required_samples'] ?? [];
            if (!empty($requiredSamples)) {
                $reportLines[] = '  required-new sample: ' . implode(', ', $requiredSamples);
            }
        }

        $_SESSION['legacy_sql_schema_check_status'] = [
            'overall' => $schemaPass ? 'PASS' : 'FAIL',
            'checks' => $checks,
        ];
        $_SESSION['legacy_sql_schema_check_report'] = implode(PHP_EOL, $reportLines);

        if ($schemaPass) {
            $stagedToken = _raid(24);
            $_SESSION['legacy_sql_staged_file_path'] = $targetPath;
            $_SESSION['legacy_sql_staged_file_name'] = $uploadedName;
            $_SESSION['legacy_sql_staged_token'] = $stagedToken;
            $_SESSION['legacy_sql_staged_tables'] = $selectedTables;
            $_SESSION['legacy_sql_staged_options'] = [
                'truncate_existing' => _post('truncate_existing') === 'yes',
                'dry_run' => _post('dry_run') === 'yes',
                'skip_duplicates' => _post('skip_duplicates') === 'yes',
                'allow_suspect_encoding' => $allowSuspectEncoding,
            ];

            r2(U . 'util/sql-import', 's', 'Schema check passed. You can now insert data.');
        }

        @unlink($targetPath);
        r2(U . 'util/sql-import', 'e', 'Schema check failed. Fix issues and retry.');

        break;

    case 'sql-import-export-checklist':
        $verificationSql = inSession('legacy_sql_verification_sql');

        if (!$verificationSql || trim((string) $verificationSql) === '') {
            r2(
                U . 'util/sql-import',
                'e',
                'No verification checklist available. Run dry run or import first.'
            );
        }

        $fileName = 'legacy_post_import_checklist_' . date('Y-m-d_H-i-s') . '.sql';

        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        echo $verificationSql;
        exit;

        break;

    case 'sql-import-run':
        $stagedToken = _post('staged_import_token');
        $targetPath = '';
        $selectedTables = [];
        $encodingCheck = null;
        $allowSuspectEncoding = false;
        $cleanupAfterImport = false;

        if ($stagedToken !== '') {
            $sessionToken = inSession('legacy_sql_staged_token');
            $sessionPath = inSession('legacy_sql_staged_file_path');
            $sessionTables = inSession('legacy_sql_staged_tables');
            $sessionOptions = inSession('legacy_sql_staged_options');

            if (!$sessionToken || $sessionToken !== $stagedToken || !$sessionPath || !file_exists($sessionPath)) {
                $_SESSION['legacy_sql_import_report'] =
                    'Staged SQL file was not found. Please run Schema Check again.';
                r2(U . 'util/sql-import', 'e', 'Staged file missing');
            }

            $targetPath = $sessionPath;
            $selectedTables = is_array($sessionTables) ? $sessionTables : [];
            $encodingCheck = utilLegacyAnalyzeSqlEncoding($targetPath);
            $allowSuspectEncoding = (bool) ($sessionOptions['allow_suspect_encoding'] ?? false);
            $cleanupAfterImport = true;

            $truncateExisting = _post('truncate_existing') === 'yes';
            $dryRun = _post('dry_run') === 'yes';
            $skipDuplicates = _post('skip_duplicates') === 'yes';
        } else {
            if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== 0) {
                $_SESSION['legacy_sql_import_report'] =
                    'Upload failed. Please select a valid .sql file.';
                r2(U . 'util/sql-import', 'e', 'File upload failed');
            }

            $uploadedName = $_FILES['sql_file']['name'] ?? '';
            $tmpName = $_FILES['sql_file']['tmp_name'] ?? '';

            if (strtolower(pathinfo($uploadedName, PATHINFO_EXTENSION)) !== 'sql') {
                $_SESSION['legacy_sql_import_report'] =
                    'Invalid file extension. Please upload a .sql file.';
                r2(U . 'util/sql-import', 'e', 'Invalid file type');
            }

            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                $_SESSION['legacy_sql_import_report'] =
                    'No uploaded file was found in request.';
                r2(U . 'util/sql-import', 'e', 'Upload not detected');
            }

            $allowSuspectEncoding = _post('allow_suspect_encoding') === 'yes';
            $encodingCheck = utilLegacyAnalyzeSqlEncoding($tmpName);

            if (($encodingCheck['has_risk'] ?? false) && !$allowSuspectEncoding) {
                $encodingMessage =
                    'UTF-8 safety check blocked this import. ' .
                    ($encodingCheck['risk_reason'] ?? 'Potential text corruption detected.');

                $_SESSION['legacy_sql_import_report'] =
                    $encodingMessage .
                    PHP_EOL .
                    'Scanned lines: ' .
                    (int) ($encodingCheck['scanned_lines'] ?? 0) .
                    PHP_EOL .
                    'Arabic chars found: ' .
                    (int) ($encodingCheck['arabic_chars'] ?? 0) .
                    PHP_EOL .
                    'Multi-question-mark groups found: ' .
                    (int) ($encodingCheck['question_groups'] ?? 0) .
                    PHP_EOL .
                    'Tip: Export fresh SQL from old live DB using tools/ibilling_old_one_click_export.php';

                $_SESSION['legacy_sql_import_status'] = utilLegacyBuildCompactStatus(
                    [
                        'executed' => 0,
                        'failed' => 1,
                        'errors' => [$encodingMessage],
                        'selected_tables' => [],
                        'duplicates_skipped' => 0,
                    ],
                    false,
                    $encodingCheck
                );

                $_SESSION['legacy_sql_verification_sql'] = null;
                r2(U . 'util/sql-import', 'e', 'UTF-8 safety check failed');
            }

            $selectedTables = utilLegacyResolveSelectedTables(_post('import_tables'));
            if (empty($selectedTables)) {
                $_SESSION['legacy_sql_import_report'] =
                    'Please select at least one target table to import.';
                r2(U . 'util/sql-import', 'e', 'No tables selected');
            }

            $targetPath =
                'storage/temp/legacy_import_' .
                date('Ymd_His') .
                '_' .
                _raid(6) .
                '.sql';

            if (!move_uploaded_file($tmpName, $targetPath)) {
                $_SESSION['legacy_sql_import_report'] =
                    'Could not save uploaded file into storage/temp.';
                r2(U . 'util/sql-import', 'e', 'Unable to save uploaded file');
            }

            $truncateExisting = _post('truncate_existing') === 'yes';
            $dryRun = _post('dry_run') === 'yes';
            $skipDuplicates = _post('skip_duplicates') === 'yes';
        }
        $summary = utilImportLegacySqlDump(
            $targetPath,
            $truncateExisting,
            $selectedTables,
            $dryRun,
            $skipDuplicates
        );

        $compactStatus = utilLegacyBuildCompactStatus($summary, $dryRun, $encodingCheck);
        $verificationSql = utilLegacyPostImportChecklistSql($summary, $dryRun);

        if ($cleanupAfterImport) {
            @unlink($targetPath);
            unset($_SESSION['legacy_sql_staged_file_path']);
            unset($_SESSION['legacy_sql_staged_file_name']);
            unset($_SESSION['legacy_sql_staged_token']);
            unset($_SESSION['legacy_sql_staged_tables']);
            unset($_SESSION['legacy_sql_staged_options']);
        } else {
            @unlink($targetPath);
        }

        $reportLines = [];
        $reportLines[] = $dryRun
            ? 'Legacy SQL dry run completed.'
            : 'Legacy SQL import completed.';
        $reportLines[] = ($dryRun ? 'Planned rows: ' : 'Imported rows: ') . $summary['executed'];
        $reportLines[] = 'Skipped statements: ' . $summary['skipped'];
        $reportLines[] = 'Failed statements: ' . $summary['failed'];
        $reportLines[] = 'Duplicate rows skipped: ' . $summary['duplicates_skipped'];
        $reportLines[] = 'UTF-8 safety scan: ' . (($encodingCheck['has_risk'] ?? false) ? 'Risk detected' : 'OK');
        $reportLines[] = 'UTF-8 scan details: lines=' .
            (int) ($encodingCheck['scanned_lines'] ?? 0) .
            ', arabic=' .
            (int) ($encodingCheck['arabic_chars'] ?? 0) .
            ', ???=' .
            (int) ($encodingCheck['question_groups'] ?? 0);

        if (($encodingCheck['has_risk'] ?? false) && $allowSuspectEncoding) {
            $reportLines[] = 'Warning: UTF-8 risk was detected but import was allowed by override option.';
            if (!empty($encodingCheck['risk_reason'])) {
                $reportLines[] = 'UTF-8 warning detail: ' . $encodingCheck['risk_reason'];
            }
        }

        $reportLines[] = '-------------------------------------';
        $reportLines[] = 'Selected tables:';
        foreach ($summary['selected_tables'] as $table) {
            $reportLines[] = '- ' . $table;
        }
        $reportLines[] = '-------------------------------------';

        $reportLines[] = 'Options:';
        $reportLines[] = '- Dry run: ' . ($dryRun ? 'Yes' : 'No');
        $reportLines[] = '- Truncate existing: ' . ($truncateExisting ? 'Yes' : 'No');
        $reportLines[] = '- Skip duplicates: ' . ($skipDuplicates ? 'Yes' : 'No');
        $reportLines[] = '-------------------------------------';

        if (!empty($summary['truncated_tables'])) {
            $reportLines[] = 'Truncated tables before import:';
            foreach ($summary['truncated_tables'] as $table) {
                $reportLines[] = '- ' . $table;
            }
            $reportLines[] = '-------------------------------------';
        }

        if (!empty($summary['duplicate_tables'])) {
            $reportLines[] = 'Duplicate rows skipped by table:';
            foreach ($summary['duplicate_tables'] as $table => $count) {
                $reportLines[] = '- ' . $table . ': ' . $count;
            }
            $reportLines[] = '-------------------------------------';
        }

        if (!empty($summary['tables'])) {
            $reportLines[] = 'Imported statement count by table:';
            foreach ($summary['tables'] as $table => $count) {
                $reportLines[] = '- ' . $table . ': ' . $count;
            }
            $reportLines[] = '-------------------------------------';
        }

        if (!empty($summary['errors'])) {
            $reportLines[] = 'Errors:';
            foreach ($summary['errors'] as $error) {
                $reportLines[] = '- ' . $error;
            }
        }

        $_SESSION['legacy_sql_import_report'] = implode(PHP_EOL, $reportLines);
        $_SESSION['legacy_sql_import_status'] = $compactStatus;
        $_SESSION['legacy_sql_verification_sql'] = $verificationSql;

        if ($summary['failed'] > 0) {
            r2(U . 'util/sql-import', 'e', 'Import finished with errors');
        }

        if ($dryRun) {
            r2(U . 'util/sql-import', 's', 'Dry run completed successfully');
        }

        r2(U . 'util/sql-import', 's', 'Import completed successfully');

        break;

    case 'import':
        $importFrom = _post('importFrom');
        $fromUrl = _post('fromUrl');
        $apiKey = _post('apiKey');

        $_SESSION['fromUrl'] = $fromUrl;
        $_SESSION['apiKey'] = $apiKey;

        $import_appConfig = _post('appConfig');

        $import_customers = _post('customers');
        $import_groups = _post('groups');
        $import_companies = _post('companies');

        $import_invoices = _post('invoices');
        $import_invoice_items = _post('invoice_items');

        $import_quotes = _post('quotes');
        $import_quote_items = _post('quote_items');

        $import_accounts = _post('accounts');
        $import_transactions = _post('transactions');
        $import_currencies = _post('currencies');
        $import_items = _post('items');

        $message = '';

        switch ($importFrom) {
            case 'iBilling':
                $message .= 'Import Started...' . PHP_EOL;

                $data = ib_http_request($fromUrl . '/?ng=jsonexport', 'POST', [
                    'dataType' => 'auth',
                    'apiKey' => $apiKey,
                ]);

                $authCheck = json_decode($data);

                if (
                    isset($authCheck->success) &&
                    $authCheck->success == false
                ) {
                    $message .=
                        '====== ' . $authCheck->message . ' =======' . PHP_EOL;

                    echo $message;

                    exit();
                }

                if ($import_appConfig == 'yes') {
                    $message .=
                        '====== Importing Configuration =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'appConfig',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $appConfig = json_decode($data);

                    foreach ($appConfig as $c => $val) {
                        if (
                            $c == 'theme' ||
                            $c == 'nstyle' ||
                            $c == 'license_key' ||
                            $c == 'url_rewrite'
                        ) {
                            continue;
                        } else {
                            update_option($c, $val);

                            $message .=
                                'Config: ' . $c . ' => ' . $val . PHP_EOL;
                            $message .=
                                '_____________________________________' .
                                PHP_EOL;
                        }
                    }

                    $message .=
                        '====== Config Import Finished =======' . PHP_EOL;
                }

                if ($import_currencies == 'yes') {
                    Currency::truncate();

                    $message .= '====== Importing Currencies =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'currencies',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $currencies = json_decode($data);

                    foreach ($currencies as $currency) {
                        $d = new Currency();

                        if (isset($currency->cname)) {
                            $d->cname = $currency->cname;
                        }

                        if (isset($currency->iso_code)) {
                            $d->iso_code = $currency->iso_code;
                        }

                        if (isset($currency->symbol)) {
                            $d->symbol = $currency->symbol;
                        }

                        if (isset($currency->rate)) {
                            $d->rate = $currency->rate;
                        }

                        if (isset($currency->isdefault)) {
                            $d->isdefault = $currency->isdefault;
                        }

                        $d->save();
                    }

                    $message .=
                        '====== Config Import Finished =======' . PHP_EOL;
                }

                $currency = homeCurrency();

                if (!$currency) {
                    $currency = new Currency();

                    $currency->cname = $config['home_currency'];
                    $currency->iso_code = $config['home_currency'];
                    $currency->symbol = $config['currency_code'];
                    $currency->isdefault = 1;

                    $currency->save();

                    $home_currency_id = $currency->id;
                }

                $home_currency_id = $currency->id;

                if ($import_customers == 'yes') {
                    $customer_count = 0;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'customers',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $customers = json_decode($data);

                    $message .= '====== Importing Customers =======' . PHP_EOL;

                    foreach ($customers as $customer) {
                        $d = new Contact();

                        if (isset($customer->id)) {
                            $d->id = $customer->id;
                        }

                        if (isset($customer->account)) {
                            $d->account = $customer->account;
                        }

                        if (isset($customer->email)) {
                            $d->email = $customer->email;
                        }

                        if (isset($customer->phone)) {
                            $d->phone = $customer->phone;
                        }

                        if (isset($customer->address)) {
                            $d->address = $customer->address;
                        }

                        if (isset($customer->city)) {
                            $d->city = $customer->city;
                        }

                        if (isset($customer->zip)) {
                            $d->zip = $customer->zip;
                        }

                        if (isset($customer->state)) {
                            $d->state = $customer->state;
                        }

                        if (isset($customer->country)) {
                            $d->country = $customer->country;
                        }

                        if (isset($customer->company)) {
                            $d->company = $customer->company;
                        }

                        if (isset($customer->balance)) {
                            $d->balance = $customer->balance;
                        }

                        if (isset($customer->notes)) {
                            $d->notes = $customer->notes;
                        }

                        if (isset($customer->password)) {
                            $d->password = $customer->password;
                        }

                        if (isset($customer->token)) {
                            $d->token = $customer->token;
                        }

                        if (isset($customer->gname)) {
                            $d->gname = $customer->gname;
                        }

                        if (isset($customer->gid)) {
                            $d->gid = $customer->gid;
                        }

                        if (isset($customer->currency)) {
                            $d->currency = $customer->currency;
                        }

                        if (isset($customer->facebook)) {
                            $d->facebook = $customer->facebook;
                        }

                        if (isset($customer->google)) {
                            $d->google = $customer->google;
                        }

                        if (isset($customer->linkedin)) {
                            $d->linkedin = $customer->linkedin;
                        }

                        $d->save();

                        $message .=
                            'Customer: ' .
                            $customer->account .
                            ' Imported.' .
                            PHP_EOL;
                        $message .=
                            '_____________________________________' . PHP_EOL;

                        $customer_count++;
                    }

                    $message .=
                        '... ' .
                        $customer_count .
                        ' Customer Imported!' .
                        PHP_EOL;
                    $message .=
                        '====== Customers Import Finished =======' . PHP_EOL;

                    $sLogoUrl =
                        $fromUrl . '/application/storage/system/logo.png';

                    $message .= '..... Importing old logo' . PHP_EOL;

                    try {
                        $file_name = 'logo_' . _raid(10) . '.png';

                        $img = Image::make($sLogoUrl)->save(
                            'storage/system/' . $file_name
                        );

                        update_option('logo_default', $file_name);

                        $message .= '====== Logo Imported =======' . PHP_EOL;
                    } catch (Exception $e) {
                        $message .= 'warn: Importing logo failed.' . PHP_EOL;
                        $message .= $e->getMessage() . PHP_EOL;
                    }
                }

                if ($import_companies == 'yes') {
                    $company_count = 0;

                    $message .= '====== Importing Companies =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'companies',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $companies = json_decode($data);

                    foreach ($companies as $company) {
                        if ($company->company_name == '') {
                            continue;
                        } else {
                            $company_exist = Company::where(
                                'company_name',
                                $company->company_name
                            )->first();

                            if (!$company_exist) {
                                $d = new Company();

                                if (isset($company->id)) {
                                    $d->id = $company->id;
                                }

                                if (isset($company->company_name)) {
                                    $d->company_name = $company->company_name;
                                }

                                if (isset($company->url)) {
                                    $d->url = $company->url;
                                }

                                if (isset($company->logo_url)) {
                                    $d->logo_url = $company->logo_url;
                                }

                                if (isset($company->email)) {
                                    $d->email = $company->email;
                                }

                                if (isset($company->phone)) {
                                    $d->phone = $company->phone;
                                }

                                $d->save();

                                $message .=
                                    'Company: ' .
                                    $company->company_name .
                                    ' Imported.' .
                                    PHP_EOL;
                                $message .=
                                    '_____________________________________' .
                                    PHP_EOL;

                                $company_count++;
                            }
                        }
                    }

                    $message .=
                        '... ' .
                        $company_count .
                        ' Company Imported!' .
                        PHP_EOL;
                    $message .=
                        '====== Companies Import Finished =======' . PHP_EOL;
                }

                if ($import_groups == 'yes') {
                    $group_count = 0;

                    $message .= '====== Importing Groups =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'groups',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $groups = json_decode($data);

                    foreach ($groups as $group) {
                        if ($group->gname == '') {
                            continue;
                        } else {
                            $group_exist = ContactGroup::where(
                                'gname',
                                $group->gname
                            )->first();

                            if (!$group_exist) {
                                $d = new ContactGroup();

                                if (isset($group->gname)) {
                                    $d->gname = $group->gname;
                                }

                                $d->save();

                                $message .=
                                    'Group: ' .
                                    $group->gname .
                                    ' Imported.' .
                                    PHP_EOL;
                                $message .=
                                    '_____________________________________' .
                                    PHP_EOL;

                                $group_count++;
                            }
                        }
                    }

                    $message .=
                        '... ' . $group_count . ' Group Imported!' . PHP_EOL;
                    $message .=
                        '====== Groups Import Finished =======' . PHP_EOL;
                }

                if ($import_items == 'yes') {
                    $item_count = 0;

                    $message .= '====== Importing Items =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'items',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $items = json_decode($data);

                    foreach ($items as $item) {
                        $d = new Item();

                        if (isset($item->name)) {
                            $d->name = $item->name;
                        }

                        if (isset($item->sales_price)) {
                            $d->sales_price = $item->sales_price;
                        }

                        if (isset($item->item_number)) {
                            $d->item_number = $item->item_number;
                        }

                        if (isset($item->description)) {
                            $d->description = $item->description;
                        }

                        if (isset($item->type)) {
                            $d->type = $item->type;
                        }

                        if (isset($item->unit)) {
                            $d->unit = $item->unit;
                        }

                        if (isset($item->weight)) {
                            $d->weight = $item->weight;
                        }

                        if (isset($item->inventory)) {
                            $d->inventory = $item->inventory;
                        }

                        if (isset($item->e)) {
                            $d->e = $item->e;
                        }

                        if (isset($item->cost_price)) {
                            $d->cost_price = $item->cost_price;
                        }

                        $d->save();

                        $message .= 'Item: ' . $item->name . ' ...' . PHP_EOL;
                        $message .=
                            '_____________________________________' . PHP_EOL;

                        $item_count++;
                    }

                    $message .=
                        '... ' . $item_count . ' Item Imported!' . PHP_EOL;
                    $message .=
                        '====== Items Import Finished =======' . PHP_EOL;
                }

                if ($import_invoices == 'yes') {
                    $invoice_count = 0;

                    $message .= '====== Importing Invoices =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'invoices',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $invoices = json_decode($data);

                    foreach ($invoices as $invoice) {
                        $d = new Invoice();

                        if (isset($invoice->id)) {
                            $d->id = $invoice->id;
                        }

                        if (isset($invoice->userid)) {
                            $d->userid = $invoice->userid;
                        }

                        if (isset($invoice->account)) {
                            $d->account = $invoice->account;
                        }

                        if (isset($invoice->date)) {
                            $d->date = $invoice->date;
                        }

                        if (isset($invoice->duedate)) {
                            $d->duedate = $invoice->duedate;
                        }

                        if (
                            isset($invoice->datepaid) &&
                            $invoice->datepaid != '0000-00-00 00:00:00'
                        ) {
                            $d->datepaid = $invoice->datepaid;
                        }

                        if (isset($invoice->subtotal)) {
                            $d->subtotal = $invoice->subtotal;
                        }

                        if (isset($invoice->discount_type)) {
                            $d->discount_type = $invoice->discount_type;
                        }

                        if (isset($invoice->discount_value)) {
                            $d->discount_value = $invoice->discount_value;
                        }

                        if (isset($invoice->discount)) {
                            $d->discount = $invoice->discount;
                        }

                        if (isset($invoice->total)) {
                            $d->total = $invoice->total;
                        }

                        if (isset($invoice->tax)) {
                            $d->tax = $invoice->tax;
                        }

                        if (isset($invoice->taxname)) {
                            $d->taxname = $invoice->taxname;
                        }

                        if (isset($invoice->taxrate)) {
                            $d->taxrate = $invoice->taxrate;
                        }

                        if (isset($invoice->vtoken)) {
                            $d->vtoken = $invoice->vtoken;
                        }

                        if (isset($invoice->ptoken)) {
                            $d->ptoken = $invoice->ptoken;
                        }

                        if (isset($invoice->status)) {
                            $d->status = $invoice->status;
                        }

                        if (isset($invoice->notes)) {
                            $d->notes = $invoice->notes;
                        }

                        if (isset($invoice->r)) {
                            $d->r = $invoice->r;
                        }

                        if (isset($invoice->nd)) {
                            $d->nd = $invoice->nd;
                        }

                        if (isset($invoice->invoicenum)) {
                            $d->invoicenum = $invoice->invoicenum;
                        }

                        if (isset($invoice->cn)) {
                            $d->cn = $invoice->cn;
                        }

                        if (isset($invoice->tax2)) {
                            $d->tax2 = $invoice->tax2;
                        }

                        if (isset($invoice->taxrate2)) {
                            $d->taxrate2 = $invoice->taxrate2;
                        }

                        if (isset($invoice->paymentmethod)) {
                            $d->paymentmethod = $invoice->paymentmethod;
                        }

                        if (isset($invoice->currency)) {
                            $d->currency = $invoice->currency;
                        }

                        if (isset($invoice->currency_symbol)) {
                            $d->currency_symbol = $invoice->currency_symbol;
                        }

                        if (isset($invoice->currency_rate)) {
                            $d->currency_rate = $invoice->currency_rate;
                        }

                        if (isset($invoice->receipt_number)) {
                            $d->receipt_number = $invoice->receipt_number;
                        }

                        $d->save();
                    }

                    $message .=
                        '... ' .
                        $invoice_count .
                        ' Invoice Imported!' .
                        PHP_EOL;
                    $message .=
                        '====== Invoices Import Finished =======' . PHP_EOL;
                }

                if ($import_invoice_items == 'yes') {
                    $invoice_item_count = 0;

                    $message .=
                        '====== Importing Invoice Items =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'invoice_items',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $invoice_items = json_decode($data);

                    foreach ($invoice_items as $invoice_item) {
                        $d = new InvoiceItem();

                        if (isset($invoice_item->id)) {
                            $d->id = $invoice_item->id;
                        }

                        if (isset($invoice_item->invoiceid)) {
                            $d->invoiceid = $invoice_item->invoiceid;
                        }

                        if (isset($invoice_item->userid)) {
                            $d->userid = $invoice_item->userid;
                        }

                        if (isset($invoice_item->description)) {
                            $d->description = $invoice_item->description;
                        }

                        if (isset($invoice_item->qty)) {
                            $d->qty = $invoice_item->qty;
                        }

                        if (isset($invoice_item->amount)) {
                            $d->amount = $invoice_item->amount;
                        }

                        if (isset($invoice_item->total)) {
                            $d->total = $invoice_item->total;
                        }

                        if (isset($invoice_item->taxed)) {
                            $d->taxed = $invoice_item->taxed;
                        }

                        if (isset($invoice_item->type)) {
                            $d->type = $invoice_item->type;
                        }

                        if (isset($invoice_item->relid)) {
                            $d->relid = $invoice_item->relid;
                        }

                        if (isset($invoice_item->itemcode)) {
                            $d->itemcode = $invoice_item->itemcode;
                        }

                        if (isset($invoice_item->taxamount)) {
                            $d->taxamount = $invoice_item->taxamount;
                        }

                        if (isset($invoice_item->duedate)) {
                            $d->duedate = $invoice_item->duedate;
                        }

                        if (isset($invoice_item->paymentmethod)) {
                            $d->paymentmethod = $invoice_item->paymentmethod;
                        }

                        if (isset($invoice_item->notes)) {
                            $d->notes = $invoice_item->notes;
                        }

                        $d->save();

                        $message .=
                            'Invoice Item: ' .
                            $invoice_item->description .
                            ' ...' .
                            PHP_EOL;
                        $message .=
                            '_____________________________________' . PHP_EOL;

                        $invoice_item_count++;
                    }

                    $message .=
                        '... ' .
                        $invoice_item_count .
                        ' Invoice Item Imported!' .
                        PHP_EOL;
                    $message .=
                        '====== Invoice Items Import Finished =======' .
                        PHP_EOL;
                }

                if ($import_quotes == 'yes') {
                    $quote_count = 0;

                    $message .= '====== Importing Quotes =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'quotes',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $quotes = json_decode($data);

                    foreach ($quotes as $quote) {
                        $quote_count++;

                        $d = ORM::for_table('sys_quotes')->create();

                        if (isset($quote->id)) {
                            $d->id = $quote->id;
                        }

                        if (isset($quote->subject)) {
                            $d->subject = $quote->subject;
                        }

                        if (isset($quote->stage)) {
                            $d->stage = $quote->stage;
                        }

                        if (isset($quote->validuntil)) {
                            $d->validuntil = $quote->validuntil;
                        }

                        if (isset($quote->userid)) {
                            $d->userid = $quote->userid;
                        }

                        if (isset($quote->account)) {
                            $d->account = $quote->account;
                        }

                        if (isset($quote->invoicenum)) {
                            $d->invoicenum = $quote->invoicenum;
                        }

                        if (isset($quote->cn)) {
                            $d->cn = $quote->cn;
                        }

                        if (isset($quote->firstname)) {
                            $d->firstname = $quote->firstname;
                        }

                        if (isset($quote->lastname)) {
                            $d->lastname = $quote->lastname;
                        }

                        if (isset($quote->companyname)) {
                            $d->companyname = $quote->companyname;
                        }

                        if (isset($quote->email)) {
                            $d->email = $quote->email;
                        }

                        if (isset($quote->address1)) {
                            $d->address1 = $quote->address1;
                        }

                        if (isset($quote->address2)) {
                            $d->address2 = $quote->address2;
                        }

                        if (isset($quote->city)) {
                            $d->city = $quote->city;
                        }

                        if (isset($quote->state)) {
                            $d->state = $quote->state;
                        }

                        if (isset($quote->postcode)) {
                            $d->postcode = $quote->postcode;
                        }

                        if (isset($quote->country)) {
                            $d->country = $quote->country;
                        }

                        if (isset($quote->phonenumber)) {
                            $d->phonenumber = $quote->phonenumber;
                        }

                        if (isset($quote->currency)) {
                            $d->currency = $quote->currency;
                        }

                        if (isset($quote->subtotal)) {
                            $d->subtotal = $quote->subtotal;
                        }

                        if (isset($quote->discount_type)) {
                            $d->discount_type = $quote->discount_type;
                        }

                        if (isset($quote->discount_value)) {
                            $d->discount_value = $quote->discount_value;
                        }

                        if (isset($quote->discount)) {
                            $d->discount = $quote->discount;
                        }

                        if (isset($quote->taxname)) {
                            $d->taxname = $quote->taxname;
                        }

                        if (isset($quote->taxrate)) {
                            $d->taxrate = $quote->taxrate;
                        }

                        if (isset($quote->tax1)) {
                            $d->tax1 = $quote->tax1;
                        }

                        if (isset($quote->tax2)) {
                            $d->tax2 = $quote->tax2;
                        }

                        if (isset($quote->total)) {
                            $d->total = $quote->total;
                        }

                        if (isset($quote->proposal)) {
                            $d->proposal = $quote->proposal;
                        }

                        if (isset($quote->customernotes)) {
                            $d->customernotes = $quote->customernotes;
                        }

                        if (isset($quote->adminnotes)) {
                            $d->adminnotes = $quote->adminnotes;
                        }

                        if (
                            isset($quote->datecreated) &&
                            $quote->datecreated != '0000-00-00 00:00:00'
                        ) {
                            $d->datecreated = $quote->datecreated;
                        }

                        if (
                            isset($quote->lastmodified) &&
                            $quote->lastmodified != '0000-00-00 00:00:00'
                        ) {
                            $d->lastmodified = $quote->lastmodified;
                        }

                        if (
                            isset($quote->datesent) &&
                            $quote->datesent != '0000-00-00 00:00:00'
                        ) {
                            $d->datesent = $quote->datesent;
                        }

                        if (
                            isset($quote->dateaccepted) &&
                            $quote->dateaccepted != '0000-00-00 00:00:00'
                        ) {
                            $d->dateaccepted = $quote->dateaccepted;
                        }

                        if (isset($quote->vtoken)) {
                            $d->vtoken = $quote->vtoken;
                        }

                        $d->save();

                        $message .= 'Quote: ' . $quote->id . ' ...' . PHP_EOL;
                        $quote_count++;
                    }

                    $message .=
                        '... ' . $quote_count . ' Quote Imported!' . PHP_EOL;
                    $message .=
                        '====== Quotes Import Finished =======' . PHP_EOL;
                }

                if ($import_quote_items == 'yes') {
                    $quote_item_count = 0;

                    $message .=
                        '====== Importing Quote Items =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'quote_items',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $quote_items = json_decode($data);

                    foreach ($quote_items as $quote_item) {
                        $d = ORM::for_table('sys_quoteitems')->create();

                        if (isset($quote_item->id)) {
                            $d->id = $quote_item->id;
                        }

                        if (isset($quote_item->qid)) {
                            $d->qid = $quote_item->qid;
                        }

                        if (isset($quote_item->description)) {
                            $d->description = $quote_item->description;
                        }

                        if (isset($quote_item->qty)) {
                            $d->qty = $quote_item->qty;
                        }

                        if (isset($quote_item->amount)) {
                            $d->amount = $quote_item->amount;
                        }

                        if (isset($quote_item->discount)) {
                            $d->discount = $quote_item->discount;
                        }

                        if (isset($quote_item->total)) {
                            $d->total = $quote_item->total;
                        }

                        if (isset($quote_item->taxable)) {
                            $d->taxable = $quote_item->taxable;
                        }

                        if (isset($quote_item->itemcode)) {
                            $d->itemcode = $quote_item->itemcode;
                        }

                        $d->save();

                        $message .=
                            'Quote Item: ' .
                            $quote_item->description .
                            ' ...' .
                            PHP_EOL;
                        $message .=
                            '_____________________________________' . PHP_EOL;

                        $quote_item_count++;
                    }

                    $message .=
                        '... ' .
                        $quote_item_count .
                        ' Invoice Item Imported!' .
                        PHP_EOL;
                    $message .=
                        '====== Quote Items Import Finished =======' . PHP_EOL;
                }

                if ($import_accounts == 'yes') {
                    // Import Categories

                    $t_old = TransactionCategory::truncate();

                    //

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'categories',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $categories = json_decode($data);

                    foreach ($categories as $category) {
                        $c = new TransactionCategory();

                        $c->name = $category->name;
                        $c->type = $category->type;
                        $c->sorder = $category->sorder;

                        $c->save();

                        $message .=
                            'Category: ' . $category->name . ' ...' . PHP_EOL;
                    }

                    //

                    $account_count = 0;

                    $message .=
                        '====== Importing Bank Accounts =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'accounts',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $accounts = json_decode($data);

                    $currency = homeCurrency();

                    foreach ($accounts as $account) {
                        if ($account->account == '') {
                            continue;
                        } else {
                            $account_exist = Account::where(
                                'account',
                                $account->account
                            )->first();

                            if (!$account_exist) {
                                $d = new Account();

                                if (isset($account->account)) {
                                    $d->account = $account->account;
                                }

                                if (isset($account->description)) {
                                    $d->description = $account->description;
                                }

                                if (isset($account->balance)) {
                                    $d->balance = $account->balance;
                                }

                                if (isset($account->bank_name)) {
                                    $d->bank_name = $account->bank_name;
                                }

                                if (isset($account->account_number)) {
                                    $d->account_number =
                                        $account->account_number;
                                }

                                if (isset($account->currency)) {
                                    $d->currency = $account->currency;
                                }

                                if (isset($account->branch)) {
                                    $d->branch = $account->branch;
                                }

                                if (isset($account->address)) {
                                    $d->address = $account->address;
                                }

                                if (isset($account->contact_person)) {
                                    $d->contact_person =
                                        $account->contact_person;
                                }

                                if (isset($account->contact_phone)) {
                                    $d->contact_phone = $account->contact_phone;
                                }

                                if (isset($account->website)) {
                                    $d->website = $account->website;
                                }

                                if (isset($account->ib_url)) {
                                    $d->ib_url = $account->ib_url;
                                }

                                if (isset($account->notes)) {
                                    $d->notes = $account->notes;
                                }

                                if (isset($account->sorder)) {
                                    $d->sorder = $account->sorder;
                                }

                                if (isset($account->e)) {
                                    $d->e = $account->e;
                                }

                                if (isset($account->token)) {
                                    $d->token = $account->token;
                                }

                                if (isset($account->status)) {
                                    $d->status = $account->status;
                                }

                                $d->save();

                                $account_id = $d->id;

                                $b = new Balance();
                                $b->account_id = $account_id;
                                $b->currency_id = $home_currency_id;
                                $b->balance = $account->balance;
                                $b->save();

                                $message .=
                                    'Account: ' .
                                    $account->account .
                                    ' ...' .
                                    PHP_EOL;
                                $message .=
                                    '_____________________________________' .
                                    PHP_EOL;

                                $message .=
                                    '.... Updating Account Balance' . PHP_EOL;

                                $account_count++;
                            }
                        }
                    }

                    $message .=
                        '... ' .
                        $account_count .
                        ' Account Imported!' .
                        PHP_EOL;
                    $message .=
                        '====== Accounts Import Finished =======' . PHP_EOL;
                }

                if ($import_transactions == 'yes') {
                    $transaction_count = 0;

                    $message .=
                        '====== Importing Transactions =======' . PHP_EOL;

                    $data = ib_http_request(
                        $fromUrl . '/?ng=jsonexport',
                        'POST',
                        [
                            'dataType' => 'transactions',
                            'apiKey' => $apiKey,
                        ]
                    );

                    $transactions = json_decode($data);

                    foreach ($transactions as $transaction) {
                        $d = new Transaction();

                        if (isset($transaction->account)) {
                            $d->account = $transaction->account;
                        }

                        if (isset($transaction->type)) {
                            $d->type = $transaction->type;
                        }

                        if (isset($transaction->payerid)) {
                            $d->payerid = $transaction->payerid;
                        }

                        if (isset($transaction->tags)) {
                            $d->tags = $transaction->tags;
                        }

                        if (isset($transaction->amount)) {
                            $d->amount = $transaction->amount;
                        }

                        if (isset($transaction->category)) {
                            $d->category = $transaction->category;
                        }

                        if (isset($transaction->method)) {
                            $d->method = $transaction->method;
                        }

                        if (isset($transaction->ref)) {
                            $d->ref = $transaction->ref;
                        }

                        if (isset($transaction->attachments)) {
                            $d->attachments = $transaction->attachments;
                        }

                        if (isset($transaction->description)) {
                            $d->description = $transaction->description;
                        }

                        if (isset($transaction->date)) {
                            $d->date = $transaction->date;
                        }

                        if (isset($transaction->dr)) {
                            $d->dr = $transaction->dr;
                        }

                        if (isset($transaction->cr)) {
                            $d->cr = $transaction->cr;
                        }

                        if (isset($transaction->bal)) {
                            $d->bal = $transaction->bal;
                        }

                        if (isset($transaction->payer)) {
                            $d->payer = $transaction->payer;
                        }

                        if (isset($transaction->payee)) {
                            $d->payee = $transaction->payee;
                        }
                        if (isset($transaction->payeeid)) {
                            $d->payeeid = $transaction->payeeid;
                        }
                        if (isset($transaction->status)) {
                            $d->status = $transaction->status;
                        }

                        if (isset($transaction->tax)) {
                            $d->tax = $transaction->tax;
                        }

                        if (isset($transaction->iid)) {
                            $d->iid = $transaction->iid;
                        }

                        if (isset($transaction->aid)) {
                            $d->aid = $transaction->aid;
                        }

                        if (isset($transaction->vid)) {
                            $d->vid = $transaction->vid;
                        }

                        $d->save();

                        $message .=
                            'Transaction: ' .
                            $transaction->description .
                            ' ...' .
                            PHP_EOL;
                        $message .=
                            '_____________________________________' . PHP_EOL;

                        $transaction_count++;
                    }

                    $message .=
                        '... ' .
                        $transaction_count .
                        ' Transaction Imported!' .
                        PHP_EOL;
                    $message .=
                        '====== Transactions Import Finished =======' . PHP_EOL;

                    $categories = TransactionCategory::where(
                        'type',
                        'Income'
                    )->get();

                    foreach ($categories as $category) {
                        $total = categoryCalculateTotalByName(
                            $category->name,
                            'Income'
                        );
                        $category->total_amount = $total;
                        $category->save();

                        $message .=
                            'Category Balance Updated: ' .
                            $category->name .
                            ' -' .
                            $total .
                            PHP_EOL;
                    }

                    $categories = TransactionCategory::where(
                        'type',
                        'Expense'
                    )->get();

                    foreach ($categories as $category) {
                        $total = categoryCalculateTotalByName(
                            $category->name,
                            'Expense'
                        );
                        $category->total_amount = $total;
                        $category->save();

                        $message .=
                            'Category Balance Updated: ' .
                            $category->name .
                            ' -' .
                            $total .
                            PHP_EOL;
                    }

                    $items = InvoiceItem::all();
                }

                echo $message;

                break;
        }

        break;

    case 'rebuild_cat_summary':
        break;

    case 'rebuild_item_sales':
        break;

    case 'clear-financial-data-cache':
        Transaction::rebuildCatData();

        r2(U . 'util/tools', 's', $_L['Data Updated']);

        break;

    case 'backup-database':
        $backup = new Backup();

        $backupDB = $backup->backupDB();

        $message = '';
        $continue = 'No';

        if ($backupDB['success']) {
            $continue = 'Yes';
            $message = $backupDB['message'];

            r2(
                U . 'util/tools',
                's',
                'Backup created. <a href="' .
                    APP_URL .
                    '/' .
                    $backupDB['file_path'] .
                    '">Click Here to Download</a>'
            );
        } else {
            $message = $backupDB['message'];
            r2(U . 'util/tools', 'e', $message);
        }

        break;

    case 'backups':
        require_once 'system/lib/directory_list/DirectoryLister.php';

        $lister = new DirectoryLister();

        $files = $lister->listDirectory('storage/backups/db');

        if (APP_STAGE == 'Demo') {
            $files = [];
        }

        view('util_backups', [
            'files' => $files,
        ]);

        break;

    case 'backup_files':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'util/backups',
                'e',
                'Sorry, this feature is disabled in the Demo mode!'
            );
        }

        require_once 'system/lib/directory_list/DirectoryLister.php';

        $lister = new DirectoryLister();

        $files = $lister->listDirectory('storage/backups/app');

        view('util_backups_files', [
            'files' => $files,
        ]);

        break;

    case 'do-backup-db':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'util/backups',
                'e',
                'Sorry, this feature is disabled in the Demo mode!'
            );
        }

        clxPerformLongProcess();

        Util::backupDatabase();

        r2(U . 'util/backups', 's', $_L['Created Successfully']);

        break;

    case 'download-db-backup':

        if (APP_STAGE == 'Demo') {
            r2(
                U . 'util/backups',
                'e',
                'Sorry, this feature is disabled in the Demo mode!'
            );
        }

        $path = route(2);
        $path = base64_decode($path);
        if(file_exists($path)){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($path).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }


        break;

    case 'do-backup-files':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'util/backup_files',
                'e',
                'Sorry, this feature is disabled in the Demo mode!'
            );
        }

        clxPerformLongProcess();

        $out_name = date('Y-m-d-H-i-s') . '_' . _raid();

        try {
            ExtendedZip::zipTree(
                './',
                'storage/backups/app/' . $out_name . '.zip',
                ZipArchive::CREATE
            );
            r2(U . 'util/backup_files', 's', $_L['Created Successfully']);
        } catch (\Exception $e) {
            r2(U . 'util/backup_files', 'e', $e->getMessage());
        }

        break;

    case 'delete-backup-db':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'util/backups',
                'e',
                'Sorry, this feature is disabled in the Demo mode!'
            );
        }

        $path = route(2);

        $path = str_replace('storage:backups:db:', '', $path);

        if (file_exists('storage/backups/db/' . $path)) {
            unlink('storage/backups/db/' . $path);
            r2(U . 'util/backups', 's', $_L['delete_successful']);
        } else {
            exit('Invalid file path!');
        }

        break;

    case 'delete-backup-files':
        if (APP_STAGE == 'Demo') {
            r2(
                U . 'util/backups',
                'e',
                'Sorry, this feature is disabled in the Demo mode!'
            );
        }

        $path = route(2);

        $path = str_replace('storage:backups:app:', '', $path);

        if (file_exists('storage/backups/app/' . $path)) {
            unlink('storage/backups/app/' . $path);
            r2(U . 'util/backup_files', 's', $_L['delete_successful']);
        } else {
            exit('Invalid file path!');
        }

        break;

    default:
        echo 'action not defined';
}
