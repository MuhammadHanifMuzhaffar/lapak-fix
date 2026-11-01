<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\JenisDompet;
use App\Models\Dompet;
use App\Models\MutasiSaldo;
use Illuminate\Console\Command;

final class PeriksaBuku extends Command
{
    protected $signature = 'lapak:periksa-buku';

    protected $description = 'Memeriksa empat invarian buku besar (LP-13); kode keluar 1 bila dilanggar.';

    public function handle(): int
    {
        $pelanggaran = [];

        // 1. Uang tidak tercipta dan tidak lenyap: jumlah seluruh mutasi harus nol.
        $jumlahSemua = (int) MutasiSaldo::query()->sum('jumlah_rupiah');
        if ($jumlahSemua !== 0) {
            $pelanggaran[] = "Jumlah seluruh mutasi adalah {$jumlahSemua}, seharusnya 0.";
        }

        // 2. Setiap pemindahan terdiri atas tepat dua baris yang saling meniadakan.
        $kelompokTimpang = MutasiSaldo::query()
            ->select('kelompok')
            ->groupBy('kelompok')
            ->havingRaw('count(*) <> 2 or sum(jumlah_rupiah) <> 0')
            ->pluck('kelompok');
        foreach ($kelompokTimpang as $kelompok) {
            $pelanggaran[] = "Kelompok {$kelompok} timpang.";
        }

        $baris = [];
        foreach (Dompet::query()->withSum('mutasi', 'jumlah_rupiah')->orderBy('id')->get() as $dompet) {
            $jumlahMutasi = (int) $dompet->mutasi_sum_jumlah_rupiah;

            // 3. Saldo tersimpan harus sama dengan jumlah mutasinya.
            if ($dompet->saldo_rupiah !== $jumlahMutasi) {
                $pelanggaran[] = "Saldo {$dompet->kode} tercatat {$dompet->saldo_rupiah}, "
                    ."jumlah mutasinya {$jumlahMutasi}.";
            }

            // 4. Hanya dompet eksternal yang boleh negatif.
            if ($dompet->saldo_rupiah < 0 && $dompet->jenis !== JenisDompet::Eksternal) {
                $pelanggaran[] = "Saldo {$dompet->kode} negatif.";
            }

            $saldo = number_format($dompet->saldo_rupiah, 0, ',', '.');
            $baris[] = [$dompet->kode, $dompet->jenis->value, $saldo];
        }

        $this->table(['Dompet', 'Jenis', 'Saldo (Rp)'], $baris);

        if ($pelanggaran !== []) {
            foreach ($pelanggaran as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        $this->info('Buku besar seimbang: '.MutasiSaldo::query()->count().' mutasi, 0 pelanggaran.');

        return self::SUCCESS;
    }
}
