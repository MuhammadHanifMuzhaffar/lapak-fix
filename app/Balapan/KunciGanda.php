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

final class KunciGanda implements SkenarioBalapan
{
    use PanggilHttp;

    public function keterangan(): string
    {
        return 'Checkout yang sama diulang serentak dengan satu Idempotency-Key: efeknya satu kali';
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
            'stok' => 50,
            'harga_rupiah' => 100_000,
        ]);
        $alamat = Alamat::factory()->for($pembeli, 'pengguna')->create();

        [$sesi, $kunci] = app(PenerbitSesi::class)->terbitkan($pembeli, 'Uji Kunci Ganda');

        $this->panggil('PUT', "/api/v1/keranjang/butir/{$produk->id}", ['kuantitas' => 2], [
            'Authorization' => "Bearer {$kunci}",
        ]);

        return [
            'pembeli' => $pembeli->id,
            'produk' => $produk->id,
            'alamat' => $alamat->id,
            'kunci_sesi' => $kunci,
            'kunci_idempotensi' => (string) Str::uuid(),
        ];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        $badan = ['alamat_id' => $konteks['alamat']];

        [$status, $json, $header] = $this->panggil('POST', '/api/v1/checkout', $badan, [
            'Authorization' => 'Bearer '.$konteks['kunci_sesi'],
            'Idempotency-Key' => $konteks['kunci_idempotensi'],
        ]);

        if ($status !== 201) {
            return $this->ringkas($status, $json);
        }

        $jenis = isset($header['idempotency-replayed']) ? 'ulangan' : 'pertama';

        return "201 {$jenis} ".substr($json['data']['kode'], -6);
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $checkout = Checkout::query()->where('pembeli_id', $konteks['pembeli'])->count();
        $stok = Produk::query()->findOrFail($konteks['produk'])->stok;
        $pertama = count(array_filter(
            $hasil,
            fn (string $h) => str_starts_with($h, '201 pertama'),
        ));
        $kode = array_unique(array_map(fn (string $h) => substr($h, -6), $hasil));

        $pelanggaran = [];
        if ($checkout !== 1) {
            $pelanggaran[] = "Lahir {$checkout} checkout dari satu Idempotency-Key, seharusnya 1.";
        }
        if ($pertama !== 1) {
            $pelanggaran[] = "{$pertama} proses merasa menjadi yang pertama, seharusnya tepat 1.";
        }
        if (count($kode) !== 1) {
            $pelanggaran[] = 'Proses yang berbeda menerima jawaban yang berbeda.';
        }
        if ($stok !== 48) {
            $pelanggaran[] = 'Stok berkurang '.(50 - $stok).', padahal keranjang hanya berisi 2.';
        }

        return $pelanggaran;
    }
}
