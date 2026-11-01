<?php

return [
    'versi' => '0.1.0',

    // LP-8: komisi marketplace, dipotong dari bagian penjual saat dana dilepas.
    'komisi_persen' => (int) env('LAPAK_KOMISI_PERSEN', 5),

    // LP-10: pesanan yang belum dibayar dibatalkan otomatis setelah sekian jam.
    'batas_bayar_jam' => (int) env('LAPAK_BATAS_BAYAR_JAM', 24),

    // LP-12: dana dilepas otomatis sekian hari setelah pesanan dikirim.
    'tahan_dana_hari' => (int) env('LAPAK_TAHAN_DANA_HARI', 3),

    // LP-16: batas bawah pencairan saldo penjual.
    'pencairan_minimum_rupiah' => (int) env('LAPAK_PENCAIRAN_MINIMUM', 50000),

    'katalog' => [
        'per_halaman_bawaan' => 12,
        'per_halaman_maksimum' => 60,
    ],

    'belanja' => [
        'butir_maksimum' => 20,
        'kuantitas_maksimum' => 99,
    ],
];
