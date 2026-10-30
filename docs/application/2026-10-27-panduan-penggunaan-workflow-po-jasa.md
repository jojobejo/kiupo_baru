# Panduan Penggunaan Workflow PO Jasa

Tanggal: 27 Oktober 2026  
Panduan ini mengikuti workflow PO Jasa yang berjalan di aplikasi. Menu dapat berbeda posisi sesuai konfigurasi hak akses; rute aplikasi utama dicantumkan di bawah.

## Ringkasan proses

1. PIC membuat dan mengajukan request beserta scope, kebutuhan alat/bahan, jadwal, dan dokumen pendukung.
2. Purchasing memeriksa vendor dan harga, lalu memilih **OK** untuk meneruskan ke Kadep atau **Revisi** untuk mengembalikan ke PIC.
3. Kadep menilai kebutuhan dan dokumen. Setelah ACC, request bernilai jasa sampai dengan Rp500.000, request departemen IT, dan request departemen GA diteruskan ke Dirop. Request lain diteruskan ke Direktur.
4. Setelah approval pimpinan, Purchasing menerbitkan SPK dan memproses PO Pembelian untuk material yang diperlukan.
5. PIC memperbarui progress pekerjaan dan konfirmasi penerimaan material. Purchasing memantau progress, harga pembelian nyata, dan biaya aktual.

## PIC

**Menu/rute:** daftar request PO Jasa (`/pojasa/pic`) → buat baru (`/pojasa/pic/create`) → detail/edit (`/pojasa/pic/detail/{kode}` atau `/pojasa/pic/edit/{kode}`).

### Membuat request

1. Isi tujuan pekerjaan, lokasi bila tersedia, dan tanggal mulai serta target selesai.
2. Tambahkan minimal satu scope pekerjaan dengan nama, uraian/keterangan, QTY, dan satuan.
3. Tambahkan alat/bahan bila diperlukan. Pilih barang dari katalog jika tersedia; jika tidak, isi sebagai item manual. Harga dan subtotal tidak diisi oleh PIC.
4. Unggah minimal satu dokumen pendukung sebelum mengajukan. Pastikan dokumen menjelaskan kebutuhan pekerjaan dan dapat dibaca approver.
5. Simpan draft untuk dilanjutkan kemudian, atau pilih **Ajukan Request** untuk mengirim ke Purchasing.

Daftar **Request PO Jasa Saya** berisi kolom Tanggal, Scope Pekerjaan, Tujuan Pekerjaan, Status, Status PO Pembelian, dan Aksi. Buka detail untuk melihat dokumen, histori approval/revisi, status SPK, dan aktivitas lanjutan.

### Jika Purchasing meminta revisi

1. Buka request berstatus revisi dari daftar request.
2. Baca alasan revisi dan catatan Purchasing.
3. Perbaiki tujuan, jadwal, scope/material atau dokumen yang relevan.
4. Ajukan kembali. Periksa status agar yakin request sudah kembali ke alur Purchasing.

### Setelah SPK terbit

1. Buka detail request yang berstatus **SPK Terbit** atau **Dalam Pengerjaan**.
2. Catat progress secara berkala: tanggal, persentase, status pekerjaan, aktivitas, kendala, dan tindak lanjut.
3. Lampirkan bukti progress bila tersedia.
4. Konfirmasi material yang diterima sesuai jumlah aktual; jangan mengonfirmasi material yang belum diterima.
5. Perbarui progress hingga pekerjaan selesai. Status progress tidak boleh mundur dari catatan sebelumnya.

## Purchasing

**Menu/rute:** **Approval & Tracking PO Jasa** (`/pojasa/workflow`); review vendor/perbandingan dan proses pembelian tersedia pada detail Purchasing (`/pojasa/purchasing/detail/{kode}`).

### Review awal

1. Buka request berstatus **Menunggu Purchasing Awal**.
2. Periksa scope pekerjaan, jumlah/satuan, keterangan, material, jadwal, dokumen PIC, dan tujuan pekerjaan.
3. Isi/pilih vendor melalui bagian perbandingan/seleksi vendor. Sistem menerima vendor aktif atau vendor manual yang sudah dicatat.
4. Isi harga satuan untuk semua baris scope dan material aktif. Total dihitung dari QTY × harga satuan Purchasing.
5. Simpan bagian vendor/jadwal dan review harga yang berubah sampai status review menunjukkan tersimpan.
6. Pilih **OK** untuk meneruskan request ke Kadep. Pilih **Revisi** dan tulis alasan yang jelas bila PIC harus memperbaiki request.

Harga seluruh baris wajib tersedia sebelum keputusan **OK** dapat diproses. Nilai Rp0 boleh dicatat eksplisit bila memang tidak dikenakan biaya; harga kosong tidak dianggap nol.

### Setelah approval pimpinan

1. Buka request berstatus **Menunggu Penerbitan Purchasing**.
2. Periksa kembali vendor, harga final, jadwal, dokumen, ketersediaan stok, dan kebutuhan PO Pembelian.
3. Pastikan draft pembelian aktif sesuai kebutuhan; kuantitas stok yang telah direservasi mengurangi jumlah yang perlu dibeli.
4. Pilih **Terbitkan SPK & PO**. Periksa nomor SPK dan status pengajuan PO Pembelian pada detail request.

### Tracking setelah SPK

1. Gunakan daftar **Approval & Tracking PO Jasa** untuk menemukan request yang telah diproses. Request yang tidak lagi menunggu tindakan tetap ditampilkan untuk tracking.
2. Masuk ke detail Purchasing untuk memantau progress PIC dan penerimaan material.
3. Periksa harga nyata pada detail PO Pembelian/penerimaan, lalu verifikasi biaya aktual dan bukti transaksinya.
4. Ringkasan menunjukkan nilai **Disetujui** dan **Total Biaya**. Rincian aktual tetap tersedia di tabel biaya aktual.
5. Jika biaya aktual memerlukan koreksi atau melampaui anggaran, ikuti alur verifikasi/perubahan SPK yang disediakan aplikasi dan simpan alasan serta bukti.

## Kadep

**Menu/rute:** **Approval & Tracking PO Jasa** (`/pojasa/workflow`).

1. Buka request berstatus **Menunggu Kadep** atau **Pending Kadep** dari departemen yang menjadi kewenangan Anda.
2. Tinjau tujuan, detail final scope/material, harga Purchasing, jadwal, dan bagian **Dokumen dari PIC**. Unduh dokumen bila diperlukan.
3. Pilih **ACC** untuk melanjutkan ke Dirop atau Direktur sesuai aturan routing; pilih **Revisi** untuk meminta Purchasing melakukan perbaikan terarah; pilih **Pending** jika membutuhkan waktu sebelum keputusan; pilih **Reject** hanya bila request ditolak.
4. Isi alasan/catatan pada tindakan yang meminta klarifikasi, revisi, atau penolakan agar jejak keputusan dapat dipahami.

## Dirop (Direktur Operasional)

**Menu/rute:** **Approval & Tracking PO Jasa** (`/pojasa/workflow`).

1. Buka request berstatus **Menunggu Dirop**.
2. Tinjau dokumen PIC, detail final, nilai jasa, dan keputusan Kadep.
3. Pilih **ACC** untuk meneruskan ke Direktur, **Revisi** untuk mengembalikan ke Purchasing dengan catatan, atau **Reject** untuk penolakan terminal.

Request IT, GA, atau jasa sampai dengan Rp500.000 masuk ke tahap ini setelah Kadep ACC. Untuk dokumen, gunakan bagian **Dokumen dari PIC** atau tab Dokumen.

## Direktur

**Menu/rute:** **Approval & Tracking PO Jasa** (`/pojasa/workflow`).

1. Buka request berstatus **Menunggu Direktur**.
2. Tinjau tujuan, nilai final Purchasing, scope/material, dokumen PIC, dan histori persetujuan.
3. Pilih **ACC** untuk mengizinkan Purchasing menerbitkan SPK/PO, **Revisi** untuk perbaikan melalui Purchasing, atau **Reject** untuk menolak request.

Request IT, GA, atau jasa sampai dengan Rp500.000 akan melewati Dirop sebelum Direktur.

## Admin dan pengguna pendukung

- Admin dapat membantu memantau request dan menjaga dukungan operasional sesuai hak akses aplikasi. Keputusan bisnis tetap dilakukan role approver yang dituju.
- Status approval, histori revisi, dokumen, notifikasi, dan status PO Pembelian membantu Admin menelusuri kendala tanpa mengubah jejak keputusan.
- Purchasing adalah pemilik data vendor/harga, penerbitan SPK, tindak lanjut PO Pembelian, dan verifikasi biaya aktual. PIC adalah pemilik progress pekerjaan dan konfirmasi penerimaan material.

## Makna tindakan utama

| Tindakan | Dampak |
| --- | --- |
| OK oleh Purchasing | Melanjutkan review awal ke Kadep setelah review tersimpan dan data vendor/harga lengkap. |
| Revisi | Mengembalikan request ke pihak yang harus melakukan perbaikan. Pada review awal Purchasing, targetnya PIC. |
| ACC oleh Kadep | Melanjutkan ke Dirop atau Direktur sesuai departemen/nilai jasa. |
| ACC oleh Dirop | Melanjutkan ke Direktur. |
| ACC oleh Direktur | Memindahkan request ke antrian penerbitan Purchasing. |
| Terbitkan SPK & PO | Menerbitkan SPK dan menjalankan integrasi PO Pembelian yang tersedia. |
| Reject | Mengakhiri request dengan status penolakan terminal. |
| Pending oleh Kadep | Menahan request pada tahap Kadep hingga ada keputusan berikutnya. |

## Batasan penggunaan

- Jangan memasukkan harga PIC; harga final awal dicatat oleh Purchasing.
- Pastikan dokumen ada sebelum submit. Dokumen aktual dan bukti transaksi perlu dilampirkan pada aktivitas yang relevan.
- Simpan setiap review sebelum menekan **OK**. Jika harga/vendor belum lengkap, sistem akan menolak keputusan tersebut.
- Gunakan histori approval untuk melihat siapa melakukan tindakan, kapan, status sebelum/sesudah, dan catatan.

