<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Pesanan\AturanTransisi;
use App\Domain\Pesanan\PelakuPesanan;
use App\Domain\Pesanan\StatusPesanan;
use App\Exceptions\TransisiPesananTidakSah;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Satu-satunya pintu perubahan status pesanan (LP-10).
 * Aturannya sendiri ada di AturanTransisi, yang ditulis dan diuji pada Sesi 3.
 */
final class MesinStatusPesanan
{
    /**
     * Pemanggil wajib sudah berada di dalam GembokCheckout::dalam().
     *
     * @throws TransisiPesananTidakSah 409 LP-10 bila perpindahan itu tidak ada jalurnya
     */
    public function pindahkan(
        Pesanan $pesanan,
        StatusPesanan $ke,
        PelakuPesanan $oleh,
        ?string $alasanBatal = null,
    ): Pesanan {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Status pesanan hanya boleh diubah di dalam GembokCheckout.');
        }

        // Status dibaca ULANG dengan kunci. Salinan di tangan pemanggil bisa sudah basi,
        // dan pembacaan biasa di dalam transaksi MySQL melihat potret lama, bukan keadaan terkini.
        $terkini = Pesanan::query()->lockForUpdate()->findOrFail($pesanan->id);

        if (! AturanTransisi::boleh($terkini->status, $ke, $oleh)) {
            throw new TransisiPesananTidakSah($terkini->status, $ke);
        }

        $cap = match ($ke) {
            StatusPesanan::Dibayar => ['dibayar_pada' => now()],
            StatusPesanan::Dibatalkan => [
                'dibatalkan_pada' => now(),
                'alasan_batal' => $alasanBatal,
            ],
            default => [],
        };

        $terkini->forceFill(['status' => $ke, ...$cap])->save();

        return $terkini;
    }
}
