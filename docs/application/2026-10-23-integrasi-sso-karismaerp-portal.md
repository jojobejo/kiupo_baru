# Integrasi SSO KarismaERP Portal

Kontrak endpoint, parameter API, persiapan administrator, langkah pengguna, dan kebutuhan database diringkas di [Dokumentasi API SSO](2026-10-24-api-sso-karismaerp-portal.md).

## Tujuan dan batas modul

Menambahkan login KIU PO melalui KarismaERP Portal menggunakan authorization code dan PKCE S256. Integrasi ini hanya mengubah jalur login dan pembuatan session lokal; modul operasional, role, menu, dan hak akses KIU PO tetap ditentukan aplikasi.

## Alur penggunaan

1. Pengguna membuka URL peluncuran KIU PO dari kartu Portal. Jika SSO dikonfigurasi, halaman awal KIU PO selalu mengarahkan browser ke **KarismaERP Portal**, termasuk saat browser masih memiliki session lokal dari akun lain.
2. KIU PO membuat `state` serta `code_verifier` acak di session server, membuat challenge S256, lalu mengarahkan browser ke `/sso/authorize`.
3. Portal memvalidasi callback terdaftar, user, dan izin aplikasi. Portal mengembalikan `code` dan `state` ke callback KIU PO.
4. KIU PO mencocokkan state, mengirim `code`, `client_secret`, callback, dan verifier dari backend ke `/api/sso/token` melalui HTTPS.
5. KIU PO memeriksa `success`, `app_code`, `state`, dan klaim `sub`, lalu menghubungkan subject ke akun `tbpo_user` yang telah tersedia.
6. KIU PO membuat session lokal dari data user dan role lokal yang sudah ada, lalu membuka dashboard.

Logout KIU PO mengakhiri session lokal dan menahan redirect SSO otomatis pada browser tersebut supaya portal yang masih login tidak langsung membuat session KIU PO baru. Pengguna dapat melanjutkan SSO secara manual dari halaman login. Portal belum menyediakan single logout, token API, role, atau pencabutan session KIU PO yang sudah berjalan.

## Konfigurasi deployment

Daftarkan klien pada halaman **Integrasi SSO** di portal KarismaERP. Portal memiliki URL peluncuran aplikasi dan callback protokol yang berbeda. Untuk setiap environment, isikan URL berikut (sesuaikan host dan subfolder jika berbeda):

```text
URL peluncuran: https://10.10.10.26/kiupobaru/
URL callback:   https://10.10.10.26/kiupobaru/sso/callback
```

Atur environment pada Apache/PHP atau secret manager; jangan menaruh secret di repository. Saat konfigurasi lengkap, membuka route login/default aplikasi langsung memulai SSO. Login username/password tetap bisa dibuka lewat `Auth?local=1` dan ditampilkan jika SSO gagal. Route mulai dan callback menolak request HTTP biasa:

| Environment | Nilai |
| --- | --- |
| `KARISMAERP_SSO_CLIENT_ID` | `app_code` yang terdaftar; contoh dokumentasi portal adalah `kiu_po` |
| `KARISMAERP_SSO_CLIENT_SECRET` | Secret klien dari portal, khusus backend |
| `KARISMAERP_SSO_PORTAL_BASE_URL` | Base URL portal berakhiran slash; nilai lokal default `https://localhost/karismaerp/` |
| `KARISMAERP_SSO_REDIRECT_URI` | Callback HTTPS persis seperti yang didaftarkan |
| `KARISMAERP_SSO_CA_BUNDLE` | Opsional, path CA PEM untuk sertifikat CA privat; validasi sertifikat tidak dimatikan |

Secret juga dapat diberikan melalui `application/config/portal_sso.local.php` pada server; file ini diabaikan Git. Environment server tetap menjadi pilihan utama. Restart Apache setelah environment berubah. Di database portal, terapkan `docs/database/portal_sso_launch_uri_20261024.sql` sebelum mengisi URL peluncuran. Di database KIU PO, terapkan skema pemetaan subject yang tercatat pada dokumen database. Pastikan ekstensi PHP cURL aktif dan akun lokal pengguna sudah tersedia dengan role yang benar. User tanpa izin aplikasi di portal ditolak sebelum callback.

## Aturan akun dan otorisasi

- Tidak ada auto-provisioning akun lokal. Login pertama mencari satu akun `tbpo_user` dengan username yang sama seperti klaim portal, lalu menyimpan `sub` sebagai pengenal tetap. Username ganda atau akun yang tidak ditemukan ditolak.
- Login berikutnya mencari berdasarkan `sub`, sehingga perubahan username portal tidak memindahkan mapping ke akun lain.
- `aksess_lv`, `departement`, dan penetapan super admin selalu dibaca dari `tbpo_user`; klaim portal tidak dapat menaikkan role lokal.
- KIU PO hanya menerima level akses 1 sampai 6 yang didukung dashboard; level di luar daftar dan akun yang tidak ditemukan ditolak. Setelah diterima, `dashboard` memilih tampilan/menu sesuai level lokal (1 super admin, 2 purchasing, 3/6 direktur, 4 karyawan, 5 KADEP; kode pengguna khusus tetap mengikuti aturan dashboard).
- Akses aplikasi portal adalah gerbang login. Pemeriksaan role, menu, aksi, dan cakupan data tetap tanggung jawab KIU PO.
- Token/code, client secret, verifier, serta respons token tidak ditulis ke log maupun dikirim ke browser.

## Troubleshooting dan UAT sebelum rilis

- Perbaikan pembacaan konfigurasi dan alert tidak mengubah skema database; tabel mapping tetap mengikuti dokumen database SSO.
- Redirect otomatis tidak mulai: periksa `KARISMAERP_SSO_CLIENT_SECRET`, callback, client ID, base URL, serta PHP cURL. Jika SSO belum dikonfigurasi, login lokal tetap tersedia.
- `invalid_request` / `invalid_grant`: cocokkan app code, callback, secret, kode sekali pakai (batas 120 detik), verifier, dan waktu server.
- `access_denied`: administrator portal perlu memberi izin akses aplikasi kepada akun tersebut.
- Jika SSO belum mulai dan halaman langsung menampilkan login: konfigurasi CI3 harus dibaca dari grup `portal_sso`. Controller sekarang memulai redirect otomatis dan menampilkan kegagalan konfigurasi sebagai alert.
- Jika pertukaran tiket gagal, halaman login menampilkan alert terpisah untuk gangguan jaringan, verifikasi TLS, atau respons/izin portal yang tidak valid. Jangan menampilkan kode tiket, secret, verifier, atau respons token kepada pengguna.
- Gagal TLS: pasang CA server portal pada trust store PHP atau atur `KARISMAERP_SSO_CA_BUNDLE`; jangan menonaktifkan verifikasi peer/host.
- Verifikasi lokal 24 Oktober 2026 menemukan sertifikat XAMPP `C:\xampp\apache\conf\ssl.crt\server.crt` kedaluwarsa pada 8 November 2019; PHP cURL menolak token exchange (`curl_errno 60`). Sertifikat portal harus diperbarui sebelum SSO dapat menyelesaikan login; bundle CA tidak dapat mengatasi sertifikat yang kedaluwarsa.
- Pada 24 Oktober 2026 sertifikat XAMPP yang kedaluwarsa (berakhir 8 November 2019) diperbarui memakai CA lokal khusus pengembangan. Sertifikat server memiliki SAN `localhost`, `127.0.0.1`, dan `10.10.10.26`; CA publik berada di `C:\xampp\apache\conf\ssl.crt\karisma-local-root-ca.crt`. `application/config/portal_sso.local.php` menunjuk CA tersebut untuk PHP cURL. Uji koneksi dengan verifikasi peer/host aktif menghasilkan `curl_errno=0`.
- CA lokal ditambahkan ke Windows Current User Root setelah persetujuan pengguna, agar Chrome memercayai kedua hostname. Impor ini hanya berlaku untuk akun Windows saat ini; jangan melewati peringatan browser. Kunci privat CA berada di profil lokal pengembang dan tidak masuk repository.
- UAT browser 24 Oktober 2026: akun portal yang diminta menampilkan kartu KIU PO; klik kartu membuka tab `https://10.10.10.26/kiupobaru/dashboard` tanpa form login lokal. Session dan dashboard KIU PO terbentuk melalui SSO setelah validasi akses portal dan mapping akun lokal. Browser tidak lagi menampilkan interstitial TLS.
- Peluncuran SSO selalu memvalidasi ulang identitas Portal. Sebelum redirect, identitas lokal lama dihapus dan session ID diganti; jika user Portal ditolak atau mapping akun gagal, session akun sebelumnya tidak tertinggal. Session baru hanya dibuat dari akun lokal dan level akses yang berhasil dipetakan.
- Mapping ditolak: pastikan akun lokal sudah dibuat dan username hanya dimiliki satu baris di `tbpo_user`.

UAT minimum: klik kartu portal membuka halaman awal KIU PO; pengguna yang diizinkan dan ditolak; akun lokal hilang atau username ganda; state salah; verifier/secret/callback salah; code kedaluwarsa atau replay; client nonaktif; session lokal sesudah login; role tetap mengikuti KIU PO; logout lokal; berganti user Portal pada browser yang sebelumnya memiliki session KIU PO. Jalur login sukses awal sudah terverifikasi; pergantian akun dan skenario penolakan akses perlu UAT setelah perubahan ini.
