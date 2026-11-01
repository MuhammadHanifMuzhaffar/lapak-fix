<?php

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Models\Dompet;
use App\Models\Pengguna;
use App\Services\BukuBesar;

function lepasDanaKe(Pengguna $penjual, int $rupiah, string $rujukan): void
{
    $buku = app(BukuBesar::class);
    $luar = $buku->dompetSistem(JenisDompet::Eksternal);
    $escrow = $buku->dompetSistem(JenisDompet::Escrow);
    $jumlah = Uang::dari($rupiah);

    $buku->pindahkan($luar, $escrow, $jumlah, JenisMutasi::DanaMasuk, $rujukan);
    $buku->pindahkan($escrow, $buku->dompetPenjual($penjual), $jumlah, JenisMutasi::LepasDana, $rujukan);
}

it('menolak permintaan tanpa kunci sesi', function () {
    $this->getJson('/api/v1/dompet')->assertUnauthorized();
});

it('menampilkan saldo nol tanpa membuat dompet bagi pengguna tanpa transaksi', function () {
    $this->sebagai(Pengguna::factory()->create())->getJson('/api/v1/dompet')
        ->assertOk()
        ->assertExactJson(['sukses' => true, 'pesan' => 'Operasi berhasil', 'data' => [
            'saldo' => ['rupiah' => 0, 'tampilan' => 'Rp 0'],
            'jumlah_mutasi' => 0,
        ]]);

    expect(Dompet::count())->toBe(0);
});

it('menampilkan saldo milik sendiri dan bukan milik orang lain', function () {
    $sri = Pengguna::factory()->create();
    $wulan = Pengguna::factory()->create();
    lepasDanaKe($sri, 363_500, 'LPK-UJI-1001');
    lepasDanaKe($wulan, 97_500, 'LPK-UJI-1002');

    $this->sebagai($sri)->getJson('/api/v1/dompet')
        ->assertJsonPath('data.saldo.rupiah', 363_500)
        ->assertJsonPath('data.saldo.tampilan', 'Rp 363.500')
        ->assertJsonPath('data.jumlah_mutasi', 1);

    $this->sebagai($wulan)->getJson('/api/v1/dompet')
        ->assertJsonPath('data.saldo.rupiah', 97_500);
});
