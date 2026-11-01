<?php

namespace Tests;

use App\Models\Pengguna;
use App\Models\Sesi;
use App\Services\PenerbitSesi;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ?Sesi $sesiUji = null;

    protected function sebagai(Pengguna $pengguna, bool $segar = true): static
    {
        [$this->sesiUji, $kunci] = app(PenerbitSesi::class)->terbitkan($pengguna, 'Perangkat uji');

        if (! $segar) {
            $this->sesiUji->forceFill(['dikonfirmasi_pada' => now()->subHour()])->save();
        }

        return $this->denganKunci($kunci);
    }

    protected function denganKunci(string $kunci): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$kunci);
    }

    protected function tanpaKunci(): static
    {
        return $this->withoutHeader('Authorization');
    }

    public function call(
        $method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null,
    ) {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }
}
