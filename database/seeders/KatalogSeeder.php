<?php

namespace Database\Seeders;

use App\Enums\StatusProduk;
use App\Enums\StatusToko;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class KatalogSeeder extends Seeder
{
    private const KATEGORI = [
        'Batik dan Busana',
        'Oleh-Oleh Kuliner',
        'Kriya dan Dekorasi',
        'Minuman Tradisional',
    ];

    private const TOKO = [
        ['sri@lapak.test', 'Batik Laweyan Bu Sri', 'Surakarta', 12_000, StatusToko::Aktif, [
            ['Batik dan Busana', 'Kemeja Batik Tulis Motif Parang', 485_000, 8, 350, StatusProduk::Aktif],
            ['Batik dan Busana', 'Kain Batik Cap Sogan 2 Meter', 185_000, 25, 400, StatusProduk::Aktif],
            ['Batik dan Busana', 'Selendang Batik Sutra', 320_000, 0, 150, StatusProduk::Aktif],
            ['Batik dan Busana', 'Daster Batik Kencana', 95_000, 40, 250, StatusProduk::Draf],
        ]],
        ['wulan@lapak.test', 'Oleh-Oleh Mbak Wulan', 'Surakarta', 10_000, StatusToko::Aktif, [
            ['Oleh-Oleh Kuliner', 'Intip Goreng Gula Jawa 250 g', 32_500, 60, 300, StatusProduk::Aktif],
            ['Oleh-Oleh Kuliner', 'Abon Sapi Asli 200 g', 78_000, 35, 250, StatusProduk::Aktif],
            ['Oleh-Oleh Kuliner', 'Keripik Paru Renyah 150 g', 54_000, 18, 200, StatusProduk::Aktif],
            ['Oleh-Oleh Kuliner', 'Ampyang Kacang 300 g', 27_500, 45, 350, StatusProduk::Aktif],
        ]],
        ['eko@lapak.test', 'Rotan Trangsan Mas Eko', 'Sukoharjo', 25_000, StatusToko::Aktif, [
            ['Kriya dan Dekorasi', 'Keranjang Rotan Anyam Sedang', 145_000, 12, 900, StatusProduk::Aktif],
            ['Kriya dan Dekorasi', 'Kap Lampu Rotan Gantung', 210_000, 6, 700, StatusProduk::Aktif],
            ['Kriya dan Dekorasi', 'Tatakan Gelas Rotan Isi 6', 48_000, 30, 200, StatusProduk::Aktif],
        ]],
        ['tini@lapak.test', 'Wedang Uwuh Bu Tini', 'Karanganyar', 9_000, StatusToko::Menunggu, [
            ['Minuman Tradisional', 'Wedang Uwuh Celup Isi 10', 22_000, 80, 120, StatusProduk::Aktif],
            ['Minuman Tradisional', 'Jahe Merah Bubuk 250 g', 36_000, 50, 280, StatusProduk::Aktif],
        ]],
    ];

    public function run(): void
    {
        $kategori = [];
        foreach (self::KATEGORI as $nama) {
            $kategori[$nama] = Kategori::updateOrCreate(['slug' => Str::slug($nama)], ['nama' => $nama]);
        }

        foreach (self::TOKO as [$surel, $namaToko, $kota, $ongkir, $statusToko, $daftarProduk]) {
            $pemilik = Pengguna::where('surel', $surel)->first() ?? throw new RuntimeException("{$surel} belum ada. Jalankan PenggunaSeeder dulu.");

            $toko = Toko::firstOrNew(['slug' => Str::slug($namaToko)]);
            $toko->forceFill([
                'pemilik_id' => $pemilik->id,
                'nama' => $namaToko,
                'kota' => $kota,
                'ongkir_rupiah' => $ongkir,
                'status' => $statusToko,
            ])->save();

            foreach ($daftarProduk as [$namaKategori, $namaProduk, $harga, $stok, $berat, $status]) {
                $produk = Produk::firstOrNew(['slug' => Str::slug($namaProduk)]);
                $produk->forceFill([
                    'toko_id' => $toko->id,
                    'kategori_id' => $kategori[$namaKategori]->id,
                    'nama' => $namaProduk,
                    'harga_rupiah' => $harga,
                    'stok' => $stok,
                    'berat_gram' => $berat,
                    'status' => $status,
                ])->save();
            }
        }
    }
}
