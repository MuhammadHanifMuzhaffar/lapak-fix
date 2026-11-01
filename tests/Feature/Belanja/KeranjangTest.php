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

it('menaruh butir dan menetapkan kuantitasnya, bukan menambahkannya', function () {
    $alamat = "/api/v1/keranjang/butir/{$this->kain->id}";

    $this->sebagai($this->andi)->putJson($alamat, ['kuantitas' => 2])
        ->assertOk()
        ->assertJsonPath('data.total_bayar.rupiah', 382_000);

    $this->putJson($alamat, ['kuantitas' => 2])->assertJsonPath('data.total_bayar.rupiah', 382_000);

    expect(ButirKeranjang::sole()->kuantitas)->toBe(2);
});

it('tidak menyimpan harga kiriman klien di mana pun (LP-6)', function () {
    $this->sebagai($this->andi)
        ->putJson("/api/v1/keranjang/butir/{$this->kain->id}", ['kuantitas' => 1, 'harga_satuan' => 1])
        ->assertOk()
        ->assertJsonPath('data.pesanan.0.baris.0.harga_satuan', 185_000);
});

it('menolak penjual yang memasukkan produk tokonya sendiri (LP-5)', function () {
    $sri = $this->batik->pemilik;

    $this->sebagai($sri)->putJson("/api/v1/keranjang/butir/{$this->kain->id}", ['kuantitas' => 1])
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'LP-5');

    expect(ButirKeranjang::count())->toBe(0);

    $produkLain = Produk::factory()->create(['stok' => 3]);
    $this->putJson("/api/v1/keranjang/butir/{$produkLain->id}", ['kuantitas' => 1])->assertOk();
});

it('menolak produk yang tidak tampil dengan jawaban yang sama untuk semua sebabnya (LP-4)', function () {
    $draf = Produk::factory()->for($this->batik)->draf()->create();

    $jawabanDraf = $this->sebagai($this->andi)
        ->putJson("/api/v1/keranjang/butir/{$draf->id}", ['kuantitas' => 1])
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'LP-4');
    $jawabanTakAda = $this->putJson('/api/v1/keranjang/butir/999999', ['kuantitas' => 1])
        ->assertUnprocessable();

    expect($jawabanDraf->json('galat.pesan'))->toBe($jawabanTakAda->json('galat.pesan'));
});

it('menolak kuantitas melebihi stok dengan status 409 (LP-4)', function () {
    $this->sebagai($this->andi)
        ->putJson("/api/v1/keranjang/butir/{$this->kain->id}", ['kuantitas' => 6])
        ->assertConflict()
        ->assertJsonPath('galat.rincian.tersedia', 5);
});

it('menolak kuantitas yang tidak masuk akal', function (mixed $kuantitas) {
    $this->sebagai($this->andi)
        ->putJson("/api/v1/keranjang/butir/{$this->kain->id}", ['kuantitas' => $kuantitas])
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'VALIDASI');
})->with([
    'nol' => [0], 'negatif' => [-2], 'pecahan' => [1.5], 'terlalu banyak' => [100], 'teks' => ['dua'],
]);

it('membatasi jumlah produk berbeda dalam satu keranjang', function () {
    config(['lapak.belanja.butir_maksimum' => 2]);
    $lain = Produk::factory()->count(2)->create(['stok' => 5]);

    $taruh = fn (Produk $produk, int $kuantitas) => $this->putJson(
        "/api/v1/keranjang/butir/{$produk->id}", ['kuantitas' => $kuantitas],
    );

    $this->sebagai($this->andi);
    $taruh($this->kain, 1)->assertOk();
    $taruh($lain[0], 1)->assertOk();
    $taruh($lain[1], 1)->assertUnprocessable()->assertJsonPath('galat.kode', 'KERANJANG_PENUH');

    $taruh($this->kain, 3)->assertOk();
});

it('menghapus butir dan mengosongkan keranjang tanpa menyentuh keranjang orang lain', function () {
    $dewi = Pengguna::factory()->create();
    isiKeranjang($dewi, $this->kain, 1);
    isiKeranjang($this->andi, $this->kain, 2);

    $this->sebagai($this->andi)->deleteJson("/api/v1/keranjang/butir/{$this->kain->id}")
        ->assertOk()
        ->assertJsonPath('data.pesanan', []);

    $this->deleteJson("/api/v1/keranjang/butir/{$this->kain->id}")->assertOk();
    $this->deleteJson('/api/v1/keranjang')->assertOk();

    expect(ButirKeranjang::count())->toBe(1);
    $this->sebagai($dewi)->getJson('/api/v1/keranjang')
        ->assertJsonPath('data.total_bayar.rupiah', 197_000);
});
