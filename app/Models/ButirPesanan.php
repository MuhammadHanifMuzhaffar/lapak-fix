<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ButirPesanan extends Model
{
    use HasFactory;

    protected $table = 'butir_pesanan';

    protected $fillable = [
        'pesanan_id',
        'produk_id',
        'nama_produk',
        'harga_rupiah',
        'kuantitas',
        'subtotal_rupiah',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
