-- Add five empty application tables from the app-only export.
USE `kiucoid_kiupo`;

CREATE TABLE IF NOT EXISTS `tbpo_admin_activity_log` (
 `id_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `table_name` VARCHAR(100) NOT NULL,
 `table_label` VARCHAR(150) NOT NULL, `primary_key` VARCHAR(100) NOT NULL, `primary_value` VARCHAR(150) NOT NULL,
 `action` ENUM('CREATE','UPDATE','DELETE') NOT NULL, `old_data` LONGTEXT NULL, `new_data` LONGTEXT NULL,
 `kode_user` VARCHAR(100) NULL, `username` VARCHAR(100) NULL, `nama_user` VARCHAR(150) NULL,
 `ip_address` VARCHAR(45) NULL, `user_agent` VARCHAR(255) NULL,
 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id_log`),
 KEY `idx_admin_activity_table` (`table_name`), KEY `idx_admin_activity_user` (`kode_user`),
 KEY `idx_admin_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_arsip_evident_ponk` (
 `id_arsip_evident` INT(11) NOT NULL AUTO_INCREMENT, `kd_po_nk` VARCHAR(25) NOT NULL,
 `jenis_evident` VARCHAR(60) NOT NULL, `keterangan` TEXT NULL, `file_path` VARCHAR(255) NOT NULL,
 `file_name` VARCHAR(255) NOT NULL, `file_uploaded` VARCHAR(255) NOT NULL, `file_original` VARCHAR(255) NULL,
 `file_ext` VARCHAR(20) NULL, `file_size` DECIMAL(12,2) NOT NULL DEFAULT 0, `source_table` VARCHAR(60) NULL,
 `source_id` INT(11) NULL, `user_upload` VARCHAR(25) NOT NULL,
 `create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id_arsip_evident`),
 KEY `idx_arsip_evident_ponk_kd` (`kd_po_nk`), KEY `idx_arsip_evident_ponk_jenis` (`jenis_evident`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_stock_opname_nk` (
 `id_stock_opname` INT(11) NOT NULL AUTO_INCREMENT, `tgl_opname` DATE NOT NULL, `catatan` TEXT NULL,
 `total_item` INT(11) NOT NULL DEFAULT 0, `total_selisih_plus` DECIMAL(18,2) NOT NULL DEFAULT 0,
 `total_selisih_minus` DECIMAL(18,2) NOT NULL DEFAULT 0, `created_by` VARCHAR(25) NOT NULL,
 `created_name` VARCHAR(255) NULL, `created_at` DATETIME NOT NULL, PRIMARY KEY (`id_stock_opname`),
 KEY `idx_stock_opname_nk_tgl` (`tgl_opname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_stock_opname_nk_detail` (
 `id_stock_opname_detail` INT(11) NOT NULL AUTO_INCREMENT, `id_stock_opname` INT(11) NOT NULL,
 `kode_barang` VARCHAR(25) NOT NULL, `kode_barangs` VARCHAR(25) NOT NULL, `nama_barang` VARCHAR(255) NOT NULL,
 `qty_sistem` DECIMAL(18,2) NOT NULL DEFAULT 0, `qty_fisik` DECIMAL(18,2) NOT NULL DEFAULT 0,
 `selisih` DECIMAL(18,2) NOT NULL DEFAULT 0, `satuan` VARCHAR(60) NULL, `keterangan` TEXT NULL,
 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id_stock_opname_detail`),
 KEY `idx_stock_opname_nk_detail_header` (`id_stock_opname`), KEY `idx_stock_opname_nk_detail_barang` (`kode_barang`,`kode_barangs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbpo_tmp_diskon_nk` (
 `id_tmp_diskon` INT(11) NOT NULL AUTO_INCREMENT, `kd_user` VARCHAR(25) NOT NULL,
 `kd_po_nk` VARCHAR(25) NOT NULL, `deskripsi` VARCHAR(255) NOT NULL,
 `nominal` DECIMAL(18,2) NOT NULL DEFAULT 0, `create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id_tmp_diskon`), KEY `idx_tmp_diskon_nk_user` (`kd_user`), KEY `idx_tmp_diskon_nk_po` (`kd_po_nk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
