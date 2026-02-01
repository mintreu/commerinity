<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class TestRunCommand extends Command
{
    protected $signature = 'test:run
        {files?* : Specific test files or directories to run (e.g. tests/Feature)}
        {--suite= : Run the named Pest/ PHPUnit testsuite}
        {--group= : Run Pest test group}
        {--filter= : Run tests matching the filter}
        {--parallel : Run Pest tests in parallel}
        {--env= : Environment file to load for the run}
        {--phpunit-args= : Additional arguments forwarded to Pest/PHPUnit}
    ';

    protected $description = 'Convenient wrapper for php artisan test (group, file, and filter friendly).';

    public function handle(): int
    {
        $options = [];

        if ($files = $this->argument('files')) {
            $options['files'] = (array) $files;
        }

        if ($suite = $this->option('suite')) {
            $options['--testsuite'] = $suite;
        }

        if ($group = $this->option('group')) {
            $options['--group'] = $group;
        }

        if ($filter = $this->option('filter')) {
            $options['--filter'] = $filter;
        }

        if ($this->option('parallel')) {
            $options['--parallel'] = true;
        }

        if ($env = $this->option('env')) {
            $options['--env'] = $env;
        }

        if ($phpunitArgs = $this->option('phpunit-args')) {
            $options['--phpunit-args'] = $phpunitArgs;
        }

        $this->line('<info>Running:</info> php artisan test'.$this->buildCommandSuffix($options));

        $status = Artisan::call('test', $options);

        $this->line(Artisan::output());

        return $status === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function buildCommandSuffix(array $options): string
    {
        $parts = [];

        foreach ($options as $key => $value) {
            if ($key === 'files') {
                foreach ((array) $value as $file) {
                    $parts[] = $file;
                }

                continue;
            }

            if ($value === true) {
                $parts[] = $key;
                continue;
            }

            $parts[] = $key.'='.$value;
        }

        return $parts ? ' '.implode(' ', $parts) : '';
    }
}
