<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class QbiezSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        // Merge package config with app config
        $this->mergeConfigFrom(__DIR__ . '/../config/qsms.php', 'qsms');

        // Bind the service
        $this->app->singleton('qsms', function ($app) {
            return new SendSMS();
        });

        // Register Facade alias if it does not exist
        if (!class_exists('Qsms')) {
            class_alias(Facades\Qsms::class, 'Qsms');
        }
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/qsms.php' => config_path('qsms.php'),
            ], 'qsms-config');

            $this->publishes([
                __DIR__ . '/../config/qsms.php' => config_path('qsms.php'),
            ], 'config');

            $this->registerCommands();
        }
    }

    protected function registerCommands()
    {
        if (class_exists(Console\CleanLogsCommand::class)) {
            $this->commands([
                Console\CleanLogsCommand::class
            ]);
        }
    }

    public function provides()
    {
        return ['qsms', SendSMS::class];
    }
}
