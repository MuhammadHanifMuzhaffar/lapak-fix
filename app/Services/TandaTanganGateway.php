<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Memeriksa bahwa sebuah webhook benar-benar dikirim gerbang pembayaran.
 *
 * Pintu webhook tidak dapat memakai kunci sesi: pemanggilnya server lain, bukan pengguna.
 * Sebagai gantinya, gerbang dan Lapak memegang satu rahasia bersama. Gerbang mengirim header
 *
 *     X-Gateway-Signature: t=<detik unix>,v1=<HMAC-SHA256 atas "<t>.<badan mentah>">
 *
 * Siapa pun dapat mengirim JSON ke alamat ini. Hanya pemegang rahasia yang dapat menandatanganinya.
 */
final class TandaTanganGateway
{
    public const HEADER = 'X-Gateway-Signature';

    /** Dipakai simulasi gerbang dan uji. Di dunia nyata, gerbanglah yang menandatangani. */
    public function buat(string $badan, ?int $waktu = null, ?string $rahasia = null): string
    {
        $waktu ??= now()->getTimestamp();
        $rahasia ??= (string) config('lapak.gateway.rahasia');

        return "t={$waktu},v1=".hash_hmac('sha256', "{$waktu}.{$badan}", $rahasia);
    }

    public function sah(?string $header, string $badan): bool
    {
        $rahasia = (string) config('lapak.gateway.rahasia');

        // Gagal tertutup: rahasia yang lupa diisi menolak SEMUA webhook, bukan menerima semuanya.
        if ($rahasia === '' || $header === null) {
            return false;
        }

        if (preg_match('/\At=(\d{1,12}),v1=([0-9a-f]{64})\z/', $header, $bagian) !== 1) {
            return false;
        }

        [, $waktu, $tanda] = $bagian;

        // Webhook lama yang direkam orang di tengah jalan tidak dapat diputar ulang besok.
        if (abs(now()->getTimestamp() - (int) $waktu) > config('lapak.gateway.toleransi_detik')) {
            return false;
        }

        // hash_equals membandingkan dalam waktu tetap, seperti pada kunci sesi.
        return hash_equals(hash_hmac('sha256', "{$waktu}.{$badan}", $rahasia), $tanda);
    }
}
