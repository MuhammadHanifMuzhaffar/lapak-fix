<?php

it('hanya mengubah peran pengguna lewat PengelolaPeran (LP-2)', function () {
    $pelanggar = [];
    $berkas = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path()));

    foreach ($berkas as $satu) {
        if (! $satu->isFile() || $satu->getFilename() === 'PengelolaPeran.php') {
            continue;
        }

        $isi = file_get_contents($satu->getPathname());
        $lewatRelasi = '/->peran\(\)\s*->(attach|detach|sync\w*|toggle|updateExistingPivot)\(/';
        $lewatTabel = '/table\(\'pengguna_peran\'\)\s*->(insert\w*|update|delete|upsert)\(/';

        $menulis = preg_match($lewatRelasi, $isi) || preg_match($lewatTabel, $isi);

        if ($menulis) {
            $pelanggar[] = $satu->getFilename();
        }
    }

    expect($pelanggar)->toBeEmpty('Menulis peran di luar PengelolaPeran: '.implode(', ', $pelanggar));
});
