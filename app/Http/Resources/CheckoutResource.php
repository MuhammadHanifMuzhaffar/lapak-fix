<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Uang;
use App\Models\Checkout;
use App\Services\LayananPembayaran;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Hasil sebuah checkout: satu tagihan dan satu pesanan per toko (LP-7).
 * Relasi pesanan beserta toko dan butirnya harus sudah dimuat.
 *
 * @mixin Checkout
 */
final class CheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total = Uang::dari($this->total_rupiah);
        $pembayaran = app(LayananPembayaran::class)->untuk($this->resource);

        return [
            'kode' => $this->kode,
            'total' => ['rupiah' => $total, 'tampilan' => $total->format()],
            'pembayaran' => PembayaranResource::make($pembayaran),
            'pesanan' => PesananResource::collection($this->pesanan),
        ];
    }
}
