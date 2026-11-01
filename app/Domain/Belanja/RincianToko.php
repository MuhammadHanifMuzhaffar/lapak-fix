<?php

declare(strict_types=1);

namespace App\Domain\Belanja;

use App\Domain\Uang;

final readonly class RincianToko
{
    /**
     * @param  list<BarisBelanja>  $baris
     */
    public function __construct(
        public int $tokoId,
        public string $namaToko,
        public array $baris,
        public Uang $subtotal,
        public Uang $ongkir,
        public Uang $komisi,
    ) {}

    public function total(): Uang
    {
        return $this->subtotal->tambah($this->ongkir);
    }

    public function bagianPenjual(): Uang
    {
        return $this->subtotal->kurang($this->komisi)->tambah($this->ongkir);
    }
}
