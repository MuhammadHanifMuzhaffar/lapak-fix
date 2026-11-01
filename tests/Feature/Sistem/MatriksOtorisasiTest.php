<?php

use App\Enums\NamaPeran;
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

it('hanya menyebut peran yang dikenal', function () {
    $dikenal = array_map(fn (NamaPeran $peran) => $peran->value, NamaPeran::cases());

    foreach (matriksOtorisasi() as $alamat => $peran) {
        if ($peran === '*') {
            continue;
        }

        $asing = array_diff($peran, $dikenal);
        expect($asing)->toBeEmpty("Baris {$alamat} menyebut peran tak dikenal: ".implode(', ', $asing));
    }
});
