# Login KIU PO Menggunakan Akun Klien Pilihan Portal

## Alur

Portal tetap mengautentikasi user dan memeriksa izin aplikasi. Pada pertukaran authorization code server-ke-server, Portal menyertakan username akun aplikasi yang ditetapkan pada klaim opsional `data.client_account_username`. KIU PO memvalidasi transaksi SSO, mencari tepat satu baris dengan username tersebut pada `tbpo_user`, memastikan level akses lokal 1–6, lalu membuat session KIU PO untuk user lokal itu.

Jika klien belum memiliki akun impor, KIU PO meneruskan alur lama dengan mencari user lokal dari username Portal. Bila klien memiliki akun impor namun admin belum menetapkan salah satunya, Portal menolak login. Username pilihan yang tidak ditemukan, tidak unik, atau tidak memiliki level akses yang didukung juga akan ditolak.

## Keamanan

Portal tidak mengirim password akun terpilih. Password tidak dibutuhkan untuk membentuk session setelah Portal membuktikan identitas dan izin melalui code sekali pakai, PKCE, client secret, dan HTTPS. Session dibuat oleh KIU PO sendiri, dan role serta hak akses tetap berasal dari database KIU PO. Username akun lokal yang sama tidak boleh terikat ke subject Portal lain.

Setelah session lokal berhasil dibentuk, KIU PO mengirim event konfirmasi ke `POST {portal_base_url}/api/sso/login-event`. Portal memvalidasi event terhadap akun yang masih ditetapkan untuk user dan aplikasi tersebut. KIU PO membatalkan session dan menghentikan login jika pencatatan gagal. Portal mencatat waktu, aplikasi, user Portal, username akun lokal, IP, dan user-agent; password dan ID session tidak disimpan.

## Perubahan dan validasi

- `application/libraries/Portal_sso.php`: validasi dan meneruskan klaim username opsional.
- `application/models/Auth/M_Portal_sso.php`: memetakan subject Portal ke user KIU PO berdasarkan username pilihan bila tersedia.
- Tidak ada schema database baru atau perubahan password `tbpo_user`.
- Login akun pilihan memerlukan tabel histori yang diterapkan pada database Portal; KIU PO tidak menambah tabel atau kolom.
- UAT browser dan login dengan akun data aktual belum dilakukan.
