<?php

use App\Domain\Sesi\KunciSesi;

const ULID_UJI = '01JABCDEFGHJKMNPQRSTVWXYZ0';

it('berbentuk awalan, pengenal, titik, lalu 64 karakter heksadesimal', function () {
    expect(KunciSesi::baru(ULID_UJI)->teks())->toMatch('/^lpk_'.ULID_UJI.'\.[0-9a-f]{64}$/');
});

it('tidak pernah menghasilkan rahasia yang sama dua kali', function () {
    $kunci = array_map(fn () => KunciSesi::baru(ULID_UJI)->teks(), range(1, 50));

    expect(array_unique($kunci))->toHaveCount(50);
});

it('dapat diurai kembali dan cocok dengan hash-nya sendiri', function () {
    $asli = KunciSesi::baru(ULID_UJI);
    $terurai = KunciSesi::urai($asli->teks());

    expect($terurai->pengenal)->toBe(ULID_UJI)
        ->and($terurai->hash())->toBe($asli->hash())
        ->and($terurai->cocok($asli->hash()))->toBeTrue();
});

it('menyimpan hash, bukan rahasia', function () {
    $kunci = KunciSesi::baru(ULID_UJI);
    $rahasia = explode('.', $kunci->teks())[1];

    expect($kunci->hash())->toBe(hash('sha256', $rahasia))
        ->and($kunci->hash())->not->toContain($rahasia);
});

it('tidak cocok dengan hash kunci lain', function () {
    expect(KunciSesi::baru(ULID_UJI)->cocok(KunciSesi::baru(ULID_UJI)->hash()))->toBeFalse();
});

it('menolak bentuk yang tidak persis sesuai pola', function (string $teks) {
    expect(KunciSesi::urai($teks))->toBeNull();
})->with([
    'kosong' => [''],
    'tanpa awalan' => [ULID_UJI.'.'.str_repeat('a', 64)],
    'awalan lain' => ['sk_'.ULID_UJI.'.'.str_repeat('a', 64)],
    'rahasia terlalu pendek' => ['lpk_'.ULID_UJI.'.'.str_repeat('a', 63)],
    'rahasia huruf besar' => ['lpk_'.ULID_UJI.'.'.str_repeat('A', 64)],
    'pengenal huruf kecil' => ['lpk_'.strtolower(ULID_UJI).'.'.str_repeat('a', 64)],
    'ada spasi di ujung' => ['lpk_'.ULID_UJI.'.'.str_repeat('a', 64).' '],
    'ada baris baru di ujung' => ['lpk_'.ULID_UJI.'.'.str_repeat('a', 64)."\n"],
    'injeksi' => ["lpk_' OR 1=1 --"],
]);
