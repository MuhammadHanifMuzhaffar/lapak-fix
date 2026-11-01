<?php

declare(strict_types=1);

namespace App\Domain\Pesanan;

enum StatusPesanan: string
{
    case MenungguPembayaran = 'menunggu_pembayaran';
    case Dibayar = 'dibayar';
    case Dikemas = 'dikemas';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
}
