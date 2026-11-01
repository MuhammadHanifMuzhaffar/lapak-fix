<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ButirPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ButirPesanan>
 */
final class ButirPesananFactory extends Factory
{
    protected $model = ButirPesanan::class;

    public function definition(): array
    {
        return [
            'pesanan_id' => Pesanan::factory(),
            'produk_id' => Produk::factory(),
            'nama_produk' => 'Kain Batik Tulis Solo',
            'harga_rupiah' => 100_000,
            'kuantitas' => 1,
            'subtotal_rupiah' => 100_000,
        ];
    }
}
