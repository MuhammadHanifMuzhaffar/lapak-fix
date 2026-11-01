<?php 
  
use App\Enums\StatusProduk; 
use App\Enums\StatusToko; 
use App\Exceptions\ProdukTidakDapatDibeli; 
use App\Models\Produk; 
use App\Models\Toko; 
use App\Services\PenjagaStok; 
  
beforeEach(function () { 
    $this->penjaga = app(PenjagaStok::class); 
    $this->kain = Produk::factory()->create(['nama' => 'Kain Batik Cap', 'stok' => 5]); 
}); 
  
it('mengambil produk berkunci nomornya, beserta tokonya', function () { 
    $lain = Produk::factory()->create(); 
    Produk::factory()->create(); 
  
    $produk = $this->penjaga->ambil([$lain->id, $this->kain->id, 999_999]); 
  
    expect($produk->keys()->sort()->values()->all())->toBe([$this->kain->id, $lain->id]) 
        ->and($produk[$this->kain->id]->relationLoaded('toko'))->toBeTrue(); 
}); 
  
it('mengurangi stok sebanyak yang dibeli', function () { 
    $this->penjaga->kurangi($this->penjaga->ambil([$this->kain->id])[$this->kain->id], 2); 
  
    expect($this->kain->refresh()->stok)->toBe(3); 
}); 
  
it('menghabiskan stok sampai nol, tidak lebih (LP-4)', function () { 
    $kain = $this->penjaga->ambil([$this->kain->id])[$this->kain->id]; 
  
    $this->penjaga->kurangi($kain, 5); 
    expect($this->kain->refresh()->stok)->toBe(0); 
  
    expect(fn () => $this->penjaga->kurangi($kain, 1)) 
        ->toThrow(ProdukTidakDapatDibeli::class, 'Stok Kain Batik Cap tidak mencukupi.'); 
    expect($this->kain->refresh()->stok)->toBe(0); 
}); 
  
it('menolak pembelian melebihi stok dengan 409 dan tidak mengubah apa pun', function () { 
    $kain = $this->penjaga->ambil([$this->kain->id])[$this->kain->id]; 
  
    try { 
        $this->penjaga->kurangi($kain, 6); 
        $this->fail('Seharusnya ditolak.'); 
    } catch (ProdukTidakDapatDibeli $galat) { 
        expect($galat->kode)->toBe('LP-4') 
            ->and($galat->statusHttp)->toBe(409) 
            ->and($galat->rincian)->toMatchArray(['diminta' => 6, 'tersedia' => 5]); 
    } 
  
    expect($this->kain->refresh()->stok)->toBe(5); 
});