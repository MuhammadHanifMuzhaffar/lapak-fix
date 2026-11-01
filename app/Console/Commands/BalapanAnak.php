<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/** Satu peserta balapan. Dijalankan oleh lapak:balapan, tidak untuk diketik sendiri. */
final class BalapanAnak extends Command
{
    protected $signature = 'lapak:balapan-anak {skenario} {nomor} {muatan}';

    protected $description = 'Proses anak uji balapan';

    protected $hidden = true;

    public function handle(): int
    {
        if (app()->isProduction()) {
            return self::FAILURE;
        }

        $skenario = Balapan::muat($this->argument('skenario'));
        $muatan = base64_decode($this->argument('muatan'));
        $konteks = json_decode($muatan, true, 512, JSON_THROW_ON_ERROR);

        // Sambungan dibuka SEBELUM garis mulai, supaya yang berlomba adalah kuerinya,
        // bukan lamanya membuka sambungan.
        DB::connection()->getPdo();

        $tunggu = $konteks['mulai'] - microtime(true);
        if ($tunggu > 0) {
            usleep((int) ($tunggu * 1_000_000));
        }

        try {
            $hasil = $skenario->jalankan($konteks, (int) $this->argument('nomor'));
        } catch (Throwable $galat) {
            // Galat basis data sudah diawali SQLSTATE; galat lain diawali nama kelasnya.
            $pesan = $galat instanceof QueryException
                ? $galat->getMessage()
                : class_basename($galat).': '.$galat->getMessage();
            $hasil = 'GALAT '.Str::limit($pesan, 64);
        }

        $this->line($hasil);

        return self::SUCCESS;
    }
}
