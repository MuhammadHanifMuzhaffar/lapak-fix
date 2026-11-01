<?php

use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\SimulasiBelanjaController;
use Illuminate\Support\Facades\Route;

Route::post('simulasi-belanja', SimulasiBelanjaController::class)->name('belanja.simulasi');

Route::middleware('auth:sesi')->group(function (): void {
    Route::get('keranjang', [KeranjangController::class, 'show'])->name('keranjang.show');
});
