<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $tabel) {
            $tabel->id();
            // LP-7: unique() menjamin satu checkout dibayar dengan tepat satu pembayaran.
            $tabel->foreignId('checkout_id')->unique()->constrained('checkout')->restrictOnDelete();
            // Yang disebut pembeli dan gerbang pembayaran, misalnya BYR-01K7...
            $tabel->string('kode', 30)->unique();
            $tabel->unsignedBigInteger('jumlah_rupiah');
            $tabel->string('status', 20)->index();
            $tabel->timestamp('batas_bayar_pada');
            $tabel->timestamp('lunas_pada')->nullable();
            // Pengenal peristiwa dari gerbang yang melunasi tagihan ini. Unik: satu peristiwa
            // tidak dapat melunasi dua tagihan.
            $tabel->string('id_peristiwa', 64)->nullable()->unique();
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
