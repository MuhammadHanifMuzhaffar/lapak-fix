<?php 
  
declare(strict_types=1); 
  
namespace App\Models; 
  
use App\Domain\Pesanan\PenomorPesanan; 
use App\Domain\Pesanan\StatusPesanan; 
use Database\Factories\PesananFactory; 
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany; 
  
final class Pesanan extends Model 
{ 
    /** @use HasFactory<PesananFactory> */ 
    use HasFactory; 
  
    protected $table = 'pesanan'; 
  
    // Status hanya berubah lewat MesinStatusPesanan. Tidak ada kolom yang diisi dari masukan klien. 
    protected $fillable = []; 
  
    protected function casts(): array 
    { 
        return [ 
            'status' => StatusPesanan::class, 
            'subtotal_rupiah' => 'integer', 
            'ongkir_rupiah' => 'integer', 
            'total_rupiah' => 'integer', 
            'alamat_kirim' => 'array', 
            'dibayar_pada' => 'datetime', 
            'dibatalkan_pada' => 'datetime', 
        ]; 
    } 
  
    protected static function booted(): void 
    { 
        // LP-9: nomor diturunkan dari id, maka baru dapat diisi setelah baris tersimpan. 
        self::created(function (Pesanan $pesanan): void { 
            $pesanan->forceFill([ 
                'nomor' => PenomorPesanan::untuk($pesanan->id, $pesanan->created_at), 
            ])->saveQuietly(); 
        }); 
    } 
  
    /** @return BelongsTo<Checkout, $this> */ 
    public function checkout(): BelongsTo 
    { 
        return $this->belongsTo(Checkout::class, 'checkout_id'); 
    } 
  
    /** @return BelongsTo<Toko, $this> */ 
    public function toko(): BelongsTo 
    { 
        return $this->belongsTo(Toko::class, 'toko_id'); 
    } 
  
    /** @return HasMany<ButirPesanan, $this> */ 
    public function butir(): HasMany 
    { 

        return $this->hasMany(ButirPesanan::class, 'pesanan_id'); 
    } 
  
    /** 
     * Pintu pembeli selalu berangkat dari scope ini. Nomor pesanan orang lain dijawab 404. 
     * 
     * @param  Builder<Pesanan>  $kueri 
     */ 
    public function scopeMilikPembeli(Builder $kueri, Pengguna $pembeli): void 
    { 
        $kueri->where('pesanan.pembeli_id', $pembeli->id); 
    } 
}