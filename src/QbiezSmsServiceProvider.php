<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;

class QbiezSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merges the package config with the app's config
        $this->mergeConfigFrom(__DIR__ . '/../config/qsms.php', 'qsms');
    }

    public function boot()
    {
        // Publishes the config file to the app's config directory
        $this->publishes([
            __DIR__ . '/../config/qsms.php' => config_path('qsms.php'),
        ], 'qsms-config');
    }
}
