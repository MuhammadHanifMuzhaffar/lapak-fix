<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Uang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Produk
 */
final class ProdukResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
            'harga' => [
                'rupiah' => $this->harga_rupiah,
                'tampilan' => Uang::dari($this->harga_rupiah)->format(),
            ],
            'stok' => $this->stok,
            'tersedia' => $this->stok > 0,
            'berat_gram' => $this->berat_gram,
            'kategori' => $this->whenLoaded('kategori', fn () => [
                'nama' => $this->kategori->nama,
                'slug' => $this->kategori->slug,
            ]),
            'toko' => $this->whenLoaded('toko', fn () => [
                'nama' => $this->toko->nama,
                'slug' => $this->toko->slug,
                'kota' => $this->toko->kota,
            ]),
        ];
    }
}
