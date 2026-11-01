<?php

return [
    'GET api/v1/saya' => '*',
    'POST api/v1/sesi/konfirmasi' => '*',
    'GET api/v1/sesi' => '*',
    'DELETE api/v1/sesi/{id}' => '*',
    'DELETE api/v1/sesi' => '*',
    'GET api/v1/admin/pengguna' => ['admin'],
    'POST api/v1/admin/pengguna/{id}/peran/{peran}' => ['admin'],
    'DELETE api/v1/admin/pengguna/{id}/peran/{peran}' => ['admin'],
    'GET api/v1/alamat' => ['pembeli'],
    'POST api/v1/alamat' => ['pembeli'],
    'PATCH api/v1/alamat/{alamat}' => ['pembeli'],
    'DELETE api/v1/alamat/{alamat}' => ['pembeli'],
    'POST api/v1/alamat/{alamat}/jadikan-utama' => ['pembeli'],
];
