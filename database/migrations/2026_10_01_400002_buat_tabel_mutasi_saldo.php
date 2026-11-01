<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_saldo', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('dompet_id')->constrained('dompet')->restrictOnDelete();
            // Dua baris hasil satu pemindahan berbagi kelompok yang sama dan berjumlah nol.
            $tabel->uuid('kelompok')->index();
            $tabel->string('jenis', 20);
            $tabel->bigInteger('jumlah_rupiah'); // negatif = keluar, positif = masuk
            $tabel->bigInteger('saldo_setelah');
            $tabel->string('rujukan', 60)->index(); // mis. nomor pesanan
            $tabel->string('keterangan', 160)->nullable();
            // LP-13: hanya created_at. Baris mutasi tidak pernah diubah, jadi updated_at tidak ada.
            $tabel->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldo');
    }
};
