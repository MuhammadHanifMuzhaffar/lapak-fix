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
        400 => ['BAD_REQUEST', 'Format permintaan tidak dapat dipahami server.'],
        401 => ['BELUM_MASUK', 'Anda belum masuk atau sesi tidak berlaku.'],
        403 => ['DITOLAK', 'Anda tidak memiliki hak untuk melakukan tindakan ini.'],
        404 => ['TIDAK_DITEMUKAN', 'Data atau alamat yang diminta tidak ditemukan.'],
        405 => ['METODE_DITOLAK', 'Metode HTTP tidak diizinkan untuk alamat ini.'],
        429 => ['TERLALU_SERING', 'Terlalu banyak permintaan. Coba lagi beberapa saat.'],
    ];

    private const PESAN_HTTP_LAIN = ['GALAT_KLIEN', 'Permintaan tidak dapat diproses.'];

    public static function daftarkan(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (Throwable $galat, Request $request): ?JsonResponse {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return self::ubahMenjadiRespons($galat);
        });
    }

    public static function ubahMenjadiRespons(Throwable $galat): JsonResponse
    {
        return match (true) {
            $galat instanceof GalatDomain => self::bentuk($galat->statusHttp, $galat->kode, $galat->getMessage(), $galat->rincian),
            $galat instanceof ValidationException => self::bentuk(
                422, 'VALIDASI', 'Data yang dikirim tidak memenuhi syarat.', $galat->errors(),
            ),
            $galat instanceof AuthenticationException => self::bentuk(401, ...self::PESAN_HTTP[401])
                ->header('WWW-Authenticate', 'Bearer'),
            $galat instanceof HttpExceptionInterface => self::bentuk(
                $galat->getStatusCode(),
                ...(self::PESAN_HTTP[$galat->getStatusCode()] ?? self::PESAN_HTTP_LAIN),
            )->withHeaders($galat->getHeaders()),
            default => self::bentuk(
                500,
                'GALAT_SERVER',
                'Terjadi kesalahan pada server.',
                config('app.debug') ? ['pesan_asli' => $galat->getMessage()] : [],
            ),
        };
    }

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
