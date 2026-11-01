<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\KategoriFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Kategori extends Model
{
    /** @use HasFactory<KategoriFactory> */
    use HasFactory;

    protected $table = 'kategori';

    protected $fillable = ['nama', 'slug'];

    /** @return HasMany<Produk, $this> */
    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}
