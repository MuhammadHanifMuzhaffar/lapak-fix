<?php

namespace Database\Factories;

use App\Enums\StatusProduk;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Produk>
 */
class ProdukFactory extends Factory
{
    protected $model = Produk::class;

    public function definition(): array
    {
        $nama = ucfirst(fake()->unique()->words(3, true));

        return [
            'toko_id' => Toko::factory(),
            'kategori_id' => Kategori::factory(),
            'nama' => $nama,
            'slug' => Str::slug($nama),
            'harga_rupiah' => fake()->numberBetween(10, 600) * 500,
            'stok' => fake()->numberBetween(1, 50),
            'berat_gram' => fake()->numberBetween(1, 30) * 50,
            'status' => StatusProduk::Aktif,
        ];
    }

    public function draf(): static
    {
        return $this->state(['status' => StatusProduk::Draf]);
    }

    public function habis(): static
    {
        return $this->state(['stok' => 0]);
    }
}
