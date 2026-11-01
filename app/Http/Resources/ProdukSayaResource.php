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
final class ProdukSayaResource extends JsonResource
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
            'berat_gram' => $this->berat_gram,
            'status' => $this->status->value,
            'kategori_id' => $this->kategori_id,
        ];
    }
}
