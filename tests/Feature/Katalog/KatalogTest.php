<?php

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Toko;

it('menampilkan produk aktif dari toko aktif dengan amplop berhalaman', function () {
    Produk::factory()->count(3)->create();

    $this->getJson('/api/v1/produk')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 3)
        ->assertJsonStructure(['data' => [[
            'id', 'nama', 'slug', 'harga' => ['rupiah', 'tampilan'], 'toko' => ['nama', 'slug', 'kota'],
        ]]]);
});

it('menyembunyikan produk draf dan produk dari toko yang belum aktif (LP-4)', function () {
    $tampil = Produk::factory()->create(['nama' => 'Batik Tampil']);
    Produk::factory()->draf()->create(['slug' => 'produk-draf']);
    Produk::factory()->for(Toko::factory()->menunggu())->create(['slug' => 'produk-toko-menunggu']);

    $this->getJson('/api/v1/produk')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $tampil->slug);

    $this->getJson('/api/v1/produk/produk-draf')->assertNotFound();
    $this->getJson('/api/v1/produk/produk-toko-menunggu')->assertNotFound();
});

it('menyaring menurut kategori, rentang harga, dan ketersediaan', function () {
    $batik = Kategori::factory()->create(['slug' => 'batik']);
    Produk::factory()->for($batik)->create(['slug' => 'murah', 'harga_rupiah' => 50_000]);
    Produk::factory()->for($batik)->create(['slug' => 'mahal', 'harga_rupiah' => 500_000]);
    Produk::factory()->for($batik)->habis()->create(['slug' => 'habis', 'harga_rupiah' => 60_000]);
    Produk::factory()->create(['slug' => 'kategori-lain', 'harga_rupiah' => 55_000]);

    $this->getJson('/api/v1/produk?kategori=batik&harga_maks=100000&tersedia=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'murah');
});

it('mengurutkan hanya dengan pilihan dari daftar putih', function () {
    Produk::factory()->create(['slug' => 'b', 'harga_rupiah' => 200_000]);
    Produk::factory()->create(['slug' => 'a', 'harga_rupiah' => 100_000]);

    $this->getJson('/api/v1/produk?urut=harga_naik')->assertJsonPath('data.0.slug', 'a');
    $this->getJson('/api/v1/produk?urut=harga_turun')->assertJsonPath('data.0.slug', 'b');
    $this->getJson('/api/v1/produk?urut=kata_sandi')
        ->assertUnprocessable()
        ->assertJsonStructure(['galat' => ['rincian' => ['urut']]]);
});

it('membatasi ukuran halaman', function () {
    $this->getJson('/api/v1/produk?per_halaman=1000')
        ->assertUnprocessable()
        ->assertJsonStructure(['galat' => ['rincian' => ['per_halaman']]]);
});

it('memperlakukan karakter pengganti pada pencarian sebagai teks biasa', function () {
    Produk::factory()->create(['nama' => 'Kain Batik Cap', 'slug' => 'kain-batik-cap']);
    Produk::factory()->create(['nama' => 'Paket Hemat 50% Intip', 'slug' => 'paket-hemat']);

    $cari = fn (string $kata) => $this->getJson('/api/v1/produk?cari='.urlencode($kata))->assertOk();

    $cari('%%')->assertJsonCount(0, 'data');
    $cari('50%')->assertJsonPath('data.0.slug', 'paket-hemat');
    $cari('b_tik')->assertJsonCount(0, 'data');
    $cari('batik')->assertJsonCount(1, 'data');
});

it('menolak rentang harga yang terbalik', function () {
    $this->getJson('/api/v1/produk?harga_min=200000&harga_maks=100000')
        ->assertUnprocessable()
        ->assertJsonStructure(['galat' => ['rincian' => ['harga_maks']]]);
});

it('menampilkan profil toko aktif tanpa membocorkan pemilik', function () {
    $toko = Toko::factory()->create(['slug' => 'batik-bu-sri']);
    Produk::factory()->for($toko)->count(2)->create();
    Produk::factory()->for($toko)->draf()->create();
    Toko::factory()->menunggu()->create(['slug' => 'toko-menunggu']);

    $this->getJson('/api/v1/toko/batik-bu-sri')
        ->assertOk()
        ->assertJsonPath('data.jumlah_produk', 2)
        ->assertJsonMissingPath('data.pemilik_id');

    $this->getJson('/api/v1/toko/toko-menunggu')->assertNotFound();
});
