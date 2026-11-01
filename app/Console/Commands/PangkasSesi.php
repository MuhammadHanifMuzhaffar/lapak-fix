<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Sesi;
use Illuminate\Console\Command;

final class PangkasSesi extends Command
{
    protected $signature = 'lapak:pangkas-sesi';

    protected $description = 'Menghapus sesi yang sudah mati lebih lama dari masa simpan.';

    public function handle(): int
    {
        $batas = now()->subDays(config('lapak.sesi.simpan_hari'));

        $terhapus = Sesi::query()
            ->where(fn ($kueri) => $kueri
                ->where('dicabut_pada', '<', $batas)
                ->orWhere('kedaluwarsa_pada', '<', $batas))
            ->delete();

        $this->info("{$terhapus} sesi lama dipangkas.");

        return self::SUCCESS;
    }
}
