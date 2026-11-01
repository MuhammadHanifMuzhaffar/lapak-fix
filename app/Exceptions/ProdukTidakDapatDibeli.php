<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ProdukTidakDapatDibeli extends GalatDomain
{
    /**
     * @param  list<int>  $idProduk
     */
    public static function tidakTampil(array $idProduk): self
    {
        return new self('LP-4', 'Sebagian produk tidak tersedia untuk dibeli.', 422, [
            'produk_id' => $idProduk,
        ]);
    }

    public static function stokKurang(int $idProduk, string $nama, int $diminta, int $tersedia): self
    {
        return new self('LP-4', "Stok {$nama} tidak mencukupi.", 409, [
            'produk_id' => $idProduk,
            'diminta' => $diminta,
            'tersedia' => $tersedia,
        ]);
    }
}
