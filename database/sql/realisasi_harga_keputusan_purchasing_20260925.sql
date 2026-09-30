-- Pilihan harga yang ditetapkan Purchasing untuk referensi batch LIFO.
ALTER TABLE `tbpo_realisasi_detail_po_nk`
    ADD COLUMN `harga_dipakai_lifo` enum('REALISASI','PENGAJUAN') NOT NULL DEFAULT 'REALISASI' AFTER `harga_nyata`,
    ADD COLUMN `catatan_keputusan_harga` text NULL AFTER `alasan_realisasi`;
