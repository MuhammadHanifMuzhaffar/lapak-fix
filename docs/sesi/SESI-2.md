# Papan Sesi 2: Identitas dan Session dengan Kunci

- **Integrator:** Ni'matul Azizah (@NimatulAzizahh)
- **Tag Penutup:** `sesi-2`
- **Target Capaian:** Mekanisme sesi berbasis kunci kriptografis, guard auth:sesi mandiri, sesi segar, dan pengikatan kepemilikan data ke sesi.

## Matriks Pull Request Sesi 2

| PR | Jendela | Cabang | Penulis | Isi / Fitur | Peninjau | Menunggu | Status |
|:---:|:---:|:---|:---:|:---|:---:|:---:|:---:|
| 13 | Pembuka | `sesi-2/pembuka-azizah` | Azizah | Papan sesi 2, pembaruan CODEOWNERS | Kaishan | - | Selesai (Merged) |
| 14 | A1 | `sesi-2/kontrak-sesi-hanif` | Hanif | Format kunci, tabel sesi, penerbit, pemeriksa | Novendra | PR 13 | Selesai (Merged) |
| 15 | A2 | `sesi-2/kontrak-guard-zidni` | Zidnii | Guard sesi, sesi segar, pembatas laju, alat bantu uji | Azizah | PR 14 | Selesai (Merged) |
| 16 | A3 | `sesi-2/kontrak-toko-azizah` | Azizah | Penanda kepemilikan toko & produk, GET /toko-saya | Kaishan | PR 15 | Selesai (Merged) |
| 17 | A4 | `sesi-2/kontrak-keranjang-novendra` | Novendra | Pemilah butir tanpa galat, GET /keranjang | Zidnii | PR 15 | Selesai (Merged) |
| 18 | A5 | `sesi-2/kontrak-dompet-dimas` | Kaishan | Pembacaan saldo dompet sendiri, GET /dompet | Hanif | PR 15 | Selesai (Merged) |
| 19 | B1 | `sesi-2/masuk-hanif` | Hanif | Masuk, keluar, kelola sesi, konfirmasi sandi | Novendra | PR 15 | Selesai (Merged) |
| 20 | B2 | `sesi-2/gerbang-zidni` | Zidnii | Uji rute terlindung deny-by-default, pangkas sesi | Azizah | PR 19 | Selesai (Merged) |
| 21 | B3 | `sesi-2/toko-saya-azizah` | Azizah | Buka toko, kelola produk toko sendiri | Kaishan | PR 16 | Selesai (Merged) |
| 22 | B4 | `sesi-2/keranjang-novendra` | Novendra | Operasi keranjang idempoten, LP-5 larangan beli sendiri | Zidnii | PR 16, 17 | Selesai (Merged) |
| 23 | B5 | `sesi-2/mutasi-kaishan` | Kaishan | Riwayat mutasi di balik sesi segar, seeder mutasi | Hanif | PR 18, 19 | Selesai (Merged) |
| 24 | Penutup | `sesi-2/penutup-azizah` | Azizah | Papan sesi lengkap, pembaruan aturan bisnis, tag sesi-2 | Novendra | PR 23 | Selesai (Merged) |

## Catatan Penutup Sesi 2 & Titipan untuk Sesi 3
1. Sesi 2 sukses menjawab pertanyaan domain: siapa yang sedang berbicara dengan sistem, dan apa buktinya.
2. Mekanisme sesi mandiri bebas cookie dan tabel bawaan, berjalan dengan token bearer terenkripsi hash SHA-256.
3. Rute terlindung ditegakkan secara bawaan (*deny-by-default*).
4. Titipan untuk Novendra (Integrator Sesi 3): Implementasi RBAC, moderasi toko oleh admin, dan Policy hak akses.
