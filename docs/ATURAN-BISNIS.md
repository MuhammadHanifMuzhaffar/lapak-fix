# Tujuh Belas Aturan Bisnis Lapak

Setiap aturan memiliki kode unik agar dapat dirujuk secara konsisten pada kode sumber, respons galat, kasus pengujian, dan judul Pull Request.

| Kode | Nama Aturan | Ringkasan Kebijakan | Tempat Penegakan |
|---|---|---|---|
| LP-1 | Bilangan Bulat Rupiah | Seluruh nilai uang adalah integer rupiah. Tipe pecahan dilarang di basis data, kode, dan JSON. | Value Object `App\Domain\Uang` |
| LP-2 | Izin Berbasis Peran | Kewenangan ditentukan izin yang melekat pada peran, bukan nama peran. Satu akun boleh banyak peran. | Sesi 3 |
| LP-3 | Satu Pengguna Satu Toko | Satu akun maksimal memiliki satu toko. Toko baru berstatus menunggu sampai disetujui admin. | `unique` pada `toko.pemilik_id`, `TokoSayaController::store` |
| LP-4 | Scope Tampil Produk | Produk hanya tampil jika berstatus aktif, tokonya aktif, dan stok mencukupi (> 0). | `App\Models\Produk::scopeTampil()` |
| LP-5 | Larangan Beli Sendiri | Penjual tidak dapat membeli produk dari tokonya sendiri. | `PenyusunBelanja::pilah`, `LayananKeranjang::aturButir` |
| LP-6 | Perhitungan Server-Side | Harga, ongkir, dan total dihitung server dari basis data. Angka kiriman klien diabaikan. | `SimulasiBelanjaRequest`, `PenyusunBelanja`, `LayananKeranjang::lihat` |
| LP-7 | Pemisahan per Toko | Satu checkout menghasilkan satu pesanan per toko dan dibayar dengan satu pembayaran. | `App\Domain\Belanja\PenghitungBelanja` |
| LP-8 | Komisi Marketplace | Komisi 5% dari subtotal produk (dibulatkan setengah ke atas), dipotong dari bagian penjual. Ongkir milik penjual. | `config/lapak.php`, `App\Domain\Belanja\PenghitungBelanja` |
| LP-9 | Format Nomor Pesanan | Nomor pesanan berformat `LPK-YYYYMMDD-NNNNNN` dan unik seumur hidup sistem. | Sesi 3 |
| LP-10 | Siklus Hidup Pesanan | Menunggu pembayaran -> Dibayar -> Dikemas -> Dikirim -> Selesai. Batal otomatis dalam 24 jam jika belum dibayar. | `config/lapak.php`, Sesi 3 |
| LP-11 | Idempotensi Transaksi | Setiap permintaan yang mengubah uang atau stok wajib membawa header `Idempotency-Key`. | Sesi 3 |
| LP-12 | Penahanan Dana (Escrow) | Dana ditahan di escrow dan dilepas setelah pembeli konfirmasi atau 3 hari setelah dikirim. | `config/lapak.php`, Sesi 4 |
| LP-13 | Mutasi Berpasangan | Saldo hanya berubah lewat pemindahan antardompet dengan sepasang mutasi bernilai nol. Mutasi append-only. | `App\Services\BukuBesar` |
| LP-14 | Keamanan Sesi Token | Kunci sesi ditampilkan sekali dan disimpan sebagai hash. Berlaku 7 hari, timeout 2 jam tidak aktif. | `KunciSesi`, `PenerbitSesi`, `Sesi::scopeBerlaku`, `lapak:pangkas-sesi` |
| LP-15 | Sesi Segar | Tindakan sensitif menuntut kata sandi dikonfirmasi ulang dalam 10 menit terakhir. | `SesiSegar`, `SesiController::konfirmasi` (cabut sesi, riwayat mutasi) |
| LP-16 | Batas Pencairan Saldo | Pencairan minimal Rp 50.000, disetujui peran keuangan, pengaju dan penyetuju tidak boleh sama. | `config/lapak.php`, Sesi 4 |
| LP-17 | Jejak Audit Tak Terbantahkan | Tindakan sensitif dicatat pada log audit append-only dengan rantai hash SHA-256. | Sesi 4 & 5 |
