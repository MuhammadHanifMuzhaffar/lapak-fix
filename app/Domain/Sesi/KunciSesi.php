<?php

declare(strict_types=1);

namespace App\Domain\Sesi;

final readonly class KunciSesi
{
    public const AWALAN = 'lpk_';

    private const POLA = '/^lpk_([0-9A-HJKMNP-TV-Z]{26})\.([0-9a-f]{64})$/D';

    private function __construct(public string $pengenal, private string $rahasia) {}

    public static function baru(string $pengenal): self
    {
        return new self($pengenal, bin2hex(random_bytes(32)));
    }

    public static function urai(string $teks): ?self
    {
        if (preg_match(self::POLA, $teks, $bagian) !== 1) {
            return null;
        }

        return new self($bagian[1], $bagian[2]);
    }

    public function teks(): string
    {
        return self::AWALAN.$this->pengenal.'.'.$this->rahasia;
    }

    public function hash(): string
    {
        return hash('sha256', $this->rahasia);
    }

    public function cocok(string $hashTersimpan): bool
    {
        return hash_equals($hashTersimpan, $this->hash());
    }
}
