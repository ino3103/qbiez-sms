<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;

class QbiezSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/qsms.php',
            'qsms'
        );

        $this->app->singleton('qsms', function ($app) {
            return new SendSMS();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/qsms.php' => config_path('qsms.php'),
        ], 'qsms-config');
    }
}
