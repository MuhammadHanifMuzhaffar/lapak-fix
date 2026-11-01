<?php

use App\Enums\StatusAkun;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->string('nama', 100);
            $tabel->string('surel', 150)->unique();
            $tabel->string('telepon', 20)->nullable();
            $tabel->string('kata_sandi');
            $tabel->string('status', 20)->default(StatusAkun::Aktif->value)->index();
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
