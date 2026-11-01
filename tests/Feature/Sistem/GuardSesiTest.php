<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PenerbitSesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['api', 'auth:sesi'])->get('/api/_uji/terlindung', fn (Request $request) => [
        'pengguna' => $request->user()->id,
        'sesi' => Sesi::dariPermintaan($request)->pengenal,
    ]);
    Route::middleware(['api', 'auth:sesi', 'sesi.segar'])
        ->post('/api/_uji/sensitif', fn () => ['ok' => true]);

    $this->pengguna = Pengguna::factory()->create();
});

it('menjawab 401 berbentuk kontrak bila kunci tidak ada', function () {
    $this->getJson('/api/_uji/terlindung')
        ->assertUnauthorized()
        ->assertExactJson(['sukses' => false, 'galat' => [
            'kode' => 'BELUM_MASUK',
            'pesan' => 'Anda belum masuk atau sesi tidak berlaku.',
            'rincian' => null,
        ]]);
});

it('menjawab 401 yang sama untuk setiap jenis kunci yang tidak berlaku', function (string $kunci) {
    $this->denganKunci($kunci)->getJson('/api/_uji/terlindung')
        ->assertUnauthorized()
        ->assertJsonPath('galat.kode', 'BELUM_MASUK');
})->with([
    'salah bentuk' => ['bukan-kunci'],
    'pengenal tak dikenal' => ['lpk_01JABCDEFGHJKMNPQRSTVWXYZ0.'.str_repeat('a', 64)],
    'token gaya lain' => ['eyJhbG...NiJ9.e30.abc'],
]);

it('mengenali pengguna dan sesinya dari kunci yang sah', function () {
    $this->sebagai($this->pengguna)->getJson('/api/_uji/terlindung')
        ->assertOk()
        ->assertExactJson(['pengguna' => $this->pengguna->id, 'sesi' => $this->sesiUji->pengenal]);
});

it('menolak kunci begitu sesinya dicabut', function () {
    $this->sebagai($this->pengguna)->getJson('/api/_uji/terlindung')->assertOk();

    app(PenerbitSesi::class)->cabut($this->sesiUji);

    $this->getJson('/api/_uji/terlindung')->assertUnauthorized();
});

it('menolak kunci begitu akunnya ditangguhkan', function () {
    $this->sebagai($this->pengguna)->getJson('/api/_uji/terlindung')->assertOk();

    $this->pengguna->forceFill(['status' => 'ditangguhkan'])->save();

    $this->getJson('/api/_uji/terlindung')->assertUnauthorized();
});

it('menolak kunci setelah dua jam menganggur', function () {
    $this->sebagai($this->pengguna)->getJson('/api/_uji/terlindung')->assertOk();

    $this->travel(121)->minutes();

    $this->getJson('/api/_uji/terlindung')->assertUnauthorized();
});

it('tidak tertukar antara dua pengguna dalam satu rangkaian permintaan', function () {
    $lain = Pengguna::factory()->create();

    $alamat = '/api/_uji/terlindung';

    $this->sebagai($this->pengguna)->getJson($alamat)->assertJsonPath('pengguna', $this->pengguna->id);
    $this->sebagai($lain)->getJson($alamat)->assertJsonPath('pengguna', $lain->id);
    $this->tanpaKunci()->getJson($alamat)->assertUnauthorized();
});

it('meloloskan tindakan sensitif hanya pada sesi segar (LP-15)', function () {
    $this->sebagai($this->pengguna)->postJson('/api/_uji/sensitif')->assertOk();

    $this->travel(11)->minutes();

    $this->postJson('/api/_uji/sensitif')
        ->assertForbidden()
        ->assertExactJson(['sukses' => false, 'galat' => [
            'kode' => 'LP-15',
            'pesan' => 'Tindakan ini menuntut konfirmasi ulang kata sandi.',
            'rincian' => ['konfirmasi' => 'POST /api/v1/sesi/konfirmasi'],
        ]]);
});
