<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\NamaPeran;
use App\Exceptions\GalatDomain;
use App\Exceptions\PeranMengikutiToko;
use App\Http\Requests\DaftarPenggunaRequest;
use App\Http\Resources\PenggunaResource;
use App\Models\Pengguna;
use App\Services\PengelolaPeran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PeranPenggunaController extends Controller
{
    public function __construct(private readonly PengelolaPeran $pengelola) {}

    public function index(DaftarPenggunaRequest $request): JsonResponse
    {
        $halaman = Pengguna::query()
            ->with('peran')
            ->when($request->validated('peran'), fn (Builder $q, string $peran) => $q
                ->whereHas('peran', fn (Builder $p) => $p->where('nama', $peran)))
            ->orderBy('id')
            ->paginate($request->integer('per_halaman', 20), ['*'], 'halaman');

        return $this->berhalaman($halaman, PenggunaResource::class);
    }

    public function beri(Request $request, int $id, string $peran): JsonResponse
    {
        $sasaran = Pengguna::query()->findOrFail($id);
        $peranEnum = NamaPeran::from($peran);

        $this->tegakkanAturan($request->user(), $sasaran, $peranEnum);
        $this->pengelola->beri($sasaran, $peranEnum, oleh: $request->user());

        return $this->sukses(PenggunaResource::make($sasaran->fresh('peran')));
    }

    public function cabut(Request $request, int $id, string $peran): JsonResponse
    {
        $sasaran = Pengguna::query()->findOrFail($id);
        $peranEnum = NamaPeran::from($peran);

        $this->tegakkanAturan($request->user(), $sasaran, $peranEnum);
        $this->pengelola->cabut($sasaran, $peranEnum);

        return $this->sukses(PenggunaResource::make($sasaran->fresh('peran')));
    }

    private function tegakkanAturan(Pengguna $pelaku, Pengguna $sasaran, NamaPeran $peran): void
    {
        if (! Gate::forUser($pelaku)->check('kelolaPeran', $sasaran)) {
            throw new GalatDomain('DITOLAK', 'Anda tidak dapat mengubah peran Anda sendiri.', 403);
        }

        if ($peran === NamaPeran::Penjual) {
            throw new PeranMengikutiToko;
        }
    }
}
