# Eksekusi Rebuild LIFO Non-Komersil

Tanggal: 1 Oktober 2026  
Database: `kiucoid_karismaerp_local`  
Status: berhasil.

## Backup sebelum mutasi

Backup konsisten dibuat sebelum rebuild:

`database/backups/kiucoid_karismaerp_local_before_lifo_rebuild_20261001.sql`

## Perintah yang dijalankan

```powershell
C:\xampp\php\php.exe index.php cli StockLifoRebuild index --confirm
```

Catatan: pada CodeIgniter CLI, `index` harus menjadi method sebelum
`--confirm`. Bentuk dengan garis miring atau tanpa method dapat dibaca router
sebagai pasangan controller/method yang tidak ada.

## Hasil validasi pasca-eksekusi

| Artefak | Jumlah |
|---|---:|
| Transaksi diproses | 8.413 |
| Batch LIFO | 2.551 |
| Batch aktif | 417 |
| Qty batch aktif | 8.933 |
| Alokasi LIFO | 5.796 |
| Transaksi keluar teralokasi | 5.672 |
| Issue terbuka | 738 |
| Batch perlu harga | 519 |
| Qty perlu harga | 9.379 |
| Issue stok minus historis | 219 |
| Qty stok minus historis | 1.480,250 |

## Tidak ada perubahan schema

Tidak ada migration, tabel, kolom, indeks, atau constraint baru. Rebuild hanya
memuat ulang data pada tabel bantu LIFO yang sudah ada:

- `tbpo_stock_lifo_batch_nk`
- `tbpo_stock_lifo_allocation`
- `tbpo_stock_lifo_harga_log`
- `tbpo_stock_lifo_rebuild_issue`

## Tindak lanjut wajib

1. Purchasing/Keuangan mengisi harga untuk 519 batch berstatus `PERLU_HARGA`.
2. Rekonsiliasi 219 isu `STOK_MINUS_HISTORIS` dengan bukti penerimaan dan
   pengambilan barang.
3. Uji melalui browser dengan akun berwenang pada sampel transaksi PO dan
   pengambilan sebelum menjadikan nilai LIFO sebagai laporan keuangan resmi.
