<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Sesi\KunciSesi;
use App\Models\Pengguna;
use App\Models\Sesi;
use Illuminate\Support\Str;

class PenerbitSesi
{
    public function terbitkan(
        Pengguna $pengguna,
        string $namaPerangkat,
        ?string $ip = null,
        ?string $agenPengguna = null,
    ): array {
        $pengenal = (string) Str::ulid();
        $rahasia = hash('sha256', Str::random(40));
        $hashKunci = hash('sha256', $rahasia);

        $sesi = new Sesi;
        $sesi->forceFill([
            'pengenal' => $pengenal,
            'pengguna_id' => $pengguna->id,
            'hash_kunci' => $hashKunci,
            'nama_perangkat' => $namaPerangkat,
            'ip' => $ip,
            'agen_pengguna' => $agenPengguna,
            'terakhir_dipakai_pada' => now(),
            'dikonfirmasi_pada' => now(),
            'kedaluwarsa_pada' => now()->addDays(config('lapak.sesi.masa_hidup_hari')),
        ])->save();

        return [$sesi, KunciSesi::dari($pengenal, $rahasia)->string()];
    }

    public function cabut(Sesi $sesi): void
    {
        $sesi->forceFill(['dicabut_pada' => now()])->save();
    }

    public function cabutSemua(Pengguna $pengguna): int
    {
        return Sesi::query()
            ->berlaku()
            ->where('pengguna_id', $pengguna->id)
            ->update(['dicabut_pada' => now()]);
    }

    public function tandaiSegar(Sesi $sesi): void
    {
        $sesi->forceFill(['dikonfirmasi_pada' => now()])->save();
    }

    public function rotasi(Sesi $sesi): string
    {
        $rahasiaBaru = hash('sha256', Str::random(40));

        $sesi->forceFill([
            'hash_kunci' => hash('sha256', $rahasiaBaru),
            'terakhir_dipakai_pada' => now(),
        ])->save();

        return KunciSesi::dari($sesi->pengenal, $rahasiaBaru)->string();
    }
}
