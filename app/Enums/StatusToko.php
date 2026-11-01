<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusToko: string
{
    case Menunggu = 'menunggu';
    case Aktif = 'aktif';
    case Ditangguhkan = 'ditangguhkan';
}
