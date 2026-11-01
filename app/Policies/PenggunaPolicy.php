<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pengguna;

final class PenggunaPolicy
{
    public function kelolaPeran(Pengguna $pelaku, Pengguna $sasaran): bool
    {
        return $pelaku->id !== $sasaran->id;
    }
}
