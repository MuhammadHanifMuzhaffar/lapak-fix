<?php

use App\Http\Controllers\MasukController;
use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\SesiController;
use Illuminate\Support\Facades\Route;

Route::post('registrasi', RegistrasiController::class)->name('registrasi');
Route::post('masuk', MasukController::class)->middleware('throttle:masuk')->name('masuk');

Route::middleware('auth:sesi')->group(function (): void {
    Route::get('saya', [SesiController::class, 'saya'])->name('saya');
    Route::post('keluar', [SesiController::class, 'keluar'])->name('keluar');

    Route::get('sesi', [SesiController::class, 'index'])->name('sesi.index');
    Route::post('sesi/konfirmasi', [SesiController::class, 'konfirmasi'])
        ->middleware('throttle:konfirmasi')
        ->name('sesi.konfirmasi');

    Route::delete('sesi/{pengenal}', [SesiController::class, 'destroy'])
        ->middleware('sesi.segar')
        ->name('sesi.destroy');
});
