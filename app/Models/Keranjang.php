<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Keranjang extends Model
{
    protected $table = 'keranjang';

    protected $fillable = [];

    /** @return BelongsTo<Pengguna, $this> */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    /** @return HasMany<ButirKeranjang, $this> */
    public function butir(): HasMany
    {
        return $this->hasMany(ButirKeranjang::class, 'keranjang_id');
    }
}
