# Runbook Migrasi Struktur dan Data `tbpo_`

## Otorisasi dan batas aman

Status dokumen: **rencana, belum dieksekusi**. Jalankan hanya setelah backup diverifikasi dan approval eksplisit diberikan.

Target: `kiucoid_karismaerp_local` (MariaDB 10.4).  
Sumber: `database/exports/kiucoid_karismaerp_local_without_tbpo_user_20260929.sql`.

Semua query staging memakai prefiks sementara yang tidak diawali `tbpo_`, misalnya `stg_20260930_*`, agar tidak mengubah kontrak aplikasi. Semua tabel sumber/target yang dimigrasikan harus tetap berawalan `tbpo_`.

## Tahap 0 — backup dan baseline

1. Buat backup konsisten seluruh database, lalu simpan di lokasi terpisah dari dump sumber.
2. Catat `COUNT(*)`, `MAX(primary_key)`, struktur (`SHOW CREATE TABLE`), dan checksum bisnis untuk setiap tabel yang masuk batch.
3. Jalankan `database/sql/hostinger/po_nonkomersil_preflight.sql` dengan database target. Hasil awal yang sudah terbukti masih memuat kekurangan struktur adalah 12 tabel inti dan 8 kolom inti.
4. Hentikan bila backup gagal, database aktif bukan `kiucoid_karismaerp_local`, atau versi server bukan MariaDB/MySQL kompatibel.

## Tahap 1 — struktur terlebih dahulu

Gunakan migration SQL repository yang additive/idempotent; jangan mengekstrak lalu mengimpor `CREATE TABLE` dari dump secara langsung. Urutan berikut menjaga foreign key PO Jasa:

1. Request PIC: `reqpic_supporting_documents_20260916.sql`, `reqpic_prevent_cross_pic_merge_20260916.sql`, `reqpic_pickup_status_length_20260917.sql`, `reqpic_kadep_timeout_workflow_20260923.sql`, lalu `reqpic_timeout_history_20260925.sql`.
2. PO Jasa fondasi: `pojasa_phase2_foundation_20260910.sql`, phase 3, phase 4, phase 5, phase 6, phase 7, phase 8, dan phase 9 dalam urutan nomor.
3. PO Jasa lanjutan: phase 10, 11, 12, 13, 14, 15, 16, dan 17 dalam urutan nomor, lalu migration pendukung tanggal 7 September (`pojasa_request_detail_split`, `pojasa_detail_purchasing_input`, dan `pojasa_direktur_oprasional`) bila precheck menunjukkan kolomnya belum ada.
4. LIFO: `stock_nonkomersil_lifo_foundation_20260918.sql`, lalu `stock_nonkomersil_lifo_batch_nk_rename_20260925.sql` hanya jika table legacy `tbpo_stock_lifo_batch` benar-benar ada.
5. Kebijakan harga: `realisasi_harga_tanpa_approval_direktur_20260925.sql`, `realisasi_harga_keputusan_purchasing_20260925.sql`; script sinkronisasi harga tidak dijalankan sebelum data LIFO tervalidasi.

Setelah tahap ini, ulangi preflight. Hentikan proses data bila ada hasil `missing_table` atau `missing_column`.

## Tahap 2 — staging dan klasifikasi konflik

Untuk setiap tabel sumber yang mempunyai statement `INSERT`, muat dahulu ke staging dengan struktur yang sama tanpa foreign key. Klasifikasikan setiap record berdasarkan key bisnis berikut:

| Domain | Tabel | Key pembanding awal | Aksi yang diizinkan |
| --- | --- | --- | --- |
| Master barang komersil | `tbpo_barang` | `kode_barang` | Insert hanya jika baru; konflik nilai dikaji manual |
| Master barang non-komersil | `tbpo_barang_nk` | `kd_barang` | Insert baru; validasi lokasi/satuan |
| Supplier | `tbpo_suplier` | `kd_suplier` | Insert baru; jangan menimpa identitas/alamat |
| Master pendukung | `tbpo_satuan`, `tbpo_kat_br`, `tbpo_set_tax`, `tbpo_notetemplate` | primary/natural key tabel | Insert baru atau identical saja |
| Request non-komersil | `tbpo_req_nk` | `kd_po_nk` | Header sebelum detail |
| Detail request | `tbpo_detail_req` | primary key dan relasi request | Hanya setelah header tersedia |
| PO non-komersil | `tbpo_po_nk` | `kd_po_nk` | Header sebelum detail/realisasi |
| Detail PO non-komersil | `tbpo_detail_po_nk` | primary key dan `kd_po_nk` | Validasi barang dan header |
| Realisasi harga | `tbpo_realisasi_po_nk`, `tbpo_realisasi_detail_po_nk`, `tbpo_realisasi_harganyata_log` | referensi PO/detail | Hanya setelah PO/detail |
| File dan catatan | `tbpo_file_nk`, `tbpo_file_bukti_beli`, `tbpo_note_*` | primary key + referensi dokumen | Validasi path fisik; tidak menciptakan file |
| Transaksi stok | `tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin` | `id_transnk` plus kode referensi | Hanya bila referensi bisnis cocok |
| LIFO | empat tabel `tbpo_stock_lifo_*` | primary key dan referensi batch/transaksi | Paling akhir |

Setiap staging menghasilkan tiga set: `NEW`, `IDENTICAL`, dan `CONFLICT`. Batch commit hanya boleh berisi `NEW`; `IDENTICAL` dicatat lalu dilewati; `CONFLICT` wajib memperoleh keputusan bisnis tertulis.

## Tahap 3 — urutan commit data

1. Master: `tbpo_kat_br` → `tbpo_satuan` → `tbpo_suplier` → `tbpo_barang` → `tbpo_barang_nk` → `tbpo_barang_nk_lokasi` → pengaturan/generator yang relevan.
2. Request: `tbpo_req_nk` → `tbpo_detail_req` → `tbpo_req_nk_supporting_file` → `tbpo_file_nk`.
3. PO: `tbpo_po` → `tbpo_detail_po`; lalu `tbpo_po_nk` → `tbpo_detail_po_nk` → tabel realisasi harga.
4. Transaksi: `tbpo_transaksi` dan tabel pendukung yang memiliki referensi telah lolos precheck. Data trashbin hanya diimpor bila diperlukan sebagai audit, bukan untuk menghidupkan kembali transaksi.
5. LIFO: `tbpo_stock_lifo_batch_nk` → `tbpo_stock_lifo_allocation` → `tbpo_stock_lifo_harga_log` → `tbpo_stock_lifo_rebuild_issue`.

Dump tidak memuat statement `INSERT` untuk tabel PO Jasa yang belum tersedia. Karena itu tahap struktur PO Jasa tidak mengimpor histori PO Jasa baru dari dump; tahap tersebut menjaga enam request PO Jasa lokal yang sudah ada agar kompatibel dengan struktur terbaru.

## Validasi wajib per batch

- Jumlah `NEW`, `IDENTICAL`, dan `CONFLICT` terdokumentasi.
- Tidak ada orphan pada relasi request-detail, PO-detail, detail barang, maupun LIFO ke transaksi/detail PO.
- Nilai `qty`, harga, total, status, dan tanggal tidak berubah pada record lokal yang konflik.
- `AUTO_INCREMENT` minimal lebih tinggi daripada `MAX(id)` hasil commit.
- Preflight PO non-komersil tetap bersih setelah struktur dan data.
- UAT: Request PIC, approval KADEP/Purchasing/Direktur, pembelian, penerimaan, serta stok masuk-keluar LIFO.

## Rollback

Jalankan rollback hanya untuk batch terakhir dan dari mapping primary key yang dihasilkan saat commit. Pulihkan tabel target dari backup hanya jika batch tidak dapat dihapus secara aman. Jangan menjalankan dump sumber sebagai rollback dan jangan melakukan `DELETE` lintas tabel tanpa mapping batch yang telah diverifikasi.

## Kriteria selesai

Migrasi dianggap selesai hanya bila seluruh batch berstatus committed/validated, konflik berjumlah nol atau memiliki keputusan tertulis, preflight tidak menghasilkan gap, dan UAT peran PO serta LIFO lolos.

