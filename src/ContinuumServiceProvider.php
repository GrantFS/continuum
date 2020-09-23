<?php

namespace Loopy\Continuum;

use Illuminate\Support\ServiceProvider;
use View;
use Loopy\Continuum\Services\Continuum;

class ContinuumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'continuum');
        $this->publishes([__DIR__ . '/Config/continuum.php' => config_path('continuum.php')], 'config');
        $this->publishes([__DIR__ . '/Views/publish' => app_path('Vendor/Continuum')]);
        $this->publishes([__DIR__ . '/Public' => public_path('vendor/loopy/continuum')], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('continuum', function () {
            return new Continuum;
        });
    }
}
