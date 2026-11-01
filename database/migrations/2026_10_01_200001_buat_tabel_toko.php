<?php

use App\Enums\StatusToko;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toko', function (Blueprint $tabel) {
            $tabel->id();
            // LP-3: unique() menjamin satu pengguna paling banyak satu toko.
            $tabel->foreignId('pemilik_id')->unique()->constrained('pengguna')->restrictOnDelete();
            $tabel->string('nama', 80);
            $tabel->string('slug', 100)->unique();
            $tabel->string('kota', 60);
            $tabel->text('deskripsi')->nullable();
            $tabel->unsignedBigInteger('ongkir_rupiah')->default(0);
            $tabel->string('status', 20)->default(StatusToko::Menunggu->value)->index();
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toko');
    }
};
