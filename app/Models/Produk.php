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

final class Produk extends Model
{
    /** @use HasFactory<ProdukFactory> */
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'nama',
        'slug',
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

    /** @return BelongsTo<Toko, $this> */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /** @return BelongsTo<Kategori, $this> */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * LP-4: produk tampil hanya bila berstatus aktif DAN tokonya aktif.
     * Seluruh pintu publik wajib lewat scope ini; jangan menulis ulang syaratnya di controller.
     *
     * @param  Builder<Produk>  $kueri
     */
    public function scopeTampil(Builder $kueri): void
    {
        $kueri->where('produk.status', StatusProduk::Aktif)
            ->whereHas('toko', fn (Builder $toko) => $toko->where('status', StatusToko::Aktif));
    }
}
