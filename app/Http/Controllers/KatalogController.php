<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StatusProduk;
use App\Enums\StatusToko;
use App\Http\Requests\KatalogRequest;
use App\Http\Resources\ProdukResource;
use App\Http\Resources\TokoResource;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

final class KatalogController extends Controller
{
    public function index(KatalogRequest $request): JsonResponse
    {
        $saringan = $request->validated();

        $halaman = Produk::query()
            ->tampil()
            ->saring($saringan)
            ->urut($saringan['urut'] ?? null)
            ->with(['toko', 'kategori'])
            ->paginate($request->perHalaman(), ['*'], 'halaman');

        return $this->berhalaman($halaman, ProdukResource::class);
    }

    public function show(string $slug): JsonResponse
    {
        $produk = Produk::query()
            ->tampil()
            ->with(['toko', 'kategori'])
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->sukses(ProdukResource::make($produk));
    }

    public function toko(string $slug): JsonResponse
    {
        $toko = Toko::query()
            ->where('status', StatusToko::Aktif)
            ->where('slug', $slug)
            ->withCount(['produk' => fn (Builder $q) => $q->where('status', StatusProduk::Aktif)])
            ->firstOrFail();

        return $this->sukses(TokoResource::make($toko));
    }
}
