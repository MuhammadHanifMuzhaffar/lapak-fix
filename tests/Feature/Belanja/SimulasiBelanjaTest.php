<?php

use App\Models\Produk;
use App\Models\Toko;

beforeEach(function () {
    $this->batik = Toko::factory()->create(['nama' => 'Batik Bu Sri', 'ongkir_rupiah' => 12_000]);
    $this->kain = Produk::factory()->for($this->batik)->create([
        'nama' => 'Kain Batik Cap',
        'harga_rupiah' => 185_000,
        'stok' => 5,
    ]);

    $this->simulasi = fn (array $butir, array $lain = []) => $this->postJson(
        '/api/v1/simulasi-belanja',
        ['butir' => $butir] + $lain,
    );
});

it('menghitung total dari harga di server', function () {
    ($this->simulasi)([['produk_id' => $this->kain->id, 'kuantitas' => 2]])
        ->assertOk()
        ->assertJsonPath('data.pesanan.0.toko', 'Batik Bu Sri')
        ->assertJsonPath('data.pesanan.0.subtotal', 370_000)
        ->assertJsonPath('data.pesanan.0.ongkir', 12_000)
        ->assertJsonPath('data.total_bayar.rupiah', 382_000)
        ->assertJsonPath('data.total_bayar.tampilan', 'Rp 382.000');
});

it('mengabaikan harga dan total yang dikirim klien (LP-6)', function () {
    $curang = ['produk_id' => $this->kain->id, 'kuantitas' => 2, 'harga_satuan' => 1, 'subtotal' => 2];

    ($this->simulasi)([$curang], ['total_bayar' => 2])
        ->assertOk()
        ->assertJsonPath('data.pesanan.0.baris.0.harga_satuan', 185_000)
        ->assertJsonPath('data.total_bayar.rupiah', 382_000);
});

it('memecah belanja lintas toko menjadi beberapa pesanan (LP-7)', function () {
    $kuliner = Toko::factory()->create(['ongkir_rupiah' => 10_000]);
    $intip = Produk::factory()->for($kuliner)->create(['harga_rupiah' => 32_500, 'stok' => 10]);

    ($this->simulasi)([
        ['produk_id' => $this->kain->id, 'kuantitas' => 1],
        ['produk_id' => $intip->id, 'kuantitas' => 3],
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data.pesanan')
        ->assertJsonPath('data.total_bayar.rupiah', 185_000 + 12_000 + 97_500 + 10_000);
});

it('menolak produk yang tidak tampil tanpa membedakan alasannya (LP-4)', function () {
    $draf = Produk::factory()->for($this->batik)->draf()->create();

    ($this->simulasi)([
        ['produk_id' => $draf->id, 'kuantitas' => 1],
        ['produk_id' => 999_999, 'kuantitas' => 1],
    ])
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'LP-4')
        ->assertJsonPath('galat.rincian.produk_id', [$draf->id, 999_999]);
});

it('menolak kuantitas melebihi stok dengan status 409 (LP-4)', function () {
    ($this->simulasi)([['produk_id' => $this->kain->id, 'kuantitas' => 6]])
        ->assertConflict()
        ->assertJsonPath('galat.kode', 'LP-4')
        ->assertJsonPath('galat.rincian.tersedia', 5);
});

it('menolak bentuk masukan yang salah', function (array $butir, string $kunci) {
    ($this->simulasi)($butir)
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'VALIDASI')
        ->assertJsonStructure(['galat' => ['rincian' => [$kunci]]]);
})->with([
    'keranjang kosong' => [[], 'butir'],
    'kuantitas nol' => [[['produk_id' => 1, 'kuantitas' => 0]], 'butir.0.kuantitas'],
    'kuantitas negatif' => [[['produk_id' => 1, 'kuantitas' => -3]], 'butir.0.kuantitas'],
    'kuantitas pecahan' => [[['produk_id' => 1, 'kuantitas' => 1.5]], 'butir.0.kuantitas'],
    'produk kembar' => [
        [['produk_id' => 1, 'kuantitas' => 1], ['produk_id' => 1, 'kuantitas' => 2]],
        'butir.0.produk_id',
    ],
]);
