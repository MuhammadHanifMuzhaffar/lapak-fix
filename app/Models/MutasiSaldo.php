<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\JenisMutasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class MutasiSaldo extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'mutasi_saldo';

    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'jenis' => JenisMutasi::class,
            'jumlah_rupiah' => 'integer',
            'saldo_setelah' => 'integer',
        ];
    }

    /**
     * LP-13: mutasi hanya bisa ditambah. Kesalahan dikoreksi dengan mutasi pembalik,
     * bukan dengan menyunting atau menghapus baris lama.
     */
    protected static function booted(): void
    {
        self::updating(fn () => throw new LogicException('LP-13: mutasi saldo tidak boleh diubah.'));
        self::deleting(fn () => throw new LogicException('LP-13: mutasi saldo tidak boleh dihapus.'));
    }

    /** @return BelongsTo<Dompet, $this> */
    public function dompet(): BelongsTo
    {
        return $this->belongsTo(Dompet::class, 'dompet_id');
    }
}
