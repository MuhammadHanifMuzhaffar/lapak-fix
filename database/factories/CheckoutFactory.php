<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Checkout;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Checkout>
 */
final class CheckoutFactory extends Factory
{
    protected $model = Checkout::class;

    public function definition(): array
    {
        return [
            'pembeli_id' => Pengguna::factory()->pembeli(),
            'kode' => (string) Str::ulid(),
            'pengenal_sesi' => (string) Str::ulid(),
            'total_rupiah' => 197_000,
        ];
    }
}
