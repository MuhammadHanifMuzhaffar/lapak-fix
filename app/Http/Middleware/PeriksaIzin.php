<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\NamaIzin;
use App\Exceptions\GalatDomain;
use App\Services\PemeriksaIzin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PeriksaIzin
{
    public function __construct(private readonly PemeriksaIzin $pemeriksa) {}

    public function handle(Request $request, Closure $next, string $izin): Response
    {
        $pengguna = $request->user();

        if ($pengguna === null) {
            throw new GalatDomain('BELUM_MASUK', 'Anda belum masuk atau sesi tidak berlaku.', 401);
        }

        if (! $this->pemeriksa->punya($pengguna, NamaIzin::from($izin))) {
            throw new GalatDomain('DITOLAK', 'Anda tidak memiliki hak untuk melakukan tindakan ini.', 403);
        }

        return $next($request);
    }
}
