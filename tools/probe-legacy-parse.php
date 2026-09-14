<?php

$file = 'D:/xampp/htdocs/ibilling_sute/backup_2026-04-27_11_52_24.sql';
$handle = fopen($file, 'r');

$collecting = false;
$statement = '';
$lineNumber = 0;
$statementStart = 0;

while (($line = fgets($handle)) !== false) {
    $lineNumber++;

    if (!$collecting) {
        if (preg_match('/^\s*(INSERT|REPLACE)\s+INTO\s+`?(sys_invoiceitems)`?/i', $line)) {
            $collecting = true;
            $statement = $line;
            $statementStart = $lineNumber;

            if (preg_match('/;\s*$/', $line)) {
                $collecting = false;
            }
        }
        continue;
    }

    $statement .= $line;
    if (!preg_match('/;\s*$/', $line)) {
        continue;
    }

    $collecting = false;

    if (!preg_match('/^(INSERT|REPLACE)\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*VALUES\s*(.+);$/is', trim($statement), $matches)) {
        echo 'regex-fail at lines ' . $statementStart . '-' . $lineNumber . PHP_EOL;
        $statement = '';
        continue;
    }

    $sourceColumns = array_map(static function ($column) {
        return trim($column, " `\r\n\t");
    }, explode(',', $matches[3]));

    $valuesSql = $matches[4];
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
                $tuples[] = trim($buffer);
                $buffer = '';
            }
        }
    }

    foreach ($tuples as $tuple) {
        $tupleBody = preg_replace('/^\(|\)$/', '', trim($tuple));
        $rawValues = str_getcsv($tupleBody, ',', '"', '\\');
        if (count($rawValues) !== count($sourceColumns)) {
            echo 'mismatch at lines ' . $statementStart . '-' . $lineNumber . ' columns=' . count($sourceColumns) . ' values=' . count($rawValues) . PHP_EOL;
            echo substr(str_replace(["\r", "\n"], ['\\r', '\\n'], $tupleBody), 0, 300) . PHP_EOL;
            echo '---' . PHP_EOL;
        }
    }

    $statement = '';
}

fclose($handle);
