<?php

use App\Enums\StatusProduk;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;

beforeEach(function () {
    $this->sri = Pengguna::factory()->create();
    $this->tokoSri = Toko::factory()->for($this->sri, 'pemilik')->create();
    $this->batik = Kategori::factory()->create();

    $this->dataProduk = fn (array $timpa = []) => array_merge([
        'nama' => 'Kain Batik Cap Sogan', 'kategori_id' => $this->batik->id,
        'harga_rupiah' => 185_000, 'stok' => 25, 'berat_gram' => 400,
    ], $timpa);
});

it('menjawab 404 bagi pengguna yang belum membuka toko', function () {
    $this->sebagai(Pengguna::factory()->create())
        ->postJson('/api/v1/toko-saya/produk', ($this->dataProduk)())
        ->assertNotFound();
});

it('menyimpan produk sebagai draf di toko milik pengguna sesi', function () {
    $tokoLain = Toko::factory()->create();

    $this->sebagai($this->sri)
        ->postJson('/api/v1/toko-saya/produk', ($this->dataProduk)(['toko_id' => $tokoLain->id]))
        ->assertCreated()
        ->assertJsonPath('data.status', 'draf')
        ->assertJsonPath('data.harga.tampilan', 'Rp 185.000');

    expect(Produk::sole()->toko_id)->toBe($this->tokoSri->id);

    $this->tanpaKunci()->getJson('/api/v1/produk')->assertJsonCount(0, 'data');
});

it('menerbitkan produk lewat perubahan status', function () {
    $produk = Produk::factory()->for($this->tokoSri)->draf()->create();

    $this->sebagai($this->sri)
        ->patchJson("/api/v1/toko-saya/produk/{$produk->id}", ['status' => 'aktif'])
        ->assertOk()
        ->assertJsonPath('data.status', 'aktif');

    $this->tanpaKunci()->getJson('/api/v1/produk')->assertJsonPath('data.0.id', $produk->id);
});

it('menolak harga yang bukan bilangan bulat rupiah (LP-1)', function (mixed $harga) {
    $this->sebagai($this->sri)
        ->postJson('/api/v1/toko-saya/produk', ($this->dataProduk)(['harga_rupiah' => $harga]))
        ->assertUnprocessable()
        ->assertJsonStructure(['galat' => ['rincian' => ['harga_rupiah']]]);
})->with([
    'pecahan' => [185_000.5],
    'teks berformat' => ['185.000'],
    'nol' => [0],
    'negatif' => [-185_000],
    'melampaui batas' => [1_000_000_001],
]);

it('menolak status di luar tiga pilihan', function () {
    $produk = Produk::factory()->for($this->tokoSri)->create();

    $this->sebagai($this->sri)
        ->patchJson("/api/v1/toko-saya/produk/{$produk->id}", ['status' => 'unggulan'])
        ->assertUnprocessable();
});

it('menjawab 404 untuk produk toko lain dan tidak mengubahnya', function () {
    $milikLain = Produk::factory()->create(['nama' => 'Produk Orang Lain', 'harga_rupiah' => 50_000]);

    $this->sebagai($this->sri)
        ->patchJson("/api/v1/toko-saya/produk/{$milikLain->id}", ['harga_rupiah' => 100])
        ->assertNotFound();
    $this->deleteJson("/api/v1/toko-saya/produk/{$milikLain->id}")->assertNotFound();

    $this->patchJson('/api/v1/toko-saya/produk/bukan-angka', ['harga_rupiah' => 100])->assertNotFound();

    expect($milikLain->refresh())
        ->harga_rupiah->toBe(50_000)
        ->status->toBe(StatusProduk::Aktif);
});

it('mengarsipkan, bukan menghapus', function () {
    $produk = Produk::factory()->for($this->tokoSri)->create();

    $this->sebagai($this->sri)
        ->deleteJson("/api/v1/toko-saya/produk/{$produk->id}")
        ->assertOk()
        ->assertJsonPath('data.status', 'arsip');

    expect(Produk::whereKey($produk->id)->exists())->toBeTrue();
    $this->tanpaKunci()->getJson("/api/v1/produk/{$produk->slug}")->assertNotFound();
});

it('mendaftar seluruh produk sendiri, termasuk draf dan arsip, tanpa produk toko lain', function () {
    Produk::factory()->for($this->tokoSri)->create();
    Produk::factory()->for($this->tokoSri)->draf()->create();
    Produk::factory()->for($this->tokoSri)->create(['status' => StatusProduk::Arsip]);
    Produk::factory()->count(2)->create();

    $this->sebagai($this->sri)->getJson('/api/v1/toko-saya/produk')
        ->assertOk()
        ->assertJsonPath('meta.total_data', 3);
});
