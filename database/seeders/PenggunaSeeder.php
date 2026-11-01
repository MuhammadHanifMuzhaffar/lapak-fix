<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    /** Kata sandi akun contoh. Hanya untuk mesin pengembangan. */
    public const SANDI_CONTOH = 'LapakContoh#2026';

    private const AKUN = [
        ['Admin Lapak', 'admin@lapak.test'],
        ['Staf Keuangan', 'keuangan@lapak.test'],
        ['Auditor Internal', 'auditor@lapak.test'],
        ['Sri Wahyuni', 'sri@lapak.test'],
        ['Wulan Sari', 'wulan@lapak.test'],
        ['Eko Prasetyo', 'eko@lapak.test'],
        ['Tini Lestari', 'tini@lapak.test'],
        ['Andi Nugroho', 'andi@lapak.test'],
        ['Dewi Anggraini', 'dewi@lapak.test'],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('PenggunaSeeder dilewati: akun contoh tidak boleh ada di produksi.');

            return;
        }

        foreach (self::AKUN as [$nama, $surel]) {
            Pengguna::updateOrCreate(
                ['surel' => $surel],
                ['nama' => $nama, 'kata_sandi' => self::SANDI_CONTOH],
            );
        }
    }
}
