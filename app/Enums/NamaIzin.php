<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Seluruh izin yang dikenal sistem. Izin dinamai <sumber daya>.<tindakan>.
 * Kode aplikasi HANYA bertanya "punya izin ini?", tidak pernah "apakah ia admin?" (LP-2).
 */
enum NamaIzin: string
{
    case KeranjangKelola = 'keranjang.kelola';
    case TokoBuka = 'toko.buka';
    case ProdukKelola = 'produk.kelola';
    case DompetLihat = 'dompet.lihat';
    case TokoModerasi = 'toko.moderasi';
    case PeranKelola = 'peran.kelola';
    case BukuBesarLihat = 'buku-besar.lihat';

    public function keterangan(): string
    {
        return match ($this) {
            self::KeranjangKelola => 'Mengisi dan mengubah keranjang belanja sendiri',
            self::TokoBuka => 'Mengajukan pembukaan toko',
            self::ProdukKelola => 'Menambah, mengubah, dan mengarsipkan produk toko sendiri',
            self::DompetLihat => 'Melihat saldo dan mutasi dompet sendiri',
            self::TokoModerasi => 'Menyetujui dan menangguhkan toko',
            self::PeranKelola => 'Memberi dan mencabut peran pengguna',
            self::BukuBesarLihat => 'Membaca seluruh dompet dan mutasi marketplace',
        };
    }
}
