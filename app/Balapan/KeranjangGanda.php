<?php

declare(strict_types=1);

namespace App\Balapan;

use App\Models\Alamat;
use App\Models\Checkout;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\PenerbitSesi;
use Illuminate\Support\Str;

final class KeranjangGanda implements SkenarioBalapan
{
    use PanggilHttp;

    public const STOK_AWAL = 50;

    public function keterangan(): string
    {
        return 'Satu keranjang di-checkout dari banyak tab, kunci berbeda: tepat satu checkout';
    }

    public function proses(): int
    {
        return 6;
    }

    public function siapkan(): array
    {
        $akhiran = Str::lower(Str::random(8));
        $pembeli = Pengguna::factory()->pembeli()->create();
        $toko = Toko::factory()->create(['slug' => "toko-{$akhiran}"]);
        $kategori = Kategori::query()->firstOrFail();
        $produk = Produk::factory()->for($toko)->for($kategori)->create([
            'slug' => "produk-{$akhiran}",
            'stok' => self::STOK_AWAL,
            'harga_rupiah' => 100_000,
        ]);
        $alamat = Alamat::factory()->for($pembeli, 'pengguna')->create();

        [$sesi, $kunci] = app(PenerbitSesi::class)->terbitkan($pembeli, 'Uji Keranjang Ganda');

        $this->panggil('PUT', "/api/v1/keranjang/butir/{$produk->id}", ['kuantitas' => 2], [
            'Authorization' => "Bearer {$kunci}",
        ]);

        return [
            'pembeli' => $pembeli->id,
            'produk' => $produk->id,
            'alamat' => $alamat->id,
            'kunci' => $kunci,
        ];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        $badan = ['alamat_id' => $konteks['alamat']];

        [$status, $json] = $this->panggil('POST', '/api/v1/checkout', $badan, [
            'Authorization' => 'Bearer '.$konteks['kunci'],
            'Idempotency-Key' => "tab-{$nomor}-".Str::random(16),
        ]);

        return $this->ringkas($status, $json);
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $checkout = Checkout::query()->where('pembeli_id', $konteks['pembeli'])->count();
        $stok = Produk::query()->findOrFail($konteks['produk'])->stok;

        $pelanggaran = [];
        if ($checkout !== 1) {
            $pelanggaran[] = "Lahir {$checkout} checkout dari satu keranjang, seharusnya 1.";
        }
        if ($stok !== self::STOK_AWAL - 2) {
            $terjual = self::STOK_AWAL - $stok;
            $pelanggaran[] = "Stok berkurang {$terjual}, padahal keranjang hanya berisi 2.";
        }

        return $pelanggaran;
    }
}
