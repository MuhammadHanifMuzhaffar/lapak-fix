<?php

return [
    'GET api/v1/keranjang' => ['pembeli'],
    'DELETE api/v1/keranjang' => ['pembeli'],
    'PUT api/v1/keranjang/butir/{produkId}' => ['pembeli'],
    'DELETE api/v1/keranjang/butir/{produkId}' => ['pembeli'],

    'POST api/v1/checkout' => ['pembeli'],
    'GET api/v1/pesanan' => ['pembeli'],
    'GET api/v1/pesanan/{nomor}' => ['pembeli'],
];
