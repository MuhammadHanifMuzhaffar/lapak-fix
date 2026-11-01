<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
}
