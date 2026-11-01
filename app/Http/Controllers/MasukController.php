<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StatusAkun;
use App\Exceptions\GalatDomain;
use App\Exceptions\KredensialSalah;
use App\Http\Requests\MasukRequest;
use App\Http\Resources\PenggunaResource;
use App\Http\Resources\SesiResource;
use App\Models\Pengguna;
use App\Services\PemeriksaKataSandi;
use App\Services\PenerbitSesi;
use Illuminate\Http\JsonResponse;

final class MasukController extends Controller
{
    public function __construct(
        private readonly PemeriksaKataSandi $sandi,
        private readonly PenerbitSesi $penerbit,
    ) {}

    public function __invoke(MasukRequest $request): JsonResponse
    {
        $pengguna = Pengguna::query()->where('surel', $request->validated('surel'))->first();

        if (! $this->sandi->cocok($pengguna, $request->validated('kata_sandi'))) {
            throw KredensialSalah::saatMasuk();
        }

        if ($pengguna->status !== StatusAkun::Aktif) {
            throw new GalatDomain('AKUN_DITANGGUHKAN', 'Akun ini ditangguhkan. Hubungi admin.', 403);
        }

        [$sesi, $kunci] = $this->penerbit->terbitkan(
            $pengguna, $request->namaPerangkat(), $request->ip(), $request->userAgent(),
        );

        return $this->sukses([
            'kunci' => $kunci,
            'sesi' => SesiResource::saatIni($sesi),
            'pengguna' => PenggunaResource::make($pengguna),
        ], 201);
    }
}
