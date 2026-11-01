<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class KunciIdempotensi extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'kunci_idempotensi';

    // Hanya ditulis middleware Idempoten, tidak pernah dari masukan klien.
    protected $fillable = [];

    protected function casts(): array
    {
        return ['kode_status' => 'integer'];
    }
}
