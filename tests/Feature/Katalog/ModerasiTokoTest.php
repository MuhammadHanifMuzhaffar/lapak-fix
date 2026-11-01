<?php

use App\Enums\NamaPeran;
use App\Enums\StatusToko;
use App\Exceptions\TransisiTokoTidakSah;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\ModerasiToko;
use App\Services\PenerbitSesi;
use App\Services\PengelolaPeran;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = Pengguna::factory()->admin()->create();
    $this->tini = Pengguna::factory()->pembeli()->create(['nama' => 'Tini Lestari']);
    $this->tokoTini = Toko::factory()->menunggu()->for($this->tini, 'pemilik')
        ->create(['nama' => 'Wedang Uwuh Bu Tini']);
});

it('menyetujui toko: status aktif, pemilik menjadi penjual, jejak tercatat', function () {
    Produk::factory()->for($this->tokoTini)->create();
    $this->tanpaKunci()->getJson('/api/v1/produk')->assertJsonCount(0, 'data');

    $this->sebagai($this->admin)->postJson("/api/v1/admin/toko/{$this->tokoTini->id}/setujui")
        ->assertOk()
        ->assertJsonPath('data.status', 'aktif')
        ->assertJsonPath('data.pemilik.id', $this->tini->id);

    $toko = $this->tokoTini->refresh();

    expect($toko->status)->toBe(StatusToko::Aktif)
        ->and($toko->dimoderasi_oleh)->toBe($this->admin->id)
        ->and($toko->dimoderasi_pada)->not->toBeNull()
        ->and(app(PengelolaPeran::class)->daftar($this->tini))->toBe(['pembeli', 'penjual']);

    $this->tanpaKunci()->getJson('/api/v1/produk')->assertJsonCount(1, 'data');
});

it('memaksa pemilik masuk ulang, dan baru setelah itu ia dapat mengelola produk', function () {
    [, $kunciLama] = app(PenerbitSesi::class)->terbitkan($this->tini, 'Ponsel Tini');
    $this->denganKunci($kunciLama)->getJson('/api/v1/toko-saya/produk')->assertForbidden();

    $this->sebagai($this->admin)->postJson("/api/v1/admin/toko/{$this->tokoTini->id}/setujui")
        ->assertOk();

    $this->denganKunci($kunciLama)->getJson('/api/v1/toko-saya/produk')->assertUnauthorized();
    $this->sebagai($this->tini)->getJson('/api/v1/toko-saya/produk')->assertOk();
});

it('menangguhkan toko dengan alasan, tanpa mencabut peran dan sesi pemiliknya', function () {
    $sri = Pengguna::factory()->penjual()->create();
    $toko = Toko::factory()->for($sri, 'pemilik')->create();
    Produk::factory()->for($toko)->create();
    [, $kunciSri] = app(PenerbitSesi::class)->terbitkan($sri, 'Laptop Sri');

    $this->sebagai($this->admin)->postJson("/api/v1/admin/toko/{$toko->id}/tangguhkan", [
        'alasan' => 'Foto produk memakai merek pihak lain.',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', 'ditangguhkan')
        ->assertJsonPath('data.moderasi.catatan', 'Foto produk memakai merek pihak lain.');

    expect(app(PengelolaPeran::class)->daftar($sri))->toBe(['pembeli', 'penjual']);
    $this->denganKunci($kunciSri)->getJson('/api/v1/saya')->assertOk();
    $this->tanpaKunci()->getJson('/api/v1/produk')->assertJsonCount(0, 'data');
});

it('melarang admin memoderasi tokonya sendiri', function () {
    $adminBerjualan = Pengguna::factory()->denganPeran(NamaPeran::Admin, NamaPeran::Pembeli)->create();
    $tokoSendiri = Toko::factory()->menunggu()->for($adminBerjualan, 'pemilik')->create();

    $this->sebagai($adminBerjualan)->postJson("/api/v1/admin/toko/{$tokoSendiri->id}/setujui")
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'DITOLAK');

    expect($tokoSendiri->refresh()->status)->toBe(StatusToko::Menunggu)
        ->and($adminBerjualan->punyaPeran(NamaPeran::Penjual))->toBeFalse();

    // Admin lain boleh.
    $this->sebagai($this->admin)->postJson("/api/v1/admin/toko/{$tokoSendiri->id}/setujui")->assertOk();
});

it('hanya mengubah satu kali bila dua admin menyetujui toko yang sama', function () {
    $adminKedua = Pengguna::factory()->admin()->create();

    $salinanPertama = Toko::query()->findOrFail($this->tokoTini->id);
    $salinanKedua = Toko::query()->findOrFail($this->tokoTini->id);

    app(ModerasiToko::class)->setujui($salinanPertama, $this->admin);

    expect($salinanKedua->status)->toBe(StatusToko::Menunggu)
        ->and(fn () => app(ModerasiToko::class)->setujui($salinanKedua, $adminKedua))
        ->toThrow(TransisiTokoTidakSah::class)
        ->and($this->tokoTini->refresh()->dimoderasi_oleh)->toBe($this->admin->id);
});

it('tidak mengubah status bila pemberian peran gagal', function () {
    DB::table('peran')->delete();

    expect(fn () => app(ModerasiToko::class)->setujui($this->tokoTini, $this->admin))
        ->toThrow(LogicException::class);

    expect($this->tokoTini->refresh()->status)->toBe(StatusToko::Menunggu);
});
