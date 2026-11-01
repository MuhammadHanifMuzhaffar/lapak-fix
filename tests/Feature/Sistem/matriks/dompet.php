<?php

return [
    'GET api/v1/dompet' => ['penjual'],
    'GET api/v1/dompet/mutasi' => ['penjual'],

    'GET api/v1/buku-besar/dompet' => ['keuangan', 'auditor'],
    'GET api/v1/buku-besar/dompet/{id}/mutasi' => ['keuangan', 'auditor'],
];
