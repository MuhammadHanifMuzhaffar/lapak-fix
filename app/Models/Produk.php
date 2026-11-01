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

    /**
     * @param  Builder<Produk>  $kueri
     * @param  array<string, mixed>  $saringan  hasil validasi KatalogRequest
     */
    public function scopeSaring(Builder $kueri, array $saringan): void
    {
        $kueri
            ->when($saringan['kategori'] ?? null, fn (Builder $q, string $slug) => $q
                ->whereHas('kategori', fn (Builder $k) => $k->where('slug', $slug)))
            ->when($saringan['toko'] ?? null, fn (Builder $q, string $slug) => $q
                ->whereHas('toko', fn (Builder $t) => $t->where('slug', $slug)))
            ->when($saringan['cari'] ?? null, fn (Builder $q, string $kata) => $q->whereRaw(
                "produk.nama like ? escape '!'",
                ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $kata).'%'],
            ))
            ->when(isset($saringan['harga_min']), fn (Builder $q) => $q
                ->where('harga_rupiah', '>=', $saringan['harga_min']))
            ->when(isset($saringan['harga_maks']), fn (Builder $q) => $q
                ->where('harga_rupiah', '<=', $saringan['harga_maks']))
            ->when($saringan['tersedia'] ?? false, fn (Builder $q) => $q->where('stok', '>', 0));
    }

    /**
     * Pengurutan memakai daftar putih. Nama kolom tidak pernah diambil dari masukan klien.
     *
     * @param  Builder<Produk>  $kueri
     */
    public function scopeUrut(Builder $kueri, ?string $urut): void
    {
        match ($urut) {
            'harga_naik' => $kueri->orderBy('harga_rupiah')->orderBy('id'),
            'harga_turun' => $kueri->orderByDesc('harga_rupiah')->orderBy('id'),
            default => $kueri->orderByDesc('id'),
        };
    }
}
