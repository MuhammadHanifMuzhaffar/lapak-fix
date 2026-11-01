<?php

use App\Exceptions\PenanganGalatApi;
use App\Http\Middleware\IdPermintaan;
use App\Http\Middleware\PaksaJson;
use App\Http\Middleware\SesiSegar;
use App\Http\Middleware\TanpaCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [PaksaJson::class, IdPermintaan::class, TanpaCache::class]);
        $middleware->alias(['sesi.segar' => SesiSegar::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        PenanganGalatApi::daftarkan($exceptions);
    })->create();
