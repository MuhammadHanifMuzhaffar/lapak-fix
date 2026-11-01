<?php

declare(strict_types=1);

namespace App\Enums;

enum NamaPeran: string
{
    case Pembeli = 'pembeli';
    case Penjual = 'penjual';
    case Admin = 'admin';
    case Keuangan = 'keuangan';
    case Auditor = 'auditor';

    public function daftarIzin(): array
    {
        return match ($this) {
            self::Pembeli => [
                NamaIzin::KeranjangKelola,
                NamaIzin::TokoBuka,
                NamaIzin::PesananBuat,
            ],
            self::Penjual => [
                NamaIzin::ProdukKelola,
                NamaIzin::DompetLihat,
            ],
            self::Admin => [
                NamaIzin::TokoModerasi,
                NamaIzin::PeranKelola,
            ],
            self::Keuangan => [
                NamaIzin::BukuBesarLihat,
            ],
            self::Auditor => [
                NamaIzin::BukuBesarLihat,
            ],
        };
    }
}
