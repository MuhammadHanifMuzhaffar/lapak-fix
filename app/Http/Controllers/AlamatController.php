<?php 
  
declare(strict_types=1); 
  

namespace App\Http\Controllers; 
  
use App\Http\Resources\AlamatResource; 
use App\Models\Alamat; 
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Request; 
  
final class AlamatController extends Controller 
{ 
    /** GET /alamat: alamat milik pengguna sesi, alamat utama lebih dulu. */ 
    public function index(Request $request): JsonResponse 
    { 
        $alamat = Alamat::query() 
            ->milik($request->user()) 
            ->orderByDesc('utama') 
            ->orderBy('id') 
            ->get(); 
  
        return $this->sukses(AlamatResource::collection($alamat)); 
    } 
}