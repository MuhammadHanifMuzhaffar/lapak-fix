<?php

use App\Enums\NamaPeran;
use App\Models\Pengguna;
use App\Services\PenerbitSesi;
use App\Services\PengelolaPeran;

beforeEach(function () {
    $this->pengelola = app(PengelolaPeran::class);
    $this->tini = Pengguna::factory()->create();
});

it('memberi peran dan mencatat siapa yang memberikannya', function () {
    $admin = Pengguna::factory()->create();

    expect($this->pengelola->beri($this->tini, NamaPeran::Keuangan, oleh: $admin))->toBeTrue();

    $baris = $this->tini->peran()->first();
    expect($baris->nama)->toBe('keuangan')
        ->and($baris->pivot->diberikan_oleh)->toBe($admin->id);
});

it('mengembalikan false bila peran sudah dipegang dan tidak menduplikasi baris', function () {
    $this->pengelola->beri($this->tini, NamaPeran::Pembeli);

    expect($this->pengelola->beri($this->tini, NamaPeran::Pembeli))->toBeFalse()
        ->and($this->tini->peran()->count())->toBe(1);
});

it('mencabut seluruh sesi pengguna sasaran saat perannya berubah', function () {
    $penerbit = app(PenerbitSesi::class);
    [, $kunciLaptop] = $penerbit->terbitkan($this->tini, 'Laptop Tini');
    [, $kunciPonsel] = $penerbit->terbitkan($this->tini, 'Ponsel Tini');

    $this->denganKunci($kunciLaptop)->getJson('/api/v1/saya')->assertOk();

    $this->pengelola->beri($this->tini, NamaPeran::Pembeli);

    $this->denganKunci($kunciLaptop)->getJson('/api/v1/saya')->assertUnauthorized();
    $this->denganKunci($kunciPonsel)->getJson('/api/v1/saya')->assertUnauthorized();
});

it('tidak mencabut sesi pengguna lain saat seorang pengguna berubah perannya', function () {
    $sri = Pengguna::factory()->create();
    $penerbit = app(PenerbitSesi::class);
    [, $kunciSri] = $penerbit->terbitkan($sri, 'Laptop Sri');

    $this->pengelola->beri($this->tini, NamaPeran::Keuangan);

    $this->denganKunci($kunciSri)->getJson('/api/v1/saya')->assertOk();
});

it('mengembalikan false bila mencabut peran yang tidak dipegang', function () {
    expect($this->pengelola->cabut($this->tini, NamaPeran::Auditor))->toBeFalse();
});
