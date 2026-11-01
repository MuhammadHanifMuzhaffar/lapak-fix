<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan pemanggilan mengikuti arah foreign key:
     * tabel yang dirujuk harus terisi lebih dulu.
     */
    public function run(): void
    {
        $this->call([
            //
        ]);
    }
}
