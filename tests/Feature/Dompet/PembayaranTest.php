<?php 
  
use App\Enums\StatusPembayaran; 
use App\Models\Checkout; 

use App\Models\Pembayaran; 
use App\Services\GembokCheckout; 
use App\Services\LayananPembayaran; 
use Illuminate\Database\UniqueConstraintViolationException; 
  
beforeEach(function () { 
    $this->layanan = app(LayananPembayaran::class); 
    $this->checkout = Checkout::factory()->create(['total_rupiah' => 567_500]); 
}); 
  
it('membuat tagihan sebesar total checkout dengan batas bayar 24 jam (LP-7, LP-10)', function () { 
    $this->freezeTime(); 
  
    $pembayaran = $this->layanan->buat($this->checkout); 
  
    expect($pembayaran->jumlah_rupiah)->toBe(567_500) 
        ->and($pembayaran->status)->toBe(StatusPembayaran::Menunggu) 
        ->and($pembayaran->kode)->toMatch('/\ABYR-[0-9A-Z]{26}\z/') 
        ->and($pembayaran->batas_bayar_pada->toDateTimeString()) 
        ->toBe(now()->addHours(24)->toDateTimeString()) 
        ->and($this->layanan->untuk($this->checkout)->is($pembayaran))->toBeTrue(); 
}); 
  
it('menolak tagihan kedua untuk checkout yang sama (LP-7)', function () { 
    $this->layanan->buat($this->checkout); 
    $this->layanan->buat($this->checkout); 
})->throws(UniqueConstraintViolationException::class); 
  
it('hanya menandai kedaluwarsa tagihan yang menunggu dan sudah lewat batas', function () { 
    $pembayaran = $this->layanan->buat($this->checkout); 
    $tandai = fn () => app(GembokCheckout::class)->dalam( 
        $this->checkout->id, 
        fn (Checkout $terkunci) => $this->layanan->tandaiKedaluwarsa($terkunci), 
    ); 
  
    expect($tandai())->toBeFalse() 
        ->and($pembayaran->refresh()->status)->toBe(StatusPembayaran::Menunggu); 
  
    $this->travel(24)->hours(); 
    $this->travel(1)->seconds(); 
  
    expect($tandai())->toBeTrue() 
        ->and($pembayaran->refresh()->status)->toBe(StatusPembayaran::Kedaluwarsa) 
        ->and($tandai())->toBeFalse();   // sudah kedaluwarsa: tidak ada yang berubah lagi 
});