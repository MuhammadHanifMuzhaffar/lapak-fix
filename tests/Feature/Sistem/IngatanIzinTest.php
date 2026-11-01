<?php

use App\Enums\NamaPeran;
use App\Models\Pengguna;
use App\Services\PemeriksaIzin;
use App\Services\PengelolaPeran;

beforeEach(function () {
    $this->pemeriksa = app(PemeriksaIzin::class);
    $this->pengelola = app(PengelolaPeran::class);
    $this->pengguna = Pengguna::factory()->create();
});

it('melupakan ingatan izin saat peran berubah', function () {
    $this->pengelola->beri($this->pengguna, NamaPeran::Pembeli);
    expect($this->pemeriksa->daftar($this->pengguna))->toBe(['keranjang.kelola', 'toko.buka']);

    $this->pengelola->beri($this->pengguna, NamaPeran::Admin);
    expect($this->pemeriksa->daftar($this->pengguna))->toBe([
        'keranjang.kelola', 'peran.kelola', 'toko.buka', 'toko.moderasi',
    ]);
});

it('menampilkan tabel matriks izin lewat artisan', function () {
    $this->artisan('lapak:matriks-izin')->assertSuccessful();
});
