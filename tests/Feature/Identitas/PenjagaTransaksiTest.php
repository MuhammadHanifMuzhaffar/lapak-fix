<?php 

use App\Domain\Uang; 
use App\Exceptions\SesiTidakSegar; 
use App\Models\Pengguna; 
use App\Models\Sesi; 
use App\Services\PenerbitSesi; 
use App\Services\PenjagaTransaksi; 
use Illuminate\Support\Facades\Route; 
  
beforeEach(function () { 
    config(['lapak.transaksi.batas_segar_rupiah' => 2_000_000]); 
  
    $this->penjaga = app(PenjagaTransaksi::class); 
    $this->sesi = function (bool $segar): Sesi { 
        [$sesi] = app(PenerbitSesi::class)->terbitkan(Pengguna::factory()->create(), 'Laptop'); 
  
        if (! $segar) { 
            $sesi->forceFill(['dikonfirmasi_pada' => now()->subHour()])->save(); 
        } 
  
        return $sesi; 
    }; 
}); 
  
it('meloloskan transaksi kecil pada sesi yang tidak segar', function (int $rupiah) { 
    $this->penjaga->pastikanLayak(($this->sesi)(false), Uang::dari($rupiah)); 
})->with([185_000, 2_000_000])->throwsNoExceptions(); 
  
it('menuntut sesi segar untuk transaksi di atas batas (LP-15)', function () { 
    $this->penjaga->pastikanLayak(($this->sesi)(false), Uang::dari(2_000_001)); 
})->throws(SesiTidakSegar::class); 
  
it('meloloskan transaksi besar pada sesi yang segar', function () { 
    $this->penjaga->pastikanLayak(($this->sesi)(true), Uang::dari(15_000_000)); 
})->throwsNoExceptions(); 
  
it('membatasi laju pintu transaksi per sesi, bukan per alamat IP', function () { 
    config(['lapak.transaksi.laju_per_menit' => 3]); 
    Route::middleware(['api', 'auth:sesi', 'throttle:transaksi']) 
        ->post('/api/_uji/transaksi', fn () => ['ok' => true]); 
  
    $andi = Pengguna::factory()->create(); 
    $this->sebagai($andi); 
  
    foreach (range(1, 3) as $ke) { 
        $this->postJson('/api/_uji/transaksi')->assertOk(); 
    } 
    $this->postJson('/api/_uji/transaksi') 
        ->assertStatus(429) 
        ->assertJsonPath('galat.kode', 'TERLALU_SERING') 
        ->assertHeader('Retry-After'); 
  
    // Perangkat lain milik orang yang sama, dari alamat IP yang sama, memiliki jatah sendiri. 
    $this->sebagai($andi)->postJson('/api/_uji/transaksi')->assertOk(); 
});