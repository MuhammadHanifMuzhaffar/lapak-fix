<?php

use App\Domain\Pesanan\PelakuPesanan;
use App\Domain\Pesanan\StatusPesanan;
use App\Exceptions\TransisiPesananTidakSah;
use App\Models\Checkout;
use App\Models\Pesanan;
use App\Services\GembokCheckout;
use App\Services\MesinStatusPesanan;

beforeEach(function () {
    $this->gembok = app(GembokCheckout::class);
    $this->mesin = app(MesinStatusPesanan::class);
});

it('membatalkan seluruh pekerjaan di dalam gembok bila salah satunya gagal', function () {
    $checkout = Checkout::factory()->create();
    [$a, $b] = Pesanan::factory()->count(2)->for($checkout)->create();

    try {
        $this->gembok->dalam($checkout->id, function () use ($a, $b) {
            $this->mesin->pindahkan($a, StatusPesanan::Dibayar, PelakuPesanan::Sistem);
            $this->mesin->pindahkan($b, StatusPesanan::Dibayar, PelakuPesanan::Pembeli);
        });
    } catch (TransisiPesananTidakSah) {
    }

    expect($a->refresh()->status)->toBe(StatusPesanan::MenungguPembayaran)
        ->and($b->refresh()->status)->toBe(StatusPesanan::MenungguPembayaran);
});

it('menandai pesanan dibayar atas nama sistem dan mencatat waktunya', function () {
    $pesanan = Pesanan::factory()->create();

    $hasil = $this->gembok->dalam($pesanan->checkout_id, fn () => $this->mesin->pindahkan(
        $pesanan, StatusPesanan::Dibayar, PelakuPesanan::Sistem,
    ));

    expect($hasil->status)->toBe(StatusPesanan::Dibayar)
        ->and($pesanan->refresh()->dibayar_pada)->not->toBeNull();
});

it('membatalkan pesanan beserta alasan dan waktunya', function () {
    $pesanan = Pesanan::factory()->create();

    $this->gembok->dalam($pesanan->checkout_id, fn () => $this->mesin->pindahkan(
        $pesanan, StatusPesanan::Dibatalkan, PelakuPesanan::Sistem, 'Tidak dibayar dalam 24 jam',
    ));

    expect($pesanan->refresh())
        ->status->toBe(StatusPesanan::Dibatalkan)
        ->alasan_batal->toBe('Tidak dibayar dalam 24 jam')
        ->dibatalkan_pada->not->toBeNull();
});

it('menolak perpindahan yang tidak ada jalurnya dengan 409 (LP-10)', function (
    StatusPesanan $dari,
    StatusPesanan $ke,
    PelakuPesanan $oleh,
) {
    $pesanan = Pesanan::factory()->create(['status' => $dari]);

    try {
        $this->gembok->dalam(
            $pesanan->checkout_id,
            fn () => $this->mesin->pindahkan($pesanan, $ke, $oleh),
        );
        $this->fail('Seharusnya ditolak.');
    } catch (TransisiPesananTidakSah $galat) {
        expect($galat->kode)->toBe('LP-10')->and($galat->statusHttp)->toBe(409);
    }

    expect($pesanan->refresh()->status)->toBe($dari);
})->with([
    'pembeli mengaku sudah membayar' => [
        StatusPesanan::MenungguPembayaran, StatusPesanan::Dibayar, PelakuPesanan::Pembeli,
    ],
    'dibayar dua kali' => [StatusPesanan::Dibayar, StatusPesanan::Dibayar, PelakuPesanan::Sistem],
    'hidup lagi setelah batal' => [
        StatusPesanan::Dibatalkan, StatusPesanan::Dibayar, PelakuPesanan::Sistem,
    ],
    'batal setelah dikirim' => [
        StatusPesanan::Dikirim, StatusPesanan::Dibatalkan, PelakuPesanan::Pembeli,
    ],
]);

it('memutuskan dari status terkini di basis data, bukan dari salinan pemanggil', function () {
    $pesanan = Pesanan::factory()->create();
    $salinanBasi = Pesanan::query()->findOrFail($pesanan->id);

    $this->gembok->dalam($pesanan->checkout_id, fn () => $this->mesin->pindahkan(
        $pesanan, StatusPesanan::Dibatalkan, PelakuPesanan::Sistem, 'Kedaluwarsa',
    ));

    expect($salinanBasi->status)->toBe(StatusPesanan::MenungguPembayaran)
        ->and(fn () => $this->gembok->dalam($pesanan->checkout_id, fn () => $this->mesin->pindahkan(
            $salinanBasi, StatusPesanan::Dibayar, PelakuPesanan::Sistem,
        )))->toThrow(TransisiPesananTidakSah::class);
});
