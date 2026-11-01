<?php

use App\Enums\NamaIzin;
use Illuminate\Support\Facades\Route;

function rutePerIzin(): array
{
    $peta = [];

    foreach (Route::getRoutes() as $rute) {
        foreach ($rute->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'izin:')) {
                $peta[substr($middleware, 5)][] = implode('|', $rute->methods()).' '.$rute->uri();
            }
        }
    }

    return $peta;
}

it('tidak menyimpan izin yang menganggur', function () {
    $terpakai = array_keys(rutePerIzin());
    $menganggur = array_filter(
        NamaIzin::cases(),
        fn (NamaIzin $izin) => ! in_array($izin->value, $terpakai, true),
    );

    $nama = implode(', ', array_map(fn (NamaIzin $izin) => $izin->value, $menganggur));

    expect($menganggur)->toBeEmpty("Izin tanpa rute: {$nama}");
});

it('hanya menyebut izin yang dikenal pada rute', function () {
    $dikenal = array_map(fn (NamaIzin $izin) => $izin->value, NamaIzin::cases());
    $asing = array_diff(array_keys(rutePerIzin()), $dikenal);

    expect($asing)->toBeEmpty('Rute menyebut izin tak dikenal: '.implode(', ', $asing));
});

it('memasang izin hanya pada rute yang sudah berada di belakang kunci sesi', function () {
    $tanpaKunci = [];

    foreach (Route::getRoutes() as $rute) {
        $middleware = array_filter($rute->gatherMiddleware(), 'is_string');
        $berizin = array_filter($middleware, fn (string $m) => str_starts_with($m, 'izin:')) !== [];

        if ($berizin && ! in_array('auth:sesi', $middleware, true)) {
            $tanpaKunci[] = $rute->uri();
        }
    }

    expect($tanpaKunci)->toBeEmpty('Rute berizin tanpa auth:sesi: '.implode(', ', $tanpaKunci));
});
