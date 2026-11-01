<?php 
  
declare(strict_types=1); 
  
namespace App\Models; 
  
use App\Enums\StatusPembayaran; 
use Database\Factories\PembayaranFactory; 
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
  
final class Pembayaran extends Model 
{ 
    /** @use HasFactory<PembayaranFactory> */ 
    use HasFactory; 
  
    protected $table = 'pembayaran'; 
  
    // Hanya dibuat dan diubah LayananPembayaran. 
    protected $fillable = []; 
  
    protected function casts(): array 
    { 
        return [ 
            'status' => StatusPembayaran::class, 
            'jumlah_rupiah' => 'integer', 
            'batas_bayar_pada' => 'datetime', 
            'lunas_pada' => 'datetime', 
        ]; 
    } 
  
    /** @return BelongsTo<Checkout, $this> */ 
    public function checkout(): BelongsTo 
    { 
        return $this->belongsTo(Checkout::class, 'checkout_id'); 
    } 
  
    /** 
     * Tagihan yang masih menunggu padahal batas bayarnya sudah lewat (LP-10). 
     * 
     * @param  Builder<Pembayaran>  $kueri 
     */ 
    public function scopeLewatBatas(Builder $kueri): void 
    { 
        $kueri->where('status', StatusPembayaran::Menunggu)->where('batas_bayar_pada', '<=', now()); 
    } 
}