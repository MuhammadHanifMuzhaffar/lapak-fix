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

## Idempotensi 
  
- Setiap permintaan yang mengubah uang atau stok wajib membawa header `Idempotency-Key` (LP-11): 

  16 sampai 64 huruf, angka, garis bawah, atau tanda hubung. UUID memenuhi syarat ini. 
- Klien membuat SATU kunci untuk satu niat, misalnya satu kali menekan tombol bayar, dan memakai 
  kunci yang sama setiap kali mengulang permintaan itu karena jaringan putus atau batas waktu habis. 
- Permintaan pertama yang berhasil (2xx) dicatat bersama responsnya. Pengulangan dengan kunci dan 
  badan yang sama menerima respons yang sama, ditandai header `Idempotency-Replayed: true`, dan 
  tidak berefek lagi. 
- Permintaan yang gagal tidak meninggalkan catatan. Setelah sebabnya dibetulkan, kunci yang sama 
  boleh dipakai lagi. 
- Kunci yang sama dengan badan yang berbeda dijawab 422 `LP-11`. Kunci berlaku per pengguna. 
- Catatan kunci dan seluruh perubahan data berada dalam satu transaksi basis data. Rute yang 
  memasang `idempoten` karenanya tidak boleh memanggil layanan luar di dalam controller-nya.
