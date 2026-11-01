<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Belanja\PenghitungBelanja;
use App\Domain\Belanja\RincianToko;
use App\Domain\Pesanan\StatusPesanan;
use App\Exceptions\GalatDomain;
use App\Models\Alamat;
use App\Models\ButirPesanan;
use App\Models\Checkout;
use App\Models\Keranjang;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Sesi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Mengubah isi keranjang menjadi pesanan. Seluruh langkahnya berada dalam SATU transaksi:
 * stok berkurang, pesanan lahir, tagihan terbit, dan keranjang kosong terjadi bersama,
 * atau tidak satu pun terjadi.
 *
 * Urutan kunci: keranjang, lalu produk urut nomor. Urutan ini tidak boleh dibalik.
 */
final class LayananCheckout
{
    public function __construct(
        private readonly PenjagaStok $stok,
        private readonly PenyusunBelanja $penyusun,
        private readonly PenjagaTransaksi $penjaga,
        private readonly LayananPembayaran $pembayaran,
    ) {}

    public function proses(Pengguna $pembeli, Alamat $alamat, Sesi $sesi): Checkout
    {
        return DB::transaction(function () use ($pembeli, $alamat, $sesi): Checkout {
            // 1. Kunci keranjang. Dua checkout atas keranjang yang sama antre di baris ini;
            //    yang kedua mendapati keranjangnya sudah kosong.
            $keranjang = Keranjang::query()
                ->where('pengguna_id', $pembeli->id)
                ->lockForUpdate()
                ->first();

            // Isinya dibaca SETELAH kunci didapat, dan dengan kunci pula.
            $butir = $keranjang?->butir()->orderBy('id')->lockForUpdate()->get() ?? collect();

            if ($butir->isEmpty()) {
                throw new GalatDomain('KERANJANG_KOSONG', 'Keranjang Anda kosong.', 422);
            }

            // 2. Ambil produk lewat PenjagaStok, lalu hitung dari baris yang diambilnya.
            //    Klien tidak mengirim harga, kuantitas, maupun total (LP-6).
            $produk = $this->stok->ambil($butir->pluck('produk_id')->all());
            $diminta = $butir
                ->map(fn ($b) => ['produk_id' => $b->produk_id, 'kuantitas' => $b->kuantitas])
                ->all();

            // Satu butir bermasalah menggagalkan seluruh checkout (LP-4, LP-5).
            $rincian = $this->penyusun->susun($diminta, $pembeli, $produk);
            $total = PenghitungBelanja::totalBayar($rincian);

            // 3. Nilai besar menuntut sesi segar (LP-15). Gagal di sini membatalkan semuanya.
            $this->penjaga->pastikanLayak($sesi, $total);

            // 4. Tulis: checkout, satu pesanan per toko (LP-7), butir berharga beku, stok.
            $checkout = new Checkout;
            $checkout->forceFill([
                'kode' => (string) Str::ulid(),
                'pembeli_id' => $pembeli->id,
                'total_rupiah' => $total->rupiah,
                'pengenal_sesi' => $sesi->pengenal,
            ])->save();

            foreach ($rincian as $rincianToko) {
                $this->buatPesanan($checkout, $rincianToko, $alamat, $produk);
            }

            // 5. Satu tagihan untuk seluruh pesanan, lalu keranjang dikosongkan.
            $this->pembayaran->buat($checkout);
            $keranjang->butir()->delete();

            return $checkout;
        });
    }

    /**
     * @param  Collection<int, Produk>  $produk  hasil PenjagaStok::ambil()
     */
    private function buatPesanan(
        Checkout $checkout,
        RincianToko $rincian,
        Alamat $alamat,
        Collection $produk,
    ): void {
        $pesanan = new Pesanan;
        $pesanan->forceFill([
            'checkout_id' => $checkout->id,
            'pembeli_id' => $checkout->pembeli_id,
            'toko_id' => $rincian->tokoId,
            'status' => StatusPesanan::MenungguPembayaran,
            'subtotal_rupiah' => $rincian->subtotal->rupiah,
            'ongkir_rupiah' => $rincian->ongkir->rupiah,
            'total_rupiah' => $rincian->total()->rupiah,
            // Salinan, bukan penunjuk: lihat Alamat::potret().
            'alamat_kirim' => $alamat->potret(),
        ])->save();

        foreach ($rincian->baris as $baris) {
            $this->stok->kurangi($produk[$baris->produkId], $baris->kuantitas);

            $butir = new ButirPesanan;
            $butir->forceFill([

                'pesanan_id' => $pesanan->id,
                'produk_id' => $baris->produkId,
                // LP-6: nama dan harga dibekukan pada saat ini.
                'nama_produk' => $baris->namaProduk,
                'harga_rupiah' => $baris->hargaSatuan->rupiah,
                'kuantitas' => $baris->kuantitas,
                'subtotal_rupiah' => $baris->subtotal()->rupiah,
            ])->save();
        }
    }
}
