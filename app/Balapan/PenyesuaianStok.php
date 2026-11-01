<?php

declare(strict_types=1);

namespace App\Balapan;

use App\Exceptions\ProdukTidakDapatDibeli;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\PenjagaStok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PenyesuaianStok implements SkenarioBalapan
{
    use PanggilHttp;

    public const STOK_AWAL = 10;

    public const RESTOK = 3;

    public function __construct(private readonly PenjagaStok $penjaga) {}

    public function keterangan(): string
    {
        return 'Penjual menambah stok selagi pembeli berbelanja: tidak ada perubahan yang hilang';
    }

    public function proses(): int
    {
        return 12;
    }

    public function siapkan(): array
    {
        $akhiran = Str::lower(Str::random(8));
        $toko = Toko::factory()->create(['slug' => "toko-{$akhiran}"]);
        $produk = Produk::factory()->for($toko)->for(Kategori::query()->firstOrFail())->create([
            'slug' => "produk-{$akhiran}",
            'stok' => self::STOK_AWAL,
        ]);

        return ['produk' => $produk->id];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        $id = $konteks['produk'];

        if ($nomor % 2 === 0) {
            $this->penjaga->sesuaikan($id, self::RESTOK);

            return 'penjual: tambah '.self::RESTOK;
        }

        try {
            DB::transaction(function () use ($id): void {
                $this->penjaga->kurangi($this->penjaga->ambil([$id])[$id], 1);
            });
        } catch (ProdukTidakDapatDibeli) {
            return 'pembeli: ditolak';
        }

        return 'pembeli: beli 1';
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $terbeli = count(array_filter($hasil, fn (string $h) => $h === 'pembeli: beli 1'));
        $restok = count(array_filter($hasil, fn (string $h) => str_starts_with($h, 'penjual')));
        $seharusnya = self::STOK_AWAL - $terbeli + $restok * self::RESTOK;
        $nyata = Produk::query()->findOrFail($konteks['produk'])->stok;

        return $nyata === $seharusnya
            ? []
            : ["Stok akhir {$nyata}, seharusnya {$seharusnya}: ada perubahan yang tertimpa."];
    }
}
