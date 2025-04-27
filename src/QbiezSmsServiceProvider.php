<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class QbiezSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $configPath = __DIR__ . '/../config/qsms.php'; // Fixed path

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'qsms');
        } else {
            $this->app['log']->warning('Qbiez SMS config file not found at: ' . $configPath);
        }

        $this->app->singleton('qsms', function ($app) {
            return new SendSMS();
        });

        if (!class_exists('Qsms')) {
            class_alias(Facades\Qsms::class, 'Qsms');
        }
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->registerCommands();
        }
    }

    protected function publishConfig()
    {
        $configPath = __DIR__ . '/../config/qsms.php'; // Fixed path

        $this->publishes([
            $configPath => config_path('qsms.php'),
        ], ['qsms-config', 'config']);
    }

    protected function registerCommands()
    {
        if (class_exists(Console\CleanLogsCommand::class)) { // Added namespace
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
