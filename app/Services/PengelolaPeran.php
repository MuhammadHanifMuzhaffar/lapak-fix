<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NamaPeran;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\UniqueConstraintViolationException;
use LogicException;

final class PengelolaPeran
{
    public function __construct(
        private readonly PemeriksaIzin $izin,
        private readonly PenerbitSesi $sesi,
    ) {}

    public function beri(Pengguna $pengguna, NamaPeran $peran, ?Pengguna $oleh = null): bool
    {
        $idPeran = $this->idPeran($peran);

        if ($pengguna->peran()->where('peran.id', $idPeran)->exists()) {
            return false;
        }

        try {
            $pengguna->peran()->attach($idPeran, ['diberikan_oleh' => $oleh?->id]);
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        $this->setelahBerubah($pengguna);

        return true;
    }

    public function cabut(Pengguna $pengguna, NamaPeran $peran): bool
    {
        if ($pengguna->peran()->detach($this->idPeran($peran)) === 0) {
            return false;
        }

        $this->setelahBerubah($pengguna);

        return true;
    }

    /**
     * @return list<string>
     */
    public function daftar(Pengguna $pengguna): array
    {
        return $pengguna->peran()->orderBy('peran.nama')->pluck('peran.nama')->all();
    }

    private function setelahBerubah(Pengguna $pengguna): void
    {
        if (method_exists($this->izin, 'lupakan')) {
            $this->izin->lupakan($pengguna);
        }

        $this->sesi->cabutSemua($pengguna);
    }

    private function idPeran(NamaPeran $peran): int
    {
        $id = Peran::query()->where('nama', $peran->value)->value('id');

        if ($id === null) {
            throw new LogicException("Tabel peran belum memuat peran {$peran->value}. Jalankan seeder.");
        }

        return $id;
    }
}
