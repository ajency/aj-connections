<?php

namespace Ajency\Connections;

use Illuminate\Support\ServiceProvider;

class ConnectionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/elastic.php' => config_path('elastic.php'),
            __DIR__ . '/config/odoo.php'    => config_path('odoo.php'),
        ], 'config');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
