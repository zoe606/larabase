<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class HerdDevCommand extends Command
{
    protected $signature = 'herd:dev';

    protected $description = 'Run queue worker and Reverb WebSocket server for Herd development';

    public function handle(): int
    {
        $this->info('Starting queue worker and Reverb for Herd development...');
        $this->newLine();

        $process = new Process([
            'npx', 'concurrently',
            '-c', '#c4b5fd,#86efac',
            '--names=queue,reverb',
            'php artisan queue:listen --tries=1',
            'php artisan reverb:start',
        ]);

        $process->setWorkingDirectory(base_path());
        $process->setTimeout(null);
        $process->setTty(Process::isTtySupported());

        $process->run(function (string $type, string $output): void {
            $this->output->write($output);
        });

        return self::SUCCESS;
    }
}
