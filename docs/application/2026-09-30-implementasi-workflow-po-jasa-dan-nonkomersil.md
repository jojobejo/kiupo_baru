# Implementasi workflow PO Jasa dan PO Non-Komersil

Tanggal: 30 September 2026  
Status: diterapkan pada database lokal `kiucoid_karismaerp_local`; UAT peran masih diperlukan.

## Hasil

Workflow PO Jasa yang sebelumnya terputus setelah konfirmasi PIC sudah disambungkan. Urutannya sekarang adalah:

`Purchasing review awal -> PIC konfirmasi -> KADEP -> Direktur Operasional -> Direktur -> Purchasing terbitkan SPK dan PO pembelian`.

Purchasing hanya melakukan review bisnis pada tahap awal. Setelah ACC Direktur, Purchasing hanya menerbitkan dokumen operasional (SPK dan PO pembelian otomatis), bukan memberi approval baru. Pemisahan ini menjaga akuntabilitas PIC, KADEP, Purchasing, dan Direktur.

Perbaikan berada di `application/helpers/pojasa_status_helper.php`: aksi `ACC` pada `MENUNGGU_KONFIRMASI_PIC` sekarang menuju `MENUNGGU_KADEP`, bukan kembali ke `MENUNGGU_PURCHASING`. Pemeriksaan CLI `PojasaPhase9Check` juga diselaraskan untuk menguji seluruh rantai sampai SPK dan PO pembelian diterbitkan.

## Cara menggunakan

1. PIC membuat dan mengajukan draft PO Jasa dari `/pojasa/pic`.
2. Purchasing menyimpan review awal dan memilih **Update** agar PIC menerima konfirmasi perubahan.
3. PIC memilih **ACC** untuk meneruskan request kepada KADEP, atau **Revisi** untuk kembali memperbaiki request.
4. KADEP, Direktur Operasional, dan Direktur memproses approval berurutan pada `/pojasa/workflow`.
5. Setelah ACC Direktur, Purchasing membuka workflow dan memilih **Terbitkan SPK & PO Pembelian**. Sistem menerbitkan SPK dan membentuk PO non-komersil sumber `PO_JASA` secara idempoten.
6. Untuk PO non-komersil, Request PIC sekarang mendukung dokumen pendukung; stok LIFO tersedia setelah proses rebuild dan UAT stok dijalankan.

## Validasi yang sudah dilakukan

- Semua migration PO Jasa fase 2–17, dokumen Request PIC, timeout, LIFO, serta kolom keputusan harga Purchasing berhasil diterapkan pada database lokal.
- Kontrak tabel PO Jasa, Request PIC, dan LIFO tersedia setelah migrasi.
- Uji rollback CLI `PojasaPhase9Check` lulus setelah perbaikan: urutan status, otorisasi, penerbitan SPK/PO pembelian, notifikasi, dan rollback bersih semuanya terverifikasi. Untuk eksekusi CLI di lingkungan ini, `HTTP_HOST=localhost` dipasang agar konfigurasi legacy tidak memunculkan notice.

## Batas UAT

Validasi ini tidak menggantikan UAT browser terautentikasi untuk setiap peran, upload dokumen, penerbitan SPK, pembentukan PO pembelian, penerimaan stok, dan pembukuan harga LIFO.
