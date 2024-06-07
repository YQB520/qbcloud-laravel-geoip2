<?php

namespace QbCloud\Geoip2\Providers;

use Illuminate\Support\ServiceProvider;
use QbCloud\Geoip2\IPQuery;

class GeoipServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('geoip2', function ($app) {
            return new IPQuery();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__ . '/../../config/geoip2.php');

        $this->publishes([$path => config_path('geoip2.php')], 'config');

        $this->mergeConfigFrom($path, 'geoip2');
    }
}
