<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Belanja\BarisBelanja;
use App\Domain\Belanja\RincianToko;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RincianToko
 */
final class RincianTokoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'toko' => $this->namaToko,
            'baris' => array_map(fn (BarisBelanja $baris) => [
                'produk_id' => $baris->produkId,
                'nama' => $baris->namaProduk,
                'harga_satuan' => $baris->hargaSatuan,
                'kuantitas' => $baris->kuantitas,
                'subtotal' => $baris->subtotal(),
            ], $this->baris),
            'subtotal' => $this->subtotal,
            'ongkir' => $this->ongkir,
            'total' => $this->total(),
        ];
    }
}
