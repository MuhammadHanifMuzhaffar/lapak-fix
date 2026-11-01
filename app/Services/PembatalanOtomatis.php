<?php 
  
declare(strict_types=1); 
  
namespace App\Services; 
  
use App\Domain\Pesanan\PelakuPesanan; 
use App\Domain\Pesanan\StatusPesanan; 
use App\Models\Checkout; 
use App\Models\Pembayaran; 
  
/** 
 * LP-10: checkout yang tidak dibayar sampai batas waktunya dibatalkan dan stoknya kembali. 
 * 
 * Tanpa kelas ini, siapa pun dapat mengosongkan stok seluruh toko tanpa membayar sepeser pun: 
 * cukup checkout, lalu tidak pernah melunasinya. 
 */ 
final class PembatalanOtomatis 
{ 
    public const ALASAN = 'Tidak dibayar sampai batas waktu'; 
  
    public function __construct( 
        private readonly GembokCheckout $gembok, 
        private readonly LayananPembayaran $pembayaran, 
        private readonly MesinStatusPesanan $mesin, 
        private readonly PenjagaStok $stok, 
    ) {} 
  
    /** @return int jumlah checkout yang dibatalkan */ 
    public function jalankan(): int 
    { 
        $dibatalkan = 0; 
  
        // Satu transaksi per checkout: kegagalan pada satu checkout tidak menahan yang lain. 
        foreach (Pembayaran::query()->lewatBatas()->orderBy('id')->pluck('checkout_id') as $id) { 
            $dibatalkan += $this->batalkan($id) ? 1 : 0; 
        } 
  
        return $dibatalkan; 
    } 
  
    public function batalkan(int $idCheckout): bool 
    { 
        return $this->gembok->dalam($idCheckout, function (Checkout $checkout): bool { 
            // Di bawah gembok, tagihannya diperiksa ulang. Bila pembayaran masuk sesaat sebelum 
            // gembok didapat, tidak ada yang dibatalkan. 
            if (! $this->pembayaran->tandaiKedaluwarsa($checkout)) { 
                return false; 
            } 
  
            foreach ($checkout->pesanan()->with('butir')->orderBy('id')->get() as $pesanan) { 
                $this->mesin->pindahkan( 
                    $pesanan, StatusPesanan::Dibatalkan, PelakuPesanan::Sistem, self::ALASAN, 
                ); 
  
                foreach ($pesanan->butir as $butir) { 
                    $this->stok->kembalikan($butir->produk_id, $butir->kuantitas); 
                } 
            } 
  
            return true; 
        }); 
    } 
}