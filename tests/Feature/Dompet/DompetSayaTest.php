<?php

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Models\Dompet;
use App\Models\MutasiSaldo;
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
        ->assertExactJson(['sukses' => true, 'meta' => null, 'data' => [
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

it('membuka saldo tanpa sesi segar tetapi menuntutnya untuk riwayat mutasi (LP-15)', function () {
    $sri = Pengguna::factory()->create();
    lepasDanaKe($sri, 363_500, 'LPK-UJI-1003');

    $this->sebagai($sri, segar: false)->getJson('/api/v1/dompet')->assertOk();
    $this->getJson('/api/v1/dompet/mutasi')
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'LP-15');
});

it('membuka riwayat setelah kata sandi dikonfirmasi, dengan kunci yang baru', function () {
    $sri = Pengguna::factory()->create(['kata_sandi' => 'BatikSolo2026']);
    lepasDanaKe($sri, 363_500, 'LPK-UJI-1004');

    $kunciBaru = $this->sebagai($sri, segar: false)
        ->postJson('/api/v1/sesi/konfirmasi', ['kata_sandi' => 'BatikSolo2026'])
        ->assertOk()
        ->json('data.kunci');

    $this->getJson('/api/v1/dompet/mutasi')->assertUnauthorized();

    $this->denganKunci($kunciBaru)->getJson('/api/v1/dompet/mutasi')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.jenis', 'lepas_dana')
        ->assertJsonPath('data.0.jumlah.tampilan', '+Rp 363.500')
        ->assertJsonPath('data.0.saldo_setelah', 363_500)
        ->assertJsonMissingPath('data.0.kelompok');
});

it('menampilkan hanya mutasi dompet sendiri, terbaru lebih dulu', function () {
    $sri = Pengguna::factory()->create();
    $wulan = Pengguna::factory()->create();
    lepasDanaKe($sri, 100_000, 'LPK-UJI-1005');
    lepasDanaKe($wulan, 55_000, 'LPK-UJI-1006');
    lepasDanaKe($sri, 250_000, 'LPK-UJI-1007');

    $this->sebagai($sri)->getJson('/api/v1/dompet/mutasi')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0.rujukan', 'LPK-UJI-1007')
        ->assertJsonPath('data.0.saldo_setelah', 350_000)
        ->assertJsonPath('data.1.rujukan', 'LPK-UJI-1005');

    $this->sebagai(Pengguna::factory()->create())->getJson('/api/v1/dompet/mutasi')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);
});

it('menjalankan seeder contoh berulang kali tanpa menggandakan mutasi', function () {
    $this->seed();
    $this->seed();

    expect(MutasiSaldo::where('rujukan', 'LPK-CONTOH-0001')->count())->toBe(6);
    $this->artisan('lapak:periksa-buku')->assertSuccessful();

    $sri = Pengguna::where('surel', 'sri@lapak.test')->sole();
    $this->sebagai($sri)->getJson('/api/v1/dompet')->assertJsonPath('data.saldo.rupiah', 363_500);
});
