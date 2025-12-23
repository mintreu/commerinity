<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ResetAppCommand extends Command
{
    protected $signature = 'app:reset {--force : Force reset without prompts}';

    protected $description = 'Reset the application (migrations, seeders, caches, assets)';

    public function handle(): int
    {
        $this->info('🚀 Starting application reset...');

        // Generate app key if not already set
        if (empty(config('app.key'))) {
            $this->info('🔑 Generating application key...');
            $this->call('key:generate');
        }

        // Create storage symlink if it doesn't exist
        if (! File::exists(public_path('storage'))) {
            $this->info('🔗 Creating storage symlink...');
            $this->call('storage:link');
        }

        // Install and run migrations
        $this->info('📊 Running database migrations...');
        $this->call('migrate:fresh', ['--force' => true]);
        $this->info('✅ Database migrated successfully!');

        // Seed the database
        $this->info('🌱 Seeding database...');
        $this->call('db:seed', ['--force' => true]);
        $this->info('✅ Database seeded successfully!');

        // Clear and optimize caches
        $this->info('🧹 Clearing caches...');
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        $this->info('⚡ Optimizing for development...');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->info('✅ Caches optimized successfully!');

        $this->newLine();
        $this->info('🎉 Application reset completed successfully!');
        $this->newLine();

        return self::SUCCESS;
    }
}
