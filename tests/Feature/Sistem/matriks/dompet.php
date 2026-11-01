<?php

return [
    'POST api/v1/webhook/pembayaran' => '*',
    'GET api/v1/dompet' => ['penjual'],
    'GET api/v1/dompet/mutasi' => ['penjual'],
    'GET api/v1/pembayaran-saya/{nomorPesanan}' => ['pembeli'],
    'GET api/v1/buku-besar/dompet' => ['keuangan', 'auditor'],
    'GET api/v1/buku-besar/mutasi' => ['keuangan', 'auditor'],
];
