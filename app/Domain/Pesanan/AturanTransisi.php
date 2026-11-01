<?php

declare(strict_types=1);

namespace App\Domain\Pesanan;

final class AturanTransisi
{
    public static function boleh(StatusPesanan $dari, StatusPesanan $ke, PelakuPesanan $oleh): bool
    {
        return in_array($oleh, self::jalur($dari)[$ke->value] ?? [], true);
    }

    /**
     * @return list<StatusPesanan>
     */
    public static function tujuan(StatusPesanan $dari, PelakuPesanan $oleh): array
    {
        $tujuan = [];

        foreach (self::jalur($dari) as $ke => $pelaku) {
            if (in_array($oleh, $pelaku, true)) {
                $tujuan[] = StatusPesanan::from($ke);
            }
        }

        return $tujuan;
    }

    /**
     * @return array<string, list<PelakuPesanan>>
     */
    private static function jalur(StatusPesanan $dari): array
    {
        $pembeli = PelakuPesanan::Pembeli;
        $penjual = PelakuPesanan::Penjual;
        $sistem = PelakuPesanan::Sistem;

        return match ($dari) {
            StatusPesanan::MenungguPembayaran => [
                'dibayar' => [$sistem],
                'dibatalkan' => [$pembeli, $sistem],
            ],
            StatusPesanan::Dibayar => [
                'dikemas' => [$penjual],
                'dibatalkan' => [$pembeli, $penjual],
            ],
            StatusPesanan::Dikemas => [
                'dikirim' => [$penjual],
                'dibatalkan' => [$pembeli, $penjual],
            ],
            StatusPesanan::Dikirim => [
                'selesai' => [$pembeli, $sistem],
            ],
            StatusPesanan::Selesai, StatusPesanan::Dibatalkan => [],
        };
    }
}
