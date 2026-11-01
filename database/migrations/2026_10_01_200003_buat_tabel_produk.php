<?php

use App\Enums\StatusProduk;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('toko_id')->constrained('toko')->restrictOnDelete();
            $tabel->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $tabel->string('nama', 120);
            $tabel->string('slug', 140)->unique();
            $tabel->text('deskripsi')->nullable();
            // LP-1: bilangan bulat rupiah. LP-4: unsigned menolak stok negatif di MySQL.
            $tabel->unsignedBigInteger('harga_rupiah');
            $tabel->unsignedInteger('stok')->default(0);
            $tabel->unsignedInteger('berat_gram')->default(0);
            $tabel->string('status', 20)->default(StatusProduk::Draf->value);
            $tabel->timestamps();

            $tabel->index(['status', 'kategori_id']);
            $tabel->index('harga_rupiah');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
