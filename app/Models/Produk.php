<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusProduk;
use App\Enums\StatusToko;
use Database\Factories\ProdukFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    /** @use HasFactory<ProdukFactory> */
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'nama',
        'deskripsi',
        'harga_rupiah',
        'stok',
        'berat_gram',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusProduk::class,
            'harga_rupiah' => 'integer',
            'stok' => 'integer',
            'berat_gram' => 'integer',
        ];
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function dimilikiOleh(Pengguna $pengguna): bool
    {
        return $this->toko->pemilik_id === $pengguna->id;
    }

    public function scopeTampil(Builder $query): void
    {
        $query->where('status', StatusProduk::Aktif)
            ->where('stok', '>', 0)
            ->whereHas('toko', function (Builder $q) {
                $q->where('status', StatusToko::Aktif);
            });
    }
}
