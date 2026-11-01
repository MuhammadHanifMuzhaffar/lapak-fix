<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PembayaranResource;
use App\Http\Resources\PesananResource;
use App\Models\Pesanan;
use App\Services\LayananPembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Pesanan milik pembeli yang sedang masuk. Seluruh kuerinya berangkat dari scopeMilikPembeli. */
final class PesananSayaController extends Controller
{
    /** GET /pesanan: terbaru lebih dulu. */
    public function index(Request $request): JsonResponse
    {
        $halaman = Pesanan::query()
            ->milikPembeli($request->user())
            ->with(['toko', 'butir'])
            ->orderByDesc('id')
            ->paginate(config('lapak.katalog.per_halaman_bawaan'), ['*'], 'halaman');

        return $this->berhalaman($halaman, PesananResource::class);
    }

    /**
     * GET /pesanan/{nomor}. Nomor pesanan orang lain dijawab 404, sama seperti nomor yang tidak

     * pernah ada. Nomor pesanan berurutan dan mudah ditebak; yang menjaganya adalah kueri ini.
     */
    public function show(
        Request $request,
        LayananPembayaran $pembayaran,
        string $nomor,
    ): JsonResponse {
        $pesanan = Pesanan::query()
            ->milikPembeli($request->user())
            ->with(['toko', 'butir', 'checkout'])
            ->where('nomor', $nomor)
            ->firstOrFail();

        return $this->sukses([
            'pesanan' => PesananResource::make($pesanan),
            'pembayaran' => PembayaranResource::make($pembayaran->untuk($pesanan->checkout)),
        ]);
    }
}
