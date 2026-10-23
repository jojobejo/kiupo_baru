# Integrasi SSO KarismaERP Portal

## Tujuan dan batas modul

Menambahkan login KIU PO melalui KarismaERP Portal menggunakan authorization code dan PKCE S256. Integrasi ini hanya mengubah jalur login dan pembuatan session lokal; modul operasional, role, menu, dan hak akses KIU PO tetap ditentukan aplikasi.

## Alur penggunaan

1. Pengguna membuka KIU PO. Jika belum memiliki session lokal dan SSO dikonfigurasi, aplikasi otomatis mengarahkan browser ke **KarismaERP Portal**.
2. KIU PO membuat `state` serta `code_verifier` acak di session server, membuat challenge S256, lalu mengarahkan browser ke `/sso/authorize`.
3. Portal memvalidasi callback terdaftar, user, dan izin aplikasi. Portal mengembalikan `code` dan `state` ke callback KIU PO.
4. KIU PO mencocokkan state, mengirim `code`, `client_secret`, callback, dan verifier dari backend ke `/api/sso/token` melalui HTTPS.
5. KIU PO memeriksa `success`, `app_code`, `state`, dan klaim `sub`, lalu menghubungkan subject ke akun `tbpo_user` yang telah tersedia.
6. KIU PO membuat session lokal dari data user dan role lokal yang sudah ada, lalu membuka dashboard.

Logout KIU PO mengakhiri session lokal dan menahan redirect SSO otomatis pada browser tersebut supaya portal yang masih login tidak langsung membuat session KIU PO baru. Pengguna dapat melanjutkan SSO secara manual dari halaman login. Portal belum menyediakan single logout, token API, role, atau pencabutan session KIU PO yang sudah berjalan.

## Konfigurasi deployment

Daftarkan klien pada halaman **Integrasi SSO** di portal KarismaERP. Untuk setiap environment, daftarkan callback HTTPS persis berikut (sesuaikan host dan subfolder jika berbeda):

```text
https://localhost/kiupobaru/sso/callback
```

Atur environment pada Apache/PHP atau secret manager; jangan menaruh secret di repository. Saat konfigurasi lengkap, membuka route login/default aplikasi langsung memulai SSO. Login username/password tetap bisa dibuka lewat `Auth?local=1` dan ditampilkan jika SSO gagal. Route mulai dan callback menolak request HTTP biasa:

| Environment | Nilai |
| --- | --- |
| `KARISMAERP_SSO_CLIENT_ID` | `app_code` yang terdaftar; contoh dokumentasi portal adalah `kiu_po` |
| `KARISMAERP_SSO_CLIENT_SECRET` | Secret klien dari portal, khusus backend |
| `KARISMAERP_SSO_PORTAL_BASE_URL` | Base URL portal berakhiran slash; nilai lokal default `https://localhost/karismaerp/` |
| `KARISMAERP_SSO_REDIRECT_URI` | Callback HTTPS persis seperti yang didaftarkan |
| `KARISMAERP_SSO_CA_BUNDLE` | Opsional, path CA PEM untuk sertifikat CA privat; validasi sertifikat tidak dimatikan |

Restart Apache setelah environment berubah. Pastikan ekstensi PHP cURL aktif, CA TLS portal dipercaya oleh PHP, skema pemetaan database pada dokumen database sudah diterapkan, dan akun lokal pengguna sudah tersedia dengan role yang benar. User tanpa izin aplikasi di portal ditolak portal sebelum callback.

## Aturan akun dan otorisasi

- Tidak ada auto-provisioning akun lokal. Login pertama mencari satu akun `tbpo_user` dengan username yang sama seperti klaim portal, lalu menyimpan `sub` sebagai pengenal tetap. Username ganda atau akun yang tidak ditemukan ditolak.
- Login berikutnya mencari berdasarkan `sub`, sehingga perubahan username portal tidak memindahkan mapping ke akun lain.
- `aksess_lv`, `departement`, dan penetapan super admin selalu dibaca dari `tbpo_user`; klaim portal tidak dapat menaikkan role lokal.
- Akses aplikasi portal adalah gerbang login. Pemeriksaan role, menu, aksi, dan cakupan data tetap tanggung jawab KIU PO.
- Token/code, client secret, verifier, serta respons token tidak ditulis ke log maupun dikirim ke browser.

## Troubleshooting dan UAT sebelum rilis

- Redirect otomatis tidak mulai: periksa `KARISMAERP_SSO_CLIENT_SECRET`, callback, client ID, base URL, serta PHP cURL. Jika SSO belum dikonfigurasi, login lokal tetap tersedia.
- `invalid_request` / `invalid_grant`: cocokkan app code, callback, secret, kode sekali pakai (batas 120 detik), verifier, dan waktu server.
- `access_denied`: administrator portal perlu memberi izin akses aplikasi kepada akun tersebut.
- Gagal TLS: pasang CA server portal pada trust store PHP atau atur `KARISMAERP_SSO_CA_BUNDLE`; jangan menonaktifkan verifikasi peer/host.
- Mapping ditolak: pastikan akun lokal sudah dibuat dan username hanya dimiliki satu baris di `tbpo_user`.

UAT minimum: pengguna yang diizinkan dan ditolak; akun lokal hilang atau username ganda; state salah; verifier/secret/callback salah; code kedaluwarsa atau replay; client nonaktif; session lokal sesudah login; role tetap mengikuti KIU PO; logout lokal. Pengujian browser dengan akun portal dan aktivasi kredensial belum dilakukan.
