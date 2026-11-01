<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusAkun: string
{
    case Aktif = 'aktif';
    case Ditangguhkan = 'ditangguhkan';
}
