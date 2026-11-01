<?php

declare(strict_types=1);

namespace App\Exceptions;

final class PenyesuaianStokDitolak extends GalatDomain
{
    public static function akanNegatif(int $idProduk, string $nama, int $stok, int $selisih): self
    {
        $pesan = "Stok {$nama} tinggal {$stok}, tidak dapat dikurangi ".abs($selisih).'.';

        return new self('LP-4', $pesan, 409, [
            'produk_id' => $idProduk,
            'stok_sekarang' => $stok,
            'selisih' => $selisih,
        ]);
    }

    public static function melewatiBatas(int $idProduk, string $nama, int $stok, int $batas): self
    {
        return new self('LP-4', "Stok {$nama} tidak boleh melebihi {$batas}.", 409, [
            'produk_id' => $idProduk,
            'stok_sekarang' => $stok,
            'batas' => $batas,
        ]);
    }
}
