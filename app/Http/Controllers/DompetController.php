<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Uang;
use App\Services\BukuBesar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DompetController extends Controller
{
    public function __construct(private readonly BukuBesar $buku) {}

    public function show(Request $request): JsonResponse
    {
        $dompet = $this->buku->cariDompetPenjual($request->user());
        $saldo = Uang::dari($dompet?->saldo_rupiah ?? 0);

        return $this->sukses([
            'saldo' => ['rupiah' => $saldo, 'tampilan' => $saldo->format()],
            'jumlah_mutasi' => $dompet?->mutasi()->count() ?? 0,
        ]);
    }
}
