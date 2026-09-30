-- Normalize the collations of every table referenced by application/models.
--
-- This version deliberately does NOT read the database metadata catalog:
-- shared Hostinger accounts can alter their own tables but are often denied
-- metadata access. It targets the complete model table list directly.
-- Run once during a maintenance window, after taking a backup.
--
-- Missing optional tables are skipped. Permission errors and any real ALTER
-- failure are not swallowed and will still be reported by MySQL/MariaDB.

DELIMITER $$

DROP PROCEDURE IF EXISTS normalize_model_collation_table $$

CREATE PROCEDURE normalize_model_collation_table(IN p_table_name VARCHAR(64))
BEGIN
    -- 1146 = table does not exist. Some modules/tables are optional.
    DECLARE CONTINUE HANDLER FOR 1146 BEGIN END;

    -- phpMyAdmin imports can split an SQL file into separate execution units.
    -- Set this again immediately before every ALTER so the setting belongs to
    -- the same server session that rebuilds the table.
    SET FOREIGN_KEY_CHECKS = 0;

    SET @collation_sql := CONCAT(
        'ALTER TABLE `', REPLACE(p_table_name, '`', '``'),
        '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci'
    );
    PREPARE collation_statement FROM @collation_sql;
    EXECUTE collation_statement;
    DEALLOCATE PREPARE collation_statement;
END $$

DELIMITER ;

-- A collation conversion rebuilds text columns. MariaDB rejects that rebuild
-- when a column is currently referenced by an FK (for example kd_po_jasa),
-- even though every related table is converted by this same migration.
SET FOREIGN_KEY_CHECKS = 0;

CALL normalize_model_collation_table('tbpo_admin_activity_log');
CALL normalize_model_collation_table('tbpo_arsip_evident_ponk');
CALL normalize_model_collation_table('tbpo_barang');
CALL normalize_model_collation_table('tbpo_barang_nk');
CALL normalize_model_collation_table('tbpo_barang_nk_lokasi');
CALL normalize_model_collation_table('tbpo_detail_po');
CALL normalize_model_collation_table('tbpo_detail_po_nk');
CALL normalize_model_collation_table('tbpo_detail_req');
CALL normalize_model_collation_table('tbpo_diskon');
CALL normalize_model_collation_table('tbpo_diskon_merk');
CALL normalize_model_collation_table('tbpo_file_bukti_beli');
CALL normalize_model_collation_table('tbpo_file_nk');
CALL normalize_model_collation_table('tbpo_formula');
-- tbpo_formula_result has input_json with utf8mb4_bin and is intentionally
-- excluded; its model joins use numeric IDs, so it cannot cause this error.
CALL normalize_model_collation_table('tbpo_formula_variable');
CALL normalize_model_collation_table('tbpo_generate_kd');
CALL normalize_model_collation_table('tbpo_generate_kd_ponk');
CALL normalize_model_collation_table('tbpo_generateqrcode');
CALL normalize_model_collation_table('tbpo_jasa_approval');
CALL normalize_model_collation_table('tbpo_jasa_bast');
CALL normalize_model_collation_table('tbpo_jasa_biaya');
CALL normalize_model_collation_table('tbpo_jasa_biaya_aktual');
CALL normalize_model_collation_table('tbpo_jasa_cost_audit');
CALL normalize_model_collation_table('tbpo_jasa_dokumen');
CALL normalize_model_collation_table('tbpo_jasa_draft_pembelian');
CALL normalize_model_collation_table('tbpo_jasa_evaluation');
CALL normalize_model_collation_table('tbpo_jasa_file');
CALL normalize_model_collation_table('tbpo_jasa_log_aktivitas');
CALL normalize_model_collation_table('tbpo_jasa_material');
CALL normalize_model_collation_table('tbpo_jasa_material_usulan');
CALL normalize_model_collation_table('tbpo_jasa_note');
CALL normalize_model_collation_table('tbpo_jasa_notifikasi');
CALL normalize_model_collation_table('tbpo_jasa_number_counter');
CALL normalize_model_collation_table('tbpo_jasa_payment');
CALL normalize_model_collation_table('tbpo_jasa_pickup_detail');
CALL normalize_model_collation_table('tbpo_jasa_pickup_request');
CALL normalize_model_collation_table('tbpo_jasa_progress');
CALL normalize_model_collation_table('tbpo_jasa_purchase_adjustment');
CALL normalize_model_collation_table('tbpo_jasa_purchase_receipt');
CALL normalize_model_collation_table('tbpo_jasa_purchase_submission');
CALL normalize_model_collation_table('tbpo_jasa_purchase_submission_detail');
CALL normalize_model_collation_table('tbpo_jasa_request');
CALL normalize_model_collation_table('tbpo_jasa_request_detail');
CALL normalize_model_collation_table('tbpo_jasa_revisi');
CALL normalize_model_collation_table('tbpo_jasa_scope');
CALL normalize_model_collation_table('tbpo_jasa_spk');
CALL normalize_model_collation_table('tbpo_jasa_spk_archive');
CALL normalize_model_collation_table('tbpo_jasa_spk_change');
CALL normalize_model_collation_table('tbpo_jasa_spk_change_approval');
CALL normalize_model_collation_table('tbpo_jasa_spk_change_item');
CALL normalize_model_collation_table('tbpo_jasa_spk_revision');
CALL normalize_model_collation_table('tbpo_jasa_stock_allocation');
CALL normalize_model_collation_table('tbpo_jasa_stock_receipt');
CALL normalize_model_collation_table('tbpo_jasa_stock_receipt_detail');
CALL normalize_model_collation_table('tbpo_jasa_vendor');
CALL normalize_model_collation_table('tbpo_jasa_vendor_comparison');
CALL normalize_model_collation_table('tbpo_kat_br');
CALL normalize_model_collation_table('tbpo_note_barang');
CALL normalize_model_collation_table('tbpo_note_direktur');
CALL normalize_model_collation_table('tbpo_note_pembelian');
CALL normalize_model_collation_table('tbpo_notetemplate');
CALL normalize_model_collation_table('tbpo_nt_tmp_pembelian');
CALL normalize_model_collation_table('tbpo_po');
CALL normalize_model_collation_table('tbpo_po_nk');
CALL normalize_model_collation_table('tbpo_realisasi_detail_po_nk');
CALL normalize_model_collation_table('tbpo_realisasi_harganyata_log');
CALL normalize_model_collation_table('tbpo_realisasi_po_nk');
CALL normalize_model_collation_table('tbpo_req_masterbarang');
CALL normalize_model_collation_table('tbpo_req_nk');
CALL normalize_model_collation_table('tbpo_req_nk_supporting_file');
CALL normalize_model_collation_table('tbpo_satuan');
CALL normalize_model_collation_table('tbpo_set_note');
CALL normalize_model_collation_table('tbpo_set_tax');
CALL normalize_model_collation_table('tbpo_sosialisasi');
CALL normalize_model_collation_table('tbpo_stock_lifo_allocation');
CALL normalize_model_collation_table('tbpo_stock_lifo_batch_nk');
CALL normalize_model_collation_table('tbpo_stock_lifo_harga_log');
CALL normalize_model_collation_table('tbpo_stock_lifo_rebuild_issue');
CALL normalize_model_collation_table('tbpo_stock_opname_nk');
CALL normalize_model_collation_table('tbpo_stock_opname_nk_detail');
CALL normalize_model_collation_table('tbpo_suplier');
CALL normalize_model_collation_table('tbpo_tmp_diskon');
CALL normalize_model_collation_table('tbpo_tmp_diskon_nk');
CALL normalize_model_collation_table('tbpo_tmp_item');
CALL normalize_model_collation_table('tbpo_tmp_item_nk');
CALL normalize_model_collation_table('tbpo_tmp_note_barang');
CALL normalize_model_collation_table('tbpo_tmp_tax');
CALL normalize_model_collation_table('tbpo_tracking_po');
CALL normalize_model_collation_table('tbpo_transaksi');
CALL normalize_model_collation_table('tbpo_transaksi_tmp');
CALL normalize_model_collation_table('tbpo_transaksi_trashbin');
CALL normalize_model_collation_table('tbpo_user');
CALL normalize_model_collation_table('tbq_module');
CALL normalize_model_collation_table('tbq_review_pic');
CALL normalize_model_collation_table('tbq_review_q');

SET FOREIGN_KEY_CHECKS = 1;

DROP PROCEDURE normalize_model_collation_table;

-- Verify by opening the pages that previously failed. All model joins now
-- compare columns stored with utf8mb4_general_ci.
