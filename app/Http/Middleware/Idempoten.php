<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\GalatIdempotensi;
use App\Models\KunciIdempotensi;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class Idempoten
{
    private const POLA_KUNCI = '/\A[A-Za-z0-9_-]{16,64}\z/';

    public function handle(Request $request, Closure $next): Response
    {
        $kunci = (string) $request->header('Idempotency-Key');

        if (preg_match(self::POLA_KUNCI, $kunci) !== 1) {
            throw GalatIdempotensi::kunciTidakSah();
        }

        $idPengguna = $request->user()->id;
        $rute = $request->method().' '.$request->path();

        $sidik = hash('sha256', $rute."\n".$request->getContent());

        DB::beginTransaction();

        try {
            $catatan = new KunciIdempotensi;
            $catatan->forceFill([
                'pengguna_id' => $idPengguna,
                'kunci' => $kunci,
                'rute' => $rute,
                'sidik' => $sidik,
            ])->save();
        } catch (QueryException|UniqueConstraintViolationException) {
            DB::rollBack();

            return $this->ulangi($idPengguna, $kunci, $sidik);
        }

        try {
            $respons = $next($request);
        } catch (Throwable $galat) {
            DB::rollBack();

            throw $galat;
        }

        if (! $respons->isSuccessful()) {
            DB::rollBack();

            return $respons;
        }

        $catatan->forceFill([
            'kode_status' => $respons->getStatusCode(),
            'badan_respons' => $respons->getContent(),
        ])->save();

        DB::commit();

        return $respons;
    }

    private function ulangi(int $idPengguna, string $kunci, string $sidik): Response
    {
        $catatan = null;
        for ($i = 0; $i < 6; $i++) {
            $catatan = KunciIdempotensi::query()
                ->where('pengguna_id', $idPengguna)
                ->where('kunci', $kunci)
                ->first();

            if ($catatan !== null && $catatan->kode_status !== null) {
                break;
            }

            usleep(150000);
        }

        if ($catatan === null || $catatan->kode_status === null) {
            throw GalatIdempotensi::sedangDiproses();
        }

        if ($catatan->sidik !== $sidik) {
            throw GalatIdempotensi::dipakaiPermintaanLain();
        }

        return response($catatan->badan_respons, $catatan->kode_status, [
            'Content-Type' => 'application/json',
            'Idempotency-Replayed' => 'true',
        ]);
    }
}
