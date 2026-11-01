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
final class TokoModerasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'slug' => $this->slug,
            'kota' => $this->kota,
            'status' => $this->status->value,
            'ongkir' => [
                'rupiah' => $this->ongkir_rupiah,
                'tampilan' => Uang::dari($this->ongkir_rupiah)->format(),
            ],
            'pemilik' => [
                'id' => $this->pemilik->id,
                'nama' => $this->pemilik->nama,
                'surel' => $this->pemilik->surel,
            ],
            'moderasi' => $this->dimoderasi_pada === null ? null : [
                'oleh' => $this->pemoderasi?->nama,
                'pada' => $this->dimoderasi_pada?->toIso8601String(),
                'catatan' => $this->catatan_moderasi,
            ],
            'dibuat_pada' => $this->created_at?->toIso8601String(),
        ];
    }
}
