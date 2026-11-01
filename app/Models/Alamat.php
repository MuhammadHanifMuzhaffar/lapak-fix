<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AlamatFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Alamat extends Model
{
    /** @use HasFactory<AlamatFactory> */
    use HasFactory;

    protected $table = 'alamat';

    // pengguna_id dan utama TIDAK ada di sini: pemilik ditentukan sesi,
    // dan alamat utama hanya diatur BukuAlamat.
    protected $fillable = ['label', 'nama_penerima', 'telepon', 'jalan', 'kota', 'kode_pos'];

    protected function casts(): array
    {
        return ['utama' => 'boolean'];
    }

    /** @return BelongsTo<Pengguna, $this> */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    /**
     * Seluruh pintu alamat berangkat dari scope ini. Nomor alamat milik orang lain dijawab 404.
     *
     * @param  Builder<Alamat>  $kueri
     */
    public function scopeMilik(Builder $kueri, Pengguna $pengguna): void
    {
        $kueri->where('pengguna_id', $pengguna->id);
    }

    /**
     * Salinan alamat untuk DISIMPAN pada pesanan. Pesanan tidak menunjuk ke baris alamat:
     * pembeli boleh mengubah atau menghapus alamatnya besok, sedangkan paket hari ini tetap
     * harus sampai ke alamat yang tertulis saat ia membayar.
     *
     * @return array<string, string> nama_penerima, telepon, jalan, kota, kode_pos
     */
    public function potret(): array
    {
        return $this->only(['nama_penerima', 'telepon', 'jalan', 'kota', 'kode_pos']);
    }
}
