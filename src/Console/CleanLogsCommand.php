<?php

namespace Qsms\QbiezSms\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanLogsCommand extends Command
{
    protected $signature = 'qsms:clean-logs {--days=30}';

    public function handle()
    {
        $days = $this->option('days');
        $path = config('qsms.logging.path');

        foreach (glob("{$path}/*.log") as $file) {
            if (filemtime($file) < now()->subDays($days)->getTimestamp()) {
                unlink($file);
            }
        }

        $this->info("Cleaned SMS logs older than {$days} days.");
    }
}
