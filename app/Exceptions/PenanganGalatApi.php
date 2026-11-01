<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class PenanganGalatApi
{
    private const PESAN_HTTP = [
        401 => ['BELUM_MASUK', 'Anda belum masuk atau sesi tidak berlaku.'],
        403 => ['DITOLAK', 'Anda tidak berwenang melakukan tindakan ini.'],
        404 => ['TIDAK_DITEMUKAN', 'Sumber daya tidak ditemukan.'],
        405 => ['METODE_TIDAK_DIIZINKAN', 'Metode HTTP tidak diizinkan pada alamat ini.'],
        429 => ['TERLALU_SERING', 'Terlalu banyak permintaan. Coba lagi sebentar.'],
    ];

    private const PESAN_HTTP_LAIN = ['GALAT_HTTP', 'Permintaan tidak dapat diproses.'];

    public static function daftarkan(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $exceptions->render(function (Throwable $galat, Request $request): ?JsonResponse {
            return $request->is('api/*') ? self::ubah($galat) : null;
        });
    }

    private static function ubah(Throwable $galat): JsonResponse
    {
        return match (true) {
            $galat instanceof GalatDomain => self::bentuk(
                $galat->statusHttp,
                $galat->kode,
                $galat->getMessage(),
                $galat->rincian,
            ),
            $galat instanceof ValidationException => self::bentuk(
                422,
                'VALIDASI',
                'Data yang dikirim tidak memenuhi syarat.',
                $galat->errors(),
            ),
            $galat instanceof AuthenticationException => self::bentuk(401, ...self::PESAN_HTTP[401]),
            $galat instanceof HttpExceptionInterface => self::bentuk(
                $galat->getStatusCode(),
                ...(self::PESAN_HTTP[$galat->getStatusCode()] ?? self::PESAN_HTTP_LAIN),
            ),
            default => self::bentuk(
                500,
                'GALAT_SERVER',
                'Terjadi kesalahan pada server.',
                config('app.debug') ? ['pesan_asli' => $galat->getMessage()] : [],
            ),
        };
    }

    /**
     * @param  array<string, mixed>  $rincian
     */
    private static function bentuk(
        int $status,
        string $kode,
        string $pesan,
        array $rincian = [],
    ): JsonResponse {
        return response()->json([
            'sukses' => false,
            'galat' => [
                'kode' => $kode,
                'pesan' => $pesan,
                'rincian' => $rincian === [] ? null : $rincian,
            ],
        ], $status);
    }
}
