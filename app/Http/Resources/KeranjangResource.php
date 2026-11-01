<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Belanja\ButirBermasalah;
use App\Domain\Belanja\PenghitungBelanja;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class KeranjangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [$rincian, $bermasalah] = $this->resource;
        $total = PenghitungBelanja::totalBayar($rincian);

        return [
            'pesanan' => RincianTokoResource::collection($rincian),
            'total_bayar' => ['rupiah' => $total, 'tampilan' => $total->format()],
            'tidak_tersedia' => array_map(fn (ButirBermasalah $butir) => [
                'produk_id' => $butir->produkId,
                'kuantitas' => $butir->kuantitas,
                'alasan' => $butir->alasan->value,
                'stok_tersedia' => $butir->stokTersedia,
            ], $bermasalah),
        ];
    }
}
