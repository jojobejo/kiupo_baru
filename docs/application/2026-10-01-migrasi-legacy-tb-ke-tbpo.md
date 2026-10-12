# Dampak Aplikasi: Migrasi Data Legacy ke `tbpo_`

Tanggal: 1 Oktober 2026  
Status: selesai pada database lokal.

## Ruang lingkup

Data dari dump legacy `u676129830_kiupo.sql` telah digabungkan ke pasangan
tabel `tbpo_*` yang sesuai. Migrasi memakai primary key sebagai identitas,
sehingga data lokal yang sudah ada tidak ditimpa dan hanya data yang belum ada
yang ditambahkan.

`tb_user` tidak dimigrasikan ke `tbpo_user`. Modul autentikasi serta role
pengguna lokal tetap menggunakan data pengguna yang sudah ada.

## Dampak fungsional

- Riwayat PO, detail PO, request non-komersil, transaksi, lampiran, dan note
  legacy kini tersedia bagi modul yang membaca tabel `tbpo_*`.
- Note direktur yang ditambahkan sebanyak 41.330 baris menyediakan histori
  PO legacy untuk penelusuran stok dan pembelian.
- Data tracking PO tidak bertambah karena tabel sumber `tb_tracking_po`
  kosong.

## Batasan

File fisik yang dirujuk oleh tabel lampiran tidak ikut disalin; hanya metadata
database yang dimigrasikan. Lakukan uji browser untuk membuka sampel lampiran,
detail PO, request, dan histori transaksi.

