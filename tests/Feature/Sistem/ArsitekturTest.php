<?php

arch('tidak ada alat debug yang tertinggal')
    ->expect(['dd', 'dump', 'var_dump', 'ray'])
    ->not->toBeUsed();

arch('lapisan domain adalah PHP murni')
    ->expect('App\Domain')
    ->not->toUse(['Illuminate\Http', 'Illuminate\Database', 'Illuminate\Support\Facades']);

it('tidak memakai tipe pecahan untuk kolom apa pun (LP-1)', function () {
    $pelanggar = [];
    foreach (glob(database_path('migrations/*.php')) as $berkas) {
        if (preg_match('/->(float|double|decimal)\(/', file_get_contents($berkas))) {
            $pelanggar[] = basename($berkas);
        }
    }

    expect($pelanggar)->toBeEmpty();
});
