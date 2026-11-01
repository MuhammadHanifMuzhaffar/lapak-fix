<?php

it('melarang penulisan langsung pada tabel pengguna_peran di luar PengelolaPeran', function () {
    $jalur = app_path();
    $penghubung = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($jalur));
    foreach ($iterator as $satu) {
        if (! $satu->isFile() || $satu->getFilename() === 'PengelolaPeran.php') {
            continue;
        }

        $isi = file_get_contents($satu->getPathname());
        if (str_contains($isi, 'pengguna_peran')) {
            $penghubung[] = $satu->getPathname();
        }
    }

    expect($penghubung)->toBeEmpty('Penyebutan pengguna_peran di luar PengelolaPeran: '.implode(', ', $penghubung));
});
