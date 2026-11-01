<?php

declare(strict_types=1);

namespace App\Domain\Belanja;

use App\Domain\Uang;
use InvalidArgumentException;

final readonly class BarisBelanja
{
    public function __construct(
        public int $produkId,
        public string $namaProduk,
        public int $tokoId,
        public Uang $hargaSatuan,
        public int $kuantitas,
    ) {
        if ($kuantitas < 1) {
            throw new InvalidArgumentException('Kuantitas minimal 1.');
        }
    }

    public function subtotal(): Uang
    {
        return $this->hargaSatuan->kali($this->kuantitas);
    }
}
