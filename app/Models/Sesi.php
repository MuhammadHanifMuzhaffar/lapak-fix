<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\PemeriksaSesi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use LogicException;

final class Sesi extends Model
{
    protected $table = 'sesi';

    protected $fillable = [];

    protected $hidden = ['hash_kunci'];

    protected function casts(): array
    {
        return [
            'dikonfirmasi_pada' => 'datetime',
            'terakhir_dipakai_pada' => 'datetime',
            'kedaluwarsa_pada' => 'datetime',
            'dicabut_pada' => 'datetime',
        ];
    }

    /** @return BelongsTo<Pengguna, $this> */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function scopeBerlaku(Builder $kueri): void
    {
        $batasMenganggur = now()->subMinutes(config('lapak.sesi.menganggur_menit'));

        $kueri->whereNull('dicabut_pada')
            ->where('kedaluwarsa_pada', '>', now())
            ->where('terakhir_dipakai_pada', '>', $batasMenganggur);
    }

    public function segar(): bool
    {
        return $this->dikonfirmasi_pada !== null
            && $this->dikonfirmasi_pada->gt(now()->subMinutes(config('lapak.sesi.segar_menit')));
    }

    public static function dariPermintaan(Request $request): self
    {
        if ($sesi = $request->attributes->get('sesi')) {
            return $sesi;
        }

        if ($token = $request->bearerToken()) {
            $pemeriksa = app(PemeriksaSesi::class);
            if ($sesi = $pemeriksa->periksa($token)) {
                $request->attributes->set('sesi', $sesi);

                return $sesi;
            }
        }

        throw new LogicException('Rute ini memakai sesi tetapi tidak dilindungi auth:sesi.');
    }
}
