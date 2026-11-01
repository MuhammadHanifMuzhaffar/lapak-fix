<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusProduk: string
{
    case Draf = 'draf';
    case Aktif = 'aktif';
    case Arsip = 'arsip';
}
