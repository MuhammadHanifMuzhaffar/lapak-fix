<?php

namespace Database\Seeders;

use App\Enums\NamaPeran;
use App\Models\Pengguna;
use App\Services\PengelolaPeran;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    public const SANDI_CONTOH = 'LapakContoh#2026';

    private const AKUN = [
        ['Admin Lapak', 'admin@lapak.test'],
        ['Staf Keuangan', 'keuangan@lapak.test'],
        ['Auditor Internal', 'auditor@lapak.test'],
        ['Sri Wahyuni', 'sri@lapak.test'],
        ['Tini Lestari', 'tini@lapak.test'],
        ['Andi Prasetyo', 'andi@lapak.test'],
        ['Budi Santoso', 'budi@lapak.test'],
        ['Citra Dewi', 'citra@lapak.test'],
    ];

    public function run(PengelolaPeran $peran): void
    {
        $dibuat = [];

        foreach (self::AKUN as [$nama, $surel]) {
            $dibuat[$surel] = Pengguna::firstOrCreate(
                ['surel' => $surel],
                [
                    'nama' => $nama,
                    'telepon' => '08'.fake()->unique()->numerify('##########'),
                    'kata_sandi' => self::SANDI_CONTOH,
                ],
            );
        }

        $peran->beri($dibuat['admin@lapak.test'], NamaPeran::Admin);
        $peran->beri($dibuat['keuangan@lapak.test'], NamaPeran::Keuangan);
        $peran->beri($dibuat['auditor@lapak.test'], NamaPeran::Auditor);

        foreach (['sri@lapak.test', 'tini@lapak.test', 'andi@lapak.test', 'budi@lapak.test', 'citra@lapak.test'] as $surel) {
            $peran->beri($dibuat[$surel], NamaPeran::Pembeli);
        }
    }
}
