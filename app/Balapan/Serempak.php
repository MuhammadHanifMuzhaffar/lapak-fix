<?php

declare(strict_types=1);

namespace App\Balapan;

/**
 * Skenario kalibrasi: tidak menyentuh basis data. Ia hanya memeriksa bahwa mesin ini sanggup

 * melepas seluruh proses dalam jendela waktu yang sempit. Jalankan sebelum skenario lain;
 * bila ada proses yang terlambat, hasil balapan di mesin ini tidak dapat dipercaya.
 */
final class Serempak implements SkenarioBalapan
{
    private const JENDELA_DETIK = 0.25;

    public function keterangan(): string
    {
        return 'Semua proses mulai dalam jendela '.(int) (self::JENDELA_DETIK * 1000).' milidetik';
    }

    public function proses(): int
    {
        return 8;
    }

    public function siapkan(): array
    {
        return [];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        // $konteks['mulai'] diisi perintah lapak:balapan untuk setiap skenario.
        $selisih = microtime(true) - $konteks['mulai'];

        return $selisih <= self::JENDELA_DETIK ? 'tepat waktu' : 'terlambat';
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $terlambat = count(array_filter($hasil, fn (string $h) => $h !== 'tepat waktu'));

        return $terlambat === 0 ? [] : ["{$terlambat} proses mulai terlambat."];
    }
}
