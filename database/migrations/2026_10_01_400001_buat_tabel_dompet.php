<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dompet', function (Blueprint $tabel) {
            $tabel->id();
            // 'eksternal', 'escrow', 'pendapatan', atau 'penjual:<id pengguna>'.
            $tabel->string('kode', 40)->unique();
            $tabel->string('jenis', 20)->index();
            $tabel->foreignId('pengguna_id')->nullable()->constrained('pengguna')->restrictOnDelete();
            // Salinan cepat dari jumlah seluruh mutasi. Bertanda: dompet eksternal boleh negatif.
            $tabel->bigInteger('saldo_rupiah')->default(0);
            $tabel->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dompet');
    }
};
