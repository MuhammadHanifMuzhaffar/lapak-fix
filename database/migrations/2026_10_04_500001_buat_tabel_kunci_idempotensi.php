<?php 
  
use Illuminate\Database\Migrations\Migration; 
use Illuminate\Database\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema; 
  
return new class extends Migration 
{ 
    /** LP-11: ingatan atas permintaan yang berhasil, agar pengulangannya tidak berefek lagi. */ 
    public function up(): void 
    { 
        Schema::create('kunci_idempotensi', function (Blueprint $tabel) { 
            $tabel->id(); 
            $tabel->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete(); 
            $tabel->string('kunci', 64); 
            // "POST api/v1/checkout": kunci yang sama tidak boleh dipakai untuk pintu lain. 
            $tabel->string('rute', 120); 
            // SHA-256 atas rute dan badan permintaan. Badannya sendiri tidak disimpan. 
            $tabel->char('sidik', 64); 
            $tabel->unsignedSmallInteger('kode_status')->nullable(); 
            $tabel->longText('badan_respons')->nullable(); 
            $tabel->timestamp('created_at')->useCurrent(); 
  
            // Indeks inilah yang membuat dua permintaan kembar yang datang bersamaan antre. 
            $tabel->unique(['pengguna_id', 'kunci']); 
        }); 
    } 
  
    public function down(): void 
    { 
        Schema::dropIfExists('kunci_idempotensi'); 
    } 
};