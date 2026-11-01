<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PenerbitSesi;

it('memangkas sesi yang sudah lama mati dan membiarkan sisanya', function () {
    $penerbit = app(PenerbitSesi::class);
    $pengguna = Pengguna::factory()->create();

    [$dicabutLama] = $penerbit->terbitkan($pengguna, 'Dicabut lama');
    $penerbit->cabut($dicabutLama);
    [$kedaluwarsaLama] = $penerbit->terbitkan($pengguna, 'Kedaluwarsa lama');

    $this->travel(40)->days();

    [$dicabutBaru] = $penerbit->terbitkan($pengguna, 'Dicabut kemarin');
    $penerbit->cabut($dicabutBaru);
    [$berlaku] = $penerbit->terbitkan($pengguna, 'Masih berlaku');

    $this->artisan('lapak:pangkas-sesi')
        ->expectsOutput('2 sesi lama dipangkas.')
        ->assertSuccessful();

    expect(Sesi::pluck('pengenal')->all())
        ->toEqualCanonicalizing([$dicabutBaru->pengenal, $berlaku->pengenal]);
});
