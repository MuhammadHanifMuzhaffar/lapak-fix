<?php

declare(strict_types=1);

namespace App\Balapan;

use App\Domain\Pesanan\StatusPesanan;
use App\Enums\JenisDompet;
use App\Enums\StatusPembayaran;
use App\Models\Checkout;
use App\Models\MutasiSaldo;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\BukuBesar;
use App\Services\TandaTanganGateway;

final class WebhookGanda implements SkenarioBalapan
{
    use PanggilHttp;

    public const TAGIHAN = 489_500;

    public function keterangan(): string
    {
        return 'Kabar pembayaran yang sama tiba serentak: dana masuk escrow tepat satu kali';
    }

    public function proses(): int
    {
        return 6;
    }

    public function siapkan(): array
    {
        $checkout = Checkout::factory()->create();
        $pembayaran = Pembayaran::factory()->for($checkout)->create([
            'jumlah_rupiah' => self::TAGIHAN,
            'status' => StatusPembayaran::Menunggu,
        ]);
        Pesanan::factory()->count(2)->for($checkout)->create([
            'total_rupiah' => 244_750,
            'status' => StatusPesanan::MenungguPembayaran,
        ]);

        $badan = json_encode([
            'id_peristiwa' => 'evt_uji_balapan',
            'kode_pembayaran' => $pembayaran->kode,
            'jumlah_rupiah' => self::TAGIHAN,
        ], JSON_THROW_ON_ERROR);

        $tanda = app(TandaTanganGateway::class)->buat($badan);
        $escrowAwal = app(BukuBesar::class)->dompetSistem(JenisDompet::Escrow)->saldo_rupiah;

        return [
            'checkout' => $checkout->id,
            'badan' => $badan,
            'tanda' => $tanda,
            'escrow_awal' => $escrowAwal,
        ];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        [$status, $json] = $this->panggil(
            'POST',
            '/api/v1/webhook/pembayaran',
            json_decode($konteks['badan'], true),
            [TandaTanganGateway::HEADER => $konteks['tanda']],
        );

        return $status === 200 ? "200 {$json['data']['hasil']}" : $this->ringkas($status, $json);
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $checkout = Checkout::query()->findOrFail($konteks['checkout']);
        $escrow = app(BukuBesar::class)->dompetSistem(JenisDompet::Escrow)->saldo_rupiah;
        $masuk = $escrow - $konteks['escrow_awal'];
        $nomor = $checkout->pesanan()->pluck('nomor');
        $mutasi = MutasiSaldo::query()->whereIn('rujukan', $nomor)->count();
        $melunasi = count(array_filter($hasil, fn (string $h) => $h === '200 dilunasi'));

        $pelanggaran = [];
        if ($melunasi !== 1) {
            $pelanggaran[] = "{$melunasi} kabar dianggap melunasi, seharusnya tepat 1.";
        }
        if ($masuk !== self::TAGIHAN) {
            $pelanggaran[] = "Escrow bertambah {$masuk}, padahal tagihannya ".self::TAGIHAN.'.';
        }
        if ($mutasi !== 4) {
            $pelanggaran[] = "Tercatat {$mutasi} baris mutasi untuk dua pesanan, seharusnya 4.";
        }
        if ($checkout->pesanan()->where('status', StatusPesanan::Dibayar)->count() !== 2) {
            $pelanggaran[] = 'Tidak semua pesanan berstatus dibayar.';
        }
        $tagihan = Pembayaran::query()->where('checkout_id', $checkout->id)->sole();
        if ($tagihan->status !== StatusPembayaran::Lunas) {
            $pelanggaran[] = 'Tagihan tidak berstatus lunas.';
        }

        return $pelanggaran;
    }
}
