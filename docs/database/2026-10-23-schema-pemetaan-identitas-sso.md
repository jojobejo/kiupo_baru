# Skema pemetaan identitas SSO KIU PO

## Tujuan dan perubahan

Integrasi SSO memerlukan penyimpanan relasi stabil antara klaim `sub` KarismaERP Portal dan akun lokal `tbpo_user`. Perubahan menambah `tbpo_sso_identity`, satu pemetaan unik per akun lokal dan per subject portal. Tidak mengubah `tbpo_user`, data akun, maupun tabel workflow.

DDL: `database/sql/portal_sso_identity_20261023.sql`  
Rollback: `database/sql/portal_sso_identity_rollback_20261023.sql`

**Kebutuhan aktif:** tabel ini wajib ada sebelum SSO diaktifkan di setiap database environment KIU PO. Pembaruan alur SSO tanggal 24 Oktober 2026 yang selalu memeriksa ulang identitas Portal dan membersihkan session lama tidak memerlukan DDL tambahan. Tidak ada perubahan kolom `tbpo_user`.

Kolom `id_user` adalah primary key sekaligus foreign key ke `tbpo_user.id_user`, dengan `ON DELETE RESTRICT`. `external_sub` memakai ASCII binary collation agar perbandingan subject tepat dan peka huruf besar/kecil. Subject unik mencegah satu identitas portal dipakai untuk dua akun KIU PO.

## Penerapan

1. Pastikan backup database tersedia dan `tbpo_user` memakai InnoDB serta memiliki primary key `id_user` bertipe `int(11)`.
2. Terapkan file SQL pada database KIU PO untuk environment sasaran.
3. Verifikasi tabel dan constraint terbentuk sebelum mengaktifkan kredensial SSO pada konfigurasi server.
4. Lakukan UAT autentikasi dan role aplikasi sebelum rilis.

SQL ini telah diterapkan pada database pengembangan lokal `kiucoid_karismaerp_local` pada 24 Oktober 2026 setelah memverifikasi `tbpo_user` menggunakan InnoDB, `id_user` bertipe `int(11)` sebagai primary key, dan berisi 35 akun. Tabel pemetaan baru masih kosong saat penerapan. Migration production belum dijalankan; tidak ada perubahan atau pengubahan data pada `tbpo_user`.

## Rollback

Ekspor atau backup `tbpo_sso_identity` terlebih dahulu jika mapping perlu dipertahankan, lalu jalankan file rollback. Perintah `DROP TABLE` menghapus seluruh relasi subject yang telah dibuat; setelah rollback login SSO tidak dapat menyelesaikan pemetaan sampai migrasi diterapkan kembali. Tidak ada rollback untuk data akun karena tabel `tbpo_user` tidak diubah.
