<?php 
  
declare(strict_types=1); 
  
namespace App\Models; 
  
use Database\Factories\CheckoutFactory; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany; 
  
final class Checkout extends Model 
{ 
    /** @use HasFactory<CheckoutFactory> */ 
    use HasFactory; 
  
    protected $table = 'checkout'; 
  
    // Hanya dibuat LayananCheckout, tidak pernah dari masukan klien. 
    protected $fillable = []; 
  
    protected function casts(): array 
    { 
        return ['total_rupiah' => 'integer']; 
    } 
  
    /** @return BelongsTo<Pengguna, $this> */ 

    public function pembeli(): BelongsTo 
    { 
        return $this->belongsTo(Pengguna::class, 'pembeli_id'); 
    } 
  
    /** @return HasMany<Pesanan, $this> */ 
    public function pesanan(): HasMany 
    { 
        return $this->hasMany(Pesanan::class, 'checkout_id'); 
    } 
}