# Implementasi schema workflow PO Jasa dan PO Non-Komersil

Tanggal: 30 September 2026  
Target: `kiucoid_karismaerp_local`  
Status: selesai diterapkan secara lokal.

## Backup

Sebelum migrasi, backup konsisten dibuat pada `database/backups/kiucoid_karismaerp_local_before_workflow_schema_20260930.sql`.

## Migration yang diterapkan

- PO Jasa: migration pendukung 7 September, lalu fase 2 sampai 17 secara urut.
- Request PIC: `reqpic_supporting_documents_20260916.sql`, `reqpic_pickup_status_length_20260917.sql`, `reqpic_kadep_timeout_workflow_20260923.sql`, dan `reqpic_timeout_history_20260925.sql`.
- Stok non-komersil: `stock_nonkomersil_lifo_foundation_20260918.sql` dan `stock_nonkomersil_lifo_batch_nk_rename_20260925.sql`.
- Harga realisasi: `realisasi_harga_keputusan_purchasing_20260925.sql`.

Hasil pascamigrasi: 38 tabel `tbpo_jasa%` tersedia; tabel dokumen Request PIC serta lima tabel LIFO yang diperlukan tersedia; kolom `harga_dipakai_lifo` dan `catatan_keputusan_harga` tersedia pada realisasi detail PO.

## Migration yang sengaja ditahan

`reqpic_prevent_cross_pic_merge_20260916.sql` tidak dijalankan. Preflight menemukan 45 `kd_po_nk` duplikat pada `tbpo_req_nk`; memasang indeks unik saat ini akan gagal dan berisiko menghambat histori request yang sudah ada. Rekonsiliasi pemilik data diperlukan sebelum indeks unik tersebut diterapkan.

`realisasi_harga_tanpa_approval_direktur_20260925.sql` dan `sinkron_harga_purchasing_ke_lifo_20260925.sql` juga tidak dijalankan karena keduanya memutasi status/nilai transaksi historis, bukan membentuk schema. Jalankan hanya setelah keputusan bisnis dan hasil rebuild LIFO tervalidasi.

## Langkah lanjutan terkontrol

1. Jalankan rebuild LIFO dengan `php index.php cli/StockLifoRebuild --confirm` setelah backup dan review transaksi historis.
2. Tinjau issue rebuild serta harga batch sebelum sinkronisasi harga.
3. Rekonsiliasi 45 kode Request PIC duplikat, lalu jalankan preflight dan indeks unik lintas-PIC.
4. Uji role PIC, KADEP, Purchasing, Direktur Operasional, dan Direktur menggunakan data uji sebelum perubahan diterapkan ke lingkungan lain.
