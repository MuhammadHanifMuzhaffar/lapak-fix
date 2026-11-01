<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Pesanan\PelakuPesanan;
use App\Domain\Pesanan\StatusPesanan;
use App\Models\Checkout;
use App\Models\Pembayaran;

final class PembatalanOtomatis
{
    public const ALASAN = 'Tidak dibayar sampai batas waktu';

    public function __construct(
        private readonly GembokCheckout $gembok,
        private readonly LayananPembayaran $pembayaran,
        private readonly MesinStatusPesanan $mesin,
        private readonly PenjagaStok $stok,
    ) {}

    /** @return int jumlah checkout yang dibatalkan */
    public function jalankan(): int
    {
        $dibatalkan = 0;

        foreach (Pembayaran::query()->lewatBatas()->orderBy('id')->pluck('checkout_id') as $id) {
            $dibatalkan += $this->batalkan($id) ? 1 : 0;
        }

        return $dibatalkan;
    }

    public function batalkan(int|Checkout $checkout): bool
    {
        $id = $checkout instanceof Checkout ? $checkout->id : $checkout;

        return $this->gembok->dalam($id, function (Checkout $checkout): bool {
            if (! $this->pembayaran->tandaiKedaluwarsa($checkout)) {
                return false;
            }

            $kembali = [];

            foreach ($checkout->pesanan()->with('butir')->orderBy('id')->get() as $pesanan) {
                $this->mesin->pindahkan(
                    $pesanan,
                    StatusPesanan::Dibatalkan,
                    PelakuPesanan::Sistem,
                    self::ALASAN,
                );

                foreach ($pesanan->butir as $butir) {
                    $kembali[$butir->produk_id] = ($kembali[$butir->produk_id] ?? 0)
                        + $butir->kuantitas;
                }
            }

            ksort($kembali);

            foreach ($kembali as $idProduk => $kuantitas) {
                $this->stok->kembalikan($idProduk, $kuantitas);
            }

            return true;
        });
    }

    public function sapuSemua(): int
    {
        return $this->jalankan();
    }
}
