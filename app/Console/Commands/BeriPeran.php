<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\NamaPeran;
use App\Exceptions\PeranMengikutiToko;
use App\Models\Pengguna;
use App\Services\PengelolaPeran;
use Illuminate\Console\Command;

final class BeriPeran extends Command
{
    protected $signature = 'lapak:beri-peran {surel : Surel pengguna sasaran} {peran : Nama peran}';

    protected $description = 'Memberi peran kepada pengguna lewat baris perintah (untuk admin pertama dan darurat).';

    public function handle(PengelolaPeran $pengelola): int
    {
        $pengguna = Pengguna::query()->where('surel', $this->argument('surel'))->first();

        if ($pengguna === null) {
            $this->error("Pengguna dengan surel {$this->argument('surel')} tidak ditemukan.");

            return self::FAILURE;
        }

        $peran = NamaPeran::tryFrom($this->argument('peran'));
        if ($peran === null) {
            $pilihan = implode(', ', array_map(fn (NamaPeran $p) => $p->value, NamaPeran::cases()));
            $this->error("Peran {$this->argument('peran')} tidak dikenal. Pilihan: {$pilihan}.");

            return self::INVALID;
        }

        if ($peran === NamaPeran::Penjual) {
            $this->error((new PeranMengikutiToko)->getMessage());

            return self::FAILURE;
        }

        if (! $pengelola->beri($pengguna, $peran)) {
            $this->warn("{$pengguna->nama} sudah memegang peran {$peran->value}.");

            return self::SUCCESS;
        }

        $this->info("Peran {$peran->value} diberikan kepada {$pengguna->nama} ({$pengguna->surel}).");

        return self::SUCCESS;
    }
}
