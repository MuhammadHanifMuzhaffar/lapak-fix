<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pengguna
 */
final class PenggunaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'surel' => $this->surel,
            'telepon' => $this->telepon,
            'status' => $this->status->value,
            'terdaftar_pada' => $this->created_at?->toIso8601String(),
        ];
    }
}
