<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Belanja\BarisBelanja;
use App\Domain\Belanja\PenghitungBelanja;
use App\Domain\Belanja\RincianToko;
use App\Domain\Uang;
use App\Exceptions\ProdukTidakDapatDibeli;
use App\Models\Produk;

final class PenyusunBelanja
{
    /**
     * @param  list<array{produk_id: int, kuantitas: int}>  $butir
     * @return list<RincianToko>
     */
    public function susun(array $butir): array
    {
        $diminta = collect($butir)->pluck('kuantitas', 'produk_id');

        $produk = Produk::query()
            ->tampil()
            ->with('toko')
            ->whereIn('id', $diminta->keys())
            ->get()
            ->keyBy('id');

        $hilang = $diminta->keys()->diff($produk->keys())->values()->all();
        if ($hilang !== []) {
            throw ProdukTidakDapatDibeli::tidakTampil($hilang);
        }

        $baris = [];
        $toko = [];
        foreach ($diminta as $produkId => $kuantitas) {
            $barang = $produk[$produkId];

            if ($barang->stok < $kuantitas) {
                throw ProdukTidakDapatDibeli::stokKurang(
                    $barang->id,
                    $barang->nama,
                    $kuantitas,
                    $barang->stok,
                );
            }

            $baris[] = new BarisBelanja(
                produkId: $barang->id,
                namaProduk: $barang->nama,
                tokoId: $barang->toko_id,
                hargaSatuan: Uang::dari($barang->harga_rupiah),
                kuantitas: $kuantitas,
            );
            $toko[$barang->toko_id] = [
                'nama' => $barang->toko->nama,
                'ongkir' => Uang::dari($barang->toko->ongkir_rupiah),
            ];
        }

        return (new PenghitungBelanja(config('lapak.komisi_persen')))->hitung($baris, $toko);
    }
}
