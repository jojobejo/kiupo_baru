# Rencana Migrasi Data `tbpo_`

## Ruang lingkup

Rencana ini hanya mencakup struktur dan data tabel berawalan `tbpo_` dari dump `database/exports/kiucoid_karismaerp_local_without_tbpo_user_20260929.sql` ke `kiucoid_karismaerp_local`.

`tbpo_user` secara eksplisit di luar ruang lingkup: dump sumber memang tidak memuat tabel tersebut. Tabel non-`tbpo_`, file upload fisik, dan perubahan source code juga tidak termasuk.

## Prinsip keputusan

- Dump adalah kandidat sumber data, bukan otoritas untuk menimpa data lokal.
- Tidak ada `DROP`, `TRUNCATE`, `REPLACE`, atau overwrite otomatis.
- Record dengan key yang sama tetapi nilai bisnis berbeda dihentikan dan dimasukkan ke daftar keputusan bisnis.
- Migrasi data baru dapat dimulai setelah struktur PO dan preflight database dinyatakan bersih.
- Data dijalankan per domain dan per batch yang dapat di-rollback; tidak ada import dump penuh.

## Dampak pemakaian modul

Setelah struktur dan data tervalidasi, modul PO Jasa dapat melanjutkan alur PIC → KADEP → Purchasing → Direktur, termasuk scope pekerjaan, material, SPK, purchase submission, penerimaan, dan audit biaya. Modul Request PIC dapat menyimpan dokumen pendukung. Modul stok non-komersil dapat menggunakan penilaian LIFO.

## Tahap operasional

1. Aktifkan maintenance window dan hentikan penulisan modul PO selama backup, staging, serta cutover.
2. Buat backup konsisten database lokal dan simpan checksum/count sebelum migrasi.
3. Terapkan struktur secara additive sesuai runbook database pendamping.
4. Jalankan dry-run staging. Hanya hasil yang seluruhnya `NEW` atau `IDENTICAL` yang boleh masuk batch commit.
5. Jalankan data master, lalu dokumen transaksi header-detail, kemudian LIFO. Jalankan validasi setiap domain sebelum lanjut.
6. UAT dengan akun PIC, KADEP, Purchasing, dan Direktur. Uji juga penerimaan dan mutasi stok non-komersil.
7. Bila UAT gagal, rollback batch domain terakhir dari backup/staging; jangan memulihkan full dump.

Tidak ada perubahan aplikasi dalam pembuatan rencana ini. Detail tabel, urutan, key, dan acceptance criteria ada pada dokumen database.

