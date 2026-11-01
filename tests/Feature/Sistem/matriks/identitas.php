<?php

return [
    'GET api/v1/saya' => '*',
    'POST api/v1/keluar' => '*',
    'GET api/v1/sesi' => '*',
    'POST api/v1/sesi/konfirmasi' => '*',
    'DELETE api/v1/sesi/{pengenal}' => '*',

    'GET api/v1/admin/pengguna' => ['admin'],
    'PUT api/v1/admin/pengguna/{id}/peran/{peran}' => ['admin'],
    'DELETE api/v1/admin/pengguna/{id}/peran/{peran}' => ['admin'],
];
