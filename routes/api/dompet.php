<?php

use App\Http\Controllers\DompetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sesi')->group(function (): void {
    Route::get('dompet', [DompetController::class, 'show'])->name('dompet.show');
    Route::get('dompet/mutasi', [DompetController::class, 'mutasi'])
        ->middleware('sesi.segar')
        ->name('dompet.mutasi');
});
