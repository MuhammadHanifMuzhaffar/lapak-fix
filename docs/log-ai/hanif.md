# Log Penggunaan AI — Sesi 1

- **Nama:** Muhammad Hanif Muzhaffar
- **NIM:** V3425072
- **Domain:** Domain 1 (Identitas dan Sesi)

## Rincian Konsultasi AI pada Sesi 1

| Tanggal | Aktivitas / Topik | Prompt Pokok | Hasil / Keputusan Teknis |
|---|---|---|---|
| 27 Oktober 2026 | Desain Skema Pengguna & Factory | "Bagaimana memastikan kata sandi ter-hash otomatis dan status akun terlindung dari mass-assignment di Laravel 11?" | Menggunakan attribute casting `'kata_sandi' => 'hashed'` dan mengeluarkan `status` dari `$fillable` model Pengguna. |
| 27 Oktober 2026 | Validasi Registrasi & Normalisasi Surel | "Praktik terbaik normalisasi surel dan validasi aturan password kuat pada FormRequest." | Menerapkan `prepareForValidation()` dengan `mb_strtolower(trim())` dan `Password::min(10)->letters()->numbers()`. |
| 27 Oktober 2026 | Pengujian Pest Keamanan Password | "Cara menguji endpoint agar tidak membocorkan hash password pada payload respons JSON." | Menggunakan assertions `assertJsonMissingPath('data.kata_sandi')` dan `expect($response->getContent())->not->toContain('$2y$')`. |
