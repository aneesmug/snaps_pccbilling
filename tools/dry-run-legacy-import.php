<?php

require_once __DIR__ . '/../system/config.php';

function legacyImportTables()
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

function legacyDuplicateSignatureColumns()
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
            ['vtoken'],
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

function legacyBuildRowSignature($tableName, array $rowData)
{
    $signatureMap = legacyDuplicateSignatureColumns();
    if (!isset($signatureMap[$tableName])) {
        return null;
    }

    foreach ($signatureMap[$tableName] as $candidateColumns) {
        $parts = [];
        $isValid = true;

        foreach ($candidateColumns as $columnName) {
            $value = array_key_exists($columnName, $rowData)
                ? trim((string) $rowData[$columnName])
                : '';

            if ($value === '') {
                $isValid = false;
                break;
            }

            $parts[] = $columnName . '=' . mb_strtolower($value, 'UTF-8');
        }

        if ($isValid) {
            return implode('|', $parts);
        }
    }

    return null;
}

function legacySanitizeStatement($statement)
{
    $statement = str_replace(
        '<span class="redactor-invisible-space">',
        '',
        $statement
    );
    $statement = str_replace('</span>', '', $statement);

    return $statement;
}

function dryRunLegacyImport($sqlFilePath)
{
    $result = [
        'planned_rows' => 0,
        'skipped_statements' => 0,
        'failed_statements' => 0,
        'duplicates_skipped' => 0,
        'tables' => [],
        'duplicate_tables' => [],
        'errors' => [],
    ];

    $allowedTables = legacyImportTables();
    $allowedTableMap = array_fill_keys($allowedTables, true);

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_errno) {
        $result['failed_statements']++;
        $result['errors'][] = 'DB connection failed: ' . $mysqli->connect_error;
        return $result;
    }

    $mysqli->set_charset('utf8mb4');

    $tableSchemas = [];
    foreach ($allowedTables as $tableName) {
        $tableSchemas[$tableName] = [];
        $query = "
            SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $mysqli->real_escape_string($tableName) . "'
            ORDER BY ORDINAL_POSITION
        ";

        $schemaResult = $mysqli->query($query);
        if ($schemaResult) {
            while ($column = $schemaResult->fetch_assoc()) {
                $tableSchemas[$tableName][$column['COLUMN_NAME']] = $column;
            }
            $schemaResult->free();
        }
    }

    $fallbackValueForColumn = function ($columnMeta) {
        if ($columnMeta['COLUMN_DEFAULT'] !== null) {
            return $columnMeta['COLUMN_DEFAULT'];
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

        if (in_array($dataType, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit'], true)) {
            return '0';
        }

        if ($dataType === 'date') {
            return '0000-00-00';
        }

        if (in_array($dataType, ['datetime', 'timestamp', 'time', 'year'], true)) {
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
        $length = strlen($valuesSql);

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesSql[$i];
            $buffer .= $char;

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === '"') {
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
                }
            }
        }

        return $tuples;
    };

    $parseInsertStatement = function ($statement) {
        $trimmed = trim($statement);
        if (!preg_match('/^(INSERT|REPLACE)\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*VALUES\s*(.+);$/is', $trimmed, $matches)) {
            return null;
        }

        return [
            'table' => $matches[2],
            'columns' => array_map(static function ($column) {
                return trim($column, " `\r\n\t");
            }, explode(',', $matches[3])),
            'values_sql' => $matches[4],
        ];
    };

    $seenSignatures = [];

    $processStatement = function ($statement, $statementTable, $statementStartLine, $statementEndLine) use (&$result, $parseInsertStatement, $tableSchemas, $splitSqlTuples, $fallbackValueForColumn, &$seenSignatures, $mysqli) {
        $statement = legacySanitizeStatement($statement);
        $parsed = $parseInsertStatement($statement);
        if (!$parsed) {
            $result['skipped_statements']++;
            return;
        }

        $targetSchema = $tableSchemas[$statementTable] ?? [];
        $sourceColumns = $parsed['columns'];
        $sourceColumnMap = array_fill_keys($sourceColumns, true);
        $targetColumns = [];

        foreach ($sourceColumns as $columnName) {
            if (isset($targetSchema[$columnName])) {
                $targetColumns[] = $columnName;
            }
        }

        foreach ($targetSchema as $columnName => $columnMeta) {
            if (isset($sourceColumnMap[$columnName]) || stripos($columnMeta['EXTRA'] ?? '', 'auto_increment') !== false) {
                continue;
            }

            if (($columnMeta['IS_NULLABLE'] ?? 'YES') === 'NO' && $columnMeta['COLUMN_DEFAULT'] === null) {
                $targetColumns[] = $columnName;
            }
        }

        $targetColumns = array_values(array_unique($targetColumns));
        if (empty($targetColumns)) {
            $result['skipped_statements']++;
            return;
        }

        $tuples = $splitSqlTuples($parsed['values_sql']);
        foreach ($tuples as $tupleSql) {
            $tupleBody = preg_replace('/^\(|\)$/', '', trim($tupleSql));
            $rawValues = str_getcsv($tupleBody, ',', '"', '\\');

            if (count($rawValues) !== count($sourceColumns)) {
                $result['failed_statements']++;
                $preview = substr(str_replace(["\r", "\n"], ['\\r', '\\n'], $tupleBody), 0, 240);
                $result['errors'][] = "Column/value count mismatch for {$statementTable} at lines {$statementStartLine}-{$statementEndLine}. Preview: {$preview}";
                continue;
            }

            $sourceData = [];
            foreach ($sourceColumns as $index => $columnName) {
                $rawValue = $rawValues[$index];
                $sourceData[$columnName] = strtoupper(trim($rawValue)) === 'NULL' ? null : $rawValue;
            }

            $targetRowData = [];
            foreach ($targetColumns as $columnName) {
                $columnMeta = $targetSchema[$columnName];
                $value = array_key_exists($columnName, $sourceData) ? $sourceData[$columnName] : $fallbackValueForColumn($columnMeta);
                if ($value === null && ($columnMeta['IS_NULLABLE'] ?? 'YES') === 'NO' && $columnMeta['COLUMN_DEFAULT'] === null) {
                    $value = $fallbackValueForColumn($columnMeta);
                }
                $targetRowData[$columnName] = $value;
            }

            $signature = legacyBuildRowSignature($statementTable, $targetRowData);
            if ($signature !== null) {
                if (!isset($seenSignatures[$statementTable])) {
                    $seenSignatures[$statementTable] = [];
                }

                if (isset($seenSignatures[$statementTable][$signature])) {
                    $result['duplicates_skipped']++;
                    $result['duplicate_tables'][$statementTable] = ($result['duplicate_tables'][$statementTable] ?? 0) + 1;
                    continue;
                }

                $signatureColumns = explode('|', $signature);
                $conditions = [];
                foreach ($signatureColumns as $signaturePart) {
                    [$columnName] = explode('=', $signaturePart, 2);
                    $rawValue = $targetRowData[$columnName] ?? null;
                    if ($rawValue === null || !isset($targetSchema[$columnName])) {
                        continue;
                    }

                    $conditions[] = '`' . $mysqli->real_escape_string($columnName) . '` = ' . "'" . $mysqli->real_escape_string((string) $rawValue) . "'";
                }

                if (!empty($conditions)) {
                    $duplicateCheckSql = 'SELECT id FROM `' . $statementTable . '` WHERE ' . implode(' AND ', $conditions) . ' LIMIT 1';
                    $duplicateResult = $mysqli->query($duplicateCheckSql);
                    if ($duplicateResult && $duplicateResult->num_rows > 0) {
                        $duplicateResult->free();
                        $seenSignatures[$statementTable][$signature] = true;
                        $result['duplicates_skipped']++;
                        $result['duplicate_tables'][$statementTable] = ($result['duplicate_tables'][$statementTable] ?? 0) + 1;
                        continue;
                    }
                    if ($duplicateResult) {
                        $duplicateResult->free();
                    }
                }

                $seenSignatures[$statementTable][$signature] = true;
            }

            $result['planned_rows']++;
            $result['tables'][$statementTable] = ($result['tables'][$statementTable] ?? 0) + 1;
        }
    };

    $handle = fopen($sqlFilePath, 'r');
    if (!$handle) {
        $result['failed_statements']++;
        $result['errors'][] = 'Unable to read SQL file.';
        $mysqli->close();
        return $result;
    }

    $collectingStatement = false;
    $statement = '';
    $statementTable = '';
    $statementStartLine = 0;
    $lineNumber = 0;

    while (($line = fgets($handle)) !== false) {
        $lineNumber++;
        if (!$collectingStatement) {
            if (preg_match('/^\s*(INSERT|REPLACE)\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i', $line, $matches)) {
                $tableName = $matches[2];
                if (isset($allowedTableMap[$tableName])) {
                    $collectingStatement = true;
                    $statementTable = $tableName;
                    $statement = $line;
                    $statementStartLine = $lineNumber;

                    if (preg_match('/;\s*$/', $line)) {
                        $processStatement($statement, $statementTable, $statementStartLine, $lineNumber);
                        $collectingStatement = false;
                        $statement = '';
                        $statementTable = '';
                        $statementStartLine = 0;
                    }
                } else {
                    $result['skipped_statements']++;
                }
            }
            continue;
        }

        $statement .= $line;
        if (preg_match('/;\s*$/', $line)) {
            $processStatement($statement, $statementTable, $statementStartLine, $lineNumber);
            $collectingStatement = false;
            $statement = '';
            $statementTable = '';
            $statementStartLine = 0;
        }
    }

    fclose($handle);
    $mysqli->close();

    return $result;
}

if ($argc < 2) {
    fwrite(STDERR, "Usage: php tools/dry-run-legacy-import.php <sql-file-path>\n");
    exit(1);
}

$sqlFilePath = $argv[1];
if (!file_exists($sqlFilePath)) {
    fwrite(STDERR, "SQL file not found: {$sqlFilePath}\n");
    exit(1);
}

$summary = dryRunLegacyImport($sqlFilePath);
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;