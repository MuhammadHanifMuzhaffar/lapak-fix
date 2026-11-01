<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DompetBukuBesarResource;
use App\Http\Resources\MutasiBukuBesarResource;
use App\Models\Dompet;
use Illuminate\Http\JsonResponse;

final class BukuBesarController extends Controller
{
    public function index(): JsonResponse
    {
        $dompet = Dompet::query()->with('pemilik')->orderBy('id')->get();
        $jumlah = (int) $dompet->sum('saldo_rupiah');

        return $this->sukses(DompetBukuBesarResource::collection($dompet), 200, [
            'jumlah_dompet' => $dompet->count(),
            'jumlah_seluruh_saldo' => $jumlah,
            'seimbang' => $jumlah === 0,
        ]);
    }

    public function mutasi(int $id): JsonResponse
    {
        $halaman = Dompet::query()->findOrFail($id)
            ->mutasi()
            ->orderByDesc('id')
            ->paginate(config('lapak.katalog.per_halaman_bawaan'), ['*'], 'halaman');

        return $this->berhalaman($halaman, MutasiBukuBesarResource::class);
    }
}
