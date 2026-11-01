<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StatusProduk;
use App\Http\Requests\ProdukSayaRequest;
use App\Http\Resources\ProdukSayaResource;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class ProdukSayaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $halaman = $this->tokoSaya($request)
            ->produk()
            ->orderByDesc('id')
            ->paginate(config('lapak.katalog.per_halaman_bawaan'), ['*'], 'halaman');

        return $this->berhalaman($halaman, ProdukSayaResource::class);
    }

    public function store(ProdukSayaRequest $request): JsonResponse
    {
        $toko = $this->tokoSaya($request);
        $data = $request->validated();

        $produk = new Produk(Arr::except($data, 'status'));
        $produk->toko_id = $toko->id;
        $produk->slug = Str::slug($data['nama']).'-'.Str::lower(Str::random(5));
        $produk->status = StatusProduk::from($data['status'] ?? StatusProduk::Draf->value);
        $produk->save();

        return $this->sukses(ProdukSayaResource::make($produk->refresh()), 201);
    }

    public function update(ProdukSayaRequest $request, int $id): JsonResponse
    {
        $produk = $this->produkSaya($request, $id);
        $data = $request->validated();

        $produk->fill(Arr::except($data, 'status'));
        if (isset($data['status'])) {
            $produk->status = StatusProduk::from($data['status']);
        }
        $produk->save();

        return $this->sukses(ProdukSayaResource::make($produk));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $produk = $this->produkSaya($request, $id);
        $produk->status = StatusProduk::Arsip;
        $produk->save();

        return $this->sukses(ProdukSayaResource::make($produk));
    }

    private function tokoSaya(Request $request): Toko
    {
        return Toko::query()->milik($request->user())->firstOrFail();
    }

    private function produkSaya(Request $request, int $id): Produk
    {
        return $this->tokoSaya($request)->produk()->findOrFail($id);
    }
}
