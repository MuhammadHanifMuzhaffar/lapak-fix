<?php

declare(strict_types=1);

namespace App\Enums;

enum NamaIzin: string
{
    case KeranjangKelola = 'keranjang.kelola';
    case TokoBuka = 'toko.buka';
    case ProdukKelola = 'produk.kelola';
    case DompetLihat = 'dompet.lihat';
    case TokoModerasi = 'toko.moderasi';
    case PeranKelola = 'peran.kelola';
    case BukuBesarLihat = 'buku-besar.lihat';
    case PesananBuat = 'pesanan.buat';
}
