# Kontrak Format Respons dan Galat API Lapak

Seluruh endpoint REST API Lapak wajib mematuhi struktur amplop respons JSON terpadu yang didefinisikan dalam dokumen ini.

## 1. Amplop Respons Berhasil (HTTP 200 / 201)
```json
{
  "sukses": true,
  "data": { ... } atau [ ... ],
  "meta": null atau {
    "halaman_saat_ini": 1,
    "per_halaman": 12,
    "total": 45,
    "total_halaman": 4
  }
}
```

## 2. Amplop Respons Galat (HTTP 4xx / 5xx)
```json
{
  "sukses": false,
  "galat": {
    "kode": "KODE_GALAT",
    "pesan": "Penjelasan galat yang ramah pengguna.",
    "rincian": { ... } atau []
  }
}
```

## 3. Daftar Kode Galat Standar
| Kode Galat | Status HTTP | Keterangan |
|---|---|---|
| `VALIDASI` | 422 Unprocessable Entity | Masukan klien tidak memenuhi aturan validasi FormRequest. `rincian` memuat array nama field dan pesan. |
| `TIDAK_DITEMUKAN` | 404 Not Found | Data yang diminta tidak ada, berstatus draf, atau milik toko nonaktif (LP-4). |
| `OTORISASI` | 403 Forbidden | Akun tidak memiliki peran/izin untuk tindakan ini (LP-2). |
| `TIDAK_TERAUTENTIKASI` | 401 Unauthorized | Kunci sesi tidak ada, kedaluwarsa, atau dicabut (Sesi 2). |
| `SERVER_ERROR` | 500 Internal Server Error | Kesalahan tak terduga di server. Rincian internal disembunyikan saat produksi. |
