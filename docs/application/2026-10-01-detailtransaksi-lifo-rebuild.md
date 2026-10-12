# Penyajian LIFO pada Detail Transaksi Non-Komersil

Tanggal: 1 Oktober 2026  
Status: rebuild histori lokal berhasil.

## Perubahan aplikasi

`application/config/config.php` kini aman dipanggil melalui CLI. Pada mode web,
base URL tetap dibangun dari `HTTP_HOST`; pada mode CLI, aplikasi menggunakan
`localhost` dan `/index.php` sebagai fallback. Ini memungkinkan controller CLI
berjalan tanpa notice `Undefined index: HTTP_HOST`.

## Cara menggunakan detail transaksi

1. Buka `detailtransaksi/{kode_barang}`.
2. Panel **Lapisan Stok LIFO Tersisa** menampilkan batch aktif dan kuantitas
   tersisa.
3. Tab **Histori Batch LIFO Semua Transaksi** menampilkan batch aktif dan habis.
4. Tabel transaksi menampilkan harga satuan hasil alokasi dan referensi batch.
   Referensi pembelian membuka detail PO non-komersil; referensi pengambilan
   membuka detail request PIC.

## Hasil rebuild

- 2.551 batch dibentuk dari transaksi masuk.
- 5.796 alokasi dibentuk untuk transaksi keluar.
- 738 isu histori tersimpan untuk penanganan, bukan disembunyikan dari audit.

## Batasan operasional

Harga satuan hanya muncul apabila batch memiliki harga valid. Sebanyak 519
batch masih memerlukan harga dan 189 transaksi keluar tidak memiliki alokasi
penuh/apa pun karena histori masuk tidak mencukupi. Tindak lanjut harga dan
rekonsiliasi stok harus dilakukan oleh Purchasing/Keuangan sebelum nilai LIFO
dipakai sebagai dasar keputusan finansial.

