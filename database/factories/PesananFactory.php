<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Pesanan\StatusPesanan;
use App\Models\Checkout;
use App\Models\Pesanan;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesanan>
 */
final class PesananFactory extends Factory
{
    protected $model = Pesanan::class;

    public function definition(): array
    {
        return [
            'checkout_id' => Checkout::factory(),
            'pembeli_id' => fn (array $isi) => Checkout::query()
                ->findOrFail($isi['checkout_id'])
                ->pembeli_id,
            'toko_id' => Toko::factory(),
            'status' => StatusPesanan::MenungguPembayaran,
            'subtotal_rupiah' => 185_000,
            'ongkir_rupiah' => 12_000,
            'total_rupiah' => 197_000,
            'alamat_kirim' => [
                'nama_penerima' => 'Andi Nugroho',
                'telepon' => '081200000008',
                'jalan' => 'Jl. Slamet Riyadi No. 12',
                'kota' => 'Surakarta',
                'kode_pos' => '57111',
            ],
        ];
    }

    public function dibayar(): static
    {
        return $this->state(['status' => StatusPesanan::Dibayar, 'dibayar_pada' => now()]);
    }
}
