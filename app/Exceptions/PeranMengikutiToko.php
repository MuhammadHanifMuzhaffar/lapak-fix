<?php

declare(strict_types=1);

namespace App\Exceptions;

final class PeranMengikutiToko extends GalatDomain
{
    public function __construct()
    {
        parent::__construct(
            'LP-3',
            'Peran penjual mengikuti toko: diberikan saat toko disetujui, tidak lewat pintu ini.',
            422,
        );
    }
}
