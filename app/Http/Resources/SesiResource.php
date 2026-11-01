<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Sesi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Sesi
 */
final class SesiResource extends JsonResource
{
    private bool $baruSajaDipakai = false;

    public static function saatIni(Sesi $sesi): self
    {
        $resource = new self($sesi);
        $resource->baruSajaDipakai = true;

        return $resource;
    }

    public function toArray(Request $request): array
    {
        return [
            'pengenal' => $this->pengenal,
            'nama_perangkat' => $this->nama_perangkat,
            'ip' => $this->ip,
            'sedang_dipakai' => $this->baruSajaDipakai
                || $this->pengenal === $request->attributes->get('sesi')?->pengenal,
            'segar' => $this->segar(),
            'dibuat_pada' => $this->created_at?->toIso8601String(),
            'terakhir_dipakai_pada' => $this->terakhir_dipakai_pada->toIso8601String(),
            'kedaluwarsa_pada' => $this->kedaluwarsa_pada->toIso8601String(),
        ];
    }
}
