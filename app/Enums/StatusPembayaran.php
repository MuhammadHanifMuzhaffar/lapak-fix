<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusPembayaran: string
{
    case Menunggu = 'menunggu';

    case Lunas = 'lunas';

    /** Batas bayar terlewat. Pesanannya dibatalkan dan stoknya dikembalikan (LP-10). */
    case Kedaluwarsa = 'kedaluwarsa';
}
