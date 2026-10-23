# Skema pemetaan identitas SSO KIU PO

## Tujuan dan perubahan

Integrasi SSO memerlukan penyimpanan relasi stabil antara klaim `sub` KarismaERP Portal dan akun lokal `tbpo_user`. Perubahan menambah `tbpo_sso_identity`, satu pemetaan unik per akun lokal dan per subject portal. Tidak mengubah `tbpo_user`, data akun, maupun tabel workflow.

DDL: `database/sql/portal_sso_identity_20261023.sql`  
Rollback: `database/sql/portal_sso_identity_rollback_20261023.sql`

Kolom `id_user` adalah primary key sekaligus foreign key ke `tbpo_user.id_user`, dengan `ON DELETE RESTRICT`. `external_sub` memakai ASCII binary collation agar perbandingan subject tepat dan peka huruf besar/kecil. Subject unik mencegah satu identitas portal dipakai untuk dua akun KIU PO.

## Penerapan

1. Pastikan backup database tersedia dan `tbpo_user` memakai InnoDB serta memiliki primary key `id_user` bertipe `int(11)`.
2. Terapkan file SQL pada database KIU PO untuk environment sasaran.
3. Verifikasi tabel dan constraint terbentuk sebelum mengaktifkan kredensial SSO pada konfigurasi server.
4. Lakukan UAT autentikasi dan role aplikasi sebelum rilis.

SQL ini disiapkan sebagai migrasi; belum dijalankan terhadap database lokal maupun production pada perubahan ini. Tidak ada perubahan schema/data database yang telah diterapkan.

## Rollback

Ekspor atau backup `tbpo_sso_identity` terlebih dahulu jika mapping perlu dipertahankan, lalu jalankan file rollback. Perintah `DROP TABLE` menghapus seluruh relasi subject yang telah dibuat; setelah rollback login SSO tidak dapat menyelesaikan pemetaan sampai migrasi diterapkan kembali. Tidak ada rollback untuk data akun karena tabel `tbpo_user` tidak diubah.
