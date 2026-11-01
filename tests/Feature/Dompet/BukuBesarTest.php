<?php

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Exceptions\GalatDomain;
use App\Exceptions\SaldoTidakCukup;
use App\Models\Dompet;
use App\Models\MutasiSaldo;
use App\Models\Pengguna;
use App\Services\BukuBesar;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->buku = app(BukuBesar::class);
    $this->eksternal = $this->buku->dompetSistem(JenisDompet::Eksternal);
    $this->escrow = $this->buku->dompetSistem(JenisDompet::Escrow);
    $this->pendapatan = $this->buku->dompetSistem(JenisDompet::Pendapatan);

    $this->bayar = fn (int $rupiah, string $rujukan) => $this->buku->pindahkan(
        $this->eksternal,
        $this->escrow,
        Uang::dari($rupiah),
        JenisMutasi::DanaMasuk,
        $rujukan,
    );
});

it('mencatat satu pemindahan sebagai dua mutasi yang berjumlah nol', function () {
    $kelompok = ($this->bayar)(382_000, 'LPK-UJI-0001');

    $mutasi = MutasiSaldo::where('kelompok', $kelompok)->orderBy('id')->get();

    expect($mutasi)->toHaveCount(2)
        ->and($mutasi->sum('jumlah_rupiah'))->toBe(0)
        ->and($mutasi[0]->jumlah_rupiah)->toBe(-382_000)
        ->and($mutasi[1]->saldo_setelah)->toBe(382_000)
        ->and($this->escrow->refresh()->saldo_rupiah)->toBe(382_000)
        ->and($this->eksternal->refresh()->saldo_rupiah)->toBe(-382_000);
});

it('menjalankan alur escrow lengkap tanpa kehilangan satu rupiah pun', function () {
    $penjual = $this->buku->dompetPenjual(Pengguna::factory()->create());

    $rujukan = 'LPK-UJI-0002';
    ($this->bayar)(382_000, $rujukan);
    $this->buku->pindahkan(
        $this->escrow,
        $this->pendapatan,
        Uang::dari(18_500),
        JenisMutasi::Komisi,
        $rujukan,
    );
    $this->buku->pindahkan(
        $this->escrow,
        $penjual,
        Uang::dari(363_500),
        JenisMutasi::LepasDana,
        $rujukan,
    );

    expect($this->escrow->refresh()->saldo_rupiah)->toBe(0)
        ->and($this->pendapatan->refresh()->saldo_rupiah)->toBe(18_500)
        ->and($penjual->refresh()->saldo_rupiah)->toBe(363_500)
        ->and((int) MutasiSaldo::sum('jumlah_rupiah'))->toBe(0);

    $this->artisan('lapak:periksa-buku')->assertSuccessful();
});

it('menolak pemindahan melebihi saldo dan tidak meninggalkan mutasi separuh jalan', function () {
    ($this->bayar)(50_000, 'LPK-UJI-0003');

    expect(fn () => $this->buku->pindahkan(
        $this->escrow,
        $this->pendapatan,
        Uang::dari(50_001),
        JenisMutasi::Komisi,
        'LPK-UJI-0003',
    ))->toThrow(SaldoTidakCukup::class);

    expect(MutasiSaldo::count())->toBe(2)
        ->and($this->escrow->refresh()->saldo_rupiah)->toBe(50_000)
        ->and($this->pendapatan->refresh()->saldo_rupiah)->toBe(0);
});

it('menolak pemindahan nol rupiah dan pemindahan ke dompet yang sama', function () {
    $pindah = fn ($dari, $ke, Uang $jumlah) => fn () => $this->buku->pindahkan(
        $dari,
        $ke,
        $jumlah,
        JenisMutasi::DanaMasuk,
        'LPK-UJI-X',
    );

    expect($pindah($this->eksternal, $this->escrow, Uang::nol()))->toThrow(GalatDomain::class)
        ->and($pindah($this->escrow, $this->escrow, Uang::dari(1)))->toThrow(GalatDomain::class);
});

it('melarang mutasi diubah atau dihapus (LP-13)', function () {
    ($this->bayar)(10_000, 'LPK-UJI-0004');
    $mutasi = MutasiSaldo::first();

    expect(fn () => $mutasi->forceFill(['jumlah_rupiah' => 99])->save())->toThrow(LogicException::class)
        ->and(fn () => $mutasi->delete())->toThrow(LogicException::class);
});

it('memberi setiap penjual tepat satu dompet', function () {
    $penjual = Pengguna::factory()->create();

    $pertama = $this->buku->dompetPenjual($penjual);
    $kedua = $this->buku->dompetPenjual($penjual);

    expect($kedua->id)->toBe($pertama->id)
        ->and(Dompet::where('jenis', JenisDompet::Penjual)->count())->toBe(1);
});

it('mendeteksi saldo yang diubah di luar BukuBesar', function () {
    ($this->bayar)(75_000, 'LPK-UJI-0005');

    DB::table('dompet')->where('kode', 'escrow')->update(['saldo_rupiah' => 7_500_000]);

    $this->artisan('lapak:periksa-buku')
        ->expectsOutputToContain('Saldo escrow tercatat 7500000, jumlah mutasinya 75000.')
        ->assertFailed();
});
