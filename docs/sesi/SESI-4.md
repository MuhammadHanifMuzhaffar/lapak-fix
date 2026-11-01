# Papan Sesi 4: Keamanan Transaksi dan Konkurensi

- **Integrator:** Kaishan Arya Setiko (@KaishanAryaSetiko)
- **Tag Penutup:** `sesi-4`
- **Target Capaian:** Idempotensi permintaan, transaksi belanja atomik, kunci baris (lockForUpdate) anti-race condition, webhook pembayaran HMAC, dan skenario uji balapan.

## Urutan Kunci Baris yang Disepakati di Kelas

| Urut | Baris | Dikunci oleh |
|---|---|---|
| 1 | `pengguna`, lalu `alamat` miliknya | `BukuAlamat` |
| 2 | `keranjang`, lalu `butir_keranjang` | `LayananCheckout` |
| 3 | `checkout` | `GembokCheckout` |
| 4 | `pembayaran` | `LayananPembayaran` |
| 5 | `pesanan` | `MesinStatusPesanan` |
| 6 | `produk`, urut nomor menaik | `PenjagaStok` |
| 7 | `dompet`, urut nomor menaik | `BukuBesar` |

## Matriks Pull Request Sesi 4

| PR | Jendela | Cabang | Penulis | Isi / Fitur | Peninjau | Status |
|:---:|:---:|:---|:---:|:---|:---:|:---:|
| 34 | Pembuka | `sesi-4/pembuka-dimas` | Kaishan | Papan sesi 4, urutan kunci baris, pembaruan CODEOWNERS | Novendra | Merged |
| 35 | A1 | `sesi-4/kontrak-idempoten-sekar` | Zidnii | Kunci idempotensi, middleware idempoten, lapak:balapan | Kaishan | Merged |
| 36 | A2 | `sesi-4/kontrak-alamat-bambang` | Hanif | Izin pesanan.buat, tabel alamat, PenjagaTransaksi | Zidnii | Merged |
| 37 | A3 | `sesi-4/kontrak-stok-bagas` | Azizah | PenjagaStok versi kontrak | Hanif | Merged |
| 38 | A4 | `sesi-4/kontrak-pesanan-rani` | Novendra | Tabel checkout dan pesanan, penomor, gembok, mesin status | Azizah | Merged |
| 39 | A5 | `sesi-4/kontrak-pembayaran-dimas` | Kaishan | Tabel pembayaran, LayananPembayaran versi kontrak | Novendra | Merged |
| 40 | B1 | `sesi-4/alamat-bambang` | Hanif | BukuAlamat dan rute alamat | Zidnii | Merged |
| 41 | B2 | `sesi-4/checkout-rani` | Novendra | Checkout atomik, pesanan saya, pembatalan otomatis | Azizah | Merged |
| 42 | B3 | `sesi-4/kunci-stok-bagas` | Azizah | Kunci baris stok, penyesuaian stok penjual | Hanif | Merged |
| 43 | B4 | `sesi-4/webhook-dimas` | Kaishan | Webhook HMAC, dana tertahan, simulasi gateway | Novendra | Merged |
| 45 | P1 | `sesi-4/urutan-kunci-rani` | Novendra | Perbaikan urutan kunci anti-deadlock pembatalan otomatis | Azizah | Merged |
| 44 | B5 | `sesi-4/balapan-ci-sekar` | Zidnii | Klasifikasi rute POST, skenario lintas domain, CI MySQL | Kaishan | Merged |
| 46 | Penutup | `sesi-4/penutup-dimas` | Kaishan | Papan sesi, penegakan aturan bisnis LP-4 s/d LP-15, tag sesi-4 | Zidnii | Merged |
