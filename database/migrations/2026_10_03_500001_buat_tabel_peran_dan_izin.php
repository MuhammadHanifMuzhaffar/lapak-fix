<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peran', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->string('nama', 30)->unique();
            $tabel->string('keterangan', 200);
            $tabel->timestamps();
        });

        Schema::create('izin', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->string('nama', 50)->unique();
            $tabel->string('keterangan', 200);
            $tabel->timestamps();
        });

        Schema::create('peran_izin', function (Blueprint $tabel) {
            $tabel->foreignId('peran_id')->constrained('peran')->cascadeOnDelete();
            $tabel->foreignId('izin_id')->constrained('izin')->cascadeOnDelete();
            $tabel->primary(['peran_id', 'izin_id']);
        });

        Schema::create('pengguna_peran', function (Blueprint $tabel) {
            $tabel->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $tabel->foreignId('peran_id')->constrained('peran')->cascadeOnDelete();
            $tabel->foreignId('diberikan_oleh')->nullable()->constrained('pengguna')->nullOnDelete();
            $tabel->timestamp('created_at')->useCurrent();
            $tabel->primary(['pengguna_id', 'peran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna_peran');
        Schema::dropIfExists('peran_izin');
        Schema::dropIfExists('izin');
        Schema::dropIfExists('peran');
    }
};
