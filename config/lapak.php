<?php

return [
    'versi' => '0.1.0',

    'komisi_persen' => (int) env('LAPAK_KOMISI_PERSEN', 5),
    'batas_bayar_jam' => (int) env('LAPAK_BATAS_BAYAR_JAM', 24),
    'tahan_dana_hari' => (int) env('LAPAK_TAHAN_DANA_HARI', 3),
    'pencairan_minimum_rupiah' => (int) env('LAPAK_PENCAIRAN_MINIMUM', 50000),

    'katalog' => [
        'per_halaman_bawaan' => 12,
        'per_halaman_maksimum' => 60,
    ],

    'belanja' => [
        'butir_maksimum' => 20,
        'kuantitas_maksimum' => 99,
    ],

    'sesi' => [
        'masa_hidup_hari' => (int) env('LAPAK_SESI_MASA_HIDUP_HARI', 7),
        'menganggur_menit' => (int) env('LAPAK_SESI_MENGANGGUR_MENIT', 120),
        'segar_menit' => (int) env('LAPAK_SESI_SEGAR_MENIT', 10),
        'jeda_sentuh_detik' => 60,
        'simpan_hari' => 30,
    ],

    'laju' => [
        'masuk_per_akun' => 5,
        'masuk_per_ip' => 20,
        'konfirmasi' => 5,
    ],

    'izin' => [
        'ingat_menit' => (int) env('LAPAK_IZIN_INGAT_MENIT', 120),
    ],
];
