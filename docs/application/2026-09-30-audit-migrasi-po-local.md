# Audit Kesiapan Migrasi Modul PO Lokal

Tanggal audit: 30 September 2026  
Status: **read-only** — tidak ada source code, struktur, maupun data database yang diubah.

## Tujuan

Membandingkan dump `database/exports/kiucoid_karismaerp_local_without_tbpo_user_20260929.sql` dengan database aktif lokal `kiucoid_karismaerp_local`, dengan fokus pada kontrak tabel `tbpo_` yang dipakai modul PO.

## Dampak aplikasi

Database lokal belum siap menjalankan alur PO Jasa dan persediaan non-komersil versi aplikasi saat ini secara penuh. Model `M_PojasaCore`, `M_PojasaExecution`, dan `M_PojasaDemo` secara langsung memakai tabel PO Jasa yang belum tersedia. Kondisi ini akan menghasilkan kegagalan query pada tahap scope/material, dokumen, persetujuan, SPK, pembelian, penerimaan, dan pencatatan biaya.

Tabel PO inti (`tbpo_barang`, `tbpo_suplier`, `tbpo_req_nk`, `tbpo_po_nk`, `tbpo_detail_po_nk`, dan `tbpo_transaksi`) sudah ada. Tabel tersebut tidak boleh ditimpa dari dump karena telah memiliki data lokal dan tidak ada bukti bahwa dump adalah sumber kebenaran yang lebih baru untuk setiap primary key.

## Kesenjangan fungsional yang perlu ditutup

- 28 tabel PO Jasa belum tersedia, termasuk approval, scope, material, dokumen, SPK, purchase submission, penerimaan stok, audit biaya, pickup, dan arsip perubahan SPK.
- Tabel `tbpo_req_nk_supporting_file` belum ada; ini dibutuhkan untuk dokumen pendukung Request PIC.
- Empat tabel LIFO non-komersil belum ada, padahal dump memuat data LIFO dan source code menyediakan proses rekonstruksi persediaan.
- Struktur tabel PO Jasa yang telah ada masih tertinggal kolom workflow seperti versi status/revisi, estimasi jasa/bahan, harga nyata, dan jejak PIC/purchasing.

## Keputusan yang direkomendasikan

Lanjutkan dalam dua tahap terpisah setelah persetujuan pemilik database:

1. Jalankan migrasi struktur secara bertahap dan idempotent dari folder `database/sql`, bukan mengimpor dump penuh.
2. Setelah semua preflight lolos, lakukan migrasi data **additive dan per tabel**, memakai natural key/primary key yang telah direview. Data LIFO harus diproses terakhir setelah referensi PO, detail PO, produk, dan transaksi tervalidasi.

Tidak ada perubahan aplikasi dalam audit ini. Dokumen database pendamping menjelaskan daftar tabel, kolom, data, serta prosedur preflight/rollback.

