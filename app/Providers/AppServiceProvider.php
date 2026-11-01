<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Mode ketat: menolak lazy loading, atribut belum dimuat, dan unfillable mass assignment.
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
