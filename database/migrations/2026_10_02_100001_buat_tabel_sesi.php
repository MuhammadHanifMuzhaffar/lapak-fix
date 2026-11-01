<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->char('pengenal', 26)->unique();
            $tabel->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $tabel->char('hash_kunci', 64);
            $tabel->string('nama_perangkat', 60);
            $tabel->string('ip', 45)->nullable();
            $tabel->string('agen_pengguna', 255)->nullable();
            $tabel->timestamp('dikonfirmasi_pada')->nullable();
            $tabel->timestamp('terakhir_dipakai_pada');
            $tabel->timestamp('kedaluwarsa_pada');
            $tabel->timestamp('dicabut_pada')->nullable();
            $tabel->timestamps();

            $tabel->index(['pengguna_id', 'dicabut_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi');
    }
};
