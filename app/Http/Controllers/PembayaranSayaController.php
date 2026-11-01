<?php 
  
declare(strict_types=1); 
  
namespace App\Http\Controllers; 
  
use App\Http\Resources\PembayaranResource; 
use App\Models\Pembayaran; 
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Request; 
  
final class PembayaranSayaController extends Controller 
{ 
    /** 
     * GET /pembayaran/{kode}: tagihan milik pembeli yang sedang masuk. Hanya MEMBACA. 
     * Kode tagihan orang lain dijawab 404. 
     */ 
    public function show(Request $request, string $kode): JsonResponse 
    { 
        $pembayaran = Pembayaran::query() 
            ->where('kode', $kode) 
            ->whereHas('checkout', fn (Builder $c) => $c->where('pembeli_id', $request->user()->id)) 
            ->with('checkout.pesanan') 
            ->firstOrFail(); 
  
        return $this->sukses([ 
            'pembayaran' => PembayaranResource::make($pembayaran), 
            'pesanan' => $pembayaran->checkout->pesanan->pluck('nomor')->all(), 
        ]); 
    } 
}