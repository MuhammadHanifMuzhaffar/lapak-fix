<?php

declare(strict_types=1);

namespace App\Exceptions;

final class SesiTidakSegar extends GalatDomain
{
    public function __construct()
    {
        parent::__construct('LP-15', 'Tindakan ini menuntut konfirmasi ulang kata sandi.', 403, [
            'konfirmasi' => 'POST /api/v1/sesi/konfirmasi',
        ]);
    }
}
