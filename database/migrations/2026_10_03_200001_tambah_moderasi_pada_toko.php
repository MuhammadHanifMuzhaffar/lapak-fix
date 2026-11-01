<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toko', function (Blueprint $tabel) {
            $tabel->foreignId('dimoderasi_oleh')->nullable()->after('status')->constrained('pengguna')->nullOnDelete();
            $tabel->timestamp('dimoderasi_pada')->nullable()->after('dimoderasi_oleh');
            $tabel->string('catatan_moderasi', 500)->nullable()->after('dimoderasi_pada');
        });
    }

    public function down(): void
    {
        Schema::table('toko', function (Blueprint $tabel) {
            $tabel->dropForeign(['dimoderasi_oleh']);
            $tabel->dropColumn(['dimoderasi_oleh', 'dimoderasi_pada', 'catatan_moderasi']);
        });
    }
};
