<?php

use App\Http\Controllers\RegistrasiController;
use Illuminate\Support\Facades\Route;

Route::post('registrasi', RegistrasiController::class)->name('registrasi');
