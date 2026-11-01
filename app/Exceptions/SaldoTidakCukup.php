<?php

declare(strict_types=1);

namespace App\Exceptions;

final class SaldoTidakCukup extends GalatDomain
{
    public function __construct(string $kodeDompet, int $saldo, int $diminta)
    {
        parent::__construct('LP-13', 'Saldo tidak mencukupi untuk pemindahan ini.', 409, [
            'dompet' => $kodeDompet,
            'saldo' => $saldo,
            'diminta' => $diminta,
        ]);
    }
}
