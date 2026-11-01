<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PesananSayaController;
use App\Http\Controllers\SimulasiBelanjaController;
use Illuminate\Support\Facades\Route;

Route::post('simulasi-belanja', SimulasiBelanjaController::class)->name('belanja.simulasi');

// Keranjang selalu milik pengguna yang sedang masuk.
// Hanya pemegang izin keranjang.kelola: akun staf tidak berbelanja memakai akun dinasnya.
Route::middleware(['auth:sesi', 'izin:keranjang.kelola'])
    ->prefix('keranjang')
    ->name('keranjang.')
    ->whereNumber('produkId')
    ->group(function (): void {
        Route::get('/', [KeranjangController::class, 'show'])->name('show');
        Route::delete('/', [KeranjangController::class, 'kosongkan'])->name('kosongkan');

        Route::put('butir/{produkId}', [KeranjangController::class, 'simpanButir'])->name('simpan');
        Route::delete('butir/{produkId}', [KeranjangController::class, 'hapusButir'])->name('hapus');
    });

// Checkout dan pesanan milik pembeli yang sedang masuk.
Route::middleware(['auth:sesi', 'izin:pesanan.buat'])->group(function (): void {
    // Urutan: batas laju per sesi dulu, baru idempotensi. Permintaan yang ditolak pembatas laju
    // tidak sempat membuka transaksi.
    Route::post('checkout', CheckoutController::class)
        ->middleware(['throttle:transaksi', 'idempoten'])
        ->name('checkout');

    Route::get('pesanan', [PesananSayaController::class, 'index'])->name('pesanan.index');
    // Sengaja tanpa pola pada {nomor}: alamat yang bentuknya salah tetap melewati kunci sesi dan
    // izin lebih dulu, lalu dijawab 404 oleh kueri berlingkup, sama seperti nomor orang lain.
    Route::get('pesanan/{nomor}', [PesananSayaController::class, 'show'])->name('pesanan.show');
});
