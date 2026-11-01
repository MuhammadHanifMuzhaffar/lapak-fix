<?php

use App\Http\Controllers\KatalogController;
use App\Http\Controllers\TokoSayaController;
use Illuminate\Support\Facades\Route;

Route::get('produk', [KatalogController::class, 'index'])->name('produk.index');
Route::get('produk/{slug}', [KatalogController::class, 'show'])->name('produk.show');
Route::get('toko/{slug}', [KatalogController::class, 'toko'])->name('toko.show');

Route::middleware('auth:sesi')->group(function (): void {
    Route::get('toko-saya', [TokoSayaController::class, 'show'])->name('toko-saya.show');
});
