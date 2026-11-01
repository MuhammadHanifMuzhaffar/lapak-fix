<?php

namespace Database\Seeders;

use App\Enums\StatusProduk;
use App\Enums\StatusToko;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\ModerasiToko;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KatalogSeeder extends Seeder
{
    public function run(ModerasiToko $moderasi): void
    {
        $kategoriMap = [
            'Batik & Pakaian Tradisional' => 'batik-dan-pakaian-tradisional',
            'Kuliner & Oleh-Oleh Solo' => 'kuliner-dan-oleh-oleh-solo',
            'Kerajinan Tangan & Wayang' => 'kerajinan-tangan-dan-wayang',
            'Herbal & Jamu Tradisional' => 'herbal-dan-jamu-tradisional',
            'Seni, Musik & Budaya Solo' => 'seni-musik-dan-budaya-solo',
        ];

        $kategori = [];
        foreach ($kategoriMap as $nama => $slug) {
            $kategori[$slug] = Kategori::firstOrCreate(
                ['slug' => $slug],
                ['nama' => $nama, 'deskripsi' => "Kategori produk UMKM: {$nama}"],
            );
        }

        $sri = Pengguna::where('surel', 'sri@lapak.test')->first();
        $admin = Pengguna::where('surel', 'admin@lapak.test')->first();

        if ($sri && ! Toko::where('pemilik_id', $sri->id)->exists()) {
            $tokoSri = new Toko([
                'nama' => 'Batik Sekar Jagad',
                'kota' => 'Surakarta',
                'deskripsi' => 'Pengrajin batik tulis dan cap khas Solo Laweyan.',
                'ongkir_rupiah' => 12000,
            ]);
            $tokoSri->pemilik_id = $sri->id;
            $tokoSri->slug = 'batik-sekar-jagad-solo';
            $tokoSri->status = StatusToko::Menunggu;
            $tokoSri->save();

            if ($admin) {
                $moderasi->setujui($tokoSri, $admin);
            }

            Produk::create([
                'toko_id' => $tokoSri->id,
                'kategori_id' => $kategori['batik-dan-pakaian-tradisional']->id,
                'nama' => 'Kain Batik Tulis Motif Parang Rusak',
                'slug' => 'kain-batik-tulis-motif-parang-rusak-'.Str::random(5),
                'deskripsi' => 'Kain katun primissima kualitas premium, pewarna alami sogan alam.',
                'harga_rupiah' => 350000,
                'stok' => 15,
                'berat_gram' => 300,
                'status' => StatusProduk::Aktif,
            ]);

            Produk::create([
                'toko_id' => $tokoSri->id,
                'kategori_id' => $kategori['batik-dan-pakaian-tradisional']->id,
                'nama' => 'Kemeja Batik Pria Motif Kawung',
                'slug' => 'kemeja-batik-pria-motif-kawung-'.Str::random(5),
                'deskripsi' => 'Kemeja furing trikot nyaman dan elegan.',
                'harga_rupiah' => 185000,
                'stok' => 25,
                'berat_gram' => 250,
                'status' => StatusProduk::Aktif,
            ]);
        }
    }
}
