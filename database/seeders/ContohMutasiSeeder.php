<?php

namespace Database\Seeders;

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Models\MutasiSaldo;
use App\Models\Pengguna;
use App\Services\BukuBesar;
use Illuminate\Database\Seeder;

class ContohMutasiSeeder extends Seeder
{
    private const RUJUKAN = 'LPK-CONTOH-0001';

    public function run(BukuBesar $buku): void
    {
        if (app()->isProduction() || MutasiSaldo::query()->where('rujukan', self::RUJUKAN)->exists()) {
            return;
        }

        $sri = Pengguna::query()->where('surel', 'sri@lapak.test')->firstOrFail();
        $luar = $buku->dompetSistem(JenisDompet::Eksternal);
        $escrow = $buku->dompetSistem(JenisDompet::Escrow);

        $pendapatan = $buku->dompetSistem(JenisDompet::Pendapatan);
        $penjual = $buku->dompetPenjual($sri);

        $pindah = fn ($dari, $ke, int $rupiah, JenisMutasi $jenis, string $catatan) => $buku
            ->pindahkan($dari, $ke, Uang::dari($rupiah), $jenis, self::RUJUKAN, $catatan);

        $pindah($luar, $escrow, 382_000, JenisMutasi::DanaMasuk, 'Pembayaran pembeli');
        $pindah($escrow, $pendapatan, 18_500, JenisMutasi::Komisi, 'Komisi 5%');
        $pindah($escrow, $penjual, 363_500, JenisMutasi::LepasDana, 'Dana dilepas');
    }
}
