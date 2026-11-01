<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\TokoSayaResource;
use App\Models\Toko;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TokoSayaController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $toko = Toko::query()->milik($request->user())->withCount('produk')->firstOrFail();

        return $this->sukses(TokoSayaResource::make($toko));
    }
}
