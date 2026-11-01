<?php

declare(strict_types=1);

namespace App\Exceptions;

final class KredensialSalah extends GalatDomain
{
    public static function saatMasuk(): self
    {
        return new self('KREDENSIAL_SALAH', 'Surel atau kata sandi salah.', 401);
    }

    public static function saatKonfirmasi(): self
    {
        return new self('KREDENSIAL_SALAH', 'Kata sandi salah.', 422);
    }
}
