START TRANSACTION;

INSERT INTO `tbpo_realisasi_harganyata_log`
    (`kd_po_nk`, `id_det_po_nk`, `aksi`, `keterangan`, `kd_user`, `nama_user`, `created_at`)
SELECT r.`kd_po_nk`, r.`id_det_po_nk`, 'PENYESUAIAN_TANPA_DIREKTUR',
       'Harga nyata langsung berlaku dan tercatat oleh Purchasing; approval Direktur tidak diperlukan.',
       COALESCE(r.`updated_by`, ''), '', NOW()
FROM `tbpo_realisasi_detail_po_nk` r
WHERE r.`status_approval_harga` = 'PENDING_DIREKTUR';

UPDATE `tbpo_realisasi_detail_po_nk`
SET `status_approval_harga` = 'DISETUJUI_OTOMATIS',
    `approved_by` = COALESCE(NULLIF(`approved_by`, ''), `updated_by`),
    `approved_at` = COALESCE(`approved_at`, `updated_at`, NOW()),
    `updated_at` = NOW()
WHERE `status_approval_harga` = 'PENDING_DIREKTUR';

COMMIT;
