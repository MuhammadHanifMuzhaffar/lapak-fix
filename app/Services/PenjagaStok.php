<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PenyesuaianStokDitolak;
use App\Exceptions\ProdukTidakDapatDibeli;
use App\Models\Produk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final class PenjagaStok
{
    public const MAKSIMUM = 100_000;

    /**
     * @param  list<int>  $idProduk
     * @return Collection<int, Produk>
     */
    public function ambil(array $idProduk): Collection
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('PenjagaStok::ambil() harus di dalam DB::transaction().');
        }

        return Produk::query()
            ->with('toko')
            ->whereIn('id', $idProduk)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    public function kurangi(Produk $produk, int $kuantitas): void
    {
        if ($kuantitas < 1) {
            throw new InvalidArgumentException('Kuantitas minimal 1.');
        }

        if ($produk->stok < $kuantitas) {
            throw ProdukTidakDapatDibeli::stokKurang(
                $produk->id, $produk->nama, $kuantitas, $produk->stok,
            );
        }

        $produk->stok -= $kuantitas;
        $produk->save();
    }

    public function kembalikan(int $idProduk, int $kuantitas): void
    {
        if ($kuantitas < 1) {
            throw new InvalidArgumentException('Kuantitas minimal 1.');
        }

        Produk::query()->whereKey($idProduk)->increment('stok', $kuantitas);
    }

    public function sesuaikan(int $idProduk, int $selisih): Produk
    {
        if ($selisih === 0) {
            throw new InvalidArgumentException('Selisih tidak boleh nol.');
        }

        return DB::transaction(function () use ($idProduk, $selisih): Produk {
            $produk = Produk::query()->lockForUpdate()->findOrFail($idProduk);
            $baru = $produk->stok + $selisih;

            if ($baru < 0) {
                throw PenyesuaianStokDitolak::akanNegatif(
                    $produk->id, $produk->nama, $produk->stok, $selisih,
                );
            }

            if ($baru > self::MAKSIMUM) {
                throw PenyesuaianStokDitolak::melewatiBatas(
                    $produk->id, $produk->nama, $produk->stok, self::MAKSIMUM,
                );
            }

            $produk->stok = $baru;
            $produk->save();

            return $produk;
        });
    }
}
