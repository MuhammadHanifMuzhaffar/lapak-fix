<?php

declare(strict_types=1);

namespace App\Domain\Belanja;

final readonly class ButirBermasalah
{
    public function __construct(
        public int $produkId,
        public int $kuantitas,
        public AlasanBermasalah $alasan,
        public ?string $namaProduk = null,
        public ?int $stokTersedia = null,
    ) {}
}
