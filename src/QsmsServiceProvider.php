<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;
use Qsms\QbiezSms\SendSMS;

class QsmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/qsms.php', 'qsms');

        // Register the SMS service as a singleton
        $this->app->singleton('qsms', function ($app) {
            return new SendSMS();
        });

        // Optionally register an alias for backward compatibility
        $this->app->alias('qsms', SendSMS::class);
    }

    public function boot()
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/qsms.php' => config_path('qsms.php'),
        ], 'qsms-config');
    }
}
