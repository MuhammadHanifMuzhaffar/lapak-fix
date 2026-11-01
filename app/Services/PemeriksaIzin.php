<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NamaIzin;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class PemeriksaIzin
{
    public function punya(Pengguna $pengguna, NamaIzin $izin): bool
    {
        return in_array($izin->value, $this->daftar($pengguna), true);
    }

    public function daftar(Pengguna $pengguna): array
    {
        return Cache::remember(
            $this->kunciCache($pengguna),
            now()->addMinutes(config('lapak.izin.ingat_menit', 120)),
            fn (): array => DB::table('izin')
                ->join('peran_izin', 'peran_izin.izin_id', '=', 'izin.id')
                ->join('pengguna_peran', 'pengguna_peran.peran_id', '=', 'peran_izin.peran_id')
                ->where('pengguna_peran.pengguna_id', $pengguna->id)
                ->distinct()
                ->orderBy('izin.nama')
                ->pluck('izin.nama')
                ->all(),
        );
    }

    public function lupakan(Pengguna $pengguna): void
    {
        Cache::forget($this->kunciCache($pengguna));
    }

    public function lupakanSemua(): void
    {
        Cache::flush();
    }

    private function kunciCache(Pengguna $pengguna): string
    {
        return "pengguna:{$pengguna->id}:izin";
    }
}
