# Dampak Database: Login dengan Akun Klien Pilihan

Perubahan SSO menggunakan tabel `tbpo_sso_identity` dan `tbpo_user` yang sudah ada. Tidak ada tabel, kolom, indeks, maupun perubahan password yang ditambahkan. Untuk klaim akun pilihan, relasi `external_sub` yang ada dapat diperbarui ke `id_user` lokal yang ditetapkan jika akun itu belum dipetakan ke subject lain.

Konfirmasi histori login akun pilihan dicatat pada database Portal melalui `api/sso/login-event`. Tidak ada perubahan database di KIU PO; penerapan tabel histori dilakukan pada repo/database KarismaERP Portal.

