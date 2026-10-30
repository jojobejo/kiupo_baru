# Catatan Development Workflow PO Jasa

Tanggal: 27 Oktober 2026  
Ruang lingkup: aplikasi PO Jasa (PIC, Purchasing, Kadep, Dirop, Direktur, dan tracking pelaksanaan).

## Perubahan aplikasi

### PIC

- Form request tidak lagi meminta vendor atau harga estimasi/subtotal. PIC mengisi tujuan, jadwal, scope pekerjaan, alat/bahan, jumlah, satuan, keterangan, dan dokumen pendukung.
- Pengajuan ditolak server jika belum memiliki minimal satu dokumen aktif. Dokumen wajib tetap melekat pada request dan dapat diakses approver.
- Daftar **Request PO Jasa Saya** menampilkan Tanggal, Scope Pekerjaan, Tujuan Pekerjaan, Status, Status PO Pembelian, dan Aksi.
- PIC tetap menjadi pemilik input progress setelah SPK terbit.

### Purchasing

- Review awal memiliki keputusan **OK** dan **Revisi**. **OK** hanya dapat dilakukan setelah data review tersimpan, vendor aktif atau vendor manual tersedia, serta seluruh baris scope/material aktif memiliki harga Purchasing.
- **OK** meneruskan request ke Kadep. **Revisi** mengembalikan request ke PIC.
- Tabel scope dan material menampilkan Detail Scope, QTY, Harga Satuan, Total Harga, dan Keterangan. Purchasing dapat memperbarui/menghapus baris serta menambah baris sesuai kewenangan.
- Nilai estimasi Purchasing hanya dihitung dari harga satuan Purchasing; harga estimasi PIC tidak menjadi fallback. Keterangan Purchasing per baris tidak lagi digunakan di UI.
- Ringkasan biaya menampilkan **Disetujui** dan **Total Biaya**; biaya request dan selisih aktual dihilangkan dari tampilan tersebut.
- Daftar Purchasing mempertahankan request lintas tahap untuk tracking. Hak aksi tetap mengikuti status dan peran.
- Setelah SPK, halaman Purchasing menampilkan progress PIC, penerimaan material, harga nyata pembelian, dan biaya aktual.

### Approval dan dokumen

- Kadep melihat dokumen PIC langsung di tampilan data final approval.
- Setelah Kadep ACC, request diarahkan ke Dirop bila departemen request adalah IT atau GA, atau subtotal jasa pada harga Purchasing paling banyak Rp500.000. Selain kondisi tersebut, request diarahkan ke Direktur.
- Dokumen request juga tersedia di tab Dokumen dan melalui aksi unduh sesuai hak akses.
- Approval menyimpan timeline aksi dan status; request yang telah diproses tetap dapat dilihat pada halaman Approval & Tracking PO Jasa.

## Alur perubahan status

`PIC submit → Purchasing review → (OK) Kadep → Dirop atau Direktur → Purchasing terbitkan SPK/PO → PIC update progress → Purchasing tracking biaya/pembelian`

Purchasing dapat mengembalikan request ke PIC melalui **Revisi** pada review awal. Revisi yang diminta Kadep/Dirop/Direktur tetap mengikuti alur perbaikan Purchasing yang sudah tersedia.

## Dampak database

Tidak ada perubahan skema database dan tidak ada migrasi yang perlu dijalankan. Implementasi memakai kolom/tabel workflow PO Jasa yang sudah tersedia. Detail pemakaian skema dicatat terpisah pada [catatan database](../database/2026-10-27-workflow-po-jasa-no-schema-change.md).

## Validasi dan batasan

- Perubahan ditinjau dari source code dan jalur controller/model/view yang terkait.
- Lint, automated test, authenticated browser UAT untuk setiap peran, dan validasi transaksi finansial belum dijalankan pada pekerjaan ini.
- Sebelum rilis, lakukan UAT dengan akun PIC, Purchasing, Kadep, Dirop, Direktur, dan Admin, termasuk request jasa bernilai tepat Rp500.000 serta di atas Rp500.000, request IT/GA, revisi, penerbitan SPK, update progress, dan harga aktual pembelian.

