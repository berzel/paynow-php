<?php

namespace Berzel\Paynow;

use Illuminate\Support\ServiceProvider;

class PaynowServiceProvider extends ServiceProvider
{
    public function register ()
    {
        $this->app->bind('paynow', 'Berzel\Paynow\Paynow');

        $config = __DIR__ . '/../config/paynow.php';
        $this->mergeConfigFrom($config, 'paynow');
        $this->publishes([__DIR__ . '/../config/paynow.php' => config_path('paynow.php')], 'paynow-config');
    }

    public function boot ()
    {
        # code...
    }
}
