<?php

use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;

it('menolak permintaan tanpa kunci sesi', function () {
    $this->getJson('/api/v1/toko-saya')->assertUnauthorized();
});

it('menjawab 404 bila pengguna belum memiliki toko', function () {
    $this->sebagai(Pengguna::factory()->create())
        ->getJson('/api/v1/toko-saya')
        ->assertNotFound();
});

it('menampilkan toko milik pengguna yang sedang masuk, termasuk statusnya', function () {
    $sri = Pengguna::factory()->create();
    $toko = Toko::factory()->menunggu()->for($sri, 'pemilik')->create(['nama' => 'Batik Bu Sri']);
    Produk::factory()->for($toko)->count(2)->create();
    Toko::factory()->create(['nama' => 'Toko Orang Lain']);

    $this->sebagai($sri)->getJson('/api/v1/toko-saya')
        ->assertOk()
        ->assertJsonPath('data.nama', 'Batik Bu Sri')
        ->assertJsonPath('data.status', 'menunggu')
        ->assertJsonPath('data.terlihat_publik', false)
        ->assertJsonPath('data.jumlah_produk', 2)
        ->assertJsonMissingPath('data.pemilik_id');
});

it('mengenali produk milik pengguna (bahan LP-5)', function () {
    $sri = Pengguna::factory()->create();
    $milikSri = Produk::factory()->for(Toko::factory()->for($sri, 'pemilik'))->create();
    $milikLain = Produk::factory()->create();

    expect($milikSri->load('toko')->dimilikiOleh($sri))->toBeTrue()
        ->and($milikLain->load('toko')->dimilikiOleh($sri))->toBeFalse();
});
