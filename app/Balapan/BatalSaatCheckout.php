<?php

declare(strict_types=1);

namespace App\Balapan;

use App\Models\Alamat;
use App\Models\Checkout;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\PembatalanOtomatis;
use App\Services\PenerbitSesi;
use Illuminate\Support\Str;

final class BatalSaatCheckout implements SkenarioBalapan
{
    use PanggilHttp;

    public const STOK_AWAL = 50;

    public function keterangan(): string
    {
        return 'Pembatalan otomatis bertemu pembelian produk yang sama: tidak ada kebuntuan';
    }

    public function proses(): int
    {
        return 12;
    }

    public function siapkan(): array
    {
        $akhiran = Str::lower(Str::random(8));
        $toko = Toko::factory()->create(['slug' => "toko-{$akhiran}"]);
        $kategori = Kategori::query()->firstOrFail();

        [$kecil, $besar] = Produk::factory()->count(2)->for($toko)->for($kategori)
            ->sequence(['slug' => "produk-a-{$akhiran}"], ['slug' => "produk-b-{$akhiran}"])
            ->create(['stok' => self::STOK_AWAL])
            ->all();

        $checkout = [];
        foreach (range(1, 6) as $i) {
            $pembeli = Pengguna::factory()->pembeli()->create();
            $alamat = Alamat::factory()->for($pembeli, 'pengguna')->create();
            [$sesi, $kunci] = app(PenerbitSesi::class)->terbitkan($pembeli, "Sesi Batal {$i}");

            $this->panggil('PUT', "/api/v1/keranjang/butir/{$besar->id}", ['kuantitas' => 1], [
                'Authorization' => "Bearer {$kunci}",
            ]);
            $this->panggil('PUT', "/api/v1/keranjang/butir/{$kecil->id}", ['kuantitas' => 1], [
                'Authorization' => "Bearer {$kunci}",
            ]);

            [$status, $json] = $this->panggil('POST', '/api/v1/checkout', ['alamat_id' => $alamat->id], [
                'Authorization' => "Bearer {$kunci}",
                'Idempotency-Key' => (string) Str::uuid(),
            ]);

            $idCheckout = $json['data']['id'];
            \Illuminate\Support\Facades\DB::table('pembayaran')
                ->where('checkout_id', $idCheckout)
                ->update(['batas_bayar_pada' => now()->subMinutes(5)]);

            $checkout[] = $idCheckout;
        }

        $pembeliAktif = [];
        foreach (range(1, 6) as $i) {
            $pembeli = Pengguna::factory()->pembeli()->create();
            $alamat = Alamat::factory()->for($pembeli, 'pengguna')->create();
            [$sesi, $kunci] = app(PenerbitSesi::class)->terbitkan($pembeli, "Sesi Beli {$i}");

            $this->panggil('PUT', "/api/v1/keranjang/butir/{$kecil->id}", ['kuantitas' => 1], [
                'Authorization' => "Bearer {$kunci}",
            ]);
            $this->panggil('PUT', "/api/v1/keranjang/butir/{$besar->id}", ['kuantitas' => 1], [
                'Authorization' => "Bearer {$kunci}",
            ]);

            $pembeliAktif[] = [
                'kunci_sesi' => $kunci,
                'alamat_id' => $alamat->id,
            ];
        }

        return [
            'checkout_batal' => $checkout,
            'pembeli_aktif' => $pembeliAktif,
            'produk_kecil' => $kecil->id,
            'produk_besar' => $besar->id,
        ];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        if ($nomor % 2 === 0) {
            $idx = ($nomor / 2) - 1;
            $id = $konteks['checkout_batal'][$idx];
            $checkout = Checkout::find($id);
            if ($checkout && app(PembatalanOtomatis::class)->batalkan($checkout)) {
                return 'penjadwal: batal';
            }
            return 'penjadwal: abaikan';
        }

        $idx = intdiv($nomor, 2);
        $data = $konteks['pembeli_aktif'][$idx];
        [$status, $json] = $this->panggil('POST', '/api/v1/checkout', ['alamat_id' => $data['alamat_id']], [
            'Authorization' => "Bearer {$data['kunci_sesi']}",
            'Idempotency-Key' => (string) Str::uuid(),
        ]);

        if ($status === 201) {
            return 'pembeli: beli 2';
        }
        return $this->ringkas($status, $json);
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $pelanggaran = [];
        foreach ($hasil as $h) {
            if (str_contains($h, '1213 Deadlock') || str_contains($h, 'kebuntuan')) {
                $pelanggaran[] = 'Terjadi deadlock (kebuntuan) antara pembatalan otomatis dan checkout.';
            }
        }
        return $pelanggaran;
    }
}
