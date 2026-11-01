<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\KonfirmasiSesiController;
use App\Http\Controllers\MasukController;
use App\Http\Controllers\ProfilSayaController;
use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\SesiController;
use Illuminate\Support\Facades\Route;

Route::post('/registrasi', RegistrasiController::class);
Route::post('/masuk', MasukController::class);

Route::middleware(['api', 'auth:sesi'])->group(function () {
    Route::get('/saya', ProfilSayaController::class);
    Route::post('/sesi/konfirmasi', KonfirmasiSesiController::class);

    Route::get('/sesi', [SesiController::class, 'index']);
    Route::delete('/sesi/{id}', [SesiController::class, 'destroy']);
    Route::delete('/sesi', [SesiController::class, 'destroyOthers']);

    Route::get('/alamat', [AlamatController::class, 'index']);
    Route::post('/alamat', [AlamatController::class, 'store']);
    Route::patch('/alamat/{alamat}', [AlamatController::class, 'update']);
    Route::delete('/alamat/{alamat}', [AlamatController::class, 'destroy']);
    Route::post('/alamat/{alamat}/jadikan-utama', [AlamatController::class, 'jadikanUtama']);
});
