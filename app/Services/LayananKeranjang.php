<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GalatDomain;
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

        return $this->penyusun->pilah($butir, $pengguna);
    }

    public function aturButir(Pengguna $pengguna, int $produkId, int $kuantitas): void
    {
        $this->penyusun->susun([['produk_id' => $produkId, 'kuantitas' => $kuantitas]], $pengguna);

        $keranjang = $this->milik($pengguna);
        $sudahAda = $keranjang->butir()->where('produk_id', $produkId)->exists();

        if (! $sudahAda && $keranjang->butir()->count() >= config('lapak.belanja.butir_maksimum')) {
            throw new GalatDomain('KERANJANG_PENUH', 'Keranjang sudah penuh.', 422);
        }

        $keranjang->butir()->updateOrCreate(['produk_id' => $produkId], ['kuantitas' => $kuantitas]);
    }

    public function hapusButir(Pengguna $pengguna, int $produkId): void
    {
        $this->milik($pengguna)->butir()->where('produk_id', $produkId)->delete();
    }

    public function kosongkan(Pengguna $pengguna): void
    {
        $this->milik($pengguna)->butir()->delete();
    }

    private function milik(Pengguna $pengguna): Keranjang
    {
        $keranjang = Keranjang::query()->where('pengguna_id', $pengguna->id)->first();

        if ($keranjang === null) {
            $keranjang = new Keranjang;
            $keranjang->pengguna_id = $pengguna->id;
            $keranjang->save();
        }

        return $keranjang;
    }
}
