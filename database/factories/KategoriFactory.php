<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kategori>
 */
class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    public function definition(): array
    {
        $nama = ucfirst(fake()->unique()->words(2, true));

        return ['nama' => $nama, 'slug' => Str::slug($nama)];
    }
}
