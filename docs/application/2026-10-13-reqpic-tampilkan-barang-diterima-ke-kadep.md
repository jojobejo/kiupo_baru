# Penyajian Barang Diterima kepada KADEP

## Perubahan

Route `reqpicpickupapproval` kini menampilkan request PIC di departemen KADEP yang berstatus `BARANG TERSEDIA` setelah log request mencatat `BARANG DI TERIMA - ADMIN` oleh Purchasing. Request `MENUNGGU ACC PENGAMBILAN` yang sudah diajukan PIC tetap tampil seperti sebelumnya. Daftar KADEP dibatasi ke departemennya; super admin tetap dapat melihat seluruh departemen.

KADEP dapat memutuskan request yang otomatis muncul maupun pengajuan eksplisit PIC. Persetujuan mengubah status menjadi `MENUNGGU PENYERAHAN BARANG`. Penolakan untuk request yang otomatis muncul mengembalikan status ke `BARANG TERSEDIA` dan menyimpan catatan penolakan supaya tidak terus muncul sebagai permintaan baru. PIC dapat mengajukannya kembali melalui detail request.

## Cara penggunaan

1. Purchasing mengonfirmasi barang diterima pada request PIC.
2. Catatan `BARANG DI TERIMA - ADMIN` menjadi penanda agar request berstatus `BARANG TERSEDIA` muncul di `reqpicpickupapproval` milik KADEP departemen PIC tersebut.
3. KADEP membuka detail, lalu menyetujui atau menolak pengambilan.
4. Jika ditolak, PIC dapat mengajukan kembali dari detail request setelah menindaklanjuti catatan KADEP.

Tidak ada perubahan pada route atau schema database.

Jika Kadep belum memberi afirmasi atau mengubah status dalam lima menit sejak catatan `BARANG DI TERIMA - ADMIN`, Purchasing dapat meneruskan request dari status `BARANG TERSEDIA` atau `MENUNGGU ACC PENGAMBILAN` ke `MENUNGGU PENYERAHAN BARANG` melalui detail request PIC.
