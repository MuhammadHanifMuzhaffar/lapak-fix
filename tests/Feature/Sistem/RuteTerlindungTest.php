<?php

use Illuminate\Support\Facades\Route;

const RUTE_PUBLIK = [
    'GET api/v1/health',
    'POST api/v1/registrasi',
    'POST api/v1/masuk',
    'GET api/v1/produk',
    'GET api/v1/produk/{slug}',
    'GET api/v1/toko/{slug}',
    'POST api/v1/simulasi-belanja',
];

function ruteApi(): array
{
    $daftar = [];

    foreach (Route::getRoutes() as $rute) {
        if (! str_starts_with($rute->uri(), 'api/v1')) {
            continue;
        }

        foreach (array_diff($rute->methods(), ['HEAD']) as $metode) {
            $daftar[] = ["{$metode} {$rute->uri()}", $rute];
        }
    }

    return $daftar;
}

it('memasang auth:sesi pada setiap rute yang tidak terdaftar publik', function () {
    $terbuka = [];

    foreach (ruteApi() as [$nama, $rute]) {
        $terlindung = in_array('auth:sesi', $rute->gatherMiddleware(), true);

        if (! $terlindung && ! in_array($nama, RUTE_PUBLIK, true)) {
            $terbuka[] = $nama;
        }
    }

    expect($terbuka)->toBeEmpty('Rute tanpa auth:sesi: '.implode(', ', $terbuka));
});

it('tidak menyimpan rute usang pada daftar publik', function () {
    $ada = array_column(ruteApi(), 0);

    $usang = array_values(array_diff(RUTE_PUBLIK, $ada));

    expect($usang)->toBeEmpty('Rute publik yang sudah tidak ada: '.implode(', ', $usang));
});

it('benar-benar menjawab 401 pada setiap rute terlindung bila kunci tidak dikirim', function () {
    $lolos = [];

    foreach (ruteApi() as [$nama, $rute]) {
        if (in_array($nama, RUTE_PUBLIK, true)) {
            continue;
        }

        [$metode, $alamat] = explode(' ', $nama);
        $alamat = '/'.preg_replace('/\{[^}]+\}/', '1', $alamat);

        if ($this->json($metode, $alamat)->status() !== 401) {
            $lolos[] = $nama;
        }
    }

    expect($lolos)->toBeEmpty('Tidak menjawab 401 tanpa kunci: '.implode(', ', $lolos));
});
