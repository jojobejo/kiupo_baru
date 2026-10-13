# Timeout Approval Pengambilan Request PIC

## Perubahan

Pada route `reqpic/detreqbarangpic/{kode}`, status `BARANG TERSEDIA` maupun `MENUNGGU ACC PENGAMBILAN` memberi KADEP waktu lima menit sejak Purchasing mengonfirmasi barang tersedia. Waktu tersebut diambil dari catatan `BARANG DI TERIMA - ADMIN` pada log request. Jika KADEP belum memberi afirmasi atau mengubah status setelah lima menit, Purchasing melihat aksi untuk meneruskan status menjadi `MENUNGGU PENYERAHAN BARANG`, sehingga proses konfirmasi penyerahan dapat berjalan. Jika KADEP lebih dulu menyetujui atau menolak, aksi timeout tidak lagi berlaku karena status request sudah berubah.

Endpoint aksi: `POST reqpic/pickup-approval/process-timeout`. Aksi dibatasi untuk Purchasing/super admin, memeriksa ulang batas waktu di server, memperbarui hanya bila status masih menunggu, dan menulis catatan audit.

## Cara penggunaan

1. Purchasing menyetujui request seperti alur yang berlaku.
2. PIC mengajukan persetujuan pengambilan setelah barang berstatus `BARANG TERSEDIA`.
3. KADEP dapat menyetujui atau menolak dalam lima menit.
4. Jika belum ada keputusan setelah lima menit dari konfirmasi barang tersedia oleh Purchasing, Purchasing membuka detail request dan memilih **TERUSKAN PENGAMBILAN KE PROSES PENYERAHAN**. Ini berlaku baik PIC sudah mengajukan ke KADEP maupun request masih `BARANG TERSEDIA`.
5. Purchasing melanjutkan konfirmasi penyerahan melalui alur `reqpicpickup` yang sudah tersedia.

## Validasi

Waktu dihitung server menggunakan zona `Asia/Jakarta`. Endpoint mengulang pemeriksaan status dan waktu untuk mencegah proses dini atau aksi atas request yang sudah diputuskan KADEP. Tidak ada perubahan batas approval request awal.
