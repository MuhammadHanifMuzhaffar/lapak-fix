<?php 
  
declare(strict_types=1); 
  
namespace App\Http\Controllers; 
  
use App\Http\Requests\CheckoutRequest; 
use App\Http\Resources\CheckoutResource; 
use App\Models\Alamat; 
use App\Models\Sesi; 
use App\Services\LayananCheckout; 
use Illuminate\Http\JsonResponse; 
  
final class CheckoutController extends Controller 
{ 
    /** 
     * POST /checkout. Rutenya dipasangi idempoten: seluruh isi metode ini berjalan di dalam 
     * transaksi milik middleware itu, bersama catatan Idempotency-Key-nya (LP-11). 
     */ 
    public function __invoke(CheckoutRequest $request, LayananCheckout $layanan): JsonResponse 
    { 
        $pembeli = $request->user(); 
        $alamat = Alamat::query()->milik($pembeli)->findOrFail($request->integer('alamat_id')); 
  
        $checkout = $layanan->proses($pembeli, $alamat, Sesi::dariPermintaan($request)); 
  
        return $this->sukses( 
            CheckoutResource::make($checkout->load(['pesanan.toko', 'pesanan.butir'])), 
            201, 
        ); 
    } 
}