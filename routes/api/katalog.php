<?php

use App\Http\Controllers\KatalogController;
use App\Http\Controllers\ModerasiTokoController;
use App\Http\Controllers\ProdukSayaController;
use App\Http\Controllers\TokoSayaController;
use Illuminate\Support\Facades\Route;

Route::get('produk', [KatalogController::class, 'index'])->name('produk.index');
Route::get('produk/{slug}', [KatalogController::class, 'show'])->name('produk.show');
Route::get('toko/{slug}', [KatalogController::class, 'toko'])->name('toko.show');

Route::middleware('auth:sesi')
    ->prefix('toko-saya')
    ->name('toko-saya.')
    ->whereNumber('id')
    ->group(function (): void {
        Route::get('/', [TokoSayaController::class, 'show'])->name('show');
        Route::patch('/', [TokoSayaController::class, 'update'])->name('update');

        Route::post('/', [TokoSayaController::class, 'store'])
            ->middleware('izin:toko.buka')
            ->name('store');

        Route::middleware('izin:produk.kelola')->group(function (): void {
            Route::get('produk', [ProdukSayaController::class, 'index'])->name('produk.index');
            Route::post('produk', [ProdukSayaController::class, 'store'])->name('produk.store');
            Route::patch('produk/{id}', [ProdukSayaController::class, 'update'])->name('produk.update');
            Route::delete('produk/{id}', [ProdukSayaController::class, 'destroy'])->name('produk.destroy');
        });
    });

Route::middleware(['auth:sesi', 'izin:toko.moderasi'])
    ->prefix('admin/toko')
    ->name('admin.toko.')
    ->whereNumber('id')
    ->group(function (): void {
        Route::get('/', [ModerasiTokoController::class, 'index'])->name('index');

        Route::middleware('sesi.segar')->group(function (): void {
            Route::post('{id}/setujui', [ModerasiTokoController::class, 'setujui'])->name('setujui');
            Route::post('{id}/tangguhkan', [ModerasiTokoController::class, 'tangguhkan'])->name('tangguhkan');
        });
    });
