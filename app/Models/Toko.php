<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusToko;
use Database\Factories\TokoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    /** @use HasFactory<TokoFactory> */
    use HasFactory;

    protected $table = 'toko';

    protected $fillable = [
        'nama',
        'kota',
        'deskripsi',
        'ongkir_rupiah',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusToko::class,
            'ongkir_rupiah' => 'integer',
            'dimoderasi_pada' => 'datetime',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pemilik_id');
    }

    public function pemoderasi(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dimoderasi_oleh');
    }

    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'toko_id');
    }

    public function scopeMilik(Builder $kueri, Pengguna $pengguna): void
    {
        $kueri->where('pemilik_id', $pengguna->id);
    }
}
