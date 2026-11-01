<?php

use Illuminate\Support\Facades\Route;

/*
| Setiap domain memiliki berkas rute sendiri di routes/api/.
| Berkas ini tidak disunting lagi setelah fondasi: menambah rute berarti
| menyunting berkas domain Anda, bukan berkas bersama ini.
*/
Route::prefix('v1')->name('v1.')->group(function (): void {
    foreach (glob(__DIR__.'/api/*.php') ?: [] as $berkas) {
        require $berkas;
    }
});
