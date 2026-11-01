<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PemeriksaSesi;
use App\Services\PenerbitSesi;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->penerbit = app(PenerbitSesi::class);
    $this->pemeriksa = app(PemeriksaSesi::class);
    $this->pengguna = Pengguna::factory()->create();
    [$this->sesi, $this->kunci] = $this->penerbit->terbitkan($this->pengguna, 'Laptop uji', '10.0.0.7');
});

it('menyimpan hash dan tidak menyimpan rahasia di kolom mana pun (LP-14)', function () {
    $rahasia = explode('.', $this->kunci)[1];
    $baris = (array) DB::table('sesi')->first();

    expect($baris['hash_kunci'])->toBe(hash('sha256', $rahasia))
        ->and(json_encode($baris))->not->toContain($rahasia)
        ->and($this->sesi->toArray())->not->toHaveKey('hash_kunci');
});

it('menerima kunci yang sah dan memuat penggunanya', function () {
    $sesi = $this->pemeriksa->periksa($this->kunci);

    expect($sesi)->not->toBeNull()
        ->and($sesi->pengguna->is($this->pengguna))->toBeTrue();
});

it('menolak pengenal yang benar dengan rahasia yang salah', function () {
    $palsu = 'lpk_'.$this->sesi->pengenal.'.'.str_repeat('0', 64);

    expect($this->pemeriksa->periksa($palsu))->toBeNull()
        ->and($this->pemeriksa->periksa(null))->toBeNull();
});

it('menolak sesi yang sudah dicabut', function () {
    $this->penerbit->cabut($this->sesi);

    expect($this->pemeriksa->periksa($this->kunci))->toBeNull();
});

it('menolak sesi yang menganggur lebih dari dua jam', function () {
    $this->travel(119)->minutes();
    expect($this->pemeriksa->periksa($this->kunci))->not->toBeNull();

    $this->travel(121)->minutes();
    expect($this->pemeriksa->periksa($this->kunci))->toBeNull();
});

it('menolak sesi berumur lebih dari tujuh hari walau terus dipakai', function () {
    foreach (range(1, 167) as $jam) {
        $this->travel(1)->hours();
        expect($this->pemeriksa->periksa($this->kunci))->not->toBeNull();
    }

    $this->travel(2)->hours();
    expect($this->pemeriksa->periksa($this->kunci))->toBeNull();
});

it('menolak kunci milik akun yang ditangguhkan', function () {
    $this->pengguna->forceFill(['status' => 'ditangguhkan'])->save();

    expect($this->pemeriksa->periksa($this->kunci))->toBeNull();
});

it('mencatat pemakaian paling sering satu kali per menit', function () {
    $awal = $this->sesi->terakhir_dipakai_pada;

    $this->travel(30)->seconds();
    $this->pemeriksa->periksa($this->kunci);
    expect($this->sesi->refresh()->terakhir_dipakai_pada->equalTo($awal))->toBeTrue();

    $this->travel(31)->seconds();
    $this->pemeriksa->periksa($this->kunci);
    expect($this->sesi->refresh()->terakhir_dipakai_pada->gt($awal))->toBeTrue();
});

it('mematikan kunci lama saat dirotasi tanpa mengganti sesinya', function () {
    $kunciBaru = $this->penerbit->rotasi($this->sesi);

    expect($this->pemeriksa->periksa($this->kunci))->toBeNull()
        ->and($this->pemeriksa->periksa($kunciBaru)?->pengenal)->toBe($this->sesi->pengenal)
        ->and(Sesi::count())->toBe(1);
});
