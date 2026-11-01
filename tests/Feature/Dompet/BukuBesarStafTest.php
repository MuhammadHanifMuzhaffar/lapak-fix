<?php

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Models\Pengguna;
use App\Services\BukuBesar;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->sri = Pengguna::factory()->penjual()->create(['nama' => 'Sri Wahyuni']);
    $this->keuangan = Pengguna::factory()->keuangan()->create();

    $buku = app(BukuBesar::class);
    $luar = $buku->dompetSistem(JenisDompet::Eksternal);
    $escrow = $buku->dompetSistem(JenisDompet::Escrow);
    $this->dompetSri = $buku->dompetPenjual($this->sri);

    $buku->pindahkan($luar, $escrow, Uang::dari(200_000), JenisMutasi::DanaMasuk, 'LPK-UJI-3001');
    $buku->pindahkan(
        $escrow, $this->dompetSri, Uang::dari(150_000), JenisMutasi::LepasDana, 'LPK-UJI-3001',
    );
});

it('membuka buku besar bagi keuangan dan auditor, dua peran dengan satu izin', function (
    string $pemegang,
) {
    $this->sebagai(Pengguna::factory()->{$pemegang}()->create())
        ->getJson('/api/v1/buku-besar/dompet')
        ->assertOk()
        ->assertJsonPath('meta.jumlah_dompet', 3);
})->with(['keuangan', 'auditor']);

it('menutup buku besar bagi admin, penjual, dan pembeli', function (string $pemegang) {
    $this->sebagai(Pengguna::factory()->{$pemegang}()->create());

    $this->getJson('/api/v1/buku-besar/dompet')
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'DITOLAK');
    $this->getJson("/api/v1/buku-besar/dompet/{$this->dompetSri->id}/mutasi")->assertForbidden();
})->with(['admin', 'penjual', 'pembeli']);

it('tidak membuka dompet orang lain bagi penjual, ada maupun tidak ada nomornya', function () {
    $this->sebagai(Pengguna::factory()->penjual()->create());

    $this->getJson("/api/v1/buku-besar/dompet/{$this->dompetSri->id}/mutasi")->assertForbidden();
    $this->getJson('/api/v1/buku-besar/dompet/999999/mutasi')->assertForbidden();
});

it('melaporkan buku yang tidak seimbang apa adanya', function () {
    DB::table('dompet')->where('id', $this->dompetSri->id)->update(['saldo_rupiah' => 999_999]);

    $this->sebagai($this->keuangan)->getJson('/api/v1/buku-besar/dompet')
        ->assertOk()
        ->assertJsonPath('meta.seimbang', false);
});
