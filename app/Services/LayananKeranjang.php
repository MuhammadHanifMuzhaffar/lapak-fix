<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ButirKeranjang;
use App\Models\Keranjang;
use App\Models\Pengguna;

final class LayananKeranjang
{
    public function __construct(private readonly PenyusunBelanja $penyusun) {}

    public function lihat(Pengguna $pengguna): array
    {
        $keranjang = Keranjang::query()->where('pengguna_id', $pengguna->id)->with('butir')->first();

        if ($keranjang === null || $keranjang->butir->isEmpty()) {
            return [[], []];
        }

        $butir = $keranjang->butir
            ->sortBy('id')
            ->map(fn (ButirKeranjang $b) => ['produk_id' => $b->produk_id, 'kuantitas' => $b->kuantitas])
            ->values()
            ->all();

        return $this->penyusun->pilah($butir);
    }
}
