<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ButirKeranjang extends Model
{
    protected $table = 'butir_keranjang';

    // Harga TIDAK disimpan di keranjang. Harga selalu dibaca dari tabel produk saat dihitung (LP-6).
    protected $fillable = ['produk_id', 'kuantitas'];

    protected function casts(): array
    {
        return ['kuantitas' => 'integer'];
    }

    /** @return BelongsTo<Produk, $this> */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
