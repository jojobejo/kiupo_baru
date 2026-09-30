-- Jalankan setelah realisasi_harga_keputusan_purchasing_20260925.sql.
-- Harga realisasi yang dipilih Purchasing menjadi harga batch LIFO dan
-- snapshot transaksi pengambilan yang memakai batch tersebut.

UPDATE tbpo_stock_lifo_batch_nk lb
JOIN tbpo_detail_po_nk d ON d.id_det_po_nk = lb.id_detail_po_nk
JOIN tbpo_realisasi_detail_po_nk r ON r.id_det_po_nk = lb.id_detail_po_nk
SET lb.harga_satuan = CASE WHEN r.harga_dipakai_lifo = 'PENGAJUAN' THEN d.hrg_satuan ELSE r.harga_nyata END,
    lb.dasar_harga = CASE WHEN r.harga_dipakai_lifo = 'PENGAJUAN' THEN 'PENGAJUAN' ELSE 'REALISASI' END,
    lb.status_harga = 'VALID',
    lb.catatan_harga = CASE WHEN r.harga_dipakai_lifo = 'PENGAJUAN' THEN 'Harga Purchasing (pengajuan)' ELSE 'Harga Purchasing (realisasi)' END
WHERE lb.jenis_sumber = 'PO'
  AND ((r.harga_dipakai_lifo = 'REALISASI' AND COALESCE(r.harga_nyata, 0) > 0)
       OR (r.harga_dipakai_lifo = 'PENGAJUAN' AND COALESCE(d.hrg_satuan, 0) > 0));

UPDATE tbpo_stock_lifo_allocation la
JOIN tbpo_stock_lifo_batch_nk lb ON lb.id_batch = la.id_batch
JOIN tbpo_detail_po_nk d ON d.id_det_po_nk = lb.id_detail_po_nk
JOIN tbpo_realisasi_detail_po_nk r ON r.id_det_po_nk = lb.id_detail_po_nk
SET la.harga_satuan_snapshot = CASE WHEN r.harga_dipakai_lifo = 'PENGAJUAN' THEN d.hrg_satuan ELSE r.harga_nyata END,
    la.nilai_alokasi = la.qty_alokasi * CASE WHEN r.harga_dipakai_lifo = 'PENGAJUAN' THEN d.hrg_satuan ELSE r.harga_nyata END
WHERE lb.jenis_sumber = 'PO'
  AND ((r.harga_dipakai_lifo = 'REALISASI' AND COALESCE(r.harga_nyata, 0) > 0)
       OR (r.harga_dipakai_lifo = 'PENGAJUAN' AND COALESCE(d.hrg_satuan, 0) > 0));
