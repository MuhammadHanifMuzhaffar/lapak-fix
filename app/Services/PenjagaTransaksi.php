<?php 
  
declare(strict_types=1); 
  
namespace App\Services; 
  

use App\Domain\Uang; 
use App\Exceptions\SesiTidakSegar; 
use App\Models\Sesi; 
  
/** 
 * LP-15 untuk belanja: transaksi bernilai besar menuntut sesi segar. 
 * 
 * Tidak dapat dipasang sebagai middleware, karena nilainya baru diketahui setelah server 
 * menghitung total dari basis data. Checkout memanggil kelas ini di dalam transaksinya, 
 * setelah total dihitung dan sebelum apa pun di-commit. 
 */ 
final class PenjagaTransaksi 
{ 
    public function pastikanLayak(Sesi $sesi, Uang $total): void 
    { 
        $batas = Uang::dari(config('lapak.transaksi.batas_segar_rupiah')); 
  
        if ($total->lebihDari($batas) && ! $sesi->segar()) { 
            throw new SesiTidakSegar; 
        } 
    } 
}