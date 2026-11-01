<?php

use App\Enums\NamaPeran;
use App\Models\Pengguna;
use App\Services\PenerbitSesi;

beforeEach(function () {
    $this->admin = Pengguna::factory()->admin()->create();
    $this->tini = Pengguna::factory()->pembeli()->create();
});

function alamatPeran(Pengguna $sasaran, string $peran): string
{
    return "/api/v1/admin/pengguna/{$sasaran->id}/peran/{$peran}";
}

it('menolak siapa pun yang tidak memegang izin peran.kelola', function (string $pemegang) {
    $pelaku = Pengguna::factory()->{$pemegang}()->create();

    $this->sebagai($pelaku)->getJson('/api/v1/admin/pengguna')->assertForbidden();
    $this->putJson(alamatPeran($this->tini, 'keuangan'))
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'DITOLAK');

    expect($this->tini->punyaPeran(NamaPeran::Keuangan))->toBeFalse();
})->with(['pembeli', 'penjual', 'keuangan', 'auditor']);

it('mencabut seluruh sesi sasaran begitu perannya berubah', function () {
    [, $kunciTini] = app(PenerbitSesi::class)->terbitkan($this->tini, 'Ponsel Tini');
    $this->denganKunci($kunciTini)->getJson('/api/v1/saya')->assertOk();

    $this->sebagai($this->admin)->putJson(alamatPeran($this->tini, 'keuangan'))->assertOk();

    $this->denganKunci($kunciTini)->getJson('/api/v1/saya')->assertUnauthorized();
    $this->sebagai($this->admin)->getJson('/api/v1/saya')->assertOk();
});

it('menuntut sesi segar untuk mengubah peran, tetapi tidak untuk melihat daftar (LP-15)', function () {
    $this->sebagai($this->admin, segar: false);

    $this->getJson('/api/v1/admin/pengguna')->assertOk();
    $this->putJson(alamatPeran($this->tini, 'keuangan'))
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'LP-15');
    $this->deleteJson(alamatPeran($this->tini, 'pembeli'))
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'LP-15');

    expect($this->tini->punyaPeran(NamaPeran::Pembeli))->toBeTrue();
});

it('melarang admin mengubah perannya sendiri', function (string $metode, string $peran) {
    $this->sebagai($this->admin)->json($metode, alamatPeran($this->admin, $peran))
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'DITOLAK');
})->with([
    'beri' => ['PUT', 'keuangan'],
    'cabut' => ['DELETE', 'admin'],
]);

it('menolak pemberian atau pencabutan peran penjual lewat pintu admin (LP-3)', function (string $metode) {
    $this->sebagai($this->admin)->json($metode, alamatPeran($this->tini, 'penjual'))
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'LP-3');
})->with(['PUT', 'DELETE']);
