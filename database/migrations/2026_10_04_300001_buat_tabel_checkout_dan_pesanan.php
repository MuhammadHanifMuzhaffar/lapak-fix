<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LP-7: satu checkout melahirkan satu pesanan per toko dan dibayar dengan satu pembayaran.
     * Baris checkout adalah induk keduanya, dan baris itulah yang dikunci setiap kali pesanan
     * atau pembayarannya berubah.
     */
    public function up(): void
    {
        Schema::create('checkout', function (Blueprint $tabel) {
            $tabel->id();
            // Yang disebut klien dan gerbang pembayaran. Nomor urut internal tidak pernah keluar.
            $tabel->string('kode', 32)->unique();
            $tabel->foreignId('pembeli_id')->constrained('pengguna')->restrictOnDelete();
            $tabel->unsignedBigInteger('total_rupiah');
            // Sesi yang membuat checkout ini. Sengaja bukan foreign key: baris sesi dipangkas
            // setelah 30 hari, sedangkan jejak ini harus tinggal selama pesanannya ada.
            $tabel->char('pengenal_sesi', 26);
            $tabel->timestamps();
        });

        Schema::create('pesanan', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('checkout_id')->constrained('checkout')->restrictOnDelete();
            // LP-9. Diisi sesaat setelah baris lahir, karena nomornya memuat id baris itu.
            $tabel->string('nomor', 24)->nullable()->unique();
            $tabel->foreignId('pembeli_id')->constrained('pengguna')->restrictOnDelete();
            $tabel->foreignId('toko_id')->constrained('toko')->restrictOnDelete();
            $tabel->string('status', 20)->index();
            $tabel->unsignedBigInteger('subtotal_rupiah');
            $tabel->unsignedBigInteger('ongkir_rupiah');
            $tabel->unsignedBigInteger('total_rupiah');
            // Salinan alamat pada saat checkout, bukan penunjuk ke tabel alamat.
            $tabel->json('alamat_kirim');
            $tabel->timestamp('dibayar_pada')->nullable();
            $tabel->timestamp('dibatalkan_pada')->nullable();
            $tabel->string('alasan_batal', 120)->nullable();
            $tabel->timestamps();

            $tabel->index(['pembeli_id', 'id']);
        });

        Schema::create('butir_pesanan', function (Blueprint $tabel) {
            $tabel->id();
            $tabel->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            $tabel->foreignId('produk_id')->constrained('produk')->restrictOnDelete();
            // LP-6: nama dan harga dibekukan. Penjual boleh mengubah produknya besok;

            // pesanan hari ini tetap mencatat apa yang dibeli dan berapa harganya.
            $tabel->string('nama_produk', 120);
            $tabel->unsignedBigInteger('harga_rupiah');
            $tabel->unsignedInteger('kuantitas');
            $tabel->unsignedBigInteger('subtotal_rupiah');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('butir_pesanan');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('checkout');
    }
};
