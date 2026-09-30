<?php
/**
 * Build a non-destructive data merge from a legacy Kiupo phpMyAdmin dump.
 *
 * The legacy tables use the tb_ prefix while Kioponkomersil uses tbpo_.
 * This generator extracts INSERT statements only; it never emits DROP,
 * CREATE, ALTER, or DELETE statements. Existing rows are retained with
 * INSERT IGNORE, while rows whose keys are absent are added.
 *
 * Usage:
 *   php scripts/build_kiupo_data_merge.php [source-dump] [output-file]
 */

$root = dirname(__DIR__);
$source = $argv[1] ?? '/Users/itkarisma/Downloads/u676129830_kiupo (4).sql';
$output = $argv[2] ?? $root . '/dist/u676129830_kiupo_data_merge_20260929.sql';
$database = 'u676129830_kiuponkomersil';

if (!is_file($source)) {
    fwrite(STDERR, "Source dump is missing: {$source}\n");
    exit(1);
}

$sql = file_get_contents($source);
if ($sql === false) {
    fwrite(STDERR, "Cannot read source dump: {$source}\n");
    exit(1);
}

$handle = fopen($output, 'wb');
if ($handle === false) {
    fwrite(STDERR, "Cannot write output: {$output}\n");
    exit(1);
}

fwrite($handle, "-- Data-only, additive Kiupo to Kioponkomersil migration.\n");
fwrite($handle, "-- Generated from " . basename($source) . "; do not run against another database.\n");
fwrite($handle, "USE `{$database}`;\n");
fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\nSTART TRANSACTION;\n\n");

$length = strlen($sql);
$offset = 0;
$statementCount = 0;
$tableCounts = array();

while (($start = stripos($sql, 'INSERT INTO `', $offset)) !== false) {
    $position = $start;
    $quote = null;

    while ($position < $length) {
        $character = $sql[$position];

        if ($quote !== null) {
            if ($character === '\\') {
                $position += 2;
                continue;
            }
            if ($character === $quote) {
                $quote = null;
            }
        } elseif ($character === "'" || $character === '"' || $character === '`') {
            $quote = $character;
        } elseif ($character === ';') {
            break;
        }
        $position++;
    }

    if ($position >= $length) {
        fclose($handle);
        fwrite(STDERR, "Unterminated INSERT statement in source dump.\n");
        exit(1);
    }

    $statement = substr($sql, $start, $position - $start + 1);
    if (!preg_match('/^INSERT\\s+INTO\\s+`([^`]+)`/i', $statement, $matches)) {
        $offset = $position + 1;
        continue;
    }

    $sourceTable = $matches[1];
    $targetTable = strpos($sourceTable, 'tb_') === 0
        ? 'tbpo_' . substr($sourceTable, 3)
        : $sourceTable;
    $statement = preg_replace(
        '/^INSERT\\s+INTO\\s+`[^`]+`/i',
        'INSERT IGNORE INTO `' . $targetTable . '`',
        $statement,
        1
    );

    fwrite($handle, $statement . "\n\n");
    $statementCount++;
    $tableCounts[$targetTable] = ($tableCounts[$targetTable] ?? 0) + 1;
    $offset = $position + 1;
}

fwrite($handle, "COMMIT;\nSET FOREIGN_KEY_CHECKS = 1;\n");
fclose($handle);

ksort($tableCounts);
echo "Generated {$output} with {$statementCount} INSERT statements.\n";
foreach ($tableCounts as $table => $count) {
    echo "{$table}: {$count}\n";
}
