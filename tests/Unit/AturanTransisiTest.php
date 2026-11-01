<?php

use App\Domain\Pesanan\AturanTransisi;
use App\Domain\Pesanan\PelakuPesanan;
use App\Domain\Pesanan\StatusPesanan;

/*
| Tabel di bawah ini ditulis ULANG dengan tangan, tidak dibaca dari AturanTransisi.
| Uji yang menyalin tabel dari kode yang diujinya hanya membuktikan bahwa kode sama dengan dirinya.
|
| Bentuk baris: [dari, ke, pelaku].
*/
const JALUR_SAH = [
    ['menunggu_pembayaran', 'dibayar', 'sistem'],
    ['menunggu_pembayaran', 'dibatalkan', 'pembeli'],
    ['menunggu_pembayaran', 'dibatalkan', 'sistem'],
    ['dibayar', 'dikemas', 'penjual'],
    ['dibayar', 'dibatalkan', 'pembeli'],
    ['dibayar', 'dibatalkan', 'penjual'],
    ['dikemas', 'dikirim', 'penjual'],
    ['dikemas', 'dibatalkan', 'pembeli'],
    ['dikemas', 'dibatalkan', 'penjual'],
    ['dikirim', 'selesai', 'pembeli'],
    ['dikirim', 'selesai', 'sistem'],
];

it('membolehkan persis jalur yang tertulis dan menolak seluruh sisanya', function () {
    $diperiksa = 0;
    $selisih = [];

    foreach (StatusPesanan::cases() as $dari) {
        foreach (StatusPesanan::cases() as $ke) {
            foreach (PelakuPesanan::cases() as $oleh) {
                $baris = [$dari->value, $ke->value, $oleh->value];
                $seharusnya = in_array($baris, JALUR_SAH, true);

                if (AturanTransisi::boleh($dari, $ke, $oleh) !== $seharusnya) {
                    $selisih[] = implode(' -> ', $baris);
                }
                $diperiksa++;
            }
        }
    }

    // 6 status asal x 6 status tujuan x 3 pelaku.
    expect($diperiksa)->toBe(108)
        ->and($selisih)->toBeEmpty('Tidak sesuai tabel: '.implode('; ', $selisih));
});

it('tidak memberi jalan keluar dari status akhir', function (StatusPesanan $akhir) {
    expect($akhir->akhir())->toBeTrue();

    foreach (PelakuPesanan::cases() as $oleh) {
        expect(AturanTransisi::tujuan($akhir, $oleh))->toBe([]);
    }
})->with([StatusPesanan::Selesai, StatusPesanan::Dibatalkan]);

it('tidak pernah membolehkan pembeli menyatakan pesanannya sudah dibayar', function () {
    $ke = AturanTransisi::tujuan(StatusPesanan::MenungguPembayaran, PelakuPesanan::Pembeli);

    expect($ke)->toBe([StatusPesanan::Dibatalkan]);
});

it('menutup pembatalan begitu barang dikirim (LP-10)', function (PelakuPesanan $oleh) {
    expect(AturanTransisi::boleh(StatusPesanan::Dikirim, StatusPesanan::Dibatalkan, $oleh))->toBeFalse();
})->with(PelakuPesanan::cases());

it('tidak mengenal perpindahan ke status yang sama maupun mundur', function () {
    $urutan = ['menunggu_pembayaran', 'dibayar', 'dikemas', 'dikirim', 'selesai'];

    foreach (JALUR_SAH as [$dari, $ke]) {
        if ($ke === 'dibatalkan') {
            continue;
        }

        expect(array_search($ke, $urutan, true))->toBe(array_search($dari, $urutan, true) + 1);
    }
});

it('menyebut tujuan yang tersedia bagi penjual pada pesanan yang sudah dibayar', function () {
    expect(AturanTransisi::tujuan(StatusPesanan::Dibayar, PelakuPesanan::Penjual))
        ->toBe([StatusPesanan::Dikemas, StatusPesanan::Dibatalkan]);
});
