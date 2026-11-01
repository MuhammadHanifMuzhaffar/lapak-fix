<?php

return [
    'GET api/v1/kategori' => '*',
    'GET api/v1/produk' => '*',
    'GET api/v1/produk/{slug}' => '*',
    'GET api/v1/toko/{slug}' => '*',
    'POST api/v1/toko-saya' => ['pembeli'],
    'GET api/v1/toko-saya' => ['pembeli', 'penjual'],
    'PATCH api/v1/toko-saya' => ['pembeli', 'penjual'],
    'GET api/v1/toko-saya/produk' => ['penjual'],
    'POST api/v1/toko-saya/produk' => ['penjual'],
    'GET api/v1/toko-saya/produk/{id}' => ['penjual'],
    'PATCH api/v1/toko-saya/produk/{id}' => ['penjual'],
    'POST api/v1/toko-saya/produk/{id}/sesuaikan-stok' => ['penjual'],
    'POST api/v1/admin/toko/{id}/setujui' => ['admin'],
    'POST api/v1/admin/toko/{id}/tolak' => ['admin'],
    'POST api/v1/admin/toko/{id}/tangguhkan' => ['admin'],
    'POST api/v1/admin/toko/{id}/aktifkan' => ['admin'],
];
