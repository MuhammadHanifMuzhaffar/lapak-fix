<?php

it('melarang penyimpanan respons API di cache', function () {
    $sehat = $this->getJson('/api/v1/health')->assertOk();
    $ditolak = $this->getJson('/api/v1/saya')->assertUnauthorized();

    expect($sehat->headers->get('Cache-Control'))->toContain('no-store')
        ->and($ditolak->headers->get('Cache-Control'))->toContain('no-store');
});
