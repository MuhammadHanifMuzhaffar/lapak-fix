<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Uang;
use App\Models\Dompet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dompet
 */
final class DompetBukuBesarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'jenis' => $this->jenis->value,
            'saldo' => [
                'rupiah' => $this->saldo_rupiah,
                'tampilan' => Uang::dari(abs($this->saldo_rupiah))->format(),
            ],
            'pemilik' => $this->pemilik === null ? null : [
                'id' => $this->pemilik->id,
                'nama' => $this->pemilik->nama,
                'surel' => $this->pemilik->surel,
            ],
            'terakhir_mutasi_pada' => $this->updated_at?->toIso8601String(),
        ];
    }
}
