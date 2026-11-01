<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Pesanan\PelakuPesanan;
use App\Domain\Pesanan\StatusPesanan;
use App\Models\Checkout;
use App\Models\Pembayaran;
use Illuminate\Support\Carbon;

final class PembatalanOtomatis
{
    public function __construct(
        private readonly GembokCheckout $gembok,
        private readonly MesinStatusPesanan $mesin,
        private readonly PenjagaStok $stok,
    ) {}

    public function batalkan(int|Checkout $checkout): bool
    {
        $id = $checkout instanceof Checkout ? $checkout->id : $checkout;

        return $this->gembok->dalam($id, function (Checkout $checkout): bool {
            $pembayaran = Pembayaran::query()
                ->where('checkout_id', $checkout->id)
                ->lockForUpdate()
                ->first();

            if ($pembayaran === null || Carbon::parse($pembayaran->batas_bayar_pada)->isFuture()) {
                return false;
            }

            if ($pembayaran->status->selesai()) {
                return false;
            }

            $kembali = [];

            foreach ($checkout->pesanan()->with('butir')->orderBy('id')->get() as $pesanan) {
                $this->mesin->pindahkan(
                    $pesanan,
                    StatusPesanan::Dibatalkan,
                    PelakuPesanan::Sistem,
                    'Batas waktu pembayaran habis.',
                );

                foreach ($pesanan->butir as $butir) {
                    $kembali[$butir->produk_id] = ($kembali[$butir->produk_id] ?? 0)
                        + $butir->kuantitas;
                }
            }

            // Urutan kunci tim (docs/PETA-SKEMA.md): seluruh pesanan dulu, lalu produk urut nomor
            // menaik. Checkout mengunci produk dengan urutan yang sama. Bila di sini stok
            // dikembalikan menurut urutan butir, keduanya dapat saling menunggu selamanya.
            ksort($kembali);

            foreach ($kembali as $idProduk => $kuantitas) {
                $this->stok->kembalikan($idProduk, $kuantitas);
            }

            return true;
        });
    }

    public function sapuSemua(): int
    {
        $kedaluwarsa = Pembayaran::query()
            ->where('batas_bayar_pada', '<=', now())
            ->whereNotIn('status', ['lunas', 'batal'])
            ->pluck('checkout_id');

        $jumlah = 0;
        foreach ($kedaluwarsa as $id) {
            $checkout = Checkout::find($id);
            if ($checkout && $this->batalkan($checkout)) {
                $jumlah++;
            }
        }

        return $jumlah;
    }
}
