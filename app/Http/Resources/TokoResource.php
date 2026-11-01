<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Uang;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Toko
 */
final class TokoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'nama' => $this->nama,
            'slug' => $this->slug,
            'kota' => $this->kota,
            'deskripsi' => $this->deskripsi,
            'ongkir' => [
                'rupiah' => $this->ongkir_rupiah,
                'tampilan' => Uang::dari($this->ongkir_rupiah)->format(),
            ],
            'jumlah_produk' => $this->whenCounted('produk'),
        ];
    }
}
