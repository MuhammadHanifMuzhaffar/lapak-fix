<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Sesi\KunciSesi;
use App\Models\Pengguna;
use App\Models\Sesi;
use Illuminate\Support\Str;

final class PenerbitSesi
{
    public function terbitkan(
        Pengguna $pengguna,
        string $namaPerangkat,
        ?string $ip = null,
        ?string $agenPengguna = null,
    ): array {
        $kunci = KunciSesi::baru((string) Str::ulid());

        $sesi = new Sesi;
        $sesi->forceFill([
            'pengenal' => $kunci->pengenal,
            'pengguna_id' => $pengguna->id,
            'hash_kunci' => $kunci->hash(),
            'nama_perangkat' => Str::limit($namaPerangkat, 57),
            'ip' => $ip,
            'agen_pengguna' => $agenPengguna === null ? null : Str::limit($agenPengguna, 252),
            'dikonfirmasi_pada' => now(),
            'terakhir_dipakai_pada' => now(),
            'kedaluwarsa_pada' => now()->addDays(config('lapak.sesi.masa_hidup_hari')),
        ])->save();

        return [$sesi, $kunci->teks()];
    }

    public function rotasi(Sesi $sesi): string
    {
        $kunci = KunciSesi::baru($sesi->pengenal);
        $sesi->forceFill(['hash_kunci' => $kunci->hash()])->save();

        return $kunci->teks();
    }

    public function tandaiSegar(Sesi $sesi): void
    {
        $sesi->forceFill(['dikonfirmasi_pada' => now()])->save();
    }

    public function cabut(Sesi $sesi): void
    {
        if ($sesi->dicabut_pada === null) {
            $sesi->forceFill(['dicabut_pada' => now()])->save();
        }
    }
}
