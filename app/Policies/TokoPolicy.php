<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\Toko;

final class TokoPolicy
{
    public function moderasi(Pengguna $admin, Toko $toko): bool
    {
        return $admin->id !== $toko->pemilik_id;
    }
}
