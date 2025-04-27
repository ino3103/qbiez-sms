<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;

class QbiezSmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Automatically merge package config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/qsms.php',
            'qsms'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Publish config file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/qsms.php' => config_path('qsms.php'),
            ], 'qsms-config');
        }
    }
}
