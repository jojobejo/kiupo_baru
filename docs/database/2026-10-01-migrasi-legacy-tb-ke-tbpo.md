# Migrasi Data `tb_*` Legacy ke `tbpo_*`

Tanggal: 1 Oktober 2026  
Sumber: `C:\Users\bram\Downloads\u676129830_kiupo.sql`  
Target: `kiucoid_karismaerp_local`  
Status: berhasil.

## Pengamanan

Backup sebelum mutasi:

`database/backups/kiucoid_karismaerp_local_before_legacy_tbpo_merge_20261001.sql`

Dump dimuat lebih dahulu ke staging
`kiupobaru_staging_u676129830_20261001`. Data staging dipasangkan dari
`tb_nama` ke `tbpo_nama` hanya apabila tabel target tersedia.

## Aturan merge

1. `tb_user` dikecualikan sepenuhnya; tidak ada insert atau update ke
   `tbpo_user`.
2. Tabel `tbq_*` dan view tidak termasuk karena bukan pasangan `tbpo_*`.
3. Hanya baris dengan primary key yang belum ada di target yang diinsert.
   Baris lokal yang telah ada tidak ditimpa.
4. Target memakai mode SQL ketat dan seluruh merge dijalankan dalam satu
   transaksi.
5. `tbpo_barang.merk_barang` tidak tersedia di sumber; untuk setiap baris
   legacy yang baru, nilainya diisi string kosong.

## Hasil

Sebanyak 38 pasangan tabel dievaluasi dan seluruh primary key sumber pada
tabel yang dipetakan tersedia di target setelah migrasi.

Penambahan utama:

| Target | Baris baru |
|---|---:|
| `tbpo_note_direktur` | 41.330 |
| `tbpo_detail_po` | 8.978 |
| `tbpo_po` | 3.951 |
| `tbpo_generate_kd_ponk` | 4.337 |
| `tbpo_note_barang` | 3.908 |
| `tbpo_transaksi` | 640 |
| `tbpo_detail_req` | 240 |
| `tbpo_transaksi_trashbin` | 215 |

Kontrol pengecualian pengguna tervalidasi: sumber mempunyai 35 baris
`tb_user`; target `tbpo_user` tetap 35 baris setelah migrasi.

## Perubahan schema

Tidak ada tabel, kolom, indeks, constraint, atau migration schema baru.
Kegiatan ini adalah merge data saja.

## Verifikasi lanjutan

Lakukan UAT autentikasi, detail PO, request, histori transaksi, dan pembukaan
lampiran. Khusus lampiran, verifikasi juga keberadaan berkas fisiknya karena
dump hanya berisi metadata.
