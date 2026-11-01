# Peta Skema Basis Data Lapak

Dokumen ini memetakan kepemilikan tabel, penomoran migration, dan arah foreign key antardomain untuk seluruh siklus pengembangan Lapak (Sesi 1 sampai Sesi 5).

## Konvensi Penomoran Migration
Seluruh migration wajib mengikuti pola:
`2026_10_SS_DNNNNN_nama.php`
- `SS`: Nomor sesi (misal `01` untuk Sesi 1)
- `D`: Nomor domain (`1` = Identitas, `2` = Katalog, `3` = Belanja, `4` = Dompet, `5` = Kualitas)
- `NNNNN`: Nomor urut migration dalam domain tersebut (5 digit)

## Kepemilikan Tabel per Domain
| Domain | Penulis | Tabel | Sesi |
|---|---|---|---|
| D1 Identitas dan Akun | Bambang | `pengguna`, `sesi`, `alamat` | Sesi 1, 2, 4 |
| D2 Toko dan Katalog | Bagas | `toko`, `kategori`, `produk` | Sesi 1 |
| D3 Keranjang dan Pesanan | Rani | `keranjang`, `butir_keranjang`, `checkout`, `pesanan`, `butir_pesanan` | Sesi 1, 4 |
| D4 Pembayaran dan Saldo | Dimas | `dompet`, `mutasi_saldo`, `pembayaran` | Sesi 1, 4 |
| D5 Kualitas dan Konkurensi | Sekar | `kunci_idempotensi` | Sesi 4 |

## Hubungan Antartabel
```text
pengguna (D1) <- dompet (D4) <- mutasi_saldo (D4)
pengguna (D1) <- alamat (D1)
pengguna (D1) <- checkout (D3) <- pesanan (D3) <- butir_pesanan (D3)
toko (D2) <- pesanan (D3)          produk (D2) <- butir_pesanan (D3)
checkout (D3) <- pembayaran (D4)
pengguna (D1) <- kunci_idempotensi (D5)
```

`pesanan` tidak menunjuk ke `pembayaran`, walau itu yang pertama terpikir: D3 tidak boleh menunjuk ke D4. Keduanya menunjuk ke `checkout`, induk bersama milik D3 (LP-7). `pesanan` juga tidak menunjuk ke `alamat`: alamat disalin ke kolom `alamat_kirim` pada saat checkout.

## Urutan kunci baris

Berlaku sejak Sesi 4. Transaksi yang mengunci lebih dari satu baris mengambil kuncinya menurut urutan di bawah ini. Sebuah transaksi boleh melompati anak tangga, tetapi tidak pernah naik kembali.

| Urut | Baris | Dikunci oleh |
| --- | --- | --- |
| 1 | `pengguna`, lalu `alamat` miliknya | `BukuAlamat` |
| 2 | `keranjang`, lalu `butir_keranjang` | `LayananCheckout` |
| 3 | `checkout` | `GembokCheckout` |
| 4 | `pembayaran` | `LayananPembayaran` |
| 5 | `pesanan` | `MesinStatusPesanan` |
| 6 | `produk`, urut nomor menaik | `PenjagaStok` |
| 7 | `dompet`, urut nomor menaik | `BukuBesar` |

Middleware `idempoten` mencatat kuncinya sebelum anak tangga pertama. Catatan itu baris baru, dan tidak ada transaksi yang memintanya setelah memegang kunci dari tabel di atas.

Tiga aturan menyertainya:
1. Kunci hanya diambil di dalam `DB::transaction()`. Di luar transaksi, kunci lepas begitu pernyataannya selesai.
2. Kunci dulu, baca kemudian. Data yang menjadi dasar keputusan dibaca dengan `lockForUpdate()` setelah kunci induknya didapat.
3. Tidak ada panggilan ke layanan luar dan tidak ada pekerjaan lama di dalam transaksi.
