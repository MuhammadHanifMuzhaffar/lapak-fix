<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\GalatDomain;
use App\Http\Requests\DaftarTokoRequest;
use App\Http\Requests\TangguhkanTokoRequest;
use App\Http\Resources\TokoModerasiResource;
use App\Models\Toko;
use App\Services\ModerasiToko;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ModerasiTokoController extends Controller
{
    public function __construct(private readonly ModerasiToko $moderasi) {}

    public function index(DaftarTokoRequest $request): JsonResponse
    {
        $halaman = Toko::query()
            ->with(['pemilik', 'pemoderasi'])
            ->when($request->validated('status'), fn (Builder $q, string $status) => $q
                ->where('status', $status))
            ->orderByDesc('id')
            ->paginate($request->integer('per_halaman', 20), ['*'], 'halaman');

        return $this->berhalaman($halaman, TokoModerasiResource::class);
    }

    public function setujui(Request $request, int $id): JsonResponse
    {
        $toko = Toko::query()->findOrFail($id);
        $this->tegakkanAturan($request->user(), $toko);

        $hasil = $this->moderasi->setujui($toko, $request->user());

        return $this->sukses(TokoModerasiResource::make($hasil->load(['pemilik', 'pemoderasi'])));
    }

    public function tangguhkan(TangguhkanTokoRequest $request, int $id): JsonResponse
    {
        $toko = Toko::query()->findOrFail($id);
        $this->tegakkanAturan($request->user(), $toko);

        $hasil = $this->moderasi->tangguhkan($toko, $request->user(), $request->validated('alasan'));

        return $this->sukses(TokoModerasiResource::make($hasil->load(['pemilik', 'pemoderasi'])));
    }

    private function tegakkanAturan($pelaku, Toko $toko): void
    {
        if (! Gate::forUser($pelaku)->check('moderasi', $toko)) {
            throw new GalatDomain('DITOLAK', 'Anda tidak dapat memoderasi toko Anda sendiri.', 403);
        }
    }
}
