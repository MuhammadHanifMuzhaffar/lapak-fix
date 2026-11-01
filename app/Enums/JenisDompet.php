<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisDompet: string
{
    /** Dunia di luar marketplace: gerbang pembayaran dan bank. Satu-satunya yang boleh negatif. */
    case Eksternal = 'eksternal';

    /** Dana pembeli yang ditahan sampai pesanan diterima (LP-12). */
    case Escrow = 'escrow';

    /** Komisi yang sudah menjadi hak marketplace (LP-8). */
    case Pendapatan = 'pendapatan';

    /** Saldo milik seorang penjual; satu dompet per penjual. */
    case Penjual = 'penjual';

    public function bolehNegatif(): bool
    {
        return $this === self::Eksternal;
    }
}
