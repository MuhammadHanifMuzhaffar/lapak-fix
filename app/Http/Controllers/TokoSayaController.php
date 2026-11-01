<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\SudahPunyaToko;
use App\Http\Requests\TokoSayaRequest;
use App\Http\Resources\TokoSayaResource;
use App\Models\Toko;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class TokoSayaController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $toko = Toko::query()->milik($request->user())->withCount('produk')->firstOrFail();

        return $this->sukses(TokoSayaResource::make($toko));
    }

    public function store(TokoSayaRequest $request): JsonResponse
    {
        $pemilik = $request->user();

        if (Toko::query()->milik($pemilik)->exists()) {
            throw new SudahPunyaToko;
        }

        $toko = new Toko($request->validated());
        $toko->pemilik_id = $pemilik->id;
        $toko->slug = Str::slug($request->validated('nama')).'-'.Str::lower(Str::random(5));

        try {
            $toko->save();
        } catch (UniqueConstraintViolationException) {
            throw new SudahPunyaToko;
        }

        return $this->sukses(TokoSayaResource::make($toko->refresh()), 201);
    }

    public function update(TokoSayaRequest $request): JsonResponse
    {
        $toko = Toko::query()->milik($request->user())->firstOrFail();
        $toko->update($request->validated());

        return $this->sukses(TokoSayaResource::make($toko));
    }
}
