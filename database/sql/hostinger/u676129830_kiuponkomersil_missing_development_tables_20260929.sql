-- Additive schema migration for the PO tables present in the current
-- Kioponkomersil development database but absent from the online dump.
-- It does not replace existing tables or data.

USE `u676129830_kiuponkomersil`;

-- The online dump still uses the old LIFO batch table name.  Preserve its
-- records while aligning it with the name expected by the current code.
SET @old_lifo_table_exists := (
  SELECT COUNT(*) FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'tbpo_stock_lifo_batch'
);
SET @new_lifo_table_exists := (
  SELECT COUNT(*) FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'tbpo_stock_lifo_batch_nk'
);
SET @rename_lifo_table_sql := IF(
  @old_lifo_table_exists = 1 AND @new_lifo_table_exists = 0,
  'RENAME TABLE `tbpo_stock_lifo_batch` TO `tbpo_stock_lifo_batch_nk`',
  'SELECT 1'
);
PREPARE rename_lifo_table_statement FROM @rename_lifo_table_sql;
EXECUTE rename_lifo_table_statement;
DEALLOCATE PREPARE rename_lifo_table_statement;

CREATE TABLE IF NOT EXISTS `tbpo_barang_akun` (
  `kode_barang` varchar(30) NOT NULL,
  `kode_akun_penjualan` varchar(30) DEFAULT NULL,
  `kode_akun_persediaan` varchar(30) DEFAULT NULL,
  `kode_akun_harga_pokok` varchar(30) DEFAULT NULL,
  `kode_akun_retur_penjualan` varchar(30) DEFAULT NULL,
  `kode_akun_pengiriman_beli` varchar(30) DEFAULT NULL,
  `kode_akun_pengiriman_jual` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_barang_packaging` (
  `id_packaging` int(11) NOT NULL AUTO_INCREMENT,
  `id_barang` int(11) NOT NULL,
  `isi_kemasan` decimal(20,6) DEFAULT NULL,
  `satuan_kemasan` varchar(20) DEFAULT NULL,
  `isi_per_dos` decimal(20,6) DEFAULT NULL,
  `isi_per_inner` decimal(20,6) DEFAULT NULL,
  `inner_per_dos` decimal(20,6) DEFAULT NULL,
  `satuan_dasar` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_packaging`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_diskon_merk` (
  `id_diskon` int(11) NOT NULL AUTO_INCREMENT,
  `no_po` varchar(50) DEFAULT NULL,
  `merk_barang` varchar(255) NOT NULL,
  `satuan_diskon` enum('BOX','PCS','LTR','KG') NOT NULL,
  `nominal_diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_diskon`),
  KEY `idx_merk_barang` (`merk_barang`),
  KEY `idx_no_po` (`no_po`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_formula` (
  `id_formula` int(11) NOT NULL AUTO_INCREMENT,
  `kode_formula` varchar(50) NOT NULL,
  `nama_formula` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `formula_expression` text NOT NULL,
  `output_label` varchar(100) NOT NULL,
  `output_unit` varchar(50) DEFAULT NULL,
  `rounding_mode` enum('none','round','ceil','floor') DEFAULT 'none',
  `decimal_place` int(11) DEFAULT 2,
  `status` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_formula`),
  UNIQUE KEY `kode_formula` (`kode_formula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_formula_result` (
  `id_result` int(11) NOT NULL AUTO_INCREMENT,
  `id_po_detail` int(11) DEFAULT NULL,
  `id_formula` int(11) NOT NULL,
  `input_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `formula_expression` text COLLATE utf8mb4_general_ci NOT NULL,
  `result_value` decimal(20,6) NOT NULL,
  `result_label` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `result_unit` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_result`),
  KEY `id_formula` (`id_formula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbpo_formula_variable` (
  `id_variable` int(11) NOT NULL AUTO_INCREMENT,
  `id_formula` int(11) NOT NULL,
  `variable_key` varchar(100) NOT NULL,
  `variable_label` varchar(150) NOT NULL,
  `input_type` enum('number','decimal','currency') DEFAULT 'decimal',
  `unit` varchar(50) DEFAULT NULL,
  `default_value` decimal(20,6) DEFAULT NULL,
  `is_required` tinyint(4) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_variable`),
  KEY `id_formula` (`id_formula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- This result set must be empty after the migration.
SELECT required.table_name AS missing_table
FROM (
  SELECT 'tbpo_barang_akun' AS table_name
  UNION ALL SELECT 'tbpo_barang_packaging'
  UNION ALL SELECT 'tbpo_diskon_merk'
  UNION ALL SELECT 'tbpo_formula'
  UNION ALL SELECT 'tbpo_formula_result'
  UNION ALL SELECT 'tbpo_formula_variable'
  UNION ALL SELECT 'tbpo_stock_lifo_batch_nk'
) AS required
LEFT JOIN information_schema.tables existing_table
  ON existing_table.table_schema = DATABASE()
 AND existing_table.table_name = required.table_name
WHERE existing_table.table_name IS NULL
ORDER BY required.table_name;
