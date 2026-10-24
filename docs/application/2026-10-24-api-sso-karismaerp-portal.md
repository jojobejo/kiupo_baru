# API SSO KIU PO dengan KarismaERP Portal

## Cakupan

KIU PO adalah klien SSO. Portal mengautentikasi user dan memutuskan izin aplikasi; KIU PO memvalidasi respons, mencocokkan subject ke user lokal, lalu membentuk session memakai role KIU PO. API token dipanggil dari server KIU PO, bukan langsung dari browser.

## Kontrak endpoint

Semua endpoint wajib memakai HTTPS dan sertifikat harus dipercaya oleh browser serta PHP cURL.

| Route KIU PO | Fungsi |
| --- | --- |
| `GET /` | URL peluncuran dari kartu Portal; memulai SSO otomatis bila integrasi aktif, termasuk saat session lokal lama masih ada. |
| `GET /sso/start` | Membuat request authorization dan mengarahkan browser ke Portal. Identitas lokal lama dihapus sebelum redirect. |
| `GET /sso/callback` | Memvalidasi state dan menukar code; hanya membuat session setelah user lokal dan level akses lolos pemeriksaan. |
| `GET /logout` | Mengakhiri session KIU PO saja; session Portal tidak ikut berakhir. |

| Tahap | Endpoint | Parameter/hasil |
| --- | --- | --- |
| Otorisasi | `GET {portal_base_url}/sso/authorize` | Query: `client_id`, `redirect_uri`, `state`, `code_challenge`, `code_challenge_method=S256`. Portal memeriksa user, aplikasi, callback terdaftar, lalu mengembalikan browser ke callback. |
| Callback | `GET {redirect_uri}` | Sukses: `code` dan `state`. Penolakan: `error` (misalnya `access_denied`). KIU PO menerima callback hanya jika state cocok dengan session SSO yang masih berlaku. |
| Tukar code | `POST {portal_base_url}/api/sso/token` | `Content-Type: application/x-www-form-urlencoded`; body: `client_id`, `client_secret`, `code`, `redirect_uri`, `code_verifier`. Permintaan server-ke-server; code hanya boleh digunakan sekali dan `redirect_uri` harus persis sama. |

Respons sukses token yang diterima KIU PO berbentuk JSON dengan `success=true` dan `data` berisi `app_code`, `state`, serta `user`. User wajib berisi `sub`, `username`, `auth_source`, dan `user_id`; `sub` harus sama dengan `{auth_source}:{user_id}`. `name` opsional. KIU PO memvalidasi `app_code` dan `state` sebelum memakai klaim identitas. Respons gagal, struktur berbeda, TLS gagal, atau klaim tidak valid akan menolak login.

KIU PO menghasilkan `state` dan `code_verifier` acak untuk tiap proses, menyimpan keduanya di session server, lalu mengirim SHA-256 challenge (`S256`) ke Portal. Callback hanya berlaku 10 menit di sisi KIU PO. Secret, verifier, dan respons token tidak dikirim ke browser atau dicatat ke log.

## Persiapan administrator

1. Daftarkan klien `kiu_po` pada menu **Integrasi SSO** Portal dan aktifkan klien.
2. Isi URL peluncuran dengan `https://<host>/<subfolder>/` dan callback dengan `https://<host>/<subfolder>/sso/callback`. Contoh lokal: `https://10.10.10.26/kiupobaru/` dan `https://10.10.10.26/kiupobaru/sso/callback`.
3. Simpan client secret di environment server atau secret manager. Jangan masukkan secret ke Git atau URL.
4. Atur `KARISMAERP_SSO_CLIENT_ID`, `KARISMAERP_SSO_CLIENT_SECRET`, `KARISMAERP_SSO_PORTAL_BASE_URL`, dan `KARISMAERP_SSO_REDIRECT_URI`. `KARISMAERP_SSO_CA_BUNDLE` hanya diperlukan jika CA portal privat tidak ada di trust store PHP.
5. Pastikan PHP cURL aktif, domain HTTPS cocok dengan sertifikat, dan CA dipercaya. Jangan mematikan verifikasi TLS.
6. Siapkan akun lokal `tbpo_user` dengan username yang sama persis dan level akses 1–6. SSO tidak membuat user lokal baru dan tidak mengambil role dari Portal.
7. Terapkan migrasi `database/sql/portal_sso_identity_20261023.sql` pada database KIU PO sebelum mengaktifkan login. Lihat dokumen [skema database SSO](../database/2026-10-23-schema-pemetaan-identitas-sso.md).

## Tata cara pengguna

1. Login ke KarismaERP Portal.
2. Pilih kartu **KIU PO** yang tersedia untuk akun Anda.
3. KIU PO akan memvalidasi ulang akun Portal meskipun browser masih memiliki session lokal dari user sebelumnya.
4. Jika user Portal diizinkan, subject terpetakan ke tepat satu user lokal, dan level akses lokal didukung, KIU PO membuat session dan membuka Dashboard sesuai role lokal.
5. Jika akun/level tidak cocok atau akses Portal ditolak, login ditolak dan session user sebelumnya tidak dipakai.

Untuk berganti akun Portal, logout dari Portal lalu login sebagai akun tujuan terlebih dahulu. Melakukan logout hanya dari KIU PO tidak mengakhiri session Portal.

## Database yang diperlukan

SSO memerlukan tabel `tbpo_sso_identity` untuk mengikat `external_sub` Portal ke `tbpo_user.id_user`. `id_user` dan `external_sub` masing-masing unik; foreign key mencegah mapping ke akun yang tidak ada. Tidak ada kolom atau perubahan pada `tbpo_user`. Perbaikan alur pergantian akun tidak memerlukan perubahan skema tambahan. Jangan menjalankan rollback saat SSO aktif karena seluruh mapping akan terhapus.

## Pemeriksaan gagal login

- Redirect tidak berjalan: periksa URL peluncuran, konfigurasi SSO server, dan PHP cURL.
- `access_denied`: minta administrator Portal memberi akses aplikasi kepada user.
- `invalid_grant`: periksa secret, callback persis, code sekali pakai, verifier, dan waktu server.
- Mapping ditolak: periksa username unik di `tbpo_user`, tabel mapping, serta level akses 1–6.
- Kesalahan TLS: perbarui sertifikat host yang kedaluwarsa atau pasang CA yang benar pada trust store PHP dan browser.
