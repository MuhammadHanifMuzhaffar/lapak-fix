<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegistrasiRequest;
use App\Http\Resources\PenggunaResource;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;

final class RegistrasiController extends Controller
{
    public function __invoke(RegistrasiRequest $request): JsonResponse
    {
        $pengguna = Pengguna::create(
            $request->safe()->only(['nama', 'surel', 'telepon', 'kata_sandi'])
        )->refresh();

        return $this->sukses(PenggunaResource::make($pengguna), 201);
    }
}
