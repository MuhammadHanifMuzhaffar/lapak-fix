<?php

use App\Models\ButirKeranjang;
use App\Models\Keranjang;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;

function isiKeranjang(Pengguna $pengguna, Produk $produk, int $kuantitas): void
{
    $keranjang = Keranjang::query()->where('pengguna_id', $pengguna->id)->first() ?? new Keranjang;
    $keranjang->forceFill(['pengguna_id' => $pengguna->id])->save();

    $butir = new ButirKeranjang(['produk_id' => $produk->id, 'kuantitas' => $kuantitas]);
    $butir->forceFill(['keranjang_id' => $keranjang->id])->save();
}

beforeEach(function () {
    $this->andi = Pengguna::factory()->create();
    $this->batik = Toko::factory()->create(['nama' => 'Batik Bu Sri', 'ongkir_rupiah' => 12_000]);
    $this->kain = Produk::factory()->for($this->batik)->create([
        'nama' => 'Kain Batik Cap', 'harga_rupiah' => 185_000, 'stok' => 5,
    ]);
});

it('menolak permintaan tanpa kunci sesi', function () {
    $this->getJson('/api/v1/keranjang')->assertUnauthorized();
});

it('menampilkan keranjang kosong dalam bentuk yang sama dengan keranjang berisi', function () {
    $this->sebagai($this->andi)->getJson('/api/v1/keranjang')
        ->assertOk()
        ->assertExactJson(['sukses' => true, 'pesan' => 'Operasi berhasil', 'data' => [
            'pesanan' => [],
            'total_bayar' => ['rupiah' => 0, 'tampilan' => 'Rp 0'],
            'tidak_tersedia' => [],
        ]]);
});

it('menghitung isi keranjang dari harga terkini, bukan harga saat dimasukkan (LP-6)', function () {
    isiKeranjang($this->andi, $this->kain, 2);

    $this->sebagai($this->andi)->getJson('/api/v1/keranjang')
        ->assertJsonPath('data.total_bayar.rupiah', 382_000);

    $this->kain->update(['harga_rupiah' => 200_000]);

    $this->getJson('/api/v1/keranjang')
        ->assertJsonPath('data.pesanan.0.baris.0.harga_satuan', 200_000)
        ->assertJsonPath('data.total_bayar.rupiah', 412_000);
});

it('memisahkan butir yang tidak lagi dapat dibeli tanpa menggagalkan keranjang', function () {
    $arsip = Produk::factory()->for($this->batik)->create(['stok' => 9]);
    isiKeranjang($this->andi, $this->kain, 6);
    isiKeranjang($this->andi, $arsip, 1);
    $arsip->forceFill(['status' => 'arsip'])->save();

    $this->sebagai($this->andi)->getJson('/api/v1/keranjang')
        ->assertOk()
        ->assertJsonPath('data.pesanan', [])
        ->assertJsonPath('data.total_bayar.rupiah', 0)
        ->assertJsonPath('data.tidak_tersedia.0', [
            'produk_id' => $this->kain->id,
            'kuantitas' => 6,
            'alasan' => 'stok_kurang',
            'stok_tersedia' => 5,
        ])
        ->assertJsonPath('data.tidak_tersedia.1', [
            'produk_id' => $arsip->id,
            'kuantitas' => 1,
            'alasan' => 'tidak_tersedia',
            'stok_tersedia' => null,
        ]);
});

it('tidak pernah menampilkan keranjang pengguna lain', function () {
    isiKeranjang($this->andi, $this->kain, 2);

    $this->sebagai(Pengguna::factory()->create())->getJson('/api/v1/keranjang')
        ->assertOk()
        ->assertJsonPath('data.pesanan', []);
});
