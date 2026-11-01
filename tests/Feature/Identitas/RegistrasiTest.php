<?php

use App\Enums\StatusAkun;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

function dataRegistrasi(array $timpa = []): array
{
    return array_merge([
        'nama' => 'Dewi Anggraini',
        'surel' => 'dewi@contoh.test',
        'telepon' => '081234567890',
        'kata_sandi' => 'BatikSolo2026',
        'konfirmasi_kata_sandi' => 'BatikSolo2026',
    ], $timpa);
}

it('mendaftarkan pengguna dan menyimpan kata sandi sebagai hash', function () {
    $this->postJson('/api/v1/registrasi', dataRegistrasi())
        ->assertCreated()
        ->assertJsonPath('data.surel', 'dewi@contoh.test')
        ->assertJsonPath('data.status', 'aktif');

    $pengguna = Pengguna::sole();
    expect($pengguna->kata_sandi)->not->toBe('BatikSolo2026')
        ->and(Hash::check('BatikSolo2026', $pengguna->kata_sandi))->toBeTrue();
});

it('tidak pernah mengirim kata sandi atau hash-nya ke klien', function () {
    $respons = $this->postJson('/api/v1/registrasi', dataRegistrasi())->assertCreated();

    expect($respons->json('data'))->not->toHaveKey('kata_sandi')
        ->and($respons->getContent())->not->toContain('BatikSolo2026', '$2y$');
});

it('mengabaikan kolom siluman yang diselipkan klien', function () {
    $this->postJson('/api/v1/registrasi', dataRegistrasi(['id' => 999, 'status' => 'ditangguhkan']))
        ->assertCreated();

    expect(Pengguna::sole())
        ->id->not->toBe(999)
        ->status->toBe(StatusAkun::Aktif);
});

it('menolak surel yang sudah terdaftar tanpa membedakan huruf besar kecil', function () {
    Pengguna::factory()->create(['surel' => 'dewi@contoh.test']);

    $this->postJson('/api/v1/registrasi', dataRegistrasi(['surel' => ' Dewi@Contoh.TEST ']))
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'VALIDASI')
        ->assertJsonPath('galat.rincian.surel.0', 'Surel sudah terdaftar.');
});

it('menolak kata sandi yang lemah', function (string $sandi) {
    $data = dataRegistrasi(['kata_sandi' => $sandi, 'konfirmasi_kata_sandi' => $sandi]);

    $this->postJson('/api/v1/registrasi', $data)
        ->assertUnprocessable()
        ->assertJsonStructure(['galat' => ['rincian' => ['kata_sandi']]]);
})->with([
    'terlalu pendek' => ['Batik2026'],
    'tanpa angka' => ['BatikSoloRaya'],
    'tanpa huruf' => ['1234567890123'],
]);

it('menolak konfirmasi kata sandi yang tidak cocok', function () {
    $this->postJson('/api/v1/registrasi', dataRegistrasi(['konfirmasi_kata_sandi' => 'BatikSolo2027']))
        ->assertUnprocessable()
        ->assertJsonPath('galat.rincian.konfirmasi_kata_sandi.0', 'Konfirmasi kata sandi tidak cocok.');
});
