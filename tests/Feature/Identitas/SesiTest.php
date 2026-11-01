<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PenerbitSesi;

beforeEach(function () {
    $this->sri = Pengguna::factory()->create(['kata_sandi' => 'BatikSolo2026']);
    $this->terbit = fn (Pengguna $pengguna, string $perangkat) => app(PenerbitSesi::class)
        ->terbitkan($pengguna, $perangkat);
});

it('menjawab siapa saya tanpa membocorkan kata sandi maupun hash kunci', function () {
    $respons = $this->sebagai($this->sri)->getJson('/api/v1/saya')
        ->assertOk()
        ->assertJsonPath('data.pengguna.id', $this->sri->id)
        ->assertJsonPath('data.sesi.pengenal', $this->sesiUji->pengenal)
        ->assertJsonPath('data.sesi.sedang_dipakai', true);

    expect($respons->getContent())
        ->not->toContain('kata_sandi', 'hash_kunci', $this->sesiUji->hash_kunci);
});

it('mencabut hanya sesi yang sedang dipakai saat keluar', function () {
    [, $kunciPonsel] = ($this->terbit)($this->sri, 'Ponsel');

    $this->sebagai($this->sri)->postJson('/api/v1/keluar')->assertOk();

    $this->getJson('/api/v1/saya')->assertUnauthorized();
    $this->denganKunci($kunciPonsel)->getJson('/api/v1/saya')->assertOk();
});

it('mendaftar hanya sesi milik sendiri yang masih berlaku', function () {
    [$ponsel] = ($this->terbit)($this->sri, 'Ponsel');
    [$lama] = ($this->terbit)($this->sri, 'Warnet');
    app(PenerbitSesi::class)->cabut($lama);
    ($this->terbit)(Pengguna::factory()->create(), 'Perangkat orang lain');

    $daftar = $this->sebagai($this->sri)->getJson('/api/v1/sesi')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->json('data');

    expect(collect($daftar)->pluck('sedang_dipakai', 'pengenal')->all())->toEqual([
        $this->sesiUji->pengenal => true,
        $ponsel->pengenal => false,
    ]);
});

it('mencabut perangkat lain hanya dari sesi segar (LP-15)', function () {
    [$ponsel, $kunciPonsel] = ($this->terbit)($this->sri, 'Ponsel');

    $this->sebagai($this->sri, segar: false)
        ->deleteJson('/api/v1/sesi/'.$ponsel->pengenal)
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'LP-15');
    $this->denganKunci($kunciPonsel)->getJson('/api/v1/saya')->assertOk();

    $this->sebagai($this->sri)->deleteJson('/api/v1/sesi/'.$ponsel->pengenal)->assertOk();
    $this->denganKunci($kunciPonsel)->getJson('/api/v1/saya')->assertUnauthorized();
});

it('menjawab 404 untuk sesi milik orang lain dan membiarkannya tetap berlaku', function () {
    $wulan = Pengguna::factory()->create();
    [$sesiWulan, $kunciWulan] = ($this->terbit)($wulan, 'Laptop Wulan');

    $this->sebagai($this->sri)->deleteJson('/api/v1/sesi/'.$sesiWulan->pengenal)->assertNotFound();
    $this->deleteJson('/api/v1/sesi/01JABCDEFGHJKMNPQRSTVWXYZ0')->assertNotFound();

    $this->denganKunci($kunciWulan)->getJson('/api/v1/saya')->assertOk();
    expect($sesiWulan->refresh()->dicabut_pada)->toBeNull();
});

it('menyegarkan sesi dan mengganti kuncinya setelah kata sandi dikonfirmasi', function () {
    [$sesi, $kunciLama] = ($this->terbit)($this->sri, 'Laptop');
    $sesi->forceFill(['dikonfirmasi_pada' => now()->subHour()])->save();

    $kunciBaru = $this->denganKunci($kunciLama)
        ->postJson('/api/v1/sesi/konfirmasi', ['kata_sandi' => 'BatikSolo2026'])
        ->assertOk()
        ->assertJsonPath('data.sesi.pengenal', $sesi->pengenal)
        ->assertJsonPath('data.sesi.segar', true)
        ->json('data.kunci');

    expect($kunciBaru)->not->toBe($kunciLama)->and(Sesi::count())->toBe(1);

    $this->denganKunci($kunciLama)->getJson('/api/v1/saya')->assertUnauthorized();
    $this->denganKunci($kunciBaru)->getJson('/api/v1/saya')->assertOk();
});

it('menolak konfirmasi dengan kata sandi salah dan membiarkan sesi tetap tidak segar', function () {
    $this->sebagai($this->sri, segar: false)
        ->postJson('/api/v1/sesi/konfirmasi', ['kata_sandi' => 'tebakan'])
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'KREDENSIAL_SALAH');

    expect($this->sesiUji->refresh()->segar())->toBeFalse();
    $this->getJson('/api/v1/saya')->assertOk();
});

it('membatasi tebakan kata sandi lewat pintu konfirmasi', function () {
    $this->sebagai($this->sri, segar: false);

    foreach (range(1, 5) as $ke) {
        $this->postJson('/api/v1/sesi/konfirmasi', ['kata_sandi' => "tebakan-{$ke}"])
            ->assertUnprocessable();
    }

    $this->postJson('/api/v1/sesi/konfirmasi', ['kata_sandi' => 'BatikSolo2026'])
        ->assertTooManyRequests();
});
