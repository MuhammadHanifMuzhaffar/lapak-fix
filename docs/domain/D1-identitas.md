# Piagam Domain 1: Identitas dan Sesi

- **Penanggung Jawab:** Muhammad Hanif Muzhaffar (@Velmiree)
- **Lingkup:** Autentikasi Pengguna, Entitas Akun, Validasi Keamanan, dan Manajemen Sesi

## Batasan dan Tanggung Jawab Domain
1. Bertanggung jawab atas tabel `pengguna` dan model `Pengguna`.
2. Menghilangkan ketergantungan pada tabel `users` bawaan dan sesi cookie Laravel.
3. Memastikan kata sandi selalu di-hash secara aman (`hashed` cast).
4. Melindungi kolom status akun dari celah mass-assignment pada permintaan klien.
5. Membangun endpoint registrasi publik `POST /api/v1/registrasi` dengan validasi ketat (normalisasi surel, konfirmasi kata sandi, batas panjang input).
6. Menjamin respons API tidak pernah membocorkan kata sandi maupun hash kata sandi.

## Aturan Bisnis yang Dikawal
- **LP-2:** Akun pengguna memiliki status aktif atau ditangguhkan. Klien tidak boleh menetapkan status akun sendiri saat registrasi.
- **LP-17:** Pengelolaan identitas independen tanpa Laravel Sanctum/cookie pada API.
