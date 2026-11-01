<?php

use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\DompetController;
use App\Http\Controllers\PembayaranSayaController;
use App\Http\Controllers\WebhookPembayaranController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/pembayaran', WebhookPembayaranController::class);

Route::middleware(['api', 'auth:sesi'])->group(function () {
    Route::get('/dompet', [DompetController::class, 'saldo']);
    Route::get('/dompet/mutasi', [DompetController::class, 'mutasi']);

    Route::get('/pembayaran-saya/{nomorPesanan}', [PembayaranSayaController::class, 'show']);

    Route::get('/buku-besar/dompet', [BukuBesarController::class, 'daftarDompet']);
    Route::get('/buku-besar/mutasi', [BukuBesarController::class, 'mutasi']);
});
