<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PesananSayaController;
use App\Http\Controllers\SimulasiBelanjaController;
use Illuminate\Support\Facades\Route;

Route::post('/simulasi-belanja', SimulasiBelanjaController::class);

Route::middleware(['api', 'auth:sesi'])->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index']);
    Route::put('/keranjang/butir/{produk}', [KeranjangController::class, 'update']);
    Route::delete('/keranjang/butir/{produk}', [KeranjangController::class, 'destroy']);
    Route::delete('/keranjang', [KeranjangController::class, 'clear']);

    Route::post('/checkout', CheckoutController::class);
    Route::get('/pesanan-saya', [PesananSayaController::class, 'index']);
    Route::get('/pesanan-saya/{nomor}', [PesananSayaController::class, 'show']);
});
