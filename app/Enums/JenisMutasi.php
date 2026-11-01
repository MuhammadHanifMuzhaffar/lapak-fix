<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisMutasi: string
{
    case DanaMasuk = 'dana_masuk';       // eksternal -> escrow
    case LepasDana = 'lepas_dana';       // escrow -> penjual
    case Komisi = 'komisi';              // escrow -> pendapatan
    case Pengembalian = 'pengembalian'; // escrow -> eksternal
    case Pencairan = 'pencairan';        // penjual -> eksternal
}
