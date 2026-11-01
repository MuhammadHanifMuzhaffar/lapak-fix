<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\NamaIzin;
use App\Enums\NamaPeran;
use Illuminate\Console\Command;

final class MatriksIzin extends Command
{
    protected $signature = 'lapak:matriks-izin';

    protected $description = 'Menampilkan matriks peran dan izin yang berlaku.';

    public function handle(): int
    {
        $peranList = NamaPeran::cases();
        $header = array_merge(['izin'], array_map(fn (NamaPeran $p) => $p->value, $peranList));

        $baris = [];
        foreach (NamaIzin::cases() as $izin) {
            $data = [$izin->value];
            foreach ($peranList as $peran) {
                $punya = in_array($izin, $peran->izinBawaan(), true);
                $data[] = $punya ? 'v' : '';
            }
            $baris[] = $data;
        }

        $this->table($header, $baris);

        return self::SUCCESS;
    }
}
