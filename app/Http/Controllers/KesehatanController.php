<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class KesehatanController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');
        } catch (Throwable) {
            return response()->json([
                'sukses' => false,
                'galat' => [
                    'kode' => 'TIDAK_SEHAT',
                    'pesan' => 'Basis data tidak terjangkau.',
                    'rincian' => null,
                ],
            ], 503);
        }

        return $this->sukses([
            'status' => 'sehat',
            'versi' => config('lapak.versi'),
            'waktu' => now()->toIso8601String(),
        ]);
    }
}
