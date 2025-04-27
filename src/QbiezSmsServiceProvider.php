<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class QbiezSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            $this->getConfigPath(),
            'qsms'
        );

        // Bind the service
        $this->app->singleton('qsms', function ($app) {
            return new SendSMS();
        });

        // Alias the Facade
        if (!class_exists('Qsms')) {
            class_alias(Facades\Qsms::class, 'Qsms');
        }
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
            $this->registerCommands();
        }
    }

    protected function offerPublishing()
    {
        $this->publishes([
            $this->getConfigPath() => config_path('qsms.php'),
        ], ['qsms-config', 'config']);
    }

    protected function getConfigPath()
    {
        return dirname(__DIR__) . '/config/qsms.php';
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
