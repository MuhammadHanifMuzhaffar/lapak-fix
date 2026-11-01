<?php

namespace App\Providers;

use App\Models\Pengguna;
use App\Services\PemeriksaSesi;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        $this->daftarkanGuardSesi();
        $this->daftarkanPembatasLaju();
    }

    private function daftarkanGuardSesi(): void
    {
        Auth::viaRequest('sesi', function (Request $request): ?Pengguna {
            $sesi = app(PemeriksaSesi::class)->periksa($request->bearerToken());

            if ($sesi === null) {
                return null;
            }

            $request->attributes->set('sesi', $sesi);
            Log::withContext(['sesi' => $sesi->pengenal, 'pengguna' => $sesi->pengguna_id]);

            return $sesi->pengguna;
        });
    }

    private function daftarkanPembatasLaju(): void
    {
        RateLimiter::for('masuk', fn (Request $request) => [
            Limit::perMinute(config('lapak.laju.masuk_per_akun'))
                ->by('masuk:'.mb_strtolower((string) $request->input('surel')).'|'.$request->ip()),
            Limit::perMinute(config('lapak.laju.masuk_per_ip'))
                ->by('masuk-ip:'.$request->ip()),
        ]);

        RateLimiter::for('konfirmasi', fn (Request $request) => Limit::perMinute(
            config('lapak.laju.konfirmasi'),
        )->by('konfirmasi:'.($request->user()?->id ?? $request->ip())));
    }
}
