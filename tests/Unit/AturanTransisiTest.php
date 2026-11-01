<?php

use App\Domain\Pesanan\AturanTransisi;
use App\Domain\Pesanan\PelakuPesanan;
use App\Domain\Pesanan\StatusPesanan;

it('hanya membolehkan sistem mengubah status ke dibayar', function (PelakuPesanan $oleh, bool $boleh) {
    expect(AturanTransisi::boleh(StatusPesanan::MenungguPembayaran, StatusPesanan::Dibayar, $oleh))
        ->toBe($boleh);
})->with([
    'pembeli tidak boleh' => [PelakuPesanan::Pembeli, false],
    'penjual tidak boleh' => [PelakuPesanan::Penjual, false],
    'sistem boleh' => [PelakuPesanan::Sistem, true],
]);

it('menutup pembatalan begitu barang dikirim (LP-10)', function (PelakuPesanan $oleh) {
    expect(AturanTransisi::boleh(StatusPesanan::Dikirim, StatusPesanan::Dibatalkan, $oleh))->toBeFalse();
})->with([PelakuPesanan::Pembeli, PelakuPesanan::Penjual, PelakuPesanan::Sistem]);
