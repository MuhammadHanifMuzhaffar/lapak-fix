<?php 
  
declare(strict_types=1); 
  
namespace App\Services; 
  
use App\Models\Checkout; 
use Closure; 
use Illuminate\Support\Facades\DB; 
  
/** 
 * Satu gembok untuk satu transaksi belanja. 
 * 
 * Siapa pun yang mengubah pesanan atau pembayaran sebuah checkout menjalankan pekerjaannya di 
 * dalam dalam(). Baris checkout dikunci lebih dulu, sehingga konfirmasi pembayaran, pembatalan 
 * otomatis, dan kelak tindakan pembeli maupun penjual atas checkout yang sama ANTRE, tidak 
 * berjalan bersamaan. Karena semuanya mengambil gembok yang sama lebih dulu, tidak ada dua 
 * pekerjaan yang saling menunggu selamanya. 
 */ 

final class GembokCheckout 
{ 
    /** 
     * @template T 
     * 
     * @param  Closure(Checkout): T  $kerja 
     * @return T 
     */ 
    public function dalam(int $idCheckout, Closure $kerja): mixed 
    { 
        return DB::transaction(function () use ($idCheckout, $kerja) { 
            $checkout = Checkout::query()->lockForUpdate()->findOrFail($idCheckout); 
  
            return $kerja($checkout); 
        }); 
    } 
}