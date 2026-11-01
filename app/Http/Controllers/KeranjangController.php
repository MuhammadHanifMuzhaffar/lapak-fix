<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ButirKeranjangRequest;
use App\Http\Resources\KeranjangResource;
use App\Services\LayananKeranjang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class KeranjangController extends Controller
{
    public function __construct(private readonly LayananKeranjang $keranjang) {}

    public function show(Request $request): JsonResponse
    {
        return $this->sukses(KeranjangResource::make($this->keranjang->lihat($request->user())));
    }

    public function simpanButir(ButirKeranjangRequest $request, int $produkId): JsonResponse
    {
        $this->keranjang->aturButir($request->user(), $produkId, $request->integer('kuantitas'));

        return $this->show($request);
    }

    public function hapusButir(Request $request, int $produkId): JsonResponse
    {
        $this->keranjang->hapusButir($request->user(), $produkId);

        return $this->show($request);
    }

    public function kosongkan(Request $request): JsonResponse
    {
        $this->keranjang->kosongkan($request->user());

        return $this->show($request);
    }
}
