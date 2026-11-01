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
use Illuminate\Database\Eloquent\Builder;
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
        return $this->ambilAtauBuat($this->kodePenjual($penjual), JenisDompet::Penjual, $penjual->id);
    }

    public function cariDompetPenjual(Pengguna $penjual): ?Dompet
    {
        return Dompet::query()->where('kode', $this->kodePenjual($penjual))->first();
    }

    public function mutasiPenjual(Pengguna $penjual): Builder
    {
        $kode = $this->kodePenjual($penjual);

        return MutasiSaldo::query()
            ->whereHas('dompet', fn (Builder $dompet) => $dompet->where('kode', $kode))
            ->orderByDesc('id');
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

            if (! $sumberDb->jenis->bolehNegatif() && $sumberDb->saldo_rupiah < $jumlah->rupiah) {
                throw new SaldoTidakCukup($sumberDb, $jumlah->rupiah);
            }

            $saldoSetelahSumber = $sumberDb->saldo_rupiah - $jumlah->rupiah;
            $saldoSetelahTujuan = $tujuanDb->saldo_rupiah + $jumlah->rupiah;

            $sumberDb->saldo_rupiah = $saldoSetelahSumber;
            $sumberDb->save();

            $tujuanDb->saldo_rupiah = $saldoSetelahTujuan;
            $tujuanDb->save();

            (new MutasiSaldo)->forceFill([
                'dompet_id' => $sumberDb->id,
                'kelompok' => $kelompok,
                'jenis' => $jenis,
                'jumlah_rupiah' => -$jumlah->rupiah,
                'saldo_setelah' => $saldoSetelahSumber,
                'rujukan' => $rujukan,
                'keterangan' => $catatan,
            ])->save();

            (new MutasiSaldo)->forceFill([
                'dompet_id' => $tujuanDb->id,
                'kelompok' => $kelompok,
                'jenis' => $jenis,
                'jumlah_rupiah' => $jumlah->rupiah,
                'saldo_setelah' => $saldoSetelahTujuan,
                'rujukan' => $rujukan,
                'keterangan' => $catatan,
            ])->save();
        });

        return $kelompok;
    }

    private function kodePenjual(Pengguna $penjual): string
    {
        return 'penjual:'.$penjual->id;
    }

    private function ambilAtauBuat(string $kode, JenisDompet $jenis, ?int $penggunaId): Dompet
    {
        $dompet = Dompet::query()->where('kode', $kode)->first();

        if ($dompet === null) {
            $dompet = new Dompet;
            $dompet->forceFill([
                'kode' => $kode,
                'jenis' => $jenis,
                'pengguna_id' => $penggunaId,
                'saldo_rupiah' => 0,
            ])->save();
        }

        return $dompet;
    }
}
