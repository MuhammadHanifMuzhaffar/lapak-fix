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

it('membuka toko atas nama pengguna sesi, bukan atas nama kiriman klien', function () {
    $sri = Pengguna::factory()->pembeli()->create();
    $orangLain = Pengguna::factory()->create();

    $slug = $this->sebagai($sri)->postJson('/api/v1/toko-saya', [
        'nama' => 'Batik Laweyan Bu Sri', 'kota' => 'Surakarta', 'ongkir_rupiah' => 12_000,
        'pemilik_id' => $orangLain->id, 'status' => 'aktif', 'slug' => 'toko-resmi',
    ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'menunggu')
        ->assertJsonPath('data.terlihat_publik', false)
        ->json('data.slug');

    $toko = Toko::sole();

    expect($toko->pemilik_id)->toBe($sri->id)
        ->and($slug)->toStartWith('batik-laweyan-bu-sri-')->not->toBe('toko-resmi');

    $this->tanpaKunci()->getJson("/api/v1/toko/{$slug}")->assertNotFound();
});

it('menolak toko kedua untuk pengguna yang sama (LP-3)', function () {
    $sri = Pengguna::factory()->penjual()->create();
    Toko::factory()->for($sri, 'pemilik')->create();

    $this->sebagai($sri)->postJson('/api/v1/toko-saya', [
        'nama' => 'Cabang Kedua', 'kota' => 'Sukoharjo', 'ongkir_rupiah' => 9_000,
    ])
        ->assertConflict()
        ->assertJsonPath('galat.kode', 'LP-3');

    expect(Toko::count())->toBe(1);
});

it('mengubah hanya kolom yang dikirim, dan hanya pada toko sendiri', function () {
    $sri = Pengguna::factory()->create();
    $tokoSri = Toko::factory()->for($sri, 'pemilik')->create(['nama' => 'Batik Bu Sri']);
    $tokoLain = Toko::factory()->create(['nama' => 'Toko Orang Lain']);

    $this->sebagai($sri)->patchJson('/api/v1/toko-saya', ['ongkir_rupiah' => 15_000, 'status' => 'aktif'])
        ->assertOk()
        ->assertJsonPath('data.nama', 'Batik Bu Sri')
        ->assertJsonPath('data.ongkir.rupiah', 15_000);

    expect($tokoSri->refresh()->slug)->toBe($tokoSri->slug)
        ->and($tokoLain->refresh()->nama)->toBe('Toko Orang Lain');
});

it('menolak ongkir pecahan atau negatif (LP-1)', function (mixed $ongkir) {
    $this->sebagai(Pengguna::factory()->pembeli()->create())->postJson('/api/v1/toko-saya', [
        'nama' => 'Toko Uji', 'kota' => 'Surakarta', 'ongkir_rupiah' => $ongkir,
    ])
        ->assertUnprocessable()
        ->assertJsonStructure(['galat' => ['rincian' => ['ongkir_rupiah']]]);
})->with([
    'pecahan' => [12_000.5],
    'negatif' => [-1],
    'teks berformat' => ['12.000'],
]);

it('menolak pembukaan toko oleh akun yang tidak memegang izin toko.buka', function (string $pemegang) {
    $this->sebagai(Pengguna::factory()->{$pemegang}()->create())->postJson('/api/v1/toko-saya', [
        'nama' => 'Toko Staf', 'kota' => 'Surakarta', 'ongkir_rupiah' => 10_000,
    ])
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'DITOLAK');

    expect(Toko::count())->toBe(0);
})->with(['admin', 'keuangan', 'auditor']);

it('membolehkan pemilik toko yang masih menunggu membenahi datanya', function () {
    $tini = Pengguna::factory()->pembeli()->create();
    Toko::factory()->menunggu()->for($tini, 'pemilik')->create();

    $this->sebagai($tini)->patchJson('/api/v1/toko-saya', ['kota' => 'Karanganyar'])
        ->assertOk()
        ->assertJsonPath('data.kota', 'Karanganyar')
        ->assertJsonPath('data.status', 'menunggu');
});
