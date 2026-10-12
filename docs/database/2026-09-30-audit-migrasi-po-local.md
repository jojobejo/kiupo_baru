# Audit Database PO: Dump 20260929 ke Lokal

Tanggal audit: 30 September 2026  
Target terkonfigurasi: `kiucoid_karismaerp_local` pada MariaDB `10.4.27`  
Sumber: `database/exports/kiucoid_karismaerp_local_without_tbpo_user_20260929.sql`  
Status: **belum dimigrasikan; audit read-only selesai**.

## Ringkasan terukur

| Metrik | Hasil |
| --- | ---: |
| Tabel `tbpo_` pada dump | 90 |
| Tabel `tbpo_` pada lokal | 58 |
| Tabel ada di dump tetapi belum ada di lokal | 34 |
| Tabel lokal yang tidak ada di dump | `tbpo_user` |
| Data PO Jasa pada dump untuk tabel yang belum ada | Tidak ada statement `INSERT` |
| Data LIFO pada dump | 9.556 baris sumber |

`tbpo_user` sengaja tidak terdapat dalam nama dump (`without_tbpo_user`); jangan pernah menggantikannya dari dump ini.

## Tabel struktur yang belum ada di lokal

### PO Jasa — 28 tabel

`tbpo_jasa_approval`, `tbpo_jasa_biaya_aktual`, `tbpo_jasa_cost_audit`, `tbpo_jasa_dokumen`, `tbpo_jasa_draft_pembelian`, `tbpo_jasa_log_aktivitas`, `tbpo_jasa_material`, `tbpo_jasa_material_usulan`, `tbpo_jasa_notifikasi`, `tbpo_jasa_number_counter`, `tbpo_jasa_pickup_detail`, `tbpo_jasa_pickup_request`, `tbpo_jasa_purchase_adjustment`, `tbpo_jasa_purchase_receipt`, `tbpo_jasa_purchase_submission`, `tbpo_jasa_purchase_submission_detail`, `tbpo_jasa_revisi`, `tbpo_jasa_scope`, `tbpo_jasa_spk`, `tbpo_jasa_spk_archive`, `tbpo_jasa_spk_change`, `tbpo_jasa_spk_change_approval`, `tbpo_jasa_spk_change_item`, `tbpo_jasa_spk_revision`, `tbpo_jasa_stock_allocation`, `tbpo_jasa_stock_receipt`, `tbpo_jasa_stock_receipt_detail`, `tbpo_jasa_vendor_comparison`.

### Request PIC — 1 tabel

`tbpo_req_nk_supporting_file`.

### LIFO non-komersil — 4 tabel

`tbpo_stock_lifo_allocation`, `tbpo_stock_lifo_batch_nk`, `tbpo_stock_lifo_harga_log`, `tbpo_stock_lifo_rebuild_issue`.

## Kolom workflow yang belum tersedia pada tabel lokal

| Tabel | Kolom yang penting untuk modul PO terbaru |
| --- | --- |
| `tbpo_jasa_request` | `status_version`, `revision_no`, `estimasi_total_jasa`, `estimasi_total_bahan`, plus metadata PIC/purchasing/direktur dan penutupan request |
| `tbpo_jasa_request_detail` | `jenis_detail`, `harga_nyata`, `keterangan_purchasing` |
| `tbpo_jasa_progress` | status sebelum/sesudah, revisi, aktivitas/kendala, evidence dokumen, token idempoten |
| `tbpo_jasa_vendor` | normalisasi nama, sumber vendor, tipe/vendor version, soft delete |
| `tbpo_req_nk` | `pickup_status` dan timestamp tahap kadep/purchasing/pickup |
| `tbpo_detail_po_nk` | referensi draft/material/SPK dan `harga_lifo` |
| `tbpo_po_nk` | referensi modul dan nomor/versi SPK |
| `tbpo_barang_nk` | `status_master` |
| `tbpo_realisasi_detail_po_nk` | harga LIFO yang dipakai dan catatan keputusan harga |

Preflight resmi yang sudah ada juga mengonfirmasi delapan gap utama: `tbpo_req_nk.pickup_status`; `tbpo_jasa_request.status_version`, `revision_no`, `estimasi_total_jasa`, `estimasi_total_bahan`; `tbpo_jasa_request_detail.jenis_detail`, `harga_nyata`; serta `tbpo_detail_po_nk.harga_lifo`.

## Posisi data sebelum migrasi

Jumlah adalah hitungan aktual lokal; angka dump adalah hasil pembacaan statement `INSERT` pada dump. Nilai ini **bukan** dasar untuk overwrite atau keputusan konflik.

| Tabel | Dump | Lokal | Risiko |
| --- | ---: | ---: | --- |
| `tbpo_barang` | 3.966 | 7.962 | Konflik master; tidak boleh full import |
| `tbpo_barang_nk` | 949 | 940 | Perlu diff berdasarkan `kd_barang` |
| `tbpo_suplier` | 309 | 307 | Perlu diff berdasarkan `kd_suplier` |
| `tbpo_req_nk` | 4.271 | 4.156 | Perlu diff berdasarkan kode request |
| `tbpo_detail_req` | 6.816 | 6.638 | Bergantung pada header request |
| `tbpo_po_nk` | 1.397 | 1.360 | Perlu diff berdasarkan `kd_po_nk` |
| `tbpo_detail_po_nk` | 2.189 | 2.118 | Bergantung pada header PO |
| `tbpo_jasa_vendor` | dump tidak menyisipkan data | 5 | Struktur saja dari dump |
| `tbpo_jasa_request` | dump tidak menyisipkan data | 6 | Struktur dan kolom harus kompatibel dengan data lokal |
| `tbpo_stock_lifo_batch_nk` | 2.692 | belum ada | Data perlu validasi referensi |
| `tbpo_stock_lifo_allocation` | 6.115 | belum ada | Data perlu validasi referensi |
| `tbpo_stock_lifo_harga_log` | 3 | belum ada | Data perlu validasi referensi |
| `tbpo_stock_lifo_rebuild_issue` | 746 | belum ada | Data audit, jangan dibuang |

## Urutan migrasi yang aman (belum dijalankan)

1. Backup konsisten database lokal dan lakukan `SHOW CREATE TABLE`/checksum pada seluruh tabel PO yang terdampak.
2. Jalankan preflight read-only `database/sql/hostinger/po_nonkomersil_preflight.sql` terhadap database lokal. Hasil awal memang akan menampilkan gap di atas.
3. Terapkan migrasi struktur sesuai urutan dependensi: `reqpic_supporting_documents_20260916.sql`; PO Jasa phase 2 sampai phase 17 sesuai urutan nomor; lalu `stock_nonkomersil_lifo_foundation_20260918.sql` dan `stock_nonkomersil_lifo_batch_nk_rename_20260925.sql` bila tabel lama memang ada.
4. Jalankan ulang preflight. Semua tabel/kolom wajib harus kosong dari hasil `missing_table` dan `missing_column`.
5. Buat staging table untuk setiap data yang akan dipindah. Bandingkan key, checksum kolom bisnis, orphan reference, dan collision terlebih dahulu. Jangan menjalankan `INSERT IGNORE` secara buta karena dapat menyembunyikan konflik data.
6. Bila disetujui, impor hanya record yang tidak ada dan record yang identik. Record dengan key sama tetapi isi berbeda harus masuk daftar keputusan bisnis, bukan ditimpa otomatis.
7. Impor LIFO setelah referensi `tbpo_transaksi`, `tbpo_detail_po_nk`, dan `tbpo_barang_nk` tervalidasi; hitung ulang saldo dan lakukan UAT transaksi masuk/keluar.
8. Gunakan satu transaksi untuk setiap batch yang dapat ditransaksikan, simpan log hasil, dan siapkan rollback berbasis backup/staging. Jangan memakai dump penuh sebagai rollback karena dapat menghapus perubahan lokal yang sah.

## Batas validasi audit

Audit ini memverifikasi metadata live lokal, struktur yang tersimpan di dump, dan keberadaan statement data. Audit belum memvalidasi browser login berperan, alur keuangan, integritas setiap foreign key, atau keputusan bisnis atas konflik record. Migrasi data tidak boleh dilakukan sebelum tahap staging dan approval eksplisit.

