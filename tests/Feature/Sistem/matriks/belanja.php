<?php

return [
    'POST api/v1/simulasi-belanja' => '*',
    'GET api/v1/keranjang' => ['pembeli'],
    'PUT api/v1/keranjang/butir/{produk}' => ['pembeli'],
    'DELETE api/v1/keranjang/butir/{produk}' => ['pembeli'],
    'DELETE api/v1/keranjang' => ['pembeli'],
    'POST api/v1/checkout' => ['pembeli'],
    'GET api/v1/pesanan-saya' => ['pembeli'],
    'GET api/v1/pesanan-saya/{nomor}' => ['pembeli'],
];
