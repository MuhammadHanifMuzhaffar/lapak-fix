<?php 
  
declare(strict_types=1); 
  
namespace App\Http\Resources; 
  
use App\Domain\Uang; 
use App\Models\Pembayaran; 
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\JsonResource; 
  
/** 
 * @mixin Pembayaran 
 */ 

final class PembayaranResource extends JsonResource 
{ 
    /** id_peristiwa dan nomor urut internal tidak dikeluarkan. */ 
    public function toArray(Request $request): array 
    { 
        $jumlah = Uang::dari($this->jumlah_rupiah); 
  
        return [ 
            'kode' => $this->kode, 
            'status' => $this->status->value, 
            'jumlah' => ['rupiah' => $jumlah, 'tampilan' => $jumlah->format()], 
            'batas_bayar_pada' => $this->batas_bayar_pada->toIso8601String(), 
            'lunas_pada' => $this->lunas_pada?->toIso8601String(), 
        ]; 
    } 
}