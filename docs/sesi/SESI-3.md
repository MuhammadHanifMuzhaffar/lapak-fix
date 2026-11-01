# Papan Sesi 3: Peran, Izin, dan Kewenangan atas Objek

- **Integrator:** Novendra Anggoro Adji Nugroho (@NovendraAnggoroAdjiNugroho)
- **Tag Penutup:** `sesi-3`
- **Target Capaian:** RBAC berbasis izin, Policy dan otorisasi tingkat objek, matriks otorisasi 156 uji, moderasi toko admin, serta daur hidup pesanan LP-10.

## Matriks yang Disepakati di Kelas

| Izin | Pembeli | Penjual | Admin | Keuangan | Auditor | Keterangan |
|---|:---:|:---:|:---:|:---:|:---:|---|
| `keranjang.kelola` | v | | | | | Mengisi dan mengubah keranjang belanja sendiri |
| `toko.buka` | v | | | | | Mengajukan pembukaan toko |
| `produk.kelola` | | v | | | | Menambah, mengubah, mengarsipkan produk sendiri |
| `dompet.lihat` | | v | | | | Melihat saldo dan riwayat mutasi sendiri |
| `toko.moderasi` | | | v | | | Menyetujui dan menangguhkan toko |
| `peran.kelola` | | | v | | | Memberi dan mencabut peran pengguna |
| `buku-besar.lihat` | | | | v | v | Membaca seluruh dompet dan mutasi marketplace |

## Matriks Pull Request Sesi 3

| PR | Jendela | Cabang | Penulis | Isi / Fitur | Peninjau | Status |
|:---:|:---:|:---|:---:|:---|:---:|:---:|
| 25 | Pembuka | `sesi-3/pembuka-novendra` | Novendra | Papan sesi 3, matriks izin, pembaruan CODEOWNERS | Hanif | Merged |
| 26 | A1 | `sesi-3/kontrak-izin-zidni` | Zidnii | Peran, izin, middleware izin, matriks otorisasi | Novendra | Merged |
| 27 | A2 | `sesi-3/kontrak-peran-hanif` | Hanif | PengelolaPeran, state factory per peran | Kaishan | Merged |
| 28 | B1 | `sesi-3/peran-pengguna-hanif` | Hanif | Peran pendaftar, /saya, pintu admin peran | Kaishan | Merged |
| 29 | B2 | `sesi-3/ingatan-izin-zidni` | Zidnii | Ingatan izin, penjaga pintu peran | Novendra | Merged |
| 30 | B3 | `sesi-3/moderasi-toko-azizah` | Azizah | Izin toko dan produk, moderasi oleh admin | Zidnii | Merged |
| 31 | B4 | `sesi-3/gerbang-keranjang-novendra` | Novendra | Izin keranjang, aturan status pesanan LP-10 | Hanif | Merged |
| 32 | B5 | `sesi-3/buku-besar-kaishan` | Kaishan | Izin dompet, buku besar untuk staf keuangan/auditor | Azizah | Merged |
| 33 | Penutup | `sesi-3/penutup-novendra` | Novendra | Uji izin menganggur, aturan bisnis, tag sesi-3 | Kaishan | Merged |
