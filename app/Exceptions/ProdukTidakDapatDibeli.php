<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ProdukTidakDapatDibeli extends GalatDomain
{
    public static function tidakTampil(array $idProduk): self
    {
        return new self('LP-4', 'Satu atau lebih produk tidak lagi tersedia.', 422, [
            'produk_id' => $idProduk,
        ]);
    }

    public static function milikSendiri(int $idProduk, string $nama): self
    {
        return new self('LP-5', "{$nama} adalah produk toko Anda sendiri.", 422, [
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
