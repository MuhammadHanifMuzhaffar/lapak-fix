<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Uang;
use App\Models\MutasiSaldo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MutasiSaldo
 */
class MutasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tanda = $this->jumlah_rupiah < 0 ? '-' : '+';

        return [
            'jenis' => $this->jenis->value,
            'jumlah' => [
                'rupiah' => $this->jumlah_rupiah,
                'tampilan' => $tanda.Uang::dari(abs($this->jumlah_rupiah))->format(),
            ],
            'saldo_setelah' => $this->saldo_setelah,
            'rujukan' => $this->rujukan,
            'keterangan' => $this->keterangan,
            'waktu' => $this->created_at?->toIso8601String(),
        ];
    }
}
