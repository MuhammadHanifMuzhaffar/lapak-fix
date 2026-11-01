<?php

use App\Http\Controllers\KatalogController;
use App\Http\Controllers\ModerasiTokoController;
use App\Http\Controllers\ProdukSayaController;
use App\Http\Controllers\TokoSayaController;
use Illuminate\Support\Facades\Route;

Route::get('/kategori', [KatalogController::class, 'kategori']);
Route::get('/produk', [KatalogController::class, 'produk']);
Route::get('/produk/{slug}', [KatalogController::class, 'detailProduk']);
Route::get('/toko/{slug}', [KatalogController::class, 'detailToko']);

Route::middleware(['api', 'auth:sesi'])->group(function () {
    Route::post('/toko-saya', [TokoSayaController::class, 'store']);
    Route::get('/toko-saya', [TokoSayaController::class, 'show']);
    Route::patch('/toko-saya', [TokoSayaController::class, 'update']);

    Route::get('/toko-saya/produk', [ProdukSayaController::class, 'index']);
    Route::post('/toko-saya/produk', [ProdukSayaController::class, 'store']);
    Route::get('/toko-saya/produk/{id}', [ProdukSayaController::class, 'show']);
    Route::patch('/toko-saya/produk/{id}', [ProdukSayaController::class, 'update']);
    Route::post('/toko-saya/produk/{id}/sesuaikan-stok', [ProdukSayaController::class, 'sesuaikanStok']);

    Route::post('/admin/toko/{id}/setujui', [ModerasiTokoController::class, 'setujui']);
    Route::post('/admin/toko/{id}/tolak', [ModerasiTokoController::class, 'tolak']);
    Route::post('/admin/toko/{id}/tangguhkan', [ModerasiTokoController::class, 'tangguhkan']);
    Route::post('/admin/toko/{id}/aktifkan', [ModerasiTokoController::class, 'aktifkan']);
});
