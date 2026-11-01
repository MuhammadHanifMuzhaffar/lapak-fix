<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Exceptions\GalatDomain;
use App\Exceptions\SaldoTidakCukup;
use App\Models\Dompet;
use App\Models\MutasiSaldo;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Satu-satunya pintu perubahan saldo (LP-13).
 * Uang tidak pernah "ditambahkan" ke sebuah dompet; uang selalu DIPINDAHKAN dari satu dompet
 * ke dompet lain. Setiap pemindahan menghasilkan dua baris mutasi yang berjumlah nol.
 */
class BukuBesar
{
    public function dompetSistem(JenisDompet $jenis): Dompet
    {
        if ($jenis === JenisDompet::Penjual) {
            throw new GalatDomain('LP-13', 'Dompet penjual harus diminta lewat dompetPenjual().');
        }

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

    /**
     * @return string kelompok mutasi, untuk ditelusuri di kemudian hari
     */
    public function pindahkan(
        Dompet $sumber,
        Dompet $tujuan,
        Uang $jumlah,
        JenisMutasi $jenis,
        string $rujukan,
        ?string $catatan = null,
    ): string {
        if ($jumlah->rupiah === 0) {
            throw new GalatDomain('LP-13', 'Jumlah pemindahan harus lebih dari nol.');
        }
        if ($sumber->is($tujuan)) {
            throw new GalatDomain('LP-13', 'Dompet asal dan tujuan tidak boleh sama.');
        }

        return DB::transaction(function () use (
            $sumber,
            $tujuan,
            $jumlah,
            $jenis,
            $rujukan,
            $catatan,
        ): string {
            // Kunci kedua baris, selalu urut id menaik untuk mencegah deadlock.
            $terkunci = Dompet::query()
                ->whereIn('id', [$sumber->id, $tujuan->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $asal = $terkunci[$sumber->id];
            $ke = $terkunci[$tujuan->id];

            if (! $asal->jenis->bolehNegatif() && $asal->saldo_rupiah < $jumlah->rupiah) {
                throw new SaldoTidakCukup($asal->kode, $asal->saldo_rupiah, $jumlah->rupiah);
            }

            $kelompok = (string) Str::uuid();
            $this->catat($asal, -$jumlah->rupiah, $kelompok, $jenis, $rujukan, $catatan);
            $this->catat($ke, $jumlah->rupiah, $kelompok, $jenis, $rujukan, $catatan);

            return $kelompok;
        });
    }

    private function catat(
        Dompet $dompet,
        int $jumlahBertanda,
        string $kelompok,
        JenisMutasi $jenis,
        string $rujukan,
        ?string $keterangan,
    ): void {
        $saldoBaru = $dompet->saldo_rupiah + $jumlahBertanda;

        $mutasi = new MutasiSaldo;
        $mutasi->forceFill([
            'dompet_id' => $dompet->id,
            'kelompok' => $kelompok,
            'jenis' => $jenis,
            'jumlah_rupiah' => $jumlahBertanda,
            'saldo_setelah' => $saldoBaru,
            'rujukan' => $rujukan,
            'keterangan' => $keterangan,
        ])->save();

        $dompet->forceFill(['saldo_rupiah' => $saldoBaru])->save();
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
