<?php 
  
declare(strict_types=1); 
  
namespace App\Console\Commands; 
  
use App\Services\PembatalanOtomatis; 

use Illuminate\Console\Command; 
  
final class BatalkanKedaluwarsa extends Command 
{ 
    protected $signature = 'lapak:batalkan-kedaluwarsa'; 
  
    protected $description = 'Membatalkan checkout yang tidak dibayar sampai batas waktu (LP-10)'; 
  
    public function handle(PembatalanOtomatis $pembatalan): int 
    { 
        $jumlah = $pembatalan->jalankan(); 
  
        $this->info("{$jumlah} checkout dibatalkan dan stoknya dikembalikan."); 
  
        return self::SUCCESS; 
    } 
}