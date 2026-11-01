<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alamat', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $tabel->string('label', 30);
            $tabel->string('nama_penerima', 80);
            $tabel->string('telepon', 20);
            $tabel->string('jalan', 200);
            $tabel->string('kota', 60);
            $tabel->string('kode_pos', 5);
            // Tepat satu alamat utama per pengguna. Basis data tidak dapat menjaminnya dengan
            // indeks yang berlaku di MySQL maupun SQLite, maka BukuAlamat yang menjaganya.
            $tabel->boolean('utama')->default(false);
            $tabel->timestamps();

            $tabel->index(['pengguna_id', 'utama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat');
    }
};
