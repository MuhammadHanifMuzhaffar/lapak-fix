<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MutasiSaldo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MutasiSaldo
 */
final class MutasiBukuBesarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...MutasiResource::make($this->resource)->toArray($request),
            'kelompok' => $this->kelompok,
        ];
    }
}
