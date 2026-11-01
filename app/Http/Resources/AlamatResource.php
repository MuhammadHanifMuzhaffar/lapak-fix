<?php 
  
declare(strict_types=1); 
  
namespace App\Http\Resources; 
  
use App\Models\Alamat; 
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\JsonResource; 
  
/** 
 * @mixin Alamat 
 */ 
final class AlamatResource extends JsonResource 
{ 
    public function toArray(Request $request): array 
    { 
        return [ 
            'id' => $this->id, 
            'label' => $this->label, 
            'utama' => $this->utama, 
            ...$this->potret(), 
        ]; 
    } 
}