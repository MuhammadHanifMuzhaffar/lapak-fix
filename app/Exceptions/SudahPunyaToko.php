<?php

declare(strict_types=1);

namespace App\Exceptions;

final class SudahPunyaToko extends GalatDomain
{
    public function __construct()
    {
        parent::__construct('LP-3', 'Anda sudah memiliki toko. Satu pengguna, satu toko.', 409);
    }
}
