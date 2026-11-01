<?php

namespace Database\Factories;

use App\Enums\StatusAkun;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    protected $model = Pengguna::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'surel' => fake()->unique()->safeEmail(),
            'telepon' => '08'.fake()->numerify('##########'),
            'kata_sandi' => 'KataSandi#2026',
            'status' => StatusAkun::Aktif,
        ];
    }

    public function ditangguhkan(): static
    {
        return $this->state(['status' => StatusAkun::Ditangguhkan]);
    }
}
