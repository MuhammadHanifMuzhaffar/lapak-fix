<?php 
  
declare(strict_types=1); 

namespace App\Exceptions; 
  
final class GalatIdempotensi extends GalatDomain 
{ 
    public static function kunciTidakSah(): self 
    { 
        return new self( 
            'LP-11', 
            'Permintaan ini wajib membawa header Idempotency-Key: 16 sampai 64 huruf, angka, ' 
            .'garis bawah, atau tanda hubung.', 
            422, 
        ); 
    } 
  
    public static function dipakaiPermintaanLain(): self 
    { 
        return new self( 
            'LP-11', 
            'Idempotency-Key ini sudah dipakai untuk permintaan yang berbeda. Buat kunci baru.', 
            422, 
        ); 
    } 
  
    public static function sedangDiproses(): self 
    { 
        return new self( 
            'LP-11', 
            'Permintaan dengan Idempotency-Key ini sedang diproses. Ulangi sebentar lagi.', 
            409, 
        ); 
    } 
}