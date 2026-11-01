<?php 
  
declare(strict_types=1); 
  
namespace App\Exceptions; 
  
use App\Domain\Pesanan\StatusPesanan; 
  
final class TransisiPesananTidakSah extends GalatDomain 
{ 
    public function __construct(StatusPesanan $dari, StatusPesanan $ke) 
    { 
        parent::__construct( 
            'LP-10', 
            "Pesanan berstatus {$dari->value} tidak dapat dipindahkan ke {$ke->value}.", 
            409, 
        ); 
    } 
}