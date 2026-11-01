<?php 
  
declare(strict_types=1); 
  
namespace App\Services; 
  
use App\Exceptions\ProdukTidakDapatDibeli; 
use App\Models\Produk; 
use Illuminate\Support\Collection; 
use InvalidArgumentException; 
  
/** 
 * Satu-satunya pintu perubahan stok oleh transaksi (LP-4), seperti BukuBesar bagi saldo. 
 * Checkout tidak pernah menulis kolom stok sendiri: ia mengambil produk lewat ambil(), 
 * lalu mengurangi lewat kurangi(). 
 */ 
final class PenjagaStok 
{ 
    /** 
     * Produk yang akan dibeli, beserta tokonya, berkunci nomor produk. 
     * 
     * @param  list<int>  $idProduk 
     * @return Collection<int, Produk> 
     */ 
    public function ambil(array $idProduk): Collection 
    { 
        // BELUM AMAN bila dua pembeli datang bersamaan: baris dibaca tanpa dikunci. 
        // Versi kontrak ini benar untuk satu pembeli pada satu waktu. Dikunci pada jendela B. 
        return Produk::query() 
            ->with('toko') 
            ->whereIn('id', $idProduk) 
            ->get() 
            ->keyBy('id'); 

    } 
  
    /** 
     * @param  Produk  $produk  hasil ambil(), bukan hasil kueri lain 
     * 
     * @throws ProdukTidakDapatDibeli 409 LP-4 bila stok tidak mencukupi 
     */ 
    public function kurangi(Produk $produk, int $kuantitas): void 
    { 
        if ($kuantitas < 1) { 
            throw new InvalidArgumentException('Kuantitas minimal 1.'); 
        } 
  
        if ($produk->stok < $kuantitas) { 
            throw ProdukTidakDapatDibeli::stokKurang( 
                $produk->id, $produk->nama, $kuantitas, $produk->stok, 
            ); 
        } 
  
        $produk->stok -= $kuantitas; 
        $produk->save(); 
    } 
  
    /** Dipakai pembatalan. Menambah tidak memerlukan keputusan, maka cukup satu UPDATE. */ 
    public function kembalikan(int $idProduk, int $kuantitas): void 
    { 
        if ($kuantitas < 1) { 
            throw new InvalidArgumentException('Kuantitas minimal 1.'); 
        } 
  
        Produk::query()->whereKey($idProduk)->increment('stok', $kuantitas); 
    } 
}