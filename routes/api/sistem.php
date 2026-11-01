<?php

use App\Http\Controllers\KesehatanController;
use Illuminate\Support\Facades\Route;

Route::get('health', KesehatanController::class)->name('health');
