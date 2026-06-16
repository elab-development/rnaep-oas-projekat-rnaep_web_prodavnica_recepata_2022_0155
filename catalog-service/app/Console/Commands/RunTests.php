<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunTests extends Command
{
    protected $signature = 'test';

    protected $description = 'Run application tests using PHPUnit';

    public function handle(): int
    {
        $this->info('Running PHPUnit tests...');

        $phpunitPath = base_path('vendor/bin/phpunit');

        if (!file_exists($phpunitPath)) {
            $this->error('PHPUnit nije pronađen. Pokreni prvo: composer install');
            return Command::FAILURE;
        }

        $process = new Process([$phpunitPath], base_path());
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        return $process->isSuccessful()
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}