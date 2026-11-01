<?php

declare(strict_types=1);

namespace App\Domain\Pesanan;

enum PelakuPesanan: string
{
    case Pembeli = 'pembeli';
    case Penjual = 'penjual';
    case Sistem = 'sistem';
}
