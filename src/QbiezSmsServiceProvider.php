<?php

namespace Qsms\QbiezSms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class QbiezSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        // First try published config, then package default
        $this->mergeConfigFrom(
            $this->getConfigPath(),
            'qsms'
        );

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
            $this->offerPublishing();
            $this->registerCommands();
        }
    }

    protected function offerPublishing()
    {
        // Only offer to publish if config doesn't exist
        if (!file_exists(config_path('qsms.php'))) {
            $this->publishes([
                $this->getConfigPath() => config_path('qsms.php'),
            ], ['qsms-config', 'config']);
        }
    }

    protected function getConfigPath()
    {
        // Check both possible locations
        $localConfig = __DIR__ . '/../../config/qsms.php';
        $packageConfig = __DIR__ . '/../config/qsms.php';

        return file_exists($localConfig) ? $localConfig : $packageConfig;
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
