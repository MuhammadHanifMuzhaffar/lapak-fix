<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Induk seluruh pelanggaran aturan bisnis.
 * $kode memuat kode aturan (mis. LP-13) agar klien, log, dan kasus uji merujuk hal yang sama.
 */
class GalatDomain extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $rincian
     */
    public function __construct(
        public readonly string $kode,
        string $pesan,
        public readonly int $statusHttp = 422,
        public readonly array $rincian = [],
    ) {
        parent::__construct($pesan);
    }
}
