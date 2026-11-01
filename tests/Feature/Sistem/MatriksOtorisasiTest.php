<?php

use App\Enums\NamaPeran;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

function matriksOtorisasi(): array
{
    $matriks = [];

    foreach (glob(__DIR__.'/matriks/*.php') as $berkas) {
        $matriks += require $berkas;
    }

    return $matriks;
}

function ruteTerlindung(): array
{
    $daftar = [];

    foreach (Route::getRoutes() as $rute) {
        $terlindung = in_array('auth:sesi', $rute->gatherMiddleware(), true);

        if (! str_starts_with($rute->uri(), 'api/v1') || ! $terlindung) {
            continue;
        }

        foreach (array_diff($rute->methods(), ['HEAD']) as $metode) {
            $daftar[] = "{$metode} {$rute->uri()}";
        }
    }

    return $daftar;
}

it('memiliki tepat satu baris matriks untuk setiap rute terlindung', function () {
    $rute = ruteTerlindung();
    $baris = array_keys(matriksOtorisasi());

    $tanpaBaris = array_values(array_diff($rute, $baris));
    $barisUsang = array_values(array_diff($baris, $rute));

    expect($tanpaBaris)->toBeEmpty('Rute tanpa baris matriks: '.implode(', ', $tanpaBaris))
        ->and($barisUsang)->toBeEmpty('Baris matriks tanpa rute: '.implode(', ', $barisUsang));
});

it('hanya menyebut peran yang dikenal', function (string $peran) {
    expect(NamaPeran::tryFrom($peran))->not->toBeNull();
})->with(function () {
    $semua = [];
    foreach (matriksOtorisasi() as $boleh) {
        if ($boleh !== '*') {
            $semua = array_merge($semua, $boleh);
        }
    }

    return array_unique($semua);
});

it('menolak dan meloloskan setiap peran persis seperti matriks', function (
    string $rute,
    array|string $boleh,
    ?NamaPeran $peran,
) {
    [$metode, $alamat] = explode(' ', $rute);
    $alamat = '/'.preg_replace('/\{[^}]+\}/', '999999', $alamat);

    $pengguna = Pengguna::factory()->create();
    if ($peran !== null) {
        DB::table('pengguna_peran')->insert([
            'pengguna_id' => $pengguna->id,
            'peran_id' => DB::table('peran')->where('nama', $peran->value)->value('id'),
        ]);
    }

    $respons = $this->sebagai($pengguna)->json($metode, $alamat);

    $ditolak = $respons->status() === 403 && $respons->json('galat.kode') === 'DITOLAK';
    $harusBoleh = $boleh === '*' || ($peran !== null && in_array($peran->value, $boleh, true));

    expect($ditolak)->toBe(! $harusBoleh);
})->with(function () {
    $pemegang = [null, ...NamaPeran::cases()];
    $kombinasi = [];

    foreach (matriksOtorisasi() as $rute => $boleh) {
        foreach ($pemegang as $peran) {
            $kombinasi["{$rute} sebagai ".($peran->value ?? 'tanpa peran')] = [$rute, $boleh, $peran];
        }
    }

    return $kombinasi;
});
