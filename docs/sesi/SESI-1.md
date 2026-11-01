# Papan Sesi 1: Fondasi Tim dan Repositori

- **Integrator:** Muhammad Hanif Muzhaffar (@MuhammadHanifMuzhaffar)
- **Tag Penutup:** `sesi-1`
- **Target Tanggal:** Oktober 2026

## Matriks Pull Request Sesi 1

| PR | Jendela | Cabang | Penulis | Isi / Fitur | Peninjau | Menunggu | Status |
|---|---|---|---|---|---|---|---|
| 1 | Pembuka | `sesi-1/fondasi` | Hanif | Kerangka Laravel, konfigurasi Lapak, rute domain, dokumen tim | Semua | - | Sedang Berjalan |
| 2 | A1 | `sesi-1/kontrak-respons-zidni` | Zidnii | Amplop respons dan induk galat domain | Hanif | PR 1 | Siap |
| 3 | A2 | `sesi-1/kontrak-uang-novendra` | Novendra | Value object Uang dan ujinya (LP-1) | Kaishan | PR 1 | Siap |
| 4 | A3 | `sesi-1/kontrak-identitas-hanif` | Hanif | Tabel, model, factory pengguna, akun contoh | Azizah | PR 1 | Siap |
| 5 | A4 | `sesi-1/kontrak-katalog-azizah` | Azizah | Skema dan model katalog, scope tampil (LP-3, LP-4) | Novendra | PR 4 | Menunggu PR 4 |
| 6 | A5 | `sesi-1/kontrak-dompet-kaishan` | Kaishan | Skema dan model dompet (LP-13) | Zidnii | PR 4 | Menunggu PR 4 |
| 7 | B1 | `sesi-1/galat-dan-ci-zidni` | Zidnii | Penanganan galat terpusat, endpoint health, uji kontrak, CI | Hanif | PR 2 | Menunggu Jendela A |
| 8 | B2 | `sesi-1/registrasi-hanif` | Hanif | Endpoint registrasi pengguna dan uji keamanan password | Azizah | PR 4, PR 7 | Menunggu PR 7 |
| 9 | B3 | `sesi-1/katalog-azizah` | Azizah | Endpoint katalog, seeder katalog contoh, uji katalog | Novendra | PR 3, PR 5, PR 7 | Menunggu PR 7 |
| 10 | B4 | `sesi-1/belanja-rani` | Novendra | Keranjang, penghitung belanja per toko, simulasi belanja, uji | Kaishan | PR 3, PR 5, PR 7 | Menunggu PR 7 |
| 11 | B5 | `sesi-1/buku-besar-dimas` | Kaishan | Buku besar pemindahan berpasangan, audit buku, seeder dompet, uji | Zidnii | PR 2, PR 3, PR 6, PR 7 | Menunggu PR 7 |
| 12 | Penutup | `sesi-1/penutup-hanif` | Hanif | Papan sesi terisi, penegakan aturan, skrip rekap sesi-1 | Azizah | PR 11 | Menunggu PR 11 |

## Catatan Pelaksanaan Sesi 1
- Repositori privat GitHub `lapak-api` berhasil diinisialisasi dengan aturan merge commit enforced.
- Branch `sesi-1/fondasi` telah dipersiapkan dengan kerangka bersih Laravel 11 dan Pest PHP.
