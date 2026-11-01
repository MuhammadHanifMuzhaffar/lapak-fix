<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Exceptions\SaldoTidakCukup;
use App\Models\Dompet;
use App\Models\MutasiSaldo;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BukuBesar
{
    public function dompetSistem(JenisDompet $jenis): Dompet
    {
        return $this->ambilAtauBuat($jenis->value, $jenis, null);
    }

    public function dompetPenjual(Pengguna $penjual): Dompet
    {
        return $this->ambilAtauBuat('penjual:'.$penjual->id, JenisDompet::Penjual, $penjual->id);
    }

    public function cariDompetPenjual(Pengguna $penjual): ?Dompet
    {
        return Dompet::query()->where('kode', 'penjual:'.$penjual->id)->first();
    }

    public function pindahkan(
        Dompet $sumber,
        Dompet $tujuan,
        Uang $jumlah,
        JenisMutasi $jenis,
        string $rujukan,
        ?string $catatan = null,
    ): string {
        $kelompok = (string) Str::uuid();

        DB::transaction(function () use ($sumber, $tujuan, $jumlah, $jenis, $rujukan, $catatan, $kelompok): void {
            $sumberDb = Dompet::query()->whereKey($sumber->id)->lockForUpdate()->sole();
            $tujuanDb = Dompet::query()->whereKey($tujuan->id)->lockForUpdate()->sole();

            if (! $sumberDb->jenis->bolehNegatif() && $sumberDb->saldo_rupiah < $jumlah->rupiah()) {
                throw new SaldoTidakCukup($sumberDb, $jumlah->rupiah());
            }

            $saldoSetelahSumber = $sumberDb->saldo_rupiah - $jumlah->rupiah();
            $saldoSetelahTujuan = $tujuanDb->saldo_rupiah + $jumlah->rupiah();

            $sumberDb->saldo_rupiah = $saldoSetelahSumber;
            $sumberDb->save();

            $tujuanDb->saldo_rupiah = $saldoSetelahTujuan;
            $tujuanDb->save();

            MutasiSaldo::query()->create([
                'dompet_id' => $sumberDb->id,
                'kelompok' => $kelompok,
                'jenis' => $jenis,
                'jumlah_rupiah' => -$jumlah->rupiah(),
                'saldo_setelah' => $saldoSetelahSumber,
                'rujukan' => $rujukan,
                'keterangan' => $catatan,
            ]);

            MutasiSaldo::query()->create([
                'dompet_id' => $tujuanDb->id,
                'kelompok' => $kelompok,
                'jenis' => $jenis,
                'jumlah_rupiah' => $jumlah->rupiah(),
                'saldo_setelah' => $saldoSetelahTujuan,
                'rujukan' => $rujukan,
                'keterangan' => $catatan,
            ]);
        });

        return $kelompok;
    }

    private function ambilAtauBuat(string $kode, JenisDompet $jenis, ?int $penggunaId): Dompet
    {
        $dompet = Dompet::query()->where('kode', $kode)->first();

        if ($dompet === null) {
            $dompet = new Dompet([
                'kode' => $kode,
                'jenis' => $jenis,
                'pengguna_id' => $penggunaId,
                'saldo_rupiah' => 0,
            ]);
            $dompet->save();
        }

        return $dompet;
    }
}
