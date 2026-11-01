<?php

use App\Domain\Uang;

it('menolak nilai negatif', function () {
    Uang::dari(-1);
})->throws(InvalidArgumentException::class);

it('tidak berubah setelah operasi', function () {
    $harga = Uang::dari(185_000);
    $harga->tambah(Uang::dari(15_000));

    expect($harga->rupiah)->toBe(185_000);
});

it('menjumlah, mengurang, dan mengali dalam bilangan bulat', function () {
    $harga = Uang::dari(32_500);

    expect($harga->kali(3)->rupiah)->toBe(97_500)
        ->and($harga->tambah(Uang::dari(12_000))->rupiah)->toBe(44_500)
        ->and($harga->kurang(Uang::dari(2_500))->rupiah)->toBe(30_000);
});

it('menolak pengurangan yang menghasilkan nilai negatif', function () {
    Uang::dari(1_000)->kurang(Uang::dari(1_001));
})->throws(InvalidArgumentException::class);

it('membulatkan persentase setengah ke atas', function (int $rupiah, int $persen, int $hasil) {
    expect(Uang::dari($rupiah)->persen($persen)->rupiah)->toBe($hasil);
})->with([
    'tepat' => [200_000, 5, 10_000],
    'pecahan di bawah setengah' => [12_345, 5, 617],
    'pecahan tepat setengah' => [12_350, 5, 618],
    'pecahan di atas setengah' => [12_355, 5, 618],
    'nol persen' => [99_999, 0, 0],
]);

it('memformat rupiah dengan pemisah ribuan titik', function () {
    expect(Uang::dari(1_250_000)->format())->toBe('Rp 1.250.000')
        ->and(json_encode(['harga' => Uang::dari(850)]))->toBe('{"harga":850}');
});
