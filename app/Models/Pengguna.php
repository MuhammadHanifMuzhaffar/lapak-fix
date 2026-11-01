<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusAkun;
use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

final class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory;

    protected $table = 'pengguna';

    // status TIDAK ada di sini: klien tidak boleh menentukan status akunnya sendiri.
    protected $fillable = ['nama', 'surel', 'telepon', 'kata_sandi'];

    protected $hidden = ['kata_sandi'];

    protected $authPasswordName = 'kata_sandi';

    protected function casts(): array
    {
        return [
            'kata_sandi' => 'hashed',
            'status' => StatusAkun::class,
        ];
    }
}
