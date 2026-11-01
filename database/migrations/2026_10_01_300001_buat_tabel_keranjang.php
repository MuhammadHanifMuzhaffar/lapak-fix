<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keranjang', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('pengguna_id')->unique()->constrained('pengguna')->cascadeOnDelete();
            $tabel->timestamps();
        });

        Schema::create('butir_keranjang', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('keranjang_id')->constrained('keranjang')->cascadeOnDelete();
            $tabel->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            $tabel->unsignedInteger('kuantitas');
            $tabel->timestamps();

            $tabel->unique(['keranjang_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('butir_keranjang');
        Schema::dropIfExists('keranjang');
    }
};
