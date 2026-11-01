<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Sesi\KunciSesi;
use App\Enums\StatusAkun;
use App\Models\Sesi;

final class PemeriksaSesi
{
    public function periksa(?string $teksKunci): ?Sesi
    {
        $kunci = KunciSesi::urai($teksKunci ?? '');
        if ($kunci === null) {
            return null;
        }

        $sesi = Sesi::query()
            ->berlaku()
            ->with('pengguna')
            ->where('pengenal', $kunci->pengenal)
            ->first();
        if ($sesi === null || ! $kunci->cocok($sesi->hash_kunci)) {
            return null;
        }

        if ($sesi->pengguna->status !== StatusAkun::Aktif) {
            return null;
        }

        $this->sentuh($sesi);

        return $sesi;
    }

    private function sentuh(Sesi $sesi): void
    {
        $batas = now()->subSeconds(config('lapak.sesi.jeda_sentuh_detik'));

        if ($sesi->terakhir_dipakai_pada->lt($batas)) {
            $sesi->forceFill(['terakhir_dipakai_pada' => now()])->save();
        }
    }
}
