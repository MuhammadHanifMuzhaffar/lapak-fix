<?php

declare(strict_types=1);

namespace App\Domain\Belanja;

enum AlasanBermasalah: string
{
    case TidakTersedia = 'tidak_tersedia';
    case StokKurang = 'stok_kurang';
    case ProdukSendiri = 'produk_sendiri';
}
