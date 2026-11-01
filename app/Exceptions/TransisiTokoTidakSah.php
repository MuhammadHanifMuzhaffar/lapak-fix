<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\StatusToko;

final class TransisiTokoTidakSah extends GalatDomain
{
    public function __construct(StatusToko $tujuan)
    {
        parent::__construct(
            'LP-3',
            "Toko tidak dapat dipindahkan ke status {$tujuan->value} dari statusnya saat ini.",
            409,
        );
    }
}
