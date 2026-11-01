# Peta Skema Basis Data Lapak

Dokumen ini memetakan kepemilikan tabel, penomoran migration, dan arah foreign key antardomain untuk seluruh siklus pengembangan Lapak (Sesi 1 sampai Sesi 5).

## Konvensi Penomoran Migration
Seluruh migration wajib mengikuti pola:
`2026_10_SS_DNNNNN_nama.php`
- `SS`: Nomor sesi (misal `01` untuk Sesi 1)
- `D`: Nomor domain (`1` = Identitas, `2` = Katalog, `3` = Belanja, `4` = Dompet, `5` = Kualitas)
- `NNNNN`: Nomor urut migration dalam domain tersebut (5 digit)

## Kepemilikan Tabel per Domain
- Domain 1: `pengguna`
- Domain 2: `toko`, `kategori`, `produk`
- Domain 3: `butir_keranjang`
- Domain 4: `dompet`, `mutasi_saldo`
