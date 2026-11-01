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

    public function keterangan(): string
    {
        return match ($this) {
            self::Pembeli => 'Berbelanja dan mengajukan pembukaan toko',
            self::Penjual => 'Mengelola produk dan melihat dompet tokonya',
            self::Admin => 'Memoderasi toko dan mengelola peran pengguna',
            self::Keuangan => 'Membaca buku besar; menyetujui pencairan mulai Sesi 5',
            self::Auditor => 'Membaca buku besar tanpa hak tulis apa pun',
        };
    }

    /**
     * @return list<NamaIzin>
     */
    public function izinBawaan(): array
    {
        return match ($this) {
            self::Pembeli => [NamaIzin::KeranjangKelola, NamaIzin::TokoBuka],
            self::Penjual => [NamaIzin::ProdukKelola, NamaIzin::DompetLihat],
            self::Admin => [NamaIzin::TokoModerasi, NamaIzin::PeranKelola],
            self::Keuangan => [NamaIzin::BukuBesarLihat],
            self::Auditor => [NamaIzin::BukuBesarLihat],
        };
    }
}
