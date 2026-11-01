<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Belanja\AlasanBermasalah;
use App\Domain\Belanja\BarisBelanja;
use App\Domain\Belanja\ButirBermasalah;
use App\Domain\Belanja\PenghitungBelanja;
use App\Domain\Uang;
use App\Exceptions\ProdukTidakDapatDibeli;
use App\Models\Produk;

final class PenyusunBelanja
{
    public function pilah(array $butir): array
    {
        $diminta = collect($butir)->pluck('kuantitas', 'produk_id');

        $produk = Produk::query()
            ->tampil()
            ->with('toko')
            ->whereIn('id', $diminta->keys())
            ->get()
            ->keyBy('id');

        $baris = [];
        $toko = [];
        $bermasalah = [];

        foreach ($diminta as $produkId => $kuantitas) {
            $barang = $produk[$produkId] ?? null;

            if ($barang === null) {
                $bermasalah[] = new ButirBermasalah(
                    $produkId, $kuantitas, AlasanBermasalah::TidakTersedia,
                );

                continue;
            }

            if ($barang->stok < $kuantitas) {
                $bermasalah[] = new ButirBermasalah(
                    $produkId, $kuantitas, AlasanBermasalah::StokKurang, $barang->nama, $barang->stok,
                );

                continue;
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

        $rincian = $baris === []
            ? []
            : (new PenghitungBelanja(config('lapak.komisi_persen')))->hitung($baris, $toko);

        return [$rincian, $bermasalah];
    }

    public function susun(array $butir): array
    {
        [$rincian, $bermasalah] = $this->pilah($butir);

        $hilang = [];
        foreach ($bermasalah as $masalah) {
            if ($masalah->alasan === AlasanBermasalah::TidakTersedia) {
                $hilang[] = $masalah->produkId;
            }
        }
        if ($hilang !== []) {
            throw ProdukTidakDapatDibeli::tidakTampil($hilang);
        }

        foreach ($bermasalah as $masalah) {
            throw ProdukTidakDapatDibeli::stokKurang(
                $masalah->produkId,
                (string) $masalah->namaProduk,
                $masalah->kuantitas,
                (int) $masalah->stokTersedia,
            );
        }

        return $rincian;
    }
}
