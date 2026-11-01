<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Sesi\KunciSesi;
use App\Enums\NamaPeran;
use App\Enums\StatusAkun;
use App\Http\Requests\RegistrasiRequest;
use App\Http\Resources\PenggunaResource;
use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PengelolaPeran;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegistrasiController extends Controller
{
    public function __construct(private readonly PengelolaPeran $peran) {}

    public function __invoke(RegistrasiRequest $request): JsonResponse
    {
        [$pengguna, $kunci] = DB::transaction(function () use ($request): array {
            $pengguna = new Pengguna($request->safe()->only(['nama', 'surel', 'telepon']));
            $pengguna->kata_sandi = $request->validated('kata_sandi');
            $pengguna->status = StatusAkun::Aktif;
            $pengguna->save();

            $this->peran->beri($pengguna, NamaPeran::Pembeli);

            $pengenal = (string) Str::ulid();
            $rahasia = hash('sha256', Str::random(40));

            $sesi = new Sesi;
            $sesi->forceFill([
                'pengenal' => $pengenal,
                'pengguna_id' => $pengguna->id,
                'hash_kunci' => hash('sha256', $rahasia),
                'nama_perangkat' => 'Perangkat pendaftaran',
                'ip' => $request->ip(),
                'agen_pengguna' => $request->userAgent(),
                'terakhir_dipakai_pada' => now(),
                'dikonfirmasi_pada' => now(),
                'kedaluwarsa_pada' => now()->addDays(config('lapak.sesi.masa_hidup_hari')),
            ])->save();

            return [$pengguna, KunciSesi::dari($pengenal, $rahasia)->string()];
        });

        return $this->sukses([
            'kunci' => $kunci,
            'pengguna' => PenggunaResource::make($pengguna),
        ], 201);
    }
}
