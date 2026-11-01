<?php

namespace Database\Factories;

use App\Enums\StatusToko;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Toko>
 */
class TokoFactory extends Factory
{
    protected $model = Toko::class;

    public function definition(): array
    {
        $nama = 'Toko '.fake()->unique()->lastName();

        return [
            'pemilik_id' => Pengguna::factory(),
            'nama' => $nama,
            'slug' => Str::slug($nama),
            'kota' => fake()->randomElement(['Surakarta', 'Sukoharjo', 'Karanganyar', 'Boyolali']),
            'ongkir_rupiah' => fake()->randomElement([8_000, 10_000, 12_000, 15_000]),
            'status' => StatusToko::Aktif,
        ];
    }

    public function menunggu(): static
    {
        return $this->state(['status' => StatusToko::Menunggu]);
    }
}
