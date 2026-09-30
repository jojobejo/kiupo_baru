<?php
/**
 * Merge tabel legacy tb_* ke tabel aplikasi tbpo_* yang ekuivalen.
 * Tabel user dan tabel yang tidak mempunyai pasangan tbpo_* selalu dilewati.
 *
 * Usage:
 * php scripts/migrate_legacy_tb_to_tbpo.php <source_database> <target_database> --apply
 */

if (PHP_SAPI !== 'cli' || $argc !== 4 || $argv[3] !== '--apply') {
    fwrite(STDERR, "Usage: php scripts/migrate_legacy_tb_to_tbpo.php <source_database> <target_database> --apply\n");
    exit(1);
}

list(, $sourceDatabase, $targetDatabase) = $argv;
foreach (array($sourceDatabase, $targetDatabase) as $database) {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
        fwrite(STDERR, "Nama database tidak valid.\n");
        exit(1);
    }
}

$db = new mysqli('localhost', 'root', '');
if ($db->connect_errno) {
    fwrite(STDERR, "Koneksi database gagal: {$db->connect_error}\n");
    exit(1);
}
$db->set_charset('utf8mb4');

function identifier($value)
{
    return '`' . str_replace('`', '``', $value) . '`';
}

function columns(mysqli $db, $database, $table)
{
    $sql = 'SELECT column_name, column_key FROM information_schema.columns'
        . ' WHERE table_schema=? AND table_name=? ORDER BY ordinal_position';
    $statement = $db->prepare($sql);
    $statement->bind_param('ss', $database, $table);
    $statement->execute();
    $result = $statement->get_result();
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[$row['column_name']] = $row['column_key'];
    }
    $statement->close();
    return $rows;
}

$sourceTables = $db->query("SELECT table_name FROM information_schema.tables"
    . " WHERE table_schema='" . $db->real_escape_string($sourceDatabase) . "'"
    . " AND table_type='BASE TABLE' AND table_name REGEXP '^tb_' ORDER BY table_name");
if (!$sourceTables) {
    throw new RuntimeException($db->error);
}

$db->begin_transaction();
try {
    $summary = array();
    while ($source = $sourceTables->fetch_assoc()) {
        $sourceTable = $source['table_name'];
        if ($sourceTable === 'tb_user') {
            continue;
        }
        $targetTable = 'tbpo_' . substr($sourceTable, 3);
        $targetExists = $db->query('SELECT 1 FROM information_schema.tables WHERE table_schema='
            . "'" . $db->real_escape_string($targetDatabase) . "' AND table_name='"
            . $db->real_escape_string($targetTable) . "' LIMIT 1");
        if (!$targetExists || $targetExists->num_rows === 0) {
            continue;
        }

        $sourceColumns = columns($db, $sourceDatabase, $sourceTable);
        $targetColumns = columns($db, $targetDatabase, $targetTable);
        $common = array_values(array_intersect(array_keys($sourceColumns), array_keys($targetColumns)));
        if (!$common) {
            continue;
        }

        $insertColumns = $common;
        $selectColumns = array_map('identifier', $common);
        // Skema barang versi baru menambahkan merk_barang sebagai kolom wajib
        // tanpa default. Dump legacy belum menyimpan atribut ini.
        if ($targetTable === 'tbpo_barang' && !isset($sourceColumns['merk_barang'])) {
            $insertColumns[] = 'merk_barang';
            $selectColumns[] = "''";
        }
        $columnList = implode(',', array_map('identifier', $insertColumns));
        $updates = array();
        foreach ($common as $column) {
            if ($targetColumns[$column] !== 'PRI') {
                $quoted = identifier($column);
                $updates[] = $quoted . '=VALUES(' . $quoted . ')';
            }
        }
        $sql = 'INSERT INTO ' . identifier($targetDatabase) . '.' . identifier($targetTable)
            . ' (' . $columnList . ') SELECT ' . implode(',', $selectColumns) . ' FROM '
            . identifier($sourceDatabase) . '.' . identifier($sourceTable);
        $sql .= $updates ? ' ON DUPLICATE KEY UPDATE ' . implode(',', $updates) : ' ON DUPLICATE KEY UPDATE ' . identifier($common[0]) . '=' . identifier($common[0]);
        if (!$db->query($sql)) {
            throw new RuntimeException($targetTable . ': ' . $db->error);
        }
        $sourceCount = $db->query('SELECT COUNT(*) AS total FROM ' . identifier($sourceDatabase) . '.' . identifier($sourceTable))->fetch_assoc();
        $targetCount = $db->query('SELECT COUNT(*) AS total FROM ' . identifier($targetDatabase) . '.' . identifier($targetTable))->fetch_assoc();
        $summary[] = array($sourceTable, $targetTable, (int) $sourceCount['total'], (int) $targetCount['total']);
    }
    $db->commit();
} catch (Throwable $exception) {
    $db->rollback();
    fwrite(STDERR, "Migrasi dibatalkan: {$exception->getMessage()}\n");
    exit(1);
}

foreach ($summary as $row) {
    echo implode("\t", $row) . PHP_EOL;
}
