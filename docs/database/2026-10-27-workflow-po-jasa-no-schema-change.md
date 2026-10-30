# Catatan Database Workflow PO Jasa

Tanggal: 27 Oktober 2026

## Status skema

**Tidak ada perubahan skema database.** Tidak ada tabel/kolom/index/constraint baru, tidak ada kolom yang dihapus, dan tidak ada file migrasi atau SQL yang perlu dijalankan untuk perubahan aplikasi PO Jasa ini.

Implementasi menggunakan struktur yang sudah ada, termasuk:

- `tbpo_jasa_request` untuk status workflow, estimasi Purchasing, vendor terpilih, jadwal, dan nomor/tanggal SPK.
- `tbpo_jasa_scope` dan `tbpo_jasa_material` untuk detail kebutuhan serta kolom harga Purchasing yang tersedia.
- `tbpo_jasa_vendor` dan `tbpo_jasa_vendor_comparison` untuk pilihan dan perbandingan vendor.
- `tbpo_jasa_dokumen` untuk dokumen request dan bukti pelaksanaan.
- `tbpo_jasa_approval`, `tbpo_jasa_revisi`, dan `tbpo_jasa_log_aktivitas` untuk histori keputusan/revisi.
- Tabel eksekusi PO Jasa yang sudah digunakan untuk SPK, progress, reservasi/pembelian, penerimaan, dan biaya aktual.

## Dampak operasional database

- Harga Purchasing diisi pada kolom harga Purchasing yang telah tersedia pada baris scope/material. Total dihitung dari QTY × harga tersebut.
- Kewajiban dokumen dan routing approval ditegakkan oleh aplikasi; tidak memerlukan perubahan constraint database.
- Tidak ada pemutakhiran data lama yang dijalankan sebagai bagian dari pekerjaan ini.
- Keberadaan dan kompatibilitas schema aktual perlu dipastikan pada environment target sebelum deployment karena validasi koneksi/database environment tidak dilakukan.

## Dokumen terpisah

Detail perubahan source dan panduan penggunaan aplikasi ada pada:

- [Catatan development aplikasi](../application/2026-10-27-catatan-development-workflow-po-jasa.md)
- [Panduan penggunaan workflow](../application/2026-10-27-panduan-penggunaan-workflow-po-jasa.md)

