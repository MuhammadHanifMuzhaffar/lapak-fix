<?php

declare(strict_types=1);

namespace App\Http\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Amplop respons tunggal untuk seluruh domain.
 * Bentuknya dikunci di docs/KONTRAK-API.md; mengubahnya berarti mengubah kontrak.
 */
trait ResponsApi
{
    protected function sukses(mixed $data = null, int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json([
            'sukses' => true,
            'data' => $data,
            'meta' => $meta === [] ? null : $meta,
        ], $status);
    }

    /**
     * @param  class-string<JsonResource>  $resource
     */
    protected function berhalaman(LengthAwarePaginator $halaman, string $resource): JsonResponse
    {
        return $this->sukses($resource::collection($halaman->items()), 200, [
            'halaman' => $halaman->currentPage(),
            'per_halaman' => $halaman->perPage(),
            'total' => $halaman->total(),
            'halaman_terakhir' => $halaman->lastPage(),
        ]);
    }
}
