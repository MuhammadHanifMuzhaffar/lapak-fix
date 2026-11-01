<?php 
  
declare(strict_types=1); 
  
namespace App\Domain\Pesanan; 
  
use DateTimeInterface; 
use DateTimeZone; 
  
/** 
 * LP-9: nomor pesanan berbentuk LPK-YYYYMMDD-NNNNNN dan unik seumur hidup sistem. 
 * 
 * Bagian angka adalah id baris pesanan. Yang membagikan angka unik kepada dua pembeli yang 
 * datang bersamaan adalah basis data, lewat AUTO_INCREMENT, bukan kode kita. Menghitung 
 * "nomor terakhir tambah satu" di PHP adalah cara tercepat menghasilkan dua pesanan kembar. 
 */ 
final class PenomorPesanan 
{ 
    public const POLA = '/\ALPK-\d{8}-\d{6,}\z/'; 
  
    private const ZONA_BISNIS = 'Asia/Jakarta'; 
  
    public static function untuk(int $idPesanan, DateTimeInterface $dibuatPada): string 
    { 
        // Waktu disimpan dalam UTC. Tanggal pada nomor mengikuti hari kerja Lapak, yaitu WIB. 
        $tanggal = \DateTimeImmutable::createFromInterface($dibuatPada) 
            ->setTimezone(new DateTimeZone(self::ZONA_BISNIS)) 
            ->format('Ymd'); 
  

        return sprintf('LPK-%s-%06d', $tanggal, $idPesanan); 
    } 
}