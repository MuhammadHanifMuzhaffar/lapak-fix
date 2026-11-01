<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NamaIzin;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;

final class PemeriksaIzin
{
    public function punya(Pengguna $pengguna, NamaIzin $izin): bool
    {
        return in_array($izin->value, $this->daftar($pengguna), true);
    }

    /**
     * @return list<string> seluruh izin yang dipegang pengguna, terurut abjad tanpa duplikat
     */
    public function daftar(Pengguna $pengguna): array
    {
        return DB::table('izin')
            ->join('peran_izin', 'peran_izin.izin_id', '=', 'izin.id')
            ->join('pengguna_peran', 'pengguna_peran.peran_id', '=', 'peran_izin.peran_id')
            ->where('pengguna_peran.pengguna_id', $pengguna->id)
            ->distinct()
            ->orderBy('izin.nama')
            ->pluck('izin.nama')
            ->all();
    }
}
